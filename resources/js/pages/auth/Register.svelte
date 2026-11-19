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
        isTrialFlow ? 'Buat Akun Gratis' : 'Buat Akun & Lanjut Bayar',
    );

    const chooseTrial = () => {
        registrationIntent = 'trial';
        selectedPlan = 'starter';
    };

    const choosePlan = (planSlug: string) => {
        registrationIntent = 'paid';
        selectedPlan = planSlug;
    };

    const chooseSubscription = () => {
        const defaultPlan =
            currentPlan?.slug && currentPlan.slug !== 'starter'
                ? currentPlan.slug
                : (plans.find((plan) => plan.slug !== 'starter')?.slug ??
                  plans[0]?.slug ??
                  'starter');
        choosePlan(defaultPlan);
    };

    function formatRupiah(v: number): string {
        if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)} jt`;
        return `Rp ${(v / 1_000).toFixed(0)} rb`;
    }
</script>

<AppHead title="Daftar" />

<Form
    {...store.form()}
    resetOnSuccess={['password', 'password_confirmation']}
    class="overflow-hidden border-y border-border bg-card text-sm md:[&_input]:h-11 md:[&_input]:px-3 md:[&_input]:text-sm md:[&_label]:text-sm"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-5 px-4 py-5 sm:px-6 sm:py-6">
            <div class="grid gap-2">
                <a
                    href={`/auth/google/redirect?intent=${isPaymentFlow ? 'paid' : 'trial'}&plan=${selectedPlan}`}
                    class="group block border border-border bg-transparent p-3 transition-colors hover:border-[#087F79] hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                >
                    <div
                        class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-2.5">
                            <span
                                class="grid h-10 w-10 shrink-0 place-items-center border border-border bg-white"
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
                            </span>
                            <div>
                                <p
                                    class="text-sm font-semibold text-foreground"
                                >
                                    Daftar dengan Google
                                </p>
                            </div>
                        </div>
                        <span
                            class="inline-flex min-h-10 items-center justify-center bg-[#087F79] px-3 text-xs font-semibold text-white transition-colors group-hover:bg-[#075B5B]"
                        >
                            Lanjut dengan Google
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </span>
                    </div>
                </a>
                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-border"></span>
                    </div>
                    <div
                        class="relative flex justify-center text-xs md:text-[10px] font-semibold uppercase tracking-[0.16em]"
                    >
                        <span class="bg-card px-3 text-muted-foreground">
                            Isi data akun
                        </span>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label class="text-sm font-medium text-foreground"
                        >Pilih cara memulai</Label
                    >
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            onclick={chooseTrial}
                            class={`min-h-16 border px-3 py-2.5 text-left transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary ${isTrialFlow ? 'border-[#087F79] bg-accent text-foreground' : 'border-border bg-transparent hover:bg-accent'}`}
                        >
                            <span class="block text-xs font-semibold"
                                >Uji coba</span
                            >
                            <span
                                class="mt-0.5 block text-xs text-muted-foreground"
                                >Mulai tanpa bayar</span
                            >
                        </button>
                        <button
                            type="button"
                            onclick={chooseSubscription}
                            class={`min-h-16 border px-3 py-2.5 text-left transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary ${isPaymentFlow ? 'border-[#087F79] bg-accent text-foreground' : 'border-border bg-transparent hover:bg-accent'}`}
                        >
                            <span class="block text-xs font-semibold"
                                >Langganan</span
                            >
                            <span
                                class="mt-0.5 block text-xs text-muted-foreground"
                                >Pilih paket</span
                            >
                        </button>
                    </div>
                </div>

                {#if isPaymentFlow}
                    <div class="grid gap-2">
                        <Label class="text-sm font-medium text-foreground"
                            >Pilih paket langganan</Label
                        >
                        <div class="grid gap-2 sm:grid-cols-3">
                            {#each plans as plan}
                                <button
                                    type="button"
                                    onclick={() => choosePlan(plan.slug)}
                                    class={`border p-3 text-left transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary ${selectedPlan === plan.slug ? 'border-[#087F79] bg-accent' : 'border-border bg-transparent hover:bg-accent'}`}
                                >
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-foreground"
                                            >
                                                {plan.name}
                                            </p>
                                            <p
                                                class="mt-1 text-sm font-semibold text-primary"
                                            >
                                                {formatRupiah(
                                                    plan.price_monthly,
                                                )}
                                                <span
                                                    class="text-xs font-normal text-muted-foreground"
                                                    >/ bulan</span
                                                >
                                            </p>
                                        </div>
                                        {#if selectedPlan === plan.slug}
                                            <span
                                                class="grid h-5 w-5 place-items-center bg-[#087F79] text-white"
                                            >
                                                <Check class="h-3 w-3" />
                                            </span>
                                        {/if}
                                    </div>
                                </button>
                            {/each}
                        </div>
                    </div>
                {/if}

                {#if isPaymentFlow && plans.length === 0}
                    <button
                        type="button"
                        class="min-h-14 w-full border border-[#AFC9C3] bg-accent px-3 py-2 text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                        onclick={chooseTrial}
                    >
                        <span class="block text-sm font-semibold">Starter</span>
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

            <section class="grid gap-4 border-y border-border py-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="grid gap-1.5">
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
                    <div class="grid gap-1.5">
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
                    <div class="grid gap-1.5">
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
                    <div class="grid gap-1.5">
                        <Label for="password_confirmation"
                            >Konfirmasi Kata Sandi <span
                                class="text-destructive">*</span
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
                class="h-12 w-full rounded-none bg-[#087F79] text-sm text-white hover:bg-[#075B5B] focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                disabled={processing}
            >
                {#if processing}<Spinner />{/if}
                {submitLabel}
                {#if !processing}<ArrowRight class="ml-2 h-4 w-4" />{/if}
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <TextLink
                    href={login()}
                    class="font-semibold text-primary underline underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                    >Masuk</TextLink
                >
            </div>
        </div>
    {/snippet}
</Form>
