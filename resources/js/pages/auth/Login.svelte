<script module lang="ts">
    export const layout = {
        title: '',
        description: '',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import { ArrowRight, LockKeyhole, Mail } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { register } from '@/routes';
    import { store } from '@/routes/login';

    let { status = '' }: { status?: string } = $props();
</script>

<AppHead title="Masuk" />

{#if status}
    <div
        class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-700"
    >
        {status}
    </div>
{/if}

<Form
    {...store.form()}
    resetOnSuccess={['password']}
    class="overflow-hidden rounded-xl border border-[#d7dfd5] bg-white/92 shadow-[0_20px_56px_-38px_rgba(16,61,58,0.82)] backdrop-blur"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-3.5 p-4">
            <a
                href="/auth/google/redirect"
                class="group block rounded-xl border border-[#d9ded4] bg-[#fbfcf8] p-2.5 shadow-sm transition hover:-translate-y-0.5 hover:border-[#0d7066]/45 hover:shadow-[0_14px_32px_-26px_rgba(16,61,58,0.8)]"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="grid h-9 w-9 place-items-center rounded-full bg-white shadow-sm ring-1 ring-black/5"
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
                            <p class="text-[13px] font-semibold text-[#17201f]">
                                Masuk dengan Google
                            </p>
                            <p class="mt-0.5 text-[11px] text-[#687470]">
                                Paling cepat untuk pemilik dan admin pool.
                            </p>
                        </div>
                    </div>
                    <ArrowRight
                        class="h-4 w-4 text-[#0d7066] transition group-hover:translate-x-0.5"
                    />
                </div>
            </a>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-[#d9ded4]"></span>
                </div>
                <div class="relative flex justify-center text-[11px] uppercase tracking-wide">
                    <span class="bg-white px-2 text-[#687470]">
                        Atau masuk manual
                    </span>
                </div>
            </div>

            <div class="grid gap-3">
                <div class="grid gap-1.5">
                    <Label for="email" class="flex items-center gap-1.5 text-xs">
                        <Mail class="h-3.5 w-3.5 text-[#0d7066]" />
                        Email
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-8 rounded-lg px-2.5 text-[13px]"
                    />
                    <InputError message={errors.email} />
                </div>

                <div class="grid gap-1.5">
                    <Label
                        for="password"
                        class="flex items-center gap-1.5 text-xs"
                    >
                        <LockKeyhole class="h-3.5 w-3.5 text-[#0d7066]" />
                        Kata sandi
                    </Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="h-8 rounded-lg px-2.5 text-[13px]"
                    />
                    <InputError message={errors.password} />
                </div>

                <div class="flex items-center justify-between gap-3">
                    <Label for="remember" class="flex items-center gap-2">
                        <Checkbox id="remember" name="remember" />
                        <span class="text-xs text-[#53615d]">Ingat saya</span>
                    </Label>
                    <TextLink
                        href="/forgot-password"
                        class="text-xs font-semibold text-[#0d7066] underline-offset-4 hover:underline"
                    >
                        Lupa kata sandi?
                    </TextLink>
                </div>

                <Button
                    type="submit"
                    class="h-9 w-full rounded-lg bg-[#103d3a] text-[13px] text-white hover:bg-[#0b2f2c]"
                    disabled={processing}
                    data-test="login-button"
                >
                    {#if processing}<Spinner />{/if}
                    Masuk ke Dasbor
                    {#if !processing}<ArrowRight class="ml-2 h-4 w-4" />{/if}
                </Button>
            </div>

            <div class="text-center text-xs text-muted-foreground">
                Belum punya akun?
                <TextLink
                    href={register()}
                    class="font-semibold text-[#0d7066] underline underline-offset-4"
                >
                    Mulai uji coba atau pilih paket
                </TextLink>
            </div>
        </div>
    {/snippet}
</Form>
