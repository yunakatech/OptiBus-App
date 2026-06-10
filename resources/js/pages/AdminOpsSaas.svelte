<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'SaaS Management',
                href: '/admin-ops/saas',
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import {
        Ban,
        Building2,
        CheckCircle2,
        ChevronDown,
        CreditCard,
        Edit3,
        FileText,
        MoreHorizontal,
        Package,
        Plus,
        RefreshCw,
        RotateCcw,
        Search,
        XCircle,
    } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type TabName = 'tenants' | 'subscriptions' | 'plans' | 'payment';

    // ─── Props ───
    let { tab: initialTab = null, summary = null, saasTablesReady = false }: {
        tab?: TabName | null;
        summary?: { tenant_count: number; active_subscription_count: number; plan_count: number } | null;
        saasTablesReady?: boolean;
    } = $props();

    // ─── State ───
    let activeTab = $state<TabName>('tenants');
    let busy = $state(false);
    let error = $state('');
    let message = $state('');
    let searchQuery = $state('');

    // Tenants state
    let tenants = $state<any[]>([]);
    let tenantPlans = $state<any[]>([]);
    let tenantPagination = $state<any>(null);
    let showTenantForm = $state(false);
    let editingTenant = $state<any>(null);
    let tenantForm = $state({ name: '', slug: '', email: '', phone: '', address: '', plan_id: 1, trial_days: 14 });

    // Subscriptions state
    let subscriptions = $state<any[]>([]);
    let subPlans = $state<any[]>([]);
    let subTenants = $state<any[]>([]);
    let subPagination = $state<any>(null);
    let statusFilter = $state('');
    let showSubForm = $state(false);
    let editingSub = $state<any>(null);
    let subForm = $state({ tenant_id: 0, plan_id: 1, status: 'active', starts_at: '', ends_at: '', notes: '' });

    // Plans state
    let plans = $state<any[]>([]);
    let editingPlan = $state<any>(null);

    // Payment settings state
    let paymentSettings = $state<any>(null);
    let payBusy = $state(false);
    let payMessage = $state('');

    async function loadPaymentSettings() {
        payBusy = true;
        const data = await apiFetch('/api/admin/payment-settings');
        if (data) { paymentSettings = data.settings; }
        payBusy = false;
    }

    async function savePaymentSettings(formEl: HTMLFormElement) {
        payBusy = true; payMessage = '';
        const formData = new FormData(formEl);
        const res = await fetch('/api/admin/payment-settings', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' },
            body: formData,
        });
        const data = await res.json();
        if (data?.success) { payMessage = data.message; paymentSettings = data.settings; }
        else { payMessage = data?.error || 'Gagal menyimpan'; }
        payBusy = false;
    }

    // ─── Init ───
    onMount(() => {
        activeTab = initialTab ?? 'tenants';
        if (activeTab === 'tenants') loadTenants();
        else if (activeTab === 'subscriptions') loadSubscriptions();
        else if (activeTab === 'plans') loadPlans();
        else if (activeTab === 'payment') loadPaymentSettings();
    });

    function switchTab(tab: TabName) {
        activeTab = tab;
        searchQuery = '';
        statusFilter = '';
        error = '';
        message = '';
        if (tab === 'tenants') loadTenants();
        else if (tab === 'subscriptions') loadSubscriptions();
        else if (tab === 'plans') loadPlans();
        else if (tab === 'payment') loadPaymentSettings();
    }

    // ─── API helpers ───
    async function apiFetch(url: string, options?: RequestInit) {
        busy = true;
        error = '';
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }, ...options });
            const json = await res.json();
            if (!json.success) { error = json.error || 'Request failed'; }
            return json;
        } catch (e: any) {
            error = e.message || 'Network error';
            return null;
        } finally {
            busy = false;
        }
    }

    // ─── Tenants ───
    async function loadTenants(pageNum = 1) {
        const params = new URLSearchParams({ paginate: '1', page: String(pageNum), per_page: '15' });
        if (searchQuery) params.set('q', searchQuery);
        const data = await apiFetch(`/api/admin/tenants?${params}`);
        if (data) {
            tenants = data.tenants ?? [];
            tenantPlans = data.plans ?? [];
            tenantPagination = data.pagination ?? null;
        }
    }

    function openTenantForm(tenant?: any) {
        if (tenant) {
            editingTenant = tenant;
            tenantForm = { name: tenant.name, slug: tenant.slug, email: tenant.email ?? '', phone: tenant.phone ?? '', address: tenant.address ?? '', plan_id: tenant.subscription_id ? 0 : 1, trial_days: 14 };
        } else {
            editingTenant = null;
            tenantForm = { name: '', slug: '', email: '', phone: '', address: '', plan_id: 1, trial_days: 14 };
        }
        showTenantForm = true;
    }

    async function saveTenant() {
        const body: any = { name: tenantForm.name, slug: tenantForm.slug, email: tenantForm.email || null, phone: tenantForm.phone || null, address: tenantForm.address || null };
        if (editingTenant) {
            body.id = editingTenant.id;
            body.status = editingTenant.status;
        } else {
            body.plan_id = tenantForm.plan_id;
            body.trial_days = tenantForm.trial_days;
        }
        const data = await apiFetch('/api/admin/tenants', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' }, body: JSON.stringify(body) });
        if (data?.success) {
            message = data.message;
            showTenantForm = false;
            loadTenants(tenantPagination?.page ?? 1);
        }
    }

    async function deleteTenant(id: number) {
        if (!confirm('Cancel tenant ini? Data akan disimpan 30 hari sebelum dihapus permanen.')) return;
        const data = await apiFetch(`/api/admin/tenants/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' } });
        if (data?.success) { message = data.message; loadTenants(tenantPagination?.page ?? 1); }
    }

    function toggleTenantStatus(tenant: any) {
        const newStatus = tenant.status === 'active' ? 'suspended' : 'active';
        apiFetch('/api/admin/tenants', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' }, body: JSON.stringify({ id: tenant.id, name: tenant.name, slug: tenant.slug, status: newStatus }) }).then(d => { if (d?.success) loadTenants(tenantPagination?.page ?? 1); });
    }

    // ─── Subscriptions ───
    async function loadSubscriptions(pageNum = 1) {
        const params = new URLSearchParams({ paginate: '1', page: String(pageNum), per_page: '15' });
        if (statusFilter) params.set('status', statusFilter);
        if (searchQuery) params.set('q', searchQuery);
        const data = await apiFetch(`/api/admin/subscriptions?${params}`);
        if (data) {
            subscriptions = data.subscriptions ?? [];
            subPlans = data.plans ?? [];
            subTenants = data.tenants ?? [];
            subPagination = data.pagination ?? null;
        }
    }

    function openSubForm(sub?: any) {
        if (sub) {
            editingSub = sub;
            subForm = { tenant_id: sub.tenant_id, plan_id: sub.plan_id, status: sub.status, starts_at: sub.starts_at ?? '', ends_at: sub.ends_at ?? '', notes: sub.notes ?? '' };
        } else {
            editingSub = null;
            subForm = { tenant_id: 0, plan_id: 1, status: 'active', starts_at: '', ends_at: '', notes: '' };
        }
        showSubForm = true;
    }

    async function saveSubscription() {
        const body: any = { plan_id: subForm.plan_id, status: subForm.status, starts_at: subForm.starts_at || null, ends_at: subForm.ends_at || null, notes: subForm.notes || null };
        if (editingSub) { body.id = editingSub.id; } else { body.tenant_id = subForm.tenant_id; }
        const data = await apiFetch('/api/admin/subscriptions', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' }, body: JSON.stringify(body) });
        if (data?.success) { message = data.message; showSubForm = false; loadSubscriptions(subPagination?.page ?? 1); }
    }

    async function quickUpdateSub(id: number, updates: Record<string, any>) {
        const data = await apiFetch('/api/admin/subscriptions', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' }, body: JSON.stringify({ id, ...updates }) });
        if (data?.success) { message = data.message; loadSubscriptions(subPagination?.page ?? 1); }
    }

    // ─── Plans ───
    async function loadPlans() {
        const data = await apiFetch('/api/admin/plans');
        if (data) { plans = data.plans ?? []; }
    }

    function startEditPlan(plan: any) {
        editingPlan = { ...plan, _features: plan.features ? [...plan.features] : [] };
    }

    async function savePlan() {
        if (!editingPlan) return;
        const body: any = {
            id: editingPlan.id,
            price_monthly: editingPlan.price_monthly,
            price_yearly: editingPlan.price_yearly,
            max_pools: editingPlan.max_pools,
            max_users: editingPlan.max_users,
            max_armadas: editingPlan.max_armadas,
            max_routes: editingPlan.max_routes,
            max_drivers: editingPlan.max_drivers,
            has_seat_map: editingPlan.has_seat_map,
            has_pdf_export: editingPlan.has_pdf_export,
            has_csv_export: editingPlan.has_csv_export,
            has_online_booking: editingPlan.has_online_booking,
            has_analytics: editingPlan.has_analytics,
            has_whatsapp_api: editingPlan.has_whatsapp_api,
            has_custom_domain: editingPlan.has_custom_domain,
            has_custom_roles: editingPlan.has_custom_roles,
            is_active: editingPlan.is_active,
        };
        const data = await apiFetch('/api/admin/plans', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' }, body: JSON.stringify(body) });
        if (data?.success) { message = data.message; editingPlan = null; loadPlans(); }
    }

    // ─── Helpers ───
    function statusBadge(status: string): { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string } {
        const map: Record<string, any> = { trial: { variant: 'secondary', label: 'Trial' }, active: { variant: 'default', label: 'Active' }, past_due: { variant: 'outline', label: 'Past Due' }, suspended: { variant: 'destructive', label: 'Suspended' }, canceled: { variant: 'destructive', label: 'Canceled' }, expired: { variant: 'outline', label: 'Expired' } };
        return map[status] ?? { variant: 'outline', label: status };
    }

    function formatRupiah(v: number): string {
        if (Math.abs(v) >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}M`;
        return `Rp ${(v / 1_000).toFixed(0)}K`;
    }

    function formatDate(d: string | null): string {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function slugFromName(name: string) {
        tenantForm.slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
</script>

<AppHead title="SaaS Management" />

<div class="space-y-6 pb-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">SaaS Management</h1>
            <p class="text-muted-foreground mt-1">Kelola tenant, subscription, dan paket langganan</p>
        </div>
        <div class="flex items-center gap-2">
            {#if summary}
                <Badge variant="outline">{summary.tenant_count} Tenants</Badge>
                <Badge variant="secondary">{summary.active_subscription_count} Active</Badge>
                <Badge variant="outline">{summary.plan_count} Plans</Badge>
            {/if}
        </div>
    </div>

    {#if !saasTablesReady}
        <Card>
            <CardContent class="py-12 text-center">
                <Package class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="text-lg font-semibold">Tabel SaaS Belum Tersedia</h3>
                <p class="text-muted-foreground mt-1">Jalankan migrasi database terlebih dahulu.</p>
            </CardContent>
        </Card>
    {:else}
        <!-- Flash Messages -->
        {#if error}
            <div class="bg-destructive/10 text-destructive rounded-lg px-4 py-3 text-sm">{error}</div>
        {/if}
        {#if message}
            <div class="bg-green-100 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center justify-between">
                {message}
                <button onclick={() => message = ''} class="text-green-600 hover:text-green-800">&times;</button>
            </div>
        {/if}

        <!-- Tab Bar -->
        <div class="flex gap-1 border-b">
            {#each [
                { key: 'tenants', label: 'Tenants', icon: Building2 },
                { key: 'subscriptions', label: 'Subscriptions', icon: CreditCard },
                { key: 'plans', label: 'Plans', icon: Package },
                { key: 'payment', label: 'Pembayaran', icon: CreditCard },
            ] as tab}
                <button
                    onclick={() => switchTab(tab.key as TabName)}
                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors
                        {activeTab === tab.key ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'}"
                >
                    <tab.icon class="h-4 w-4" />
                    {tab.label}
                </button>
            {/each}
        </div>

        <!-- ============ TENANTS TAB ============ -->
        {#if activeTab === 'tenants'}
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        class="pl-9"
                        placeholder="Cari tenant..."
                        bind:value={searchQuery}
                        onchange={() => loadTenants()}
                    />
                </div>
                <Button variant="outline" size="icon" onclick={() => { searchQuery = ''; loadTenants(); }}><RefreshCw class="h-4 w-4" /></Button>
                <Button onclick={() => openTenantForm()}><Plus class="h-4 w-4 mr-1" /> Tambah Tenant</Button>
            </div>

            <!-- Tenant Form Modal -->
            {#if showTenantForm}
                <Card>
                    <CardHeader>
                        <CardTitle>{editingTenant ? 'Edit Tenant' : 'Tambah Tenant Baru'}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>Nama</Label>
                                <Input bind:value={tenantForm.name} onchange={() => slugFromName(tenantForm.name)} placeholder="Nama perusahaan/agency" />
                            </div>
                            <div>
                                <Label>Slug</Label>
                                <Input bind:value={tenantForm.slug} placeholder="slug-unik" />
                            </div>
                            <div>
                                <Label>Email</Label>
                                <Input type="email" bind:value={tenantForm.email} placeholder="opsional" />
                            </div>
                            <div>
                                <Label>Phone</Label>
                                <Input bind:value={tenantForm.phone} placeholder="opsional" />
                            </div>
                            <div class="md:col-span-2">
                                <Label>Alamat</Label>
                                <Input bind:value={tenantForm.address} placeholder="opsional" />
                            </div>
                            {#if !editingTenant}
                                <div>
                                    <Label>Paket Awal</Label>
                                    <select bind:value={tenantForm.plan_id} class="w-full rounded-md border px-3 py-2 text-sm">
                                        {#each tenantPlans as plan}
                                            <option value={plan.id}>{plan.name} ({formatRupiah(plan.price_monthly)})</option>
                                        {/each}
                                    </select>
                                </div>
                                <div>
                                    <Label>Masa Trial (hari)</Label>
                                    <Input type="number" bind:value={tenantForm.trial_days} min="0" max="90" />
                                </div>
                            {/if}
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button variant="outline" onclick={() => showTenantForm = false}>Batal</Button>
                            <Button onclick={saveTenant} disabled={busy}>{busy ? 'Menyimpan...' : 'Simpan'}</Button>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Tenant Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Nama</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Paket</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground text-right">Users</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground text-right">Pools</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Berlangganan</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each tenants as t}
                                    <tr class="border-b last:border-0 hover:bg-muted/30">
                                        <td class="px-4 py-2.5">
                                            <div class="font-medium">{t.name}</div>
                                            <div class="text-xs text-muted-foreground">{t.slug}</div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <Badge variant="outline">{t.plan_name}</Badge>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <Badge variant={statusBadge(t.subscription_status).variant}>
                                                {statusBadge(t.subscription_status).label}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">{t.user_count}</td>
                                        <td class="px-4 py-2.5 text-right">{t.pool_count}</td>
                                        <td class="px-4 py-2.5 text-xs text-muted-foreground">
                                            {#if t.ends_at}
                                                {formatDate(t.ends_at)}
                                            {:else}
                                                —
                                            {/if}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger><Button variant="ghost" size="icon"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    <DropdownMenuItem onclick={() => openTenantForm(t)}><Edit3 class="h-4 w-4 mr-2" />Edit</DropdownMenuItem>
                                                    <DropdownMenuItem onclick={() => toggleTenantStatus(t)}>
                                                        {#if t.status === 'active'}<Ban class="h-4 w-4 mr-2" />Suspend{:else}<RotateCcw class="h-4 w-4 mr-2" />Re-activate{/if}
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onclick={() => deleteTenant(t.id)} class="text-destructive"><XCircle class="h-4 w-4 mr-2" />Cancel</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </td>
                                    </tr>
                                {:else}
                                    <tr><td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Belum ada tenant.</td></tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    {#if tenantPagination?.last_page > 1}
                        <div class="flex items-center justify-between px-4 py-3 border-t">
                            <span class="text-xs text-muted-foreground">Page {tenantPagination.page} of {tenantPagination.last_page} ({tenantPagination.total} total)</span>
                            <div class="flex gap-1">
                                <Button variant="outline" size="sm" disabled={tenantPagination.page <= 1} onclick={() => loadTenants(tenantPagination.page - 1)}>Prev</Button>
                                <Button variant="outline" size="sm" disabled={tenantPagination.page >= tenantPagination.last_page} onclick={() => loadTenants(tenantPagination.page + 1)}>Next</Button>
                            </div>
                        </div>
                    {/if}
                </CardContent>
            </Card>
        {/if}

        <!-- ============ SUBSCRIPTIONS TAB ============ -->
        {#if activeTab === 'subscriptions'}
            <div class="flex items-center gap-3 flex-wrap">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input class="pl-9" placeholder="Cari tenant..." bind:value={searchQuery} onchange={() => loadSubscriptions()} />
                </div>
                <select bind:value={statusFilter} onchange={() => loadSubscriptions()} class="rounded-md border px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="past_due">Past Due</option>
                    <option value="suspended">Suspended</option>
                    <option value="canceled">Canceled</option>
                </select>
                <Button variant="outline" size="icon" onclick={() => { searchQuery = ''; statusFilter = ''; loadSubscriptions(); }}><RefreshCw class="h-4 w-4" /></Button>
                <Button onclick={() => openSubForm()}><Plus class="h-4 w-4 mr-1" /> Tambah Subscription</Button>
            </div>

            <!-- Sub Form Modal -->
            {#if showSubForm}
                <Card>
                    <CardHeader><CardTitle>{editingSub ? 'Edit Subscription' : 'Tambah Subscription'}</CardTitle></CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {#if !editingSub}
                                <div>
                                    <Label>Tenant</Label>
                                    <select bind:value={subForm.tenant_id} class="w-full rounded-md border px-3 py-2 text-sm">
                                        <option value={0}>Pilih tenant...</option>
                                        {#each subTenants as t}
                                            <option value={t.id}>{t.name}</option>
                                        {/each}
                                    </select>
                                </div>
                            {/if}
                            <div>
                                <Label>Paket</Label>
                                <select bind:value={subForm.plan_id} class="w-full rounded-md border px-3 py-2 text-sm">
                                    {#each subPlans as plan}
                                        <option value={plan.id}>{plan.name} ({formatRupiah(plan.price_monthly)})</option>
                                    {/each}
                                </select>
                            </div>
                            <div>
                                <Label>Status</Label>
                                <select bind:value={subForm.status} class="w-full rounded-md border px-3 py-2 text-sm">
                                    <option value="trial">Trial</option>
                                    <option value="active">Active</option>
                                    <option value="past_due">Past Due</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                            </div>
                            <div>
                                <Label>Mulai</Label>
                                <Input type="date" bind:value={subForm.starts_at} />
                            </div>
                            <div>
                                <Label>Berakhir</Label>
                                <Input type="date" bind:value={subForm.ends_at} />
                            </div>
                            <div class="md:col-span-2">
                                <Label>Catatan</Label>
                                <Input bind:value={subForm.notes} placeholder="opsional" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button variant="outline" onclick={() => showSubForm = false}>Batal</Button>
                            <Button onclick={saveSubscription} disabled={busy}>{busy ? 'Menyimpan...' : 'Simpan'}</Button>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Tenant</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Paket</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Mulai</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Berakhir</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground text-right">Harga</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each subscriptions as s}
                                    <tr class="border-b last:border-0 hover:bg-muted/30">
                                        <td class="px-4 py-2.5 font-medium">{s.tenant_name}</td>
                                        <td class="px-4 py-2.5"><Badge variant="outline">{s.plan_name}</Badge></td>
                                        <td class="px-4 py-2.5"><Badge variant={statusBadge(s.status).variant}>{statusBadge(s.status).label}</Badge></td>
                                        <td class="px-4 py-2.5 text-xs">{formatDate(s.starts_at)}</td>
                                        <td class="px-4 py-2.5 text-xs">{formatDate(s.ends_at)}</td>
                                        <td class="px-4 py-2.5 text-right">{formatRupiah(s.price_monthly)}</td>
                                        <td class="px-4 py-2.5">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger><Button variant="ghost" size="icon"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    <DropdownMenuItem onclick={() => openSubForm(s)}><Edit3 class="h-4 w-4 mr-2" />Edit</DropdownMenuItem>
                                                    {#if s.status === 'active' || s.status === 'trial'}
                                                        <DropdownMenuItem onclick={() => quickUpdateSub(s.id, { status: 'suspended' })}><Ban class="h-4 w-4 mr-2" />Suspend</DropdownMenuItem>
                                                    {/if}
                                                    {#if s.status === 'suspended'}
                                                        <DropdownMenuItem onclick={() => quickUpdateSub(s.id, { status: 'active' })}><CheckCircle2 class="h-4 w-4 mr-2" />Activate</DropdownMenuItem>
                                                    {/if}
                                                    {#if s.status !== 'canceled'}
                                                        <DropdownMenuItem onclick={() => quickUpdateSub(s.id, { status: 'canceled' })} class="text-destructive"><XCircle class="h-4 w-4 mr-2" />Cancel</DropdownMenuItem>
                                                    {/if}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </td>
                                    </tr>
                                {:else}
                                    <tr><td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Belum ada subscription.</td></tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    {#if subPagination?.last_page > 1}
                        <div class="flex items-center justify-between px-4 py-3 border-t">
                            <span class="text-xs text-muted-foreground">Page {subPagination.page} of {subPagination.last_page}</span>
                            <div class="flex gap-1">
                                <Button variant="outline" size="sm" disabled={subPagination.page <= 1} onclick={() => loadSubscriptions(subPagination.page - 1)}>Prev</Button>
                                <Button variant="outline" size="sm" disabled={subPagination.page >= subPagination.last_page} onclick={() => loadSubscriptions(subPagination.page + 1)}>Next</Button>
                            </div>
                        </div>
                    {/if}
                </CardContent>
            </Card>
        {/if}

        <!-- ============ PLANS TAB ============ -->
        {#if activeTab === 'plans'}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {#each plans as plan}
                    {@const isEditing = editingPlan?.id === plan.id}
                    <Card class={plan.is_active ? '' : 'opacity-60'}>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>{plan.name}</CardTitle>
                                    <p class="text-xs text-muted-foreground mt-1">{plan.slug}</p>
                                </div>
                                {#if !plan.is_active}
                                    <Badge variant="outline">Inactive</Badge>
                                {/if}
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            {#if isEditing}
                                <!-- Edit Mode -->
                                <div class="space-y-2">
                                    <div>
                                        <Label class="text-xs">Harga Bulanan</Label>
                                        <Input type="number" bind:value={editingPlan.price_monthly} />
                                    </div>
                                    <div>
                                        <Label class="text-xs">Harga Tahunan</Label>
                                        <Input type="number" bind:value={editingPlan.price_yearly} />
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><Label class="text-xs">Max Pools</Label><Input type="number" bind:value={editingPlan.max_pools} /></div>
                                        <div><Label class="text-xs">Max Users</Label><Input type="number" bind:value={editingPlan.max_users} /></div>
                                        <div><Label class="text-xs">Max Armadas</Label><Input type="number" bind:value={editingPlan.max_armadas} /></div>
                                        <div><Label class="text-xs">Max Routes</Label><Input type="number" bind:value={editingPlan.max_routes} /></div>
                                        <div><Label class="text-xs">Max Drivers</Label><Input type="number" bind:value={editingPlan.max_drivers} /></div>
                                    </div>
                                    <div class="space-y-1 pt-2">
                                        {#each ['has_seat_map', 'has_pdf_export', 'has_csv_export', 'has_online_booking', 'has_analytics', 'has_custom_roles'] as key}
                                            <label class="flex items-center gap-2 text-sm">
                                                <input type="checkbox" checked={editingPlan[key]} onchange={() => editingPlan[key] = !editingPlan[key]} />
                                                {key.replace('has_', '').replace(/_/g, ' ')}
                                            </label>
                                        {/each}
                                    </div>
                                    <div class="flex gap-2 pt-2">
                                        <Button size="sm" variant="outline" onclick={() => editingPlan = null}>Batal</Button>
                                        <Button size="sm" onclick={savePlan} disabled={busy}>Simpan</Button>
                                    </div>
                                </div>
                            {:else}
                                <!-- View Mode -->
                                <div>
                                    <div class="text-2xl font-bold">{formatRupiah(plan.price_monthly)}<span class="text-sm font-normal text-muted-foreground">/bln</span></div>
                                    <div class="text-xs text-muted-foreground">{formatRupiah(plan.price_yearly)}/thn</div>
                                </div>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Armada</span><span class="font-medium">{plan.max_armadas || '∞'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Rute</span><span class="font-medium">{plan.max_routes || '∞'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Pool</span><span class="font-medium">{plan.max_pools || '∞'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max User</span><span class="font-medium">{plan.max_users || '∞'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Driver</span><span class="font-medium">{plan.max_drivers || '∞'}</span></div>
                                </div>
                                <hr class="my-2" />
                                <div class="space-y-1 text-xs">
                                    {#each plan.features as f}
                                        <div class="flex items-center gap-2">
                                            {#if f.max_value === null || f.max_value > 0}
                                                <CheckCircle2 class="h-3 w-3 text-green-500" />
                                                <span>{f.feature_name}</span>
                                            {:else}
                                                <XCircle class="h-3 w-3 text-muted-foreground" />
                                                <span class="text-muted-foreground">{f.feature_name}</span>
                                            {/if}
                                        </div>
                                    {/each}
                                </div>
                                <div class="pt-2">
                                    <Button size="sm" variant="outline" onclick={() => startEditPlan(plan)}><Edit3 class="h-3 w-3 mr-1" /> Edit</Button>
                                </div>
                            {/if}
                        </CardContent>
                    </Card>
                {/each}
            </div>
        {/if}

        <!-- ============ PAYMENT TAB ============ -->
        {#if activeTab === 'payment'}
            {#if payMessage}
                <div class="bg-green-50 text-green-700 rounded-lg px-4 py-3 text-sm">{payMessage}</div>
            {/if}

            <form onsubmit={(e) => { e.preventDefault(); savePaymentSettings(e.target as HTMLFormElement); }}>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- QRIS -->
                    <Card>
                        <CardHeader><CardTitle>QRIS</CardTitle></CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-xs">Nama Merchant</Label>
                                <Input name="qris_merchant_name" value={paymentSettings?.qris?.merchant_name ?? ''} placeholder="Qbus Indonesia" />
                            </div>
                            <div>
                                <Label class="text-xs">QRIS Image</Label>
                                <div class="flex items-center gap-3">
                                    {#if paymentSettings?.qris?.image_url}
                                        <img src={paymentSettings.qris.image_url} alt="QRIS" class="w-24 h-24 object-contain border rounded" />
                                    {/if}
                                    <Input type="file" name="qris_image" accept="image/png,image/jpeg" />
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">Upload gambar QRIS. Format PNG/JPG, max 1MB.</p>
                            </div>
                            <div>
                                <Label class="text-xs">Catatan QRIS</Label>
                                <Input name="qris_note" value={paymentSettings?.qris?.note ?? ''} placeholder="Scan QRIS di bawah dan masukkan nominal sesuai paket." />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Bank Transfer -->
                    <Card>
                        <CardHeader><CardTitle>Rekening Bank</CardTitle></CardHeader>
                        <CardContent class="space-y-4">
                            {#each [1, 2, 3] as i}
                                {@const accounts = paymentSettings?.bank_transfer?.accounts ?? []}
                                {@const acc = accounts[i - 1] ?? {}}
                                <div class="border rounded-lg p-3 space-y-2">
                                    <div class="font-medium text-sm">Rekening #{i}</div>
                                    <div>
                                        <Label class="text-xs">Nama Bank</Label>
                                        <Input name={`bank_${i}_name`} value={acc.bank_name ?? ''} placeholder="BCA" />
                                    </div>
                                    <div>
                                        <Label class="text-xs">Nomor Rekening</Label>
                                        <Input name={`bank_${i}_number`} value={acc.account_number ?? ''} placeholder="1234567890" />
                                    </div>
                                    <div>
                                        <Label class="text-xs">Atas Nama</Label>
                                        <Input name={`bank_${i}_holder`} value={acc.account_holder ?? ''} placeholder="PT Qbus Indonesia" />
                                    </div>
                                </div>
                            {/each}
                        </CardContent>
                    </Card>
                </div>

                <div class="flex justify-end mt-6">
                    <Button type="submit" disabled={payBusy}>
                        {payBusy ? 'Menyimpan...' : 'Simpan Pengaturan Pembayaran'}
                    </Button>
                </div>
            </form>
        {/if}
    {/if}
</div>
