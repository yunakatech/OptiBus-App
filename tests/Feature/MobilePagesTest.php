<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MobilePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_processing_timing_is_opt_in(): void
    {
        config(['ui.navigation_metrics' => false]);
        $this->get('/')->assertHeaderMissing('Server-Timing');
        config(['ui.navigation_metrics' => true]);
        $response = $this->get('/')->assertOk();
        $this->assertMatchesRegularExpression('/app;dur=\d+(\.\d+)?/', $response->headers->get('Server-Timing'));
    }

    public function test_mobile_page_families_return_valid_inertia_payloads(): void
    {
        $fixtures = [];
        foreach (['/', '/pricing', '/login', '/register'] as $path) {
            $fixtures[$path] = $this->pagePayload($path);
        }
        $this->get('/forgot-password')->assertRedirect('/login');
        $tenant = $this->defaultTestTenantId();
        $user = $this->actingAsSuperAdminWithTenantContext($tenant);
        $pool = DB::table('pools')->insertGetId(['tenant_id' => $tenant, 'name' => 'Pool Demo', 'code' => 'MOB', 'status' => 'active', 'created_at' => now()]);
        $this->withSession(['active_tenant_id' => $tenant, 'active_pool_id' => $pool, 'auth.password_confirmed_at' => time()]);
        $route = DB::table('routes')->insertGetId(['tenant_id' => $tenant, 'name' => 'MAKASSAR - PINRANG', 'origin' => 'MAKASSAR', 'destination' => 'PINRANG', 'created_at' => now()]);
        DB::table('pool_route')->insert(['pool_id' => $pool, 'route_id' => $route, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('tenants')->where('id', $tenant)->update(['public_booking_enabled' => true]);
        DB::table('bookings')->insert(['tenant_id' => $tenant, 'rute' => 'MAKASSAR - PINRANG', 'tanggal' => now()->toDateString(), 'jam' => '09:00:00', 'unit' => 1, 'seat' => '1', 'name' => 'Penumpang Demo', 'phone' => '081200000001', 'status' => 'active', 'pembayaran' => 'Belum Lunas', 'price' => 150000, 'created_at' => now()]);
        $driver = DB::table('drivers')->insertGetId(['tenant_id' => $tenant, 'nama' => 'Driver Demo', 'phone' => '081200000002', 'created_at' => now()]);
        $armada = DB::table('armadas')->insertGetId(['tenant_id' => $tenant, 'nopol' => 'DD 1234 ZZ', 'created_at' => now()]);
        $charter = DB::table('charters')->insertGetId(['tenant_id' => $tenant, 'name' => 'Carter Demo', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(), 'price' => 1500000, 'created_at' => now()]);
        $key = md5('MAKASSAR TO PINRANG|'.now()->toDateString().'|09:00|1');

        foreach ([
            '/dashboard', '/menu', '/bookings', '/bookings/detail/'.$key, '/booking-console',
            '/booking-requests', '/payments', '/charters', '/charters/form', '/charters/view/'.$charter,
            '/luggages', '/luggages/form', '/settings/customers', '/settings/customer-bagasi',
            '/settings/customer-charter', '/settings/rute-carter', '/settings/rute-induk',
            '/settings/jadwal', '/settings/segments', '/settings/driver', '/settings/armada',
            '/settings/armada/view/'.$armada, '/settings/kategori-armada', '/settings/pool',
            '/settings/tarif-bagasi', '/settings/logs', '/settings/users', '/settings/roles',
            '/settings/booking-online', '/subscription', '/settings/profile', '/settings/security',
            '/settings/appearance', '/platform/dashboard', '/platform/saas', '/book/qbus-default',
        ] as $path) {
            $fixtures[$path] = $this->pagePayload($path);
        }
        $this->withSession(['active_tenant_id' => null, 'active_pool_id' => null]);
        foreach (['/platform/dashboard', '/platform/saas'] as $path) {
            $fixtures[$path] = $this->pagePayload($path);
        }
        $this->actingAs(User::factory()->create(['is_super_admin' => false, 'tenant_id' => null]));
        $fixtures['/onboarding'] = $this->pagePayload('/onboarding');

        // Optional local browser fixtures contain only the isolated test database.
        $output = getenv('MOBILE_PREVIEW_OUTPUT');
        if (is_string($output) && $output !== '') {
            if (! is_dir($output)) {
                mkdir($output, 0777, true);
            }
            file_put_contents($output.'/pages.json', json_encode($fixtures, JSON_THROW_ON_ERROR));
        }
    }

    private function pagePayload(string $path): array
    {
        $response = $this->get($path);
        $this->assertSame(200, $response->status(), $path.' → '.$response->headers->get('Location'));
        $page = $response->viewData('page');
        $this->assertIsString($page['component'] ?? null, $path);
        $deferred = array_merge(...array_values($page['deferredProps'] ?? [[]]));
        if ($deferred !== []) {
            $keys = array_unique([...array_keys($page['props']), ...$deferred]);
            $response = $this->get($path, ['X-Inertia' => 'true',
                'X-Inertia-Version' => $page['version'] ?? '',
                'X-Inertia-Partial-Component' => $page['component'], 'X-Inertia-Partial-Data' => implode(',', $keys)]);
            $response->assertOk();
            $page['props'] = array_replace($page['props'], $response->json('props'));
            unset($page['deferredProps']);
        }
        // The preview renderer serves this payload under its local origin.
        $page['url'] = $path;

        return $page;
    }
}
