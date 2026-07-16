<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Plus from 'lucide-svelte/icons/plus';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import GlobalCommandSearch from '@/components/GlobalCommandSearch.svelte';
    import HeaderThemeControls from '@/components/HeaderThemeControls.svelte';
    import MobileSettingsMenuButton from '@/components/MobileSettingsMenuButton.svelte';
    import ProfileMenu from '@/components/ProfileMenu.svelte';
    import TenantPoolSwitcher from '@/components/TenantPoolSwitcher.svelte';
    import { Button } from '@/components/ui/button';
    import { SidebarTrigger } from '@/components/ui/sidebar';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { shouldPrefetchNavigationHref } from '@/lib/navigation';
    import { toUrl } from '@/lib/utils';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();

    const auth = $derived(page.props.auth);
    const url = currentUrlState();
    const bookingConsoleHref = toUrl('/booking-console');
    const canPrefetchBookingConsole =
        shouldPrefetchNavigationHref(bookingConsoleHref);
    const isDashboardPage = $derived(
        url.isCurrentUrl('/dashboard', url.currentUrl),
    );
    const isBookingConsolePage = $derived(
        url.isCurrentUrl(bookingConsoleHref, url.currentUrl),
    );
    const dashboardDateLabel = $derived.by(() => {
        const props = page.props as Record<string, unknown>;
        const todayLabel = props.todayLabel;

        if (typeof todayLabel === 'string' && todayLabel.trim().length > 0) {
            return todayLabel.toUpperCase();
        }

        return new Intl.DateTimeFormat('en-US', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        })
            .format(new Date())
            .toUpperCase();
    });
</script>

<header
    class="mobile-safe-header sticky top-0 z-30 grid shrink-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-2 border-b border-sidebar-border/80 bg-background/96 px-4 backdrop-blur transition-[width,height] ease-linear supports-[backdrop-filter]:bg-background/88 md:h-14 md:grid-cols-[minmax(0,1fr)_minmax(24rem,42rem)_minmax(0,1fr)] md:px-5 md:group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:[html[data-density=compact]_&]:h-12 md:[html[data-density=compact]_&]:px-4"
>
    <div class="flex min-w-0 items-center gap-2">
        <SidebarTrigger class="-ml-1 hidden md:inline-flex" />

        <MobileSettingsMenuButton />

        {#if isDashboardPage}
            <div class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold text-foreground">
                    Dashboard Operasional
                </p>
                <p
                    class="truncate text-[10px] font-medium tracking-[0.08em] text-muted-foreground"
                >
                    {dashboardDateLabel}
                </p>
            </div>
        {:else if breadcrumbs && breadcrumbs.length > 0}
            <div class="hidden md:block">
                <Breadcrumbs {breadcrumbs} />
            </div>
        {/if}
    </div>

    <div class="hidden min-w-0 items-center justify-center gap-2 md:flex">
        {#if !isBookingConsolePage}
            <TenantPoolSwitcher
                mode="desktop"
                class="hidden min-w-0 flex-[0_1_16rem] xl:block"
            />
        {/if}
        <div class="min-w-0 flex-1">
            <GlobalCommandSearch />
        </div>
    </div>

    <div class="flex min-w-0 items-center justify-end gap-2 md:col-start-3">
        {#if !isBookingConsolePage}
            <Button
                asChild
                size="sm"
                variant="outline"
                class="hidden h-8 shrink-0 gap-2 rounded-md px-3.5 md:inline-flex"
            >
                {#snippet children(props)}
                    <Link
                        {...props}
                        href={bookingConsoleHref}
                        prefetch={canPrefetchBookingConsole || undefined}
                        cacheFor={canPrefetchBookingConsole ? 30000 : undefined}
                    >
                        <Plus class="size-4" />
                        <span>Booking Console</span>
                    </Link>
                {/snippet}
            </Button>
        {/if}
        <HeaderThemeControls />
        <ProfileMenu user={auth.user} />
    </div>
</header>
