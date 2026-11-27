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
    import { onMount } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavigationMetrics from '@/components/NavigationMetrics.svelte';
    import ViewportOnly from '@/components/ViewportOnly.svelte';
    import { getSupportWhatsappHref } from '@/lib/support';

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

    const plans = $derived((page.props.plans ?? []) as Plan[]);
    const trialDays = $derived(Math.max(0, Number(page.props.trialDays ?? 0)));
    const trialAvailable = $derived(trialDays > 0 && plans.some((plan) => plan.slug === 'starter'));
    const registerHref = $derived(trialAvailable ? '/register?plan=starter&intent=trial' : '/register');
    const featuredPlans = $derived(plans.slice(0, 3));
    const isAuthenticated = $derived(Boolean(page.props.auth?.user));
    const appHref = '/dashboard';
    const subscriptionHref = '/subscription';
    const supportWhatsappHref = getSupportWhatsappHref();
    let landingRoot: HTMLDivElement;

    onMount(() => {
        const root = landingRoot;
        const preference = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        );

        if (preference.matches || !('IntersectionObserver' in window)) {
return;
}

        const targets = Array.from(
            root.querySelectorAll<HTMLElement>('[data-reveal]'),
        );
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) {
continue;
}

                    entry.target.classList.remove('motion-pending');
                    observer.unobserve(entry.target);
                }
            },
            { threshold: 0.12, rootMargin: '0px 0px -24px 0px' },
        );

        // Enhance only offscreen content; the first screen is always readable.
        for (const target of targets) {
            if (target.getBoundingClientRect().top < window.innerHeight - 24) {
continue;
}

            target.classList.add('motion-pending');
            observer.observe(target);
        }

        const revealFocused = (event: FocusEvent) => {
            const target =
                event.target instanceof Element
                    ? event.target.closest('[data-reveal]')
                    : null;

            if (target) {
                target.classList.remove('motion-pending');
                observer.unobserve(target);
            }
        };
        const revealAll = () => {
            if (!preference.matches) {
return;
}

            observer.disconnect();
            targets.forEach((target) =>
                target.classList.remove('motion-pending'),
            );
        };
        root.addEventListener('focusin', revealFocused);
        preference.addEventListener('change', revealAll);

        return () => {
            observer.disconnect();
            root.removeEventListener('focusin', revealFocused);
            preference.removeEventListener('change', revealAll);
            targets.forEach((target) =>
                target.classList.remove('motion-pending'),
            );
        };
    });

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
        return `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
    }

    function limitLabel(value: number, suffix: string): string {
        return value > 0 ? `${value} ${suffix}` : `${suffix} tanpa batas`;
    }
</script>

<NavigationMetrics />

<svelte:head>
    <title>OptiBus — Kendali Operasional Travel</title>
    <meta
        name="description"
        content="Kelola pemesanan kursi, carter, bagasi, pembayaran, pool, dan laporan perjalanan dalam satu alur kerja dengan OptiBus."
    />
</svelte:head>

<div
    bind:this={landingRoot}
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
                    class="hidden items-center gap-7 text-xs font-semibold uppercase tracking-[0.12em] text-[#42635f] md:flex"
                >
                    <a
                        href="#workflow"
                        class="transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79]"
                        >Alur Kerja</a
                    >
                    <a
                        href="#fitur"
                        class="transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79]"
                        >Fitur</a
                    >
                    <a
                        href="/pricing"
                        class="transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087F79]"
                        >Harga</a
                    >
                </div></ViewportOnly
            >
            <div class="flex shrink-0 items-center gap-2">
                {#if !isAuthenticated}
                    <a
                        href="/login"
                        class="inline-flex h-10 items-center justify-center px-2 text-xs font-semibold text-[#42635f] transition-colors hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79] sm:px-3"
                        >Masuk</a
                    >
                {/if}
                <a
                    href={isAuthenticated
                        ? appHref
                        : registerHref}
                    class="inline-flex h-10 items-center justify-center bg-[#087F79] px-4 text-xs font-semibold text-white transition-colors hover:bg-[#075B5B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                >
                    {isAuthenticated ? 'Dasbor' : 'Daftar gratis'}
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="relative overflow-hidden border-b border-[#DCE9E6]">
            <ViewportOnly minWidth={1024}
                ><div
                    class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-full max-w-7xl -translate-x-1/2 opacity-40 lg:block"
                    style="background-image: linear-gradient(#DCE9E6 1px, transparent 1px), linear-gradient(90deg, #DCE9E6 1px, transparent 1px); background-size: 52px 52px;"
                ></div></ViewportOnly
            >
            <div
                class="relative mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[minmax(0,0.94fr)_minmax(420px,1.06fr)] lg:items-center lg:gap-12 lg:px-10 lg:py-20"
            >
                <div
                    class="hero-copy relative z-10 flex flex-col justify-center"
                >
                    <div
                        class="mb-5 inline-flex w-fit items-center gap-2 border-l-2 border-[#55E8B4] pl-3 text-xs md:text-[11px] font-semibold uppercase tracking-[0.16em] text-[#42635f] sm:mb-6"
                    >
                        <Clock3 class="h-4 w-4 text-[#087F79]" />
                        Software operasional travel & shuttle
                    </div>
                    <h1
                        class="max-w-3xl font-sans text-[2.45rem] font-medium leading-[0.98] tracking-[-0.04em] text-[#163A39] sm:text-5xl lg:text-[4.25rem]"
                    >
                        Operasional travel lebih rapi, dari booking hingga laporan.
                    </h1>
                    <p
                        class="mt-5 max-w-xl text-[15px] leading-7 text-[#42635f] sm:mt-6 sm:text-base sm:leading-8"
                    >
                        Kelola kursi, jadwal, penumpang, pembayaran, dan armada
                        dalam satu platform yang mudah digunakan bersama tim.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:mt-8 sm:flex-row">
                        <a
                            href={isAuthenticated
                                ? appHref
                                : registerHref}
                            class="motion-button inline-flex h-12 items-center justify-center bg-[#087F79] px-5 text-sm font-semibold text-white transition-colors hover:bg-[#075B5B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                        >
                            {isAuthenticated
                                ? 'Buka dasbor'
                                : trialAvailable ? 'Daftar gratis' : 'Daftar sekarang'}
                            <ArrowRight class="motion-arrow ml-2 h-4 w-4" />
                        </a>
                        <a
                            href="/pricing"
                            class="motion-button inline-flex h-12 items-center justify-center border border-[#AFC9C3] bg-transparent px-5 text-sm font-semibold text-[#163A39] transition-colors hover:bg-[#E7FAF3] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                        >
                            Bandingkan paket
                        </a>
                    </div>
                    <a href={supportWhatsappHref} target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex min-h-11 w-fit items-center text-sm font-semibold text-[#087F79] underline underline-offset-4">Konsultasi via WhatsApp</a>
                    <p class="mt-1 text-sm font-medium text-[#42635f]">
                        {trialAvailable ? `Daftar gratis · Uji coba Starter ${trialDays} hari` : 'Pilih paket sesuai kebutuhan operasional Anda'}
                    </p>
                    <dl
                        class="mt-8 grid max-w-xl grid-cols-3 gap-2 border-y border-[#DCE9E6] py-4 sm:mt-10 sm:gap-5 sm:py-5"
                    >
                        <div>
                            <dt
                                class="text-xs md:text-[10px] font-semibold uppercase tracking-[0.12em] text-[#42635f] sm:text-[11px]"
                            >
                                Transaksi
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-[#163A39] sm:text-base"
                            >
                                Kursi, carter, bagasi
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs md:text-[10px] font-semibold uppercase tracking-[0.12em] text-[#42635f] sm:text-[11px]"
                            >
                                Cakupan
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-[#163A39] sm:text-base"
                            >
                                Tim & pool
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs md:text-[10px] font-semibold uppercase tracking-[0.12em] text-[#42635f] sm:text-[11px]"
                            >
                                Audit
                            </dt>
                            <dd
                                class="mt-1 text-sm font-semibold text-[#163A39] sm:text-base"
                            >
                                Data terpisah
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="relative flex items-center justify-center overflow-hidden rounded-3xl border border-[#c8e5da] bg-[#E7FAF3] px-4 py-3 sm:px-8 sm:py-6 lg:min-h-[550px]"
                >
                    <div
                        class="absolute inset-x-0 top-0 flex items-center justify-between border-b border-[#DCE9E6] px-4 py-2 text-xs md:text-[9px] font-semibold uppercase tracking-[0.18em] text-[#42635f] sm:px-6"
                    >
                        <span>Ruang kendali / 01</span>
                        <span>OptiBus</span>
                    </div>
                    <div
                        class="hero-product relative mx-auto mt-7 w-full max-w-[340px] sm:mt-8 sm:max-w-[520px] lg:max-w-[560px]"
                    >
                        <div class="laptop-mockup relative z-10">
                            <div class="laptop-screen">
                                <div class="laptop-camera" aria-hidden="true"></div>
                                <div class="desktop-mockup overflow-hidden bg-white">
                                    <div class="desktop-mockup__bar" aria-hidden="true"><span></span><span></span><span></span><p>optibus.app / dashboard</p></div>
                                    <div class="screenshot-crop"><img src="/landing/dashboard-desktop.png" alt="Dashboard OptiBus dalam mockup laptop" width="1920" height="827" loading="eager" decoding="async" fetchpriority="high" class="product-shot block h-auto" /></div>
                                </div>
                            </div>
                            <div class="laptop-base" aria-hidden="true"></div>
                        </div>
                        <div class="hero-phone absolute -right-2 -bottom-8 z-20 overflow-hidden rounded-[22px] p-1.5">
                            <div class="hero-phone__speaker" aria-hidden="true"></div>
                            <img src="/landing/booking-mobile.png" alt="Peta kursi OptiBus pada ponsel" width="317" height="689" loading="eager" decoding="async" class="block h-auto w-full rounded-[16px]" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            id="workflow"
            class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20"
        >
            <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:gap-16">
                <div data-reveal class="lg:pt-2">
                    <p
                        class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#087F79]"
                    >
                        Dari loket ke garasi
                    </p>
                    <h2
                        class="mt-3 max-w-sm font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#163A39] sm:text-4xl"
                    >
                        Satu catatan untuk seluruh perjalanan.
                    </h2>
                    <p class="mt-4 max-w-md leading-7 text-[#42635f]">
                        Pesanan yang masuk menjadi dasar manifest, status
                        pembayaran, dan laporan harian. Informasi tetap
                        tersambung saat berpindah dari meja reservasi ke
                        operasional pool.
                    </p>
                </div>
                <ol class="divide-y divide-[#DCE9E6] border-y border-[#DCE9E6]">
                    {#each workflow as item, index (item.label)}
                        {@const Icon = item.icon}
                        <li
                            data-reveal
                            style:--reveal-delay={`${index * 55}ms`}
                            class="grid grid-cols-[2.5rem_2rem_1fr] items-center gap-3 py-4 sm:grid-cols-[3rem_2.5rem_1fr] sm:gap-4 sm:py-5"
                        >
                            <span class="font-sans text-lg text-[#087F79]">
                                0{index + 1}
                            </span>
                            <Icon
                                class="h-5 w-5 text-[#087F79]"
                                aria-hidden="true"
                            />
                            <div
                                class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-5"
                            >
                                <h3
                                    class="text-base font-semibold text-[#163A39]"
                                >
                                    {item.label}
                                </h3>
                                <p class="text-sm text-[#42635f]">
                                    {item.value}
                                </p>
                            </div>
                        </li>
                    {/each}
                </ol>
            </div>
        </section>

        <section id="fitur" class="border-y border-[#DCE9E6] bg-[#E7FAF3]">
            <div
                class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20"
            >
                <div
                    data-reveal
                    class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#087F79]"
                        >
                            Yang tercatat
                        </p>
                        <h2
                            class="mt-3 font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#163A39] sm:text-4xl"
                        >
                            Rincian yang dibutuhkan saat hari berjalan.
                        </h2>
                    </div>
                    <a
                        href="/pricing"
                        class="inline-flex min-h-10 items-center text-sm font-semibold text-[#087F79] underline decoration-[#087F79] underline-offset-4 hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                    >
                        Lihat semua paket <ArrowRight class="ml-1 h-4 w-4" />
                    </a>
                </div>
                <div
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    {#each capabilities as item, index (item.title)}
                        {@const Icon = item.icon}
                        <article
                            data-reveal
                            style:--reveal-delay={`${index * 55}ms`}
                            class="rounded-2xl border border-[#DCE9E6] bg-white p-5 sm:p-6"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span class="font-sans text-sm text-[#087F79]">
                                    0{index + 1}
                                </span>
                                <Icon
                                    class="h-5 w-5 text-[#087F79]"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3
                                class="mt-5 text-base font-semibold text-[#163A39]"
                            >
                                {item.title}
                            </h3>
                            <p
                                class="mt-2 text-sm leading-6 text-[#42635f]"
                            >
                                {item.body}
                            </p>
                        </article>
                    {/each}
                </div>
            </div>
        </section>

        <section class="border-b border-[#DCE9E6] bg-[#F1F8F5]">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1fr_1fr] lg:px-10">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#087F79]">Booking dari ponsel</p><h2 class="mt-4 max-w-xl text-3xl font-medium leading-tight tracking-tight text-[#163A39] sm:text-4xl">Peta kursi dan pesanan dalam genggaman.</h2><p class="mt-4 max-w-lg text-sm leading-7 text-[#42635f]">Lihat kursi yang tersedia, pilih tempat duduk, dan lanjutkan pengisian data penumpang dari ponsel.</p></div>
                <figure class="mobile-mockup-stage flex justify-center rounded-3xl border border-[#c8e5da] bg-white p-6 sm:p-10"><div class="phone-mockup"><div class="phone-mockup__speaker" aria-hidden="true"></div><img src="/landing/booking-mobile.png" alt="Tampilan peta kursi dan rekap booking OptiBus pada ponsel" width="317" height="689" loading="lazy" decoding="async" class="block h-auto w-full rounded-[1.45rem]" /></div></figure>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-10">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#087F79]">Lihat OptiBus bekerja</p>
                <h2 class="mt-4 max-w-2xl text-3xl font-medium leading-tight tracking-tight text-[#163A39] sm:text-4xl">Informasi perjalanan, siap dilihat tim Anda.</h2>
                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    <figure class="screenshot-card overflow-hidden rounded-2xl border border-[#DCE9E6] bg-[#F7FAF9]"><div class="screen-chrome" aria-hidden="true"><span></span><span></span><span></span><p>Keberangkatan</p></div><div class="screenshot-crop"><img src="/landing/keberangkatan-desktop.png" alt="Tabel data keberangkatan, driver, armada, dan status pembayaran di OptiBus" width="1920" height="827" loading="lazy" decoding="async" class="screenshot-image h-auto" /></div><figcaption class="px-5 py-4 text-sm font-semibold text-[#163A39]">Pantau keberangkatan dan manifest</figcaption></figure>
                    <figure class="screenshot-card overflow-hidden rounded-2xl border border-[#DCE9E6] bg-[#F7FAF9]"><div class="screen-chrome" aria-hidden="true"><span></span><span></span><span></span><p>Booking</p></div><div class="screenshot-crop"><img src="/landing/booking-desktop.png" alt="Form booking dan peta kursi OptiBus di komputer" width="1920" height="827" loading="lazy" decoding="async" class="screenshot-image h-auto" /></div><figcaption class="px-5 py-4 text-sm font-semibold text-[#163A39]">Catat booking dan pilih kursi</figcaption></figure>
                    <figure class="screenshot-card overflow-hidden rounded-2xl border border-[#DCE9E6] bg-[#F7FAF9] lg:col-span-2"><div class="screen-chrome" aria-hidden="true"><span></span><span></span><span></span><p>Dashboard operasional</p></div><div class="screenshot-crop"><img src="/landing/dashboard-desktop.png" alt="Dashboard OptiBus dengan grafik pendapatan dan ringkasan layanan" width="1920" height="827" loading="lazy" decoding="async" class="screenshot-image h-auto" /></div><figcaption class="px-5 py-4 text-sm font-semibold text-[#163A39]">Lihat perkembangan pendapatan</figcaption></figure>
                </div>
            </div>
        </section>

        <section id="solusi" class="border-b border-[#DCE9E6] bg-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-10">
                <div class="grid gap-5 lg:grid-cols-[1fr_1.4fr] lg:gap-16">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#087F79]">Dibuat untuk perjalanan Anda</p>
                        <h2 class="mt-4 max-w-md text-3xl font-medium leading-tight tracking-tight text-[#163A39] sm:text-4xl">Satu sistem, sesuai cara Anda bekerja.</h2>
                        <p class="mt-4 max-w-md text-sm leading-7 text-[#42635f]">Dari kendaraan sendiri hingga tim di beberapa pool, mulai dengan kebutuhan operasional Anda hari ini.</p>
                    </div>
                    <div class="divide-y divide-[#DCE9E6] border-y border-[#DCE9E6]">
                        <article class="grid gap-2 py-5 sm:grid-cols-[10rem_1fr]"><h3 class="font-semibold text-[#163A39]">Individu</h3><p class="text-sm leading-6 text-[#42635f]">Catat pelanggan dan perjalanan dari perangkat yang Anda gunakan sehari-hari.</p></article>
                        <article class="grid gap-2 py-5 sm:grid-cols-[10rem_1fr]"><h3 class="font-semibold text-[#163A39]">Travel berkembang</h3><p class="text-sm leading-6 text-[#42635f]">Pantau jadwal, kursi, pembayaran, carter, dan bagasi bersama tim.</p></article>
                        <article class="grid gap-2 py-5 sm:grid-cols-[10rem_1fr]"><h3 class="font-semibold text-[#163A39]">Perusahaan</h3><p class="text-sm leading-6 text-[#42635f]">Kelola kebutuhan multi-pool dan akses pengguna sesuai paket. <a href={supportWhatsappHref} target="_blank" rel="noopener noreferrer" class="font-semibold text-[#087F79] underline underline-offset-4">Konsultasikan kebutuhan Anda</a>.</p></article>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-10 lg:py-20"
        >
            <div
                data-reveal
                class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
            >
                <div>
                    <p
                        class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#087F79]"
                    >
                        Pilih skala
                    </p>
                    <h2
                        class="mt-3 font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#163A39] sm:text-4xl"
                    >
                        Mulai dari kebutuhan hari ini.
                    </h2>
                </div>
                <a
                    href="/pricing"
                    class="inline-flex min-h-11 items-center justify-center border border-[#AFC9C3] px-4 text-sm font-semibold text-[#163A39] transition-colors hover:bg-[#E7FAF3] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                >
                    Buka harga lengkap
                </a>
            </div>
            {#if featuredPlans.length === 0}
                <p class="rounded-xl border border-[#DCE9E6] bg-white p-5 text-sm text-[#42635f]">Informasi paket belum tersedia. <a href={supportWhatsappHref} target="_blank" rel="noopener noreferrer" class="font-semibold text-[#087F79] underline">Hubungi kami melalui WhatsApp</a>.</p>
            {/if}
            <div
                class="grid gap-4 md:grid-cols-3"
            >
                {#each featuredPlans as plan, index (plan.slug)}
                    <article
                        data-reveal
                        style:--reveal-delay={`${index * 65}ms`}
                        class={`plan-panel relative rounded-2xl border p-6 ${plan.slug === 'pro' ? 'border-[#087F79] bg-[#E7FAF3]' : 'border-[#DCE9E6] bg-white'}`}
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3
                                    class="font-sans text-2xl font-medium text-[#163A39]"
                                >
                                    {plan.name}
                                </h3>
                                <p
                                    class="mt-2 min-h-10 text-sm leading-6 text-[#42635f]"
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
                        <div class="mt-4">
                            <span
                                class="font-sans text-3xl font-medium text-[#163A39]"
                                >{formatRupiah(plan.price_monthly)}</span
                            >
                            <span
                                class="text-xs uppercase tracking-[0.08em] text-[#42635f]"
                                >/ bulan</span
                            >
                        </div>
                        <div
                            class="mt-5 space-y-3 border-t border-[#DCE9E6] pt-4 text-sm text-[#163A39]"
                        >
                            <div class="flex items-center gap-2">
                                <BusFront class="h-4 w-4 text-[#087F79]" />
                                {limitLabel(plan.max_armadas, 'armada')}
                            </div>
                            <div class="flex items-center gap-2">
                                <Route class="h-4 w-4 text-[#087F79]" />
                                {limitLabel(plan.max_routes, 'rute')}
                            </div>
                            <div class="flex items-center gap-2">
                                <UsersRound class="h-4 w-4 text-[#087F79]" />
                                {limitLabel(plan.max_users, 'pengguna')}
                            </div>
                            <div class="flex items-center gap-2">
                                <UsersRound class="h-4 w-4 text-[#087F79]" />
                                {limitLabel(plan.max_pools, 'pool')}
                            </div>
                        </div>
                        <a
                            href={isAuthenticated
                                ? subscriptionHref
                                : `/register?plan=${plan.slug}&intent=paid`}
                            class={`motion-button mt-6 inline-flex h-11 w-full items-center justify-center text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79] ${plan.slug === 'pro' ? 'bg-[#087F79] text-white hover:bg-[#075B5B]' : 'border border-[#AFC9C3] text-[#163A39] hover:bg-[#E7FAF3]'}`}
                        >
                            {isAuthenticated
                                ? 'Kelola paket'
                                : `Pilih ${plan.name}`}
                        </a>
                    </article>
                {/each}
            </div>
        </section>

        <section class="border-t border-[#DCE9E6] bg-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16 lg:px-10">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#087F79]">Mulai dengan OptiBus</p><h2 class="mt-4 text-3xl font-medium leading-tight tracking-tight text-[#163A39] sm:text-4xl">Siap dipakai untuk perjalanan berikutnya.</h2><p class="mt-4 text-sm leading-7 text-[#42635f]">Mulai dengan tiga langkah sederhana.</p><ol class="mt-6 space-y-3 text-sm text-[#163A39]"><li><span class="mr-3 font-semibold text-[#087F79]">01</span>Daftar dan buat akun.</li><li><span class="mr-3 font-semibold text-[#087F79]">02</span>Lengkapi data operasional.</li><li><span class="mr-3 font-semibold text-[#087F79]">03</span>Mulai mencatat perjalanan.</li></ol></div>
                <div class="divide-y divide-[#DCE9E6] border-y border-[#DCE9E6]">
                    {#if trialAvailable}<details class="group py-5"><summary class="cursor-pointer font-semibold text-[#163A39]">Bagaimana uji coba gratis bekerja?</summary><p class="mt-3 text-sm leading-6 text-[#42635f]">Daftar untuk mencoba paket Starter selama {trialDays} hari. Uji coba tersedia bagi email yang belum pernah menggunakannya. Setelah masa uji coba berakhir, pilih langganan untuk melanjutkan akses.</p></details>{/if}
                    <details class="group py-5"><summary class="cursor-pointer font-semibold text-[#163A39]">Apakah OptiBus bisa digunakan dari ponsel?</summary><p class="mt-3 text-sm leading-6 text-[#42635f]">Ya. OptiBus dapat diakses melalui browser di ponsel dan komputer.</p></details>
                    <details class="group py-5"><summary class="cursor-pointer font-semibold text-[#163A39]">Bagaimana memilih paket untuk tim saya?</summary><p class="mt-3 text-sm leading-6 text-[#42635f]">Bandingkan kapasitas armada, pengguna, dan pool di halaman harga. Untuk kebutuhan khusus, hubungi tim kami melalui WhatsApp.</p></details>
                    <details class="group py-5"><summary class="cursor-pointer font-semibold text-[#163A39]">Bagaimana jika saya memerlukan bantuan?</summary><p class="mt-3 text-sm leading-6 text-[#42635f]">Tim OptiBus dapat dihubungi melalui <a href={supportWhatsappHref} target="_blank" rel="noopener noreferrer" class="font-semibold text-[#087F79] underline underline-offset-4">WhatsApp</a> untuk membahas penggunaan dan kebutuhan usaha Anda.</p></details>
                </div>
            </div>
        </section>

        <section class="border-t border-[#163A39] bg-[#163A39] text-[#F7FAF9]">
            <div
                data-reveal
                class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 sm:py-14 md:grid-cols-[1fr_auto] md:items-center lg:px-10 lg:py-16"
            >
                <div>
                    <p
                        class="text-xs md:text-[11px] font-bold uppercase tracking-[0.18em] text-[#55E8B4]"
                    >
                        Langkah berikutnya
                    </p>
                    <h2
                        class="mt-3 max-w-3xl font-sans text-3xl font-medium leading-tight tracking-[-0.03em] text-[#EAF6F2] sm:text-4xl"
                    >
                        Lihat bagaimana alur perjalanan Anda bekerja di OptiBus.
                    </h2>
                </div>
                <a
                    href={isAuthenticated
                        ? appHref
                        : registerHref}
                    class="motion-button inline-flex h-12 items-center justify-center bg-[#F7FAF9] px-5 text-sm font-semibold text-[#163A39] transition-colors hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#55E8B4]"
                >
                    {isAuthenticated ? 'Buka dasbor' : trialAvailable ? `Daftar gratis · ${trialDays} hari` : 'Daftar sekarang'}
                    <ArrowRight class="motion-arrow ml-2 h-4 w-4" />
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#DCE9E6] bg-[#F7FAF9]">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-6 text-sm text-[#42635f] sm:px-6 md:flex-row md:items-center md:justify-between lg:px-10"
        >
            <span class="font-sans text-base text-[#163A39]">
                OptiBus
                <span class="font-sans text-xs text-[#42635f]">
                    / operasional perjalanan
                </span>
            </span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a
                    href="/pricing"
                    class="inline-flex min-h-10 items-center hover:text-[#087F79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                    >Harga</a
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
                        href={registerHref}
                        class="inline-flex min-h-10 items-center hover:text-[#087F79]"
                        >Daftar</a
                    >
                {/if}
            </div>
        </div>
    </footer>

    <a
        href={supportWhatsappHref}
        target="_blank"
        rel="noopener noreferrer"
        class="fixed right-4 bottom-4 z-50 inline-flex min-h-14 items-center gap-3 rounded-full bg-[#25D366] px-5 text-sm font-semibold text-white shadow-[0_8px_24px_rgba(22,58,57,0.22)] transition hover:-translate-y-0.5 hover:bg-[#1FBE5B] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#128C7E] focus-visible:ring-offset-2 sm:right-6 sm:bottom-6"
        aria-label="Butuh Bantuan? Hubungi kami melalui WhatsApp"
    >
        <svg
            aria-hidden="true"
            viewBox="0 0 24 24"
            class="size-6 shrink-0 fill-current"
        >
            <path
                d="M20.52 3.48A11.87 11.87 0 0 0 12.06 0C5.47 0 .11 5.36.11 11.95c0 2.11.55 4.17 1.6 5.98L0 24l6.22-1.63a11.9 11.9 0 0 0 5.83 1.48h.01c6.59 0 11.95-5.36 11.95-11.95 0-3.19-1.24-6.19-3.49-8.42ZM12.06 21.8a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.69.97.99-3.6-.23-.37a9.85 9.85 0 0 1-1.51-5.26c0-5.44 4.43-9.87 9.88-9.87 2.63 0 5.1 1.03 6.96 2.89a9.8 9.8 0 0 1 2.89 6.98c0 5.44-4.43 9.85-9.89 9.85Zm5.42-7.39c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.67-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.08 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"
            />
        </svg>
        <span>Butuh Bantuan?</span>
    </a>
</div>

<style>
    section[id] {
        scroll-margin-top: 6rem;
    }

    .laptop-screen {
        padding: 0.65rem 0.55rem 0.5rem;
        border: 1px solid #405654;
        border-radius: 14px 14px 4px 4px;
        background: #1d3332;
        box-shadow: 0 18px 36px oklch(0.22 0.04 175 / 0.18);
    }

    .laptop-camera {
        width: 4px;
        height: 4px;
        margin: -0.38rem auto 0.25rem;
        border-radius: 50%;
        background: #829795;
    }

    .laptop-base {
        position: relative;
        width: 106%;
        height: 0.65rem;
        margin-left: -3%;
        border-radius: 1px 1px 50% 50%;
        background: linear-gradient(#d4e0dd, #8fa6a2);
        box-shadow: 0 8px 12px oklch(0 0 0 / 0.12);
    }

    .laptop-base::after {
        position: absolute;
        top: 0;
        left: 42%;
        width: 16%;
        height: 0.2rem;
        border-radius: 0 0 5px 5px;
        background: #94aaa6;
        content: '';
    }

    .desktop-mockup {
        border-radius: 3px;
        border: 1px solid oklch(0 0 0 / 0.1);
    }

    .desktop-mockup__bar,
    .screen-chrome {
        display: flex;
        align-items: center;
        gap: 0.32rem;
        height: 1.75rem;
        padding: 0 0.72rem;
        background: #f4f7f6;
        border-bottom: 1px solid #dce9e6;
    }

    .desktop-mockup__bar span,
    .screen-chrome span {
        width: 0.42rem;
        height: 0.42rem;
        border-radius: 999px;
        background: #afc9c3;
    }

    .desktop-mockup__bar span:first-child,
    .screen-chrome span:first-child {
        background: #55e8b4;
    }

    .desktop-mockup__bar p,
    .screen-chrome p {
        margin-left: 0.4rem;
        overflow: hidden;
        color: #42635f;
        font-size: 0.58rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .hero-phone {
        width: clamp(4.8rem, 22%, 7.4rem);
        border: 1px solid oklch(0 0 0 / 0.1);
        background: #163a39;
        box-shadow:
            0 1px 2px oklch(0 0 0 / 0.1),
            0 16px 30px oklch(0.22 0.04 175 / 0.22);
        transform: rotate(5deg);
    }

    .hero-phone__speaker {
        position: absolute;
        top: 0.5rem;
        left: 35%;
        z-index: 1;
        width: 30%;
        height: 0.4rem;
        border-radius: 999px;
        background: #163a39;
    }

    .mobile-mockup-stage {
        background-image: radial-gradient(circle at 50% 26%, #e7faf3, transparent 62%);
    }

    .phone-mockup {
        position: relative;
        width: min(100%, 18.5rem);
        padding: 0.48rem;
        border: 1px solid oklch(0 0 0 / 0.1);
        border-radius: 2rem;
        background: #163a39;
        box-shadow:
            0 1px 2px oklch(0 0 0 / 0.1),
            0 22px 42px oklch(0.22 0.04 175 / 0.2);
    }

    .phone-mockup__speaker {
        position: absolute;
        top: 0.78rem;
        left: 50%;
        z-index: 1;
        width: 4rem;
        height: 1.15rem;
        border-radius: 999px;
        background: #163a39;
        transform: translateX(-50%);
    }

    .screenshot-card {
        border-color: transparent;
        box-shadow:
            0 1px 2px oklch(0 0 0 / 0.06),
            0 12px 28px oklch(0.22 0.04 175 / 0.1);
    }

    .screenshot-crop {
        overflow: hidden;
    }

    .screenshot-crop img {
        display: block;
        width: 101.3%;
        max-width: none;
    }

    .screenshot-image {
        outline: 1px solid oklch(0 0 0 / 0.1);
        outline-offset: -1px;
        filter: contrast(1.025) saturate(1.04);
    }

    @media (prefers-reduced-motion: no-preference) {
        .hero-copy > * {
            animation: hero-arrive 560ms cubic-bezier(0.22, 1, 0.36, 1)
                backwards;
        }
        .hero-copy > :nth-child(2) {
            animation-delay: 55ms;
        }
        .hero-copy > :nth-child(3) {
            animation-delay: 110ms;
        }
        .hero-copy > :nth-child(4) {
            animation-delay: 165ms;
        }
        .hero-copy > :nth-child(5) {
            animation-delay: 220ms;
        }
        .hero-product {
            animation: product-arrive 740ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        [data-reveal] {
            transition:
                opacity 520ms cubic-bezier(0.22, 1, 0.36, 1),
                transform 520ms cubic-bezier(0.22, 1, 0.36, 1),
                background-color 180ms ease;
            transition-delay:
                var(--reveal-delay, 0ms), var(--reveal-delay, 0ms), 0ms;
        }
        [data-reveal]:global(.motion-pending) {
            opacity: 0;
            transform: translateY(18px);
        }
        [data-reveal]:focus-within {
            opacity: 1;
            transform: none;
            transition: none;
        }
        .motion-button {
            transition:
                transform 180ms ease,
                background-color 180ms ease;
        }
        .motion-button:active {
            transform: translateY(1px);
        }
        .motion-button :global(.motion-arrow) {
            transition: transform 180ms ease;
        }
        .motion-button:focus-visible :global(.motion-arrow) {
            transform: translateX(3px);
        }
        .product-shot {
            transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
        }
        @media (hover: hover) and (pointer: fine) {
            .motion-button:hover {
                transform: translateY(-2px);
            }
            .motion-button:hover :global(.motion-arrow) {
                transform: translateX(3px);
            }
            .hero-product:hover .product-shot {
                transform: scale(1.015);
            }
            .plan-panel:hover,
            .plan-panel:focus-within {
                background-color: #e7faf3;
            }
        }
    }

    @media (prefers-reduced-motion: reduce) {
        [data-reveal] {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
    }

    @keyframes hero-arrive {
        from {
            opacity: 0.55;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    @keyframes product-arrive {
        from {
            opacity: 0.65;
            transform: translate(18px, 8px) rotate(1deg);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>
