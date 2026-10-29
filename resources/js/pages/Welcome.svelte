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
    <title>OptiBus — Kendali Operasional Travel</title>
    <meta
        name="description"
        content="Kelola pemesanan kursi, carter, bagasi, pembayaran, pool, dan laporan perjalanan dalam satu alur kerja dengan OptiBus."
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
                    href="#workflow"
                    class="transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532]"
                    >Alur Kerja</a
                >
                <a
                    href="#fitur"
                    class="transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532]"
                    >Fitur</a
                >
                <a
                    href="/pricing"
                    class="transition-colors hover:text-[#b96532] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b96532]"
                    >Harga</a
                >
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
        <section class="relative overflow-hidden border-b border-[#c9c2b3]">
            <div
                class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-full max-w-7xl -translate-x-1/2 opacity-40 lg:block"
                style="background-image: linear-gradient(#d2cabb 1px, transparent 1px), linear-gradient(90deg, #d2cabb 1px, transparent 1px); background-size: 52px 52px;"
            ></div>
            <div
                class="relative mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[minmax(0,0.94fr)_minmax(420px,1.06fr)] lg:items-center lg:gap-12 lg:px-10 lg:py-20"
            >
                <div class="relative z-10 flex flex-col justify-center">
                    <div
                        class="mb-5 inline-flex w-fit items-center gap-2 border-l-2 border-[#bc6738] pl-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#596660] sm:mb-6"
                    >
                        <Clock3 class="h-4 w-4 text-[#a94e28]" />
                        14 hari untuk mencoba alur kerja
                    </div>
                    <h1
                        class="max-w-3xl font-serif text-[2.45rem] font-medium leading-[0.98] tracking-[-0.04em] text-[#173a34] sm:text-5xl lg:text-[4.25rem]"
                    >
                        Setiap perjalanan, dari loket sampai laporan.
                    </h1>
                    <p
                        class="mt-5 max-w-xl text-[15px] leading-7 text-[#4e5b55] sm:mt-6 sm:text-base sm:leading-8"
                    >
                        Catat pesanan, atur keberangkatan, pantau pembayaran,
                        dan tutup hari dengan laporan yang sama-sama dibaca tim.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:mt-8 sm:flex-row">
                        <a
                            href={isAuthenticated
                                ? appHref
                                : '/register?plan=starter&intent=trial'}
                            class="inline-flex h-12 items-center justify-center bg-[#174b43] px-5 text-sm font-semibold text-white transition-colors hover:bg-[#103a34] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b96532]"
                        >
                            {isAuthenticated
                                ? 'Buka dasbor'
                                : 'Mulai uji coba gratis'}
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </a>
                        <a
                            href="/pricing"
                            class="inline-flex h-12 items-center justify-center border border-[#aaa494] bg-transparent px-5 text-sm font-semibold text-[#243a34] transition-colors hover:bg-[#e6e0d4] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b96532]"
                        >
                            Bandingkan paket
                        </a>
                    </div>
                    <dl
                        class="mt-8 grid max-w-xl grid-cols-3 gap-2 border-y border-[#c9c2b3] py-4 sm:mt-10 sm:gap-5 sm:py-5"
                    >
                        <div>
                            <dt
                                class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#68736c] sm:text-[11px]"
                            >
                                Transaksi
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-[#173a34] sm:text-base"
                            >
                                3 jalur
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#68736c] sm:text-[11px]"
                            >
                                Cakupan
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-[#173a34] sm:text-base"
                            >
                                Multi-pool
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#68736c] sm:text-[11px]"
                            >
                                Audit
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-[#173a34] sm:text-base"
                            >
                                Per tenant
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="relative -mx-2 flex items-center justify-center bg-[#e8e2d7] px-4 py-3 sm:mx-0 sm:px-8 sm:py-6 lg:min-h-[550px]"
                >
                    <div
                        class="absolute inset-x-0 top-0 flex items-center justify-between border-b border-[#cec6b7] px-4 py-2 text-[9px] font-semibold uppercase tracking-[0.18em] text-[#5d6962] sm:px-6"
                    >
                        <span>Ruang kendali / 01</span>
                        <span>OptiBus</span>
                    </div>
                    <div
                        class="relative mx-auto mt-7 w-full max-w-[290px] sm:mt-8 sm:max-w-[470px] lg:max-w-[500px]"
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
            class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20"
        >
            <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:gap-16">
                <div class="lg:pt-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#a94e28]">
                        Dari loket ke garasi
                    </p>
                    <h2
                        class="mt-3 max-w-sm font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#173a34] sm:text-4xl"
                    >
                        Satu catatan untuk seluruh perjalanan.
                    </h2>
                    <p class="mt-4 max-w-md leading-7 text-[#53615b]">
                        Pesanan yang masuk menjadi dasar manifest, status
                        pembayaran, dan laporan harian. Informasi tetap
                        tersambung saat berpindah dari meja reservasi ke
                        operasional pool.
                    </p>
                </div>
                <ol class="divide-y divide-[#c9c2b3] border-y border-[#c9c2b3]">
                    {#each workflow as item, index}
                        {@const Icon = item.icon}
                        <li
                            class="grid grid-cols-[2.5rem_2rem_1fr] items-center gap-3 py-4 sm:grid-cols-[3rem_2.5rem_1fr] sm:gap-4 sm:py-5"
                        >
                            <span class="font-serif text-lg text-[#a94e28]">
                                0{index + 1}
                            </span>
                            <Icon
                                class="h-5 w-5 text-[#174b43]"
                                aria-hidden="true"
                            />
                            <div
                                class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-5"
                            >
                                <h3 class="text-base font-semibold text-[#213a34]">
                                    {item.label}
                                </h3>
                                <p class="text-sm text-[#59665f]">
                                    {item.value}
                                </p>
                            </div>
                        </li>
                    {/each}
                </ol>
            </div>
        </section>

        <section id="fitur" class="border-y border-[#c9c2b3] bg-[#e8e2d7]">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20">
                <div
                    class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#a94e28]"
                        >
                            Yang tercatat
                        </p>
                        <h2
                            class="mt-3 font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#173a34] sm:text-4xl"
                        >
                            Rincian yang dibutuhkan saat hari berjalan.
                        </h2>
                    </div>
                    <a
                        href="/pricing"
                        class="inline-flex min-h-10 items-center text-sm font-semibold text-[#174b43] underline decoration-[#af7655] underline-offset-4 hover:text-[#a94e28] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]"
                    >
                        Lihat semua paket <ArrowRight class="ml-1 h-4 w-4" />
                    </a>
                </div>
                <div class="grid border-y border-[#c9c2b3] sm:grid-cols-2 lg:grid-cols-4">
                    {#each capabilities as item, index}
                        {@const Icon = item.icon}
                        <article
                            class="border-b border-[#c9c2b3] py-5 sm:px-5 sm:py-6 lg:border-b-0 lg:border-r lg:px-5 lg:first:pl-0 lg:last:border-r-0"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-serif text-sm text-[#a94e28]">
                                    0{index + 1}
                                </span>
                                <Icon
                                    class="h-5 w-5 text-[#174b43]"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3
                                class="mt-5 text-base font-semibold text-[#213a34]"
                            >
                                {item.title}
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-[#53615b]">
                                {item.body}
                            </p>
                        </article>
                    {/each}
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20">
            <div
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
            >
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#a94e28]">
                        Pilih skala
                    </p>
                    <h2
                        class="mt-3 font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#173a34] sm:text-4xl"
                    >
                        Mulai dari kebutuhan hari ini.
                    </h2>
                </div>
                <a
                    href="/pricing"
                    class="inline-flex min-h-11 items-center justify-center border border-[#aaa494] px-4 text-sm font-semibold text-[#243a34] transition-colors hover:bg-[#e6e0d4] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]"
                >
                    Buka harga lengkap
                </a>
            </div>
            <div
                class="grid gap-0 border-y border-[#c9c2b3] md:grid-cols-3 md:divide-x md:divide-[#c9c2b3]"
            >
                {#each featuredPlans as plan}
                    <article
                        class={`relative border-b border-[#c9c2b3] bg-transparent px-1 py-6 sm:px-5 sm:py-7 md:border-b-0 ${plan.slug === 'pro' ? 'bg-[#e8e2d7] md:-my-px md:border-y-2 md:border-y-[#174b43]' : ''}`}
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3
                                    class="font-serif text-2xl font-medium text-[#173a34]"
                                >
                                    {plan.name}
                                </h3>
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
                        <div class="mt-4">
                            <span
                                class="font-serif text-3xl font-medium text-[#173a34]"
                                >{formatRupiah(plan.price_monthly)}</span
                            >
                            <span
                                class="text-xs uppercase tracking-[0.08em] text-[#53615b]"
                                >/ bulan</span
                            >
                        </div>
                        <div
                            class="mt-5 space-y-3 border-t border-[#c9c2b3] pt-4 text-sm text-[#33433d]"
                        >
                            <div class="flex items-center gap-2">
                                <BusFront class="h-4 w-4 text-[#a94e28]" />
                                {limitLabel(plan.max_armadas, 'armada')}
                            </div>
                            <div class="flex items-center gap-2">
                                <Route class="h-4 w-4 text-[#a94e28]" />
                                {limitLabel(plan.max_routes, 'rute')}
                            </div>
                            <div class="flex items-center gap-2">
                                <UsersRound class="h-4 w-4 text-[#a94e28]" />
                                {limitLabel(plan.max_users, 'pengguna')}
                            </div>
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
                        </a>
                    </article>
                {/each}
            </div>
        </section>

        <section class="border-t border-[#173a34] bg-[#173a34] text-[#f1eee5]">
            <div
                class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 sm:py-14 md:grid-cols-[1fr_auto] md:items-center lg:px-10 lg:py-16"
            >
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#d9a27d]">
                        Langkah berikutnya
                    </p>
                    <h2
                        class="mt-3 max-w-3xl font-serif text-3xl font-medium leading-tight tracking-[-0.03em] text-[#f8f4eb] sm:text-4xl"
                    >
                        Lihat bagaimana alur perjalanan Anda bekerja di OptiBus.
                    </h2>
                </div>
                <a
                    href={isAuthenticated
                        ? appHref
                        : '/register?plan=starter&intent=trial'}
                    class="inline-flex h-12 items-center justify-center bg-[#f1eee5] px-5 text-sm font-semibold text-[#173a34] transition-colors hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d9a27d]"
                >
                    {isAuthenticated ? 'Buka dasbor' : 'Mulai uji coba'}
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
                    / operasional perjalanan
                </span>
            </span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a
                    href="/pricing"
                    class="inline-flex min-h-10 items-center hover:text-[#a94e28] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#a94e28]"
                    >Harga</a
                >
                {#if isAuthenticated}
                    <a
                        href={appHref}
                        class="inline-flex min-h-10 items-center hover:text-[#a94e28]"
                        >Dasbor</a
                    >
                    <a
                        href={subscriptionHref}
                        class="inline-flex min-h-10 items-center hover:text-[#a94e28]"
                        >Langganan</a
                    >
                {:else}
                    <a
                        href="/login"
                        class="inline-flex min-h-10 items-center hover:text-[#a94e28]"
                        >Masuk</a
                    >
                    <a
                        href="/register?plan=starter&intent=trial"
                        class="inline-flex min-h-10 items-center hover:text-[#a94e28]"
                        >Daftar</a
                    >
                {/if}
            </div>
        </div>
    </footer>
</div>
