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
    import { ArrowDown, ArrowUp, BarChart3, Building2, CreditCard, TrendingDown, TrendingUp, Users } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

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
    const recentSignups = $derived((page.props.recentSignups ?? []) as SignupItem[]);
    const expiringSoon = $derived((page.props.expiringSoon ?? []) as ExpiringItem[]);

    function formatRupiah(value: number): string {
        if (Math.abs(value) >= 1_000_000) {
            return `Rp ${(value / 1_000_000).toFixed(1)}M`;
        }
        if (Math.abs(value) >= 1_000) {
            return `Rp ${(value / 1_000).toFixed(0)}K`;
        }
        return `Rp ${value.toLocaleString('id-ID')}`;
    }

    function trendIcon(current: number, previous: number): typeof TrendingUp {
        return current >= previous ? TrendingUp : TrendingDown;
    }

    function statusBadge(status: string): { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string } {
        const map: Record<string, { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string }> = {
            trial: { variant: 'secondary', label: 'Trial' },
            active: { variant: 'default', label: 'Active' },
            past_due: { variant: 'outline', label: 'Past Due' },
            suspended: { variant: 'destructive', label: 'Suspended' },
            canceled: { variant: 'destructive', label: 'Canceled' },
            expired: { variant: 'outline', label: 'Expired' },
        };
        return map[status] ?? { variant: 'outline', label: status };
    }

    function tenantStatusBadge(status: string): { variant: 'default' | 'destructive' | 'outline'; label: string } {
        const map: Record<string, { variant: 'default' | 'destructive' | 'outline'; label: string }> = {
            active: { variant: 'default', label: 'Active' },
            suspended: { variant: 'destructive', label: 'Suspended' },
            canceled: { variant: 'outline', label: 'Canceled' },
        };
        return map[status] ?? { variant: 'outline', label: status };
    }

    function daysAgo(dateStr: string): string {
        const d = new Date(dateStr);
        const now = new Date();
        const diffMs = now.getTime() - d.getTime();
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        if (diffDays === 0) return 'Hari ini';
        if (diffDays === 1) return '1 hari lalu';
        return `${diffDays} hari lalu`;
    }
</script>

<AppHead title="Platform Dashboard" />

