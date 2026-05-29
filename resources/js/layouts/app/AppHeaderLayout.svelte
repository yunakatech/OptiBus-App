<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppHeader from '@/components/AppHeader.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
    import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import ToastContainer from '@/components/ToastContainer.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppShell variant="header">
    <AppHeader {breadcrumbs} />
    <AppContent variant="header" class="overflow-x-hidden pb-24 md:pb-0">
        {@render children?.()}
    </AppContent>
    <MobileBottomNav />
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
</AppShell>
