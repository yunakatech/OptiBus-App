<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Tarif Bagasi', href: '/settings/tarif-bagasi' },
        ],
    };
</script>

<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import {
        Archive,
        Boxes,
        PackagePlus,
        Route,
        Save,
        TriangleAlert,
    } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { hasPermission } from '@/lib/access';
    import { formatCurrencyInput, parseCurrencyInput } from '@/lib/currency';

    type RouteOption = { id: number; name: string };
    type SegmentOption = { id: number; route_id: number; rute: string };
    type Category = {
        id: number;
        name: string;
        description: string | null;
        is_active: boolean;
    };
    type RateRow = {
        service_id: number;
        name: string;
        description: string | null;
        unit_price: number;
        is_active: boolean;
        configured: boolean;
    };

    const permissions = $derived(page.props.auth?.permissions ?? []);
    const canManageCategory = $derived(
        hasPermission(permissions, 'luggage.category.manage'),
    );
    const canManageTariff = $derived(
        hasPermission(permissions, 'luggage.tariff.manage'),
    );
    let routes = $state<RouteOption[]>([]);
    let segments = $state<SegmentOption[]>([]);
    let categories = $state<Category[]>([]);
    let rates = $state<RateRow[]>([]);
    let selectedRouteId = $state(0);
    let selectedSegmentId = $state(0);
    let loading = $state(true);
    let loadingRates = $state(false);
    let savingRates = $state(false);
    let savingCategory = $state(false);
    let dirty = $state(false);
    let message = $state('');
    let error = $state('');
    let showCategoryForm = $state(false);
    let categoryForm = $state({
        id: 0,
        name: '',
        description: '',
        is_active: true,
    });

    const selectedRoute = $derived(
        routes.find((item) => item.id === selectedRouteId) ?? null,
    );
    const selectedSegment = $derived(
        segments.find((item) => item.id === selectedSegmentId) ?? null,
    );
    const configuredCount = $derived(
        rates.filter((item) => item.configured && item.unit_price > 0).length,
    );
    const zeroCount = $derived(
        rates.filter((item) => item.is_active && item.unit_price === 0).length,
    );
    const inactiveCount = $derived(
        rates.filter((item) => !item.is_active).length,
    );

    const api = async (
        method: 'GET' | 'POST' | 'PUT' | 'DELETE',
        url: string,
        body?: Record<string, unknown>,
    ) => {
        const token =
            document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                ?.content ?? '';
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: method === 'GET' ? undefined : JSON.stringify(body ?? {}),
        });
        const json = await response.json().catch(() => ({}));
        if (!response.ok || json.success === false) {
            const validation = Object.values(json.errors ?? {})
                .flat()
                .find(Boolean);
            throw new Error(
                String(
                    json.error ??
                        json.message ??
                        validation ??
                        `Request gagal (${response.status})`,
                ),
            );
        }
        return json;
    };

    const loadCategories = async () => {
        const data = await api('GET', '/api/admin/luggage-services');
        categories = (data.services ?? []).map((item: Category) => ({
            ...item,
            is_active: item.is_active !== false,
        }));
    };

    const chooseSegment = async (segmentId: number, ask = true) => {
        if (
            ask &&
            dirty &&
            !confirm('Perubahan tarif belum disimpan. Buang perubahan?')
        )
            return;
        selectedSegmentId = segmentId;
        rates = [];
        dirty = false;
        if (!segmentId || !selectedRouteId) return;
        loadingRates = true;
        error = '';
        try {
            const data = await api(
                'GET',
                `/api/admin/luggage-rates?route_id=${selectedRouteId}&segment_id=${segmentId}`,
            );
            rates = (data.rates ?? []).map((item: RateRow) => ({
                ...item,
                unit_price: Number(item.unit_price ?? 0),
            }));
        } catch (reason) {
            error =
                reason instanceof Error
                    ? reason.message
                    : 'Tarif segment gagal dimuat.';
        } finally {
            loadingRates = false;
        }
    };

    const chooseRoute = async (routeId: number, ask = true) => {
        if (
            ask &&
            dirty &&
            !confirm('Perubahan tarif belum disimpan. Buang perubahan?')
        )
            return;
        selectedRouteId = routeId;
        selectedSegmentId = 0;
        rates = [];
        dirty = false;
        if (!routeId) {
            segments = [];
            return;
        }
        const data = await api(
            'GET',
            `/api/admin/segments?route_id=${routeId}&per_page=100`,
        );
        segments = data.segments ?? [];
        if (segments[0]) await chooseSegment(segments[0].id, false);
    };

    const loadInitialData = async () => {
        loading = true;
        try {
            const [routeData] = await Promise.all([
                api('GET', '/api/admin/routes'),
                loadCategories(),
            ]);
            routes = routeData.routes ?? [];
            if (routes[0]) await chooseRoute(routes[0].id, false);
        } catch (reason) {
            error =
                reason instanceof Error
                    ? reason.message
                    : 'Data tarif bagasi gagal dimuat.';
        } finally {
            loading = false;
        }
    };

    const updateRate = (serviceId: number, value: string) => {
        rates = rates.map((item) =>
            item.service_id === serviceId
                ? { ...item, unit_price: parseCurrencyInput(value) }
                : item,
        );
        dirty = true;
    };

    const saveRates = async () => {
        if (!selectedSegmentId || !canManageTariff) return;
        savingRates = true;
        message = '';
        error = '';
        try {
            await api('PUT', `/api/admin/luggage-rates/${selectedSegmentId}`, {
                route_id: selectedRouteId,
                rates: rates.map((item) => ({
                    service_id: item.service_id,
                    unit_price: item.unit_price,
                    is_active: item.is_active,
                })),
            });
            dirty = false;
            message = 'Tarif segment berhasil disimpan.';
            await chooseSegment(selectedSegmentId, false);
        } catch (reason) {
            error =
                reason instanceof Error
                    ? reason.message
                    : 'Tarif gagal disimpan.';
        } finally {
            savingRates = false;
        }
    };

    const openCategory = (item?: Category) => {
        categoryForm = item
            ? {
                  id: item.id,
                  name: item.name,
                  description: item.description ?? '',
                  is_active: item.is_active,
              }
            : { id: 0, name: '', description: '', is_active: true };
        showCategoryForm = true;
    };

    const refreshCategoriesAndRates = async () => {
        await loadCategories();
        if (selectedSegmentId) await chooseSegment(selectedSegmentId, false);
    };

    const saveCategory = async (event: SubmitEvent) => {
        event.preventDefault();
        savingCategory = true;
        try {
            const payload: Record<string, unknown> = {
                name: categoryForm.name,
                description: categoryForm.description,
                is_active: categoryForm.is_active,
            };
            if (categoryForm.id > 0) payload.id = categoryForm.id;

            await api('POST', '/api/admin/luggage-services', payload);
            showCategoryForm = false;
            message = categoryForm.id
                ? 'Kategori barang diperbarui.'
                : 'Kategori tersedia di seluruh segment dengan tarif awal Rp0.';
            await refreshCategoriesAndRates();
        } catch (reason) {
            error =
                reason instanceof Error
                    ? reason.message
                    : 'Kategori gagal disimpan.';
        } finally {
            savingCategory = false;
        }
    };

    const archiveCategory = async (item: Category) => {
        if (
            !confirm(
                `${item.name} akan disembunyikan dari transaksi baru. Lanjut?`,
            )
        )
            return;
        try {
            await api('DELETE', `/api/admin/luggage-services/${item.id}`);
            message =
                'Kategori barang diarsipkan. Riwayat transaksi tetap aman.';
            await refreshCategoriesAndRates();
        } catch (reason) {
            error =
                reason instanceof Error
                    ? reason.message
                    : 'Kategori gagal diarsipkan.';
        }
    };

    onMount(() => void loadInitialData());
