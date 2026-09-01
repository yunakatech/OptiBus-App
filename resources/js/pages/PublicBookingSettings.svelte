<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Booking Online', href: '/settings/booking-online' },
        ],
    };
</script>

<script lang="ts">
    import { Check, Copy, ExternalLink, MessageCircle } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';

    type Settings = {
        tenant: {
            name: string;
            slug: string;
            phone: string;
            logo_url: string | null;
            status: string;
        } | null;
        enabled: boolean;
        entitled: boolean;
        url: string | null;
    };
    let { settings: initialSettings }: { settings: Settings } = $props();
    let settings = $state<Settings>({
        tenant: null,
        enabled: false,
        entitled: false,
        url: null,
    });
    let saving = $state(false);
    let copied = $state(false);
    let message = $state('');
    let error = $state('');

    onMount(() => {
        settings = initialSettings;
    });

    function csrfToken(): string {
        const token =
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '';

        return decodeURIComponent(token);
    }

    async function toggle() {
        saving = true;
        message = '';
        error = '';

        try {
            const response = await fetch('/api/admin/public-booking-settings', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ enabled: !settings.enabled }),
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.error ?? 'Pengaturan gagal disimpan.');
            }

            settings = payload.settings;
            message = settings.enabled
                ? 'Booking online aktif.'
                : 'Booking online dinonaktifkan.';
        } catch (cause) {
            error =
                cause instanceof Error
                    ? cause.message
                    : 'Pengaturan gagal disimpan.';
        } finally {
            saving = false;
        }
    }

    async function copyUrl() {
        if (!settings.url) {
            return;
        }

        await navigator.clipboard?.writeText(settings.url);
        copied = true;
        setTimeout(() => (copied = false), 1800);
    }
</script>

<AppHead title="Booking Online" />
<div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6">
    <div>
        <p
            class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600"
        >
            Kanal penjualan
        </p>
        <h1 class="mt-1 text-2xl font-black tracking-tight">Booking Online</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Bagikan halaman booking yang ringan dan nyaman untuk pelanggan
            mobile.
        </p>
    </div>
    {#if message}<div
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"
        >
            {message}
        </div>{/if}
    {#if error}<div
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
        >
            {error}
        </div>{/if}
    {#if !settings.entitled}
        <section
            class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900"
        >
            <p class="font-black">Fitur belum tersedia</p>
            <p class="mt-1">
                Aktifkan entitlement Halaman Booking Online pada paket tenant
                ini untuk menggunakan URL publik.
            </p>
        </section>
    {:else if settings.tenant}
        <section
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="bg-slate-950 p-6 text-white">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-emerald-300">
                            {settings.tenant.name}
                        </p>
                        <h2 class="mt-1 text-2xl font-black">
                            Terima booking dari publik
                        </h2>
                    </div>
                    <MessageCircle class="h-9 w-9 text-emerald-300" />
                </div>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                    Pelanggan memilih kursi dan mengirim request. Admin pool
                    tetap menyetujui sebelum booking resmi dibuat.
                </p>
            </div>
            <div class="space-y-5 p-6">
                <div
                    class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 p-4"
                >
                    <div>
                        <p class="font-black">Status halaman</p>
                        <p class="text-sm text-muted-foreground">
                            {settings.enabled
                                ? 'Pelanggan dapat mengakses URL booking.'
                                : 'URL publik sedang ditutup.'}
                        </p>
                    </div>
                    <button
                        type="button"
                        onclick={toggle}
                        disabled={saving}
                        aria-label="Aktifkan atau nonaktifkan booking online"
                        class:!bg-emerald-700={settings.enabled}
                        class="relative h-8 w-14 rounded-full bg-slate-300 transition disabled:opacity-50"
                        ><span
                            class:translate-x-6={settings.enabled}
                            class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white shadow transition"
                        ></span></button
                    >
                </div>
                <div>
                    <label
                        for="booking-url"
                        class="mb-2 block text-sm font-black"
                        >URL booking tenant</label
                    >
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input
                            id="booking-url"
                            readonly
                            value={settings.url ?? '-'}
                            class="h-11 min-w-0 flex-1 rounded-xl border-slate-200 bg-slate-50 px-3 text-sm"
                        />
                        <div class="flex gap-2">
                            <button
                                type="button"
                                onclick={copyUrl}
                                disabled={!settings.url}
                                class="flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-bold sm:flex-none"
                                >{#if copied}<Check
                                        class="h-4 w-4 text-emerald-600"
                                    /> Tersalin{:else}<Copy class="h-4 w-4" /> Salin{/if}</button
                            >{#if settings.enabled && settings.url}<a
                                    href={settings.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    class="flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-sm font-bold text-white"
                                    ><ExternalLink class="h-4 w-4" /> Buka</a
                                >{/if}
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">
                        URL memakai slug tenant dan dapat dibagikan melalui
                        WhatsApp, bio, atau website.
                    </p>
                </div>
            </div>
        </section>
    {/if}
</div>
