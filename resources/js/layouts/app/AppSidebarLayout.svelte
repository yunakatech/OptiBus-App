<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/AppSidebarHeader.svelte';
    import ExternalLinkFallback from '@/components/ExternalLinkFallback.svelte';
    import GlobalConfirmDialog from '@/components/GlobalConfirmDialog.svelte';
    import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import TenantPoolSwitcher from '@/components/TenantPoolSwitcher.svelte';
    import ToastContainer from '@/components/ToastContainer.svelte';
    import { hasPermission } from '@/lib/access';
    import { bookingInboxNudge } from '@/lib/bookingInboxNudge.svelte';
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
    const auth = $derived(page.props.auth ?? null);
    const canCheckInboxNudge = $derived(
        hasPermission(auth?.permissions, 'booking.view') &&
            Boolean(auth?.active_tenant),
    );
    const activeTenantId = $derived(
        Number(auth?.active_tenant?.id ?? 0),
    );
    const isBookingConsolePage = $derived(
        url.isCurrentUrl('/booking-console', url.currentUrl),
    );

    $effect(() => {
        const enabled = canCheckInboxNudge;
        const tenantId = activeTenantId;

        bookingInboxNudge.hasPending = false;

        if (!enabled || tenantId <= 0 || typeof window === 'undefined') {
            return;
        }

        let disposed = false;
        let requestPending = false;

        const refreshInboxNudge = async () => {
            if (
                disposed ||
                requestPending ||
                document.visibilityState === 'hidden'
            ) {
                return;
            }

            requestPending = true;

            try {
                const response = await fetch(
                    '/api/admin/public-booking-requests/count',
                    {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    },
                );
                const payload = await response.json();

                if (!disposed && response.ok && payload.success === true) {
                    bookingInboxNudge.hasPending =
                        Number(payload.pending_count ?? 0) > 0;
                }
            } catch {
                // Keep the latest known indicator if a temporary request fails.
            } finally {
                requestPending = false;
            }
        };

        void refreshInboxNudge();
        const interval = window.setInterval(
            refreshInboxNudge,
            30_000,
        );
        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible') {
                void refreshInboxNudge();
            }
        };
        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            disposed = true;
            window.clearInterval(interval);
            document.removeEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
        };
    });
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent
        variant="sidebar"
        class="mobile-app-content overflow-x-clip md:pb-0"
    >
        <AppSidebarHeader {breadcrumbs} />
        {#if !isBookingConsolePage}
            <div
                class="border-b border-sidebar-border/70 bg-background/95 px-4 py-3 md:hidden"
            >
                <TenantPoolSwitcher mode="mobile" />
            </div>
        {/if}
        {@render children?.()}
    </AppContent>
    <MobileBottomNav />
    <GlobalLoadingOverlay />
    <GlobalConfirmDialog />
    <ToastContainer />
    <ExternalLinkFallback />
</AppShell>
