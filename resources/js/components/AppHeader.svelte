<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Plus from 'lucide-svelte/icons/plus';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import AppLogo from '@/components/AppLogo.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import GlobalCommandSearch from '@/components/GlobalCommandSearch.svelte';
    import HeaderThemeControls from '@/components/HeaderThemeControls.svelte';
    import MobileSettingsMenuButton from '@/components/MobileSettingsMenuButton.svelte';
    import ProfileMenu from '@/components/ProfileMenu.svelte';
    import TenantPoolSwitcher from '@/components/TenantPoolSwitcher.svelte';
    import { Button } from '@/components/ui/button';
    import {
        NavigationMenu,
        NavigationMenuItem,
        NavigationMenuList,
        navigationMenuTriggerStyle,
    } from '@/components/ui/navigation-menu';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { shouldPrefetchNavigationHref } from '@/lib/navigation';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { BreadcrumbItem, NavItem } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();

    const auth = $derived(page.props.auth);
    const url = currentUrlState();
    const dashboardHref = toUrl(dashboard());
    const canPrefetchDashboard = shouldPrefetchNavigationHref(dashboardHref);
    const bookingConsoleHref = toUrl('/booking-console');
    const canPrefetchBookingConsole =
        shouldPrefetchNavigationHref(bookingConsoleHref);
    const isBookingConsolePage = $derived(
        url.isCurrentUrl(bookingConsoleHref, url.currentUrl),
    );

    const activeItemStyles =
        'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
</script>

<div>
    <div
        class="mobile-safe-header-shell sticky top-0 z-30 border-b border-sidebar-border/80 bg-background/96 backdrop-blur supports-[backdrop-filter]:bg-background/88"
    >
        <div
            class="mx-auto flex h-14 w-full max-w-7xl items-center gap-3 px-4 md:px-6"
        >
            <div class="flex min-w-0 shrink-0 items-center gap-2">
                <MobileSettingsMenuButton class="mr-1" />

                {#if isBookingConsolePage}
                    <Link
                        href={dashboardHref}
                        prefetch={canPrefetchDashboard || undefined}
                        cacheFor={canPrefetchDashboard ? 30000 : undefined}
                        class="hidden items-center gap-x-2 md:flex"
                    >
                        <AppLogo />
                    </Link>
                {:else}
                    <Link
                        href={dashboardHref}
                        prefetch={canPrefetchDashboard || undefined}
                        cacheFor={canPrefetchDashboard ? 30000 : undefined}
                        class="flex items-center gap-x-2"
                    >
                        <AppLogo />
                    </Link>
                {/if}

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex">
                    <NavigationMenu class="ml-6 flex h-full items-stretch">
                        <NavigationMenuList
                            class="flex h-full items-stretch gap-1.5"
                        >
                            {#each mainNavItems as item (toUrl(item.href))}
                                <NavigationMenuItem
                                    class="relative flex h-full items-center"
                                >
                                    <Link
                                        class="{navigationMenuTriggerStyle()} {url.whenCurrentUrl(
                                            item.href,
                                            url.currentUrl,
                                            activeItemStyles,
                                            '',
                                        ) ??
                                            ''} h-8 cursor-pointer rounded-md px-3.5 text-sm"
                                        href={toUrl(item.href)}
                                        prefetch={shouldPrefetchNavigationHref(
                                            toUrl(item.href),
                                        )
                                            ? ['hover', 'click']
                                            : undefined}
                                        cacheFor={shouldPrefetchNavigationHref(
                                            toUrl(item.href),
                                        )
                                            ? 30000
                                            : undefined}
                                    >
                                        {#if item.icon}
                                            <item.icon class="mr-2 h-4 w-4" />
                                        {/if}
                                        {item.title}
                                    </Link>
                                    {#if url.isCurrentUrl(item.href, url.currentUrl)}
                                        <div
                                            class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                        ></div>
                                    {/if}
                                </NavigationMenuItem>
                            {/each}
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>
            </div>

            <div
                class="hidden min-w-0 flex-1 items-center justify-center gap-2 md:flex"
            >
                {#if !isBookingConsolePage}
                    <TenantPoolSwitcher
                        mode="desktop"
                        class="hidden min-w-0 flex-[0_1_14rem] xl:block"
                    />
                {/if}
                <div class="min-w-0 flex-1">
                    <GlobalCommandSearch />
                </div>
            </div>

            <div class="ml-auto flex min-w-0 items-center gap-2">
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
                                cacheFor={canPrefetchBookingConsole
                                    ? 30000
                                    : undefined}
                            >
                                <Plus class="size-4" />
                                <span>Booking Console</span>
                            </Link>
                        {/snippet}
                    </Button>
                {/if}
                <HeaderThemeControls />
                <ProfileMenu user={auth.user} triggerClass="shrink-0" />
            </div>
        </div>
    </div>

    {#if breadcrumbs.length > 1}
        <div class="flex w-full border-b border-sidebar-border/70">
            <div
                class="mx-auto flex h-11 w-full max-w-6xl items-center justify-start px-4 text-neutral-500 md:px-6"
            >
                <Breadcrumbs {breadcrumbs} />
            </div>
        </div>
    {/if}
</div>
