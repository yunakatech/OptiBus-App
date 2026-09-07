<script lang="ts">
    import MobileBottomNav from '../../../resources/js/components/MobileBottomNav.svelte';
    import {
        Dialog,
        DialogContent,
    } from '../../../resources/js/components/ui/dialog';
    import { page, activity } from './inertia.svelte';

    let theme = $state('light');
    let scenario = $state('full');
    let large = $state(false);
    let keyboard = $state(false);
    let inset = $state(false);
    let dialog = $state(false);

    if (typeof window !== 'undefined') {
        const initialTheme = new URLSearchParams(window.location.search).get(
            'theme',
        );

        if (initialTheme === 'dark') {
            theme = 'dark';
        }
    }

    const presets: Record<string, string[]> = {
        full: [
            'dashboard.view',
            'booking.view',
            'luggage.view',
            'charter.view',
        ],
        partial: ['booking.view', 'charter.view'],
        left: ['dashboard.view', 'booking.view'],
        plain: ['dashboard.view', 'luggage.view', 'charter.view'],
        locked: [],
        none: [],
        platform: ['dashboard.view', 'booking.view'],
    };

    $effect(() => {
        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.classList.toggle('fixture-large-text', large);
        document.documentElement.dataset.keyboardOpen = String(keyboard);
        document.documentElement.classList.toggle('fixture-inset', inset);
    });
    $effect(() => {
        page.props.auth.permissions = presets[scenario];
        page.props.auth.billing_access.locked = scenario === 'locked';
        page.props.auth.user.is_super_admin = scenario === 'platform';
        page.props.auth.active_tenant =
            scenario === 'platform' ? null : { name: 'Tenant QA' };
    });
</script>

<main class="mobile-app-content fixture-page">
    <header>
        <p class="eyebrow">OPTIBUS / COMPONENT QA</p>
        <h1>Siap berangkat.</h1>
        <p>Uji navigasi mobile dengan data contoh.</p>
    </header>
    <section class="fixture-controls" aria-label="Pengaturan QA">
        <label
            >Tema<select bind:value={theme}
                ><option value="light">Light</option><option value="dark"
                    >Dark</option
                ></select
            ></label
        >
        <label
            >Akses menu<select bind:value={scenario}
                ><option value="full">Semua menu</option><option value="partial"
                    >Berangkat + Console + Carter</option
                ><option value="left">Menu kiri saja</option><option
                    value="plain">Tanpa Console</option
                ><option value="locked">Billing terkunci</option><option
                    value="none">Tanpa menu</option
                ><option value="platform">Super admin platform</option></select
            ></label
        >
        <label
            >Halaman<select bind:value={page.url}
                ><option value="/dashboard">Dashboard</option><option
                    value="/bookings/detail/demo">Detail keberangkatan</option
                ><option value="/booking-console">Console</option><option
                    value="/payments">Di luar menu</option
                ><option value="/bookings-extra">Prefix URL berbeda</option
                ></select
            ></label
        >
        <label class="check"
            ><input type="checkbox" bind:checked={large} />Teks 200%</label
        >
        <label class="check"
            ><input type="checkbox" bind:checked={keyboard} />Simulasi keyboard</label
        >
        <label class="check"
            ><input type="checkbox" bind:checked={inset} />Simulasi inset 24 px</label
        >
        <label class="check"
            ><input type="checkbox" bind:checked={activity.slow} />Navigasi
            lambat</label
        >
        <label class="check"
            ><input type="checkbox" bind:checked={activity.fail} />Navigasi
            gagal</label
        >
        <button onclick={() => (dialog = true)}>Buka dialog</button>
        <p role="status">
            Visit: {activity.visits} · Prefetch: {activity.prefetches} · Halaman:
            {page.url}
        </p>
    </section>
    <section class="fixture-trip">
        <p class="eyebrow">PERJALANAN HARI INI</p>
        <h2>Makassar → Pinrang</h2>
        <p>08.00 · Armada reguler</p>
        <span class="fixture-status">Siap berangkat</span>
    </section>
    <p class="fixture-end">
        Konten terakhir — harus tetap dapat dibaca di atas navigasi.
    </p>
</main>
<MobileBottomNav />
<Dialog bind:open={dialog}>
    <DialogContent>
        <h2>Dialog pengujian</h2>
        <p>Panel ini berada di atas bottom bar.</p>
        <button onclick={() => (dialog = false)}>Tutup dialog</button>
    </DialogContent>
</Dialog>

<style>
    :global(html.fixture-large-text) {
        font-size: 32px;
    }
    :global(.fixture-inset .mobile-bottom-navigation) {
        padding-bottom: 32px;
    }
    .fixture-page {
        max-width: 680px;
        margin: auto;
        padding-inline: 20px;
        padding-top: 28px;
    }
    header {
        margin-bottom: 24px;
    }
    .eyebrow {
        font-size: 10px;
        letter-spacing: 0.16em;
        font-weight: 600;
        color: var(--primary);
    }
    h1 {
        font-size: 24px;
        margin: 8px 0;
    }
    h2 {
        font-size: 18px;
        margin: 8px 0;
    }
    header > p:last-child {
        font-size: 14px;
        color: var(--muted-foreground);
    }
    .fixture-controls {
        display: grid;
        gap: 10px;
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: var(--card);
        font-size: 12px;
    }
    label {
        display: grid;
        gap: 4px;
    }
    select {
        width: 100%;
        min-width: 0;
        border: 1px solid var(--border);
        background: var(--background);
        border-radius: 8px;
    }
    .check {
        display: flex;
        align-items: center;
    }
    button {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px;
    }
    .fixture-trip {
        margin-top: 24px;
        padding: 20px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
    }
    .fixture-status {
        display: inline-block;
        margin-top: 16px;
        color: var(--primary);
        font-size: 12px;
    }
    .fixture-end {
        margin-top: 200px;
        margin-bottom: 12px;
        font-size: 14px;
        color: var(--muted-foreground);
    }
</style>
