<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

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
            $this->replaceAvatar($user, $request->file('avatar'));
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
        $path = $file->store('avatars', 'public');

        $user->avatar = Storage::disk('public')->url($path);

        $this->deleteStoredAvatar($previousAvatar, $user->avatar);
    }

    private function deleteStoredAvatar(?string $avatar, ?string $except = null): void
    {
        $path = $this->avatarDiskPath($avatar);

        if ($path === null) {
            return;
        }

        if ($except !== null && $this->avatarDiskPath($except) === $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function avatarDiskPath(?string $avatar): ?string
    {
        $value = trim((string) $avatar);

        if ($value === '') {
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
