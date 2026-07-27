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
        const amount = Math.round(Number(value || 0));
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

    function statusLabel(status: string): string {
        const normalized = String(status || '').toLowerCase();

        const labels: Record<string, string> = {
            active: 'Aktif',
            trial: 'Uji Coba',
            pending: 'Menunggu',
            pending_payment: 'Menunggu Bayar',
            paid: 'Lunas',
            overdue: 'Terlambat',
            past_due: 'Terlambat',
            suspended: 'Ditahan',
            canceled: 'Batal',
            inactive: 'Tidak Aktif',
        };

        return labels[normalized] ?? (status || '-');
    }

    function formatDate(value: string | null | undefined): string {
        if (!value) return '-';

        const date = new Date(`${value}`.slice(0, 10) + 'T00:00:00');
        if (Number.isNaN(date.getTime())) return value;

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(date);
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
            label: 'Pendapatan Bulanan',
            value: formatRupiah(metrics.mrr),
            meta: `${percentDelta(metrics.mrr, metrics.previous_mrr)} dari bulan lalu.`,
            icon: CreditCard,
        },
        {
            label: 'Perkiraan Setahun',
            value: formatRupiah(metrics.arr),
            meta: `Rata-rata per tenant ${formatRupiah(metrics.arpu)}.`,
            icon: TrendingUp,
        },
        {
            label: 'Tenant Aktif',
            value: `${Number(metrics.active_tenants || 0).toLocaleString('id-ID')}`,
            meta: `${percentDelta(metrics.active_tenants, metrics.previous_active_tenants)} dari periode lalu.`,
            icon: Building2,
        },
        {
            label: 'Tagihan Perlu Dicek',
            value: `${Number(paymentMetrics.pending_count || 0) + Number(paymentMetrics.overdue_count || 0)}`,
            meta: `${paymentMetrics.pending_count || 0} menunggu bayar, ${paymentMetrics.overdue_count || 0} terlambat.`,
            icon: ShieldAlert,
        },
    ]);

    const operationalMetrics = $derived([
        {
            label: 'Transaksi Travel Bulan Ini',
            value: formatRupiah(metrics.tpv_month),
            meta: `${percentDelta(metrics.tpv_month, metrics.tpv_previous_month)} dari bulan lalu.`,
        },
        {
            label: 'Pembayaran Masuk',
            value: formatRupiah(paymentMetrics.paid_month_amount),
            meta: `${paymentMetrics.paid_month_count || 0} invoice lunas bulan ini.`,
        },
        {
            label: 'Tagihan Belum Dibayar',
            value: formatRupiah(paymentMetrics.pending_amount),
            meta: `${paymentMetrics.pending_count || 0} invoice menunggu.`,
        },
        {
            label: 'Trial Jadi Pelanggan',
            value: `${metrics.trial_conversion_rate || 0}%`,
            meta: `Pelanggan berhenti bulan ini ${metrics.churn_rate || 0}%.`,
        },
    ]);
</script>

<AppHead title="Platform Dashboard" />

<div
    class="mx-auto min-w-0 max-w-[1500px] space-y-3 overflow-x-hidden p-2 pb-8 sm:space-y-4 sm:p-3 md:space-y-5 md:p-5"
