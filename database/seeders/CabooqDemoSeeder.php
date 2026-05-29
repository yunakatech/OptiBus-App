<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CabooqDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@cabooq.local'],
            [
                'name' => 'CabooQ Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'ops@cabooq.local'],
            [
                'name' => 'CabooQ Ops',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        DB::table('routes')->updateOrInsert(
            ['name' => 'PINRANG - MAKASSAR'],
            ['origin' => 'PINRANG', 'destination' => 'MAKASSAR', 'created_at' => now()]
        );

        DB::table('routes')->updateOrInsert(
            ['name' => 'MAKASSAR - PINRANG'],
            ['origin' => 'MAKASSAR', 'destination' => 'PINRANG', 'created_at' => now()]
        );

        DB::table('units')->updateOrInsert(
            ['nopol' => 'DD 1234 XX'],
            ['merek' => 'Isuzu', 'type' => 'Elf', 'kapasitas' => 12, 'status' => 'Aktif', 'created_at' => now()]
        );
        DB::table('units')->updateOrInsert(
            ['nopol' => 'DD 5678 YY'],
            ['merek' => 'Toyota', 'type' => 'Hiace', 'kapasitas' => 14, 'status' => 'Aktif', 'created_at' => now()]
        );

        $resolvedRouteId = (int) (DB::table('routes')->where('name', 'PINRANG - MAKASSAR')->value('id') ?? 0);
        $unitId = (int) (DB::table('units')->where('nopol', 'DD 1234 XX')->value('id') ?? 0);
        $dow = now()->dayOfWeek;

        DB::table('schedules')->updateOrInsert(
            ['rute' => 'PINRANG - MAKASSAR', 'dow' => $dow, 'jam' => '08:00:00'],
            ['units' => 1, 'unit_label' => 'Reguler', 'unit_id' => $unitId > 0 ? $unitId : null, 'created_at' => now()]
        );
        DB::table('schedules')->updateOrInsert(
            ['rute' => 'PINRANG - MAKASSAR', 'dow' => $dow, 'jam' => '14:00:00'],
            ['units' => 1, 'unit_label' => 'Reguler', 'unit_id' => $unitId > 0 ? $unitId : null, 'created_at' => now()]
        );

        if (DB::getSchemaBuilder()->hasTable('schedule_units')) {
            $scheduleRows = DB::table('schedules')
                ->where('rute', 'PINRANG - MAKASSAR')
                ->where('dow', $dow)
                ->whereIn('jam', ['08:00:00', '14:00:00'])
                ->get(['id', 'units', 'unit_label', 'unit_id']);

            foreach ($scheduleRows as $scheduleRow) {
                $scheduleId = (int) ($scheduleRow->id ?? 0);
                if ($scheduleId <= 0) {
                    continue;
                }

                DB::table('schedule_units')->where('schedule_id', $scheduleId)->delete();
                $unitsCount = max(1, (int) ($scheduleRow->units ?? 1));
                $label = trim((string) ($scheduleRow->unit_label ?? ''));
                $rows = [];
                for ($i = 1; $i <= $unitsCount; $i += 1) {
                    $rows[] = [
                        'schedule_id' => $scheduleId,
                        'unit_no' => $i,
                        'label' => $label !== '' ? $label : "Unit {$i}",
                        'unit_id' => $scheduleRow->unit_id !== null ? (int) $scheduleRow->unit_id : null,
                        'created_at' => now(),
                    ];
                }
                DB::table('schedule_units')->insert($rows);
            }
        }

        DB::table('segments')->updateOrInsert(
            ['rute' => 'PINRANG - MAKASSAR', 'harga' => 150000],
            [
                'route_id' => $resolvedRouteId > 0 ? $resolvedRouteId : 0,
                'origin' => 'PINRANG',
                'destination' => 'MAKASSAR',
                'created_at' => now(),
            ]
        );

        DB::table('drivers')->updateOrInsert(
            ['nama' => 'ANDI SOPIR'],
            ['phone' => '081234567890', 'unit_id' => $unitId > 0 ? $unitId : null, 'created_at' => now()]
        );

        DB::table('luggage_services')->updateOrInsert(
            ['name' => 'Dokumen'],
            ['created_at' => now()]
        );
        DB::table('luggage_services')->updateOrInsert(
            ['name' => 'Paket Sedang'],
            ['created_at' => now()]
        );

        DB::table('master_carter')->updateOrInsert(
            ['name' => 'PINRANG - MAKASSAR'],
            [
                'origin' => 'PINRANG',
                'destination' => 'MAKASSAR',
                'duration' => 'Regular',
                'rental_price' => 2500000,
                'bop_price' => 200000,
                'notes' => 'Demo charter route',
                'created_at' => now(),
            ]
        );

    }
}
