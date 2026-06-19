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
    import { Link, page } from '@inertiajs/svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import Building2 from 'lucide-svelte/icons/building-2';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import CarFront from 'lucide-svelte/icons/car-front';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import History from 'lucide-svelte/icons/history';
    import IdCard from 'lucide-svelte/icons/id-card';
    import MapPinned from 'lucide-svelte/icons/map-pinned';
    import Package from 'lucide-svelte/icons/package';
    import Route from 'lucide-svelte/icons/route';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import Shuffle from 'lucide-svelte/icons/shuffle';
    import Truck from 'lucide-svelte/icons/truck';
    import UserCog from 'lucide-svelte/icons/user-cog';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import { hasPermission } from '@/lib/access';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';

    const url = currentUrlState();
    const menuSections = [
        {
            label: 'Pelanggan',
            items: [
                { title: 'Reguler', href: '/admin-ops/customers', icon: Users, permission: 'customer.view' },
                { title: 'Bagasi', href: '/admin-ops/master/customer-bagasi', icon: Briefcase, permission: 'customer.view' },
                { title: 'Carter', href: '/admin-ops/master/customer-charter', icon: BusFront, permission: 'customer.view' },
            ],
        },
        {
            label: 'Pengaturan',
            items: [
                { title: 'Laporan', href: '/report', icon: ChartColumn, permission: 'report.view' },
                { title: 'Pembayaran', href: '/payments', icon: CreditCard, permission: ['payment.update', 'booking.update', 'charter.update', 'luggage.update'] },
                { title: 'Jadwal', href: '/admin-ops/schedules', icon: CalendarDays, permission: 'master.view' },
                { title: 'Logs', href: '/admin-ops/cancellations', icon: History, permission: 'logs.view' },
                { title: 'Rute Induk', href: '/admin-ops/routes', icon: Route, permission: 'master.view' },
                { title: 'Pool', href: '/admin-ops/pools', icon: Building2, permission: 'pool.manage' },
                { title: 'Master Carter', href: '/admin-ops/master/rute-carter', icon: MapPinned, permission: 'master.view' },
                { title: 'Segment', href: '/admin-ops/segments', icon: Shuffle, permission: 'master.view' },
                { title: 'Tarif Bagasi', href: '/admin-ops/services', icon: Package, permission: 'master.view' },
                { title: 'Kategori Armada', href: '/admin-ops/units', icon: Truck, permission: 'master.view' },
                { title: 'Armada', href: '/admin-ops/armadas', icon: CarFront, permission: 'armada.view' },
                { title: 'Driver', href: '/admin-ops/drivers', icon: IdCard, permission: 'driver.view' },
                { title: 'Users', href: '/admin-ops/users', icon: UserCog, permission: 'user.manage' },
                { title: 'Role & Hak Akses', href: '/admin-ops/roles', icon: ShieldCheck, permission: 'role.manage', superAdminOnly: true },
            ],
        },
    ] as const;
    const billingMenuSections = [
        {
            label: 'Akun',
            items: [
                {
                    title: 'Langganan',
                    href: '/subscription',
                    icon: CreditCard,
                },
            ],
        },
    ] as const;

    const permissions = $derived(page.props.auth?.permissions ?? []);
    const isSuperAdmin = $derived(Boolean(page.props.auth?.user?.is_super_admin));
    const billingLocked = $derived(Boolean(page.props.auth?.billing_access?.locked));
    const visibleMenuSections = $derived(
        (billingLocked ? billingMenuSections : menuSections)
            .map((section) => ({
                ...section,
                items: section.items.filter((item) =>
                    hasPermission(
                        permissions,
                        'permission' in item ? item.permission : undefined,
                    ) &&
                    (!('superAdminOnly' in item) ||
                        !item.superAdminOnly ||
                        isSuperAdmin),
                ),
            }))
            .filter((section) => section.items.length > 0),
    );
</script>

<AppHead title="Menu" />

<div class="min-h-full w-full bg-background px-4 pt-3 pb-28 md:px-4 md:pb-4">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        {#each visibleMenuSections as section (section.label)}
            <section class="space-y-3">
                <h2 class="px-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">{section.label}</h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
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
