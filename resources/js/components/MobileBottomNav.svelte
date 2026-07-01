<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Plus from 'lucide-svelte/icons/plus';
    import Tickets from 'lucide-svelte/icons/tickets';
    import { onMount } from 'svelte';
    import { hasPermission } from '@/lib/access';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    const url = currentUrlState();
    const NAV_PREFETCH_CACHE_MS = 30_000;

    const mainItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
            permission: 'dashboard.view',
        },
        {
            title: 'Keberangkatan',
            href: '/bookings',
            icon: Tickets,
            permission: 'booking.view',
        },
        {
            title: 'Console',
            href: '/booking-console',
            icon: Plus,
            permission: 'booking.view',
        },
        {
            title: 'Bagasi',
            href: '/luggages',
            icon: Briefcase,
            permission: 'luggage.view',
        },
        {
            title: 'Carter',
            href: '/charters',
            icon: BusFront,
            permission: 'charter.view',
        },
    ];
    const billingItems: NavItem[] = [
        {
            title: 'Langganan',
            href: '/subscription',
            icon: CreditCard,
        },
    ];

    const permissions = $derived(page.props.auth?.permissions ?? []);
    const billingLocked = $derived(
        Boolean(page.props.auth?.billing_access?.locked),
    );
    const visibleMainItems = $derived(
        billingLocked
            ? billingItems
            : mainItems.filter((item) =>
                  hasPermission(permissions, item.permission),
              ),
    );
    const navCount = $derived(Math.max(visibleMainItems.length, 1));
    let isCompact = $state(false);
    let pendingHref = $state('');
    let prefetchedHrefs = new Set<string>();
    let lastScrollY = 0;
    let scrollFrameRequested = false;

    const SCROLL_THRESHOLD = 24;
    const SCROLL_DELTA = 6;
    const activeIndex = $derived.by(() => {
        const matchedIndex = visibleMainItems.findIndex((item) =>
            isNavItemActive(item.href),
        );

        return matchedIndex >= 0 ? matchedIndex : 0;
    });

    function prepareNavPress(href: string): void {
        prefetchNavItem(href);
        isCompact = false;
    }

    function prefetchNavItem(href: string): void {
        if (
            typeof window === 'undefined' ||
            url.isCurrentUrl(href, url.currentUrl) ||
            prefetchedHrefs.has(href)
        ) {
            return;
        }

        prefetchedHrefs = new Set(prefetchedHrefs).add(href);

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
            prefetchedHrefs = new Set(prefetchedHrefs);
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

    function pageCanScroll(): boolean {
        if (typeof window === 'undefined') {
            return false;
        }

        return document.documentElement.scrollHeight - window.innerHeight > 24;
    }

    function updateCompactState(): void {
        const currentScrollY = Math.max(
            window.scrollY || document.documentElement.scrollTop || 0,
            0,
        );

        if (!pageCanScroll() || currentScrollY < SCROLL_THRESHOLD) {
            isCompact = false;
            lastScrollY = currentScrollY;

            return;
        }

        const delta = currentScrollY - lastScrollY;

        if (Math.abs(delta) < SCROLL_DELTA) {
            return;
        }

        isCompact = delta > 0;
        lastScrollY = currentScrollY;
    }

    function requestCompactUpdate(): void {
        if (scrollFrameRequested) {
            return;
        }

        scrollFrameRequested = true;

        window.requestAnimationFrame(() => {
            scrollFrameRequested = false;
            updateCompactState();
        });
    }

    onMount(() => {
        lastScrollY = Math.max(
            window.scrollY || document.documentElement.scrollTop || 0,
            0,
        );
        updateCompactState();

        window.addEventListener('scroll', requestCompactUpdate, {
            passive: true,
        });
        window.addEventListener('resize', requestCompactUpdate);

        return () => {
            window.removeEventListener('scroll', requestCompactUpdate);
            window.removeEventListener('resize', requestCompactUpdate);
        };
    });

    $effect(() => {
        const matched = visibleMainItems.find((item) =>
            url.isCurrentOrParentUrl(item.href, url.currentUrl),
        );

        if (!matched) {
            return;
        }

        isCompact = false;

        if (typeof window !== 'undefined') {
            lastScrollY = Math.max(
                window.scrollY || document.documentElement.scrollTop || 0,
                0,
            );
        }
    });
</script>

<nav
    class={`fixed inset-x-0 bottom-0 z-40 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2 transition-[padding] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] md:hidden ${isCompact ? 'px-14' : 'px-2'}`}
    aria-label="Mobile bottom navigation"
>
    <div
        class={`mx-auto w-full border border-sidebar-border/70 bg-background/88 ring-1 ring-black/5 backdrop-blur-xl transition-[max-width,padding,border-radius,box-shadow] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] ${isCompact ? 'max-w-[16rem] rounded-2xl p-1 shadow-[0_12px_24px_-22px_hsl(201_96%_30%_/_0.75)]' : 'max-w-md rounded-2xl p-1.5 shadow-[0_14px_30px_-24px_hsl(201_96%_30%_/_0.7)]'}`}
    >
        <div
            class={`relative overflow-hidden transition-[border-radius] duration-300 ${isCompact ? 'rounded-[1rem]' : 'rounded-[1.15rem]'}`}
        >
            <div
                class={`pointer-events-none absolute left-0 z-0 transition-[transform,padding,inset] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] ${isCompact ? 'inset-y-0.5 px-1' : 'inset-y-[3px] px-[5px]'}`}
                style={`width: ${100 / navCount}%; transform: translateX(${activeIndex * 100}%);`}
            >
                <div
                    class={`h-full border border-cyan-300/30 bg-linear-to-b from-cyan-500/25 to-sky-500/15 shadow-[0_8px_20px_-12px_hsl(200_95%_45%_/_0.7)] transition-[border-radius] duration-300 ${isCompact ? 'rounded-lg' : 'rounded-xl'}`}
                ></div>
            </div>

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
                            onclick={(event) => visitNavItem(event, itemHref)}
                            class="group relative flex touch-manipulation select-none items-center justify-center transition-all duration-150 ease-out active:scale-[0.97] {pendingHref ===
                            itemHref
                                ? 'opacity-70'
                                : ''} {isCompact
                                ? 'h-10 rounded-lg'
                                : 'h-12 rounded-xl'} {isNavItemActive(item.href)
                                ? 'text-primary'
                                : 'text-muted-foreground/90'}"
                        >
                            {#if pendingHref === itemHref}
                                <span
                                    class="absolute right-2 top-2 size-1.5 rounded-full bg-cyan-500 shadow-[0_0_12px_rgba(6,182,212,0.9)] motion-safe:animate-ping"
                                ></span>
                            {/if}
                            {#if item.icon}
                                <item.icon
                                    class="shrink-0 transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] {isCompact
                                        ? 'size-4'
                                        : 'size-5'} {isNavItemActive(item.href)
                                        ? '-translate-y-0.5 scale-110'
                                        : 'group-hover:-translate-y-0.5'}"
                                />
                            {/if}
                        </a>
                    </li>
                {/each}
            </ul>
        </div>
    </div>
</nav>
