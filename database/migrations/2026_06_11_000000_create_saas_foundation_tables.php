<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────
        // 1. tenants — perusahaan/agency yang berlangganan
        // ──────────────────────────────────────────────
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name', 200);
                $table->string('slug', 100)->unique();
                $table->string('domain')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->string('logo_url', 500)->nullable();
                $table->string('status', 20)->default('active'); // active, suspended, canceled
                $table->decimal('target_revenue', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // ──────────────────────────────────────────────
        // 2. plans — paket langganan
        // ──────────────────────────────────────────────
        if (! Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 50)->unique();
                $table->text('description')->nullable();
                $table->decimal('price_monthly', 15, 2)->default(0);
                $table->decimal('price_yearly', 15, 2)->default(0);
                $table->integer('max_pools')->default(1);
                $table->integer('max_users')->default(1);
                $table->integer('max_armadas')->default(1);
                $table->integer('max_routes')->default(0); // 0 = unlimited
                $table->integer('max_drivers')->default(1);
                $table->integer('max_charters_per_month')->default(0); // 0 = unlimited
                $table->boolean('has_seat_map')->default(false);
                $table->boolean('has_pdf_export')->default(false);
                $table->boolean('has_csv_export')->default(false);
                $table->boolean('has_online_booking')->default(false);
                $table->boolean('has_analytics')->default(false);
                $table->boolean('has_whatsapp_api')->default(false);
                $table->boolean('has_custom_domain')->default(false);
                $table->boolean('has_custom_roles')->default(false);
                $table->string('support_priority', 20)->default('standard');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ──────────────────────────────────────────────
        // 3. feature_gates — daftar fitur yang bisa di-gate
        // ──────────────────────────────────────────────
        if (! Schema::hasTable('feature_gates')) {
            Schema::create('feature_gates', function (Blueprint $table) {
                $table->id();
                $table->string('feature_key', 100)->unique();
                $table->string('feature_name', 200);
                $table->string('feature_group', 80);
                $table->text('description')->nullable();
                $table->boolean('is_core')->default(false); // true = selalu include di semua plan
                $table->timestamps();
            });
        }

        // ──────────────────────────────────────────────
        // 4. plan_feature — mapping fitur ke paket
        // ──────────────────────────────────────────────
        if (! Schema::hasTable('plan_feature')) {
            Schema::create('plan_feature', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plan_id');
                $table->unsignedBigInteger('feature_gate_id');
                $table->integer('max_value')->nullable(); // NULL = unlimited, 0 = disabled, N = batas numerik
                $table->timestamps();
                $table->unique(['plan_id', 'feature_gate_id'], 'uniq_plan_feature_pair');
                $table->index('feature_gate_id', 'idx_plan_feature_gate_id');
            });
        }

        // ──────────────────────────────────────────────
        // 5. subscriptions — langganan per tenant
        // ──────────────────────────────────────────────
        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('plan_id');
                $table->string('status', 20)->default('trial'); // trial, active, past_due, suspended, canceled, expired
                $table->date('trial_ends_at')->nullable();
                $table->date('starts_at')->nullable();
                $table->date('ends_at')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->string('billing_interval', 10)->default('monthly'); // monthly, yearly
                $table->integer('grace_period_days')->default(7);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('tenant_id', 'idx_subscriptions_tenant_id');
            });
        }

        // ──────────────────────────────────────────────
        // 6. invoice_subscriptions — invoice langganan
        // ──────────────────────────────────────────────
        if (! Schema::hasTable('invoice_subscriptions')) {
            Schema::create('invoice_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('subscription_id');
                $table->string('invoice_number', 50)->unique();
                $table->decimal('amount', 15, 2);
                $table->string('status', 20)->default('pending'); // pending, paid, overdue, failed, refunded
                $table->date('due_date');
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method', 50)->nullable();
                $table->text('payment_proof')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('tenant_id', 'idx_invoice_subs_tenant_id');
                $table->index('subscription_id', 'idx_invoice_subs_subscription_id');
            });
        }

        // ──────────────────────────────────────────────
        // Seed default data
        // ──────────────────────────────────────────────
        $this->seedDefaults();
    }

    private function seedDefaults(): void
    {
        // --- Default Tenant ---
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => 'OptiBus Default',
            'slug' => 'qbus-default',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // --- Plans ---
        $starterId = DB::table('plans')->insertGetId([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Untuk driver individu — 1 armada, 2 rute, 1 user.',
            'price_monthly' => 49000,
            'price_yearly' => 490000,
            'max_pools' => 1,
            'max_users' => 1,
            'max_armadas' => 1,
            'max_routes' => 2,
            'max_drivers' => 1,
            'max_charters_per_month' => 3,
            'has_seat_map' => false,
            'has_pdf_export' => false,
            'has_csv_export' => false,
            'has_online_booking' => false,
            'has_analytics' => false,
            'has_whatsapp_api' => false,
            'has_custom_domain' => false,
            'has_custom_roles' => false,
            'support_priority' => 'standard',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $proId = DB::table('plans')->insertGetId([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Untuk driver aktif & travel kecil — 3 armada, unlimited rute, PDF/CSV export, booking online.',
            'price_monthly' => 99000,
            'price_yearly' => 990000,
            'max_pools' => 2,
            'max_users' => 3,
            'max_armadas' => 3,
            'max_routes' => 0, // unlimited
            'max_drivers' => 3,
            'max_charters_per_month' => 0, // unlimited
            'has_seat_map' => true,
            'has_pdf_export' => true,
            'has_csv_export' => true,
            'has_online_booking' => true,
            'has_analytics' => true,
            'has_whatsapp_api' => false,
            'has_custom_domain' => false,
            'has_custom_roles' => false,
            'support_priority' => 'standard',
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fleetId = DB::table('plans')->insertGetId([
            'name' => 'Fleet',
            'slug' => 'fleet',
            'description' => 'Untuk pemilik armada — 10+ armada, multi-pool, multi-role, laporan per driver & kendaraan.',
            'price_monthly' => 199000,
            'price_yearly' => 1990000,
            'max_pools' => 5,
            'max_users' => 10,
            'max_armadas' => 10,
            'max_routes' => 0, // unlimited
            'max_drivers' => 15,
            'max_charters_per_month' => 0, // unlimited
            'has_seat_map' => true,
            'has_pdf_export' => true,
            'has_csv_export' => true,
            'has_online_booking' => true,
            'has_analytics' => true,
            'has_whatsapp_api' => false,
            'has_custom_domain' => false,
            'has_custom_roles' => true,
            'support_priority' => 'priority',
            'sort_order' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // --- Feature Gates ---
        $featureGates = [
            // Core (semua plan)
            ['feature_key' => 'dashboard.view', 'feature_name' => 'Dashboard Revenue', 'feature_group' => 'Dashboard', 'is_core' => true],
            ['feature_key' => 'booking.basic', 'feature_name' => 'Booking Seat Basic', 'feature_group' => 'Booking', 'is_core' => true],
            ['feature_key' => 'luggage.basic', 'feature_name' => 'Booking Bagasi + Tracking', 'feature_group' => 'Bagasi', 'is_core' => true],
            ['feature_key' => 'payment.unified', 'feature_name' => 'Pembayaran Terpadu', 'feature_group' => 'Keuangan', 'is_core' => true],
            ['feature_key' => 'customer.basic', 'feature_name' => 'Database Pelanggan', 'feature_group' => 'Customer', 'is_core' => true],
            ['feature_key' => 'activity.logs', 'feature_name' => 'Log Aktivitas', 'feature_group' => 'Audit', 'is_core' => true],

            // Pro
            ['feature_key' => 'booking.seat_map', 'feature_name' => 'Seat Map Visual', 'feature_group' => 'Booking', 'is_core' => false],
            ['feature_key' => 'booking.manifest', 'feature_name' => 'Manifest Perjalanan', 'feature_group' => 'Booking', 'is_core' => false],
            ['feature_key' => 'booking.ticket_print', 'feature_name' => 'Cetak Tiket PDF', 'feature_group' => 'Booking', 'is_core' => false],
            ['feature_key' => 'charter.full', 'feature_name' => 'Carter Full Lifecycle', 'feature_group' => 'Carter', 'is_core' => false],
            ['feature_key' => 'charter.invoice_print', 'feature_name' => 'Cetak Invoice Carter PDF', 'feature_group' => 'Carter', 'is_core' => false],
            ['feature_key' => 'luggage.resi_print', 'feature_name' => 'Cetak Resi Bagasi PDF', 'feature_group' => 'Bagasi', 'is_core' => false],
            ['feature_key' => 'report.export_csv', 'feature_name' => 'Export CSV', 'feature_group' => 'Laporan', 'is_core' => false],
            ['feature_key' => 'report.export_pdf', 'feature_name' => 'Export PDF', 'feature_group' => 'Laporan', 'is_core' => false],
            ['feature_key' => 'saas.online_booking', 'feature_name' => 'Halaman Booking Online', 'feature_group' => 'SaaS', 'is_core' => false],

            // Fleet
            ['feature_key' => 'dashboard.analytics', 'feature_name' => 'Analytics Lanjutan', 'feature_group' => 'Dashboard', 'is_core' => false],
            ['feature_key' => 'tenant.multiple_pools', 'feature_name' => 'Multi Pool/Cabang', 'feature_group' => 'Tenant', 'is_core' => false],
            ['feature_key' => 'role.custom', 'feature_name' => 'Custom Role', 'feature_group' => 'Akses', 'is_core' => false],

            // Add-ons (tidak include di paket manapun secara default)
            ['feature_key' => 'tenant.custom_domain', 'feature_name' => 'Custom Domain', 'feature_group' => 'Tenant', 'is_core' => false],
            ['feature_key' => 'saas.whatsapp_api', 'feature_name' => 'WhatsApp API', 'feature_group' => 'SaaS', 'is_core' => false],
        ];

        $featureGateIds = [];
        foreach ($featureGates as $gate) {
            $featureGateIds[$gate['feature_key']] = DB::table('feature_gates')->insertGetId([
                'feature_key' => $gate['feature_key'],
                'feature_name' => $gate['feature_name'],
                'feature_group' => $gate['feature_group'],
                'description' => null,
                'is_core' => $gate['is_core'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- Plan-Feature Mappings ---
        // Helper: semua core features
        $coreKeys = ['dashboard.view', 'booking.basic', 'luggage.basic', 'payment.unified', 'customer.basic', 'activity.logs'];

        // Helper: semua Pro features
        $proKeys = ['booking.seat_map', 'booking.manifest', 'booking.ticket_print', 'charter.full', 'charter.invoice_print', 'luggage.resi_print', 'report.export_csv', 'report.export_pdf', 'saas.online_booking'];

        // Helper: semua Fleet features
        $fleetKeys = ['dashboard.analytics', 'tenant.multiple_pools', 'role.custom'];

        foreach ($coreKeys as $key) {
            foreach ([$starterId, $proId, $fleetId] as $planId) {
                DB::table('plan_feature')->insert([
                    'plan_id' => $planId,
                    'feature_gate_id' => $featureGateIds[$key],
                    'max_value' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($proKeys as $key) {
            DB::table('plan_feature')->insert([
                'plan_id' => $starterId,
                'feature_gate_id' => $featureGateIds[$key],
                'max_value' => 0, // disabled untuk Starter
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ([$proId, $fleetId] as $planId) {
                DB::table('plan_feature')->insert([
                    'plan_id' => $planId,
                    'feature_gate_id' => $featureGateIds[$key],
                    'max_value' => null, // unlimited
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($fleetKeys as $key) {
            DB::table('plan_feature')->insert([
                'plan_id' => $starterId,
                'feature_gate_id' => $featureGateIds[$key],
                'max_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('plan_feature')->insert([
                'plan_id' => $proId,
                'feature_gate_id' => $featureGateIds[$key],
                'max_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('plan_feature')->insert([
                'plan_id' => $fleetId,
                'feature_gate_id' => $featureGateIds[$key],
                'max_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add-ons: disabled untuk semua paket secara default (harus dibeli terpisah)
        foreach (['tenant.custom_domain', 'saas.whatsapp_api'] as $addonKey) {
            foreach ([$starterId, $proId, $fleetId] as $planId) {
                DB::table('plan_feature')->insert([
                    'plan_id' => $planId,
                    'feature_gate_id' => $featureGateIds[$addonKey],
                    'max_value' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // --- Default subscription for default tenant (active, Fleet plan) ---
        DB::table('subscriptions')->insert([
            'tenant_id' => $tenantId,
            'plan_id' => $fleetId,
            'status' => 'active',
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'billing_interval' => 'monthly',
            'grace_period_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_subscriptions');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plan_feature');
        Schema::dropIfExists('feature_gates');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('tenants');
    }
};
