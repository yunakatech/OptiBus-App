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
    const consoleIndex = $derived(
        visibleMainItems.findIndex(
            (item) => toUrl(item.href) === '/booking-console',
        ),
    );
    const hasConsole = $derived(consoleIndex >= 0);
    const leftItems = $derived(
        hasConsole ? visibleMainItems.slice(0, consoleIndex) : visibleMainItems,
    );
    const centerItem = $derived(
        hasConsole ? visibleMainItems[consoleIndex] : undefined,
    );
    const rightItems = $derived(
        hasConsole ? visibleMainItems.slice(consoleIndex + 1) : [],
    );
    const activeHref = $derived(
        visibleMainItems.find((item) => isNavItemActive(item.href))?.href,
    );
    let surfaceWidth = $state(296);
    let surfaceHeight = $state(76);
    // Keep the cutout in CSS pixels as the surface grows with wrapped labels.
    const surfacePath = $derived.by(() => {
        const w = Math.max(surfaceWidth, 1);
        const h = Math.max(surfaceHeight, 76);
        const c = w / 2;
        const top = hasConsole
            ? `H ${c - 46} C ${c - 34} 1 ${c - 38} 12 ${c - 32} 27 C ${c - 26} 42 ${c - 15} 48 ${c} 48 C ${c + 15} 48 ${c + 26} 42 ${c + 32} 27 C ${c + 38} 12 ${c + 34} 1 ${c + 46} 1`
            : '';

        return `M 24 1 ${top} H ${w - 24} Q ${w - 1} 1 ${w - 1} 24 V ${h} H 1 V 24 Q 1 1 24 1 Z`;
    });
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
        const href = toUrl(itemHref);

        return url.currentUrl === href || url.currentUrl.startsWith(`${href}/`);
    }

    function mobileLabel(title: string): string {
        return title === 'Keberangkatan' ? 'Berangkat' : title;
    }
</script>

