<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import {
        ArrowRight,
        BarChart3,
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

    type PlanFeature = { name: string; group: string; included: boolean; limit: number | null };
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
            description: 'Untuk driver individu dan travel yang baru merapikan operasional.',
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
            description: 'Untuk travel kecil yang butuh seat map, laporan, dan booking online.',
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
            description: 'Untuk pemilik armada dengan multi-pool dan kontrol role tim.',
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

    const plans = $derived(((page.props.plans ?? []) as Plan[]).length > 0 ? ((page.props.plans ?? []) as Plan[]) : fallbackPlans);
    const featuredPlans = $derived(plans.slice(0, 3));

    const workflow = [
        { label: 'Booking', value: 'Seat, pelanggan, pickup', icon: Ticket },
        { label: 'Keberangkatan', value: 'Manifest dan unit aktif', icon: BusFront },
        { label: 'Bagasi', value: 'Resi dan status kiriman', icon: PackageCheck },
        { label: 'Keuangan', value: 'DP, lunas, target revenue', icon: WalletCards },
    ];

    const capabilities = [
        { title: 'Booking reguler', body: 'Seat, jadwal, manifest, dan data pelanggan tersimpan per rute.', icon: Ticket },
        { title: 'Carter dan bagasi', body: 'Transaksi non-seat tetap masuk laporan operasional harian.', icon: PackageCheck },
        { title: 'Pembayaran', body: 'Status DP, lunas, dan belum bayar dapat dipantau per transaksi.', icon: CreditCard },
        { title: 'Kontrol tenant', body: 'Pool, rute, user, dan log aktivitas dipisahkan untuk tiap perusahaan.', icon: ShieldCheck },
    ];

    function formatRupiah(value: number): string {
        if (value >= 1_000_000) return `Rp ${(value / 1_000_000).toFixed(1)} jt`;
        return `Rp ${(value / 1_000).toFixed(0)} rb`;
    }

    function limitLabel(value: number, suffix: string): string {
        return value > 0 ? `${value} ${suffix}` : `Unlimited ${suffix}`;
    }
</script>

<svelte:head>
    <title>Qbus - Sistem Operasional Travel</title>
    <meta name="description" content="Qbus membantu travel mengelola booking seat, carter, bagasi, pembayaran, pool, dan laporan revenue dalam satu sistem." />
</svelte:head>

<div class="min-h-screen bg-[#f7f8f4] text-[#17201f]">
    <nav class="sticky top-0 z-40 border-b border-[#d9ded4] bg-[#f7f8f4]/92 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center" aria-label="Qbus">
                <AppLogo />
            </a>
            <div class="hidden items-center gap-7 text-sm font-medium text-[#4b5a56] md:flex">
                <a href="#workflow" class="hover:text-[#17201f]">Workflow</a>
                <a href="#fitur" class="hover:text-[#17201f]">Fitur</a>
                <a href="/pricing" class="hover:text-[#17201f]">Pricing</a>
            </div>
            <div class="flex items-center gap-2">
                <a href="/login" class="hidden rounded-md px-3 py-2 text-sm font-semibold text-[#4b5a56] hover:bg-white md:inline-flex">Login</a>
                <a href="/register" class="inline-flex h-9 items-center justify-center rounded-md bg-[#103d3a] px-4 text-sm font-semibold text-white shadow-sm hover:bg-[#0b2f2c]">
                    Mulai trial
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="relative overflow-hidden border-b border-[#d9ded4]">
            <div class="pointer-events-none absolute inset-0 opacity-[0.28]" style="background-image: linear-gradient(#cad4ca 1px, transparent 1px), linear-gradient(90deg, #cad4ca 1px, transparent 1px); background-size: 44px 44px;"></div>
            <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:py-16 lg:grid-cols-[minmax(0,0.92fr)_minmax(520px,1fr)] lg:px-8 lg:py-20">
                <div class="flex flex-col justify-center">
                    <div class="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-[#cfd8cf] bg-white px-3 py-1 text-xs font-semibold uppercase text-[#53615d]">
                        <Clock3 class="h-3.5 w-3.5 text-[#b96c20]" />
                        Trial 14 hari - setup cepat
                    </div>
                    <h1 class="max-w-3xl text-4xl font-semibold leading-[1.03] tracking-normal text-[#152321] sm:text-5xl lg:text-6xl">
                        Operasional travel yang rapi dari booking sampai laporan.
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-8 text-[#4b5a56] sm:text-lg">
                        Qbus menyatukan booking seat, carter, bagasi, pembayaran, pool, rute, dan target revenue dalam dashboard yang siap dipakai tim lapangan.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="/register" class="inline-flex h-11 items-center justify-center rounded-md bg-[#103d3a] px-5 text-sm font-semibold text-white shadow-sm hover:bg-[#0b2f2c]">
                            Mulai trial gratis <ArrowRight class="ml-2 h-4 w-4" />
                        </a>
                        <a href="/pricing" class="inline-flex h-11 items-center justify-center rounded-md border border-[#bac5bd] bg-white px-5 text-sm font-semibold text-[#17201f] hover:bg-[#eef2eb]">
                            Bandingkan paket
                        </a>
                    </div>
                    <dl class="mt-10 grid max-w-xl grid-cols-3 gap-3 border-y border-[#d9ded4] py-5">
                        <div>
                            <dt class="text-xs font-medium uppercase text-[#687470]">Transaksi</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#17201f]">3 jalur</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-[#687470]">Scope</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#17201f]">Multi-pool</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-[#687470]">Audit</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#17201f]">Per tenant</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative">
                    <div class="overflow-hidden rounded-lg border border-[#bdc9c1] bg-[#17201f] shadow-2xl shadow-[#17201f]/18">
                        <div class="flex items-center justify-between border-b border-white/10 bg-[#111917] px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#ef7d32]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[#e4b94b]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[#5ab98f]"></span>
                            </div>
                            <span class="text-xs font-semibold uppercase text-white/55">Dashboard Operasional</span>
                        </div>
                        <div class="grid gap-3 p-4 text-white md:grid-cols-[1.1fr_0.9fr]">
                            <div class="rounded-md border border-white/10 bg-white/[0.06] p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase text-white/45">Target revenue hari ini</p>
                                        <p class="mt-1 text-2xl font-semibold">Rp 18.4 jt</p>
                                    </div>
                                    <BarChart3 class="h-8 w-8 text-[#80d8bd]" />
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-md bg-white/[0.08] p-3">
                                        <p class="text-[11px] text-white/45">Seat</p>
                                        <p class="text-lg font-semibold">42</p>
                                    </div>
                                    <div class="rounded-md bg-white/[0.08] p-3">
                                        <p class="text-[11px] text-white/45">Carter</p>
                                        <p class="text-lg font-semibold">6</p>
                                    </div>
                                    <div class="rounded-md bg-white/[0.08] p-3">
                                        <p class="text-[11px] text-white/45">Bagasi</p>
                                        <p class="text-lg font-semibold">28</p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-md border border-white/10 bg-white/[0.06] p-4">
                                <p class="text-xs uppercase text-white/45">Pool aktif</p>
                                <div class="mt-3 space-y-2">
                                    {#each ['Pool Pinrang', 'Pool Makassar', 'Pool Parepare'] as pool, index}
                                        <div class="flex items-center justify-between rounded-md bg-white/[0.08] px-3 py-2">
                                            <span class="text-sm">{pool}</span>
                                            <span class={`h-2 w-2 rounded-full ${index === 0 ? 'bg-[#80d8bd]' : 'bg-white/30'}`}></span>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                            <div class="rounded-md border border-white/10 bg-white/[0.06] p-4 md:col-span-2">
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-xs uppercase text-white/45">Keberangkatan berikutnya</p>
                                    <span class="rounded-full bg-[#ef7d32]/20 px-2 py-1 text-[11px] text-[#ffc79d]">09:00</span>
                                </div>
                                <div class="grid gap-2 md:grid-cols-3">
                                    {#each ['PINRANG - MAKASSAR', 'MAKASSAR - PAREPARE', 'CARTER BANDARA'] as routeName}
                                        <div class="rounded-md bg-[#f7f8f4] p-3 text-[#17201f]">
                                            <p class="text-[11px] font-semibold uppercase text-[#64716d]">{routeName}</p>
                                            <div class="mt-3 flex items-center justify-between text-sm">
                                                <span>Manifest</span>
                                                <span class="font-semibold">Ready</span>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="workflow" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <p class="text-sm font-semibold uppercase text-[#b96c20]">Workflow harian</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-normal text-[#17201f]">Satu alur untuk transaksi lapangan.</h2>
                    <p class="mt-4 text-[#53615d]">Tim booking, admin pool, driver, dan owner membaca data yang sama tanpa spreadsheet terpisah.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    {#each workflow as item}
                        {@const Icon = item.icon}
                        <div class="rounded-lg border border-[#d9ded4] bg-white p-5">
                            <Icon class="h-6 w-6 text-[#0d7066]" />
                            <h3 class="mt-4 text-base font-semibold">{item.label}</h3>
                            <p class="mt-1 text-sm text-[#53615d]">{item.value}</p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section id="fitur" class="border-y border-[#d9ded4] bg-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="mb-9 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase text-[#b96c20]">Fitur inti</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-normal">Didesain untuk travel, bukan POS umum.</h2>
                    </div>
                    <a href="/pricing" class="inline-flex items-center text-sm font-semibold text-[#0d7066] hover:text-[#094e47]">
                        Lihat semua paket <ArrowRight class="ml-1 h-4 w-4" />
                    </a>
                </div>
                <div class="grid gap-px overflow-hidden rounded-lg border border-[#d9ded4] bg-[#d9ded4] md:grid-cols-2 lg:grid-cols-4">
                    {#each capabilities as item}
                        {@const Icon = item.icon}
                        <div class="bg-[#fbfcf8] p-5">
                            <Icon class="h-6 w-6 text-[#0d7066]" />
                            <h3 class="mt-4 text-base font-semibold">{item.title}</h3>
                            <p class="mt-2 text-sm leading-6 text-[#53615d]">{item.body}</p>
                        </div>
                    {/each}
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <p class="text-sm font-semibold uppercase text-[#b96c20]">Paket</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-normal">Mulai kecil, naik saat armada bertambah.</h2>
                </div>
                <a href="/pricing" class="inline-flex h-10 items-center justify-center rounded-md border border-[#bac5bd] bg-white px-4 text-sm font-semibold text-[#17201f] hover:bg-[#eef2eb]">
                    Buka pricing lengkap
                </a>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                {#each featuredPlans as plan}
                    <article class={`rounded-lg border bg-white p-5 ${plan.slug === 'pro' ? 'border-[#0d7066] shadow-lg shadow-[#0d7066]/10' : 'border-[#d9ded4]'}`}>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold">{plan.name}</h3>
                                <p class="mt-2 min-h-12 text-sm leading-6 text-[#53615d]">{plan.description}</p>
                            </div>
                            {#if plan.slug === 'pro'}
                                <span class="rounded-full bg-[#eaf6f2] px-2.5 py-1 text-xs font-semibold text-[#0d7066]">Populer</span>
                            {/if}
                        </div>
                        <div class="mt-5">
                            <span class="text-3xl font-semibold">{formatRupiah(plan.price_monthly)}</span>
                            <span class="text-sm text-[#53615d]">/bulan</span>
                        </div>
                        <div class="mt-5 space-y-2 text-sm text-[#33403d]">
                            <div class="flex items-center gap-2"><BusFront class="h-4 w-4 text-[#0d7066]" /> {limitLabel(plan.max_armadas, 'armada')}</div>
                            <div class="flex items-center gap-2"><Route class="h-4 w-4 text-[#0d7066]" /> {limitLabel(plan.max_routes, 'rute')}</div>
                            <div class="flex items-center gap-2"><UsersRound class="h-4 w-4 text-[#0d7066]" /> {limitLabel(plan.max_users, 'user')}</div>
                        </div>
                        <a href={`/register?plan=${plan.slug}`} class={`mt-6 inline-flex h-10 w-full items-center justify-center rounded-md text-sm font-semibold ${plan.slug === 'pro' ? 'bg-[#103d3a] text-white hover:bg-[#0b2f2c]' : 'border border-[#bac5bd] text-[#17201f] hover:bg-[#eef2eb]'}`}>
                            Pilih {plan.name}
                        </a>
                    </article>
                {/each}
            </div>
        </section>

        <section class="border-t border-[#d9ded4] bg-[#103d3a]">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 text-white sm:px-6 md:grid-cols-[1fr_auto] md:items-center lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase text-[#a8dccd]">Siap dipakai tim operasional</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-normal text-white">Rapikan booking, pembayaran, dan laporan minggu ini.</h2>
                </div>
                <a href="/register" class="inline-flex h-11 items-center justify-center rounded-md bg-white px-5 text-sm font-semibold text-[#103d3a] hover:bg-[#eef2eb]">
                    Mulai trial <ArrowRight class="ml-2 h-4 w-4" />
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#d9ded4] bg-[#f7f8f4]">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-[#53615d] sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <span>Qbus - Sistem operasional travel.</span>
            <div class="flex gap-5">
                <a href="/pricing" class="hover:text-[#17201f]">Pricing</a>
                <a href="/login" class="hover:text-[#17201f]">Login</a>
                <a href="/register" class="hover:text-[#17201f]">Daftar</a>
            </div>
        </div>
    </footer>
</div>
