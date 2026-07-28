<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import {
        ArrowRight,
        BusFront,
        Clock3,
        CreditCard,
        PackageCheck,
        Route,
        ShieldCheck,
        Ticket,
        UsersRound,
        WalletCards,
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
                'Untuk pengemudi individu dan travel yang baru merapikan operasional.',
            price_monthly: 49000,
            price_yearly: 490000,
            max_armadas: 1,
            max_routes: 2,
            max_users: 1,
            max_pools: 1,
            max_drivers: 1,
            features: [],
        },
        {
            id: 2,
            name: 'Pro',
            slug: 'pro',
            description:
                'Untuk travel kecil yang butuh peta kursi, laporan, dan pemesanan online.',
            price_monthly: 99000,
            price_yearly: 990000,
            max_armadas: 3,
            max_routes: 0,
            max_users: 3,
            max_pools: 2,
            max_drivers: 3,
            features: [],
        },
        {
            id: 3,
            name: 'Fleet',
            slug: 'fleet',
            description:
                'Untuk pemilik armada dengan multi-pool dan kontrol peran tim.',
            price_monthly: 199000,
            price_yearly: 1990000,
            max_armadas: 10,
            max_routes: 0,
            max_users: 10,
            max_pools: 5,
            max_drivers: 15,
            features: [],
        },
    ];

    const plans = $derived(
        ((page.props.plans ?? []) as Plan[]).length > 0
            ? ((page.props.plans ?? []) as Plan[])
            : fallbackPlans,
    );
    const featuredPlans = $derived(plans.slice(0, 3));
    const isAuthenticated = $derived(Boolean(page.props.auth?.user));
    const appHref = '/dashboard';
    const subscriptionHref = '/subscription';

    const workflow = [
        {
            label: 'Pemesanan',
            value: 'Kursi, pelanggan, titik jemput',
            icon: Ticket,
        },
        {
            label: 'Keberangkatan',
            value: 'Manifest dan unit aktif',
            icon: BusFront,
        },
        {
            label: 'Bagasi',
            value: 'Resi dan status kiriman',
            icon: PackageCheck,
        },
        {
            label: 'Keuangan',
            value: 'DP, lunas, target pendapatan',
            icon: WalletCards,
        },
    ];

    const capabilities = [
        {
            title: 'Pemesanan reguler',
            body: 'Kursi, jadwal, manifest, dan data pelanggan tersimpan per rute.',
            icon: Ticket,
        },
        {
            title: 'Carter dan bagasi',
            body: 'Transaksi non-kursi tetap masuk laporan operasional harian.',
            icon: PackageCheck,
        },
        {
            title: 'Pembayaran',
            body: 'Status DP, lunas, dan belum bayar dapat dipantau per transaksi.',
            icon: CreditCard,
        },
        {
            title: 'Kontrol tenant',
            body: 'Pool, rute, pengguna, dan log aktivitas dipisahkan untuk tiap perusahaan.',
            icon: ShieldCheck,
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
</script>

<svelte:head>
    <title>OptiBus - Sistem Operasional Travel</title>
    <meta
        name="description"
        content="OptiBus membantu travel mengelola pemesanan kursi, carter, bagasi, pembayaran, pool, dan laporan pendapatan dalam satu sistem."
    />
</svelte:head>

<div class="min-h-screen overflow-x-hidden bg-[#f7f8f4] text-[#17201f]">
    <nav
        class="sticky top-0 z-40 bg-[#f7f8f4]/86 py-1.5 backdrop-blur-xl sm:py-2"
    >
        <div
            class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-2.5 sm:px-5 lg:px-6"
        >
            <a
                href="/"
                class="flex h-9 max-w-[44vw] items-center overflow-hidden rounded-full border border-[#d9ded4]/80 bg-white/86 px-2 shadow-[0_10px_28px_-24px_rgba(23,32,31,0.9)] transition hover:border-[#b9c5bd] hover:bg-white sm:h-10 sm:max-w-none sm:px-2.5"
                aria-label="OptiBus"
            >
                <AppLogo />
            </a>
            <div
                class="hidden items-center gap-1 rounded-full border border-[#d9ded4]/90 bg-white/72 p-1 text-xs font-semibold text-[#4b5a56] shadow-[0_14px_38px_-30px_rgba(23,32,31,0.88)] ring-1 ring-white/70 md:flex"
            >
                <a
                    href="#workflow"
                    class="rounded-full px-3 py-1.5 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                    >Alur Kerja</a
                >
                <a
                    href="#fitur"
                    class="rounded-full px-3 py-1.5 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                    >Fitur</a
                >
                <a
                    href="/pricing"
                    class="rounded-full px-3 py-1.5 transition hover:bg-[#eef2eb] hover:text-[#17201f]"
                    >Harga</a
                >
            </div>
            <div
                class="flex shrink-0 items-center gap-1 rounded-full border border-[#d9ded4]/90 bg-white/80 p-1 shadow-[0_14px_38px_-30px_rgba(23,32,31,0.88)] ring-1 ring-white/70"
            >
                {#if !isAuthenticated}
                    <a
                        href="/login"
                        class="inline-flex h-8 items-center justify-center rounded-full px-2.5 text-xs font-semibold text-[#4b5a56] transition hover:bg-[#eef2eb] hover:text-[#17201f] sm:h-9 sm:px-3"
                        >Masuk</a
                    >
                {/if}
                <a
                    href={isAuthenticated
                        ? appHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-8 items-center justify-center rounded-full bg-[#103d3a] px-3 text-xs font-semibold text-white shadow-[0_12px_26px_-18px_rgba(16,61,58,0.9)] transition hover:-translate-y-0.5 hover:bg-[#0b2f2c] sm:h-9 sm:px-4"
                >
                    {isAuthenticated ? 'Dasbor' : 'Mulai'}
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="relative overflow-hidden border-b border-[#d9ded4]">
            <div
                class="pointer-events-none absolute inset-y-0 left-1/2 w-full max-w-6xl -translate-x-1/2 opacity-[0.32]"
                style="background-image: linear-gradient(#cad4ca 1px, transparent 1px), linear-gradient(90deg, #cad4ca 1px, transparent 1px); background-size: 44px 44px;"
            ></div>
            <div
                class="relative mx-auto grid max-w-6xl gap-5 px-4 py-7 sm:px-5 md:py-11 lg:grid-cols-[minmax(0,0.92fr)_minmax(460px,0.95fr)] lg:px-6 lg:py-14"
            >
                <div class="flex flex-col justify-center">
                    <div
                        class="mb-3 inline-flex w-fit items-center gap-2 rounded-full border border-[#cfd8cf] bg-white px-3 py-1 text-[11px] font-semibold uppercase text-[#53615d] sm:mb-4"
                    >
                        <Clock3 class="h-3.5 w-3.5 text-[#b96c20]" />
                        Uji coba 14 hari - pengaturan cepat
                    </div>
                    <h1
                        class="max-w-3xl text-[1.9rem] font-semibold leading-[1.03] tracking-normal text-[#152321] sm:text-4xl lg:text-5xl"
                    >
                        Operasional travel yang rapi dari pemesanan sampai
                        laporan.
                    </h1>
                    <p
                        class="mt-3 max-w-2xl text-[14px] leading-6 text-[#4b5a56] sm:mt-4 sm:text-base sm:leading-7"
                    >
                        OptiBus menyatukan pemesanan kursi, carter, bagasi,
                        pembayaran, pool, rute, dan target pendapatan dalam
                        dasbor yang siap dipakai tim lapangan.
                    </p>
                    <div class="mt-5 flex flex-col gap-2.5 sm:mt-6 sm:flex-row">
                        <a
                            href={isAuthenticated
                                ? appHref
                                : '/register?plan=starter&intent=trial'}
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-[#103d3a] px-4 text-sm font-semibold text-white shadow-sm hover:bg-[#0b2f2c] sm:h-9 sm:rounded-md"
                        >
                            {isAuthenticated
                                ? 'Buka dasbor'
                                : 'Mulai uji coba gratis'}
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </a>
                        <a
                            href="/pricing"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-[#bac5bd] bg-white px-4 text-sm font-semibold text-[#17201f] hover:bg-[#eef2eb] sm:h-9 sm:rounded-md"
                        >
                            Bandingkan paket
                        </a>
                    </div>
                    <dl
                        class="mt-5 grid max-w-xl grid-cols-1 gap-3 border-y border-[#d9ded4] py-3 min-[390px]:grid-cols-3 sm:mt-6 sm:py-4"
                    >
                        <div>
                            <dt
                                class="text-xs font-medium uppercase text-[#687470]"
                            >
                                Transaksi
                            </dt>
                            <dd
                                class="mt-1 text-base font-semibold text-[#17201f] sm:text-lg"
                            >
                                3 jalur
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium uppercase text-[#687470]"
                            >
                                Cakupan
                            </dt>
                            <dd
                                class="mt-1 text-base font-semibold text-[#17201f] sm:text-lg"
                            >
                                Multi-pool
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium uppercase text-[#687470]"
                            >
                                Audit
                            </dt>
                            <dd
                                class="mt-1 text-base font-semibold text-[#17201f] sm:text-lg"
                            >
                                Per tenant
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="relative -mt-2 flex items-center justify-center sm:mt-0"
                >
                    <div
                        class="relative mx-auto w-full max-w-[300px] sm:max-w-[500px] lg:max-w-[460px] xl:max-w-[500px]"
                    >
                        <img
                            src="/landing/iPhone 12 Pro (Wooden Hands).webp"
                            alt="Mockup aplikasi OptiBus di iPhone saat memilih kursi perjalanan"
                            width="3000"
                            height="2250"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            class="relative z-10 h-auto w-full object-contain"
                        />
                    </div>
                </div>
            </div>
        </section>

        <section
            id="workflow"
            class="mx-auto max-w-6xl px-4 py-8 sm:px-5 sm:py-10 lg:px-6"
        >
            <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <p class="text-sm font-semibold uppercase text-[#b96c20]">
                        Alur kerja harian
                    </p>
                    <h2
                        class="mt-2 text-xl font-semibold tracking-normal text-[#17201f] sm:text-2xl"
                    >
                        Satu alur untuk transaksi lapangan.
                    </h2>
                    <p class="mt-4 text-[#53615d]">
                        Tim pemesanan, admin pool, pengemudi, dan pemilik
                        membaca data yang sama tanpa lembar kerja terpisah.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    {#each workflow as item}
                        {@const Icon = item.icon}
                        <div
                            class="rounded-2xl border border-[#d9ded4] bg-white p-3 shadow-[0_16px_40px_-32px_rgba(23,32,31,0.9)] sm:rounded-lg sm:p-4"
                        >
                            <Icon class="h-6 w-6 text-[#0d7066]" />
                            <h3 class="mt-4 text-base font-semibold">
                                {item.label}
                            </h3>
                            <p class="mt-1 text-sm text-[#53615d]">
                                {item.value}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section id="fitur" class="border-y border-[#d9ded4] bg-white">
            <div class="mx-auto max-w-6xl px-4 py-8 sm:px-5 sm:py-10 lg:px-6">
                <div
                    class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase text-[#b96c20]"
                        >
                            Fitur inti
                        </p>
                        <h2
                            class="mt-2 text-xl font-semibold tracking-normal sm:text-2xl"
                        >
                            Didesain untuk travel, bukan POS umum.
                        </h2>
                    </div>
                    <a
                        href="/pricing"
                        class="inline-flex items-center text-sm font-semibold text-[#0d7066] hover:text-[#094e47]"
                    >
                        Lihat semua paket <ArrowRight class="ml-1 h-4 w-4" />
                    </a>
                </div>
                <div
                    class="grid gap-3 overflow-hidden rounded-2xl md:gap-px md:border md:border-[#d9ded4] md:bg-[#d9ded4] md:grid-cols-2 lg:grid-cols-4"
                >
                    {#each capabilities as item}
                        {@const Icon = item.icon}
                        <div
                            class="rounded-2xl border border-[#d9ded4] bg-[#fbfcf8] p-3 md:rounded-none md:border-0 md:p-4"
                        >
                            <Icon class="h-6 w-6 text-[#0d7066]" />
                            <h3 class="mt-4 text-base font-semibold">
                                {item.title}
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-[#53615d]">
                                {item.body}
                            </p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-5 sm:py-10 lg:px-6">
            <div
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
            >
                <div>
                    <p class="text-sm font-semibold uppercase text-[#b96c20]">
                        Paket
                    </p>
                    <h2
                        class="mt-2 text-xl font-semibold tracking-normal sm:text-2xl"
                    >
                        Mulai kecil, naik saat armada bertambah.
                    </h2>
                </div>
                <a
                    href="/pricing"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-[#bac5bd] bg-white px-4 text-sm font-semibold text-[#17201f] hover:bg-[#eef2eb] sm:h-9 sm:rounded-md"
                >
                    Buka harga lengkap
                </a>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                {#each featuredPlans as plan}
                    <article
                        class={`rounded-2xl border bg-white p-3 shadow-[0_18px_44px_-36px_rgba(23,32,31,0.95)] sm:p-4 ${plan.slug === 'pro' ? 'border-[#0d7066] shadow-[#0d7066]/10' : 'border-[#d9ded4]'}`}
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold">
                                    {plan.name}
                                </h3>
                                <p
                                    class="mt-2 min-h-10 text-sm leading-6 text-[#53615d]"
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
                        <div class="mt-4">
                            <span class="text-2xl font-semibold"
                                >{formatRupiah(plan.price_monthly)}</span
                            >
                            <span class="text-sm text-[#53615d]">/bulan</span>
                        </div>
                        <div class="mt-4 space-y-1.5 text-sm text-[#33403d]">
                            <div class="flex items-center gap-2">
                                <BusFront class="h-4 w-4 text-[#0d7066]" />
                                {limitLabel(plan.max_armadas, 'armada')}
                            </div>
                            <div class="flex items-center gap-2">
                                <Route class="h-4 w-4 text-[#0d7066]" />
                                {limitLabel(plan.max_routes, 'rute')}
                            </div>
                            <div class="flex items-center gap-2">
                                <UsersRound class="h-4 w-4 text-[#0d7066]" />
                                {limitLabel(plan.max_users, 'pengguna')}
                            </div>
                        </div>
                        <a
                            href={isAuthenticated
                                ? subscriptionHref
                                : `/register?plan=${plan.slug}&intent=paid`}
                            class={`mt-5 inline-flex h-10 w-full items-center justify-center rounded-xl text-sm font-semibold sm:h-9 sm:rounded-md ${plan.slug === 'pro' ? 'bg-[#103d3a] text-white hover:bg-[#0b2f2c]' : 'border border-[#bac5bd] text-[#17201f] hover:bg-[#eef2eb]'}`}
                        >
                            {isAuthenticated
                                ? 'Kelola paket'
                                : `Pilih ${plan.name}`}
                        </a>
                    </article>
                {/each}
            </div>
        </section>

        <section class="border-t border-[#d9ded4] bg-[#103d3a]">
            <div
                class="mx-auto grid max-w-6xl gap-5 px-4 py-8 text-white sm:px-5 sm:py-10 md:grid-cols-[1fr_auto] md:items-center lg:px-6"
            >
                <div>
                    <p class="text-sm font-semibold uppercase text-[#a8dccd]">
                        Siap dipakai tim operasional
                    </p>
                    <h2
                        class="mt-2 text-xl font-semibold tracking-normal text-white sm:text-2xl"
                    >
                        Rapikan pemesanan, pembayaran, dan laporan minggu ini.
                    </h2>
                </div>
                <a
                    href={isAuthenticated
                        ? appHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-white px-4 text-sm font-semibold text-[#103d3a] hover:bg-[#eef2eb] sm:h-9 sm:rounded-md"
                >
                    {isAuthenticated ? 'Buka dasbor' : 'Mulai uji coba'}
                    <ArrowRight class="ml-2 h-4 w-4" />
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#d9ded4] bg-[#f7f8f4]">
        <div
            class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-sm text-[#53615d] sm:px-5 md:flex-row md:items-center md:justify-between lg:px-6"
        >
            <span>OptiBus - Sistem operasional travel.</span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="/pricing" class="hover:text-[#17201f]">Harga</a>
                {#if isAuthenticated}
                    <a href={appHref} class="hover:text-[#17201f]">Dasbor</a>
                    <a href={subscriptionHref} class="hover:text-[#17201f]"
                        >Langganan</a
                    >
                {:else}
                    <a href="/login" class="hover:text-[#17201f]">Masuk</a>
                    <a
                        href="/register?plan=starter&intent=trial"
                        class="hover:text-[#17201f]">Daftar</a
                    >
                {/if}
            </div>
        </div>
    </footer>
</div>
