<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_payment_proof_moves_invoice_to_verification(): void
    {
        Storage::fake('public');
        [$user, $invoiceId] = $this->tenantWithPendingInvoice();

        $this->actingAs($user)
            ->postJson(route('api.subscription.upload-proof', ['invoiceId' => $invoiceId]), [
                'proof_file' => UploadedFile::fake()->image('proof.jpg'),
                'payment_method' => 'Transfer BCA',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoice_subscriptions', [
            'id' => $invoiceId,
            'status' => 'verification',
            'payment_method' => 'Transfer BCA',
        ]);
    }

    public function test_mark_invoice_paid_activates_pending_tenant_and_subscription(): void
    {
        [$tenantUser, $invoiceId, $tenantId, $subscriptionId] = $this->tenantWithPendingInvoice();
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->postJson(route('api.admin.invoices.mark-paid', ['id' => $invoiceId]), [
                'payment_method' => 'Manual Transfer',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoice_subscriptions', [
            'id' => $invoiceId,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscriptionId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('tenants', [
            'id' => $tenantId,
            'status' => 'active',
        ]);
        $this->assertSame($tenantId, (int) DB::table('users')->where('id', $tenantUser->id)->value('tenant_id'));
    }

    /**
     * @return array{0: User, 1: int, 2: int, 3: int}
     */
    private function tenantWithPendingInvoice(): array
    {
        $planId = (int) DB::table('plans')->where('slug', 'pro')->value('id');
        $tenantId = (int) DB::table('tenants')->insertGetId([
            'name' => 'Pending Travel',
            'slug' => 'pending-travel-'.uniqid(),
            'email' => 'pending@example.com',
            'status' => 'pending_payment',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subscriptionId = (int) DB::table('subscriptions')->insertGetId([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'pending_payment',
            'starts_at' => null,
            'ends_at' => null,
            'billing_interval' => 'monthly',
            'grace_period_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $invoiceId = (int) DB::table('invoice_subscriptions')->insertGetId([
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'amount' => 99000,
            'status' => 'pending',
            'due_date' => now()->addDay()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        DB::table('users')->where('id', $user->id)->update(['tenant_id' => $tenantId]);

        return [$user->fresh(), $invoiceId, $tenantId, $subscriptionId];
    }

    private function superAdmin(): User
    {
        AccessControl::syncDefaults();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $roleId = (int) DB::table('roles')->where('slug', 'super-admin')->value('id');
        DB::table('user_role')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }
}
