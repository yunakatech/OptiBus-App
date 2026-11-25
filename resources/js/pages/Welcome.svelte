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
    const supportWhatsappMessage = encodeURIComponent(
        'Halo tim OptiBus, saya membutuhkan bantuan.',
    );
    const supportWhatsappHref = `https://wa.me/6287778110950?text=${supportWhatsappMessage}`;
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
                    class="hidden items-center gap-7 text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground md:flex"
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
                        class="mb-5 inline-flex w-fit items-center gap-2 border-l-2 border-[#55E8B4] pl-3 text-xs md:text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground sm:mb-6"
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
                        class="mt-5 max-w-xl text-[15px] leading-7 text-muted-foreground sm:mt-6 sm:text-base sm:leading-8"
                    >
                        Kelola kursi, jadwal, penumpang, pembayaran, dan armada
                        dalam satu platform yang mudah digunakan bersama tim.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:mt-8 sm:flex-row">
                        <a
                            href={isAuthenticated
                                ? appHref
                                : '/register?plan=starter&intent=trial'}
                            class="motion-button inline-flex h-12 items-center justify-center bg-[#087F79] px-5 text-sm font-semibold text-white transition-colors hover:bg-[#075B5B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                        >
                            {isAuthenticated
                                ? 'Buka dasbor'
                                : 'Mulai uji coba gratis'}
                            <ArrowRight class="motion-arrow ml-2 h-4 w-4" />
                        </a>
                        <a
                            href="/pricing"
                            class="motion-button inline-flex h-12 items-center justify-center border border-[#AFC9C3] bg-transparent px-5 text-sm font-semibold text-[#163A39] transition-colors hover:bg-[#E7FAF3] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087F79]"
                        >
                            Bandingkan paket
                        </a>
                    </div>
                    <p class="mt-4 text-sm font-medium text-[#42635f]">
                        Coba OptiBus · Pilih paket sesuai kebutuhan
                    </p>
                    <dl
                        class="mt-8 grid max-w-xl grid-cols-3 gap-2 border-y border-[#DCE9E6] py-4 sm:mt-10 sm:gap-5 sm:py-5"
                    >
                        <div>
                            <dt
                                class="text-xs md:text-[10px] font-semibold uppercase tracking-[0.12em] text-muted-foreground sm:text-[11px]"
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
                                class="text-xs md:text-[10px] font-semibold uppercase tracking-[0.12em] text-muted-foreground sm:text-[11px]"
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
                                class="text-xs md:text-[10px] font-semibold uppercase tracking-[0.12em] text-muted-foreground sm:text-[11px]"
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
                        class="absolute inset-x-0 top-0 flex items-center justify-between border-b border-[#DCE9E6] px-4 py-2 text-xs md:text-[9px] font-semibold uppercase tracking-[0.18em] text-muted-foreground sm:px-6"
                    >
                        <span>Ruang kendali / 01</span>
                        <span>OptiBus</span>
                    </div>
                    <div
                        class="hero-product relative mx-auto mt-7 w-full max-w-[290px] sm:mt-8 sm:max-w-[470px] lg:max-w-[500px]"
                    >
                        <img
                            src="/landing/iPhone 12 Pro (Wooden Hands).webp"
                            alt="Mockup aplikasi OptiBus di iPhone saat memilih kursi perjalanan"
                            width="3000"
                            height="2250"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            class="product-shot relative z-10 h-auto w-full object-contain"
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
                    <p class="mt-4 max-w-md leading-7 text-muted-foreground">
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
                                <p class="text-sm text-muted-foreground">
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
                                class="mt-2 text-sm leading-6 text-muted-foreground"
                            >
                                {item.body}
                            </p>
                        </article>
                    {/each}
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
                        <div class="mt-4">
                            <span
                                class="font-sans text-3xl font-medium text-[#163A39]"
                                >{formatRupiah(plan.price_monthly)}</span
                            >
                            <span
                                class="text-xs uppercase tracking-[0.08em] text-muted-foreground"
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
                        : '/register?plan=starter&intent=trial'}
                    class="motion-button inline-flex h-12 items-center justify-center bg-[#F7FAF9] px-5 text-sm font-semibold text-[#163A39] transition-colors hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#55E8B4]"
                >
                    {isAuthenticated ? 'Buka dasbor' : 'Mulai uji coba'}
                    <ArrowRight class="motion-arrow ml-2 h-4 w-4" />
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
                        href="/register?plan=starter&intent=trial"
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
                transform: scale(1.025) rotate(-0.5deg);
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
