<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/solo/dashboard' },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { BusFront, CircleDollarSign, CreditCard, Package, Phone, Plus, Wallet } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

    type Booking = { name: string; phone: string; seat: string; rute: string; jam: string; pickup_point: string; pembayaran: string; price: number };
    type Charter = { name: string; phone: string; start_date: string; end_date: string; departure_time: string; pickup_point: string; drop_point: string; payment_status: string; price: number };

    const stats = $derived((page.props.stats ?? {}) as Record<string, number>);
    const todayBookings = $derived((page.props.todayBookings ?? []) as Booking[]);
    const upcomingCharters = $derived((page.props.upcomingCharters ?? []) as Charter[]);
    const todayLabel = $derived((page.props.todayLabel ?? '') as string);

    function formatRupiah(v: number): string {
        return `Rp ${v.toLocaleString('id-ID')}`;
    }

    function paymentBadge(status: string): { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string } {
        const s = status.toLowerCase();
        if (s.includes('lunas') || s.includes('paid')) return { variant: 'default', label: 'Lunas' };
        if (s.includes('dp')) return { variant: 'secondary', label: 'DP' };
        return { variant: 'destructive', label: 'Belum' };
    }

    function waLink(phone: string, name: string): string {
        return `https://wa.me/${phone.replace(/[^0-9]/g, '')}`;
    }
</script>

<AppHead title="Dashboard Solo" />

<div class="space-y-4 pb-8 max-w-lg mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold">Dashboard</h1>
            <p class="text-sm text-muted-foreground">{todayLabel}</p>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="grid grid-cols-3 gap-2">
        <Button variant="outline" class="flex-col gap-1 py-3 h-auto" href="/bookings">
            <BusFront class="h-5 w-5" />
            <span class="text-xs">Order</span>
        </Button>
        <Button variant="outline" class="flex-col gap-1 py-3 h-auto" href="/charters/form">
            <Wallet class="h-5 w-5" />
            <span class="text-xs">Carter</span>
        </Button>
        <Button variant="outline" class="flex-col gap-1 py-3 h-auto" href="/luggages/form">
            <Package class="h-5 w-5" />
            <span class="text-xs">Barang</span>
        </Button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3">
        <Card>
            <CardHeader class="pb-1"><CardTitle class="text-xs text-muted-foreground">Order Hari Ini</CardTitle></CardHeader>
            <CardContent><div class="text-xl font-bold">{stats.bookings_today ?? 0}</div></CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-1"><CardTitle class="text-xs text-muted-foreground">Pendapatan Hari Ini</CardTitle></CardHeader>
            <CardContent><div class="text-xl font-bold">{formatRupiah(stats.revenue_today ?? 0)}</div></CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-1"><CardTitle class="text-xs text-muted-foreground">Pendapatan Bulan Ini</CardTitle></CardHeader>
            <CardContent><div class="text-lg font-bold">{formatRupiah(stats.revenue_month ?? 0)}</div></CardContent>
        </Card>
        <Card>
            <CardHeader class="pb-1"><CardTitle class="text-xs text-muted-foreground">Belum Lunas</CardTitle></CardHeader>
            <CardContent><div class="text-lg font-bold text-destructive">{formatRupiah(stats.unpaid_total ?? 0)}</div></CardContent>
        </Card>
    </div>

    <!-- Today's bookings -->
    <Card>
        <CardHeader class="pb-2"><CardTitle class="text-sm">Order Hari Ini {todayBookings.length > 0 ? `(${todayBookings.length})` : ''}</CardTitle></CardHeader>
        <CardContent>
            {#if todayBookings.length > 0}
                <div class="space-y-2">
                    {#each todayBookings as b}
                        <div class="flex items-start justify-between text-sm border-b pb-2 last:border-0">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium flex items-center gap-1">
                                    {b.name}
                                    <a href={waLink(b.phone, b.name)} target="_blank" class="text-green-600"><Phone class="h-3 w-3" /></a>
                                </div>
                                <div class="text-xs text-muted-foreground">{b.rute} · {b.jam} · Seat {b.seat}</div>
                                {#if b.pickup_point}
                                    <div class="text-xs text-muted-foreground">Jemput: {b.pickup_point}</div>
                                {/if}
                            </div>
                            <div class="text-right shrink-0">
                                <Badge variant={paymentBadge(b.pembayaran).variant} class="text-[10px]">{paymentBadge(b.pembayaran).label}</Badge>
                                <div class="text-xs font-medium mt-0.5">{formatRupiah(b.price)}</div>
                            </div>
                        </div>
                    {/each}
                </div>
            {:else}
                <p class="text-sm text-muted-foreground text-center py-4">Belum ada order hari ini.</p>
            {/if}
        </CardContent>
    </Card>

    <!-- Upcoming charters -->
    {#if upcomingCharters.length > 0}
        <Card>
            <CardHeader class="pb-2"><CardTitle class="text-sm">Carter Minggu Ini</CardTitle></CardHeader>
            <CardContent>
                <div class="space-y-2">
                    {#each upcomingCharters as c}
                        <div class="text-sm border-b pb-2 last:border-0">
                            <div class="font-medium">{c.name}</div>
                            <div class="text-xs text-muted-foreground">
                                {new Date(c.start_date).toLocaleDateString('id-ID')} — {new Date(c.end_date).toLocaleDateString('id-ID')}
                                · {c.pickup_point} → {c.drop_point}
                            </div>
                            <div class="flex justify-between mt-1">
                                <Badge variant={c.payment_status === 'Lunas' ? 'default' : 'secondary'} class="text-[10px]">{c.payment_status}</Badge>
                                <span class="text-xs font-medium">{formatRupiah(c.price)}</span>
                            </div>
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>
    {/if}
</div>
