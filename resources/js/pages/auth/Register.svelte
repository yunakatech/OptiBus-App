<script module lang="ts">
    export const layout = {
        title: 'Daftar Akun',
        description: 'Pilih paket dan isi data Anda untuk memulai trial gratis.',
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
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';
    import { store } from '@/routes/register';

    let { passwordRules }: { passwordRules: string } = $props();

    // Plan data from Inertia props (public plans)
    const plans = $derived((page.props.plans ?? []) as Array<{ id: number; name: string; slug: string; price_monthly: number; description: string }>);

    // Selected plan — default from query param ?plan=starter
    let selectedPlan = $state('starter');
    let showPlanSelector = $state(false);

    onMount(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const planParam = urlParams.get('plan');
        if (planParam && plans.some(p => p.slug === planParam)) {
            selectedPlan = planParam;
        }
    });

    const currentPlan = $derived(plans.find(p => p.slug === selectedPlan));

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
                    <button
                        type="button"
                        onclick={() => showPlanSelector = !showPlanSelector}
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
                                <button
                                    type="button"
                                    onclick={() => { selectedPlan = plan.slug; showPlanSelector = false; }}
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-muted/20 first:rounded-t-lg last:rounded-b-lg {selectedPlan === plan.slug ? 'bg-primary/5' : ''}"
                                >
                                    <div>
                                        <div class="font-medium flex items-center gap-2">
                                            {plan.name}
                                            {#if plan.slug === 'pro'}
                                                <Badge variant="default" class="text-[10px] py-0">Populer</Badge>
                                            {/if}
                                        </div>
                                        <div class="text-sm text-muted-foreground">{plan.description}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">{formatRupiah(plan.price_monthly)}<span class="text-xs text-muted-foreground">/bln</span></div>
                                        {#if selectedPlan === plan.slug}
                                            <Check class="h-4 w-4 text-primary ml-auto mt-1" />
                                        {/if}
                                    </div>
                                </button>
                            {/each}
                        </div>
                    {/if}
                </div>
                <input type="hidden" name="plan" value={selectedPlan} />
                {#if currentPlan}
                    <p class="text-xs text-muted-foreground mt-1">
                        Trial 14 hari gratis. Setelah trial, lanjutkan dengan {formatRupiah(currentPlan.price_monthly)}/bulan.
                    </p>
                {/if}
            </div>

            <div class="grid gap-2">
                <Label for="name">Nama</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autocomplete="name"
                    name="name"
                    placeholder="Nama lengkap Anda"
                />
                <InputError message={errors.name} />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    required
                    autocomplete="new-password"
                    name="password"
                    placeholder="Password"
                    passwordrules={passwordRules}
                />
                <InputError message={errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi Password</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Konfirmasi password"
                    passwordrules={passwordRules}
                />
                <InputError message={errors.password_confirmation} />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                disabled={processing}
                data-test="register-user-button"
            >
                {#if processing}<Spinner />{/if}
                Daftar — Trial 14 Hari Gratis
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <TextLink href={login()} class="underline underline-offset-4">
                Login
            </TextLink>
        </div>
    {/snippet}
</Form>
