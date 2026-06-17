<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Platform Dashboard',
                href: '/platform/dashboard',
            },
        ],
    };
</script>

<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import {
        ArrowRight,
        Building2,
        CalendarClock,
        CreditCard,
        Receipt,
        ShieldAlert,
        TrendingUp,
        Users,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';

    type TenantItem = {
        id: number;
        name: string;
        slug: string;
        tenant_status: string;
        subscription_status: string;
        plan_name: string;
        plan_slug: string;
        ends_at: string | null;
        user_count: number;
        pool_count: number;
        created_at: string;
    };

    type ExpiringItem = {
        id: number;
        tenant_id: number;
        tenant_name: string;
        plan_name: string;
        status: string;
        ends_at: string;
        days_left: number;
    };

    type SignupItem = {
        id: number;
        name: string;
        slug: string;
        created_at: string;
    };

    type TrendItem = {
        label: string;
        name: string;
        value: number;
    };

    type PaymentMetrics = {
        pending_count: number;
        overdue_count: number;
        paid_month_count: number;
        paid_month_amount: number;
        pending_amount: number;
    };

    type PaymentWatchItem = {
        id: number;
        invoice_number: string;
        tenant_name: string;
        plan_name: string;
        amount: number;
        status: string;
        gateway_status: string;
        due_date: string | null;
        days_overdue: number;
    };

    interface Metrics {
        mrr: number;
        previous_mrr: number;
        arr: number;
        active_tenants: number;
        previous_active_tenants: number;
        churn_rate: number;
        trial_conversion_rate: number;
        arpu: number;
        tpv_month: number;
        tpv_previous_month: number;
        month_label: string;
        previous_month_label: string;
    }

    const metrics = $derived((page.props.metrics ?? {}) as Metrics);
    const mrrTrend = $derived((page.props.mrrTrend ?? []) as TrendItem[]);
    const tenants = $derived((page.props.tenants ?? []) as TenantItem[]);
    const recentSignups = $derived(
        (page.props.recentSignups ?? []) as SignupItem[],
    );
    const expiringSoon = $derived(
        (page.props.expiringSoon ?? []) as ExpiringItem[],
    );
    const paymentMetrics = $derived(
        (page.props.paymentMetrics ?? {}) as PaymentMetrics,
    );
    const paymentWatchlist = $derived(
        (page.props.paymentWatchlist ?? []) as PaymentWatchItem[],
    );
    const maxMrrTrend = $derived(
        Math.max(1, ...mrrTrend.map((item) => Number(item.value || 0))),
    );

    function formatRupiah(value: number): string {
        const amount = Number(value || 0);
        if (Math.abs(amount) >= 1_000_000) {
            return `Rp ${(amount / 1_000_000).toFixed(1).replace('.0', '')}M`;
        }
        if (Math.abs(amount) >= 1_000) {
            return `Rp ${(amount / 1_000).toFixed(0)}K`;
        }
        return `Rp ${amount.toLocaleString('id-ID')}`;
    }

    function percentDelta(current: number, previous: number): string {
        if (!previous) {
            return current > 0 ? 'Baru' : '0%';
        }

        const delta = ((current - previous) / previous) * 100;
        const prefix = delta > 0 ? '+' : '';

        return `${prefix}${delta.toFixed(1).replace('.0', '')}%`;
    }

    function statusVariant(
        status: string,
    ): 'default' | 'destructive' | 'outline' | 'secondary' {
        if (status === 'active') return 'default';
        if (status === 'trial' || status === 'pending') return 'secondary';
        if (
            status === 'past_due' ||
            status === 'overdue' ||
            status === 'suspended'
        )
            return 'destructive';
        return 'outline';
    }

    function daysAgo(dateStr: string): string {
        const date = new Date(dateStr);
        const now = new Date();
        const diffDays = Math.floor(
            (now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24),
        );
        if (Number.isNaN(diffDays) || diffDays <= 0) return 'Hari ini';
        if (diffDays === 1) return '1 hari lalu';
        return `${diffDays} hari lalu`;
    }

    const headlineMetrics = $derived([
        {
            label: 'MRR',
            value: formatRupiah(metrics.mrr),
            meta: `${percentDelta(metrics.mrr, metrics.previous_mrr)} vs ${metrics.previous_month_label ?? 'bulan lalu'}`,
            icon: CreditCard,
        },
        {
            label: 'ARR',
            value: formatRupiah(metrics.arr),
            meta: `ARPU ${formatRupiah(metrics.arpu)}`,
            icon: TrendingUp,
        },
        {
            label: 'Active Tenants',
            value: `${Number(metrics.active_tenants || 0).toLocaleString('id-ID')}`,
            meta: `${percentDelta(metrics.active_tenants, metrics.previous_active_tenants)} vs periode lalu`,
            icon: Building2,
        },
        {
            label: 'Billing Attention',
            value: `${Number(paymentMetrics.pending_count || 0) + Number(paymentMetrics.overdue_count || 0)}`,
            meta: `${paymentMetrics.pending_count || 0} pending, ${paymentMetrics.overdue_count || 0} overdue`,
            icon: ShieldAlert,
        },
    ]);

    const operationalMetrics = $derived([
        {
            label: 'TPV Bulan Ini',
            value: formatRupiah(metrics.tpv_month),
            meta: percentDelta(metrics.tpv_month, metrics.tpv_previous_month),
        },
        {
            label: 'Paid Bulan Ini',
            value: formatRupiah(paymentMetrics.paid_month_amount),
            meta: `${paymentMetrics.paid_month_count || 0} invoice`,
        },
        {
            label: 'Pending Amount',
            value: formatRupiah(paymentMetrics.pending_amount),
            meta: `${paymentMetrics.pending_count || 0} menunggu checkout`,
        },
        {
            label: 'Trial Conversion',
            value: `${metrics.trial_conversion_rate || 0}%`,
            meta: `Churn ${metrics.churn_rate || 0}%`,
        },
    ]);
</script>

<AppHead title="Platform Dashboard" />

<div class="space-y-4 p-2 pb-8 md:p-4">
    <header
        class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950 md:flex-row md:items-end md:justify-between"
    >
        <div>
            <p class="text-xs font-semibold uppercase text-muted-foreground">
                Platform Overview
            </p>
            <h1 class="mt-1 text-2xl font-semibold text-foreground md:text-3xl">
                SaaS Command Center
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                MRR, tenant risk, renewal, dan pembayaran subscription.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="/admin-ops/saas/invoices">
                <Button size="sm"
                    ><Receipt class="mr-1 h-4 w-4" /> Billing</Button
                >
            </a>
            <a href="/admin-ops/saas/subscriptions">
                <Button variant="outline" size="sm"
                    ><CreditCard class="mr-1 h-4 w-4" /> Subscription</Button
                >
            </a>
            <a href="/admin-ops/saas/tenants">
                <Button variant="outline" size="sm"
                    ><Building2 class="mr-1 h-4 w-4" /> Tenants</Button
                >
            </a>
        </div>
    </header>

    <section
        class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 text-white shadow-sm"
    >
        <div class="grid gap-0 lg:grid-cols-[1.2fr_1fr]">
            <div
                class="border-b border-white/10 p-4 md:p-5 lg:border-b-0 lg:border-r"
            >
                <p class="text-xs font-semibold uppercase text-slate-400">
                    {metrics.month_label}
                </p>
                <div class="mt-2 flex flex-wrap items-end gap-x-4 gap-y-2">
                    <h2 class="text-3xl font-semibold md:text-4xl">
                        {formatRupiah(metrics.mrr)}
                    </h2>
                    <span
                        class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-200"
                    >
                        {percentDelta(metrics.mrr, metrics.previous_mrr)}
                    </span>
                </div>
                <p class="mt-2 text-sm text-slate-300">
                    Recurring revenue aktif dengan ARR {formatRupiah(
                        metrics.arr,
                    )}.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-0">
                {#each headlineMetrics as metric (metric.label)}
                    {@const Icon = metric.icon}
                    <div
                        class="border-b border-white/10 p-4 odd:border-r last:border-b-0 md:p-5"
                    >
                        <div
                            class="mb-3 flex h-9 w-9 items-center justify-center rounded-2xl bg-white/10 text-slate-100"
                        >
                            <Icon class="h-4 w-4" />
                        </div>
                        <p class="text-xs text-slate-400">{metric.label}</p>
                        <p class="mt-1 text-lg font-semibold">{metric.value}</p>
                        <p class="mt-1 text-xs text-slate-400">{metric.meta}</p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        {#each operationalMetrics as metric (metric.label)}
            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
            >
                <p class="text-xs font-medium text-muted-foreground">
                    {metric.label}
                </p>
                <p class="mt-2 text-xl font-semibold text-foreground">
                    {metric.value}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">{metric.meta}</p>
            </div>
        {/each}
    </section>

    {#if mrrTrend.length > 0}
        <section
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
        >
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-foreground">
                        MRR Trend
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        12 bulan terakhir
                    </p>
                </div>
                <Badge variant="outline">{formatRupiah(metrics.mrr)}</Badge>
            </div>
            <div class="grid h-36 grid-cols-12 items-end gap-1.5">
                {#each mrrTrend as item (item.name)}
                    {@const height = Math.max(
                        6,
                        Math.round(
                            (Number(item.value || 0) / maxMrrTrend) * 100,
                        ),
                    )}
                    <div class="flex h-full flex-col justify-end gap-1">
                        <div
                            class="rounded-t bg-primary/75"
                            style={`height:${height}%`}
                            title={`${item.name}: ${formatRupiah(item.value)}`}
                        ></div>
                        <span
                            class="text-center text-[10px] text-muted-foreground"
                            >{item.label}</span
                        >
                    </div>
                {/each}
            </div>
        </section>
    {/if}

    <div class="grid gap-4 xl:grid-cols-[1.45fr_0.8fr]">
        <section
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
        >
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-foreground">
                        Tenant Portfolio
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        {tenants.length} tenant terbaru
                    </p>
                </div>
                <a
                    href="/admin-ops/saas/tenants"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary"
                >
                    Kelola <ArrowRight class="h-3.5 w-3.5" />
                </a>
            </div>
            {#if tenants.length > 0}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-xs text-muted-foreground"
                            >
                                <th class="pb-2 font-medium">Tenant</th>
                                <th class="pb-2 font-medium">Paket</th>
                                <th class="pb-2 font-medium">Subscription</th>
                                <th class="pb-2 text-right font-medium"
                                    >Users</th
                                >
                                <th class="pb-2 text-right font-medium"
                                    >Pools</th
                                >
                                <th class="pb-2 text-right font-medium">Ends</th
                                >
                            </tr>
                        </thead>
                        <tbody>
                            {#each tenants as tenant (tenant.id)}
                                <tr class="border-b last:border-0">
                                    <td class="py-3">
                                        <a
                                            href="/admin-ops/saas/tenants"
                                            class="font-semibold text-foreground hover:text-primary"
                                            >{tenant.name}</a
                                        >
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {tenant.slug}
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <Badge variant="outline"
                                            >{tenant.plan_name}</Badge
                                        >
                                    </td>
                                    <td class="py-3">
                                        <Badge
                                            variant={statusVariant(
                                                tenant.subscription_status,
                                            )}
                                        >
                                            {tenant.subscription_status}
                                        </Badge>
                                    </td>
                                    <td class="py-3 text-right"
                                        >{tenant.user_count}</td
                                    >
                                    <td class="py-3 text-right"
                                        >{tenant.pool_count}</td
                                    >
                                    <td
                                        class="py-3 text-right text-xs text-muted-foreground"
                                        >{tenant.ends_at ?? '-'}</td
                                    >
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            {:else}
                <div
                    class="rounded-2xl border border-dashed p-6 text-center text-sm text-muted-foreground"
                >
                    Belum ada tenant terdaftar.
                </div>
            {/if}
        </section>

        <aside class="space-y-4">
            <section
                class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-foreground">
                        Payment Watchlist
                    </h2>
                    <a
                        href="/admin-ops/saas/invoices"
                        class="text-xs font-semibold text-primary">Buka</a
                    >
                </div>
                {#if paymentWatchlist.length > 0}
                    <div class="space-y-2">
                        {#each paymentWatchlist as invoice (invoice.id)}
                            <div
                                class="rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            {invoice.tenant_name}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {invoice.invoice_number} - {invoice.plan_name}
                                        </p>
                                    </div>
                                    <Badge
                                        variant={invoice.status === 'overdue' ||
                                        invoice.days_overdue > 0
                                            ? 'destructive'
                                            : 'outline'}
                                    >
                                        {invoice.gateway_status ||
                                            invoice.status}
                                    </Badge>
                                </div>
                                <div
                                    class="mt-2 flex items-center justify-between text-xs"
                                >
                                    <span class="text-muted-foreground"
                                        >{invoice.due_date ?? '-'}</span
                                    >
                                    <span class="font-semibold"
                                        >{formatRupiah(invoice.amount)}</span
                                    >
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground">
                        Tidak ada invoice yang perlu ditindaklanjuti.
                    </p>
                {/if}
            </section>

            <section
                class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
            >
                <div class="mb-3 flex items-center gap-2">
                    <CalendarClock class="h-4 w-4 text-muted-foreground" />
                    <h2 class="text-sm font-semibold text-foreground">
                        Expiring 7 Hari
                    </h2>
                </div>
                {#if expiringSoon.length > 0}
                    <div class="space-y-2">
                        {#each expiringSoon as item (item.id)}
                            <div
                                class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800"
                            >
                                <div>
                                    <p class="font-semibold text-foreground">
                                        {item.tenant_name}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {item.plan_name} - {item.status}
                                    </p>
                                </div>
                                <Badge
                                    variant={item.days_left <= 2
                                        ? 'destructive'
                                        : 'outline'}
                                >
                                    {item.days_left} hari
                                </Badge>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground">
                        Tidak ada subscription yang expiring.
                    </p>
                {/if}
            </section>

            <section
                class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950"
            >
                <div class="mb-3 flex items-center gap-2">
                    <Users class="h-4 w-4 text-muted-foreground" />
                    <h2 class="text-sm font-semibold text-foreground">
                        Signup Bulan Ini
                    </h2>
                </div>
                {#if recentSignups.length > 0}
                    <div class="space-y-2">
                        {#each recentSignups as item (item.id)}
                            <div
                                class="flex items-center justify-between gap-3 text-sm"
                            >
                                <div>
                                    <p class="font-semibold text-foreground">
                                        {item.name}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {item.slug}
                                    </p>
                                </div>
                                <span class="text-xs text-muted-foreground"
                                    >{daysAgo(item.created_at)}</span
                                >
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground">
                        Belum ada signup baru.
                    </p>
                {/if}
            </section>
        </aside>
    </div>
</div>
