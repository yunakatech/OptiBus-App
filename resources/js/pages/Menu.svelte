<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import Building2 from 'lucide-svelte/icons/building-2';
    import BusFront from 'lucide-svelte/icons/bus-front';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import CarFront from 'lucide-svelte/icons/car-front';
    import ChartColumn from 'lucide-svelte/icons/chart-column';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import History from 'lucide-svelte/icons/history';
    import IdCard from 'lucide-svelte/icons/id-card';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import MessageCircle from 'lucide-svelte/icons/message-circle';
    import MapPinned from 'lucide-svelte/icons/map-pinned';
    import Plus from 'lucide-svelte/icons/plus';
    import Package from 'lucide-svelte/icons/package';
    import Route from 'lucide-svelte/icons/route';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import Truck from 'lucide-svelte/icons/truck';
    import UserCog from 'lucide-svelte/icons/user-cog';
    import Users from 'lucide-svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { hasPermission } from '@/lib/access';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { shouldPrefetchNavigationHref } from '@/lib/navigation';
    import { mobileHiddenMenuHrefs } from '@/lib/mobileNavigation';
    import { getSupportWhatsappHref } from '@/lib/support';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';

    const url = currentUrlState();
    const dashboardHref = toUrl(dashboard());
    const supportHref = getSupportWhatsappHref();
    const canPrefetchDashboard = shouldPrefetchNavigationHref(dashboardHref);
    const permissions = $derived(page.props.auth?.permissions ?? []);
    const isSuperAdmin = $derived(
        Boolean(page.props.auth?.user?.is_super_admin),
    );
    const activeTenant = $derived(page.props.auth?.active_tenant ?? null);
    const showTenantScopedSections = $derived(
        !isSuperAdmin || Boolean(activeTenant),
    );
    const isPlatformContext = $derived(isSuperAdmin && !activeTenant);
    const billingLocked = $derived(
        Boolean(page.props.auth?.billing_access?.locked),
    );

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
                    href: '/settings/customers',
                    icon: Users,
                    permission: 'customer.view',
                },
                {
                    title: 'Bagasi',
                    href: '/settings/customer-bagasi',
                    icon: Briefcase,
                    permission: 'customer.view',
                },
                {
                    title: 'Carter',
                    href: '/settings/customer-charter',
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
                    href: '/settings/jadwal',
                    icon: CalendarDays,
                    permission: 'master.view',
                },
                {
                    title: 'Rute Induk',
                    href: '/settings/rute-induk',
                    icon: Route,
                    permission: 'master.view',
                },
                {
                    title: 'Pool',
                    href: '/settings/pool',
                    icon: Building2,
                    permission: 'pool.manage',
                },
                {
                    title: 'Master Carter',
                    href: '/settings/rute-carter',
                    icon: MapPinned,
                    permission: 'master.view',
                },
                {
                    title: 'Tarif Bagasi',
                    href: '/settings/tarif-bagasi',
                    icon: Package,
                    permission: 'master.view',
                },
                {
                    title: 'Kategori Armada',
                    href: '/settings/kategori-armada',
                    icon: Truck,
                    permission: 'master.view',
                },
                {
                    title: 'Armada',
                    href: '/settings/armada',
                    icon: CarFront,
                    permission: 'armada.view',
                },
                {
                    title: 'Driver',
                    href: '/settings/driver',
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
                    href: '/settings/users',
                    icon: UserCog,
                    permission: 'user.manage',
                },
                {
                    title: 'Logs',
                    href: '/settings/logs',
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
                    href: '/platform/saas',
                    icon: Building2,
                    permission: 'platform.manage',
                    superAdminOnly: true,
                },
                {
                    title: 'Role & Hak Akses',
                    href: '/settings/roles',
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

    let desktopMenu = $state(false);

    onMount(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const mediaQuery = window.matchMedia('(min-width: 768px)');
        const syncDesktopMenu = () => {
            desktopMenu = mediaQuery.matches;
        };

        syncDesktopMenu();
        mediaQuery.addEventListener('change', syncDesktopMenu);

        return () => {
            mediaQuery.removeEventListener('change', syncDesktopMenu);
        };
    });

    const hiddenMenuHrefs = $derived(
        desktopMenu ? new Set<string>() : mobileHiddenMenuHrefs(billingLocked),
    );
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
                                  href === '/platform/saas'
                              );
                          }),
                      },
            )
            .filter(
                (section) =>
                    showTenantScopedSections ||
                    (!isPlatformContext && section.label === 'Operasional') ||
                    section.label === 'Sistem',
            )
            .filter((section) => section.items.length > 0),
    );
