<script lang="ts">
    import { Link } from '@inertiajs/svelte';
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

    const wideLayout = $derived(['Daftar Akun', 'Lengkapi Data Travel'].includes(title));
</script>

<div
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
>
    <div class={`w-full ${wideLayout ? 'max-w-2xl' : 'max-w-sm'}`}>
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <Link
                    href={home()}
                    class="flex flex-col items-center gap-2 font-medium"
                >
                    <img
                        src="/branding/OptiBus-logo-full.png"
                        alt="OptiBus Booking & Operations Workspace"
                        class="mb-1 h-auto w-[210px] object-contain"
                        loading="eager"
                        decoding="async"
                    />
                    <span class="sr-only">{title}</span>
                </Link>
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">{title}</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
            </div>
            {@render children?.()}
        </div>
    </div>
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
</div>
