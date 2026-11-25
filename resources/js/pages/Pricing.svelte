<script module lang="ts">
    export const layout = {
        title: 'Paket & Harga',
        description:
            'Pilih paket OptiBus sesuai ukuran operasional travel Anda.',
    };
</script>

<script lang="ts">


    import { page } from '@inertiajs/svelte';
    import {
        ArrowRight,
        Building2,
        BusFront,
        CalendarRange,
        Check,
        HelpCircle,
        Route,
        ShieldCheck,
        UsersRound,
        X,
    } from 'lucide-svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavigationMetrics from '@/components/NavigationMetrics.svelte';
    import ViewportOnly from '@/components/ViewportOnly.svelte';

    type PlanFeature = {
        name: string;
        group: string;
        included: boolean;
        limit: number | null;
    };
    type Plan = {
        id: number;
        name: string;
        slug: string;
        description: string;
        price_monthly: number;
        price_yearly: number;
        max_armadas: number;
        max_routes: number;
        max_users: number;
        max_pools: number;
        max_drivers: number;
        features: PlanFeature[];
    };

    const fallbackPlans: Plan[] = [
        {
            id: 1,
            name: 'Starter',
            slug: 'starter',
            description:
                'Untuk pengemudi individu dan travel yang mulai digital.',
            price_monthly: 49000,
            price_yearly: 490000,
            max_armadas: 1,
            max_routes: 2,
            max_users: 1,
            max_pools: 1,
            max_drivers: 1,
            features: [
                {
                    name: 'Dasbor Pendapatan',
                    group: 'Dasbor',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Pemesanan Kursi Dasar',
                    group: 'Pemesanan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Pembayaran Terpadu',
                    group: 'Keuangan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Peta Kursi Visual',
                    group: 'Pemesanan',
                    included: false,
                    limit: 0,
                },
                {
                    name: 'Ekspor CSV',
                    group: 'Laporan',
                    included: false,
                    limit: 0,
                },
            ],
        },
        {
            id: 2,
            name: 'Pro',
            slug: 'pro',
            description:
                'Untuk travel kecil dengan jadwal aktif dan tim admin.',
            price_monthly: 99000,
            price_yearly: 990000,
            max_armadas: 3,
            max_routes: 0,
            max_users: 3,
            max_pools: 2,
            max_drivers: 3,
            features: [
                {
                    name: 'Dasbor Pendapatan',
                    group: 'Dasbor',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Pemesanan Kursi Dasar',
                    group: 'Pemesanan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Pembayaran Terpadu',
                    group: 'Keuangan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Peta Kursi Visual',
                    group: 'Pemesanan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Ekspor CSV',
                    group: 'Laporan',
                    included: true,
                    limit: null,
                },
            ],
        },
        {
            id: 3,
            name: 'Fleet',
            slug: 'fleet',
            description:
                'Untuk multi-pool, banyak armada, dan kontrol akses tim.',
            price_monthly: 199000,
            price_yearly: 1990000,
            max_armadas: 10,
            max_routes: 0,
            max_users: 10,
            max_pools: 5,
            max_drivers: 15,
            features: [
                {
                    name: 'Dasbor Pendapatan',
                    group: 'Dasbor',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Pemesanan Kursi Dasar',
                    group: 'Pemesanan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Pembayaran Terpadu',
                    group: 'Keuangan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Peta Kursi Visual',
                    group: 'Pemesanan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Ekspor CSV',
                    group: 'Laporan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Peran Khusus',
                    group: 'Akses',
                    included: true,
                    limit: null,
                },
            ],
        },
    ];

    const plans = $derived(
        ((page.props.plans ?? []) as Plan[]).length > 0
            ? ((page.props.plans ?? []) as Plan[])
            : fallbackPlans,
    );
    const allFeatureNames = $derived(
        [
            ...new Set(
                plans.flatMap(
                    (plan) =>
                        plan.features?.map((feature) => feature.name) ?? [],
                ),
            ),
        ].filter(Boolean),
    );
    const comparisonRows = $derived([
        {
            label: 'Armada',
            values: plans.map((plan) => capacityLabel(plan.max_armadas)),
        },
        {
            label: 'Rute',
            values: plans.map((plan) => capacityLabel(plan.max_routes)),
        },
        {
            label: 'Pengguna',
            values: plans.map((plan) => capacityLabel(plan.max_users)),
        },
        ...allFeatureNames.map((featureName) => ({
            label: featureName,
            values: plans.map((plan) =>
                plan.features?.find((item) => item.name === featureName)
                    ?.included
                    ? 'Ada'
                    : 'Tidak',
            ),
        })),
    ]);
    const isAuthenticated = $derived(Boolean(page.props.auth?.user));
    const appHref = '/dashboard';
    const subscriptionHref = '/subscription';

    const buyingGuides = [
        {
            title: 'Starter',
            body: 'Cocok untuk 1 armada, rute terbatas, dan satu admin utama.',
        },
        {
            title: 'Pro',
            body: 'Pilihan standar untuk travel kecil yang perlu peta kursi, ekspor, dan pemesanan online.',
        },
        {
            title: 'Fleet',
            body: 'Dipakai saat cabang, peran, dan armada sudah perlu kontrol terpisah.',
        },
    ];

    function formatRupiah(value: number): string {
        return `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
    }

    function limitLabel(value: number, suffix: string): string {
        return value > 0 ? `${value} ${suffix}` : `${suffix} tanpa batas`;
    }

    function capacityLabel(value: number): string {
        return value > 0 ? String(value) : 'Tanpa batas';
    }

    function yearlySaving(plan: Plan): number {
        return Math.max(0, plan.price_monthly * 12 - plan.price_yearly);
    }
</script>

<NavigationMetrics />

<svelte:head>
    <title>Paket & Harga — OptiBus</title>
    <meta
        name="description"
        content="Bandingkan kapasitas dan fitur paket OptiBus untuk operasional travel Anda."
    />
</svelte:head>

<div
    class:mobile-page={true}
    class="min-h-screen overflow-x-hidden bg-[#F7FAF9] text-[#163A39]"
>
    <nav
        class="sticky top-0 z-40 border-b border-[#DCE9E6] bg-[#F7FAF9]/95 py-2 backdrop-blur sm:py-3"
    >
        <div
            class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-4 sm:px-6 lg:px-10"
        >
            <a
                href="/"
                class="flex h-10 max-w-[44vw] items-center overflow-hidden px-1 transition-opacity hover:opacity-75 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79] sm:max-w-none"
                aria-label="OptiBus"
            >
                <AppLogo />
            </a>
            <ViewportOnly minWidth={768}
                ><div
                    class="hidden items-center gap-7 text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground md:flex"
                >
                    <a
                        href="/"
                        class="transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79]"
                        >Beranda</a
                    >
                    <a
                        href="#compare"
                        class="transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79]"
                        >Perbandingan</a
                    >
                    {#if isAuthenticated}
                        <a
                            href={appHref}
                            class="transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79]"
                            >Dasbor</a
                        >
                    {/if}
                </div></ViewportOnly
            >
            <div class="flex shrink-0 items-center gap-2">
                {#if !isAuthenticated}
                    <a
                        href="/login"
                        class="inline-flex h-10 items-center justify-center px-2 text-xs font-semibold text-muted-foreground transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79] sm:px-3"
                        >Masuk</a
                    >
                {/if}
                <a
                    href={isAuthenticated
                        ? appHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-10 items-center justify-center bg-[#087F79] px-4 text-xs font-semibold text-white transition-colors hover:bg-[#075B5B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                >
                    {isAuthenticated ? 'Dasbor' : 'Mulai'}
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="border-b border-[#DCE9E6]">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-14 md:grid-cols-[1fr_auto] md:items-end md:gap-12 lg:px-10 lg:py-16"
            >
                <div>
                    <p
                        class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#087F79]"
                    >
                        Paket & kapasitas
                    </p>
                    <h1
                        class="mt-3 max-w-3xl font-sans text-4xl font-medium leading-[1.02] tracking-[-0.04em] text-[#163A39] sm:text-5xl lg:text-6xl"
                    >
                        Pilih paket yang pas untuk operasional travel Anda.
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-[15px] leading-7 text-muted-foreground sm:text-base"
                    >
                        Mulai dari kebutuhan hari ini, lalu bandingkan kapasitas
                        armada, tim, dan fitur untuk setiap tahap perjalanan bisnis Anda.
                    </p>
                </div>
                <div
                    class="max-w-xs border-l-2 border-[#55E8B4] py-1 pl-4 text-sm leading-6 text-muted-foreground"
                >
                    <div
                        class="flex items-center gap-2 font-semibold text-[#163A39]"
                    >
                        <CalendarRange
                            class="h-4 w-4 text-[#087F79]"
                            aria-hidden="true"
                        />
                        Opsi tagihan tahunan
                    </div>
                    <p class="mt-2">
                        Total tahunan dan penghematan ditampilkan pada setiap
                        paket.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-10">
            <div
                class="grid gap-4 lg:grid-cols-3"
            >
                {#each plans as plan (plan.slug)}
                    <article
                        class={`relative flex flex-col rounded-2xl border p-6 sm:p-7 ${plan.slug === 'pro' ? 'border-[#087F79] bg-[#E7FAF3]' : 'border-[#DCE9E6] bg-white'}`}
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2
                                    class="font-sans text-2xl font-medium text-[#163A39]"
                                >
                                    {plan.name}
                                </h2>
                                <p
                                    class="mt-2 min-h-10 text-sm leading-6 text-muted-foreground"
                                >
                                    {plan.description}
                                </p>
                            </div>
                            {#if plan.slug === 'pro'}
                                <span
                                    class="border border-[#AFC9C3] px-2 py-1 text-xs md:text-[10px] font-bold uppercase tracking-[0.12em] text-[#087F79]"
                                    >Untuk tim</span
                                >
                            {/if}
                        </div>

                        <div class="mt-5 border-y border-[#DCE9E6] py-4">
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="font-sans text-3xl font-medium text-[#163A39] sm:text-4xl"
                                    >{formatRupiah(plan.price_monthly)}</span
                                >
                                <span
                                    class="text-xs uppercase tracking-[0.08em] text-muted-foreground"
                                    >/ bulan</span
                                >
                            </div>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {formatRupiah(plan.price_yearly)}/tahun
                                {#if yearlySaving(plan) > 0}
                                    <span class="font-semibold text-[#087F79]">
                                        - hemat {formatRupiah(
                                            yearlySaving(plan),
                                        )}</span
                                    >
                                {/if}
                            </p>
                        </div>

                        <div
                            class="mt-5 grid grid-cols-2 gap-0 border-y border-[#DCE9E6] text-xs"
                        >
                            <div
                                class="border-b border-r border-[#DCE9E6] py-3 pr-2"
                            >
                                <BusFront
                                    class="mb-2 h-4 w-4 text-[#087F79]"
                                />{limitLabel(plan.max_armadas, 'armada')}
                            </div>
                            <div class="border-b border-[#DCE9E6] py-3 pl-3">
                                <Route
                                    class="mb-2 h-4 w-4 text-[#087F79]"
                                />{limitLabel(plan.max_routes, 'rute')}
                            </div>
                            <div class="border-r border-[#DCE9E6] py-3 pr-2">
                                <UsersRound
                                    class="mb-2 h-4 w-4 text-[#087F79]"
                                />{limitLabel(plan.max_users, 'pengguna')}
                            </div>
                            <div class="py-3 pl-3">
                                <Building2
                                    class="mb-2 h-4 w-4 text-[#087F79]"
                                />{limitLabel(plan.max_pools, 'pool')}
                            </div>
                        </div>

                        <div class="mt-5 grow space-y-3 py-1">
                            {#each (plan.features ?? [])
                                .filter((feature) => feature.included)
                                .slice(0, 5) as feature (feature.name)}
                                <div
                                    class="flex items-center gap-2 text-sm text-[#163A39]"
                                >
                                    <Check class="h-4 w-4 text-[#087F79]" />
                                    <span>{feature.name}</span>
                                </div>
                            {/each}
                            {#if (plan.features ?? []).filter((feature) => feature.included).length === 0}
                                <div
                                    class="flex items-center gap-2 text-sm text-[#163A39]"
                                >
                                    <Check class="h-4 w-4 text-[#087F79]" />
                                    <span
                                        >Dasbor, pemesanan, pembayaran, dan
                                        laporan dasar</span
                                    >
                                </div>
                            {/if}
                        </div>

                        <a
                            href={isAuthenticated
                                ? subscriptionHref
                                : `/register?plan=${plan.slug}&intent=paid`}
                            class={`mt-6 inline-flex h-11 w-full items-center justify-center text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79] ${plan.slug === 'pro' ? 'bg-[#087F79] text-white hover:bg-[#075B5B]' : 'border border-[#AFC9C3] text-[#163A39] hover:bg-[#E7FAF3]'}`}
                        >
                            {isAuthenticated
                                ? 'Kelola paket'
                                : `Pilih ${plan.name}`}
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </a>
                    </article>
                {/each}
            </div>
        </section>

        <section id="compare" class="border-y border-[#DCE9E6] bg-[#E7FAF3]">
            <div
                class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20"
            >
                <div
                    class="mb-5 flex flex-col justify-between gap-4 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#087F79]"
                        >
                            Perbandingan fitur
                        </p>
                        <h2
                            class="mt-3 font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#163A39] sm:text-4xl"
                        >
                            Bandingkan kapasitas dan fitur utama.
                        </h2>
                    </div>
                    <div
                        class="inline-flex min-h-10 w-fit items-center gap-2 border-l-2 border-[#55E8B4] pl-3 text-xs text-muted-foreground"
                    >
                        <ShieldCheck
                            class="h-4 w-4 text-[#087F79]"
                            aria-hidden="true"
                        />
                        Data tenant dan pool terpisah
                    </div>
                </div>

                <ViewportOnly minWidth={0} maxWidth={767}
                    ><div
                        class="divide-y divide-[#DCE9E6] border-y border-[#DCE9E6] md:hidden"
                    >
                        {#each comparisonRows as row (row.label)}
                            <div class="py-4">
                                <p class="text-sm font-semibold text-[#163A39]">
                                    {row.label}
                                </p>
                                <div
                                    class="mt-3 grid gap-x-3 gap-y-2 sm:grid-cols-2"
                                >
                                    {#each plans as plan, index (plan.slug)}
                                        <div
                                            class="flex items-center justify-between gap-3 border-b border-[#DCE9E6] py-2 text-sm"
                                        >
                                            <span class="font-medium"
                                                >{plan.name}</span
                                            >
                                            <span
                                                class={row.values[index] ===
                                                'Tidak'
                                                    ? 'text-muted-foreground'
                                                    : 'font-semibold text-[#087F79]'}
                                            >
                                                {row.values[index]}
                                            </span>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/each}
                    </div></ViewportOnly
                >

                <ViewportOnly minWidth={768}
                    ><div
                        class="hidden overflow-hidden border-y border-[#DCE9E6] md:block"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-xs">
                                <thead>
                                    <tr
                                        class="border-b border-[#DCE9E6] bg-[#F7FAF9]"
                                    >
                                        <th
                                            class="px-3 py-2.5 text-left font-semibold"
                                            >Item</th
                                        >
                                        {#each plans as plan (plan.slug)}
                                            <th
                                                class="px-3 py-2.5 text-center font-semibold"
                                                >{plan.name}</th
                                            >
                                        {/each}
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-[#DCE9E6]">
                                        <td class="px-3 py-2.5 font-medium"
                                            >Armada</td
                                        >
                                        {#each plans as plan (plan.slug)}<td
                                                class="px-3 py-2.5 text-center"
                                                >{capacityLabel(
                                                    plan.max_armadas,
                                                )}</td
                                            >{/each}
                                    </tr>
                                    <tr class="border-b border-[#DCE9E6]">
                                        <td class="px-3 py-2.5 font-medium"
                                            >Rute</td
                                        >
                                        {#each plans as plan (plan.slug)}<td
                                                class="px-3 py-2.5 text-center"
                                                >{capacityLabel(
                                                    plan.max_routes,
                                                )}</td
                                            >{/each}
                                    </tr>
                                    <tr class="border-b border-[#DCE9E6]">
                                        <td class="px-3 py-2.5 font-medium"
                                            >Pengguna</td
                                        >
                                        {#each plans as plan (plan.slug)}<td
                                                class="px-3 py-2.5 text-center"
                                                >{capacityLabel(
                                                    plan.max_users,
                                                )}</td
                                            >{/each}
                                    </tr>
                                    {#each allFeatureNames as featureName (featureName)}
                                        <tr
                                            class="border-b border-[#DCE9E6] last:border-0"
                                        >
                                            <td class="px-3 py-2.5 font-medium"
                                                >{featureName}</td
                                            >
                                            {#each plans as plan (plan.slug)}
                                                {@const feature =
                                                    plan.features?.find(
                                                        (item) =>
                                                            item.name ===
                                                            featureName,
                                                    )}
                                                <td
                                                    class="px-3 py-2.5 text-center"
                                                >
                                                    {#if feature?.included}
                                                        <Check
                                                            class="mx-auto h-4 w-4 text-[#087F79]"
                                                            aria-label="Termasuk"
                                                        />
                                                    {:else}
                                                        <X
                                                            class="mx-auto h-4 w-4 text-muted-foreground"
                                                            aria-label="Tidak termasuk"
                                                        />
                                                    {/if}
                                                </td>
                                            {/each}
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </div></ViewportOnly
                >
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20"
        >
            <div class="grid gap-8 md:grid-cols-[0.72fr_1.28fr] md:gap-16">
                <div class="md:pt-2">
                    <div
                        class="flex items-center gap-2 text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#087F79]"
                    >
                        <HelpCircle class="h-4 w-4" aria-hidden="true" />
                        Panduan paket
                    </div>
                    <h2
                        class="mt-3 max-w-sm font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#163A39] sm:text-4xl"
                    >
                        Sesuaikan dengan ritme kerja tim.
                    </h2>
                </div>
                <ol class="divide-y divide-[#DCE9E6] border-y border-[#DCE9E6]">
                    {#each buyingGuides as guide, index (guide.title)}
                        <li
                            class="grid grid-cols-[2.5rem_1fr] gap-3 py-4 sm:grid-cols-[3rem_1fr] sm:py-5"
                        >
                            <span class="font-sans text-lg text-[#087F79]"
                                >0{index + 1}</span
                            >
                            <div>
                                <h3 class="font-semibold text-[#163A39]">
                                    {guide.title}
                                </h3>
                                <p
                                    class="mt-1 text-sm leading-6 text-muted-foreground"
                                >
                                    {guide.body}
                                </p>
                            </div>
                        </li>
                    {/each}
                </ol>
            </div>
        </section>

        <section class="border-t border-[#163A39] bg-[#163A39] text-[#F7FAF9]">
            <div
                class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 sm:py-14 md:grid-cols-[1fr_auto] md:items-center lg:px-10 lg:py-16"
            >
                <div>
                    <p
                        class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#55E8B4]"
                    >
                        Coba alurnya
                    </p>
                    <h2
                        class="mt-3 max-w-3xl font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#EAF6F2] sm:text-4xl"
                    >
                        Mulai dengan paket yang sesuai kebutuhan travel Anda.
                    </h2>
                </div>
                <a
                    href={isAuthenticated
                        ? subscriptionHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-12 items-center justify-center bg-[#F7FAF9] px-5 text-sm font-semibold text-[#163A39] transition-colors hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#55E8B4]"
                >
                    {isAuthenticated ? 'Kelola langganan' : 'Daftar sekarang'}
                    <ArrowRight class="ml-2 h-4 w-4" />
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#DCE9E6] bg-[#F7FAF9]">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-6 text-sm text-muted-foreground sm:px-6 md:flex-row md:items-center md:justify-between lg:px-10"
        >
            <span class="font-sans text-base text-[#163A39]">
                OptiBus
                <span class="font-sans text-xs text-muted-foreground">
                    / paket operasional perjalanan
                </span>
            </span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a
                    href="/"
                    class="inline-flex min-h-10 items-center hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                    >Beranda</a
                >
                {#if isAuthenticated}
                    <a
                        href={appHref}
                        class="inline-flex min-h-10 items-center hover:text-[#087F79]"
                        >Dasbor</a
                    >
                    <a
                        href={subscriptionHref}
                        class="inline-flex min-h-10 items-center hover:text-[#087F79]"
                        >Langganan</a
                    >
                {:else}
                    <a
                        href="/login"
                        class="inline-flex min-h-10 items-center hover:text-[#087F79]"
                        >Masuk</a
                    >
                    <a
                        href="/register?plan=starter&intent=trial"
                        class="inline-flex min-h-10 items-center hover:text-[#087F79]"
                        >Daftar</a
                    >
                {/if}
            </div>
        </div>
    </footer>
</div>
