<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentSettings
{
    /**
     * @return array{
     *     qris: array{enabled: bool, merchant_name: string, image_url: string, image_path: string, has_image: bool, storage_status: string, note: string},
     *     bank_transfer: array{enabled: bool, accounts: array<int, array{bank_name: string, account_number: string, account_holder: string, note: string}>},
     *     upload_max_kb: int
     * }
     */
    public static function all(bool $includeEmptyBankAccounts = false): array
    {
        $qrisPath = self::get('payment.qris_image_path', '');
        if ($qrisPath === '') {
            $qrisPath = (string) config('payment.qris.image_path', 'images/qris.png');
        }

        $qrisImage = self::imageState($qrisPath);
        $accounts = self::bankAccounts();
        if (! $includeEmptyBankAccounts) {
            $accounts = array_values(array_filter($accounts, static fn (array $account): bool => $account['bank_name'] !== '' && $account['account_number'] !== ''));
        }

        return [
            'qris' => [
                'enabled' => (bool) config('payment.qris.enabled', true),
                'merchant_name' => self::get('payment.qris_merchant_name', config('payment.qris.merchant_name', 'OptiBus Indonesia')),
                'image_url' => $qrisImage['url'],
                'image_path' => $qrisPath,
                'has_image' => $qrisImage['has_image'],
                'storage_status' => $qrisImage['storage_status'],
                'note' => self::get('payment.qris_note', config('payment.qris.note', '')),
            ],
            'bank_transfer' => [
                'enabled' => (bool) config('payment.bank_transfer.enabled', true),
                'accounts' => $accounts,
            ],
            'upload_max_kb' => (int) config('payment.upload.max_size_kb', 2048),
        ];
    }

    public static function saveQrisImage(UploadedFile $file): string
    {
        $path = $file->storeAs('payment', 'qris.png', 'public');
        self::save('payment.qris_image_path', $path);

        return $path;
    }

    public static function save(string $key, string $value): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public static function get(string $key, string $default = ''): string
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        return (string) (DB::table('settings')->where('key', $key)->value('value') ?? $default);
    }

    /**
     * @return array<int, array{bank_name: string, account_number: string, account_holder: string, note: string}>
     */
    private static function bankAccounts(): array
    {
        return collect([1, 2, 3])->map(static function (int $i): array {
            return [
                'bank_name' => self::get("payment.bank_{$i}_name", config('payment.bank_transfer.accounts.'.($i - 1).'.bank_name', '')),
                'account_number' => self::get("payment.bank_{$i}_number", config('payment.bank_transfer.accounts.'.($i - 1).'.account_number', '')),
                'account_holder' => self::get("payment.bank_{$i}_holder", config('payment.bank_transfer.accounts.'.($i - 1).'.account_holder', '')),
                'note' => 'Transfer sesuai nominal paket dan upload bukti.',
            ];
        })->all();
    }

    /**
     * @return array{url: string, has_image: bool, storage_status: string}
     */
    private static function imageState(string $path): array
    {
        $path = trim($path);
        if ($path === '') {
            return ['url' => '', 'has_image' => false, 'storage_status' => 'missing_file'];
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return ['url' => $path, 'has_image' => true, 'storage_status' => 'external'];
        }

        $diskPath = Str::startsWith($path, 'storage/') ? Str::after($path, 'storage/') : $path;
        if (Storage::disk('public')->exists($diskPath)) {
            return [
                'url' => Storage::disk('public')->url($diskPath),
                'has_image' => true,
                'storage_status' => file_exists(public_path('storage')) ? 'ready' : 'missing_link',
            ];
        }

        if (file_exists(public_path($path))) {
            return ['url' => asset($path), 'has_image' => true, 'storage_status' => 'ready'];
        }

        return ['url' => asset($path), 'has_image' => false, 'storage_status' => 'missing_file'];
    }
}
