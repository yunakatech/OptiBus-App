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
        if (value >= 1_000_000)
            return `Rp ${(value / 1_000_000).toFixed(1)} jt`;
        return `Rp ${(value / 1_000).toFixed(0)} rb`;
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

<svelte:head>
    <title>Paket & Harga — OptiBus</title>
    <meta
        name="description"
        content="Bandingkan kapasitas dan fitur paket OptiBus untuk operasional travel Anda."
    />
</svelte:head>

<div class="min-h-screen overflow-x-hidden bg-[#f1eee5] text-[#1a2825]">
    <nav
        class="sticky top-0 z-40 border-b border-[#d6d0c2] bg-[#f1eee5]/95 py-2 backdrop-blur sm:py-3"
    >
        <div
            class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-4 sm:px-6 lg:px-10"
        >
            <a
                href="/"
                class="flex h-10 max-w-[44vw] items-center overflow-hidden px-1 transition-opacity hover:opacity-75 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532] sm:max-w-none"
                aria-label="OptiBus"
            >
                <AppLogo />
            </a>
            <div
                class="hidden items-center gap-7 text-xs font-semibold uppercase tracking-[0.12em] text-[#52615b] md:flex"
            >
                <a
                    href="/"
                    class="transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532]"
                    >Beranda</a
                >
                <a
                    href="#compare"
                    class="transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532]"
                    >Perbandingan</a
                >
                {#if isAuthenticated}
                    <a
                        href={appHref}
                        class="transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532]"
                        >Dasbor</a
                    >
                {/if}
            </div>
            <div class="flex shrink-0 items-center gap-2">
                {#if !isAuthenticated}
                    <a
                        href="/login"
                        class="inline-flex h-10 items-center justify-center px-2 text-xs font-semibold text-[#42514c] transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b96532] sm:px-3"
                        >Masuk</a
                    >
                {/if}
                <a
                    href={isAuthenticated
                        ? appHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-10 items-center justify-center bg-[#174b43] px-4 text-xs font-semibold text-white transition-colors hover:bg-[#103a34] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b96532]"
                >
                    {isAuthenticated ? 'Dasbor' : 'Mulai'}
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="border-b border-[#c9c2b3]">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-14 md:grid-cols-[1fr_auto] md:items-end md:gap-12 lg:px-10 lg:py-16"
            >
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#a94e28]">
                        Paket & kapasitas
                    </p>
                    <h1
                        class="mt-3 max-w-3xl font-serif text-4xl font-medium leading-[1.02] tracking-[-0.04em] text-[#173a34] sm:text-5xl lg:text-6xl"
                    >
                        Ruang untuk perjalanan yang terus bertambah.
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-[15px] leading-7 text-[#53615b] sm:text-base"
                    >
                        Bandingkan kapasitas armada, rute, tim, dan fitur yang
                        tersedia di setiap paket. Pilih paket yang sesuai dengan
                        cara travel Anda beroperasi sekarang.
                    </p>
                </div>
                <div
                    class="max-w-xs border-l-2 border-[#bc6738] py-1 pl-4 text-sm leading-6 text-[#53615b]"
                >
                    <div
                        class="flex items-center gap-2 font-semibold text-[#213a34]"
                    >
                        <CalendarRange class="h-4 w-4 text-[#a94e28]" aria-hidden="true" />
                        Opsi tagihan tahunan
                    </div>
                    <p class="mt-2">
                        Total tahunan dan penghematan ditampilkan pada setiap paket.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-10">
            <div class="grid border-y border-[#c9c2b3] lg:grid-cols-3 lg:divide-x lg:divide-[#c9c2b3]">
                {#each plans as plan}
                    <article
                        class={`relative flex flex-col border-b border-[#c9c2b3] px-1 py-6 sm:px-5 sm:py-7 lg:border-b-0 ${plan.slug === 'pro' ? 'bg-[#e8e2d7] lg:-my-px lg:border-y-2 lg:border-y-[#174b43]' : ''}`}
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="font-serif text-2xl font-medium text-[#173a34]">
                                    {plan.name}
                                </h2>
                                <p
                                    class="mt-2 min-h-10 text-sm leading-6 text-[#53615b]"
                                >
                                    {plan.description}
                                </p>
                            </div>
                            {#if plan.slug === 'pro'}
                                <span
                                    class="border border-[#9eb5a8] px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-[#174b43]"
                                    >Populer</span
                                >
                            {/if}
                        </div>

                        <div class="mt-5 border-y border-[#c9c2b3] py-4">
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="font-serif text-3xl font-medium text-[#173a34] sm:text-4xl"
                                    >{formatRupiah(plan.price_monthly)}</span
                                >
                                <span class="text-xs uppercase tracking-[0.08em] text-[#53615b]"
                                    >/ bulan</span
                                >
                            </div>
                            <p class="mt-2 text-sm text-[#53615b]">
                                {formatRupiah(plan.price_yearly)}/tahun
                                {#if yearlySaving(plan) > 0}
                                    <span class="font-semibold text-[#174b43]">
                                        - hemat {formatRupiah(
                                            yearlySaving(plan),
                                        )}</span
                                    >
                                {/if}
                            </p>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-0 border-y border-[#c9c2b3] text-xs">
                            <div
                                class="border-b border-r border-[#c9c2b3] py-3 pr-2"
                            >
                                <BusFront
                                    class="mb-2 h-4 w-4 text-[#a94e28]"
                                />{limitLabel(plan.max_armadas, 'armada')}
                            </div>
                            <div
                                class="border-b border-[#c9c2b3] py-3 pl-3"
                            >
                                <Route
                                    class="mb-2 h-4 w-4 text-[#a94e28]"
                                />{limitLabel(plan.max_routes, 'rute')}
                            </div>
                            <div
                                class="border-r border-[#c9c2b3] py-3 pr-2"
                            >
                                <UsersRound
                                    class="mb-2 h-4 w-4 text-[#a94e28]"
                                />{limitLabel(plan.max_users, 'pengguna')}
                            </div>
                            <div
                                class="py-3 pl-3"
                            >
                                <Building2
                                    class="mb-2 h-4 w-4 text-[#a94e28]"
                                />{limitLabel(plan.max_pools, 'pool')}
                            </div>
                        </div>

                        <div class="mt-5 grow space-y-3 py-1">
                            {#each (plan.features ?? [])
                                .filter((feature) => feature.included)
                                .slice(0, 5) as feature}
                                <div
                                    class="flex items-center gap-2 text-sm text-[#33433d]"
                                >
                                    <Check class="h-4 w-4 text-[#174b43]" />
                                    <span>{feature.name}</span>
                                </div>
                            {/each}
                            {#if (plan.features ?? []).filter((feature) => feature.included).length === 0}
                                <div
                                    class="flex items-center gap-2 text-sm text-[#33433d]"
                                >
                                    <Check class="h-4 w-4 text-[#174b43]" />
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
                            class={`mt-6 inline-flex h-11 w-full items-center justify-center text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28] ${plan.slug === 'pro' ? 'bg-[#174b43] text-white hover:bg-[#103a34]' : 'border border-[#aaa494] text-[#243a34] hover:bg-[#e6e0d4]'}`}
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

        <section id="compare" class="border-y border-[#c9c2b3] bg-[#e8e2d7]">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20">
                <div
                    class="mb-5 flex flex-col justify-between gap-4 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#a94e28]"
                        >
                            Perbandingan fitur
                        </p>
                        <h2
                            class="mt-3 font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#173a34] sm:text-4xl"
                        >
                            Bandingkan kapasitas dan fitur utama.
                        </h2>
                    </div>
                    <div
                        class="inline-flex min-h-10 w-fit items-center gap-2 border-l-2 border-[#bc6738] pl-3 text-xs text-[#53615b]"
                    >
                        <ShieldCheck class="h-4 w-4 text-[#174b43]" aria-hidden="true" />
                        Data tenant dan pool terpisah
                    </div>
                </div>

                <div class="divide-y divide-[#c9c2b3] border-y border-[#c9c2b3] md:hidden">
                    {#each comparisonRows as row}
                        <div class="py-4">
                            <p class="text-sm font-semibold text-[#213a34]">
                                {row.label}
                            </p>
                            <div class="mt-3 grid gap-x-3 gap-y-2 sm:grid-cols-2">
                                {#each plans as plan, index}
                                    <div
                                        class="flex items-center justify-between gap-3 border-b border-[#d2cabb] py-2 text-sm"
                                    >
                                        <span class="font-medium"
                                            >{plan.name}</span
                                        >
                                        <span
                                            class={row.values[index] === 'Tidak'
                                                ? 'text-[#8c938c]'
                                                : 'font-semibold text-[#174b43]'}
                                        >
                                            {row.values[index]}
                                        </span>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/each}
                </div>

                <div
                    class="hidden overflow-hidden border-y border-[#c9c2b3] md:block"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-xs">
                            <thead>
                                <tr
                                    class="border-b border-[#c9c2b3] bg-[#f1eee5]"
                                >
                                    <th
                                        class="px-3 py-2.5 text-left font-semibold"
                                        >Item</th
                                    >
                                    {#each plans as plan}
                                        <th
                                            class="px-3 py-2.5 text-center font-semibold"
                                            >{plan.name}</th
                                        >
                                    {/each}
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#d2cabb]">
                                    <td class="px-3 py-2.5 font-medium">Armada</td
                                    >
                                    {#each plans as plan}<td
                                            class="px-3 py-2.5 text-center"
                                            >{capacityLabel(
                                                plan.max_armadas,
                                            )}</td
                                        >{/each}
                                </tr>
                                <tr class="border-b border-[#d2cabb]">
                                    <td class="px-3 py-2.5 font-medium">Rute</td>
                                    {#each plans as plan}<td
                                            class="px-3 py-2.5 text-center"
                                            >{capacityLabel(
                                                plan.max_routes,
                                            )}</td
                                        >{/each}
                                </tr>
                                <tr class="border-b border-[#d2cabb]">
                                    <td class="px-3 py-2.5 font-medium"
                                        >Pengguna</td
                                    >
                                    {#each plans as plan}<td
                                            class="px-3 py-2.5 text-center"
                                            >{capacityLabel(plan.max_users)}</td
                                        >{/each}
                                </tr>
                                {#each allFeatureNames as featureName}
                                    <tr
                                        class="border-b border-[#d2cabb] last:border-0"
                                    >
                                        <td class="px-3 py-2.5 font-medium"
                                            >{featureName}</td
                                        >
                                        {#each plans as plan}
                                            {@const feature =
                                                plan.features?.find(
                                                    (item) =>
                                                        item.name ===
                                                        featureName,
                                                )}
                                            <td class="px-3 py-2.5 text-center">
                                                {#if feature?.included}
                                                    <Check
                                                        class="mx-auto h-4 w-4 text-[#174b43]"
                                                        aria-label="Termasuk"
                                                    />
                                                {:else}
                                                    <X
                                                        class="mx-auto h-4 w-4 text-[#8c938c]"
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
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20">
            <div class="grid gap-8 md:grid-cols-[0.72fr_1.28fr] md:gap-16">
                <div class="md:pt-2">
                    <div
                        class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-[#a94e28]"
                    >
                        <HelpCircle class="h-4 w-4" aria-hidden="true" />
                        Panduan paket
                    </div>
                    <h2
                        class="mt-3 max-w-sm font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#173a34] sm:text-4xl"
                    >
                        Sesuaikan dengan ritme kerja tim.
                    </h2>
                </div>
                <ol class="divide-y divide-[#c9c2b3] border-y border-[#c9c2b3]">
                    {#each buyingGuides as guide, index}
                        <li class="grid grid-cols-[2.5rem_1fr] gap-3 py-4 sm:grid-cols-[3rem_1fr] sm:py-5">
                            <span class="font-serif text-lg text-[#a94e28]">0{index + 1}</span>
                            <div>
                                <h3 class="font-semibold text-[#213a34]">{guide.title}</h3>
                                <p class="mt-1 text-sm leading-6 text-[#53615b]">
                                {guide.body}
                                </p>
                            </div>
                        </li>
                    {/each}
                </ol>
            </div>
        </section>

        <section class="border-t border-[#173a34] bg-[#173a34] text-[#f1eee5]">
            <div
                class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 sm:py-14 md:grid-cols-[1fr_auto] md:items-center lg:px-10 lg:py-16"
            >
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#d9a27d]">
                        Coba alurnya
                    </p>
                    <h2
                        class="mt-3 max-w-3xl font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#f8f4eb] sm:text-4xl"
                    >
                        Mulai dengan paket yang sesuai kebutuhan travel Anda.
                    </h2>
                </div>
                <a
                    href={isAuthenticated
                        ? subscriptionHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-12 items-center justify-center bg-[#f1eee5] px-5 text-sm font-semibold text-[#173a34] transition-colors hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d9a27d]"
                >
                    {isAuthenticated ? 'Kelola langganan' : 'Daftar sekarang'}
                    <ArrowRight class="ml-2 h-4 w-4" />
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#c9c2b3] bg-[#f1eee5]">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-6 text-sm text-[#53615b] sm:px-6 md:flex-row md:items-center md:justify-between lg:px-10"
        >
            <span class="font-serif text-base text-[#173a34]">
                OptiBus
                <span class="font-sans text-xs text-[#68736c]">
                    / paket operasional perjalanan
                </span>
            </span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="/" class="inline-flex min-h-10 items-center hover:text-[#a94e28] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]">Beranda</a>
                {#if isAuthenticated}
                    <a href={appHref} class="inline-flex min-h-10 items-center hover:text-[#a94e28]">Dasbor</a>
                    <a href={subscriptionHref} class="inline-flex min-h-10 items-center hover:text-[#a94e28]"
                        >Langganan</a
                    >
                {:else}
                    <a href="/login" class="inline-flex min-h-10 items-center hover:text-[#a94e28]">Masuk</a>
                    <a
                        href="/register?plan=starter&intent=trial"
                        class="inline-flex min-h-10 items-center hover:text-[#a94e28]">Daftar</a
                    >
                {/if}
            </div>
        </div>
    </footer>
</div>
