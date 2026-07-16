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

    import { untrack } from 'svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';

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

    const { state: sidebarState, isMobile, setOpenMobile } = useSidebar();
    const urlState = currentUrlState();

    let openSections = $state<Record<string, boolean>>({});

    $effect(() => {
        // Hanya `sections` dan `urlState.currentUrl` yang menjadi dependensi reaktif.
        // `openSections` dibaca via untrack agar tidak memicu loop.
        const _sections = sections;
        const _url = urlState.currentUrl;

        const validIds = new Set(_sections.map((section) => section.id));
        const prev = untrack(() => openSections);

        const next: Record<string, boolean> = {};
        for (const section of _sections) {
            const active = section.items.some((item) =>
                urlState.isCurrentOrParentUrl(item.href, _url),
            );
            if (active) {
                next[section.id] = true;
            } else if (prev[section.id] !== undefined) {
                next[section.id] = Boolean(prev[section.id]);
            } else {
                next[section.id] = false;
            }
        }

        const hasRemovedId = Object.keys(prev).some((id) => !validIds.has(id));
        const hasMissingId = _sections.some(
            (section) => prev[section.id] === undefined,
        );
        const activeChanged = _sections.some(
            (section) =>
                section.items.some((item) =>
                    urlState.isCurrentOrParentUrl(item.href, _url),
                ) && !prev[section.id],
        );

        let shouldUpdate = false;
        if (hasRemovedId || hasMissingId || activeChanged) {
            shouldUpdate = true;
        }

        if (!shouldUpdate) {
            const nextKeys = Object.keys(next);
            const prevKeys = Object.keys(prev);

            if (nextKeys.length !== prevKeys.length) {
                shouldUpdate = true;
            } else {
                for (const key of nextKeys) {
                    if (next[key] !== prev[key]) {
                        shouldUpdate = true;
                        break;
                    }
                }
            }
        }

        if (shouldUpdate) {
            openSections = next;
        }
    });

    const isItemActive = (href: NavItem['href']): boolean => {
        return urlState.isCurrentOrParentUrl(href, urlState.currentUrl);
    };

    const isSectionActive = (section: NavSection): boolean =>
        section.items.some((item) => isItemActive(item.href));

    const isSectionOpen = (id: string) => Boolean(openSections[id] ?? false);
    const isCollapsedDesktop = () =>
        $sidebarState === 'collapsed' && !$isMobile;

    const toggleSection = (id: string) => {
        if (isCollapsedDesktop()) {
            return;
        }

        openSections = {
            ...openSections,
            [id]: !(openSections[id] ?? false),
        };
    };

    const handleNavigate = () => {
        if ($isMobile) {
            setOpenMobile(false);
        }
    };
</script>

<SidebarGroup class="px-1.5 py-0">
    <SidebarGroupLabel class="px-2 text-[11px] tracking-[0.12em]"
        >{label}</SidebarGroupLabel
    >
    <SidebarMenu class="gap-1">
        {#each sections as section (section.id)}
            <SidebarMenuItem class="group/section">
                <div class="relative">
                    <SidebarMenuButton
                        isActive={isSectionActive(section)}
                        class="rounded-md border border-transparent hover:border-sidebar-border/70 hover:bg-sidebar-accent/70 data-[active=true]:border-sidebar-border/70 data-[active=true]:bg-sidebar-accent/85"
                        onclick={() => toggleSection(section.id)}
                    >
                        {#if section.icon}
                            <section.icon class="size-4 shrink-0" />
                        {/if}
                        <span>{section.title}</span>
                        <ChevronDown
                            class={`ml-auto size-3.5 transition-transform group-data-[collapsible=icon]:hidden ${isSectionOpen(section.id) ? 'rotate-180' : ''}`}
                        />
                    </SidebarMenuButton>

                    {#if isSectionOpen(section.id)}
                        <div
                            class="mt-1 space-y-1 rounded-md border border-sidebar-border/70 bg-sidebar-accent/35 p-1 group-data-[collapsible=icon]:hidden"
                        >
                            {#each section.items as item (toUrl(item.href))}
                                <SidebarMenuButton
                                    asChild
                                    isActive={isItemActive(item.href)}
                                    class="h-7 rounded-md text-xs"
                                >
                                    {#snippet children(props)}
                                        <Link
                                            {...props}
                                            href={toUrl(item.href)}
                                            class={props.class}
                                            onclick={handleNavigate}
                                        >
                                            {#if item.icon}
                                                <item.icon
                                                    class="size-3 shrink-0"
                                                />
                                            {/if}
                                            <span>{item.title}</span>
                                        </Link>
                                    {/snippet}
                                </SidebarMenuButton>
                            {/each}
                        </div>
                    {/if}

                    <div
                        class="pointer-events-none invisible absolute top-0 left-full z-[90] ml-0 w-64 rounded-lg border border-sidebar-border/80 bg-background/95 p-2 opacity-0 shadow-md ring-1 ring-black/5 transition-opacity duration-150 group-hover/section:pointer-events-auto group-hover/section:visible group-hover/section:opacity-100 group-focus-within/section:pointer-events-auto group-focus-within/section:visible group-focus-within/section:opacity-100 group-data-[collapsible=icon]:block md:group-data-[collapsible=icon]:block"
                        class:hidden={$sidebarState !== 'collapsed' ||
                            $isMobile}
                    >
                        <p
                            class="mb-1 px-2 text-[11px] font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            {section.title}
                        </p>
                        <div class="space-y-1">
                            {#each section.items as item (toUrl(item.href))}
                                <Link
                                    href={toUrl(item.href)}
                                    onclick={handleNavigate}
                                    class={`flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground ${isItemActive(item.href) ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : ''}`}
                                >
                                    {#if item.icon}
                                        <item.icon class="size-3 shrink-0" />
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
