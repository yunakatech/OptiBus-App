<script module lang="ts">
    export const layout = {
        title: 'Paket & Harga',
        description: 'Pilih paket yang sesuai dengan kebutuhan operasional travel Anda.',
    };
</script>

<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { ArrowRight, Check, X } from 'lucide-svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

    type PlanFeature = { name: string; group: string; included: boolean; limit: number | null };
    type Plan = { id: number; name: string; slug: string; description: string; price_monthly: number; price_yearly: number; max_armadas: number; max_routes: number; max_users: number; max_pools: number; max_drivers: number; features: PlanFeature[] };

    const plans = $derived((page.props.plans ?? []) as Plan[]);

    function formatRupiah(v: number): string {
        if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}M`;
        return `Rp ${(v / 1_000).toFixed(0)}K`;
    }

    // Build a flat list of unique feature names across all plans for comparison table
    const allFeatureNames = $derived(
        [...new Set(plans.flatMap(p => p.features?.map(f => f.name) ?? []))]
            .filter(Boolean)
    );
</script>

<svelte:head>
    <title>Paket & Harga — Qbus</title>
</svelte:head>

<div class="space-y-8">
    <!-- Header -->
    <div class="text-center py-6">
        <h1 class="text-2xl font-bold">Paket & Harga</h1>
        <p class="text-muted-foreground mt-2">Trial 14 hari gratis. Upgrade kapan saja.</p>
    </div>

    <!-- Plan Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {#each plans as plan}
            <Card class={plan.slug === 'pro' ? 'border-primary ring-1 ring-primary/20 relative' : ''}>
                {#if plan.slug === 'pro'}
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <Badge class="shadow-md">Paling Populer</Badge>
                    </div>
                {/if}
                <CardHeader>
                    <CardTitle class="text-xl">{plan.name}</CardTitle>
                    <CardDescription>{plan.description}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold">{formatRupiah(plan.price_monthly)}</span>
                        <span class="text-muted-foreground text-sm">/bulan</span>
                    </div>
                    <div class="text-sm text-muted-foreground">{formatRupiah(plan.price_yearly)}/tahun</div>

                    <div class="border-t pt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-muted-foreground">Armada</span> <span class="font-medium">{plan.max_armadas > 0 ? plan.max_armadas : 'Unlimited'}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Rute</span> <span class="font-medium">{plan.max_routes > 0 ? plan.max_routes : 'Unlimited'}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">User</span> <span class="font-medium">{plan.max_users}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Pool/Cabang</span> <span class="font-medium">{plan.max_pools}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Driver</span> <span class="font-medium">{plan.max_drivers > 0 ? plan.max_drivers : 'Unlimited'}</span></div>
                    </div>

                    <a href={`/register?plan=${plan.slug}`} class="block">
                        <Button class="w-full" variant={plan.slug === 'pro' ? 'default' : 'outline'}>
                            Daftar {plan.name} <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                    </a>
                </CardContent>
            </Card>
        {/each}
    </div>

    <!-- Feature Comparison Table -->
    <Card>
        <CardHeader>
            <CardTitle>Perbandingan Fitur</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/30">
                            <th class="text-left px-4 py-3 font-medium">Fitur</th>
                            {#each plans as plan}
                                <th class="text-center px-4 py-3 font-medium">{plan.name}</th>
                            {/each}
                        </tr>
                    </thead>
                    <tbody>
                        {#each allFeatureNames as featureName}
                            <tr class="border-b last:border-0">
                                <td class="px-4 py-2.5">{featureName}</td>
                                {#each plans as plan}
                                    {@const f = plan.features?.find(f => f.name === featureName)}
                                    <td class="text-center px-4 py-2.5">
                                        {#if f?.included}
                                            <Check class="h-4 w-4 text-green-500 mx-auto" />
                                        {:else}
                                            <X class="h-4 w-4 text-muted-foreground mx-auto" />
                                        {/if}
                                    </td>
                                {/each}
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>

    <!-- Bottom CTA -->
    <div class="text-center py-8">
        <p class="text-muted-foreground mb-4">Butuh bantuan memilih? Hubungi kami di WhatsApp.</p>
        <Button variant="outline" href="/login">Sudah punya akun? Login</Button>
    </div>
</div>
