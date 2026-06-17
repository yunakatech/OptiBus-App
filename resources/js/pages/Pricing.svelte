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
            description: 'Untuk driver individu dan travel yang mulai digital.',
            price_monthly: 49000,
            price_yearly: 490000,
            max_armadas: 1,
            max_routes: 2,
            max_users: 1,
            max_pools: 1,
            max_drivers: 1,
            features: [
                {
                    name: 'Dashboard Revenue',
                    group: 'Dashboard',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Booking Seat Basic',
                    group: 'Booking',
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
                    name: 'Seat Map Visual',
                    group: 'Booking',
                    included: false,
                    limit: 0,
                },
                {
                    name: 'Export CSV',
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
                    name: 'Dashboard Revenue',
                    group: 'Dashboard',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Booking Seat Basic',
                    group: 'Booking',
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
                    name: 'Seat Map Visual',
                    group: 'Booking',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Export CSV',
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
                    name: 'Dashboard Revenue',
                    group: 'Dashboard',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Booking Seat Basic',
                    group: 'Booking',
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
                    name: 'Seat Map Visual',
                    group: 'Booking',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Export CSV',
                    group: 'Laporan',
                    included: true,
                    limit: null,
                },
                {
                    name: 'Custom Role',
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
            body: 'Pilihan standar untuk travel kecil yang perlu seat map, export, dan booking online.',
        },
        {
            title: 'Fleet',
            body: 'Dipakai saat cabang, role, dan armada sudah perlu kontrol terpisah.',
        },
    ];

    function formatRupiah(value: number): string {
        if (value >= 1_000_000)
            return `Rp ${(value / 1_000_000).toFixed(1)} jt`;
        return `Rp ${(value / 1_000).toFixed(0)} rb`;
    }

    function limitLabel(value: number, suffix: string): string {
        return value > 0 ? `${value} ${suffix}` : `Unlimited ${suffix}`;
    }

    function capacityLabel(value: number): string {
        return value > 0 ? String(value) : 'Unlimited';
    }

    function yearlySaving(plan: Plan): number {
        return Math.max(0, plan.price_monthly * 12 - plan.price_yearly);
    }
</script>

<svelte:head>
    <title>Paket & Harga - OptiBus</title>
    <meta
        name="description"
        content="Bandingkan paket Starter, Pro, dan Fleet untuk sistem operasional travel OptiBus."
    />
</svelte:head>

