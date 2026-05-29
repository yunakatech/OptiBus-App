<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Armchair from 'lucide-svelte/icons/armchair';
    import CalendarClock from 'lucide-svelte/icons/calendar-clock';
    import Gauge from 'lucide-svelte/icons/gauge';
    import ShieldCheck from 'lucide-svelte/icons/shield-check';
    import AppHead from '@/components/AppHead.svelte';
    import TailwindShowcase from '@/components/TailwindShowcase.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login, register } from '@/routes';

    let {
        canRegister = true,
    }: {
        canRegister: boolean;
    } = $props();

    const auth = $derived(page.props.auth);

    const highlights = [
        {
            title: 'Live Booking Console',
            description: 'Pilih kursi, input customer, dan konfirmasi booking cepat dari satu layar.',
            icon: Armchair,
        },
        {
            title: 'Data Keberangkatan',
            description: 'List keberangkatan per jadwal dengan detail penumpang, status pembayaran, dan copy data instan.',
            icon: CalendarClock,
        },
        {
            title: 'Admin Operations',
            description: 'Kelola master data operasional, assignment, customer, dan laporan harian.',
            icon: ShieldCheck,
        },
    ];
</script>

<AppHead title="CabooQ" />

<div class="min-h-screen bg-background text-foreground">
    <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-10">
        <header class="mb-8 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img
                    src="/branding/qbus-logo-full.png"
                    alt="Qbus Booking & Operations Workspace"
                    class="h-auto w-[220px] object-contain"
                    loading="eager"
                    decoding="async"
                />
            </div>

            {#if auth.user}
                <Link
                    href={toUrl(dashboard())}
                    class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                >
                    Buka Dashboard
                </Link>
            {:else}
                <div class="flex items-center gap-2">
                    <Link
                        href={toUrl(login())}
                        class="inline-flex h-9 items-center rounded-md border px-4 text-sm font-medium"
                    >
                        Login
                    </Link>
                    {#if canRegister}
                        <Link
                            href={toUrl(register())}
                            class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                        >
                            Register
                        </Link>
                    {/if}
                </div>
            {/if}
        </header>

        <TailwindShowcase />

        <section class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-md border bg-card p-4 sm:col-span-2 lg:col-span-1">
                <div class="mb-2 inline-flex rounded-md bg-primary/10 p-2 text-primary">
                    <Gauge class="size-4" />
                </div>
                <p class="text-xs text-muted-foreground">Mode</p>
                <p class="text-sm font-semibold">Production-ready workspace</p>
            </article>

            <article class="rounded-md border bg-card p-4">
                <p class="text-xs text-muted-foreground">Frontend</p>
                <p class="text-sm font-semibold">Inertia + Svelte</p>
            </article>

            <article class="rounded-md border bg-card p-4">
                <p class="text-xs text-muted-foreground">Backend</p>
                <p class="text-sm font-semibold">Laravel</p>
            </article>

            <article class="rounded-md border bg-card p-4">
                <p class="text-xs text-muted-foreground">Fokus</p>
                <p class="text-sm font-semibold">Fast booking flow</p>
            </article>
        </section>

        <section aria-label="Fitur utama" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            {#each highlights as item (item.title)}
                <article class="rounded-md border bg-card p-4">
                    <div class="mb-3 inline-flex rounded-md bg-muted p-2">
                        <item.icon class="size-4 text-foreground" />
                    </div>
                    <h2 class="mb-1 text-sm font-semibold">{item.title}</h2>
                    <p class="text-sm text-muted-foreground">{item.description}</p>
                </article>
            {/each}
        </section>
    </main>
</div>