<div class="space-y-6 pb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Platform Dashboard</h1>
        <p class="text-muted-foreground mt-1">Metrik SaaS dan manajemen tenant Qbus</p>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">MRR</CardTitle>
                <CreditCard class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{formatRupiah(metrics.mrr)}</div>
                <p class="text-xs text-muted-foreground mt-1">
                    {#if metrics.previous_mrr > 0}
                        {@const pct = Math.round(((metrics.mrr - metrics.previous_mrr) / metrics.previous_mrr) * 100)}
                        <span class={pct >= 0 ? 'text-green-600' : 'text-red-600'}>
                            {pct >= 0 ? '↑' : '↓'} {Math.abs(pct)}%
                        </span>
                    {/if}
                    <span> vs {metrics.previous_month_label}</span>
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Active Tenants</CardTitle>
                <Building2 class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{metrics.active_tenants}</div>
                <p class="text-xs text-muted-foreground mt-1">
                    ARPU: {formatRupiah(metrics.arpu)}
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Churn Rate</CardTitle>
                <TrendingDown class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{metrics.churn_rate}%</div>
                <p class="text-xs text-muted-foreground mt-1">{metrics.month_label}</p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">TPV (Bulan Ini)</CardTitle>
                <BarChart3 class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{formatRupiah(metrics.tpv_month)}</div>
                <p class="text-xs text-muted-foreground mt-1">
                    {#if metrics.tpv_previous_month > 0}
                        {@const pct = Math.round(((metrics.tpv_month - metrics.tpv_previous_month) / metrics.tpv_previous_month) * 100)}
                        <span class={pct >= 0 ? 'text-green-600' : 'text-red-600'}>
                            {pct >= 0 ? '↑' : '↓'} {Math.abs(pct)}%
                        </span>
                    {/if}
                    <span> vs {metrics.previous_month_label}</span>
                </p>
            </CardContent>
        </Card>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-xs font-medium text-muted-foreground">ARR</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-lg font-semibold">{formatRupiah(metrics.arr)}</div>
            </CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-xs font-medium text-muted-foreground">Trial → Paid</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-lg font-semibold">{metrics.trial_conversion_rate}%</div>
            </CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="text-xs font-medium text-muted-foreground">TPV</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="text-lg font-semibold">{formatRupiah(metrics.tpv_month)}</div>
            </CardContent>
        </Card>
    </div>

    <!-- MRR Trend Chart -->
    {#if mrrTrend.length > 0}
        <Card>
            <CardHeader>
                <CardTitle>MRR Trend (12 Bulan)</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-end gap-1 h-32">
                    {#each mrrTrend as item}
                        {@const maxVal = Math.max(...mrrTrend.map((t: TrendItem) => t.value), 1)}
                        {@const height = (item.value / maxVal) * 100}
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-muted-foreground">{formatRupiah(item.value)}</span>
                            <div
                                class="w-full bg-primary/20 rounded-t"
                                style="height: {height}%"
                                title="{item.name}: {formatRupiah(item.value)}"
                            ></div>
                            <span class="text-[10px] text-muted-foreground">{item.label}</span>
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>
    {/if}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tenant List -->
        <div class="lg:col-span-2">
            <Card>
                <CardHeader>
                    <CardTitle>Tenants</CardTitle>
                </CardHeader>
                <CardContent>
                    {#if tenants.length > 0}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left">
                                        <th class="pb-2 font-medium text-muted-foreground">Nama</th>
                                        <th class="pb-2 font-medium text-muted-foreground">Paket</th>
                                        <th class="pb-2 font-medium text-muted-foreground">Status</th>
                                        <th class="pb-2 font-medium text-muted-foreground text-right">Users</th>
                                        <th class="pb-2 font-medium text-muted-foreground text-right">Pools</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each tenants as tenant}
                                        <tr class="border-b last:border-0">
                                            <td class="py-2">
                                                <div class="font-medium">{tenant.name}</div>
                                                <div class="text-xs text-muted-foreground">{tenant.slug}</div>
                                            </td>
                                            <td class="py-2">
                                                <Badge variant="outline">{tenant.plan_name}</Badge>
                                            </td>
                                            <td class="py-2">
                                                <Badge variant={statusBadge(tenant.subscription_status).variant}>
                                                    {statusBadge(tenant.subscription_status).label}
                                                </Badge>
                                            </td>
                                            <td class="py-2 text-right">{tenant.user_count}</td>
                                            <td class="py-2 text-right">{tenant.pool_count}</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    {:else}
                        <p class="text-sm text-muted-foreground">Belum ada tenant terdaftar.</p>
                    {/if}
                </CardContent>
            </Card>
        </div>

        <!-- Side Panel: Expiring + Recent Signups -->
        <div class="space-y-6">
            <!-- Expiring Soon -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">⏳ Expiring Minggu Ini</CardTitle>
                </CardHeader>
                <CardContent>
                    {#if expiringSoon.length > 0}
                        <div class="space-y-3">
                            {#each expiringSoon as item}
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <div class="font-medium">{item.tenant_name}</div>
                                        <div class="text-xs text-muted-foreground">{item.plan_name} · {item.status}</div>
                                    </div>
                                    <Badge variant={item.days_left <= 2 ? 'destructive' : 'outline'}>
                                        {item.days_left}d left
                                    </Badge>
                                </div>
                            {/each}
                        </div>
                    {:else}
                        <p class="text-sm text-muted-foreground">Tidak ada yang expiring dalam 7 hari.</p>
                    {/if}
                </CardContent>
            </Card>

            <!-- Recent Signups -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">🆕 Signup Bulan Ini</CardTitle>
                </CardHeader>
                <CardContent>
                    {#if recentSignups.length > 0}
                        <div class="space-y-3">
                            {#each recentSignups as item}
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <div class="font-medium">{item.name}</div>
                                        <div class="text-xs text-muted-foreground">{item.slug}</div>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{daysAgo(item.created_at)}</span>
                                </div>
                            {/each}
                        </div>
                    {:else}
                        <p class="text-sm text-muted-foreground">Belum ada signup bulan ini.</p>
                    {/if}
                </CardContent>
            </Card>
        </div>
    </div>
</div>
