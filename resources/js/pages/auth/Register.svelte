<script module lang="ts">
    export const layout = {
        title: 'Daftar Akun',
        description: '',
    };
</script>

<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import { ArrowRight, Check } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
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
        if (registrationIntent === 'trial' && !planParam) {
            selectedPlan = 'starter';
            return;
        }

        if (planParam && plans.some((p) => p.slug === planParam)) {
            selectedPlan = planParam;
            if (planParam !== 'starter') {
                registrationIntent = 'paid';
            }
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
        isTrialFlow ? 'Mulai Uji Coba' : 'Daftar & Lanjut Pembayaran',
    );

    const chooseTrial = () => {
        registrationIntent = 'trial';
        selectedPlan = 'starter';
    };

    const choosePlan = (planSlug: string) => {
        registrationIntent = 'paid';
        selectedPlan = planSlug;
    };

    function formatRupiah(v: number): string {
        if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}M`;
        return `Rp ${(v / 1_000).toFixed(0)}K`;
    }
</script>

<AppHead title="Daftar" />

<Form
    {...store.form()}
    resetOnSuccess={['password', 'password_confirmation']}
    class="overflow-hidden rounded-[1.4rem] border border-[#d7dfd5] bg-white/94 shadow-[0_26px_80px_-46px_rgba(16,61,58,0.85)] backdrop-blur"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-5 p-4 sm:p-5">
            <div class="grid gap-2">
                <a
                    href={`/auth/google/redirect?intent=${isPaymentFlow ? 'paid' : 'trial'}&plan=${selectedPlan}`}
                    class="group block rounded-2xl border border-[#d9ded4] bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0d7066]/45 hover:shadow-lg hover:shadow-emerald-950/10"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white shadow-sm ring-1 ring-black/5"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24"
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
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-foreground">
                                    Daftar dengan Google
                                </p>
                            </div>
                        </div>
                        <span
                            class="inline-flex h-10 items-center justify-center rounded-full bg-emerald-700 px-4 text-sm font-semibold text-white shadow-sm transition group-hover:bg-emerald-800 dark:bg-emerald-500 dark:text-emerald-950 dark:group-hover:bg-emerald-400"
                        >
                            Lanjut dengan Google
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </span>
                    </div>
                </a>
                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t"></span>
                    </div>
                    <div class="relative flex justify-center text-[11px] uppercase tracking-wide">
                        <span class="bg-card px-2 text-muted-foreground">
                            Atau daftar manual
                        </span>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Pilih akses</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            onclick={chooseTrial}
                            class={`rounded-xl border px-3 py-2 text-left transition ${isTrialFlow ? 'border-[#0d7066] bg-emerald-50 text-[#103d3a] ring-1 ring-[#0d7066]/15' : 'border-[#d9ded4] bg-white hover:border-[#0d7066]/40'}`}
                        >
                            <span class="block text-sm font-semibold"
                                >Uji coba</span
                            >
                            <span class="block text-xs text-muted-foreground"
                                >Starter 14 hari</span
                            >
                        </button>
                        <button
                            type="button"
                            onclick={() =>
                                choosePlan(currentPlan?.slug ?? plans[0]?.slug ?? 'starter')}
                            class={`rounded-xl border px-3 py-2 text-left transition ${isPaymentFlow ? 'border-[#0d7066] bg-emerald-50 text-[#103d3a] ring-1 ring-[#0d7066]/15' : 'border-[#d9ded4] bg-white hover:border-[#0d7066]/40'}`}
                        >
                            <span class="block text-sm font-semibold"
                                >Langganan</span
                            >
                            <span class="block text-xs text-muted-foreground"
                                >Pilih paket</span
                            >
                        </button>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Paket</Label>
                    <div class="grid gap-2 sm:grid-cols-3">
                        {#each plans as plan}
                            <button
                                type="button"
                                onclick={() => choosePlan(plan.slug)}
                                class={`rounded-2xl border p-3 text-left transition hover:-translate-y-0.5 hover:border-[#0d7066]/50 ${selectedPlan === plan.slug && isPaymentFlow ? 'border-[#0d7066] bg-[#eef7ef] shadow-sm ring-1 ring-[#0d7066]/15' : 'border-[#d9ded4] bg-white'}`}
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-foreground">
                                            {plan.name}
                                        </p>
                                        <p class="mt-1 text-sm font-bold text-[#103d3a]">
                                            {formatRupiah(plan.price_monthly)}
                                            <span
                                                class="text-xs font-normal text-muted-foreground"
                                                >/bln</span
                                            >
                                        </p>
                                    </div>
                                    {#if selectedPlan === plan.slug && isPaymentFlow}
                                        <span
                                            class="grid h-6 w-6 place-items-center rounded-full bg-[#103d3a] text-white"
                                        >
                                            <Check class="h-3.5 w-3.5" />
                                        </span>
                                    {/if}
                                </div>
                            </button>
                        {/each}
                    </div>
                </div>

                {#if plans.length === 0}
                    <button
                        type="button"
                        class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-left"
                        onclick={chooseTrial}
                    >
                        <span class="block text-sm font-semibold"
                            >Starter</span
                        >
                        <span class="text-xs text-muted-foreground"
                            >Uji coba 14 hari</span
                        >
                    </button>
                {/if}
                <input type="hidden" name="plan" value={selectedPlan} />
                <input
                    type="hidden"
                    name="registration_intent"
                    value={isPaymentFlow ? 'paid' : 'trial'}
                />
            </div>

            <section
                class="grid gap-4 rounded-2xl border border-[#d9ded4]/90 bg-[#fbfcf8] p-3 sm:p-4"
            >
                <div>
                    <h3 class="text-sm font-semibold text-foreground">
                        Data Travel
                    </h3>
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
                class="grid gap-4 rounded-2xl border border-[#d9ded4]/90 bg-[#fbfcf8] p-3 sm:p-4"
            >
                <div>
                    <h3 class="text-sm font-semibold text-foreground">
                        Akun Pemilik
                    </h3>
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
                            >Kata Sandi <span class="text-destructive">*</span
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
                            >Konfirmasi Kata Sandi <span class="text-destructive"
                                >*</span
                            ></Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            required
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Ulangi kata sandi"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>
                </div>
            </section>

            <Button
                type="submit"
                class="h-11 w-full rounded-xl bg-[#103d3a] text-white hover:bg-[#0b2f2c]"
                disabled={processing}
            >
                {#if processing}<Spinner />{/if}
                {submitLabel}
                {#if !processing}<ArrowRight class="ml-2 h-4 w-4" />{/if}
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <TextLink href={login()} class="underline underline-offset-4"
                    >Masuk</TextLink
                >
            </div>
        </div>
    {/snippet}
</Form>
