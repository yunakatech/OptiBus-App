<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
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
    class={`relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-[#f1eee5] text-[#1a2825] ${compactLogin ? 'p-4 sm:p-5' : compactFlow ? 'p-4 md:p-6' : 'p-5 md:p-8'}`}
>
    <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(90deg,transparent_calc(50%-0.5px),rgba(190,181,164,0.22)_50%,transparent_calc(50%+0.5px))]"
    ></div>
    <div
        class="pointer-events-none absolute inset-x-0 top-0 h-20 border-b border-[#d2cabb] bg-[linear-gradient(90deg,rgba(23,58,52,0.08)_1px,transparent_1px),linear-gradient(rgba(23,58,52,0.06)_1px,transparent_1px)] bg-[size:48px_48px]"
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
                    class="group flex flex-col items-center gap-2 font-medium focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a94e28]"
                >
                    <img
                        src="/branding/OptiBus-logo-full.png"
                        alt="OptiBus"
                        class={`mb-1 h-auto object-contain transition-opacity duration-200 group-hover:opacity-80 ${compactLogin || compactFlow ? 'w-[156px]' : 'w-[196px]'}`}
                        loading="eager"
                        decoding="async"
                    />
                    <span class="sr-only">{title || 'OptiBus'}</span>
                </Link>
                {#if title || description}
                    <div class="max-w-2xl space-y-2 text-center">
                        {#if title}
                            <h1
                                class={`font-serif font-medium leading-tight tracking-[-0.03em] text-[#173a34] ${compactLogin || compactFlow ? 'text-2xl md:text-3xl' : 'text-3xl md:text-4xl'}`}
                            >
                                {title}
                            </h1>
                        {/if}
                        {#if description}
                            <p
                                class={`text-center text-[#53615b] ${compactLogin || compactFlow ? 'text-sm leading-6' : 'text-base leading-7'}`}
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