</script>

<AppHead title="Menu" />

<div
    class="min-h-[calc(100dvh-4rem)] px-4 py-5 pb-[calc(6rem+env(safe-area-inset-bottom))] sm:px-6 lg:px-8"
>
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-5">
        <section
            class="rounded-lg border border-border/70 bg-card p-4 shadow-sm sm:p-5"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold text-foreground">Menu</h1>
                    <p
                        class="hidden max-w-2xl text-sm text-muted-foreground sm:block"
                    >
                        Pilih tujuan yang kamu butuhkan. Susunan dibuat ringkas
                        supaya cepat dipindai di layar kecil.
                    </p>
                </div>

                <Button
                    asChild
                    variant="outline"
                    class="w-full rounded-full sm:w-auto"
                >
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={dashboardHref}
                            prefetch={canPrefetchDashboard || undefined}
                            cacheFor={canPrefetchDashboard ? 30000 : undefined}
                        >
                            <ChevronLeft class="size-4" />
                            <span>Kembali ke Dashboard</span>
                        </Link>
                    {/snippet}
                </Button>
            </div>
        </section>

        <div class="space-y-5">
            {#if visibleMenuSections.length === 0}
                <section
                    class="rounded-lg border border-dashed border-border/80 bg-card p-5 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex max-w-sm flex-col items-center gap-3"
                    >
                        <div
                            class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary"
                        >
                            <LayoutGrid class="size-5" />
                        </div>
                        <div class="space-y-1">
                            <h2 class="text-base font-semibold text-foreground">
                                Menu belum tersedia
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Belum ada akses menu yang aktif untuk akun ini.
                                Jika ini tidak sesuai, cek hak akses user.
                            </p>
                        </div>
                        <Button asChild class="w-full rounded-full">
                            {#snippet children(props)}
                                <Link
                                    {...props}
                                    href={billingLocked
                                        ? '/subscription'
                                        : dashboardHref}
                                >
                                    {billingLocked
                                        ? 'Buka Langganan'
                                        : 'Buka Dashboard'}
                                </Link>
                            {/snippet}
                        </Button>
                    </div>
                </section>
            {:else}
                {#each visibleMenuSections as section (section.label)}
                    <section
                        class="space-y-3 rounded-lg border border-border/70 bg-card p-3 shadow-sm sm:p-4"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h2
                                class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground"
                            >
                                {section.label}
                            </h2>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-2 min-[390px]:gap-3 sm:grid-cols-3 lg:grid-cols-4"
                        >
                            {#each section.items as item (item.href)}
                                <Link
                                    href={toUrl(item.href)}
                                    class="flex min-h-[5.5rem] flex-col items-center justify-center gap-1 rounded-lg border bg-background px-3 py-3 text-center text-xs font-semibold text-foreground transition-[background-color,border-color,color,transform,box-shadow] duration-200 hover:-translate-y-0.5 {url.isCurrentOrParentUrl(
                                        item.href,
                                        url.currentUrl,
                                    )
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'hover:bg-accent'}"
                                >
                                    <item.icon class="size-5 shrink-0" />
                                    <span class="line-clamp-2 leading-tight">
                                        {item.title}
                                    </span>
                                </Link>
                            {/each}
                        </div>
                    </section>
                {/each}

                <section
                    class="rounded-lg border border-[#b9d8d0] bg-[#effaf7] p-3 shadow-sm sm:p-4"
                >
                    <a
                        href={supportHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex min-h-[5.5rem] items-center justify-center gap-2 rounded-lg border border-[#b9d8d0] bg-white px-4 py-3 text-center text-sm font-semibold text-[#17655f] transition hover:-translate-y-0.5 hover:bg-[#f7fffd] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#168c7c] focus-visible:ring-offset-2"
                        aria-label="Hubungi bantuan OptiBus melalui WhatsApp"
                    >
                        <MessageCircle class="size-5 shrink-0" />
                        <span>Bantuan WhatsApp</span>
                    </a>
                </section>
            {/if}
        </div>
    </div>
</div>
