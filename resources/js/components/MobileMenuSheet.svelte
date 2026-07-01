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
    import {
        Sheet,
        SheetClose,
        SheetContent,
        SheetHeader,
        SheetTitle,
    } from '@/components/ui/sheet';
    import X from 'lucide-svelte/icons/x';
    import { hasPermission } from '@/lib/access';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { mobileHiddenMenuHrefs } from '@/lib/mobileNavigation';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';

    let {
        open = $bindable(false),
    }: {
        open: boolean;
    } = $props();

    const url = currentUrlState();
    const menuSections = [
        {
            label: 'Operasional',
            items: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                    permission: 'dashboard.view',
                },
                {
                    title: 'Keberangkatan',
                    href: '/bookings',
                    icon: CalendarDays,
                    permission: 'booking.view',
                },
                {
                    title: 'Console',
                    href: '/booking-console',
                    icon: Plus,
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
            ],
        },
        {
            label: 'Pelanggan',
            items: [
                {
                    title: 'Reguler',
                    href: '/admin-ops/customers',
                    icon: Users,
                    permission: 'customer.view',
                },
                {
                    title: 'Bagasi',
                    href: '/admin-ops/customer-bagasi',
                    icon: Briefcase,
                    permission: 'customer.view',
                },
                {
                    title: 'Carter',
                    href: '/admin-ops/customer-charter',
                    icon: BusFront,
                    permission: 'customer.view',
                },
            ],
        },
        {
            label: 'Data Master',
            items: [
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
                    title: 'Pool',
                    href: '/admin-ops/pool',
                    icon: Building2,
                    permission: 'pool.manage',
                },
                {
                    title: 'Master Carter',
                    href: '/admin-ops/rute-carter',
                    icon: MapPinned,
                    permission: 'master.view',
                },
                {
                    title: 'Tarif Bagasi',
                    href: '/admin-ops/tarif-bagasi',
                    icon: Package,
                    permission: 'master.view',
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
                {
                    title: 'Driver',
                    href: '/admin-ops/driver',
                    icon: IdCard,
                    permission: 'driver.view',
                },
            ],
        },
        {
            label: 'Tenant',
            items: [
                {
                    title: 'Laporan',
                    href: '/report',
                    icon: ChartColumn,
                    permission: 'report.view',
                },
                { title: 'Langganan', href: '/subscription', icon: CreditCard },
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
            ],
        },
        {
            label: 'Sistem',
            items: [
                {
                    title: 'Platform Dashboard',
                    href: '/platform/dashboard',
                    icon: Building2,
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
    const isSuperAdmin = $derived(
        Boolean(page.props.auth?.user?.is_super_admin),
    );
    const activeTenant = $derived(page.props.auth?.active_tenant ?? null);
    const showTenantScopedSections = $derived(
        !isSuperAdmin || Boolean(activeTenant),
    );
    const billingLocked = $derived(
        Boolean(page.props.auth?.billing_access?.locked),
    );
    const hiddenMenuHrefs = $derived(mobileHiddenMenuHrefs(billingLocked));
    const visibleMenuSections = $derived(
        (billingLocked ? billingMenuSections : menuSections)
            .map((section) => ({
                ...section,
                items: section.items.filter(
                    (item) =>
                        hasPermission(
                            permissions,
                            'permission' in item ? item.permission : undefined,
                        ) &&
                        (!('superAdminOnly' in item) ||
                            !item.superAdminOnly ||
                            isSuperAdmin) &&
                        !hiddenMenuHrefs.has(toUrl(item.href)),
                ),
            }))
            .map((section) =>
                showTenantScopedSections || section.label !== 'Sistem'
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
                (section) =>
                    showTenantScopedSections || section.label === 'Sistem',
            )
            .filter((section) => section.items.length > 0),
    );

    const sectionThemes = {
        Operasional: {
            shell: 'border-cyan-500/15 bg-cyan-500/[0.05]',
            accent: 'bg-cyan-500',
            badge: 'bg-cyan-500/10 text-cyan-800 dark:text-cyan-200',
        },
        Pelanggan: {
            shell: 'border-emerald-500/15 bg-emerald-500/[0.05]',
            accent: 'bg-emerald-500',
            badge: 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-200',
        },
        'Data Master': {
            shell: 'border-amber-500/15 bg-amber-500/[0.06]',
            accent: 'bg-amber-500',
            badge: 'bg-amber-500/10 text-amber-800 dark:text-amber-200',
        },
        Tenant: {
            shell: 'border-sky-500/15 bg-sky-500/[0.05]',
            accent: 'bg-sky-500',
            badge: 'bg-sky-500/10 text-sky-800 dark:text-sky-200',
        },
        Sistem: {
            shell: 'border-slate-500/15 bg-slate-500/[0.05]',
            accent: 'bg-slate-500',
            badge: 'bg-slate-500/10 text-slate-700 dark:text-slate-200',
        },
        Akun: {
            shell: 'border-violet-500/15 bg-violet-500/[0.05]',
            accent: 'bg-violet-500',
            badge: 'bg-violet-500/10 text-violet-800 dark:text-violet-200',
        },
    } as const;

    function getSectionTheme(label: string) {
        return (
            sectionThemes[label as keyof typeof sectionThemes] ?? {
                shell: 'border-border/70 bg-card/70',
                accent: 'bg-muted-foreground',
                badge: 'bg-muted text-muted-foreground',
            }
        );
    }
</script>

<Sheet bind:open>
    <SheetContent
        side="bottom"
        showCloseButton={false}
        class="h-[100dvh] w-screen max-w-none gap-0 overflow-y-hidden rounded-none border-0 p-0 shadow-none transition-[transform] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] sm:h-[100dvh]"
    >
        <div class="flex h-full w-full flex-col overflow-hidden bg-background">
            <SheetHeader
                class="sticky top-0 z-30 mb-0 flex-row items-start justify-between border-b border-border/70 px-4 pb-3 pt-[calc(0.75rem+env(safe-area-inset-top))] text-left backdrop-blur supports-[backdrop-filter]:bg-background/90"
            >
                <div class="min-w-0 space-y-1 pr-4">
                    <SheetTitle>Menu Navigasi</SheetTitle>
                    <p class="text-xs text-muted-foreground">
                        Menu penuh layar untuk navigasi cepat di mobile.
                    </p>
                </div>
                <SheetClose asChild>
                    {#snippet children(props)}
                        <button
                            {...props}
                            type="button"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full border border-border/80 bg-card px-3 py-2 text-xs font-semibold text-foreground shadow-sm transition-colors hover:bg-accent"
                            aria-label="Tutup menu navigasi"
                        >
                            <X class="size-4" />
                            <span>Tutup</span>
                        </button>
                    {/snippet}
                </SheetClose>
            </SheetHeader>
            <div
                class="scrollbar-thin flex min-h-0 w-full flex-1 overflow-y-auto px-3 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-4 sm:px-6"
            >
                <div class="mx-auto w-full max-w-6xl space-y-5">
                    {#each visibleMenuSections as section (section.label)}
                        {@const sectionTheme = getSectionTheme(section.label)}
                        <section
                            class={`space-y-3 rounded-3xl border p-3 shadow-sm ${sectionTheme.shell}`}
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class={`h-2.5 w-2.5 rounded-full ${sectionTheme.accent}`}
                                    ></span>
                                    <h2
                                        class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                                    >
                                        {section.label}
                                    </h2>
                                </div>
                                <span
                                    class={`rounded-full px-2 py-1 text-[10px] font-medium ${sectionTheme.badge}`}
                                >
                                    {section.items.length} menu
                                </span>
                            </div>
                            <div
                                class="grid grid-cols-2 gap-2 min-[390px]:gap-3 sm:grid-cols-3 md:grid-cols-4"
                            >
                                {#each section.items as item (item.href)}
                                    <Link
                                        href={toUrl(item.href)}
                                        onclick={() => {
                                            open = false;
                                        }}
                                        class="flex min-h-[5.75rem] flex-col items-center justify-center gap-1 rounded-2xl border bg-background px-3 py-3 text-center text-xs font-semibold text-foreground transition-[background-color,border-color,color,transform,box-shadow] duration-200 hover:-translate-y-0.5 {url.isCurrentOrParentUrl(
                                            item.href,
                                            url.currentUrl,
                                        )
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'hover:bg-accent'}"
                                    >
                                        <item.icon class="size-5 shrink-0" />
                                        <span class="line-clamp-2 leading-tight"
                                            >{item.title}</span
                                        >
                                    </Link>
                                {/each}
                            </div>
                        </section>
                    {/each}
                </div>
            </div>
        </div>
    </SheetContent>
</Sheet>
