<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.svelte';
    import SupportButton from '@/components/SupportButton.svelte';
    import ToastContainer from '@/components/ToastContainer.svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();

    const wideLayout = $derived(
        ['Daftar Akun', 'Lengkapi Data Travel'].includes(title),
    );
    const compactLogin = $derived(page.url?.startsWith('/login') ?? false);
    const compactFlow = $derived(wideLayout);
</script>

<div
    class={`relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-background text-foreground ${compactLogin ? 'p-4 sm:p-5' : compactFlow ? 'p-4 md:p-6' : 'p-5 md:p-8'}`}
>
    <div
        class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-primary"
    ></div>
    <div
        class="pointer-events-none absolute -right-32 top-10 h-72 w-72 rounded-full bg-[#e7faf3]/70 blur-3xl dark:bg-[#1a4242]/40"
    ></div>
    <div
        class={`relative z-10 w-full ${wideLayout ? 'max-w-[54rem]' : compactLogin ? 'max-w-[23rem]' : 'max-w-[28rem]'}`}
    >
        <div
            class={`flex flex-col ${compactLogin || compactFlow ? 'gap-4' : 'gap-6'}`}
        >
            <div
                class={`flex flex-col items-center ${compactLogin || compactFlow ? 'gap-2.5' : 'gap-4'}`}
            >
                <Link
                    href={home()}
                    class="group flex flex-col items-center gap-2 font-medium focus-visible:rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
                >
                    <AppLogo />
                    <span class="sr-only">{title || 'OptiBus'}</span>
                </Link>
                {#if title || description}
                    <div class="max-w-2xl space-y-2 text-center">
                        {#if title}
                            <h1
                                class={`font-sans font-semibold leading-tight tracking-[-0.03em] text-foreground ${compactLogin || compactFlow ? 'text-2xl md:text-3xl' : 'text-3xl md:text-4xl'}`}
                            >
                                {title}
                            </h1>
                        {/if}
                        {#if description}
                            <p
                                class={`text-center text-muted-foreground ${compactLogin || compactFlow ? 'text-sm leading-6' : 'text-base leading-7'}`}
                            >
                                {description}
                            </p>
                        {/if}
                    </div>
                {/if}
            </div>
            {@render children?.()}
        </div>
    </div>
    <div class="relative z-10 mt-6">
        <SupportButton />
    </div>
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
</div>