>
    <header
        class="relative min-w-0 overflow-hidden rounded-2xl border border-emerald-900/10 bg-[linear-gradient(135deg,#103d3a,#0d7066_54%,#f7f0dc)] p-4 text-white shadow-[0_22px_70px_-40px_rgba(16,61,58,0.85)] sm:p-5 md:p-6"
    >
        <div
            class="pointer-events-none absolute -right-24 -top-24 h-56 w-56 rounded-full border border-white/15 sm:-right-16 sm:-top-20 sm:h-64 sm:w-64"
        ></div>
        <div
            class="pointer-events-none absolute bottom-0 right-0 h-16 w-44 rounded-t-full bg-white/10 blur-2xl sm:right-10 sm:h-20 sm:w-72"
        ></div>
        <div
            class="relative flex min-w-0 flex-col gap-4 md:flex-row md:items-end md:justify-between"
        >
            <div class="min-w-0 max-w-3xl">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-50/75"
                >
                    Ringkasan Platform
                </p>
                <h1
                    class="mt-2 text-xl font-semibold leading-tight tracking-[-0.04em] sm:text-2xl md:text-4xl"
                >
                    Pantau kesehatan bisnis OptiBus.
                </h1>
                <p
                    class="mt-2 hidden text-sm leading-6 text-emerald-50/86 sm:block"
                >
                    Pendapatan, tenant aktif, tagihan, dan paket hampir
                    berakhir.
                </p>
            </div>
            <div class="grid gap-2 sm:flex sm:flex-wrap">
                <a class="w-full sm:w-auto" href="/admin-ops/saas/invoices">
                    <Button
                        size="sm"
                        class="w-full justify-center rounded-full bg-white text-[#103d3a] hover:bg-emerald-50 sm:w-auto"
                        ><Receipt class="mr-1 h-4 w-4" /> Tagihan</Button
                    >
                </a>
                <a
                    class="w-full sm:w-auto"
                    href="/admin-ops/saas/subscriptions"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full justify-center rounded-full border-white/35 bg-white/10 text-white hover:bg-white/18 hover:text-white sm:w-auto"
                        ><CreditCard class="mr-1 h-4 w-4" /> Langganan</Button
                    >
                </a>
                <a class="w-full sm:w-auto" href="/admin-ops/saas/tenants">
                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full justify-center rounded-full border-white/35 bg-white/10 text-white hover:bg-white/18 hover:text-white sm:w-auto"
                        ><Building2 class="mr-1 h-4 w-4" /> Tenant</Button
                    >
                </a>
            </div>
        </div>
    </header>

    <section
        class="min-w-0 overflow-hidden rounded-2xl border border-[#d7dfd5] bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950"
    >
        <div class="grid gap-0 xl:grid-cols-[1.05fr_1.25fr]">
            <div
                class="min-w-0 border-b border-[#d7dfd5] bg-[#f8faf4] p-4 dark:border-slate-800 dark:bg-slate-900/40 sm:p-5 xl:border-b-0 xl:border-r"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-[#697570]"
                >
                    {metrics.month_label}
                </p>
                <h2
                    class="mt-3 break-words text-2xl font-bold leading-tight tracking-[-0.05em] text-[#103d3a] sm:text-3xl md:text-4xl"
                >
                    {formatRupiah(metrics.mrr)}
                </h2>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                    >
                        {percentDelta(metrics.mrr, metrics.previous_mrr)}
                    </span>
                    <span class="text-xs text-muted-foreground">
                        dibanding {metrics.previous_month_label ?? 'bulan lalu'}
                    </span>
                </div>
                <p
                    class="mt-4 hidden text-sm leading-6 text-[#53615d] sm:block"
                >
                    Ini adalah estimasi pendapatan berulang dari tenant yang
                    masih aktif atau sedang uji coba. Jika semua tetap aktif,
                    perkiraan setahun menjadi {formatRupiah(metrics.arr)}.
                </p>
            </div>
            <div class="grid md:grid-cols-2">
                {#each headlineMetrics as metric (metric.label)}
                    {@const Icon = metric.icon}
                    <div
                        class="min-w-0 border-b border-[#d7dfd5] p-3 last:border-b-0 sm:p-4 md:odd:border-r md:[&:nth-last-child(-n+2)]:border-b-0 dark:border-slate-800"
                    >
                        <div
                            class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#eef7ef] text-[#103d3a]"
                        >
                            <Icon class="h-4 w-4" />
                        </div>
                        <p class="text-xs font-semibold text-[#697570]">
                            {metric.label}
                        </p>
                        <p
                            class="mt-1 break-words text-lg font-bold leading-tight tracking-[-0.03em] text-foreground sm:text-xl"
                        >
                            {metric.value}
                        </p>
                        <p
                            class="mt-2 hidden text-xs leading-5 text-muted-foreground sm:block"
                        >
                            {metric.meta}
                        </p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        {#each operationalMetrics as metric (metric.label)}
            <div
                class="min-w-0 rounded-2xl border border-[#d7dfd5] bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-950 sm:p-4"
            >
                <p class="text-xs font-semibold text-[#697570]">
                    {metric.label}
                </p>
                <p
                    class="mt-2 break-words text-lg font-bold leading-tight tracking-[-0.03em] text-[#103d3a] dark:text-emerald-100 sm:text-xl"
                >
                    {metric.value}
                </p>
                <p
                    class="mt-2 hidden text-xs leading-5 text-muted-foreground sm:block"
                >
                    {metric.meta}
                </p>
            </div>
        {/each}
    </section>

    {#if mrrTrend.length > 0}
        <section
            class="min-w-0 rounded-2xl border border-[#d7dfd5] bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-4 md:p-5"
        >
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-foreground">
                        Pendapatan Bulanan
                    </h2>
                    <p
                        class="hidden text-xs leading-5 text-muted-foreground sm:block"
                    >
                        Tren 12 bulan terakhir.
                    </p>
                </div>
                <Badge variant="outline" class="shrink-0 rounded-full">
                    Bulan ini {formatRupiah(metrics.mrr)}
                </Badge>
            </div>
            <div class="min-w-0 overflow-hidden pb-1">
                <div
                    class="grid h-32 grid-cols-12 items-end gap-1 sm:h-40 sm:gap-1.5"
                >
                    {#each mrrTrend as item (item.name)}
                        {@const height = Math.max(
                            6,
                            Math.round(
                                (Number(item.value || 0) / maxMrrTrend) * 100,
                            ),
                        )}
                        <div
                            class="flex h-full min-w-0 flex-col justify-end gap-1"
                        >
                            <div
                                class="rounded-t-lg bg-[linear-gradient(180deg,#0d7066,#b96c20)] shadow-sm"
                                style={`height:${height}%`}
                                title={`${item.name}: ${formatRupiah(item.value)}`}
                            ></div>
                            <span
                                class="truncate text-center text-[10px] text-muted-foreground"
                                >{item.label}</span
                            >
                        </div>
                    {/each}
                </div>
            </div>
        </section>
    {/if}

    <div class="grid gap-4 xl:grid-cols-[1.45fr_0.85fr]">
        <section
            class="min-w-0 rounded-2xl border border-[#d7dfd5] bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-4 md:p-5"
        >
            <div
                class="mb-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-foreground">
                        Tenant Terbaru
                    </h2>
                    <p
                        class="hidden text-xs leading-5 text-muted-foreground sm:block"
                    >
                        {tenants.length} tenant terakhir.
                    </p>
                </div>
                <a
                    href="/admin-ops/saas/tenants"
                    class="inline-flex w-full items-center justify-center gap-1 rounded-full border border-[#d7dfd5] px-3 py-1.5 text-xs font-semibold text-[#103d3a] transition hover:bg-[#eef7ef] sm:w-auto"
                >
                    Kelola <ArrowRight class="h-3.5 w-3.5" />
                </a>
            </div>
            {#if tenants.length > 0}
                <div class="space-y-3 md:hidden">
                    {#each tenants as tenant (tenant.id)}
                        <article
                            class="rounded-xl border border-[#d7dfd5] bg-[#fbfcf8] p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900/45"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a
                                        href="/admin-ops/saas/tenants"
                                        class="block truncate font-semibold text-foreground hover:text-primary"
                                    >
                                        {tenant.name}
                                    </a>
                                    <p
                                        class="mt-0.5 truncate text-xs text-muted-foreground"
                                    >
                                        {tenant.slug}
                                    </p>
                                </div>
                                <Badge
                                    variant={statusVariant(
                                        tenant.subscription_status,
                                    )}
                                    class="shrink-0 rounded-full"
                                >
                                    {statusLabel(tenant.subscription_status)}
                                </Badge>
                            </div>

                            <div
                                class="mt-3 grid min-w-0 grid-cols-2 gap-2 text-xs"
                            >
                                <div
                                    class="min-w-0 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <p class="text-muted-foreground">Paket</p>
                                    <p
                                        class="mt-1 truncate font-semibold text-[#103d3a] dark:text-emerald-100"
                                    >
                                        {tenant.plan_name}
                                    </p>
                                </div>
                                <div
                                    class="min-w-0 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <p class="text-muted-foreground">
                                        Berakhir
                                    </p>
                                    <p
                                        class="mt-1 font-semibold text-[#103d3a] dark:text-emerald-100"
                                    >
                                        {formatDate(tenant.ends_at)}
                                    </p>
                                </div>
                                <div
                                    class="min-w-0 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <p class="text-muted-foreground">User</p>
                                    <p
                                        class="mt-1 font-semibold text-[#103d3a] dark:text-emerald-100"
                                    >
                                        {tenant.user_count}
                                    </p>
                                </div>
                                <div
                                    class="min-w-0 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <p class="text-muted-foreground">Pool</p>
                                    <p
                                        class="mt-1 font-semibold text-[#103d3a] dark:text-emerald-100"
                                    >
                                        {tenant.pool_count}
                                    </p>
                                </div>
                            </div>
                        </article>
                    {/each}
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-xs text-muted-foreground"
                            >
                                <th class="pb-2 font-medium">Tenant</th>
                                <th class="pb-2 font-medium">Paket</th>
                                <th class="pb-2 font-medium">Status Bayar</th>
                                <th class="pb-2 text-right font-medium">User</th
                                >
                                <th class="pb-2 text-right font-medium">Pool</th
                                >
                                <th class="pb-2 text-right font-medium">
                                    Berakhir
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each tenants as tenant (tenant.id)}
                                <tr
                                    class="border-b last:border-0 hover:bg-[#f8faf4]"
                                >
                                    <td class="py-3 pr-3">
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
                                    <td class="py-3 pr-3">
                                        <Badge
                                            variant="outline"
                                            class="rounded-full"
                                            >{tenant.plan_name}</Badge
                                        >
                                    </td>
                                    <td class="py-3 pr-3">
                                        <Badge
                                            variant={statusVariant(
                                                tenant.subscription_status,
                                            )}
                                            class="rounded-full"
                                        >
                                            {statusLabel(
                                                tenant.subscription_status,
                                            )}
                                        </Badge>
                                    </td>
                                    <td class="py-3 text-right">
                                        {tenant.user_count}
                                    </td>
                                    <td class="py-3 text-right">
                                        {tenant.pool_count}
                                    </td>
                                    <td
                                        class="py-3 text-right text-xs text-muted-foreground"
                                    >
                                        {formatDate(tenant.ends_at)}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            {:else}
                <div
                    class="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground"
                >
                    Belum ada tenant terdaftar.
                </div>
            {/if}
        </section>

        <aside class="space-y-4">
            <section
                class="min-w-0 rounded-2xl border border-[#d7dfd5] bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-4"
            >
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-foreground">
                            Tagihan Perlu Aksi
                        </h2>
                        <p
                            class="hidden text-xs leading-5 text-muted-foreground sm:block"
                        >
                            Invoice yang belum dibayar atau sudah lewat jatuh
                            tempo.
                        </p>
                    </div>
                    <a
                        href="/admin-ops/saas/invoices"
                        class="text-xs font-semibold text-primary">Buka</a
                    >
                </div>
                {#if paymentWatchlist.length > 0}
                    <div class="space-y-2">
                        {#each paymentWatchlist as invoice (invoice.id)}
                            <div
                                class="rounded-xl border border-slate-200 p-3 text-sm dark:border-slate-800"
                            >
                                <div
                                    class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-3"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate font-semibold text-foreground"
                                        >
                                            {invoice.tenant_name}
                                        </p>
                                        <p
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {invoice.invoice_number} - {invoice.plan_name}
                                        </p>
                                    </div>
                                    <Badge
                                        class="shrink-0 rounded-full"
                                        variant={invoice.status === 'overdue' ||
                                        invoice.days_overdue > 0
                                            ? 'destructive'
                                            : 'outline'}
                                    >
                                        {statusLabel(
                                            invoice.gateway_status ||
                                                invoice.status,
                                        )}
                                    </Badge>
                                </div>
                                <div
                                    class="mt-3 flex flex-col gap-1 text-xs sm:flex-row sm:items-center sm:justify-between sm:gap-3"
                                >
                                    <span class="text-muted-foreground">
                                        Jatuh tempo {formatDate(
                                            invoice.due_date,
                                        )}
                                    </span>
                                    <span
                                        class="text-right font-semibold text-[#103d3a] dark:text-emerald-100"
                                    >
                                        {formatRupiah(invoice.amount)}
                                    </span>
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground">
                        Tidak ada tagihan yang perlu ditindaklanjuti.
                    </p>
                {/if}
            </section>

            <section
                class="min-w-0 rounded-2xl border border-[#d7dfd5] bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-4"
            >
                <div class="mb-3 flex items-start gap-2">
                    <CalendarClock class="mt-0.5 h-4 w-4 text-[#0d7066]" />
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-foreground">
                            Paket Akan Berakhir
                        </h2>
                        <p
                            class="hidden text-xs leading-5 text-muted-foreground sm:block"
                        >
                            Tenant yang masa aktifnya selesai dalam 7 hari.
                        </p>
                    </div>
                </div>
                {#if expiringSoon.length > 0}
                    <div class="space-y-2">
                        {#each expiringSoon as item (item.id)}
                            <div
                                class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 p-3 text-sm dark:border-slate-800"
                            >
                                <div class="min-w-0">
                                    <p class="font-semibold text-foreground">
                                        {item.tenant_name}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {item.plan_name} - {statusLabel(
                                            item.status,
                                        )}
                                    </p>
                                </div>
                                <Badge
                                    class="shrink-0 rounded-full"
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
                        Tidak ada paket yang hampir berakhir.
                    </p>
                {/if}
            </section>

            <section
                class="min-w-0 rounded-2xl border border-[#d7dfd5] bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-4"
            >
                <div class="mb-3 flex items-start gap-2">
                    <Users class="mt-0.5 h-4 w-4 text-[#0d7066]" />
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-foreground">
                            Tenant Baru
                        </h2>
                        <p
                            class="hidden text-xs leading-5 text-muted-foreground sm:block"
                        >
                            Tenant yang baru mendaftar di bulan berjalan.
                        </p>
                    </div>
                </div>
                {#if recentSignups.length > 0}
                    <div class="space-y-2">
                        {#each recentSignups as item (item.id)}
                            <div
                                class="flex items-center justify-between gap-3 text-sm"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate font-semibold text-foreground"
                                    >
                                        {item.name}
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {item.slug}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 text-xs text-muted-foreground"
                                    >{daysAgo(item.created_at)}</span
                                >
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground">
                        Belum ada tenant baru bulan ini.
                    </p>
                {/if}
            </section>
        </aside>
    </div>
</div>
