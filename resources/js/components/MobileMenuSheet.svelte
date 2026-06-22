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
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import MapPinned from 'lucide-svelte/icons/map-pinned';
    import Plus from 'lucide-svelte/icons/plus';
    import Package from 'lucide-svelte/icons/package';
    import Route from 'lucide-svelte/icons/route';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import Truck from 'lucide-svelte/icons/truck';
    import UserCog from 'lucide-svelte/icons/user-cog';
    import Users from 'lucide-svelte/icons/users';
    import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
    import { hasPermission } from '@/lib/access';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';

    let {
        open = $bindable(false)
    }: {
        open: boolean;
    } = $props();

    const url = currentUrlState();
    const menuSections = [
        {
            label: 'Operasional',
            items: [
                { title: 'Dashboard', href: dashboard(), icon: LayoutGrid, permission: 'dashboard.view' },
                { title: 'Keberangkatan', href: '/bookings', icon: CalendarDays, permission: 'booking.view' },
                { title: 'Console', href: '/booking-console', icon: Plus, permission: 'booking.view' },
                { title: 'Carter', href: '/charters', icon: BusFront, permission: 'charter.view' },
                { title: 'Bagasi', href: '/luggages', icon: Briefcase, permission: 'luggage.view' },
                { title: 'Pembayaran', href: '/payments', icon: CreditCard, permission: ['payment.update', 'booking.update', 'charter.update', 'luggage.update'] },
            ],
        },
        {
            label: 'Pelanggan',
            items: [
                { title: 'Reguler', href: '/admin-ops/customers', icon: Users, permission: 'customer.view' },
                { title: 'Bagasi', href: '/admin-ops/customer-bagasi', icon: Briefcase, permission: 'customer.view' },
                { title: 'Carter', href: '/admin-ops/customer-charter', icon: BusFront, permission: 'customer.view' },
            ],
        },
        {
            label: 'Data Master',
            items: [
                { title: 'Jadwal', href: '/admin-ops/jadwal', icon: CalendarDays, permission: 'master.view' },
                { title: 'Rute Induk', href: '/admin-ops/rute-induk', icon: Route, permission: 'master.view' },
                { title: 'Pool', href: '/admin-ops/pool', icon: Building2, permission: 'pool.manage' },
                { title: 'Master Carter', href: '/admin-ops/rute-carter', icon: MapPinned, permission: 'master.view' },
                { title: 'Tarif Bagasi', href: '/admin-ops/tarif-bagasi', icon: Package, permission: 'master.view' },
                { title: 'Kategori Armada', href: '/admin-ops/kategori-armada', icon: Truck, permission: 'master.view' },
                { title: 'Armada', href: '/admin-ops/armada', icon: CarFront, permission: 'armada.view' },
                { title: 'Driver', href: '/admin-ops/driver', icon: IdCard, permission: 'driver.view' },
            ],
        },
        {
            label: 'Tenant',
            items: [
                { title: 'Laporan', href: '/report', icon: ChartColumn, permission: 'report.view' },
                { title: 'Langganan', href: '/subscription', icon: CreditCard },
                { title: 'Users', href: '/admin-ops/users', icon: UserCog, permission: 'user.manage' },
                { title: 'Logs', href: '/admin-ops/cancellations', icon: History, permission: 'logs.view' },
            ],
        },
        {
            label: 'Sistem',
            items: [
                { title: 'Platform Dashboard', href: '/platform/dashboard', icon: Building2, permission: 'platform.manage', superAdminOnly: true },
                { title: 'SaaS', href: '/admin-ops/saas', icon: Building2, permission: 'platform.manage', superAdminOnly: true },
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
    const activeTenant = $derived(page.props.auth?.active_tenant ?? null);
    const showTenantScopedSections = $derived(!isSuperAdmin || Boolean(activeTenant));
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
            .map((section) =>
                showTenantScopedSections || section.label !== 'Sistem'
                    ? section
                    : {
                          ...section,
                          items: section.items.filter((item) => {
                              const href = toUrl(item.href);
                              return href === '/platform/dashboard' || href === '/admin-ops/saas';
                          }),
                      },
            )
            .filter((section) => showTenantScopedSections || section.label === 'Sistem')
            .filter((section) => section.items.length > 0),
    );
</script>

<Sheet bind:open>
    <SheetContent side="bottom" class="h-[85vh] rounded-t-3xl sm:h-[90vh]">
        <SheetHeader class="text-left px-2 sm:px-0 mt-3 pb-2">
            <SheetTitle>Menu Navigasi</SheetTitle>
        </SheetHeader>
        <div class="h-full w-full overflow-y-auto px-2 pb-24 pt-2">
            <div class="mx-auto w-full max-w-6xl space-y-6">
                {#each visibleMenuSections as section (section.label)}
                    <section class="space-y-3">
                        <h2 class="px-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">{section.label}</h2>
                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                            {#each section.items as item (item.href)}
                                <Link
                                    href={toUrl(item.href)}
                                    onclick={() => { open = false; }}
                                    class="flex min-h-[5.5rem] flex-col items-center justify-center gap-2 rounded-xl border bg-card px-2 py-3 text-center text-xs font-semibold text-foreground transition-colors {url.isCurrentOrParentUrl(
                                        item.href,
                                        url.currentUrl,
                                    )
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'hover:bg-accent'}"
                                >
                                    <item.icon class="size-6 shrink-0" />
                                    <span class="line-clamp-2 leading-snug">{item.title}</span>
                                </Link>
                            {/each}
                        </div>
                    </section>
                {/each}
            </div>
        </div>
    </SheetContent>
</Sheet>
