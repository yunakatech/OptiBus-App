<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import CarFront from 'lucide-svelte/icons/car-front';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import History from 'lucide-svelte/icons/history';
    import IdCard from 'lucide-svelte/icons/id-card';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Package from 'lucide-svelte/icons/package';
    import Route from 'lucide-svelte/icons/route';
    import Settings2 from 'lucide-svelte/icons/settings-2';
    import Shuffle from 'lucide-svelte/icons/shuffle';
    import Tickets from 'lucide-svelte/icons/tickets';
    import Truck from 'lucide-svelte/icons/truck';
    import UserCog from 'lucide-svelte/icons/user-cog';
    import Users from 'lucide-svelte/icons/users';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { NavItem } from '@/types';
    type NavSection = {
        id: string;
        title: string;
        icon: NavItem['icon'];
        items: NavItem[];
    };

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const platformNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Data Keberangkatan',
            href: '/bookings',
            icon: Tickets,
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
        {
            title: 'Laporan',
            href: '/report',
            icon: ChartColumn,
        },
    ];

    const customerNavItems: NavItem[] = [
        {
            title: 'Reguler',
            href: '/admin-ops/customers',
            icon: Users,
        },
        {
            title: 'Bagasi',
            href: '/admin-ops/master/customer-bagasi',
            icon: Briefcase,
        },
        {
            title: 'Carter',
            href: '/admin-ops/master/customer-charter',
            icon: BusFront,
        },
    ];

    const settingsNavItems: NavItem[] = [
        {
            title: 'Jadwal',
            href: '/admin-ops/schedules',
            icon: CalendarDays,
        },
        {
            title: 'Logs',
            href: '/admin-ops/cancellations',
            icon: History,
        },
        {
            title: 'Rute Induk',
            href: '/admin-ops/routes',
            icon: Route,
        },
        {
            title: 'Pool',
            href: '/admin-ops/pools',
            icon: Route,
        },
        {
            title: 'Master Carter',
            href: '/admin-ops/master/rute-carter',
            icon: BusFront,
        },
        {
            title: 'Segment',
            href: '/admin-ops/segments',
            icon: Shuffle,
        },
        {
            title: 'Tarif Bagasi',
            href: '/admin-ops/services',
            icon: Package,
        },
        {
            title: 'Driver',
            href: '/admin-ops/drivers',
            icon: IdCard,
        },
        {
            title: 'Kategori Armada',
            href: '/admin-ops/units',
            icon: Truck,
        },
        {
            title: 'Armada',
            href: '/admin-ops/armadas',
            icon: CarFront,
        },
        {
            title: 'Users',
            href: '/admin-ops/users',
            icon: UserCog,
        },
    ];

    const mainSections: NavSection[] = [
        {
            id: 'platform',
            title: 'Platform',
            icon: LayoutGrid,
            items: platformNavItems,
        },
        {
            id: 'customer',
            title: 'Customer',
            icon: Users,
            items: customerNavItems,
        },
        {
            id: 'pengaturan',
            title: 'Pengaturan',
            icon: Settings2,
            items: settingsNavItems,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader class="border-b border-sidebar-border/70">
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton
                    size="lg"
                    asChild
                    class="rounded-xl hover:bg-sidebar-accent/60"
                >
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={`${props.class} justify-center`}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent
        class="gap-3 px-1 py-2 group-data-[collapsible=icon]:!overflow-visible"
    >
        <NavMain label="Navigasi" sections={mainSections} />
    </SidebarContent>
</Sidebar>
{@render children?.()}
