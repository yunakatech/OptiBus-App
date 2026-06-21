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
    import Tickets from 'lucide-svelte/icons/tickets';
    import Truck from 'lucide-svelte/icons/truck';
    import UserCog from 'lucide-svelte/icons/user-cog';
    import Users from 'lucide-svelte/icons/users';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import TenantPoolSwitcher from '@/components/TenantPoolSwitcher.svelte';
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

    const pelangganNavItems: NavItem[] = [
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
    ];

    const dataMasterNavItems: NavItem[] = [
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

    const tenantNavItems: NavItem[] = [
        {
            title: 'Laporan',
            href: '/report',
            icon: ChartColumn,
            permission: 'report.view',
        },
        {
            title: 'Langganan',
            href: '/subscription',
            icon: CreditCard,
        },
        {
            title: 'Users',
            href: '/admin-ops/users',
            icon: UserCog,
            permission: 'user.manage',
        },
        {
            title: 'Logs',
            href: '/admin-ops/cancellations',
            icon: History,
            permission: 'logs.view',
        },
    ];

    const sistemNavItems: NavItem[] = [
        {
            title: 'Platform Dashboard',
            href: '/platform/dashboard',
            icon: BarChart3,
            permission: 'platform.manage',
            superAdminOnly: true,
        },
        {
            title: 'SaaS',
            href: '/admin-ops/saas',
            icon: Building2,
            permission: 'platform.manage',
            superAdminOnly: true,
        },
        {
            title: 'Role & Hak Akses',
            href: '/admin-ops/roles',
            icon: ShieldCheck,
            permission: 'role.manage',
            superAdminOnly: true,
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
            id: 'pelanggan',
            title: 'Pelanggan',
            icon: Users,
            items: pelangganNavItems,
        },
        {
            id: 'data-master',
            title: 'Data Master',
            icon: Settings2,
            items: dataMasterNavItems,
        },
        {
            id: 'tenant',
            title: 'Tenant',
            icon: UserCog,
            items: tenantNavItems,
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
    const activeTenant = $derived(page.props.auth?.active_tenant ?? null);
    const showTenantScopedSections = $derived(!isSuperAdmin || Boolean(activeTenant));
    const billingLocked = $derived(Boolean(page.props.auth?.billing_access?.locked));
    const homeHref = $derived(billingLocked ? '/subscription' : toUrl(dashboard()));
    const visibleSections = $derived(
        mainSections
            .map((section) => ({
                ...section,
                items: section.items.filter((item) =>
                    (!billingLocked || toUrl(item.href) === '/subscription') &&
                    hasPermission(permissions, item.permission) &&
                    (!item.superAdminOnly || isSuperAdmin),
                ),
            }))
            .map((section) =>
                showTenantScopedSections || section.id !== 'sistem'
                    ? section
                    : {
                          ...section,
                          items: section.items.filter((item) => {
                              const href = toUrl(item.href);
                              return href === '/platform/dashboard' || href === '/admin-ops/saas';
                          }),
                      },
            )
            .filter((section) => showTenantScopedSections || section.id === 'sistem')
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
                            href={homeHref}
                            class={`${props.class} justify-center`}
                            prefetch
                            cacheFor={30000}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem class={`px-1 group-data-[collapsible=icon]:hidden ${billingLocked ? 'hidden' : ''}`}>
                <TenantPoolSwitcher mode="desktop" />
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent
        class="gap-3 px-1 py-2 group-data-[collapsible=icon]:!overflow-visible"
    >
        <NavMain label="Navigasi" sections={visibleSections} />
    </SidebarContent>
</Sidebar>
{@render children?.()}
