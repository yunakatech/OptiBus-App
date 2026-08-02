<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill(collect($validated)->except('avatar')->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            if (! Schema::hasColumn('users', 'avatar')) {
                throw ValidationException::withMessages([
                    'avatar' => 'Kolom avatar belum tersedia. Jalankan migrasi database terlebih dahulu.',
                ]);
            }

            try {
                $this->replaceAvatar($user, $request->file('avatar'));
            } catch (Throwable $e) {
                Log::warning('profile.avatar_upload_failed', [
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                    'storage' => $this->safeSupabaseStorageDiagnostics(),
                ]);

                report($e);

                throw ValidationException::withMessages([
                    'avatar' => 'Gagal mengunggah foto profil ke storage. Periksa konfigurasi Supabase Storage.',
                ]);
            }
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        $this->deleteStoredAvatar($user->avatar);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function replaceAvatar(\App\Models\User $user, \Illuminate\Http\UploadedFile $file): void
    {
        $previousAvatar = $user->avatar;
        $disk = $this->avatarDisk();
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg'));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';
        $filename = 'avatar-'.Str::uuid()->toString().'.'.$extension;
        $path = $disk === 'supabase'
            ? $file->storeAs('', $filename, $disk)
            : $file->storeAs('avatars', $filename, $disk);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Profile avatar upload returned an empty path.');
        }

        $user->avatar = $this->avatarUrl($disk, $path);

        $this->deleteStoredAvatar($previousAvatar, $user->avatar);
    }

    private function deleteStoredAvatar(?string $avatar, ?string $except = null): void
    {
        $disk = $this->avatarStorageDiskForValue($avatar);
        $path = $this->avatarDiskPath($avatar, $disk);

        if ($path === null) {
            return;
        }

        if ($except !== null && $this->avatarDiskPath($except, $disk) === $path) {
            return;
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    private function avatarDisk(): string
    {
        if ($this->hasSupabaseAvatarStorage()) {
            return 'supabase';
        }

        return 'public';
    }

    private function hasSupabaseAvatarStorage(): bool
    {
        return trim((string) config('filesystems.disks.supabase.key', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.secret', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.bucket', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.endpoint', '')) !== ''
            && trim((string) config('filesystems.disks.supabase.url', '')) !== '';
    }

    /**
     * Keep production logs useful without exposing Supabase credentials.
     *
     * @return array<string, bool|string|null>
     */
    private function safeSupabaseStorageDiagnostics(): array
    {
        $endpoint = trim((string) config('filesystems.disks.supabase.endpoint', ''));
        $url = trim((string) config('filesystems.disks.supabase.url', ''));

        return [
            'disk' => $this->avatarDisk(),
            'bucket' => trim((string) config('filesystems.disks.supabase.bucket', '')) ?: null,
            'region' => trim((string) config('filesystems.disks.supabase.region', '')) ?: null,
            'endpoint_host' => $endpoint !== '' ? parse_url($endpoint, PHP_URL_HOST) : null,
            'endpoint_path' => $endpoint !== '' ? parse_url($endpoint, PHP_URL_PATH) : null,
            'url_host' => $url !== '' ? parse_url($url, PHP_URL_HOST) : null,
            'url_path' => $url !== '' ? parse_url($url, PHP_URL_PATH) : null,
            'key_configured' => trim((string) config('filesystems.disks.supabase.key', '')) !== '',
            'secret_configured' => trim((string) config('filesystems.disks.supabase.secret', '')) !== '',
            'url_configured' => $url !== '',
            'endpoint_configured' => $endpoint !== '',
        ];
    }

    private function avatarStorageDiskForValue(?string $avatar): string
    {
        $value = trim((string) $avatar);

        if ($value !== '' && Str::contains($value, '/storage/v1/object/public/')) {
            return 'supabase';
        }

        return 'public';
    }

    private function avatarUrl(string $disk, string $path): string
    {
        if ($disk === 'supabase') {
            $baseUrl = trim((string) config('filesystems.disks.supabase.url', ''));
            if ($baseUrl !== '') {
                return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
            }
        }

        return Storage::disk($disk)->url($path);
    }

    private function avatarDiskPath(?string $avatar, string $disk): ?string
    {
        $value = trim((string) $avatar);

        if ($value === '') {
            return null;
        }

        if ($disk === 'supabase') {
            $bucket = trim((string) config('filesystems.disks.supabase.bucket', ''));
            $marker = '/storage/v1/object/public/'.($bucket !== '' ? $bucket.'/' : '');

            if (Str::startsWith($value, ['http://', 'https://'])) {
                $parsedPath = parse_url($value, PHP_URL_PATH);

                if (! is_string($parsedPath)) {
                    return null;
                }

                $value = $parsedPath;
            }

            if ($bucket !== '' && Str::contains($value, $marker)) {
                return Str::after($value, $marker);
            }

            if (Str::startsWith($value, 'avatars/')) {
                return $value;
            }

            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            $parsedPath = parse_url($value, PHP_URL_PATH);

            if (! is_string($parsedPath) || ! Str::startsWith($parsedPath, '/storage/')) {
                return null;
            }

            $value = $parsedPath;
        }

        if (Str::startsWith($value, '/storage/')) {
            return Str::after($value, '/storage/');
        }

        if (Str::startsWith($value, 'storage/')) {
            return Str::after($value, 'storage/');
        }

        return $value;
    }
}
