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
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

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

    function mobileLabel(title: string): string {
        return title === 'Keberangkatan' ? 'Berangkat' : title;
    }
</script>

{#if visibleMainItems.length > 0}
    <nav
        use:measureBar={'--mobile-nav-height'}
        class="mobile-bottom-navigation fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2 md:hidden"
        aria-label="Mobile bottom navigation"
    >
        <div
            class="mx-auto w-full max-w-md rounded-3xl border border-border/80 bg-background p-1.5 shadow-[0_12px_28px_-18px_hsl(215_25%_20%_/_0.38)]"
        >
            <div class="overflow-hidden rounded-2xl">
                <ul
                    class="grid"
                    style={`grid-template-columns: repeat(${navCount}, minmax(0, 1fr));`}
                >
                    {#each visibleMainItems as item (toUrl(item.href))}
                        {@const itemHref = toUrl(item.href)}
                        {@const itemActive = isNavItemActive(item.href)}
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
                                class="group relative flex min-h-12 touch-manipulation select-none flex-col items-center justify-center gap-0.5 rounded-2xl px-1 text-xs font-semibold leading-4 transition-colors duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary {pendingHref ===
                                itemHref
                                    ? 'opacity-70'
                                    : ''} {itemActive
                                    ? 'text-primary'
                                    : 'text-muted-foreground'}"
                            >
                                {#if pendingHref === itemHref}
                                    <span
                                        class="absolute right-2 top-1 size-1.5 rounded-full bg-primary motion-safe:animate-pulse"
                                    ></span>
                                {/if}
                                <span
                                    class="flex h-8 w-12 items-center justify-center rounded-full transition-colors duration-200 {itemActive
                                        ? 'bg-primary/15'
                                        : 'bg-transparent'}"
                                >
                                    {#if item.icon}
                                        <item.icon class="size-6 shrink-0" />
                                    {/if}
                                </span>
                                <span
                                    class="max-w-full whitespace-normal break-words text-center"
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
