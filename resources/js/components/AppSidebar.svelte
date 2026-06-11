<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import Building2 from 'lucide-svelte/icons/building-2';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import CarFront from 'lucide-svelte/icons/car-front';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import History from 'lucide-svelte/icons/history';
    import IdCard from 'lucide-svelte/icons/id-card';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Package from 'lucide-svelte/icons/package';
    import Route from 'lucide-svelte/icons/route';
    import Settings2 from 'lucide-svelte/icons/settings-2';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
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
    import { hasPermission } from '@/lib/access';
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

    const operasionalNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
            permission: 'dashboard.view',
        },
        {
            title: 'Data Keberangkatan',
            href: '/bookings',
            icon: Tickets,
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
        {
            title: 'Pembayaran',
            href: '/payments',
            icon: CreditCard,
            permission: ['payment.update', 'booking.update', 'charter.update', 'luggage.update'],
        },
    ];

    const laporanNavItems: NavItem[] = [
        {
            title: 'Laporan',
            href: '/report',
            icon: ChartColumn,
            permission: 'report.view',
        },
    ];

    const dataMasterNavItems: NavItem[] = [
        {
            title: 'Pelanggan Reguler',
            href: '/admin-ops/customers',
            icon: Users,
            permission: 'customer.view',
        },
        {
            title: 'Pelanggan Bagasi',
            href: '/admin-ops/master/customer-bagasi',
            icon: Briefcase,
            permission: 'customer.view',
        },
        {
            title: 'Pelanggan Carter',
            href: '/admin-ops/master/customer-charter',
            icon: BusFront,
            permission: 'customer.view',
        },
        {
            title: 'Jadwal',
            href: '/admin-ops/schedules',
            icon: CalendarDays,
            permission: 'master.view',
        },
        {
            title: 'Rute Induk',
            href: '/admin-ops/routes',
            icon: Route,
            permission: 'master.view',
        },
        {
            title: 'Master Carter',
            href: '/admin-ops/master/rute-carter',
            icon: BusFront,
            permission: 'master.view',
        },
        {
            title: 'Segment',
            href: '/admin-ops/segments',
            icon: Shuffle,
            permission: 'master.view',
        },
        {
            title: 'Tarif Bagasi',
            href: '/admin-ops/services',
            icon: Package,
            permission: 'master.view',
        },
        {
            title: 'Pool',
            href: '/admin-ops/pools',
            icon: Building2,
            permission: 'pool.manage',
        },
        {
            title: 'Driver',
            href: '/admin-ops/drivers',
            icon: IdCard,
            permission: 'driver.view',
        },
        {
            title: 'Kategori Armada',
            href: '/admin-ops/units',
            icon: Truck,
            permission: 'master.view',
        },
        {
            title: 'Armada',
            href: '/admin-ops/armadas',
            icon: CarFront,
            permission: 'armada.view',
        },
    ];

    const sistemNavItems: NavItem[] = [
        {
            title: 'Platform Dashboard',
            href: '/platform/dashboard',
            icon: BarChart3,
            permission: 'pool.manage',
            superAdminOnly: true,
        },
        {
            title: 'SaaS',
            href: '/admin-ops/saas',
            icon: Building2,
            permission: 'pool.manage',
            superAdminOnly: true,
        },
        {
            title: 'Users',
            href: '/admin-ops/users',
            icon: UserCog,
            permission: 'user.manage',
        },
        {
            title: 'Role & Hak Akses',
            href: '/admin-ops/roles',
            icon: ShieldCheck,
            permission: 'role.manage',
            superAdminOnly: true,
        },
        {
            title: 'Logs',
            href: '/admin-ops/cancellations',
            icon: History,
            permission: 'logs.view',
        },
    ];

    const mainSections: NavSection[] = [
        {
            id: 'operasional',
            title: 'Operasional',
            icon: LayoutGrid,
            items: operasionalNavItems,
        },
        {
            id: 'data-master',
            title: 'Data Master',
            icon: Settings2,
            items: dataMasterNavItems,
        },
        {
            id: 'laporan',
            title: 'Laporan',
            icon: ChartColumn,
            items: laporanNavItems,
        },
        {
            id: 'sistem',
            title: 'Sistem',
            icon: ShieldCheck,
            items: sistemNavItems,
        },
    ];

    const permissions = $derived(page.props.auth?.permissions ?? []);
    const isSuperAdmin = $derived(Boolean(page.props.auth?.user?.is_super_admin));
    const visibleSections = $derived(
        mainSections
            .map((section) => ({
                ...section,
                items: section.items.filter((item) =>
                    hasPermission(permissions, item.permission) &&
                    (!item.superAdminOnly || isSuperAdmin),
                ),
            }))
            .filter((section) => section.items.length > 0),
    );
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
                            prefetch
                            cacheFor={30000}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
            {#if page.props.auth?.pools && page.props.auth.pools.length > 0}
                <SidebarMenuItem>
                    <div class="relative mx-1 group/pool-switcher">
                        <button
                            type="button"
                            class="flex w-full items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] font-medium transition {page.props.auth?.active_pool ? 'border-primary/30 bg-primary/8 text-primary' : 'border-sidebar-border/70 text-muted-foreground hover:border-primary/30 hover:bg-primary/5'}"
                        >
                            <Building2 class="size-3 shrink-0" />
                            <span class="truncate">{page.props.auth?.active_pool?.name ?? 'Semua Pool'}</span>
                            <svg class="ml-auto size-3 shrink-0 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <!-- Dropdown (hidden by default, shown on click via Svelte) -->
                        <div class="absolute left-0 top-full z-50 mt-1 hidden w-52 rounded-xl border border-border/80 bg-background shadow-lg group-focus-within/pool-switcher:block group-hover/pool-switcher:block">
                            <a href="/api/admin/pool/reset" class="block px-3 py-2 text-xs text-muted-foreground hover:bg-muted/50 rounded-t-xl"
                               onclick={(e) => {
                                   e.preventDefault();
                                   fetch('/api/admin/pool/switch', {
                                       method: 'POST',
                                       headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content || ''},
                                       body: JSON.stringify({pool_id: 0}),
                                   }).finally(() => window.location.reload());
                               }}>
                                <span>Semua Pool</span>
                            </a>
                            {#each page.props.auth.pools as pool (pool.id)}
                                <a href="?pool_id={pool.id}" class="block px-3 py-2 text-xs hover:bg-muted/50 {page.props.auth?.active_pool?.id === pool.id ? 'bg-primary/5 font-medium text-primary' : 'text-foreground'}"
                                   onclick={(e) => {
                                       e.preventDefault();
                                       fetch('/api/admin/pool/switch', {
                                           method: 'POST',
                                           headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content || ''},
                                           body: JSON.stringify({pool_id: pool.id}),
                                       }).finally(() => window.location.reload());
                                   }}>
                                    {pool.name}
                                    {#if pool.code}<span class="ml-1 text-[10px] text-muted-foreground">({pool.code})</span>{/if}
                                </a>
                            {/each}
                        </div>
                    </div>
                </SidebarMenuItem>
            {/if}
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent
        class="gap-3 px-1 py-2 group-data-[collapsible=icon]:!overflow-visible"
    >
        <NavMain label="Navigasi" sections={visibleSections} />
    </SidebarContent>
</Sidebar>
{@render children?.()}