<div class="min-h-screen bg-[#f7f8f4] text-[#17201f]">
    <nav class="sticky top-0 z-40 bg-[#f7f8f4]/78 py-3 backdrop-blur-xl">
        <div
            class="mx-auto flex max-w-7xl items-center justify-between px-3 sm:px-6 lg:px-8"
        >
            <a
                href="/"
                class="flex h-12 items-center rounded-full border border-[#d9ded4]/80 bg-white/80 px-3 shadow-[0_10px_28px_-24px_rgba(23,32,31,0.9)] transition hover:border-[#b9c5bd] hover:bg-white"
                aria-label="OptiBus"
            >
                <AppLogo />
            </a>
            <div
                class="hidden items-center gap-1 rounded-full border border-[#d9ded4]/90 bg-white/72 p-1 text-sm font-semibold text-[#4b5a56] shadow-[0_14px_38px_-30px_rgba(23,32,31,0.88)] ring-1 ring-white/70 md:flex"
            >
                <a
                    href="/"
                    class="rounded-full px-4 py-2 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                    >Landing</a
                >
                <a
                    href="#compare"
                    class="rounded-full px-4 py-2 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                    >Perbandingan</a
                >
                {#if isAuthenticated}
                    <a
                        href={appHref}
                        class="rounded-full px-4 py-2 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                        >Dashboard</a
                    >
                {:else}
                    <a
                        href="/login"
                        class="rounded-full px-4 py-2 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                        >Login</a
                    >
                {/if}
            </div>
            <a
                href={isAuthenticated
                    ? appHref
                    : '/register?plan=starter&intent=trial'}
                class="inline-flex h-10 items-center justify-center rounded-full bg-[#103d3a] px-4 text-sm font-semibold text-white shadow-[0_12px_26px_-18px_rgba(16,61,58,0.9)] transition hover:-translate-y-0.5 hover:bg-[#0b2f2c] sm:px-5"
            >
                {isAuthenticated ? 'Dashboard' : 'Mulai trial'}
            </a>
        </div>
    </nav>

    <main>
        <section class="border-b border-[#d9ded4]">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-[1fr_auto] md:items-end lg:px-8"
            >
                <div>
                    <p class="text-sm font-semibold uppercase text-[#b96c20]">
                        Pricing
                    </p>
                    <h1
                        class="mt-3 max-w-3xl text-4xl font-semibold leading-tight tracking-normal sm:text-5xl"
                    >
                        Pilih kapasitas sesuai ukuran travel.
                    </h1>
                    <p
                        class="mt-4 max-w-2xl text-base leading-7 text-[#53615d]"
                    >
                        Pilih paket untuk lanjut pembayaran, atau mulai trial
                        Starter dari tombol trial.
                    </p>
                </div>
                <div
                    class="rounded-lg border border-[#d9ded4] bg-white p-4 text-sm text-[#53615d]"
                >
                    <div
                        class="flex items-center gap-2 font-semibold text-[#17201f]"
                    >
                        <CalendarRange class="h-4 w-4 text-[#0d7066]" />
                        Tagihan tahunan lebih hemat
                    </div>
                    <p class="mt-2">
                        Harga di bawah belum termasuk biaya add-on khusus.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-5 lg:grid-cols-3">
                {#each plans as plan}
                    <article
                        class={`flex flex-col rounded-lg border bg-white p-5 ${plan.slug === 'pro' ? 'border-[#0d7066] shadow-xl shadow-[#0d7066]/10' : 'border-[#d9ded4]'}`}
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    {plan.name}
                                </h2>
                                <p
                                    class="mt-2 min-h-14 text-sm leading-6 text-[#53615d]"
                                >
                                    {plan.description}
                                </p>
                            </div>
                            {#if plan.slug === 'pro'}
                                <span
                                    class="rounded-full bg-[#eaf6f2] px-2.5 py-1 text-xs font-semibold text-[#0d7066]"
                                    >Populer</span
                                >
                            {/if}
                        </div>

                        <div class="mt-6 border-y border-[#e1e6df] py-5">
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-semibold"
                                    >{formatRupiah(plan.price_monthly)}</span
                                >
                                <span class="text-sm text-[#53615d]"
                                    >/bulan</span
                                >
                            </div>
                            <p class="mt-2 text-sm text-[#53615d]">
                                {formatRupiah(plan.price_yearly)}/tahun
                                {#if yearlySaving(plan) > 0}
                                    <span class="font-semibold text-[#0d7066]">
                                        - hemat {formatRupiah(
                                            yearlySaving(plan),
                                        )}</span
                                    >
                                {/if}
                            </p>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-md bg-[#f1f4ee] p-3">
                                <BusFront
                                    class="mb-2 h-4 w-4 text-[#0d7066]"
                                />{limitLabel(plan.max_armadas, 'armada')}
                            </div>
                            <div class="rounded-md bg-[#f1f4ee] p-3">
                                <Route
                                    class="mb-2 h-4 w-4 text-[#0d7066]"
                                />{limitLabel(plan.max_routes, 'rute')}
                            </div>
                            <div class="rounded-md bg-[#f1f4ee] p-3">
                                <UsersRound
                                    class="mb-2 h-4 w-4 text-[#0d7066]"
                                />{limitLabel(plan.max_users, 'user')}
                            </div>
                            <div class="rounded-md bg-[#f1f4ee] p-3">
                                <Building2
                                    class="mb-2 h-4 w-4 text-[#0d7066]"
                                />{limitLabel(plan.max_pools, 'pool')}
                            </div>
                        </div>

                        <div class="mt-5 grow space-y-2">
                            {#each (plan.features ?? [])
                                .filter((feature) => feature.included)
                                .slice(0, 5) as feature}
                                <div
                                    class="flex items-center gap-2 text-sm text-[#33403d]"
                                >
                                    <Check class="h-4 w-4 text-[#0d7066]" />
                                    <span>{feature.name}</span>
                                </div>
                            {/each}
                            {#if (plan.features ?? []).filter((feature) => feature.included).length === 0}
                                <div
                                    class="flex items-center gap-2 text-sm text-[#33403d]"
                                >
                                    <Check class="h-4 w-4 text-[#0d7066]" />
                                    <span
                                        >Dashboard, booking, pembayaran, dan
                                        laporan dasar</span
                                    >
                                </div>
                            {/if}
                        </div>

                        <a
                            href={isAuthenticated
                                ? subscriptionHref
                                : `/register?plan=${plan.slug}&intent=paid`}
                            class={`mt-6 inline-flex h-11 w-full items-center justify-center rounded-md text-sm font-semibold ${plan.slug === 'pro' ? 'bg-[#103d3a] text-white hover:bg-[#0b2f2c]' : 'border border-[#bac5bd] text-[#17201f] hover:bg-[#eef2eb]'}`}
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

        <section id="compare" class="border-y border-[#d9ded4] bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div
                    class="mb-7 flex flex-col justify-between gap-4 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase text-[#b96c20]"
                        >
                            Perbandingan fitur
                        </p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-normal">
                            Lihat batas kapasitas dan fitur utama.
                        </h2>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-[#d9ded4] px-3 py-1.5 text-sm text-[#53615d]"
                    >
                        <ShieldCheck class="h-4 w-4 text-[#0d7066]" />
                        Tenant dan pool terpisah
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#d9ded4]">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead>
                                <tr
                                    class="border-b border-[#d9ded4] bg-[#f1f4ee]"
                                >
                                    <th
                                        class="px-4 py-3 text-left font-semibold"
                                        >Item</th
                                    >
                                    {#each plans as plan}
                                        <th
                                            class="px-4 py-3 text-center font-semibold"
                                            >{plan.name}</th
                                        >
                                    {/each}
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-[#e5e9e2]">
                                    <td class="px-4 py-3 font-medium">Armada</td
                                    >
                                    {#each plans as plan}<td
                                            class="px-4 py-3 text-center"
                                            >{capacityLabel(
                                                plan.max_armadas,
                                            )}</td
                                        >{/each}
                                </tr>
                                <tr class="border-b border-[#e5e9e2]">
                                    <td class="px-4 py-3 font-medium">Rute</td>
                                    {#each plans as plan}<td
                                            class="px-4 py-3 text-center"
                                            >{capacityLabel(
                                                plan.max_routes,
                                            )}</td
                                        >{/each}
                                </tr>
                                <tr class="border-b border-[#e5e9e2]">
                                    <td class="px-4 py-3 font-medium">User</td>
                                    {#each plans as plan}<td
                                            class="px-4 py-3 text-center"
                                            >{capacityLabel(plan.max_users)}</td
                                        >{/each}
                                </tr>
                                {#each allFeatureNames as featureName}
                                    <tr
                                        class="border-b border-[#e5e9e2] last:border-0"
                                    >
                                        <td class="px-4 py-3 font-medium"
                                            >{featureName}</td
                                        >
                                        {#each plans as plan}
                                            {@const feature =
                                                plan.features?.find(
                                                    (item) =>
                                                        item.name ===
                                                        featureName,
                                                )}
                                            <td class="px-4 py-3 text-center">
                                                {#if feature?.included}
                                                    <Check
                                                        class="mx-auto h-4 w-4 text-[#0d7066]"
                                                    />
                                                {:else}
                                                    <X
                                                        class="mx-auto h-4 w-4 text-[#9aa39f]"
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

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <div
                        class="flex items-center gap-2 text-sm font-semibold uppercase text-[#b96c20]"
                    >
                        <HelpCircle class="h-4 w-4" />
                        Cara memilih
                    </div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-normal">
                        Paket mengikuti skala operasional.
                    </h2>
                </div>
                <div class="grid gap-3">
                    {#each buyingGuides as guide}
                        <div
                            class="rounded-lg border border-[#d9ded4] bg-white p-5"
                        >
                            <h3 class="font-semibold">{guide.title}</h3>
                            <p class="mt-1 text-sm leading-6 text-[#53615d]">
                                {guide.body}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section class="border-t border-[#d9ded4] bg-[#103d3a]">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-12 text-white sm:px-6 md:grid-cols-[1fr_auto] md:items-center lg:px-8"
            >
                <div>
                    <p class="text-sm font-semibold uppercase text-[#a8dccd]">
                        Trial 14 hari
                    </p>
                    <h2
                        class="mt-2 text-3xl font-semibold tracking-normal text-white"
                    >
                        Mulai dengan paket yang paling dekat dengan kondisi saat
                        ini.
                    </h2>
                </div>
                <a
                    href={isAuthenticated
                        ? subscriptionHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-11 items-center justify-center rounded-md bg-white px-5 text-sm font-semibold text-[#103d3a] hover:bg-[#eef2eb]"
                >
                    {isAuthenticated
                        ? 'Kelola subscription'
                        : 'Daftar sekarang'}
                    <ArrowRight class="ml-2 h-4 w-4" />
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#d9ded4] bg-[#f7f8f4]">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-[#53615d] sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"
        >
            <span>OptiBus - Paket SaaS operasional travel.</span>
            <div class="flex gap-5">
                <a href="/" class="hover:text-[#17201f]">Landing</a>
                {#if isAuthenticated}
                    <a href={appHref} class="hover:text-[#17201f]">Dashboard</a>
                    <a href={subscriptionHref} class="hover:text-[#17201f]"
                        >Subscription</a
                    >
                {:else}
                    <a href="/login" class="hover:text-[#17201f]">Login</a>
                    <a
                        href="/register?plan=starter&intent=trial"
                        class="hover:text-[#17201f]">Daftar</a
                    >
                {/if}
            </div>
        </div>
    </footer>
</div>