{#if visibleMainItems.length > 0}
    <nav
        use:measureBar={'--mobile-nav-height'}
        class="mobile-bottom-navigation fixed inset-x-0 bottom-0 z-40 md:hidden"
        class:has-console={hasConsole}
        aria-label="Navigasi utama"
    >
        <div
            class="mobile-nav-surface"
            bind:clientWidth={surfaceWidth}
            bind:clientHeight={surfaceHeight}
        >
            <svg
                class="mobile-nav-background"
                viewBox={`0 0 ${Math.max(surfaceWidth, 1)} ${Math.max(surfaceHeight, 76)}`}
                preserveAspectRatio="none"
                aria-hidden="true"
                focusable="false"
            >
                <path d={surfacePath} />
            </svg>
            <ul
                class="mobile-nav-items"
                style:grid-template-columns={hasConsole
                    ? 'minmax(0, 1fr) 76px minmax(0, 1fr)'
                    : `repeat(${navCount}, minmax(0, 1fr))`}
            >
                {#if hasConsole}
                    <li class="mobile-nav-group">
                        {#each leftItems as item (toUrl(item.href))}
                            <div class="mobile-nav-item">
                                {@render navItem(item)}
                            </div>
                        {/each}
                    </li>
                    <li class="mobile-nav-center-slot">
                        {#if centerItem}
                            {@render navItem(centerItem, true)}
                        {/if}
                    </li>
                    <li class="mobile-nav-group">
                        {#each rightItems as item (toUrl(item.href))}
                            <div class="mobile-nav-item">
                                {@render navItem(item)}
                            </div>
                        {/each}
                    </li>
                {:else}
                    {#each visibleMainItems as item (toUrl(item.href))}
                        <li class="mobile-nav-item">
                            {@render navItem(item)}
                        </li>
                    {/each}
                {/if}
            </ul>
        </div>
    </nav>
{/if}

{#snippet navItem(item: NavItem, isConsole = false)}
    {@const itemHref = toUrl(item.href)}
    {@const itemActive = item.href === activeHref}
    <a
        href={itemHref}
        aria-label={isConsole ? 'Buka Booking Console' : item.title}
        title={item.title}
        aria-busy={pendingHref === itemHref}
        onpointerenter={() => prefetchNavItem(itemHref)}
        onpointerdown={() => prepareNavPress(itemHref)}
        onfocus={() => prefetchNavItem(itemHref)}
        onclick={(event) => visitNavItem(event, itemHref)}
        aria-current={itemActive ? 'page' : undefined}
        class="mobile-nav-link"
        class:mobile-nav-link-center={isConsole}
    >
        <span class="mobile-nav-icon" class:mobile-nav-fab={isConsole}>
            {#if item.icon}
                <item.icon class={isConsole ? 'size-7' : 'size-6'} />
            {/if}
            {#if pendingHref === itemHref}
                <span class="mobile-nav-loading" aria-hidden="true"></span>
            {/if}
        </span>
        {#if !isConsole}
            <span class="mobile-nav-label">{mobileLabel(item.title)}</span>
        {/if}
    </a>
{/snippet}

<style>
    .mobile-bottom-navigation {
        position: fixed;
        inset-inline: 0;
        bottom: 0;
        z-index: 40;
        width: 100%;
        padding-top: 8px;
        padding-bottom: env(safe-area-inset-bottom);
        pointer-events: none;
    }

    .mobile-bottom-navigation::after {
        position: absolute;
        inset-inline: 0;
        bottom: 0;
        height: env(safe-area-inset-bottom);
        background: var(--card);
        content: '';
        pointer-events: none;
    }

    .mobile-bottom-navigation.has-console {
        padding-top: 24px;
    }

    .mobile-nav-surface {
        position: relative;
        width: 100%;
        max-width: none;
        margin-inline: auto;
    }

    .mobile-nav-background {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: visible;
        fill: var(--card);
        stroke: var(--border);
        stroke-width: 1;
        filter: none;
    }

    .mobile-nav-items {
        position: relative;
        display: grid;
        align-items: stretch;
        min-height: 76px;
        margin: 0;
        padding: 0 12px 12px;
        list-style: none;
    }

    .mobile-nav-item {
        min-width: 0;
        flex: 1 1 0%;
    }

    .mobile-nav-group {
        display: flex;
        grid-row: 1;
        min-width: 0;
        margin: 0;
        padding: 0;
    }

    .mobile-nav-center-slot {
        grid-row: 1;
        min-width: 0;
        margin-top: -16px;
        margin-bottom: 16px;
    }

    .mobile-nav-link {
        display: flex;
        position: relative;
        height: 100%;
        min-width: 48px;
        min-height: 48px;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        padding: 12px 2px 0;
        border-radius: 14px;
        color: var(--muted-foreground);
        text-decoration: none;
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 500;
        touch-action: manipulation;
        pointer-events: auto;
        transition:
            color 180ms ease,
            opacity 180ms ease;
    }

    .mobile-nav-link:hover,
    .mobile-nav-link[aria-current='page'] {
        color: var(--primary);
    }

    .mobile-nav-link[aria-current='page'] .mobile-nav-label {
        font-weight: 600;
    }

    .mobile-nav-link:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .mobile-nav-icon {
        display: flex;
        position: relative;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
    }

    .mobile-nav-link-center {
        gap: 0;
        padding-top: 0;
    }

    .mobile-nav-fab {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--primary);
        color: var(--primary-foreground);
        box-shadow: 0 4px 8px rgb(0 0 0 / 0.14);
    }

    .mobile-nav-link[aria-current='page'] .mobile-nav-fab {
        outline: 2px solid var(--primary);
        outline-offset: 3px;
    }

    .mobile-nav-label {
        max-width: 100%;
        text-align: center;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .mobile-nav-link[aria-busy='true'] .mobile-nav-label {
        opacity: 0.65;
    }

    .mobile-nav-loading {
        position: absolute;
        top: -3px;
        right: -4px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .mobile-nav-fab .mobile-nav-loading {
        top: 8px;
        right: 8px;
    }

    @media (prefers-reduced-motion: no-preference) {
        .mobile-nav-loading {
            animation: nav-pending 1s ease-in-out infinite;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mobile-nav-link {
            transition: none;
        }
    }

    @keyframes nav-pending {
        50% {
            opacity: 0.3;
        }
    }
</style>
