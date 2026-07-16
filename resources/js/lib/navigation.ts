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
import Plus from 'lucide-svelte/icons/plus';
import Route from 'lucide-svelte/icons/route';
import Settings2 from 'lucide-svelte/icons/settings-2';
import ShieldCheck from 'lucide-svelte/icons/shield-check';
import Tickets from 'lucide-svelte/icons/tickets';
import Truck from 'lucide-svelte/icons/truck';
import UserCog from 'lucide-svelte/icons/user-cog';
import Users from 'lucide-svelte/icons/users';
import { hasPermission } from '@/lib/access';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

export type NavSection = {
    id: string;
    title: string;
    icon?: NavItem['icon'];
    items: NavItem[];
};

export type FlatNavItem = NavItem & {
    sectionId: string;
    sectionTitle: string;
};

type AuthLike =
    | {
          permissions?: string[] | null;
          user?: {
              is_super_admin?: boolean | null;
          } | null;
          active_tenant?: unknown;
          billing_access?: {
              locked?: boolean | null;
          } | null;
      }
    | null
    | undefined;

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
        permission: [
            'payment.update',
            'booking.update',
            'charter.update',
            'luggage.update',
        ],
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
        href: '/admin-ops/customer-bagasi',
        icon: Briefcase,
        permission: 'customer.view',
    },
    {
        title: 'Pelanggan Carter',
        href: '/admin-ops/customer-charter',
        icon: BusFront,
        permission: 'customer.view',
    },
];

const dataMasterNavItems: NavItem[] = [
    {
        title: 'Jadwal',
        href: '/admin-ops/jadwal',
        icon: CalendarDays,
        permission: 'master.view',
    },
    {
        title: 'Rute Induk',
        href: '/admin-ops/rute-induk',
        icon: Route,
        permission: 'master.view',
    },
    {
        title: 'Master Carter',
        href: '/admin-ops/rute-carter',
        icon: BusFront,
        permission: 'master.view',
    },
    {
        title: 'Tarif Bagasi',
        href: '/admin-ops/tarif-bagasi',
        icon: Package,
        permission: 'master.view',
    },
    {
        title: 'Pool',
        href: '/admin-ops/pool',
        icon: Building2,
        permission: 'pool.manage',
    },
    {
        title: 'Driver',
        href: '/admin-ops/driver',
        icon: IdCard,
        permission: 'driver.view',
    },
    {
        title: 'Kategori Armada',
        href: '/admin-ops/kategori-armada',
        icon: Truck,
        permission: 'master.view',
    },
    {
        title: 'Armada',
        href: '/admin-ops/armada',
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

const mobilePrimaryItems: NavItem[] = [
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

const billingLockedItems: NavItem[] = [
    {
        title: 'Langganan',
        href: '/subscription',
        icon: CreditCard,
    },
];

export function getMainNavSections(): NavSection[] {
    return [
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
}

export function getVisibleNavSections(auth: AuthLike): NavSection[] {
    const permissions = auth?.permissions ?? [];
    const isSuperAdmin = Boolean(auth?.user?.is_super_admin);
    const activeTenant = auth?.active_tenant ?? null;
    const showTenantScopedSections = !isSuperAdmin || Boolean(activeTenant);
    const billingLocked = Boolean(auth?.billing_access?.locked);

    return getMainNavSections()
        .map((section) => ({
            ...section,
            items: section.items.filter(
                (item) =>
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
                          return (
                              href === '/platform/dashboard' ||
                              href === '/admin-ops/saas'
                          );
                      }),
                  },
        )
        .filter(
            (section) => showTenantScopedSections || section.id === 'sistem',
        )
        .filter((section) => section.items.length > 0);
}

export function flattenNavSections(sections: NavSection[]): FlatNavItem[] {
    return sections.flatMap((section) =>
        section.items.map((item) => ({
            ...item,
            sectionId: section.id,
            sectionTitle: section.title,
        })),
    );
}

export function getVisibleMobileNavItems(auth: AuthLike): NavItem[] {
    const permissions = auth?.permissions ?? [];
    const billingLocked = Boolean(auth?.billing_access?.locked);

    return billingLocked
        ? billingLockedItems
        : mobilePrimaryItems.filter((item) =>
              hasPermission(permissions, item.permission),
          );
}
