<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
    import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.svelte';
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
    class={`relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-[#f5f3eb] text-[#17201f] ${compactLogin ? 'p-3 md:p-4' : compactFlow ? 'p-3 md:p-5' : 'p-4 md:p-8'}`}
>
    <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_12%,rgba(13,112,102,0.18),transparent_28%),radial-gradient(circle_at_88%_18%,rgba(185,108,32,0.16),transparent_26%),linear-gradient(135deg,rgba(255,255,255,0.74),rgba(238,242,235,0.86))]"
    ></div>
    <div
        class="pointer-events-none absolute inset-x-0 top-0 h-24 border-b border-[#d9ded4]/70 bg-[linear-gradient(90deg,rgba(16,61,58,0.12)_1px,transparent_1px),linear-gradient(rgba(16,61,58,0.09)_1px,transparent_1px)] bg-[size:38px_38px] opacity-60"
    ></div>
    <div
        class={`relative z-10 w-full ${wideLayout ? 'max-w-[54rem]' : compactLogin ? 'max-w-[23rem]' : 'max-w-[28rem]'}`}
    >
        <div
            class={`flex flex-col ${compactLogin || compactFlow ? 'gap-3' : 'gap-5'}`}
        >
            <div
                class={`flex flex-col items-center ${compactLogin || compactFlow ? 'gap-2' : 'gap-3'}`}
            >
                <Link
                    href={home()}
                    class="group flex flex-col items-center gap-2 font-medium"
                >
                    <img
                        src="/branding/OptiBus-logo-full.png"
                        alt="OptiBus"
                        class={`mb-1 h-auto object-contain drop-shadow-[0_12px_26px_rgba(16,61,58,0.12)] transition duration-300 group-hover:-translate-y-0.5 ${compactLogin || compactFlow ? 'w-[168px]' : 'w-[210px]'}`}
                        loading="eager"
                        decoding="async"
                    />
                    <span class="sr-only">{title || 'OptiBus'}</span>
                </Link>
                {#if title || description}
                    <div class="max-w-2xl space-y-2 text-center">
                        {#if title}
                            <h1
                                class={`font-semibold tracking-[-0.03em] text-[#132b28] ${compactLogin || compactFlow ? 'text-xl md:text-2xl' : 'text-2xl md:text-3xl'}`}
                            >
                                {title}
                            </h1>
                        {/if}
                        {#if description}
                            <p
                                class={`text-center text-[#53615d] ${compactLogin || compactFlow ? 'text-xs leading-5' : 'text-sm leading-6'}`}
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
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
</div>
