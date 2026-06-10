<script module lang="ts">
    export const layout = {
        title: 'Daftar Akun',
        description: 'Pilih paket dan isi data travel Anda untuk memulai trial gratis.',
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import { Check, ChevronDown } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';
    import { store } from '@/routes/register';

    let { passwordRules = '' }: { passwordRules?: string } = $props();

    const plans = $derived((page.props.plans ?? []) as Array<{ id: number; name: string; slug: string; price_monthly: number; description: string }>);
    const routes = $derived((page.props.routes ?? []) as Array<{ id: number; name: string; label: string }>);

    let selectedPlan = $state('starter');
    let showPlanSelector = $state(false);
    let showRouteSelector = $state(false);
    let selectedRouteId = $state(0);
    let selectedRouteLabel = $state('');

    onMount(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const planParam = urlParams.get('plan');
        if (planParam && plans.some(p => p.slug === planParam)) {
            selectedPlan = planParam;
        }
    });

    const currentPlan = $derived(plans.find(p => p.slug === selectedPlan));
    const selectedRouteDisplay = $derived(
        selectedRouteId > 0
            ? (routes.find(r => r.id === selectedRouteId)?.label ?? 'Pilih rute')
            : 'Pilih rute'
    );

    function formatRupiah(v: number): string {
        if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}M`;
        return `Rp ${(v / 1_000).toFixed(0)}K`;
    }
</script>

<AppHead title="Daftar" />

<Form
    {...store.form()}
    resetOnSuccess={['password', 'password_confirmation']}
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-6">
            <!-- Plan Selector -->
            <div class="grid gap-2">
                <Label>Paket Langganan</Label>
                <div class="relative">
                    <button type="button" onclick={() => showPlanSelector = !showPlanSelector}
                        class="w-full flex items-center justify-between border rounded-lg px-4 py-3 text-left hover:bg-muted/20"
                    >
                        <div>
                            <div class="font-medium">{currentPlan?.name ?? 'Pilih Paket'}</div>
                            <div class="text-sm text-muted-foreground">{currentPlan ? `${formatRupiah(currentPlan.price_monthly)}/bulan · ${currentPlan.description}` : 'Klik untuk pilih'}</div>
                        </div>
                        <ChevronDown class="h-4 w-4 text-muted-foreground" />
                    </button>
                    {#if showPlanSelector}
                        <div class="absolute z-10 mt-1 w-full border rounded-lg bg-background shadow-lg">
                            {#each plans as plan}
                                <button type="button" onclick={() => { selectedPlan = plan.slug; showPlanSelector = false; }}
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-muted/20 first:rounded-t-lg last:rounded-b-lg {selectedPlan === plan.slug ? 'bg-primary/5' : ''}"
                                >
                                    <div>
                                        <div class="font-medium flex items-center gap-2">{plan.name}
                                            {#if plan.slug === 'pro'}<Badge variant="default" class="text-[10px] py-0">Populer</Badge>{/if}
                                        </div>
                                        <div class="text-sm text-muted-foreground">{plan.description}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">{formatRupiah(plan.price_monthly)}<span class="text-xs text-muted-foreground">/bln</span></div>
                                        {#if selectedPlan === plan.slug}<Check class="h-4 w-4 text-primary ml-auto mt-1" />{/if}
                                    </div>
                                </button>
                            {/each}
                        </div>
                    {/if}
                </div>
                <input type="hidden" name="plan" value={selectedPlan} />
                {#if currentPlan}
                    <p class="text-xs text-muted-foreground mt-1">Trial 14 hari gratis. Setelah trial: {formatRupiah(currentPlan.price_monthly)}/bulan.</p>
                {/if}
            </div>

            <!-- Travel / Pool Name (required for config) -->
            <div class="grid gap-2">
                <Label for="travel_name">Nama Travel / Pool <span class="text-destructive">*</span></Label>
                <Input id="travel_name" type="text" required name="travel_name" placeholder="Contoh: Mandiri Trans" />
                <p class="text-xs text-muted-foreground">Nama usaha travel Anda. Bisa diganti nanti.</p>
                <InputError message={errors.travel_name} />
            </div>

            <!-- Phone (required) -->
            <div class="grid gap-2">
                <Label for="phone">Nomor WhatsApp <span class="text-destructive">*</span></Label>
                <Input id="phone" type="tel" required name="phone" placeholder="0852xxxx" />
                <p class="text-xs text-muted-foreground">Untuk konfirmasi booking dan reminder.</p>
                <InputError message={errors.phone} />
            </div>

            <!-- Route selection (required for initial config) -->
            <div class="grid gap-2">
                <Label>Rute Utama <span class="text-destructive">*</span></Label>
                <div class="relative">
                    <button type="button" onclick={() => showRouteSelector = !showRouteSelector}
                        class="w-full flex items-center justify-between border rounded-lg px-4 py-3 text-left hover:bg-muted/20"
                    >
                        <span class={selectedRouteId > 0 ? '' : 'text-muted-foreground'}>{selectedRouteDisplay}</span>
                        <ChevronDown class="h-4 w-4 text-muted-foreground" />
                    </button>
                    {#if showRouteSelector}
                        <div class="absolute z-10 mt-1 w-full max-h-48 overflow-y-auto border rounded-lg bg-background shadow-lg">
                            {#each routes as route}
                                <button type="button" onclick={() => { selectedRouteId = route.id; selectedRouteLabel = route.label; showRouteSelector = false; }}
                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-muted/20 first:rounded-t-lg last:rounded-b-lg {selectedRouteId === route.id ? 'bg-primary/5 font-medium' : ''}"
                                >
                                    {route.label}
                                </button>
                            {/each}
                        </div>
                    {/if}
                </div>
                <input type="hidden" name="route_id" value={selectedRouteId} />
                <p class="text-xs text-muted-foreground">Rute utama operasional travel Anda.</p>
                <InputError message={errors.route_id} />
            </div>

            <hr />

            <!-- Account Info -->
            <div class="grid gap-2">
                <Label for="name">Nama Anda <span class="text-destructive">*</span></Label>
                <Input id="name" type="text" required autocomplete="name" name="name" placeholder="Nama lengkap" />
                <InputError message={errors.name} />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email <span class="text-destructive">*</span></Label>
                <Input id="email" type="email" required autocomplete="email" name="email" placeholder="email@example.com" />
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password <span class="text-destructive">*</span></Label>
                <PasswordInput id="password" required autocomplete="new-password" name="password" placeholder="Min. 8 karakter" passwordrules={passwordRules} />
                <InputError message={errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi Password <span class="text-destructive">*</span></Label>
                <PasswordInput id="password_confirmation" required autocomplete="new-password" name="password_confirmation" placeholder="Ulangi password" passwordrules={passwordRules} />
                <InputError message={errors.password_confirmation} />
            </div>

            <Button type="submit" class="w-full" disabled={processing}>
                {#if processing}<Spinner />{/if}
                Daftar — Trial 14 Hari Gratis
            </Button>

            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center"><span class="w-full border-t"></span></div>
                <div class="relative flex justify-center text-xs uppercase"><span class="bg-card px-2 text-muted-foreground">Atau</span></div>
            </div>

            <a href="/auth/google/redirect" class="w-full">
                <Button type="button" variant="outline" class="w-full gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Daftar dengan Google
                </Button>
            </a>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <TextLink href={login()} class="underline underline-offset-4">Login</TextLink>
        </div>
    {/snippet}
</Form>
