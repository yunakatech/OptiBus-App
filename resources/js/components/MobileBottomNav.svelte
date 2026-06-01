<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Monitor from 'lucide-svelte/icons/monitor';
    import Tickets from 'lucide-svelte/icons/tickets';
    import { onMount } from 'svelte';
    import { hasPermission } from '@/lib/access';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    const url = currentUrlState();
    const ACTIVE_NAV_STORAGE_KEY = 'cabooq_mobile_bottom_active';

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
            icon: Monitor,
            permission: 'booking.view',
        },
        {
            title: 'Carter',
            href: '/charters',
            icon: BusFront,
            permission: 'charter.view',
        },
        {
            title: 'Bagasi',
            href: '/luggages',
            icon: Briefcase,
            permission: 'luggage.view',
        },
    ];

    const permissions = $derived(page.props.auth?.permissions ?? []);
    const visibleMainItems = $derived(
        mainItems.filter((item) => hasPermission(permissions, item.permission)),
    );
    const navCount = $derived(Math.max(visibleMainItems.length, 1));
    let rememberedActiveHref = $state<string>(toUrl(dashboard()));
    let isCompact = $state(false);
    let pendingHref = $state('');
    let lastScrollY = 0;
    let scrollFrameRequested = false;

    const SCROLL_THRESHOLD = 24;
    const SCROLL_DELTA = 6;

    const isMenuPage = $derived(url.isCurrentUrl('/menu', url.currentUrl));
    const activeIndex = $derived.by(() => {
        const matchedIndex = visibleMainItems.findIndex((item) =>
            isNavItemActive(item.href),
        );

        return matchedIndex >= 0 ? matchedIndex : 0;
    });

    function markActive(href: string): void {
        rememberedActiveHref = href;

        if (typeof window !== 'undefined') {
            window.localStorage.setItem(ACTIVE_NAV_STORAGE_KEY, href);
        }
    }

    function prepareNavPress(href: string): void {
        markActive(href);
        isCompact = false;
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
        const resolved = toUrl(itemHref);

        if (isMenuPage) {
            return rememberedActiveHref === resolved;
        }

        return url.isCurrentOrParentUrl(itemHref, url.currentUrl);
    }

    function pageCanScroll(): boolean {
        if (typeof window === 'undefined') {
            return false;
        }

        return document.documentElement.scrollHeight - window.innerHeight > 24;
    }

    function updateCompactState(): void {
        const currentScrollY = Math.max(window.scrollY || document.documentElement.scrollTop || 0, 0);

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
        const stored = window.localStorage.getItem(ACTIVE_NAV_STORAGE_KEY);

        if (stored) {
            rememberedActiveHref = stored;
        }

        lastScrollY = Math.max(window.scrollY || document.documentElement.scrollTop || 0, 0);
        updateCompactState();

        window.addEventListener('scroll', requestCompactUpdate, { passive: true });
        window.addEventListener('resize', requestCompactUpdate);

        return () => {
            window.removeEventListener('scroll', requestCompactUpdate);
            window.removeEventListener('resize', requestCompactUpdate);
        };
    });

    $effect(() => {
        if (isMenuPage) {
return;
}

        const matched = visibleMainItems.find((item) =>
            url.isCurrentOrParentUrl(item.href, url.currentUrl),
        );

        if (!matched) {
return;
}

        markActive(toUrl(matched.href));
        isCompact = false;

        if (typeof window !== 'undefined') {
            lastScrollY = Math.max(window.scrollY || document.documentElement.scrollTop || 0, 0);
        }
    });
</script>

<nav
    class={`fixed inset-x-0 bottom-0 z-40 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2 transition-[padding] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] md:hidden ${isCompact ? 'px-14' : 'px-2'}`}
    aria-label="Mobile bottom navigation"
>
    <div
        class={`mx-auto w-full border border-sidebar-border/70 bg-background/85 ring-1 ring-white/10 backdrop-blur-xl transition-[max-width,padding,border-radius,box-shadow] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] ${isCompact ? 'max-w-[17rem] rounded-[1.35rem] p-1 shadow-[0_14px_32px_-24px_hsl(201_96%_30%_/_0.8)]' : 'max-w-xl rounded-[1.75rem] p-1.5 shadow-[0_18px_40px_-24px_hsl(201_96%_30%_/_0.75)]'}`}
    >
        <div class={`relative overflow-hidden transition-[border-radius] duration-300 ${isCompact ? 'rounded-[1.15rem]' : 'rounded-3xl'}`}>
            <div
                class={`pointer-events-none absolute left-0 z-0 transition-[transform,padding,inset] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] ${isCompact ? 'inset-y-0.5 px-1' : 'inset-y-1 px-1.5'}`}
                style={`width: ${100 / navCount}%; transform: translateX(${activeIndex * 100}%);`}
            >
                <div class={`h-full border border-cyan-300/30 bg-linear-to-b from-cyan-500/30 to-sky-500/20 shadow-[0_10px_25px_-12px_hsl(200_95%_45%_/_0.85)] transition-[border-radius] duration-300 ${isCompact ? 'rounded-xl' : 'rounded-2xl'}`}></div>
            </div>

            <ul class="relative z-10 grid" style={`grid-template-columns: repeat(${navCount}, minmax(0, 1fr));`}>
                {#each visibleMainItems as item (toUrl(item.href))}
                    {@const itemHref = toUrl(item.href)}
                    <li>
                        <a
                            href={itemHref}
                            aria-label={item.title}
                            title={item.title}
                            aria-busy={pendingHref === itemHref}
                            onpointerdown={() => prepareNavPress(itemHref)}
                            onclick={(event) => visitNavItem(event, itemHref)}
                            class="group flex touch-manipulation select-none items-center justify-center transition-all duration-150 ease-out active:scale-[0.97] {isCompact ? 'h-10 rounded-xl' : 'h-14 rounded-2xl'} {isNavItemActive(
                                item.href,
                            )
                                ? 'text-primary'
                                : 'text-muted-foreground/90'}"
                        >
                            {#if item.icon}
                                <item.icon
                                    class="shrink-0 transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] {isCompact ? 'size-4' : 'size-5'} {isNavItemActive(
                                        item.href,
                                    )
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
