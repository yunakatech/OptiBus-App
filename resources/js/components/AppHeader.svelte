<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Monitor from 'lucide-svelte/icons/monitor';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import AppLogo from '@/components/AppLogo.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
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
    <div class="mobile-safe-header-shell border-b border-sidebar-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/85">
        <div class="mx-auto flex h-14 max-w-6xl items-center px-4 md:px-6">
            <MobileSettingsMenuButton class="mr-2" />

            {#if isBookingConsolePage}
                <Link
                    href={toUrl(dashboard())}
                    prefetch
                    cacheFor={30000}
                    class="hidden items-center gap-x-2 md:flex"
                >
                    <AppLogo />
                </Link>
            {:else}
                <Link
                    href={toUrl(dashboard())}
                    prefetch
                    cacheFor={30000}
                    class="flex items-center gap-x-2"
                >
                    <AppLogo />
                </Link>
            {/if}

            <!-- Desktop Menu -->
            <div class="hidden h-full lg:flex lg:flex-1">
                <NavigationMenu class="ml-8 flex h-full items-stretch">
                    <NavigationMenuList class="flex h-full items-stretch gap-1.5">
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
                                    ) ?? ''} h-8 cursor-pointer rounded-md px-3.5 text-sm"
                                    href={toUrl(item.href)}
                                    prefetch={['hover', 'click']}
                                    cacheFor={30000}
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

            <div class="hidden min-w-0 items-center justify-center lg:flex">
                <TenantPoolSwitcher
                    mode="desktop"
                    class="hidden min-w-0 flex-[0_1_14.5rem] lg:block xl:flex-[0_1_16rem] 2xl:flex-[0_1_17.5rem]"
                />
            </div>

            <div class="ml-auto flex min-w-0 items-center gap-2">
                <Button
                    asChild
                    size="sm"
                    variant={url.isCurrentUrl('/booking-console', url.currentUrl)
                        ? 'default'
                        : 'outline'}
                    class="h-8 shrink-0 gap-2 rounded-md px-3.5"
                >
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl('/booking-console')}
                            prefetch
                            cacheFor={30000}
                        >
                            <Monitor class="size-4" />
                            <span>Booking Console</span>
                        </Link>
                    {/snippet}
                </Button>
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
