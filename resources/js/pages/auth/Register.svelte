<script module lang="ts">
    export const layout = {
        title: 'Daftar Akun',
        description: 'Daftar trial atau lanjutkan pembayaran paket OptiBus.',
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import {
        ArrowRight,
        Check,
        CheckCircle2,
        ChevronDown,
        CreditCard,
        Route,
        ShieldCheck,
        Timer,
    } from 'lucide-svelte';
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

    type Plan = {
        id: number;
        name: string;
        slug: string;
        price_monthly: number;
        description: string;
    };
    type RegistrationIntent = 'trial' | 'paid' | 'payment';

    let {
        passwordRules = '',
        selectedPlan: selectedPlanProp = 'starter',
        registrationIntent: registrationIntentProp = 'trial',
    }: {
        passwordRules?: string;
        selectedPlan?: string;
        registrationIntent?: RegistrationIntent;
    } = $props();

    const plans = $derived((page.props.plans ?? []) as Plan[]);

    // svelte-ignore state_referenced_locally
    let selectedPlan = $state(selectedPlanProp || 'starter');
    // svelte-ignore state_referenced_locally
    let registrationIntent = $state<RegistrationIntent>(
        registrationIntentProp === 'paid' ||
            registrationIntentProp === 'payment'
            ? 'paid'
            : 'trial',
    );
    let showPlanSelector = $state(false);

    onMount(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const intentParam = urlParams.get('intent');
        if (
            intentParam === 'trial' ||
            intentParam === 'paid' ||
            intentParam === 'payment'
        ) {
            registrationIntent =
                intentParam === 'payment' ? 'paid' : intentParam;
        }

        const planParam = urlParams.get('plan');
        if (registrationIntent === 'trial') {
            selectedPlan = 'starter';
            return;
        }

        if (planParam && plans.some((p) => p.slug === planParam)) {
            selectedPlan = planParam;
        } else if (!plans.some((p) => p.slug === selectedPlan)) {
            selectedPlan = plans[0]?.slug ?? 'starter';
        }
    });

    const currentPlan = $derived(plans.find((p) => p.slug === selectedPlan));
    const isTrialFlow = $derived(registrationIntent === 'trial');
    const isPaymentFlow = $derived(
        registrationIntent === 'paid' || registrationIntent === 'payment',
    );
    const submitLabel = $derived(
        isTrialFlow ? 'Mulai Trial Starter' : 'Daftar & Lanjut Pembayaran',
    );
    const flowLabel = $derived(
        isTrialFlow ? 'Trial 14 hari' : 'Checkout paket',
    );
    const flowTitle = $derived(
        isTrialFlow
            ? 'Mulai dari Starter trial'
            : `Aktifkan paket ${currentPlan?.name ?? 'pilihan'}`,
    );
    const flowDescription = $derived(
        isTrialFlow
            ? 'Akun dibuat dengan paket Starter trial. Setelah masuk, upgrade dapat dilakukan dari menu SaaS.'
            : 'Setelah akun dibuat, invoice pending akan tersedia di halaman Langganan untuk dibayar via Mayar.',
    );
    const planHelpText = $derived.by(() => {
        if (!currentPlan) return '';

        return isTrialFlow
            ? 'Starter trial dikunci agar pendaftaran trial tidak bercampur dengan checkout paket.'
            : `Tagihan awal: ${formatRupiah(currentPlan.price_monthly)}/bulan.`;
    });

    function formatRupiah(v: number): string {
        if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}M`;
        return `Rp ${(v / 1_000).toFixed(0)}K`;
    }
</script>

<AppHead title="Daftar" />

<Form
    {...store.form()}
    resetOnSuccess={['password', 'password_confirmation']}
    class="overflow-hidden rounded-3xl border border-border/70 bg-card shadow-sm"
>
    {#snippet children({ errors, processing })}
        <div
            class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(240,253,250,0.96),rgba(255,255,255,0.98))] p-4 dark:bg-[linear-gradient(135deg,rgba(6,78,59,0.22),rgba(15,23,42,0.98))] sm:p-5"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="space-y-2">
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase text-emerald-700 dark:border-emerald-400/20 dark:bg-slate-950/40 dark:text-emerald-200"
                    >
                        {#if isTrialFlow}<Timer
                                class="h-3.5 w-3.5"
                            />{:else}<CreditCard class="h-3.5 w-3.5" />{/if}
                        {flowLabel}
                    </div>
                    <div>
                        <h2
                            class="text-xl font-semibold tracking-tight text-foreground"
                        >
                            {flowTitle}
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-muted-foreground">
                            {flowDescription}
                        </p>
                    </div>
                </div>
                <div
                    class="grid grid-cols-3 gap-1.5 rounded-2xl border border-white/80 bg-white/75 p-1.5 text-[10px] font-medium text-muted-foreground shadow-sm dark:border-white/10 dark:bg-slate-950/35"
                >
                    <div
                        class="rounded-xl bg-emerald-50 px-2 py-2 text-center text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-100"
                    >
                        <ShieldCheck class="mx-auto mb-1 h-3.5 w-3.5" />
                        Tenant
                    </div>
                    <div
                        class="rounded-xl bg-sky-50 px-2 py-2 text-center text-sky-700 dark:bg-sky-400/10 dark:text-sky-100"
                    >
                        <Route class="mx-auto mb-1 h-3.5 w-3.5" />
                        Rute
                    </div>
                    <div
                        class="rounded-xl bg-amber-50 px-2 py-2 text-center text-amber-700 dark:bg-amber-400/10 dark:text-amber-100"
                    >
                        <CheckCircle2 class="mx-auto mb-1 h-3.5 w-3.5" />
                        Pool
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 p-4 sm:p-5">
            <div class="grid gap-2">
                <Label>{isTrialFlow ? 'Paket Trial' : 'Paket Langganan'}</Label>
                <div class="relative">
                    <button
                        type="button"
                        onclick={() => {
                            if (isPaymentFlow)
                                showPlanSelector = !showPlanSelector;
                        }}
                        class={`w-full rounded-2xl border px-4 py-3 text-left transition ${isPaymentFlow ? 'hover:border-primary/40 hover:bg-muted/20' : 'cursor-default bg-muted/20'}`}
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-foreground"
                                        >{currentPlan?.name ??
                                            'Pilih Paket'}</span
                                    >
                                    {#if currentPlan?.slug === 'pro'}<Badge
                                            variant="default"
                                            class="py-0 text-[10px]"
                                            >Populer</Badge
                                        >{/if}
                                </div>
                                <p
                                    class="mt-1 text-sm leading-5 text-muted-foreground"
                                >
                                    {currentPlan
                                        ? `${formatRupiah(currentPlan.price_monthly)}/bulan - ${currentPlan.description}`
                                        : 'Klik untuk pilih'}
                                </p>
                            </div>
                            {#if isPaymentFlow}
                                <ChevronDown
                                    class="mt-1 h-4 w-4 shrink-0 text-muted-foreground"
                                />
                            {/if}
                        </div>
                    </button>

                    {#if showPlanSelector && isPaymentFlow}
                        <div
                            class="absolute z-10 mt-1 w-full overflow-hidden rounded-2xl border bg-background shadow-lg"
                        >
                            {#each plans as plan}
                                <button
                                    type="button"
                                    onclick={() => {
                                        selectedPlan = plan.slug;
                                        showPlanSelector = false;
                                    }}
                                    class={`flex w-full items-start justify-between gap-3 px-4 py-3 text-left transition hover:bg-muted/30 ${selectedPlan === plan.slug ? 'bg-primary/5' : ''}`}
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="flex items-center gap-2 font-semibold"
                                        >
                                            {plan.name}
                                            {#if plan.slug === 'pro'}<Badge
                                                    variant="default"
                                                    class="py-0 text-[10px]"
                                                    >Populer</Badge
                                                >{/if}
                                        </div>
                                        <p
                                            class="mt-1 text-sm leading-5 text-muted-foreground"
                                        >
                                            {plan.description}
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="font-semibold">
                                            {formatRupiah(
                                                plan.price_monthly,
                                            )}<span
                                                class="text-xs font-normal text-muted-foreground"
                                                >/bln</span
                                            >
                                        </div>
                                        {#if selectedPlan === plan.slug}<Check
                                                class="mt-1 ml-auto h-4 w-4 text-primary"
                                            />{/if}
                                    </div>
                                </button>
                            {/each}
                        </div>
                    {/if}
                </div>
                <input type="hidden" name="plan" value={selectedPlan} />
                <input
                    type="hidden"
                    name="registration_intent"
                    value={isPaymentFlow ? 'paid' : 'trial'}
                />
                {#if planHelpText}
                    <p class="text-xs text-muted-foreground">{planHelpText}</p>
                {/if}
            </div>

            <section
                class="grid gap-4 rounded-2xl border border-border/70 p-3 sm:p-4"
            >
                <div>
                    <h3 class="text-sm font-semibold text-foreground">
                        Data Travel
                    </h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Dipakai untuk membuat tenant, pool awal, dan rute utama.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="travel_name"
                            >Nama Travel / Pool <span class="text-destructive"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="travel_name"
                            type="text"
                            required
                            name="travel_name"
                            placeholder="Contoh: Mandiri Trans"
                        />
                        <InputError message={errors.travel_name} />
                    </div>
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="phone"
                            >Nomor WhatsApp <span class="text-destructive"
                                >*</span
                            ></Label
                        >
                        <Input
                            id="phone"
                            type="tel"
                            required
                            name="phone"
                            placeholder="0852xxxx"
                        />
                        <InputError message={errors.phone} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="origin"
                            >Dari <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="origin"
                            type="text"
                            required
                            name="origin"
                            placeholder="Contoh: Pinrang"
                        />
                        <InputError message={errors.origin} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="destination"
                            >Tujuan <span class="text-destructive">*</span
                            ></Label
                        >
                        <Input
                            id="destination"
                            type="text"
                            required
                            name="destination"
                            placeholder="Contoh: Makassar"
                        />
                        <InputError message={errors.destination} />
                    </div>
                </div>
            </section>

            <section
                class="grid gap-4 rounded-2xl border border-border/70 p-3 sm:p-4"
            >
                <div>
                    <h3 class="text-sm font-semibold text-foreground">
                        Akun Owner
                    </h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Akun ini menjadi admin awal tenant.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name"
                            >Nama Anda <span class="text-destructive">*</span
                            ></Label
                        >
                        <Input
                            id="name"
                            type="text"
                            required
                            autocomplete="name"
                            name="name"
                            placeholder="Nama lengkap"
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email"
                            >Email <span class="text-destructive">*</span
                            ></Label
                        >
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
                        <Label for="password"
                            >Password <span class="text-destructive">*</span
                            ></Label
                        >
                        <PasswordInput
                            id="password"
                            required
                            autocomplete="new-password"
                            name="password"
                            placeholder="Min. 8 karakter"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password} />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Konfirmasi Password <span class="text-destructive"
                                >*</span
                            ></Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            required
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>
                </div>
            </section>

            <Button
                type="submit"
                class="h-11 w-full rounded-2xl"
                disabled={processing}
            >
                {#if processing}<Spinner />{/if}
                {submitLabel}
                {#if !processing}<ArrowRight class="ml-2 h-4 w-4" />{/if}
            </Button>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-card px-2 text-muted-foreground"
                        >Atau</span
                    >
                </div>
            </div>

            <a href={`/auth/google/redirect?intent=${isPaymentFlow ? 'paid' : 'trial'}&plan=${selectedPlan}`} class="w-full">
                <Button
                    type="button"
                    variant="outline"
                    class="h-11 w-full rounded-2xl gap-2"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24"
                        ><path
                            fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                        /><path
                            fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        /><path
                            fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        /><path
                            fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        /></svg
                    >
                    Daftar dengan Google
                </Button>
            </a>

            <div class="text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <TextLink href={login()} class="underline underline-offset-4"
                    >Login</TextLink
                >
            </div>
        </div>
    {/snippet}
</Form>