</script>

<AppHead title="Tarif Bagasi" />

<div class="mx-auto w-full max-w-[1500px] space-y-5 px-4 py-5 sm:px-6 lg:px-8">
    <section
        class="relative overflow-hidden rounded-3xl border border-emerald-900/10 bg-[#f3f0e8] px-5 py-6 shadow-[0_18px_60px_-38px_rgba(16,50,38,0.5)] sm:px-7"
    >
        <div
            class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-300/25 blur-3xl"
        ></div>
        <div
            class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
        >
            <div class="max-w-2xl">
                <p
                    class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-800"
                >
                    Rute → Segment → Kategori
                </p>
                <h1
                    class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl"
                >
                    Tarif bagasi yang mudah dilacak
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Pilih perjalanan, lalu isi harga per barang untuk setiap
                    kategori. Transaksi lama tidak ikut berubah.
                </p>
            </div>
            {#if canManageCategory}
                <Button class="rounded-xl" onclick={() => openCategory()}
                    ><PackagePlus class="mr-2 h-4 w-4" /> Tambah Kategori Barang</Button
                >
            {/if}
        </div>
    </section>

    {#if message}<div
            class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
        >
            {message}
        </div>{/if}
    {#if error}<div
            class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
        >
            {error}
        </div>{/if}

    <div class="grid min-w-0 gap-5 lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside
            class="space-y-4 rounded-3xl border bg-card p-4 shadow-sm lg:sticky lg:top-5 lg:self-start"
        >
            <div class="flex items-center gap-3 border-b pb-4">
                <span
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-100 text-emerald-800"
                    ><Route class="h-5 w-5" /></span
                >
                <div>
                    <p class="font-semibold">Pilih perjalanan</p>
                    <p class="text-xs text-muted-foreground">
                        Dua pilihan, lalu tarif siap diedit.
                    </p>
                </div>
            </div>
            <label class="block space-y-2 text-sm font-semibold">
                <span>1. Rute induk</span>
                <select
                    class="h-11 w-full rounded-xl border bg-background px-3 text-sm"
                    value={selectedRouteId}
                    onchange={(event) =>
                        void chooseRoute(Number(event.currentTarget.value))}
                >
                    <option value={0}>Pilih rute</option>
                    {#each routes as item (item.id)}<option value={item.id}
                            >{item.name}</option
                        >{/each}
                </select>
            </label>
            <label class="block space-y-2 text-sm font-semibold">
                <span>2. Segment perjalanan</span>
                <select
                    class="h-11 w-full rounded-xl border bg-background px-3 text-sm disabled:opacity-60"
                    value={selectedSegmentId}
                    disabled={!selectedRouteId || segments.length === 0}
                    onchange={(event) =>
                        void chooseSegment(Number(event.currentTarget.value))}
                >
                    <option value={0}>Pilih segment</option>
                    {#each segments as item (item.id)}<option value={item.id}
                            >{item.rute}</option
                        >{/each}
                </select>
            </label>
            {#if selectedRoute && segments.length === 0}
                <div
                    class="rounded-2xl bg-amber-50 p-3 text-xs leading-5 text-amber-900"
                >
                    Rute <strong>{selectedRoute.name}</strong> belum memiliki segment.
                    Buat dari menu Segment.
                </div>
            {/if}
            <div class="border-t pt-4">
                <p
                    class="mb-3 text-xs font-bold uppercase tracking-wider text-muted-foreground"
                >
                    Kategori tenant
                </p>
                <div class="max-h-64 space-y-2 overflow-y-auto pr-1">
                    {#each categories as item (item.id)}
                        <div
                            class="flex items-center justify-between gap-2 rounded-xl bg-muted/45 px-3 py-2"
                        >
                            <button
                                type="button"
                                class="min-w-0 flex-1 text-left"
                                disabled={!canManageCategory}
                                onclick={() => openCategory(item)}
                            >
                                <span class="block truncate text-sm font-medium"
                                    >{item.name}</span
                                >
                                <span class="text-[11px] text-muted-foreground"
                                    >{item.is_active
                                        ? 'Aktif di semua segment'
                                        : 'Diarsipkan'}</span
                                >
                            </button>
                            {#if item.is_active && canManageCategory}
                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-muted-foreground hover:bg-background hover:text-red-600"
                                    aria-label={`Arsipkan ${item.name}`}
                                    onclick={() => void archiveCategory(item)}
                                    ><Archive class="h-4 w-4" /></button
                                >
                            {/if}
                        </div>
                    {/each}
                </div>
            </div>
        </aside>

        <main class="min-w-0 space-y-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Tarif terisi</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">
                        {configuredCount}
                    </p>
                </div>
                <div class="rounded-2xl border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Masih Rp0</p>
                    <p class="mt-1 text-2xl font-bold text-amber-700">
                        {zeroCount}
                    </p>
                </div>
                <div class="rounded-2xl border bg-card p-4">
                    <p class="text-xs text-muted-foreground">
                        Kategori nonaktif
                    </p>
                    <p class="mt-1 text-2xl font-bold text-slate-500">
                        {inactiveCount}
                    </p>
                </div>
            </div>
            <section
                class="overflow-hidden rounded-3xl border bg-card shadow-sm"
            >
                <header
                    class="flex flex-col gap-3 border-b bg-muted/20 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-emerald-700"
                        >
                            Editor harga per barang
                        </p>
                        <h2 class="mt-1 text-lg font-semibold">
                            {selectedSegment?.rute ??
                                'Pilih segment untuk mulai'}
                        </h2>
                        {#if selectedRoute}<p
                                class="text-xs text-muted-foreground"
                            >
                                Rute {selectedRoute.name}
                            </p>{/if}
                    </div>
                    <LoadingButton
                        loading={savingRates}
                        loadingText="Menyimpan..."
                        disabled={!selectedSegmentId ||
                            !dirty ||
                            !canManageTariff}
                        class="rounded-xl"
                        onclick={() => void saveRates()}
                        ><Save class="mr-2 h-4 w-4" /> Simpan Semua Tarif</LoadingButton
                    >
                </header>
                {#if loading || loadingRates}
                    <div
                        class="grid min-h-72 place-items-center p-8 text-sm text-muted-foreground"
                    >
                        Memuat tarif bagasi...
                    </div>
                {:else if !selectedSegmentId}
                    <div
                        class="grid min-h-72 place-items-center p-8 text-center"
                    >
                        <div>
                            <Boxes
                                class="mx-auto h-10 w-10 text-muted-foreground/50"
                            />
                            <p class="mt-3 font-semibold">
                                Belum ada segment dipilih
                            </p>
                        </div>
                    </div>
                {:else if rates.length === 0}
                    <div
                        class="grid min-h-72 place-items-center p-8 text-sm text-muted-foreground"
                    >
                        Belum ada kategori barang.
                    </div>
                {:else}
                    <div class="divide-y">
                        {#each rates as item (item.service_id)}
                            <div
                                class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_220px] sm:items-center"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p class="font-semibold">{item.name}</p>
                                        {#if !item.is_active}<Badge
                                                variant="secondary"
                                                >Nonaktif</Badge
                                            >{:else if item.unit_price === 0}<Badge
                                                class="bg-amber-100 text-amber-800"
                                                >Rp0</Badge
                                            >{:else}<Badge
                                                class="bg-emerald-100 text-emerald-800"
                                                >Siap dipakai</Badge
                                            >{/if}
                                    </div>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {item.description ||
                                            'Harga berlaku untuk satu barang pada segment ini.'}
                                    </p>
                                </div>
                                <label class="relative block"
                                    ><span
                                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-muted-foreground"
                                        >Rp</span
                                    ><Input
                                        class="h-11 rounded-xl pl-10 text-right font-semibold tabular-nums"
                                        inputmode="numeric"
                                        disabled={!item.is_active ||
                                            !canManageTariff}
                                        value={formatCurrencyInput(
                                            item.unit_price,
                                        )}
                                        oninput={(event) =>
                                            updateRate(
                                                item.service_id,
                                                event.currentTarget.value,
                                            )}
                                    /></label
                                >
                            </div>
                        {/each}
                    </div>
                    {#if zeroCount > 0}<div
                            class="flex gap-3 border-t bg-amber-50 px-5 py-4 text-xs text-amber-900"
                        >
                            <TriangleAlert class="h-4 w-4 shrink-0" />Tarif Rp0
                            tetap dapat digunakan; operator akan melihat
                            peringatan.
                        </div>{/if}
                {/if}
            </section>
        </main>
    </div>
</div>

{#if showCategoryForm}
    <div
        class="fixed inset-0 z-[150] grid place-items-center bg-slate-950/45 p-4 backdrop-blur-sm"
    >
        <form
            class="w-full max-w-lg rounded-3xl border bg-background p-5 shadow-2xl sm:p-6"
            onsubmit={saveCategory}
        >
            <div class="flex items-start gap-3">
                <span
                    class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-100 text-emerald-800"
                    ><PackagePlus class="h-5 w-5" /></span
                >
                <div>
                    <h2 class="text-lg font-semibold">
                        {categoryForm.id
                            ? 'Edit kategori barang'
                            : 'Tambah kategori barang'}
                    </h2>
                    <p class="mt-1 text-xs leading-5 text-muted-foreground">
                        Kategori baru otomatis tersedia di seluruh segment
                        dengan tarif awal Rp0.
                    </p>
                </div>
            </div>
            <div class="mt-5 space-y-4">
                <label class="block space-y-1.5 text-sm font-medium"
                    >Nama kategori<Input
                        bind:value={categoryForm.name}
                        placeholder="Contoh: Koper, Kardus, Dokumen"
                        required
                    /></label
                >
                <label class="block space-y-1.5 text-sm font-medium"
                    >Keterangan<textarea
                        class="min-h-24 w-full rounded-xl border bg-background px-3 py-2 text-sm"
                        bind:value={categoryForm.description}
                        placeholder="Opsional, bantu operator mengenali barang"
                    ></textarea></label
                >
                {#if categoryForm.id}<label
                        class="flex items-center gap-3 rounded-xl border p-3 text-sm"
                        ><input
                            type="checkbox"
                            bind:checked={categoryForm.is_active}
                        /> Kategori aktif untuk transaksi baru</label
                    >{/if}
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <Button
                    type="button"
                    variant="outline"
                    class="rounded-xl"
                    onclick={() => (showCategoryForm = false)}>Batal</Button
                ><LoadingButton
                    type="submit"
                    loading={savingCategory}
                    loadingText="Menyimpan..."
                    class="rounded-xl">Simpan Kategori</LoadingButton
                >
            </div>
        </form>
    </div>
{/if}
