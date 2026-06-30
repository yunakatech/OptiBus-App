<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Ops Master',
                href: '/admin-ops/customer-bagasi',
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { CardContent } from '@/components/ui/card';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { confirmAndRun, runWithFeedback } from '@/lib/action-feedback';
    import {
        formatCurrencyDisplay,
        formatCurrencyInput,
        parseCurrencyInput,
    } from '@/lib/currency';
    import { getUiPreference } from '@/lib/ui-preferences';

    type TabName = 'customer-bagasi' | 'customer-charter' | 'rute-carter';
    type ViewMode = 'data' | 'form';
    type Pagination = {
        page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    type BagasiCustomer = {
        id: number;
        nama: string;
        no_hp: string;
        alamat: string | null;
        tipe: string | null;
    };
    type CharterCustomer = {
        id: number;
        nama: string;
        no_hp: string;
        alamat: string | null;
        company: string | null;
    };
    type CarterRoute = {
        id: number;
        name: string;
        origin: string | null;
        destination: string | null;
        duration: string | null;
        rental_price: number;
        bop_price: number;
        notes: string | null;
        created_at: string | null;
    };
    type MasterDataPayload = {
        tab: TabName;
        customers?: BagasiCustomer[] | CharterCustomer[];
        routes?: CarterRoute[];
        pagination?: Pagination;
    };

    let {
        initialTab = null,
        lockedMenuView: lockedFromServer = false,
        masterData = null,
    }: {
        initialTab?: TabName | null;
        lockedMenuView?: boolean;
        masterData?: MasterDataPayload | null;
    } = $props();

    let activeTab = $state<TabName>('customer-bagasi');
    let activeMode = $state<ViewMode>('data');
    let lockedMenuView = $state(false);
    let busy = $state(false);
    let error = $state('');
    let message = $state('');
    let activeSubmitKey = $state('');
    let pendingDeleteKey = $state('');

    const setSubmitKey = (key: string) => {
        activeSubmitKey = key;
    };

    const clearSubmitKey = (key: string) => {
        if (activeSubmitKey === key) {
            activeSubmitKey = '';
        }
    };

    const isSubmitActive = (key: string) => activeSubmitKey === key;

    let bagasiCustomers = $state<BagasiCustomer[]>([]);
    let charterCustomers = $state<CharterCustomer[]>([]);
    let carterRoutes = $state<CarterRoute[]>([]);

    const defaultMasterItemsPerPage = Math.max(
        1,
        Number(getUiPreference('itemsPerPage', 20) ?? 20) || 20,
    );

    let bagasiMeta = $state<Pagination>({
        page: 1,
        per_page: defaultMasterItemsPerPage,
        total: 0,
        last_page: 1,
    });
    let charterMeta = $state<Pagination>({
        page: 1,
        per_page: defaultMasterItemsPerPage,
        total: 0,
        last_page: 1,
    });
    let carterRouteMeta = $state<Pagination>({
        page: 1,
        per_page: defaultMasterItemsPerPage,
        total: 0,
        last_page: 1,
    });

    let bagasiQ = $state('');
    let charterQ = $state('');
    let carterRouteQ = $state('');
    let masterFiltersExpanded = $state(false);

    let bagasiForm = $state({
        id: 0,
        nama: '',
        no_hp: '',
        alamat: '',
        tipe: 'pengirim',
    });
    let charterForm = $state({
        id: 0,
        nama: '',
        no_hp: '',
        alamat: '',
        company: '',
    });
    const masterCarterServiceOptions = [
        'DROPOFF',
        'FULLDAY',
        'HALFDAY',
        '2D1N',
        '3D2N',
        '4D3N',
        '5D4N',
    ];
    const defaultMasterCarterService = masterCarterServiceOptions[0];
    const newCarterRouteForm = () => ({
        id: 0,
        name: '',
        origin: '',
        destination: '',
        duration: defaultMasterCarterService,
        rental_price: 0,
        bop_price: 0,
        notes: '',
    });
    let carterRouteForm = $state(newCarterRouteForm());

    const masterTabs: TabName[] = [
        'customer-bagasi',
        'customer-charter',
        'rute-carter',
    ];
    const masterTabTitle = (tab: TabName) => {
        if (tab === 'customer-bagasi') {
            return 'Bagasi';
        }

        if (tab === 'customer-charter') {
            return 'Carter';
        }

        return 'Rute Carter';
    };

    const isMasterTab = (value: string | null): value is TabName => {
        return value !== null && masterTabs.includes(value as TabName);
    };

    const syncTabQuery = (tab: TabName) => {
        if (typeof window === 'undefined') {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState(
            {},
            '',
            `${url.pathname}?${url.searchParams.toString()}`,
        );
    };

    const usesInertiaMasterData = () =>
        lockedMenuView && masterTabs.includes(activeTab);
    const masterQueryString = (page: number) => {
        const q = new URLSearchParams();
        const meta =
            activeTab === 'customer-bagasi'
                ? bagasiMeta
                : activeTab === 'customer-charter'
                  ? charterMeta
                  : carterRouteMeta;
        const query =
            activeTab === 'customer-bagasi'
                ? bagasiQ
                : activeTab === 'customer-charter'
                  ? charterQ
                  : carterRouteQ;

        q.set('page', String(page));
        q.set('per_page', String(meta.per_page));

        if (query.trim() !== '') {
            q.set('q', query.trim());
        }

        return q.toString();
    };
    const reloadMasterDataWithInertia = (page: number) => {
        if (typeof window === 'undefined' || !usesInertiaMasterData()) {
            return false;
        }

        busy = true;
        error = '';

        router.get(
            window.location.pathname,
            Object.fromEntries(new URLSearchParams(masterQueryString(page))),
            {
                only: ['masterData'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onError: () => {
                    error = 'Gagal memuat data terbaru.';
                },
                onFinish: () => {
                    busy = false;
                },
            },
        );

        return true;
    };

    const csrfToken = () =>
        (
            document.querySelector(
                'meta[name="csrf-token"]',
            ) as HTMLMetaElement | null
        )?.content ?? '';

    const api = async (
        method: 'GET' | 'POST' | 'DELETE',
        url: string,
        body?: Record<string, unknown>,
    ) => {
        const res = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        const json = await res.json().catch(() => ({}));

        if (!res.ok || json.success === false) {
            throw new Error(json.error || `Request gagal (${res.status})`);
        }

        return json;
    };

    const loadBagasiCustomers = async (page = 1) => {
        const qp = new URLSearchParams();
        qp.set('page', String(page));

        if (bagasiQ.trim() !== '') {
            qp.set('q', bagasiQ.trim());
        }

        const res = await api(
            'GET',
            `/api/admin/customer-bagasi?${qp.toString()}`,
        );
        bagasiCustomers = res.customers ?? [];
        bagasiMeta = res.pagination ?? bagasiMeta;
    };

    const loadCharterCustomers = async (page = 1) => {
        const qp = new URLSearchParams();
        qp.set('page', String(page));

        if (charterQ.trim() !== '') {
            qp.set('q', charterQ.trim());
        }

        const res = await api(
            'GET',
            `/api/admin/customer-charter?${qp.toString()}`,
        );
        charterCustomers = res.customers ?? [];
        charterMeta = res.pagination ?? charterMeta;
    };

    const loadCarterRoutes = async (page = 1) => {
        const qp = new URLSearchParams();
        qp.set('page', String(page));

        if (carterRouteQ.trim() !== '') {
            qp.set('q', carterRouteQ.trim());
        }

        const res = await api(
            'GET',
            `/api/admin/charter-routes?${qp.toString()}`,
        );
        carterRoutes = res.routes ?? [];
        carterRouteMeta = res.pagination ?? carterRouteMeta;
    };

    $effect(() => {
        const payload = masterData;
        if (!payload) {
            return;
        }

        if (payload.tab === 'customer-bagasi') {
            bagasiCustomers = Array.isArray(payload.customers)
                ? (payload.customers as BagasiCustomer[])
                : [];
            bagasiMeta = payload.pagination ?? bagasiMeta;
        } else if (payload.tab === 'customer-charter') {
            charterCustomers = Array.isArray(payload.customers)
                ? (payload.customers as CharterCustomer[])
                : [];
            charterMeta = payload.pagination ?? charterMeta;
        } else {
            carterRoutes = Array.isArray(payload.routes) ? payload.routes : [];
            carterRouteMeta = payload.pagination ?? carterRouteMeta;
        }

        busy = false;
    });

    const loadActiveTab = async () => {
        busy = true;
        error = '';

        try {
            if (activeTab === 'customer-bagasi') {
                await loadBagasiCustomers(bagasiMeta.page);
            }

            if (activeTab === 'customer-charter') {
                await loadCharterCustomers(charterMeta.page);
            }

            if (activeTab === 'rute-carter') {
                await loadCarterRoutes(carterRouteMeta.page);
            }
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data.';
        } finally {
            busy = false;
        }
    };

    const applySearch = async (tab: TabName) => {
        if (tab === activeTab && reloadMasterDataWithInertia(1)) {
            return;
        }

        if (tab === 'customer-bagasi') {
            await loadBagasiCustomers(1);

            return;
        }

        if (tab === 'customer-charter') {
            await loadCharterCustomers(1);

            return;
        }

        await loadCarterRoutes(1);
    };

    const setTab = async (tab: TabName) => {
        activeTab = tab;
        activeMode = 'data';
        masterFiltersExpanded = false;
        syncTabQuery(tab);
        message = '';
        error = '';
        await loadActiveTab();
    };

    const setFormMode = (mode: ViewMode) => {
        activeMode = mode;
    };
    const openCreateMasterForm = () => {
        message = '';
        error = '';

        if (activeTab === 'customer-bagasi') {
            bagasiForm = {
                id: 0,
                nama: '',
                no_hp: '',
                alamat: '',
                tipe: 'pengirim',
            };
        }

        if (activeTab === 'customer-charter') {
            charterForm = {
                id: 0,
                nama: '',
                no_hp: '',
                alamat: '',
                company: '',
            };
        }

        if (activeTab === 'rute-carter') {
            carterRouteForm = newCarterRouteForm();
        }

        activeMode = 'form';
    };

    const saveBagasiCustomer = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('bagasi-customer');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/customer-bagasi', bagasiForm);
                },
                {
                    loadingMessage: bagasiForm.id
                        ? 'Memperbarui customer bagasi...'
                        : 'Menyimpan customer bagasi...',
                    successMessage: bagasiForm.id
                        ? 'Customer bagasi berhasil diperbarui.'
                        : 'Customer bagasi berhasil dibuat.',
                    errorMessage: 'Gagal simpan customer bagasi.',
                },
            );
            message = bagasiForm.id
                ? 'Customer bagasi updated.'
                : 'Customer bagasi created.';
            bagasiForm = {
                id: 0,
                nama: '',
                no_hp: '',
                alamat: '',
                tipe: 'pengirim',
            };
            await loadBagasiCustomers(bagasiMeta.page);
            activeMode = 'data';
        } catch (e) {
            error =
                e instanceof Error
                    ? e.message
                    : 'Gagal simpan customer bagasi.';
        } finally {
            clearSubmitKey('bagasi-customer');
        }
    };

    const saveCharterCustomer = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('charter-customer');

        try {
            await runWithFeedback(
                async () => {
                    await api(
                        'POST',
                        '/api/admin/customer-charter',
                        charterForm,
                    );
                },
                {
                    loadingMessage: charterForm.id
                        ? 'Memperbarui customer carter...'
                        : 'Menyimpan customer carter...',
                    successMessage: charterForm.id
                        ? 'Customer carter berhasil diperbarui.'
                        : 'Customer carter berhasil dibuat.',
                    errorMessage: 'Gagal simpan customer charter.',
                },
            );
            message = charterForm.id
                ? 'Customer charter updated.'
                : 'Customer charter created.';
            charterForm = {
                id: 0,
                nama: '',
                no_hp: '',
                alamat: '',
                company: '',
            };
            await loadCharterCustomers(charterMeta.page);
            activeMode = 'data';
        } catch (e) {
            error =
                e instanceof Error
                    ? e.message
                    : 'Gagal simpan customer charter.';
        } finally {
            clearSubmitKey('charter-customer');
        }
    };

    const saveCarterRoute = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('carter-route');

        try {
            await runWithFeedback(
                async () => {
                    await api('POST', '/api/admin/charter-routes', {
                        id: carterRouteForm.id || undefined,
                        name: carterRouteForm.name,
                        origin: carterRouteForm.origin,
                        destination: carterRouteForm.destination,
                        duration: carterRouteForm.duration,
                        rental_price: parseCurrencyInput(
                            carterRouteForm.rental_price,
                        ),
                        bop_price: parseCurrencyInput(
                            carterRouteForm.bop_price,
                        ),
                        notes: carterRouteForm.notes,
                    });
                },
                {
                    loadingMessage: carterRouteForm.id
                        ? 'Memperbarui master carter...'
                        : 'Menyimpan master carter...',
                    successMessage: carterRouteForm.id
                        ? 'Master carter berhasil diperbarui.'
                        : 'Master carter berhasil dibuat.',
                    errorMessage: 'Gagal simpan rute carter.',
                },
            );
            message = carterRouteForm.id
                ? 'Rute carter updated.'
                : 'Rute carter created.';
            carterRouteForm = newCarterRouteForm();
            await loadCarterRoutes(carterRouteMeta.page);
            activeMode = 'data';
        } catch (e) {
            error =
                e instanceof Error ? e.message : 'Gagal simpan rute carter.';
        } finally {
            clearSubmitKey('carter-route');
        }
    };

    const removeRow = async (url: string, okMessage: string) => {
        message = '';
        error = '';
        pendingDeleteKey = url;

        try {
            const result = await confirmAndRun(
                'Yakin ingin menghapus data ini?',
                async () => {
                    await api('DELETE', url);
                },
                {
                    loadingMessage: 'Menghapus data...',
                    successMessage: okMessage,
                    errorMessage: 'Gagal hapus data.',
                },
            );

            if (result === null) {
                return;
            }

            message = okMessage;
            await loadActiveTab();
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal hapus data.';
        } finally {
            pendingDeleteKey = '';
        }
    };

    const jumpPage = async (tab: TabName, page: number) => {
        if (page < 1) {
            return;
        }

        if (tab === activeTab && reloadMasterDataWithInertia(page)) {
            return;
        }

        if (tab === 'customer-bagasi') {
            await loadBagasiCustomers(page);

            return;
        }

        if (tab === 'customer-charter') {
            await loadCharterCustomers(page);

            return;
        }

        if (tab === 'rute-carter') {
            await loadCarterRoutes(page);
        }
    };

    onMount(async () => {
        if (lockedFromServer) {
            lockedMenuView = true;
        }

        if (isMasterTab(initialTab)) {
            activeTab = initialTab;
            lockedMenuView = true;
        }

        if (typeof window !== 'undefined') {
            const params = new URLSearchParams(window.location.search);
            const routeTab = params.get('tab');

            if (isMasterTab(routeTab)) {
                activeTab = routeTab;
                lockedMenuView = true;
            }

            const query = params.get('q') ?? '';
            if (activeTab === 'customer-bagasi') {
                bagasiQ = query;
            } else if (activeTab === 'customer-charter') {
                charterQ = query;
            } else {
                carterRouteQ = query;
            }
        }

        busy = !usesInertiaMasterData() || !masterData;

        try {
            if (!usesInertiaMasterData()) {
                await loadActiveTab();
            }
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data awal.';
        } finally {
            if (!usesInertiaMasterData() || masterData) {
                busy = false;
            }
        }
    });
