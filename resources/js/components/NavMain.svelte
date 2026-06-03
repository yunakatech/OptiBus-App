<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import {
        SidebarGroup,
        SidebarGroupLabel,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
        useSidebar,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    type NavSection = {
        id: string;
        title: string;
        icon?: NavItem['icon'];
        items: NavItem[];
    };

    let {
        sections = [],
        label = 'Menu',
    }: {
        sections: NavSection[];
        label?: string;
    } = $props();

    const { state: sidebarState, isMobile } = useSidebar();

    let openSections = $state<Record<string, boolean>>({});

    $effect(() => {
        const validIds = new Set(sections.map((section) => section.id));
        const next: Record<string, boolean> = {};

        for (const section of sections) {
            if (openSections[section.id] !== undefined) {
                next[section.id] = Boolean(openSections[section.id]);
            } else {
                next[section.id] = isSectionActive(section);
            }
        }

        const existingIds = Object.keys(openSections);
        const hasRemovedId = existingIds.some((id) => !validIds.has(id));
        const hasMissingId = sections.some((section) => openSections[section.id] === undefined);

        if (hasRemovedId || hasMissingId) {
            openSections = next;
        }
    });

    const appOrigin = () =>
        typeof window === 'undefined' ? 'http://localhost' : window.location.origin;

    const isItemActive = (href: NavItem['href']): boolean => {
        const resolved = toUrl(href);

        if (typeof resolved !== 'string') {
            return false;
        }

        try {
            const current = new URL(page.url, appOrigin());
            const target = new URL(resolved, appOrigin());
            const currentPath = current.pathname.replace(/\/+$/, '') || '/';
            const targetPath = target.pathname.replace(/\/+$/, '') || '/';

            if (currentPath === targetPath) {
                if (!target.search) {
                    return true;
                }

                for (const [key, value] of target.searchParams.entries()) {
                    if (current.searchParams.get(key) !== value) {
                        return false;
                    }
                }

                return true;
            }

            if (!target.search && targetPath !== '/' && currentPath.startsWith(`${targetPath}/`)) {
                return true;
            }

            return false;
        } catch {
            return page.url === resolved;
        }
    };

    const isSectionActive = (section: NavSection): boolean =>
        section.items.some((item) => isItemActive(item.href));

    const isSectionOpen = (id: string) => Boolean(openSections[id] ?? false);
    const isCollapsedDesktop = () => $sidebarState === 'collapsed' && !$isMobile;

    const toggleSection = (id: string) => {
        if (isCollapsedDesktop()) {
            return;
        }

        openSections = {
            ...openSections,
            [id]: !(openSections[id] ?? false),
        };
    };
</script>

<SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>{label}</SidebarGroupLabel>
    <SidebarMenu class="gap-1.5">
        {#each sections as section (section.id)}
            <SidebarMenuItem class="group/section">
                <div class="relative">
                    <SidebarMenuButton
                        isActive={isSectionActive(section)}
                        class="rounded-xl border border-transparent hover:border-sidebar-border/70 hover:bg-sidebar-accent/70 data-[active=true]:border-sidebar-border/70 data-[active=true]:bg-sidebar-accent/85"
                        onclick={() => toggleSection(section.id)}
                    >
                        {#if section.icon}
                            <section.icon class="size-4 shrink-0" />
                        {/if}
                        <span>{section.title}</span>
                        <ChevronDown class={`ml-auto size-3.5 transition-transform group-data-[collapsible=icon]:hidden ${isSectionOpen(section.id) ? 'rotate-180' : ''}`} />
                    </SidebarMenuButton>

                    {#if isSectionOpen(section.id)}
                        <div class="mt-1.5 space-y-1 rounded-xl border border-sidebar-border/70 bg-sidebar-accent/35 p-1.5 group-data-[collapsible=icon]:hidden">
                            {#each section.items as item (toUrl(item.href))}
                                <SidebarMenuButton
                                    asChild
                                    isActive={isItemActive(item.href)}
                                    class="h-8 rounded-lg text-[13px]"
                                >
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={toUrl(item.href)}
                                            class={props.class}
                                            prefetch={['hover', 'click']}
                                            cacheFor={30000}
                                        >
                                            {#if item.icon}
                                                <item.icon class="size-3.5 shrink-0" />
                                            {/if}
                                            <span>{item.title}</span>
                                        </Link>
                                    {/snippet}
                                </SidebarMenuButton>
                            {/each}
                        </div>
                    {/if}

                    <div
                        class="pointer-events-none invisible absolute top-0 left-full z-[90] ml-0 w-64 rounded-xl border border-sidebar-border/80 bg-background/95 p-2 opacity-0 shadow-xl ring-1 ring-black/5 transition-opacity duration-150 group-hover/section:pointer-events-auto group-hover/section:visible group-hover/section:opacity-100 group-focus-within/section:pointer-events-auto group-focus-within/section:visible group-focus-within/section:opacity-100 group-data-[collapsible=icon]:block md:group-data-[collapsible=icon]:block"
                        class:hidden={$sidebarState !== 'collapsed' || $isMobile}
                    >
                        <p class="mb-1 px-2 text-[11px] font-semibold tracking-wide text-muted-foreground uppercase">{section.title}</p>
                        <div class="space-y-1">
                            {#each section.items as item (toUrl(item.href))}
                                <Link
                                    href={toUrl(item.href)}
                                    prefetch={['hover', 'click']}
                                    cacheFor={30000}
                                    class={`flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${isItemActive(item.href) ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : ''}`}
                                >
                                    {#if item.icon}
                                        <item.icon class="size-3.5 shrink-0" />
                                    {/if}
                                    <span>{item.title}</span>
                                </Link>
                            {/each}
                        </div>
                    </div>
                </div>
            </SidebarMenuItem>
        {/each}
    </SidebarMenu>
</SidebarGroup>
