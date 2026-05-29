<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Menu',
                href: '/menu',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import CarFront from 'lucide-svelte/icons/car-front';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import History from 'lucide-svelte/icons/history';
    import IdCard from 'lucide-svelte/icons/id-card';
    import MapPinned from 'lucide-svelte/icons/map-pinned';
    import Package from 'lucide-svelte/icons/package';
    import Route from 'lucide-svelte/icons/route';
    import Shuffle from 'lucide-svelte/icons/shuffle';
    import Truck from 'lucide-svelte/icons/truck';
    import UserCog from 'lucide-svelte/icons/user-cog';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';

    const url = currentUrlState();
    const menuSections = [
        {
            label: 'Customer',
            items: [
                { title: 'Reguler', href: '/admin-ops/customers', icon: Users },
                { title: 'Bagasi', href: '/admin-ops/master/customer-bagasi', icon: Briefcase },
                { title: 'Carter', href: '/admin-ops/master/customer-charter', icon: BusFront },
            ],
        },
        {
            label: 'Pengaturan',
            items: [
                { title: 'Laporan', href: '/report', icon: ChartColumn },
                { title: 'Jadwal', href: '/admin-ops/schedules', icon: CalendarDays },
                { title: 'Logs', href: '/admin-ops/cancellations', icon: History },
                { title: 'Rute Induk', href: '/admin-ops/routes', icon: Route },
                { title: 'Master Carter', href: '/admin-ops/master/rute-carter', icon: MapPinned },
                { title: 'Segment', href: '/admin-ops/segments', icon: Shuffle },
                { title: 'Tarif Bagasi', href: '/admin-ops/services', icon: Package },
                { title: 'Kategori Armada', href: '/admin-ops/units', icon: Truck },
                { title: 'Armada', href: '/admin-ops/armadas', icon: CarFront },
                { title: 'Driver', href: '/admin-ops/drivers', icon: IdCard },
                { title: 'Users', href: '/admin-ops/users', icon: UserCog },
            ],
        },
    ] as const;
</script>

<AppHead title="Menu" />

<div class="min-h-full w-full bg-background px-3 pt-3 pb-6 md:px-4">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        {#each menuSections as section (section.label)}
            <section class="space-y-3">
                <h2 class="px-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">{section.label}</h2>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-4">
                    {#each section.items as item (item.href)}
                        <Link
                            href={toUrl(item.href)}
                            class="flex min-h-16 items-center justify-center gap-2 rounded-lg border bg-card px-2 text-xs font-semibold text-foreground transition-colors {url.isCurrentOrParentUrl(
                                item.href,
                                url.currentUrl,
                            )
                                ? 'border-primary bg-primary/10 text-primary'
                                : 'hover:bg-accent'}"
                        >
                            <item.icon class="size-4 shrink-0" />
                            <span class="truncate">{item.title}</span>
                        </Link>
                    {/each}
                </div>
            </section>
        {/each}
    </div>
</div>

<MobileBottomNav />
