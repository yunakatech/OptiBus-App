<?php

use App\Support\BookingCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'departure_code')) {
                    $table->string('departure_code', 60)->nullable()->after('unit');
                }

                if (! Schema::hasColumn('bookings', 'ticket_code')) {
                    $table->string('ticket_code', 60)->nullable()->after('departure_code');
                }
            });

            DB::table('bookings')
                ->orderBy('id')
                ->select(['id', 'rute', 'tanggal', 'jam', 'unit', 'departure_code', 'ticket_code'])
                ->chunk(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $departureCode = trim((string) ($row->departure_code ?? ''));
                        $ticketCode = trim((string) ($row->ticket_code ?? ''));

                        DB::table('bookings')
                            ->where('id', (int) $row->id)
                            ->update([
                                'departure_code' => $departureCode !== ''
                                    ? $departureCode
                                    : BookingCode::departureCode(
                                        (string) ($row->tanggal ?? ''),
                                        substr((string) ($row->jam ?? ''), 0, 5),
                                        (int) ($row->unit ?? 1),
                                        (string) ($row->rute ?? '')
                                    ),
                                'ticket_code' => $ticketCode !== ''
                                    ? $ticketCode
                                    : BookingCode::ticketCode(
                                        (int) $row->id,
                                        (string) ($row->tanggal ?? '')
                                    ),
                            ]);
                    }
                });

            DB::statement('CREATE INDEX IF NOT EXISTS idx_bookings_departure_code ON bookings (departure_code)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_bookings_ticket_code ON bookings (ticket_code)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'ticket_code')) {
                    $table->dropColumn('ticket_code');
                }

                if (Schema::hasColumn('bookings', 'departure_code')) {
                    $table->dropColumn('departure_code');
                }
            });
        }
    }
};
