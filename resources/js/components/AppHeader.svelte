<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import AppLogo from '@/components/AppLogo.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import MobileSettingsMenuButton from '@/components/MobileSettingsMenuButton.svelte';
    import ProfileMenu from '@/components/ProfileMenu.svelte';
    import {
        NavigationMenu,
        NavigationMenuItem,
        NavigationMenuList,
        navigationMenuTriggerStyle,
    } from '@/components/ui/navigation-menu';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
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
    const isBookingConsolePage = $derived(
        url.isCurrentUrl('/booking-console', url.currentUrl),
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
    <div class="border-b border-sidebar-border/80">
        <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
            <MobileSettingsMenuButton class="mr-2" />

            {#if isBookingConsolePage}
                <Link
                    href={toUrl(dashboard())}
                    class="hidden items-center gap-x-2 md:flex"
                >
                    <AppLogo />
                </Link>
            {:else}
                <Link
                    href={toUrl(dashboard())}
                    class="flex items-center gap-x-2"
                >
                    <AppLogo />
                </Link>
            {/if}

            <!-- Desktop Menu -->
            <div class="hidden h-full lg:flex lg:flex-1">
                <NavigationMenu class="ml-10 flex h-full items-stretch">
                    <NavigationMenuList
                        class="flex h-full items-stretch space-x-2"
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
                                    ) ?? ''} h-9 cursor-pointer px-4"
                                    href={toUrl(item.href)}
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

            <div class="ml-auto flex items-center space-x-2">
                <ProfileMenu user={auth.user} />
            </div>
        </div>
    </div>

    {#if breadcrumbs.length > 1}
        <div class="flex w-full border-b border-sidebar-border/70">
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs {breadcrumbs} />
            </div>
        </div>
    {/if}
</div>
