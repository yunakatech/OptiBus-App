<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { SvelteSet } from 'svelte/reactivity';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { measureBar } from '@/lib/mobile-runtime';
    import {
        getVisibleMobileNavItems,
        shouldPrefetchNavigationHref,
    } from '@/lib/navigation';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    const url = currentUrlState();
    const NAV_PREFETCH_CACHE_MS = 30_000;
    const visibleMainItems = $derived(
        getVisibleMobileNavItems(page.props.auth),
    );
    const navCount = $derived(Math.max(visibleMainItems.length, 1));
    let pendingHref = $state('');
    let prefetchedHrefs = new SvelteSet<string>();
    const activeIndex = $derived.by(() => {
        const matchedIndex = visibleMainItems.findIndex((item) =>
            isNavItemActive(item.href),
        );

        return matchedIndex;
    });

    function prepareNavPress(href: string): void {
        prefetchNavItem(href);
    }

    function prefetchNavItem(href: string): void {
        if (
            typeof window === 'undefined' ||
            url.isCurrentUrl(href, url.currentUrl) ||
            prefetchedHrefs.has(href) ||
            !shouldPrefetchNavigationHref(href)
        ) {
            return;
        }

        prefetchedHrefs = new SvelteSet(prefetchedHrefs).add(href);

        try {
            router.prefetch(
                href,
                {
                    preserveScroll: false,
                    preserveState: false,
                },
                {
                    cacheFor: NAV_PREFETCH_CACHE_MS,
                    cacheTags: ['navigation'],
                },
            );
        } catch {
            prefetchedHrefs.delete(href);
            prefetchedHrefs = new SvelteSet(prefetchedHrefs);
        }
    }

    function visitNavItem(event: MouseEvent, href: string): void {
        event.preventDefault();

        prepareNavPress(href);

        if (url.isCurrentUrl(href, url.currentUrl)) {
            return;
        }

        if (pendingHref === href) {
            return;
        }

        pendingHref = href;
        router.visit(href, {
            preserveScroll: false,
            preserveState: false,
            onFinish: () => {
                if (pendingHref === href) {
                    pendingHref = '';
                }
            },
        });
    }

    function isNavItemActive(itemHref: NonNullable<NavItem['href']>): boolean {
        return url.isCurrentOrParentUrl(itemHref, url.currentUrl);
    }

    $effect(() => {
        const matched = visibleMainItems.find((item) =>
            url.isCurrentOrParentUrl(item.href, url.currentUrl),
        );

        if (!matched) {
            return;
        }
    });

    function mobileLabel(title: string): string {
        return title === 'Keberangkatan' ? 'Berangkat' : title;
    }
</script>

{#if visibleMainItems.length > 0}
    <nav
        use:measureBar={'--mobile-nav-height'}
        class="mobile-bottom-navigation fixed inset-x-0 bottom-0 z-40 px-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2 md:hidden"
        aria-label="Mobile bottom navigation"
    >
        <div
            class="mx-auto w-full max-w-md rounded-2xl border border-sidebar-border/70 bg-background/88 p-1.5 shadow-[0_14px_30px_-24px_hsl(201_96%_30%_/_0.7)] ring-1 ring-black/5 backdrop-blur-xl"
        >
            <div class="relative overflow-hidden rounded-[1.15rem]">
                {#if activeIndex >= 0}
                    <div
                        class="pointer-events-none absolute inset-y-[3px] left-0 z-0 px-[5px] transition-transform duration-200"
                        style={`width: ${100 / navCount}%; transform: translateX(${activeIndex * 100}%);`}
                    >
                        <div
                            class="h-full rounded-xl border border-cyan-300/30 bg-linear-to-b from-cyan-500/25 to-sky-500/15 shadow-[0_8px_20px_-12px_hsl(200_95%_45%_/_0.7)]"
                        ></div>
                    </div>
                {/if}

                <ul
                    class="relative z-10 grid"
                    style={`grid-template-columns: repeat(${navCount}, minmax(0, 1fr));`}
                >
                    {#each visibleMainItems as item (toUrl(item.href))}
                        {@const itemHref = toUrl(item.href)}
                        <li>
                            <a
                                href={itemHref}
                                aria-label={item.title}
                                title={item.title}
                                aria-busy={pendingHref === itemHref}
                                onpointerenter={() => prefetchNavItem(itemHref)}
                                onpointerdown={() => prepareNavPress(itemHref)}
                                onfocus={() => prefetchNavItem(itemHref)}
                                onclick={(event) =>
                                    visitNavItem(event, itemHref)}
                                aria-current={isNavItemActive(item.href)
                                    ? 'page'
                                    : undefined}
                                class="group relative flex min-h-12 touch-manipulation select-none flex-col items-center justify-center gap-0.5 rounded-xl px-1 text-[11px] font-semibold leading-tight transition-colors active:scale-[0.97] {pendingHref ===
                                itemHref
                                    ? 'opacity-70'
                                    : ''} {isNavItemActive(item.href)
                                    ? 'text-primary'
                                    : 'text-muted-foreground/90'}"
                            >
                                {#if pendingHref === itemHref}
                                    <span
                                        class="absolute right-2 top-1 size-1.5 rounded-full bg-cyan-500 shadow-[0_0_12px_rgba(6,182,212,0.9)] motion-safe:animate-ping"
                                    ></span>
                                {/if}
                                {#if item.icon}
                                    <item.icon
                                        class="size-5 shrink-0 transition-transform duration-200 {isNavItemActive(
                                            item.href,
                                        )
                                            ? '-translate-y-0.5 scale-110'
                                            : 'group-hover:-translate-y-0.5'}"
                                    />
                                {/if}
                                <span class="max-w-full truncate"
                                    >{mobileLabel(item.title)}</span
                                >
                            </a>
                        </li>
                    {/each}
                </ul>
            </div>
        </div>
    </nav>
{/if}
