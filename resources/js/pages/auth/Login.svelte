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
        class="border-l-2 border-[#174b43] bg-[#e5ebe3] px-4 py-3 text-sm font-medium text-[#173a34]"
    >
        {status}
    </div>
{/if}

<Form
    {...store.form()}
    resetOnSuccess={['password']}
    class="overflow-hidden border-y border-[#c9c2b3] bg-[#f7f4ec]"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-5 px-4 py-5 sm:px-6 sm:py-6">
            <a
                href="/auth/google/redirect"
                class="group block border border-[#c9c2b3] bg-transparent p-3 transition-colors hover:border-[#174b43] hover:bg-[#eee9dd] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="grid h-10 w-10 place-items-center border border-[#d2cabb] bg-white"
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
                            <p class="text-sm font-semibold text-[#213a34]">
                                Masuk dengan Google
                            </p>
                            <p class="mt-0.5 text-xs text-[#68736c]">
                                Paling cepat untuk pemilik dan admin pool.
                            </p>
                        </div>
                    </div>
                    <ArrowRight
                        class="h-4 w-4 text-[#174b43] transition-transform group-hover:translate-x-0.5"
                    />
                </div>
            </a>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-[#c9c2b3]"></span>
                </div>
                <div class="relative flex justify-center text-[10px] font-semibold uppercase tracking-[0.16em]">
                    <span class="bg-[#f7f4ec] px-3 text-[#68736c]">
                        Masuk dengan email
                    </span>
                </div>
            </div>

            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email" class="flex items-center gap-2 text-sm font-medium text-[#33433d]">
                        <Mail class="h-4 w-4 text-[#174b43]" aria-hidden="true" />
                        Email
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-11 rounded-none border-[#bdb6a8] bg-white px-3 text-sm focus-visible:ring-[#174b43]"
                    />
                    <InputError message={errors.email} />
                </div>

                <div class="grid gap-2">
                    <Label
                        for="password"
                        class="flex items-center gap-2 text-sm font-medium text-[#33433d]"
                    >
                        <LockKeyhole class="h-4 w-4 text-[#174b43]" aria-hidden="true" />
                        Kata sandi
                    </Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="h-11 rounded-none border-[#bdb6a8] bg-white px-3 text-sm focus-visible:ring-[#174b43]"
                    />
                    <InputError message={errors.password} />
                </div>

                <div class="flex items-center justify-between gap-2 sm:gap-3">
                    <Label
                        for="remember"
                    class="flex min-h-11 min-w-0 flex-1 items-center gap-2.5 px-1 py-1 text-left touch-manipulation sm:flex-none sm:px-0 sm:py-0"
                    >
                        <Checkbox
                            id="remember"
                            name="remember"
                            aria-label="Ingat saya"
                            class="inline-flex h-5 max-h-5 min-h-5 w-5 max-w-5 min-w-5 shrink-0 aspect-square items-center justify-center rounded-none border-[#aebbb5] p-0 leading-none shadow-none data-[state=checked]:border-[#174b43] data-[state=checked]:bg-[#174b43] focus-visible:ring-[#174b43]/35 sm:h-4 sm:max-h-4 sm:min-h-4 sm:w-4 sm:max-w-4 sm:min-w-4"
                        />
                        <span class="truncate text-sm text-[#53615b]">Ingat saya</span>
                    </Label>
                    <TextLink
                        href="/forgot-password"
                        class="text-sm font-semibold text-[#174b43] underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]"
                    >
                        Lupa kata sandi?
                    </TextLink>
                </div>

                <Button
                    type="submit"
                    class="h-12 w-full rounded-none bg-[#174b43] text-sm text-white hover:bg-[#103a34] focus-visible:ring-2 focus-visible:ring-[#a94e28] focus-visible:ring-offset-2"
                    disabled={processing}
                    data-test="login-button"
                >
                    {#if processing}<Spinner />{/if}
                    Masuk ke Dasbor
                    {#if !processing}<ArrowRight class="ml-2 h-4 w-4" />{/if}
                </Button>
            </div>

            <div class="text-center text-sm text-[#68736c]">
                Belum punya akun?
                <TextLink
                    href={register()}
                    class="font-semibold text-[#174b43] underline underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]"
                >
                    Mulai uji coba atau pilih paket
                </TextLink>
            </div>
        </div>
    {/snippet}
</Form>
