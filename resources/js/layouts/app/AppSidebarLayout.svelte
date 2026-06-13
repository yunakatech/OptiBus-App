<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/AppSidebarHeader.svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
    import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import ToastContainer from '@/components/ToastContainer.svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();

    const url = currentUrlState();
    const isMenuPage = $derived(url.isCurrentUrl('/menu', url.currentUrl));
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent variant="sidebar" class="overflow-x-clip pb-20 md:pb-0">
        <AppSidebarHeader {breadcrumbs} />
        {@render children?.()}
    </AppContent>
    {#if !isMenuPage}
        <MobileBottomNav />
    {/if}
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
</AppShell>
