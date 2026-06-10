<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { ArrowRight, BarChart3, BusFront, Check, CreditCard, LayoutGrid, MapPin, Package, QrCode, Users } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import AppLogo from '@/components/AppLogo.svelte';

    type PlanFeature = { name: string; group: string; included: boolean; limit: number | null };
    type Plan = { id: number; name: string; slug: string; description: string; price_monthly: number; price_yearly: number; max_armadas: number; max_routes: number; max_users: number; max_pools: number; max_drivers: number; features: PlanFeature[] };

    const plans = $derived((page.props.plans ?? []) as Plan[]);

    function formatRupiah(v: number): string {
        if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}M`;
        return `Rp ${(v / 1_000).toFixed(0)}K`;
    }

    function goRegister(planSlug?: string) {
        const url = planSlug ? `/register?plan=${planSlug}` : '/register';
        router.visit(url);
    }
</script>

<svelte:head>
    <title>Qbus — Sistem Operasional Travel</title>
    <meta name="description" content="Kelola booking seat, carter, bagasi, pembayaran, dan laporan dalam satu dashboard." />
</svelte:head>

<div class="min-h-screen bg-background">
    <!-- Nav -->
    <nav class="border-b">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <AppLogo class="h-8 w-auto" />
            </a>
            <div class="flex items-center gap-3">
                <a href="/pricing" class="text-sm text-muted-foreground hover:text-foreground">Paket</a>
                <a href="/login" class="text-sm text-muted-foreground hover:text-foreground">Login</a>
                <Button size="sm" onclick={() => goRegister()}>Daftar Gratis</Button>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-4xl mx-auto px-4 py-16 md:py-24 text-center">
        <Badge variant="secondary" class="mb-4">Trial 14 Hari · Batal Kapan Saja</Badge>
        <h1 class="text-4xl md:text-5xl font-bold tracking-tight leading-tight">
            Sistem Operasional Travel<br />dalam <span class="text-primary">Satu Dashboard</span>
        </h1>
        <p class="text-lg text-muted-foreground mt-4 max-w-2xl mx-auto">
            Booking seat, carter, bagasi, pembayaran, dan laporan pendapatan — semua dalam satu aplikasi.
            Dibuat untuk driver dan pemilik travel kecil.
        </p>
        <div class="flex gap-3 justify-center mt-8">
            <Button size="lg" onclick={() => goRegister()}>
                Mulai Trial Gratis <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
            <Button size="lg" variant="outline" href="/pricing">
                Lihat Paket
            </Button>
        </div>
    </section>

    <!-- Plans -->
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold text-center mb-8">Pilih Paket Anda</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {#each plans as plan}
                <Card class={plan.slug === 'pro' ? 'border-primary ring-1 ring-primary/20' : ''}>
                    {#if plan.slug === 'pro'}
                        <div class="bg-primary text-primary-foreground text-center text-xs font-semibold py-1 rounded-t-xl">POPULER</div>
                    {/if}
                    <CardHeader>
                        <CardTitle>{plan.name}</CardTitle>
                        <CardDescription>{plan.description}</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <span class="text-3xl font-bold">{formatRupiah(plan.price_monthly)}</span>
                            <span class="text-muted-foreground">/bulan</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2"><BusFront class="h-4 w-4 text-muted-foreground" /> {plan.max_armadas > 0 ? `${plan.max_armadas} Armada` : 'Unlimited Armada'}</div>
                            <div class="flex items-center gap-2"><MapPin class="h-4 w-4 text-muted-foreground" /> {plan.max_routes > 0 ? `${plan.max_routes} Rute` : 'Unlimited Rute'}</div>
                            <div class="flex items-center gap-2"><Users class="h-4 w-4 text-muted-foreground" /> {plan.max_users > 0 ? `${plan.max_users} User` : `${plan.max_users} User`}</div>
                            <div class="flex items-center gap-2"><CreditCard class="h-4 w-4 text-muted-foreground" /> Pembayaran Terpadu</div>
                        </div>
                        <Button class="w-full" variant={plan.slug === 'pro' ? 'default' : 'outline'} onclick={() => goRegister(plan.slug)}>
                            Pilih {plan.name}
                        </Button>
                        <p class="text-xs text-center text-muted-foreground mt-2">✓ Free Trial 14 Hari</p>
                    </CardContent>
                </Card>
            {/each}
        </div>
    </section>

    <!-- Features -->
    <section class="bg-muted/50 py-16">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-center mb-10">Fitur Lengkap</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <BusFront class="h-10 w-10 text-primary mx-auto mb-3" />
                    <h3 class="font-semibold">Booking Seat</h3>
                    <p class="text-sm text-muted-foreground mt-1">Atur seat, hindari double booking, cetak tiket.</p>
                </div>
                <div class="text-center">
                    <Package class="h-10 w-10 text-primary mx-auto mb-3" />
                    <h3 class="font-semibold">Bagasi</h3>
                    <p class="text-sm text-muted-foreground mt-1">Catat kiriman barang, tracking status, cetak resi.</p>
                </div>
                <div class="text-center">
                    <CreditCard class="h-10 w-10 text-primary mx-auto mb-3" />
                    <h3 class="font-semibold">Pembayaran</h3>
                    <p class="text-sm text-muted-foreground mt-1">Pantau lunas/DP/belum bayar di satu tempat.</p>
                </div>
                <div class="text-center">
                    <BarChart3 class="h-10 w-10 text-primary mx-auto mb-3" />
                    <h3 class="font-semibold">Laporan</h3>
                    <p class="text-sm text-muted-foreground mt-1">Pendapatan harian, bulanan, margin, target.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="max-w-2xl mx-auto px-4 py-16 text-center">
        <h2 class="text-2xl font-bold">Siap Mulai?</h2>
        <p class="text-muted-foreground mt-2">Daftar sekarang, gratis trial 14 hari. Batal kapan saja.</p>
        <Button size="lg" class="mt-6" onclick={() => goRegister()}>
            Daftar Gratis <ArrowRight class="ml-2 h-4 w-4" />
        </Button>
    </section>

    <!-- Footer -->
    <footer class="border-t py-8 text-center text-sm text-muted-foreground">
        &copy; 2026 Qbus. Sistem operasional travel untuk Indonesia.
    </footer>
</div>
