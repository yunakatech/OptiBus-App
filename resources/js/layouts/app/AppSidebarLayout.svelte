<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/AppSidebarHeader.svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
    import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import TenantPoolSwitcher from '@/components/TenantPoolSwitcher.svelte';
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
    const isBookingConsolePage = $derived(
        url.isCurrentUrl('/booking-console', url.currentUrl),
    );
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent variant="sidebar" class="overflow-x-clip pb-20 md:pb-0">
        <AppSidebarHeader {breadcrumbs} />
        {#if !isBookingConsolePage}
            <div
                class="border-b border-sidebar-border/70 bg-background/95 px-4 py-3 md:hidden"
            >
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                        >
                            Konteks aktif
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            Tenant dan pool untuk data yang sedang dibuka
                        </p>
                    </div>
                </div>
                <TenantPoolSwitcher mode="mobile" />
            </div>
        {/if}
        {@render children?.()}
    </AppContent>
    <MobileBottomNav />
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
</AppShell>
