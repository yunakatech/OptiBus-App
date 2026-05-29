<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Monitor from 'lucide-svelte/icons/monitor';
    import Tickets from 'lucide-svelte/icons/tickets';
    import { onMount } from 'svelte';
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
        },
        {
            title: 'Keberangkatan',
            href: '/bookings',
            icon: Tickets,
        },
        {
            title: 'Console',
            href: '/booking-console',
            icon: Monitor,
        },
        {
            title: 'Carter',
            href: '/charters',
            icon: BusFront,
        },
        {
            title: 'Bagasi',
            href: '/luggages',
            icon: Briefcase,
        },
    ];

    let rememberedActiveHref = $state<string>(toUrl(dashboard()));

    const isMenuPage = $derived(url.isCurrentUrl('/menu', url.currentUrl));
    const activeIndex = $derived.by(() => {
        const matchedIndex = mainItems.findIndex((item) => isNavItemActive(item.href));

        return matchedIndex >= 0 ? matchedIndex : 0;
    });

    function markActive(href: string): void {
        rememberedActiveHref = href;

        if (typeof window !== 'undefined') {
            window.localStorage.setItem(ACTIVE_NAV_STORAGE_KEY, href);
        }
    }

    function isNavItemActive(itemHref: NonNullable<NavItem['href']>): boolean {
        const resolved = toUrl(itemHref);

        if (isMenuPage) {
            return rememberedActiveHref === resolved;
        }

        return url.isCurrentOrParentUrl(itemHref, url.currentUrl);
    }

    onMount(() => {
        const stored = window.localStorage.getItem(ACTIVE_NAV_STORAGE_KEY);

        if (stored) {
            rememberedActiveHref = stored;
        }
    });

    $effect(() => {
        if (isMenuPage) {
return;
}

        const matched = mainItems.find((item) => url.isCurrentOrParentUrl(item.href, url.currentUrl));

        if (!matched) {
return;
}

        markActive(toUrl(matched.href));
    });
</script>

<nav
    class="fixed inset-x-0 bottom-0 z-40 px-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2 md:hidden"
    aria-label="Mobile bottom navigation"
>
    <div
        class="mx-auto w-full max-w-xl rounded-[1.75rem] border border-sidebar-border/70 bg-background/85 p-1.5 shadow-[0_18px_40px_-24px_hsl(201_96%_30%_/_0.75)] ring-1 ring-white/10 backdrop-blur-xl"
    >
        <div class="relative overflow-hidden rounded-3xl">
            <div
                class="pointer-events-none absolute inset-y-1 left-0 z-0 px-1.5 transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
                style={`width: ${100 / mainItems.length}%; transform: translateX(${activeIndex * 100}%);`}
            >
                <div class="h-full rounded-2xl border border-cyan-300/30 bg-linear-to-b from-cyan-500/30 to-sky-500/20 shadow-[0_10px_25px_-12px_hsl(200_95%_45%_/_0.85)]"></div>
            </div>

            <ul class="relative z-10 grid" style={`grid-template-columns: repeat(${mainItems.length}, minmax(0, 1fr));`}>
                {#each mainItems as item (toUrl(item.href))}
                    <li>
                        <Link
                            href={toUrl(item.href)}
                            aria-label={item.title}
                            title={item.title}
                            onclick={() => markActive(toUrl(item.href))}
                            class="group flex h-14 items-center justify-center rounded-2xl transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] active:scale-[0.97] {isNavItemActive(
                                item.href,
                            )
                                ? 'text-primary'
                                : 'text-muted-foreground/90'}"
                        >
                            {#if item.icon}
                                <item.icon
                                    class="size-5 shrink-0 transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] {isNavItemActive(
                                        item.href,
                                    )
                                        ? '-translate-y-0.5 scale-110'
                                        : 'group-hover:-translate-y-0.5'}"
                                />
                            {/if}
                        </Link>
                    </li>
                {/each}
            </ul>
        </div>
    </div>
</nav>
