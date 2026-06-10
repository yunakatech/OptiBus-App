<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Langganan', href: '/subscription' }],
    };
</script>

<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { Check, CreditCard, History, Receipt, ShieldAlert, Zap } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

    type TenantSub = { tenant_id: number; tenant_name: string; plan_id: number; plan_name: string; plan_slug: string; subscription_status: string; trial_ends_at: string | null; ends_at: string | null };
    type Invoice = { id: number; invoice_number: string; amount: number; status: string; due_date: string; paid_at: string | null; payment_method: string; created_at: string };
    type Plan = { id: number; name: string; slug: string; price_monthly: number; price_yearly: number; description: string };

    const tenantSub = $derived((page.props.tenant_subscription ?? null) as TenantSub | null);
    const invoices = $derived((page.props.invoices ?? []) as Invoice[]);
    const currentPlan = $derived((page.props.current_plan ?? null) as Plan | null);
    const plans = $derived((page.props.plans ?? []) as Plan[]);

    function formatRupiah(v: number): string {
        return `Rp ${v.toLocaleString('id-ID')}`;
    }

    function statusBadge(status: string) {
        const m: Record<string, { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string }> = {
            trial: { variant: 'secondary', label: 'Trial' }, active: { variant: 'default', label: 'Active' },
            past_due: { variant: 'outline', label: 'Past Due' }, suspended: { variant: 'destructive', label: 'Suspended' },
            canceled: { variant: 'destructive', label: 'Canceled' }, expired: { variant: 'outline', label: 'Expired' },
        };
        return m[status] ?? { variant: 'outline', label: status };
    }

    function invoiceStatusBadge(status: string) {
        const m: Record<string, { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string }> = {
            pending: { variant: 'secondary', label: 'Pending' }, paid: { variant: 'default', label: 'Lunas' },
            overdue: { variant: 'destructive', label: 'Overdue' }, failed: { variant: 'destructive', label: 'Gagal' },
        };
        return m[status] ?? { variant: 'outline', label: status };
    }

    async function payInvoice(invoiceId: number) {
        const res = await fetch(`/api/subscription/pay/${invoiceId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' },
        });
        const data = await res.json();
        if (data?.success) {
            if (data.mode === 'manual') {
                alert('Silakan transfer ke rekening:\nBCA 1234567890 a.n. Qbus Indonesia\n\nJumlah: ' + formatRupiah(invoices.find(i => i.id === invoiceId)?.amount ?? 0));
            } else if (data.token) {
                // Midtrans Snap — open in new window or redirect
                (window as any).snap?.pay?.(data.token, {
                    onSuccess: () => window.location.reload(),
                    onPending: () => window.location.reload(),
                    onError: () => window.location.reload(),
                });
            }
        } else {
            alert(data?.error || 'Gagal memproses pembayaran.');
        }
    }
</script>

<AppHead title="Langganan" />

<div class="space-y-6 pb-8 max-w-2xl mx-auto">
    <div>
        <h1 class="text-2xl font-bold">Langganan Anda</h1>
        <p class="text-muted-foreground mt-1">Kelola paket dan pembayaran</p>
    </div>

    {#if !tenantSub}
        <Card>
            <CardContent class="py-8 text-center">
                <ShieldAlert class="mx-auto h-10 w-10 text-muted-foreground mb-3" />
                <p class="text-muted-foreground">Tidak ada data langganan.</p>
            </CardContent>
        </Card>
    {:else}
        <!-- Current Plan -->
        <Card class="border-primary/30">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <Badge variant={statusBadge(tenantSub.subscription_status).variant}>{statusBadge(tenantSub.subscription_status).label}</Badge>
                </div>
                <CardTitle class="text-2xl mt-2">{currentPlan?.name ?? tenantSub.plan_name}</CardTitle>
                <CardDescription>{currentPlan?.description ?? ''}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-bold">{formatRupiah(currentPlan?.price_monthly ?? 0)}</span>
                    <span class="text-muted-foreground">/bulan</span>
                </div>
                <div class="text-sm text-muted-foreground mt-1">
                    {formatRupiah(currentPlan?.price_yearly ?? 0)}/tahun
                </div>
                {#if tenantSub.subscription_status === 'trial'}
                    <p class="text-sm text-amber-600 mt-3">
                        Trial berakhir: {tenantSub.trial_ends_at ? new Date(tenantSub.trial_ends_at).toLocaleDateString('id-ID') : '—'}
                    </p>
                {:else if tenantSub.ends_at}
                    <p class="text-sm text-muted-foreground mt-3">
                        Periode: {new Date(tenantSub.ends_at).toLocaleDateString('id-ID')}
                    </p>
                {/if}
            </CardContent>
        </Card>

        <!-- Plans (upgrade) -->
        <Card>
            <CardHeader><CardTitle class="text-lg">Paket Tersedia</CardTitle></CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {#each plans as plan}
                        <div class="border rounded-lg p-4 {plan.slug === currentPlan?.slug ? 'border-primary bg-primary/5' : ''}">
                            <div class="font-semibold">{plan.name}</div>
                            <div class="text-lg font-bold mt-1">{formatRupiah(plan.price_monthly)}<span class="text-xs font-normal text-muted-foreground">/bln</span></div>
                            <p class="text-xs text-muted-foreground mt-2">{plan.description}</p>
                            {#if plan.slug !== currentPlan?.slug}
                                <Button size="sm" variant="outline" class="mt-3 w-full" disabled>
                                    {plan.price_monthly > (currentPlan?.price_monthly ?? 0) ? 'Upgrade' : 'Downgrade'}
                                </Button>
                            {:else}
                                <Badge variant="default" class="mt-3 w-full justify-center">Aktif</Badge>
                            {/if}
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>

        <!-- Invoice History -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg flex items-center gap-2"><Receipt class="h-5 w-5" /> Riwayat Invoice</CardTitle>
            </CardHeader>
            <CardContent>
                {#if invoices.length > 0}
                    <div class="space-y-2">
                        {#each invoices as inv}
                            <div class="flex items-center justify-between text-sm border-b pb-2 last:border-0">
                                <div>
                                    <div class="font-medium">{inv.invoice_number}</div>
                                    <div class="text-xs text-muted-foreground">Jatuh tempo: {new Date(inv.due_date).toLocaleDateString('id-ID')}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium">{formatRupiah(inv.amount)}</div>
                                    {#if inv.status === 'pending'}
                                        <Button size="sm" variant="outline" class="mt-1 text-xs" onclick={() => payInvoice(inv.id)}>
                                            <CreditCard class="h-3 w-3 mr-1" /> Bayar
                                        </Button>
                                    {:else}
                                        <Badge variant={invoiceStatusBadge(inv.status).variant} class="text-[10px]">{invoiceStatusBadge(inv.status).label}</Badge>
                                    {/if}
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground text-center py-4">Belum ada invoice.</p>
                {/if}
            </CardContent>
        </Card>
    {/if}
</div>
