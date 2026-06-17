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
    import { page } from '@inertiajs/svelte';
    import {
        Ban,
        Building2,
        CheckCircle2,
        CreditCard,
        Edit3,
        FileText,
        MoreHorizontal,
        Package,
        Plus,
        RefreshCw,
        RotateCcw,
        Search,
        ShieldCheck,
        TrendingUp,
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

    type TabName = 'tenants' | 'subscriptions' | 'plans' | 'billing' | 'payment';

    // â”€â”€â”€ Props â”€â”€â”€
    let { tab: initialTab = null, summary = null, saasTablesReady = false }: {
        tab?: TabName | null;
        summary?: {
            tenant_count: number;
            active_subscription_count: number;
            plan_count: number;
            invoice_pending_count?: number;
            invoice_verification_count?: number;
            invoice_overdue_count?: number;
            invoice_paid_month_count?: number;
        } | null;
        saasTablesReady?: boolean;
    } = $props();

    // â”€â”€â”€ State â”€â”€â”€
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

    // Billing state
    let invoices = $state<any[]>([]);
    let invoicePagination = $state<any>(null);
    let invoiceSummary = $state<any>({ pending: 0, verification: 0, paid_month: 0, overdue: 0, total_amount_pending: 0 });

    // Payment settings state
    let paymentSettings = $state<any>(null);
    let payBusy = $state(false);
    let payMessage = $state('');

    const shellClass = 'rounded-[28px] border border-slate-200/70 bg-white/90 shadow-[0_28px_90px_-55px_rgba(15,23,42,0.55)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/80';
    const panelClass = 'rounded-2xl border border-slate-200/70 bg-white/90 shadow-sm dark:border-slate-800 dark:bg-slate-950/70';
    const tableShellClass = 'overflow-hidden rounded-2xl border border-slate-200/70 bg-white/95 shadow-sm dark:border-slate-800 dark:bg-slate-950/70';

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

    function qrisStatusLabel(status: string | undefined): string {
        const labels: Record<string, string> = {
            ready: 'QRIS tersedia',
            missing_link: 'File ada, storage link belum aktif',
            missing_file: 'File QRIS belum tersedia',
            external: 'QRIS eksternal',
        };

        return labels[status ?? ''] ?? 'Status QRIS belum diketahui';
    }

    // â”€â”€â”€ Init â”€â”€â”€
    onMount(() => {
        activeTab = initialTab ?? 'tenants';
        if (activeTab === 'tenants') loadTenants();
        else if (activeTab === 'subscriptions') loadSubscriptions();
        else if (activeTab === 'plans') loadPlans();
        else if (activeTab === 'billing') loadInvoices();
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
        else if (tab === 'billing') loadInvoices();
        else if (tab === 'payment') loadPaymentSettings();
    }

    // â”€â”€â”€ API helpers â”€â”€â”€
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

    // â”€â”€â”€ Tenants â”€â”€â”€
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

    // â”€â”€â”€ Subscriptions â”€â”€â”€
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

    // â”€â”€â”€ Plans â”€â”€â”€
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

    // â”€â”€â”€ Helpers â”€â”€â”€
    async function loadInvoices(pageNum = 1) {
        const params = new URLSearchParams({ paginate: '1', page: String(pageNum), per_page: '15' });
        if (statusFilter) params.set('status', statusFilter);
        if (searchQuery) params.set('q', searchQuery);
        const data = await apiFetch(`/api/admin/invoices?${params}`);
        if (data) {
            invoices = data.invoices ?? [];
            invoicePagination = data.pagination ?? null;
            invoiceSummary = data.summary ?? { pending: 0, verification: 0, paid_month: 0, overdue: 0, total_amount_pending: 0 };
        }
    }

    async function markInvoicePaid(invoice: any) {
        if (!confirm(`Tandai invoice ${invoice.invoice_number} sebagai lunas?`)) return;
        const data = await apiFetch(`/api/admin/invoices/${invoice.id}/mark-paid`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' },
            body: JSON.stringify({ payment_method: invoice.payment_method || 'Manual Transfer' }),
        });
        if (data?.success) {
            message = data.message;
            loadInvoices(invoicePagination?.page ?? 1);
        }
    }

    function statusBadge(status: string): { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string } {
        const map: Record<string, any> = { pending_payment: { variant: 'outline', label: 'Pending Payment' }, trial: { variant: 'secondary', label: 'Trial' }, active: { variant: 'default', label: 'Active' }, past_due: { variant: 'outline', label: 'Past Due' }, suspended: { variant: 'destructive', label: 'Suspended' }, canceled: { variant: 'destructive', label: 'Canceled' }, expired: { variant: 'outline', label: 'Expired' } };
        return map[status] ?? { variant: 'outline', label: status };
    }

    function invoiceBadge(invoice: any): { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string } {
        if (invoice.status === 'paid') return { variant: 'default', label: 'Lunas' };
        if (invoice.status === 'overdue') return { variant: 'destructive', label: 'Overdue' };
        if (invoice.status === 'verification' || (invoice.status === 'pending' && invoice.payment_proof)) return { variant: 'secondary', label: 'Verifikasi' };
        if (invoice.status === 'pending') return { variant: 'outline', label: 'Pending' };
        return { variant: 'outline', label: invoice.status || '-' };
    }

    function formatRupiah(v: number): string {
        const amount = Number(v || 0);
        if (Math.abs(amount) >= 1_000_000) return `Rp ${(amount / 1_000_000).toFixed(1).replace('.0', '')}M`;
        if (Math.abs(amount) >= 1_000) return `Rp ${(amount / 1_000).toFixed(0)}K`;
        return `Rp ${amount.toLocaleString('id-ID')}`;
    }

    function formatDate(d: string | null): string {
        if (!d) return '-';
        return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function slugFromName(name: string) {
        tenantForm.slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
</script>

<AppHead title="SaaS Management" />

<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.08),transparent_28%),linear-gradient(180deg,rgba(248,250,252,0.94)_0%,rgba(255,255,255,1)_34%,rgba(248,250,252,0.96)_100%)] dark:bg-[radial-gradient(circle_at_top_left,rgba(56,189,248,0.08),transparent_24%),linear-gradient(180deg,#020617_0%,#0f172a_45%,#020617_100%)]">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        {#if !saasTablesReady}
            <Card class={panelClass}>
                <CardContent class="py-14 text-center">
                    <Package class="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 class="mt-4 text-lg font-semibold">Tabel SaaS Belum Tersedia</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Jalankan migrasi database dulu.</p>
                </CardContent>
            </Card>
        {:else}
        <!-- Flash Messages -->
        {#if error}
            <div class="rounded-xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm text-destructive">{error}</div>
        {/if}
        {#if message}
            <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">
                {message}
                <button onclick={() => message = ''} class="text-emerald-600 hover:text-emerald-800">Ã—</button>
            </div>
        {/if}

        <!-- Header -->
        <section class={shellClass}>
            <div class="relative isolate overflow-hidden rounded-[28px]">
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(15,23,42,0.03),transparent_35%,rgba(59,130,246,0.07))] dark:bg-[linear-gradient(135deg,rgba(148,163,184,0.08),transparent_35%,rgba(56,189,248,0.12))]"></div>
                <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] xl:items-end">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-400">
                            <ShieldCheck class="h-3.5 w-3.5 text-sky-500" />
                            SaaS Command Center
                        </div>
                        <div class="space-y-2">
                            <h1 class="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">SaaS Management</h1>
                            <p class="max-w-2xl text-sm text-muted-foreground sm:text-base">
                                Panel operasional untuk tenant, subscription, plan, billing, dan payment. Semua state kebaca, semua kerjaan kepegang dari satu layar.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            {#if summary}
                                <Badge variant="outline">{summary.tenant_count} tenants</Badge>
                                <Badge variant="secondary">{summary.active_subscription_count} active</Badge>
                                <Badge variant="outline">{summary.plan_count} plans</Badge>
                                <Badge variant="outline">{summary.invoice_pending_count ?? 0} pending</Badge>
                                <Badge variant="secondary">{summary.invoice_verification_count ?? 0} verifikasi</Badge>
                                <Badge variant={(summary.invoice_overdue_count ?? 0) > 0 ? 'destructive' : 'outline'}>{summary.invoice_overdue_count ?? 0} overdue</Badge>
                            {/if}
                            <Badge variant={saasTablesReady ? 'default' : 'outline'}>{saasTablesReady ? 'tables ready' : 'migrasi needed'}</Badge>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-2 2xl:grid-cols-3">
                        {#each [
                            { label: 'Tenants', value: summary?.tenant_count ?? 0, note: 'Registry aktif', icon: Building2, tone: 'border-sky-200 bg-sky-50/80 text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300' },
                            { label: 'Active Subscriptions', value: summary?.active_subscription_count ?? 0, note: 'Tenant jalan', icon: CreditCard, tone: 'border-emerald-200 bg-emerald-50/80 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300' },
                            { label: 'Plans', value: summary?.plan_count ?? 0, note: 'Skema paket', icon: Package, tone: 'border-violet-200 bg-violet-50/80 text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300' },
                            { label: 'Pending Invoice', value: summary?.invoice_pending_count ?? 0, note: 'Antrian bayar', icon: FileText, tone: 'border-amber-200 bg-amber-50/80 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300' },
                            { label: 'Verification', value: summary?.invoice_verification_count ?? 0, note: 'Bukti masuk', icon: CheckCircle2, tone: 'border-cyan-200 bg-cyan-50/80 text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300' },
                            { label: 'Paid This Month', value: summary?.invoice_paid_month_count ?? 0, note: 'Arus sehat', icon: TrendingUp, tone: 'border-rose-200 bg-rose-50/80 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300' },
                        ] as item}
                            <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-950/65">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">{item.label}</p>
                                        <p class="mt-2 text-2xl font-semibold tracking-tight text-foreground">{item.value}</p>
                                        <p class="mt-1 text-xs text-muted-foreground">{item.note}</p>
                                    </div>
                                    <div class={`rounded-2xl border p-2 ${item.tone}`}>
                                        <item.icon class="h-4 w-4" />
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            </div>
        </section>

        <nav class="sticky top-4 z-20">
            <div class="rounded-[24px] border border-slate-200/70 bg-white/86 p-2 shadow-[0_18px_60px_-40px_rgba(15,23,42,0.55)] backdrop-blur dark:border-slate-800 dark:bg-slate-950/82">
                <div class="flex gap-2 overflow-x-auto">
                    {#each [
                        { key: 'tenants', label: 'Tenants', icon: Building2, meta: summary?.tenant_count ?? 0 },
                        { key: 'subscriptions', label: 'Subscriptions', icon: CreditCard, meta: summary?.active_subscription_count ?? 0 },
                        { key: 'plans', label: 'Plans', icon: Package, meta: summary?.plan_count ?? 0 },
                        { key: 'billing', label: 'Billing', icon: FileText, meta: summary?.invoice_pending_count ?? 0 },
                        { key: 'payment', label: 'Metode Pembayaran', icon: CreditCard, meta: 'QRIS' },
                    ] as tab}
                        <button
                            onclick={() => switchTab(tab.key as TabName)}
                            aria-current={activeTab === tab.key ? 'page' : undefined}
                            class={`group flex min-w-[150px] items-center gap-3 rounded-full border px-4 py-3 text-sm font-medium transition
                                ${activeTab === tab.key
                                    ? 'border-slate-900 bg-slate-900 text-white shadow-[0_12px_30px_-16px_rgba(15,23,42,0.7)] dark:border-slate-100 dark:bg-slate-100 dark:text-slate-950'
                                    : 'border-slate-200 bg-white/80 text-muted-foreground hover:border-slate-300 hover:text-foreground dark:border-slate-800 dark:bg-slate-950/60 dark:hover:border-slate-700'}`}
                        >
                            <tab.icon class={`h-4 w-4 ${activeTab === tab.key ? 'text-white dark:text-slate-950' : 'text-sky-500'}`} />
                            <span>{tab.label}</span>
                            <span class={`ml-auto rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${activeTab === tab.key ? 'bg-white/15 text-white dark:bg-slate-900/10 dark:text-slate-950' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'}`}>
                                {tab.meta}
                            </span>
                        </button>
                    {/each}
                </div>
            </div>
        </nav>

        <!-- ============ TENANTS TAB ============ -->
        {#if activeTab === 'tenants'}
            <section class="space-y-4">
                <div class="flex flex-col gap-4 rounded-[24px] border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70 lg:flex-row lg:items-end lg:justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Tenant registry</p>
                        <h2 class="text-lg font-semibold text-foreground">Kelola workspace tenant</h2>
                        <p class="text-sm text-muted-foreground">Cari, tambah, suspend, atau cek subscription dari satu panel.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full max-w-sm">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input class="pl-9" placeholder="Cari tenant..." bind:value={searchQuery} onchange={() => loadTenants()} />
                        </div>
                        <Button variant="outline" size="icon" onclick={() => { searchQuery = ''; loadTenants(); }}><RefreshCw class="h-4 w-4" /></Button>
                        <Button onclick={() => openTenantForm()}><Plus class="h-4 w-4 mr-1" /> Tambah Tenant</Button>
                    </div>
                </div>

            <!-- Tenant Form Modal -->
            {#if showTenantForm}
                <Card class={panelClass}>
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
            <Card class={tableShellClass}>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50/80 text-xs uppercase tracking-[0.16em] dark:bg-slate-900/60">
                                <tr class="border-b border-slate-200 text-left dark:border-slate-800">
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Nama</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Paket</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground text-right">Users</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground text-right">Pools</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Berlangganan</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each tenants as t}
                                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 dark:border-slate-800 dark:hover:bg-slate-900/40">
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
                                                â€”
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
            </section>
        {/if}

        <!-- ============ SUBSCRIPTIONS TAB ============ -->
        {#if activeTab === 'subscriptions'}
            <section class="space-y-4">
                <div class="flex flex-col gap-4 rounded-[24px] border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70 lg:flex-row lg:items-end lg:justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Subscription desk</p>
                        <h2 class="text-lg font-semibold text-foreground">Kelola lifecycle langganan</h2>
                        <p class="text-sm text-muted-foreground">Filter status, ubah plan, dan kontrol tenant dari satu daftar.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full max-w-sm">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input class="pl-9" placeholder="Cari tenant..." bind:value={searchQuery} onchange={() => loadSubscriptions()} />
                        </div>
                        <select bind:value={statusFilter} onchange={() => loadSubscriptions()} class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm text-foreground shadow-sm dark:border-slate-800 dark:bg-slate-950">
                            <option value="">Semua Status</option>
                            <option value="pending_payment">Pending Payment</option>
                            <option value="trial">Trial</option>
                            <option value="active">Active</option>
                            <option value="past_due">Past Due</option>
                            <option value="suspended">Suspended</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <Button variant="outline" size="icon" onclick={() => { searchQuery = ''; statusFilter = ''; loadSubscriptions(); }}><RefreshCw class="h-4 w-4" /></Button>
                        <Button onclick={() => openSubForm()}><Plus class="h-4 w-4 mr-1" /> Tambah Subscription</Button>
                    </div>
                </div>

            <!-- Sub Form Modal -->
            {#if showSubForm}
                <Card class={panelClass}>
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
                                    <option value="pending_payment">Pending Payment</option>
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

            <Card class={tableShellClass}>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50/80 text-xs uppercase tracking-[0.16em] dark:bg-slate-900/60">
                                <tr class="border-b border-slate-200 text-left dark:border-slate-800">
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Tenant</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Paket</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Mulai</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Berakhir</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground text-right">Harga</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each subscriptions as s}
                                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 dark:border-slate-800 dark:hover:bg-slate-900/40">
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
            </section>
        {/if}

        <!-- ============ PLANS TAB ============ -->
        {#if activeTab === 'plans'}
            <section class="space-y-4">
                <div class="rounded-[24px] border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Plan matrix</p>
                    <h2 class="mt-1 text-lg font-semibold text-foreground">Atur paket dan limit produk</h2>
                    <p class="text-sm text-muted-foreground">Harga, limit resource, dan fitur premium dipelihara di sini.</p>
                </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {#each plans as plan}
                    {@const isEditing = editingPlan?.id === plan.id}
                    <Card class={`${panelClass} ${plan.is_active ? '' : 'opacity-70'}`}>
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
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Armada</span><span class="font-medium">{plan.max_armadas || 'âˆž'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Rute</span><span class="font-medium">{plan.max_routes || 'âˆž'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Pool</span><span class="font-medium">{plan.max_pools || 'âˆž'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max User</span><span class="font-medium">{plan.max_users || 'âˆž'}</span></div>
                                    <div class="flex justify-between"><span class="text-muted-foreground">Max Driver</span><span class="font-medium">{plan.max_drivers || 'âˆž'}</span></div>
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
            </section>
        {/if}

        <!-- ============ BILLING TAB ============ -->
        {#if activeTab === 'billing'}
            <section class="space-y-4">
                <div class="rounded-[24px] border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Billing queue</p>
                    <h2 class="mt-1 text-lg font-semibold text-foreground">Invoice subscription dan verifikasi pembayaran</h2>
                    <p class="text-sm text-muted-foreground">Pantau antrian bayar, bukti transfer, dan invoice yang butuh aksi.</p>
                </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                {#each [
                    { label: 'Pending', value: invoiceSummary.pending ?? 0, meta: 'Belum upload bukti' },
                    { label: 'Verifikasi', value: invoiceSummary.verification ?? 0, meta: 'Bukti masuk' },
                    { label: 'Lunas Bulan Ini', value: invoiceSummary.paid_month ?? 0, meta: 'Invoice paid' },
                    { label: 'Overdue', value: invoiceSummary.overdue ?? 0, meta: 'Lewat jatuh tempo' },
                    { label: 'Nominal Pending', value: formatRupiah(invoiceSummary.total_amount_pending ?? 0), meta: 'Outstanding' },
                ] as item}
                    <div class="rounded-2xl border border-slate-200/70 bg-white/85 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70">
                        <p class="text-xs font-medium text-muted-foreground">{item.label}</p>
                        <p class="mt-2 text-xl font-semibold text-foreground">{item.value}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{item.meta}</p>
                    </div>
                {/each}
            </div>

            <div class="flex flex-wrap items-center gap-3 rounded-[24px] border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input class="pl-9" placeholder="Cari invoice atau tenant..." bind:value={searchQuery} onchange={() => loadInvoices()} />
                </div>
                <select bind:value={statusFilter} onchange={() => loadInvoices()} class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm text-foreground shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="verification">Perlu Verifikasi</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
                <Button variant="outline" size="icon" onclick={() => { searchQuery = ''; statusFilter = ''; loadInvoices(); }}><RefreshCw class="h-4 w-4" /></Button>
            </div>

            <Card class={tableShellClass}>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[920px] text-sm">
                            <thead class="bg-slate-50/80 text-xs uppercase tracking-[0.16em] dark:bg-slate-900/60">
                                <tr class="border-b border-slate-200 text-left dark:border-slate-800">
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Invoice</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Tenant</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Paket</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground text-right">Nominal</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Due Date</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 font-semibold text-muted-foreground">Bukti</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each invoices as inv}
                                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 dark:border-slate-800 dark:hover:bg-slate-900/40">
                                        <td class="px-4 py-2.5">
                                            <div class="font-medium">{inv.invoice_number}</div>
                                            <div class="text-xs text-muted-foreground">{formatDate(inv.created_at)}</div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <div class="font-medium">{inv.tenant_name}</div>
                                            <div class="text-xs text-muted-foreground">{inv.tenant_slug}</div>
                                        </td>
                                        <td class="px-4 py-2.5">{inv.plan_name || '-'}</td>
                                        <td class="px-4 py-2.5 text-right font-semibold">{formatRupiah(inv.amount)}</td>
                                        <td class="px-4 py-2.5 text-xs">{formatDate(inv.due_date)}</td>
                                        <td class="px-4 py-2.5">
                                            <Badge variant={invoiceBadge(inv).variant}>{invoiceBadge(inv).label}</Badge>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            {#if inv.payment_proof_url}
                                                <a href={inv.payment_proof_url} target="_blank" rel="noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                                                    Lihat Bukti
                                                </a>
                                            {:else}
                                                <span class="text-xs text-muted-foreground">-</span>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger><Button variant="ghost" size="icon"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    {#if inv.payment_proof_url}
                                                        <DropdownMenuItem onclick={() => window.open(inv.payment_proof_url, '_blank')}><FileText class="h-4 w-4 mr-2" />Lihat Bukti</DropdownMenuItem>
                                                    {/if}
                                                    {#if inv.status !== 'paid'}
                                                        <DropdownMenuItem onclick={() => markInvoicePaid(inv)}><CheckCircle2 class="h-4 w-4 mr-2" />Mark Paid</DropdownMenuItem>
                                                    {/if}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </td>
                                    </tr>
                                {:else}
                                    <tr><td colspan="8" class="px-4 py-8 text-center text-muted-foreground">Belum ada invoice subscription.</td></tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    {#if invoicePagination?.last_page > 1}
                        <div class="flex items-center justify-between px-4 py-3 border-t">
                            <span class="text-xs text-muted-foreground">Page {invoicePagination.page} of {invoicePagination.last_page} ({invoicePagination.total} total)</span>
                            <div class="flex gap-1">
                                <Button variant="outline" size="sm" disabled={invoicePagination.page <= 1} onclick={() => loadInvoices(invoicePagination.page - 1)}>Prev</Button>
                                <Button variant="outline" size="sm" disabled={invoicePagination.page >= invoicePagination.last_page} onclick={() => loadInvoices(invoicePagination.page + 1)}>Next</Button>
                            </div>
                        </div>
                    {/if}
                </CardContent>
            </Card>
            </section>
        {/if}

        <!-- ============ PAYMENT TAB ============ -->
        {#if activeTab === 'payment'}
            <section class="space-y-4">
                <div class="rounded-[24px] border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950/70">
                    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">Payment workspace</p>
                            <h2 class="mt-1 text-lg font-semibold text-foreground">Metode Pembayaran</h2>
                            <p class="text-sm text-muted-foreground">QRIS tampil langsung ke tenant, bank transfer tetap jadi fallback manual.</p>
                        </div>
                        <Badge variant={paymentSettings?.qris?.has_image ? 'default' : 'outline'}>
                            {qrisStatusLabel(paymentSettings?.qris?.storage_status)}
                        </Badge>
                    </div>
                </div>

                {#if payMessage}
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300">{payMessage}</div>
                {/if}

                <form onsubmit={(e) => { e.preventDefault(); savePaymentSettings(e.target as HTMLFormElement); }}>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- QRIS -->
                        <Card class={panelClass}>
                            <CardHeader>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <CardTitle>QRIS</CardTitle>
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            Dipakai tenant saat bayar subscription.
                                        </p>
                                    </div>
                                    <Badge variant={paymentSettings?.qris?.has_image ? 'default' : 'outline'}>
                                        {qrisStatusLabel(paymentSettings?.qris?.storage_status)}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-[144px_minmax(0,1fr)] md:items-start">
                                    <div class="flex items-center justify-center rounded-2xl border border-slate-200/70 bg-slate-50/90 p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                                        {#if paymentSettings?.qris?.image_url}
                                            <img src={paymentSettings.qris.image_url} alt="QRIS" class="h-32 w-32 object-contain" />
                                        {:else}
                                            <div class="flex h-32 w-32 items-center justify-center rounded-xl border border-dashed text-center text-xs text-muted-foreground">
                                                QRIS kosong
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <Label class="text-xs uppercase tracking-[0.14em] text-muted-foreground">Nama Merchant</Label>
                                            <Input name="qris_merchant_name" value={paymentSettings?.qris?.merchant_name ?? ''} placeholder="OptiBus Indonesia" />
                                        </div>
                                        <div>
                                            <Label class="text-xs uppercase tracking-[0.14em] text-muted-foreground">Upload QRIS</Label>
                                            <Input type="file" name="qris_image" accept="image/png,image/jpeg" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs uppercase tracking-[0.14em] text-muted-foreground">Catatan QRIS</Label>
                                    <Input name="qris_note" value={paymentSettings?.qris?.note ?? ''} placeholder="Scan QRIS di bawah dan masukkan nominal sesuai paket." />
                                </div>
                                {#if paymentSettings?.qris?.storage_status === 'missing_link'}
                                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                                        Jalankan command production: php artisan storage:link
                                    </p>
                                {:else if paymentSettings?.qris?.storage_status === 'missing_file'}
                                    <p class="rounded-xl border border-dashed border-slate-300 px-3 py-2 text-xs text-muted-foreground dark:border-slate-700">
                                        Upload QRIS baru agar tenant bisa scan langsung dari Subscription.
                                    </p>
                                {/if}
                            </CardContent>
                        </Card>

                        <!-- Bank Transfer -->
                        <Card class={panelClass}>
                            <CardHeader><CardTitle>Rekening Bank</CardTitle></CardHeader>
                            <CardContent class="space-y-4">
                                {#each [1, 2, 3] as i}
                                    {@const accounts = paymentSettings?.bank_transfer?.accounts ?? []}
                                    {@const acc = accounts[i - 1] ?? {}}
                                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                                        <div class="mb-3 flex items-center justify-between">
                                            <div class="font-medium text-sm text-foreground">Rekening #{i}</div>
                                            <Badge variant="outline">Manual</Badge>
                                        </div>
                                        <div class="space-y-3">
                                            <div>
                                                <Label class="text-xs uppercase tracking-[0.14em] text-muted-foreground">Nama Bank</Label>
                                                <Input name={`bank_${i}_name`} value={acc.bank_name ?? ''} placeholder="BCA" />
                                            </div>
                                            <div>
                                                <Label class="text-xs uppercase tracking-[0.14em] text-muted-foreground">Nomor Rekening</Label>
                                                <Input name={`bank_${i}_number`} value={acc.account_number ?? ''} placeholder="1234567890" />
                                            </div>
                                            <div>
                                                <Label class="text-xs uppercase tracking-[0.14em] text-muted-foreground">Atas Nama</Label>
                                                <Input name={`bank_${i}_holder`} value={acc.account_holder ?? ''} placeholder="PT OptiBus Indonesia" />
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </CardContent>
                        </Card>
                    </div>

                    <div class="flex justify-end pt-6">
                        <Button type="submit" disabled={payBusy}>
                            {payBusy ? 'Menyimpan...' : 'Simpan Pengaturan Pembayaran'}
                        </Button>
                    </div>
                </form>
            </section>
        {/if}
        {/if}
    </div>
</div>