</script>

<AppHead title={masterTabTitle(activeTab)} />

<div class="space-y-4 p-4">
    <section
        class="overflow-hidden rounded-2xl border border-border/70 bg-background shadow-sm"
    >
        <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-border/70 px-4 py-3"
        >
            <h1 class="text-lg font-semibold tracking-tight text-foreground">
                {masterTabTitle(activeTab)}
            </h1>
            {#if !lockedMenuView}
                <div class="flex flex-wrap items-center gap-2">
                    {#if activeMode === 'data' && activeTab !== 'rute-carter'}
                        <Button
                            type="button"
                            size="sm"
                            class="h-10 w-full justify-center rounded-2xl px-4 sm:h-9 sm:w-auto sm:rounded-full"
                            onclick={openCreateMasterForm}
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Data Baru
                        </Button>
                    {/if}
                    <Button
                        type="button"
                        size="sm"
                        variant={activeTab === 'customer-bagasi'
                            ? 'default'
                            : 'outline'}
                        class="rounded-full px-4"
                        onclick={() => void setTab('customer-bagasi')}
                        >Bagasi</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        variant={activeTab === 'customer-charter'
                            ? 'default'
                            : 'outline'}
                        class="rounded-full px-4"
                        onclick={() => void setTab('customer-charter')}
                        >Carter</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        variant={activeTab === 'rute-carter'
                            ? 'default'
                            : 'outline'}
                        class="rounded-full px-4"
                        onclick={() => void setTab('rute-carter')}
                        >Rute Carter</Button
                    >
                </div>
            {/if}
        </div>

        <CardContent class="space-y-4 p-4">
            {#if busy}<p class="text-sm text-muted-foreground">
                    Memuat data...
                </p>{/if}
            {#if error}<p class="text-sm text-red-600">{error}</p>{/if}
            {#if message}<p class="text-sm text-emerald-600">{message}</p>{/if}

            {#if activeTab === 'customer-bagasi' && !busy}
                {#if activeMode === 'form'}
                    <div
                        class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            {bagasiForm.id
                                ? 'Halaman Edit Customer Bagasi'
                                : 'Halaman Tambah Customer Bagasi Baru'}
                        </p>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-lg text-xs"
                            onclick={() => setFormMode('data')}
                            >Kembali ke Data</Button
                        >
                    </div>
                    <form
                        class="grid gap-3 md:grid-cols-4"
                        onsubmit={saveBagasiCustomer}
                    >
                        <Input
                            placeholder="Nama"
                            bind:value={bagasiForm.nama}
                            required
                        />
                        <Input
                            placeholder="No HP"
                            bind:value={bagasiForm.no_hp}
                            required
                        />
                        <Input
                            placeholder="Alamat"
                            bind:value={bagasiForm.alamat}
                        />
                        <select
                            class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                            bind:value={bagasiForm.tipe}
                        >
                            <option value="pengirim">Pengirim</option>
                            <option value="penerima">Penerima</option>
                            <option value="keduanya">Keduanya</option>
                        </select>
                        <div>
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('bagasi-customer')}
                                loadingText={bagasiForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                                >{bagasiForm.id
                                    ? 'Update'
                                    : 'Create'}</LoadingButton
                            >
                        </div>
                    </form>
                {:else}
                    <div class="flex justify-end md:hidden">
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-lg text-xs"
                            onclick={() =>
                                (masterFiltersExpanded =
                                    !masterFiltersExpanded)}
                            aria-expanded={masterFiltersExpanded}
                        >
                            {masterFiltersExpanded
                                ? 'Sembunyikan Filter'
                                : 'Tampilkan Filter'}
                        </Button>
                    </div>
                    <div
                        class={masterFiltersExpanded
                            ? 'mt-2 rounded-2xl border border-border/70 bg-muted/20 px-3 py-2 shadow-sm md:max-w-sm'
                            : 'mt-2 hidden md:block md:max-w-sm'}
                    >
                        <div
                            class="mb-2 flex items-center justify-between gap-2"
                        >
                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                            >
                                Filter Data
                            </p>
                            <span
                                class="rounded-full border border-border/60 bg-background px-2 py-0.5 text-[9px] font-medium text-muted-foreground"
                            >
                                Bagasi
                            </span>
                        </div>
                        <div
                            class="flex flex-col gap-2 md:flex-row md:items-center md:gap-2.5"
                        >
                            <Input
                                placeholder="Cari nama/no hp/alamat"
                                bind:value={bagasiQ}
                                class="h-8 text-[11px] md:flex-1 md:min-w-0"
                            />
                            <Button
                                type="button"
                                class="h-8 px-3 text-[11px] md:shrink-0"
                                onclick={() =>
                                    void applySearch('customer-bagasi')}
                                >Search</Button
                            >
                        </div>
                    </div>
                    <div class="grid gap-3 md:hidden">
                        {#each bagasiCustomers as row (row.id)}
                            <article
                                class="rounded-[24px] border border-border/80 bg-card/95 p-3 shadow-sm"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-foreground"
                                        >
                                            {row.nama}
                                        </p>
                                        <p
                                            class="mt-0.5 truncate text-xs text-muted-foreground"
                                        >
                                            {row.no_hp}
                                        </p>
                                    </div>
                                    <div
                                        class="flex shrink-0 items-center gap-1.5"
                                    >
                                        <span
                                            class="rounded-full border border-cyan-200 bg-cyan-50 px-2 py-0.5 text-[10px] font-semibold text-cyan-700 dark:border-cyan-800 dark:bg-cyan-950/40 dark:text-cyan-300"
                                        >
                                            {row.tipe ?? '-'}
                                        </span>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8 rounded-full border border-border/70"
                                                >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                    <span class="sr-only"
                                                        >Aksi customer bagasi</span
                                                    >
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                sideOffset={8}
                                                class="z-[120] w-44"
                                            >
                                                <DropdownMenuItem
                                                    onclick={() => {
                                                        bagasiForm = {
                                                            id: row.id,
                                                            nama: row.nama,
                                                            no_hp: row.no_hp,
                                                            alamat:
                                                                row.alamat ??
                                                                '',
                                                            tipe:
                                                                row.tipe ??
                                                                'pengirim',
                                                        };
                                                        setFormMode('form');
                                                    }}
                                                >
                                                    <Pencil
                                                        class="mr-2 h-3.5 w-3.5"
                                                    />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onclick={() =>
                                                        void removeRow(
                                                            `/api/admin/customer-bagasi/${row.id}`,
                                                            'Customer bagasi deleted.',
                                                        )}
                                                    disabled={pendingDeleteKey ===
                                                        `/api/admin/customer-bagasi/${row.id}`}
                                                >
                                                    <Trash2
                                                        class="mr-2 h-3.5 w-3.5"
                                                    />
                                                    {pendingDeleteKey ===
                                                    `/api/admin/customer-bagasi/${row.id}`
                                                        ? 'Menghapus...'
                                                        : 'Hapus'}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                                <div
                                    class="mt-3 rounded-xl bg-muted/30 px-3 py-2 text-xs"
                                >
                                    <p
                                        class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Alamat
                                    </p>
                                    <p
                                        class="mt-1 break-words font-medium text-foreground"
                                    >
                                        {row.alamat ?? '-'}
                                    </p>
                                </div>
                            </article>
                        {/each}
                    </div>
                    <div
                        class="hidden overflow-x-auto rounded-md border md:block"
                    >
                        <table class="min-w-full text-sm">
                            <thead class="bg-muted/50"
                                ><tr
                                    ><th class="px-3 py-2 text-left">Nama</th
                                    ><th class="px-3 py-2 text-left">No HP</th
                                    ><th class="px-3 py-2 text-left">Tipe</th
                                    ><th class="px-3 py-2 text-left">Aksi</th
                                    ></tr
                                ></thead
                            >
                            <tbody>
                                {#each bagasiCustomers as row (row.id)}
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{row.nama}</td>
                                        <td class="px-3 py-2">{row.no_hp}</td>
                                        <td class="px-3 py-2"
                                            >{row.tipe ?? '-'}</td
                                        >
                                        <td class="space-x-2 px-3 py-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onclick={() => {
                                                    bagasiForm = {
                                                        id: row.id,
                                                        nama: row.nama,
                                                        no_hp: row.no_hp,
                                                        alamat:
                                                            row.alamat ?? '',
                                                        tipe:
                                                            row.tipe ??
                                                            'pengirim',
                                                    };
                                                    setFormMode('form');
                                                }}>Edit</Button
                                            >
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                disabled={pendingDeleteKey ===
                                                    `/api/admin/customer-bagasi/${row.id}`}
                                                onclick={() =>
                                                    void removeRow(
                                                        `/api/admin/customer-bagasi/${row.id}`,
                                                        'Customer bagasi deleted.',
                                                    )}
                                                >{pendingDeleteKey ===
                                                `/api/admin/customer-bagasi/${row.id}`
                                                    ? 'Menghapus...'
                                                    : 'Delete'}</Button
                                            >
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Total: {bagasiMeta.total}
                        </p>
                        <div
                            class="flex items-center justify-between gap-2 sm:justify-end"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                disabled={bagasiMeta.page <= 1}
                                onclick={() =>
                                    void jumpPage(
                                        'customer-bagasi',
                                        bagasiMeta.page - 1,
                                    )}>Prev</Button
                            >
                            <span class="px-2 py-1 text-sm"
                                >{bagasiMeta.page} / {bagasiMeta.last_page}</span
                            >
                            <Button
                                type="button"
                                variant="outline"
                                disabled={bagasiMeta.page >=
                                    bagasiMeta.last_page}
                                onclick={() =>
                                    void jumpPage(
                                        'customer-bagasi',
                                        bagasiMeta.page + 1,
                                    )}>Next</Button
                            >
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'customer-charter' && !busy}
                {#if activeMode === 'form'}
                    <div
                        class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            {charterForm.id
                                ? 'Halaman Edit Customer Carter'
                                : 'Halaman Tambah Customer Carter Baru'}
                        </p>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-lg text-xs"
                            onclick={() => setFormMode('data')}
                            >Kembali ke Data</Button
                        >
                    </div>
                    <form
                        class="grid gap-3 md:grid-cols-4"
                        onsubmit={saveCharterCustomer}
                    >
                        <Input
                            placeholder="Nama"
                            bind:value={charterForm.nama}
                            required
                        />
                        <Input
                            placeholder="No HP"
                            bind:value={charterForm.no_hp}
                            required
                        />
                        <Input
                            placeholder="Company"
                            bind:value={charterForm.company}
                        />
                        <Input
                            placeholder="Alamat"
                            bind:value={charterForm.alamat}
                        />
                        <div>
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('charter-customer')}
                                loadingText={charterForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                                >{charterForm.id
                                    ? 'Update'
                                    : 'Create'}</LoadingButton
                            >
                        </div>
                    </form>
                {:else}
                    <div class="flex justify-end md:hidden">
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-lg text-xs"
                            onclick={() =>
                                (masterFiltersExpanded =
                                    !masterFiltersExpanded)}
                            aria-expanded={masterFiltersExpanded}
                        >
                            {masterFiltersExpanded
                                ? 'Sembunyikan Filter'
                                : 'Tampilkan Filter'}
                        </Button>
                    </div>
                    <div
                        class={masterFiltersExpanded
                            ? 'mt-2 rounded-2xl border border-border/70 bg-muted/20 px-3 py-2 shadow-sm md:max-w-sm'
                            : 'mt-2 hidden md:block md:max-w-sm'}
                    >
                        <div
                            class="mb-2 flex items-center justify-between gap-2"
                        >
                            <p
                                class="text-[9px] font-semibold uppercase tracking-[0.2em] text-muted-foreground"
                            >
                                Filter Data
                            </p>
                            <span
                                class="rounded-full border border-border/60 bg-background px-2 py-0.5 text-[9px] font-medium text-muted-foreground"
                            >
                                Carter
                            </span>
                        </div>
                        <div
                            class="flex flex-col gap-2 md:flex-row md:items-center md:gap-2.5"
                        >
                            <Input
                                placeholder="Cari nama/no hp/company"
                                bind:value={charterQ}
                                class="h-8 text-[11px] md:flex-1 md:min-w-0"
                            />
                            <Button
                                type="button"
                                class="h-8 px-3 text-[11px] md:shrink-0"
                                onclick={() =>
                                    void applySearch('customer-charter')}
                                >Search</Button
                            >
                        </div>
                    </div>
                    <div class="grid gap-3 md:hidden">
                        {#each charterCustomers as row (row.id)}
                            <article
                                class="rounded-[24px] border border-border/80 bg-card/95 p-3 shadow-sm"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-foreground"
                                        >
                                            {row.nama}
                                        </p>
                                        <p
                                            class="mt-0.5 truncate text-xs text-muted-foreground"
                                        >
                                            {row.no_hp}
                                        </p>
                                    </div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 shrink-0 rounded-full border border-border/70"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4"
                                                />
                                                <span class="sr-only"
                                                    >Aksi customer carter</span
                                                >
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            sideOffset={8}
                                            class="z-[120] w-44"
                                        >
                                            <DropdownMenuItem
                                                onclick={() => {
                                                    charterForm = {
                                                        id: row.id,
                                                        nama: row.nama,
                                                        no_hp: row.no_hp,
                                                        alamat:
                                                            row.alamat ?? '',
                                                        company:
                                                            row.company ?? '',
                                                    };
                                                    setFormMode('form');
                                                }}
                                            >
                                                <Pencil
                                                    class="mr-2 h-3.5 w-3.5"
                                                />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                onclick={() =>
                                                    void removeRow(
                                                        `/api/admin/customer-charter/${row.id}`,
                                                        'Customer charter deleted.',
                                                    )}
                                                disabled={pendingDeleteKey ===
                                                    `/api/admin/customer-charter/${row.id}`}
                                            >
                                                <Trash2
                                                    class="mr-2 h-3.5 w-3.5"
                                                />
                                                {pendingDeleteKey ===
                                                `/api/admin/customer-charter/${row.id}`
                                                    ? 'Menghapus...'
                                                    : 'Hapus'}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                                <div class="mt-3 grid gap-2 text-xs">
                                    <div
                                        class="rounded-xl bg-muted/30 px-3 py-2"
                                    >
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                        >
                                            Company
                                        </p>
                                        <p
                                            class="mt-1 break-words font-medium text-foreground"
                                        >
                                            {row.company ?? '-'}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl bg-muted/30 px-3 py-2"
                                    >
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
                                        >
                                            Alamat
                                        </p>
                                        <p
                                            class="mt-1 break-words font-medium text-foreground"
                                        >
                                            {row.alamat ?? '-'}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        {/each}
                    </div>
                    <div
                        class="hidden overflow-x-auto rounded-md border md:block"
                    >
                        <table class="min-w-full text-sm">
                            <thead class="bg-muted/50"
                                ><tr
                                    ><th class="px-3 py-2 text-left">Nama</th
                                    ><th class="px-3 py-2 text-left">No HP</th
                                    ><th class="px-3 py-2 text-left">Company</th
                                    ><th class="px-3 py-2 text-left">Aksi</th
                                    ></tr
                                ></thead
                            >
                            <tbody>
                                {#each charterCustomers as row (row.id)}
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{row.nama}</td>
                                        <td class="px-3 py-2">{row.no_hp}</td>
                                        <td class="px-3 py-2"
                                            >{row.company ?? '-'}</td
                                        >
                                        <td class="space-x-2 px-3 py-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onclick={() => {
                                                    charterForm = {
                                                        id: row.id,
                                                        nama: row.nama,
                                                        no_hp: row.no_hp,
                                                        alamat:
                                                            row.alamat ?? '',
                                                        company:
                                                            row.company ?? '',
                                                    };
                                                    setFormMode('form');
                                                }}>Edit</Button
                                            >
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                disabled={pendingDeleteKey ===
                                                    `/api/admin/customer-charter/${row.id}`}
                                                onclick={() =>
                                                    void removeRow(
                                                        `/api/admin/customer-charter/${row.id}`,
                                                        'Customer charter deleted.',
                                                    )}
                                                >{pendingDeleteKey ===
                                                `/api/admin/customer-charter/${row.id}`
                                                    ? 'Menghapus...'
                                                    : 'Delete'}</Button
                                            >
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Total: {charterMeta.total}
                        </p>
                        <div
                            class="flex items-center justify-between gap-2 sm:justify-end"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                disabled={charterMeta.page <= 1}
                                onclick={() =>
                                    void jumpPage(
                                        'customer-charter',
                                        charterMeta.page - 1,
                                    )}>Prev</Button
                            >
                            <span class="px-2 py-1 text-sm"
                                >{charterMeta.page} / {charterMeta.last_page}</span
                            >
                            <Button
                                type="button"
                                variant="outline"
                                disabled={charterMeta.page >=
                                    charterMeta.last_page}
                                onclick={() =>
                                    void jumpPage(
                                        'customer-charter',
                                        charterMeta.page + 1,
                                    )}>Next</Button
                            >
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'rute-carter' && !busy}
                {#if activeMode === 'form'}
                    <div
                        class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2"
                    >
                        <p class="text-xs font-medium text-muted-foreground">
                            {carterRouteForm.id
                                ? 'Edit data rute Carter'
                                : 'Tambah data rute Carter'}
                        </p>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="h-8 rounded-lg text-xs"
                            onclick={() => setFormMode('data')}
                            >Kembali ke Data</Button
                        >
                    </div>
                    <form
                        class="overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm"
                        onsubmit={saveCarterRoute}
                    >
                        <div class="border-b border-border/70 px-4 py-3">
                            <h3
                                class="text-base font-semibold tracking-tight text-foreground"
                            >
                                Rute Carter
                            </h3>
                        </div>
                        <div
                            class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-4"
                        >
                            <div class="space-y-1.5 md:col-span-2">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-name">Nama Rute</label
                                >
                                <Input
                                    id="carter-route-name"
                                    class="rounded-xl"
                                    placeholder="Contoh: MAKASSAR - PINRANG"
                                    bind:value={carterRouteForm.name}
                                    required
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-origin">Asal</label
                                >
                                <Input
                                    id="carter-route-origin"
                                    class="rounded-xl"
                                    placeholder="Titik jemput"
                                    bind:value={carterRouteForm.origin}
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-destination">Tujuan</label
                                >
                                <Input
                                    id="carter-route-destination"
                                    class="rounded-xl"
                                    placeholder="Titik antar"
                                    bind:value={carterRouteForm.destination}
                                />
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-duration">Layanan</label
                                >
                                <select
                                    id="carter-route-duration"
                                    class="h-10 w-full rounded-xl border border-input bg-background px-3 text-sm shadow-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    bind:value={carterRouteForm.duration}
                                >
                                    {#if carterRouteForm.duration && !masterCarterServiceOptions.includes(carterRouteForm.duration)}
                                        <option value={carterRouteForm.duration}
                                            >{carterRouteForm.duration} (data lama)</option
                                        >
                                    {/if}
                                    {#each masterCarterServiceOptions as service (service)}
                                        <option value={service}
                                            >{service}</option
                                        >
                                    {/each}
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-rental"
                                    >Harga Rental</label
                                >
                                <Input
                                    id="carter-route-rental"
                                    class="rounded-xl"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="Rp 0"
                                    value={formatCurrencyInput(
                                        carterRouteForm.rental_price,
                                    )}
                                    oninput={(event) => {
                                        carterRouteForm.rental_price =
                                            parseCurrencyInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                    }}
                                />
                            </div>
                            <div class="space-y-1.5">
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-bop">BOP</label
                                >
                                <Input
                                    id="carter-route-bop"
                                    class="rounded-xl"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="Rp 0"
                                    value={formatCurrencyInput(
                                        carterRouteForm.bop_price,
                                    )}
                                    oninput={(event) => {
                                        carterRouteForm.bop_price =
                                            parseCurrencyInput(
                                                (
                                                    event.currentTarget as HTMLInputElement
                                                ).value,
                                            );
                                    }}
                                />
                            </div>
                            <div
                                class="space-y-1.5 md:col-span-2 xl:col-span-4"
                            >
                                <label
                                    class="text-xs font-medium text-muted-foreground"
                                    for="carter-route-notes">Catatan</label
                                >
                                <textarea
                                    id="carter-route-notes"
                                    rows="3"
                                    class="min-h-24 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                    placeholder="Catatan tambahan"
                                    bind:value={carterRouteForm.notes}
                                ></textarea>
                            </div>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-2 border-t border-border/70 px-4 py-3"
                        >
                            <LoadingButton
                                type="submit"
                                loading={isSubmitActive('carter-route')}
                                loadingText={carterRouteForm.id
                                    ? 'Menyimpan...'
                                    : 'Membuat...'}
                                >{carterRouteForm.id
                                    ? 'Simpan Perubahan'
                                    : 'Simpan Preset'}</LoadingButton
                            >
                            <Button
                                type="button"
                                variant="outline"
                                onclick={() =>
                                    (carterRouteForm = newCarterRouteForm())}
                                >Reset</Button
                            >
                        </div>
                    </form>
                {:else}
                    <div class="space-y-4">
                        <div
                            class="overflow-hidden rounded-2xl border border-border/70 bg-background shadow-sm"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 border-b border-border/70 px-4 py-3"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold tracking-tight text-foreground"
                                    >
                                        Rute Carter
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-end md:hidden">
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            class="h-8 rounded-lg text-xs"
                                            onclick={() =>
                                                (masterFiltersExpanded =
                                                    !masterFiltersExpanded)}
                                            aria-expanded={masterFiltersExpanded}
                                        >
                                            {masterFiltersExpanded
                                                ? 'Sembunyikan Filter'
                                                : 'Tampilkan Filter'}
                                        </Button>
                                    </div>
                                    <div
                                        class={masterFiltersExpanded
                                            ? 'grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end'
                                            : 'hidden md:grid md:gap-2 md:grid-cols-[minmax(0,1fr)_auto] md:items-end'}
                                    >
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground"
                                                for="carter-route-search"
                                                >Cari data</label
                                            >
                                            <Input
                                                id="carter-route-search"
                                                class="h-9 w-full rounded-xl bg-background"
                                                placeholder="Cari nama, asal, atau tujuan"
                                                bind:value={carterRouteQ}
                                            />
                                        </div>
                                        <div
                                            class="flex flex-wrap gap-2 sm:justify-end"
                                        >
                                            <Button
                                                type="button"
                                                class="h-9 rounded-xl px-4"
                                                onclick={() =>
                                                    void applySearch(
                                                        'rute-carter',
                                                    )}>Cari</Button
                                            >
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="h-9 rounded-xl px-4"
                                                onclick={openCreateMasterForm}
                                                >Tambah Data Baru</Button
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {#if carterRoutes.length > 0}
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead
                                            class="bg-muted/50 text-[11px] uppercase tracking-[0.16em] text-muted-foreground"
                                        >
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                    >Nama</th
                                                >
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                    >Asal</th
                                                >
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                    >Tujuan</th
                                                >
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                    >Layanan</th
                                                >
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                    >Harga</th
                                                >
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                    >Aksi</th
                                                >
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-border/60 bg-background/80"
                                        >
                                            {#each carterRoutes as row (row.id)}
                                                <tr
                                                    class="transition-colors hover:bg-muted/30"
                                                >
                                                    <td
                                                        class="px-4 py-4 align-top"
                                                    >
                                                        <p
                                                            class="font-semibold text-foreground"
                                                        >
                                                            {row.name}
                                                        </p>
                                                    </td>
                                                    <td
                                                        class="px-4 py-4 align-top"
                                                    >
                                                        <p
                                                            class="font-medium text-foreground"
                                                        >
                                                            {row.origin ?? '-'}
                                                        </p>
                                                    </td>
                                                    <td
                                                        class="px-4 py-4 align-top"
                                                    >
                                                        <p
                                                            class="font-medium text-foreground"
                                                        >
                                                            {row.destination ??
                                                                '-'}
                                                        </p>
                                                    </td>
                                                    <td
                                                        class="px-4 py-4 align-top"
                                                    >
                                                        <span
                                                            class="inline-flex items-center rounded-full border border-cyan-200/70 bg-cyan-50 px-3 py-1 text-xs font-semibold tracking-wide text-cyan-800"
                                                        >
                                                            {row.duration ??
                                                                '-'}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="px-4 py-4 align-top"
                                                    >
                                                        <div class="space-y-1">
                                                            <p
                                                                class="text-sm font-semibold text-foreground"
                                                            >
                                                                {formatCurrencyDisplay(
                                                                    row.rental_price,
                                                                )}
                                                            </p>
                                                            <p
                                                                class="text-xs text-muted-foreground"
                                                            >
                                                                BOP {formatCurrencyDisplay(
                                                                    row.bop_price,
                                                                )}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-4 py-4 align-top"
                                                    >
                                                        <div
                                                            class="flex flex-wrap gap-2"
                                                        >
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                class="rounded-full px-4"
                                                                onclick={() => {
                                                                    carterRouteForm =
                                                                        {
                                                                            id: row.id,
                                                                            name: row.name,
                                                                            origin:
                                                                                row.origin ??
                                                                                '',
                                                                            destination:
                                                                                row.destination ??
                                                                                '',
                                                                            duration:
                                                                                row.duration ??
                                                                                defaultMasterCarterService,
                                                                            rental_price:
                                                                                Number(
                                                                                    row.rental_price,
                                                                                ),
                                                                            bop_price:
                                                                                Number(
                                                                                    row.bop_price,
                                                                                ),
                                                                            notes:
                                                                                row.notes ??
                                                                                '',
                                                                        };
                                                                    setFormMode(
                                                                        'form',
                                                                    );
                                                                }}
                                                            >
                                                                Edit
                                                            </Button>
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                class="rounded-full px-4"
                                                                disabled={pendingDeleteKey ===
                                                                    `/api/admin/charter-routes/${row.id}`}
                                                                onclick={() =>
                                                                    void removeRow(
                                                                        `/api/admin/charter-routes/${row.id}`,
                                                                        'Rute carter deleted.',
                                                                    )}
                                                            >
                                                                {pendingDeleteKey ===
                                                                `/api/admin/charter-routes/${row.id}`
                                                                    ? 'Menghapus...'
                                                                    : 'Delete'}
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/each}
                                        </tbody>
                                    </table>
                                </div>
                            {:else}
                                <div
                                    class="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center"
                                >
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl border border-dashed border-border/70 bg-muted/30 text-lg"
                                    >
                                        *
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="text-base font-semibold text-foreground"
                                        >
                                            Belum ada preset master carter
                                        </p>
                                        <p
                                            class="max-w-md text-sm text-muted-foreground"
                                        >
                                            Tambahkan preset baru supaya form
                                            Carter bisa langsung mengisi rute,
                                            layanan, harga, dan BOP secara
                                            otomatis.
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="rounded-full px-4"
                                        onclick={openCreateMasterForm}
                                        >Tambah Data Baru</Button
                                    >
                                </div>
                            {/if}

                            <div
                                class="flex flex-col gap-3 border-t border-border/70 bg-muted/10 px-4 py-4 sm:flex-row sm:items-center sm:justify-between md:px-5"
                            >
                                <p class="text-sm text-muted-foreground">
                                    Menampilkan {carterRoutes.length} data pada halaman
                                    ini.
                                </p>
                                <div class="flex items-center gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="rounded-full"
                                        disabled={carterRouteMeta.page <= 1}
                                        onclick={() =>
                                            void jumpPage(
                                                'rute-carter',
                                                carterRouteMeta.page - 1,
                                            )}>Prev</Button
                                    >
                                    <span
                                        class="rounded-full border border-border/70 bg-background px-3 py-1 text-sm text-muted-foreground"
                                        >{carterRouteMeta.page} / {carterRouteMeta.last_page}</span
                                    >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="rounded-full"
                                        disabled={carterRouteMeta.page >=
                                            carterRouteMeta.last_page}
                                        onclick={() =>
                                            void jumpPage(
                                                'rute-carter',
                                                carterRouteMeta.page + 1,
                                            )}>Next</Button
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </CardContent>
    </section>
</div>
