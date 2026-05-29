<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Ops Master',
                href: '/admin-ops/master',
            },
        ],
    };
</script>

<script lang="ts">
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { confirmAndRun, runWithFeedback } from '@/lib/action-feedback';

    type TabName = 'customer-bagasi' | 'customer-charter' | 'rute-carter';
    type ViewMode = 'data' | 'form';
    type Pagination = { page: number; per_page: number; total: number; last_page: number };
    type BagasiCustomer = { id: number; nama: string; no_hp: string; alamat: string | null; tipe: string | null };
    type CharterCustomer = { id: number; nama: string; no_hp: string; alamat: string | null; company: string | null };
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

    let {
        initialTab = null,
        lockedMenuView: lockedFromServer = false,
    }: {
        initialTab?: TabName | null;
        lockedMenuView?: boolean;
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

    let bagasiMeta = $state<Pagination>({ page: 1, per_page: 20, total: 0, last_page: 1 });
    let charterMeta = $state<Pagination>({ page: 1, per_page: 20, total: 0, last_page: 1 });
    let carterRouteMeta = $state<Pagination>({ page: 1, per_page: 20, total: 0, last_page: 1 });

    let bagasiQ = $state('');
    let charterQ = $state('');
    let carterRouteQ = $state('');

    let bagasiForm = $state({ id: 0, nama: '', no_hp: '', alamat: '', tipe: 'pengirim' });
    let charterForm = $state({ id: 0, nama: '', no_hp: '', alamat: '', company: '' });
    let carterRouteForm = $state({ id: 0, name: '', origin: '', destination: '', duration: 'Regular', rental_price: 0, bop_price: 0, notes: '' });

    const masterTabs: TabName[] = ['customer-bagasi', 'customer-charter', 'rute-carter'];
    const masterTabTitle = (tab: TabName) => {
        if (tab === 'customer-bagasi') {
return 'Bagasi';
}

        if (tab === 'customer-charter') {
return 'Carter';
}

        return 'Master Carter';
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
        window.history.replaceState({}, '', `${url.pathname}?${url.searchParams.toString()}`);
    };

    const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';

    const api = async (method: 'GET' | 'POST' | 'DELETE', url: string, body?: Record<string, unknown>) => {
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

        const res = await api('GET', `/api/admin/customer-bagasi?${qp.toString()}`);
        bagasiCustomers = res.customers ?? [];
        bagasiMeta = res.pagination ?? bagasiMeta;
    };

    const loadCharterCustomers = async (page = 1) => {
        const qp = new URLSearchParams();
        qp.set('page', String(page));

        if (charterQ.trim() !== '') {
qp.set('q', charterQ.trim());
}

        const res = await api('GET', `/api/admin/customer-charter?${qp.toString()}`);
        charterCustomers = res.customers ?? [];
        charterMeta = res.pagination ?? charterMeta;
    };

    const loadCarterRoutes = async (page = 1) => {
        const qp = new URLSearchParams();
        qp.set('page', String(page));

        if (carterRouteQ.trim() !== '') {
qp.set('q', carterRouteQ.trim());
}

        const res = await api('GET', `/api/admin/charter-routes?${qp.toString()}`);
        carterRoutes = res.routes ?? [];
        carterRouteMeta = res.pagination ?? carterRouteMeta;
    };

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

    const setTab = async (tab: TabName) => {
        activeTab = tab;
        activeMode = 'data';
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
bagasiForm = { id: 0, nama: '', no_hp: '', alamat: '', tipe: 'pengirim' };
}

        if (activeTab === 'customer-charter') {
charterForm = { id: 0, nama: '', no_hp: '', alamat: '', company: '' };
}

        if (activeTab === 'rute-carter') {
carterRouteForm = { id: 0, name: '', origin: '', destination: '', duration: 'Regular', rental_price: 0, bop_price: 0, notes: '' };
}

        activeMode = 'form';
    };

    const saveBagasiCustomer = async (event: SubmitEvent) => {
        event.preventDefault();
        message = '';
        error = '';
        setSubmitKey('bagasi-customer');

        try {
            await runWithFeedback(async () => {
                await api('POST', '/api/admin/customer-bagasi', bagasiForm);
            }, {
                loadingMessage: bagasiForm.id ? 'Memperbarui customer bagasi...' : 'Menyimpan customer bagasi...',
                successMessage: bagasiForm.id ? 'Customer bagasi berhasil diperbarui.' : 'Customer bagasi berhasil dibuat.',
                errorMessage: 'Gagal simpan customer bagasi.',
            });
            message = bagasiForm.id ? 'Customer bagasi updated.' : 'Customer bagasi created.';
            bagasiForm = { id: 0, nama: '', no_hp: '', alamat: '', tipe: 'pengirim' };
            await loadBagasiCustomers(bagasiMeta.page);
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan customer bagasi.';
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
            await runWithFeedback(async () => {
                await api('POST', '/api/admin/customer-charter', charterForm);
            }, {
                loadingMessage: charterForm.id ? 'Memperbarui customer carter...' : 'Menyimpan customer carter...',
                successMessage: charterForm.id ? 'Customer carter berhasil diperbarui.' : 'Customer carter berhasil dibuat.',
                errorMessage: 'Gagal simpan customer charter.',
            });
            message = charterForm.id ? 'Customer charter updated.' : 'Customer charter created.';
            charterForm = { id: 0, nama: '', no_hp: '', alamat: '', company: '' };
            await loadCharterCustomers(charterMeta.page);
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan customer charter.';
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
            await runWithFeedback(async () => {
                await api('POST', '/api/admin/charter-routes', {
                    id: carterRouteForm.id || undefined,
                    name: carterRouteForm.name,
                    origin: carterRouteForm.origin,
                    destination: carterRouteForm.destination,
                    duration: carterRouteForm.duration,
                    rental_price: Number(carterRouteForm.rental_price),
                    bop_price: Number(carterRouteForm.bop_price),
                    notes: carterRouteForm.notes,
                });
            }, {
                loadingMessage: carterRouteForm.id ? 'Memperbarui master carter...' : 'Menyimpan master carter...',
                successMessage: carterRouteForm.id ? 'Master carter berhasil diperbarui.' : 'Master carter berhasil dibuat.',
                errorMessage: 'Gagal simpan rute carter.',
            });
            message = carterRouteForm.id ? 'Rute carter updated.' : 'Rute carter created.';
            carterRouteForm = { id: 0, name: '', origin: '', destination: '', duration: 'Regular', rental_price: 0, bop_price: 0, notes: '' };
            await loadCarterRoutes(carterRouteMeta.page);
            activeMode = 'data';
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal simpan rute carter.';
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
            const initialTab = new URLSearchParams(window.location.search).get('tab');

            if (isMasterTab(initialTab)) {
                activeTab = initialTab;
                lockedMenuView = true;
            }
        }

        busy = true;

        try {
            await loadActiveTab();
        } catch (e) {
            error = e instanceof Error ? e.message : 'Gagal memuat data awal.';
        } finally {
            busy = false;
        }
    });
</script>

<AppHead title={masterTabTitle(activeTab)} />

<div class="space-y-4 p-4">
    <Card>
        <CardHeader><CardTitle>{masterTabTitle(activeTab)}</CardTitle></CardHeader>
        <CardContent class="space-y-4">
            {#if !lockedMenuView}
                <div class="flex flex-wrap gap-2">
                    <Button type="button" variant={activeTab === 'customer-bagasi' ? 'default' : 'outline'} onclick={() => void setTab('customer-bagasi')}>Bagasi</Button>
                    <Button type="button" variant={activeTab === 'customer-charter' ? 'default' : 'outline'} onclick={() => void setTab('customer-charter')}>Carter</Button>
                    <Button type="button" variant={activeTab === 'rute-carter' ? 'default' : 'outline'} onclick={() => void setTab('rute-carter')}>Master Carter</Button>
                </div>
            {/if}

            {#if busy}<p class="text-sm text-muted-foreground">Memuat data...</p>{/if}
            {#if error}<p class="text-sm text-red-600">{error}</p>{/if}
            {#if message}<p class="text-sm text-emerald-600">{message}</p>{/if}

            {#if activeTab === 'customer-bagasi' && !busy}
                {#if activeMode === 'form'}
                    <div class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2">
                        <p class="text-xs font-medium text-muted-foreground">{bagasiForm.id ? 'Halaman Edit Customer Bagasi' : 'Halaman Tambah Customer Bagasi Baru'}</p>
                        <Button type="button" size="sm" variant="outline" class="h-8 rounded-lg text-xs" onclick={() => setFormMode('data')}>Kembali ke Data</Button>
                    </div>
                    <form class="grid gap-3 md:grid-cols-4" onsubmit={saveBagasiCustomer}>
                        <Input placeholder="Nama" bind:value={bagasiForm.nama} required />
                        <Input placeholder="No HP" bind:value={bagasiForm.no_hp} required />
                        <Input placeholder="Alamat" bind:value={bagasiForm.alamat} />
                        <select class="h-9 rounded-md border border-input bg-background px-3 text-sm" bind:value={bagasiForm.tipe}>
                            <option value="pengirim">Pengirim</option>
                            <option value="penerima">Penerima</option>
                            <option value="keduanya">Keduanya</option>
                        </select>
                        <div><LoadingButton type="submit" loading={isSubmitActive('bagasi-customer')} loadingText={bagasiForm.id ? 'Menyimpan...' : 'Membuat...'}>{bagasiForm.id ? 'Update' : 'Create'}</LoadingButton></div>
                    </form>
                {:else}
                    <div class="flex flex-wrap gap-2">
                        <Input placeholder="Cari nama/no hp/alamat" bind:value={bagasiQ} />
                        <Button type="button" onclick={() => void loadBagasiCustomers(1)}>Search</Button>
                        <Button type="button" variant="outline" onclick={openCreateMasterForm}>Tambah Data Baru</Button>
                    </div>
                    <div class="overflow-x-auto rounded-md border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-muted/50"><tr><th class="px-3 py-2 text-left">Nama</th><th class="px-3 py-2 text-left">No HP</th><th class="px-3 py-2 text-left">Tipe</th><th class="px-3 py-2 text-left">Aksi</th></tr></thead>
                            <tbody>
                                {#each bagasiCustomers as row (row.id)}
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{row.nama}</td>
                                        <td class="px-3 py-2">{row.no_hp}</td>
                                        <td class="px-3 py-2">{row.tipe ?? '-'}</td>
                                        <td class="space-x-2 px-3 py-2">
                                            <Button type="button" size="sm" variant="outline" onclick={() => {
 bagasiForm = { id: row.id, nama: row.nama, no_hp: row.no_hp, alamat: row.alamat ?? '', tipe: row.tipe ?? 'pengirim' }; setFormMode('form'); 
}}>Edit</Button>
                                            <Button type="button" size="sm" variant="outline" disabled={pendingDeleteKey === `/api/admin/customer-bagasi/${row.id}`} onclick={() => void removeRow(`/api/admin/customer-bagasi/${row.id}`, 'Customer bagasi deleted.')}>{pendingDeleteKey === `/api/admin/customer-bagasi/${row.id}` ? 'Menghapus...' : 'Delete'}</Button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Total: {bagasiMeta.total}</p>
                        <div class="flex gap-2">
                            <Button type="button" variant="outline" disabled={bagasiMeta.page <= 1} onclick={() => void jumpPage('customer-bagasi', bagasiMeta.page - 1)}>Prev</Button>
                            <span class="px-2 py-1 text-sm">{bagasiMeta.page} / {bagasiMeta.last_page}</span>
                            <Button type="button" variant="outline" disabled={bagasiMeta.page >= bagasiMeta.last_page} onclick={() => void jumpPage('customer-bagasi', bagasiMeta.page + 1)}>Next</Button>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'customer-charter' && !busy}
                {#if activeMode === 'form'}
                    <div class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2">
                        <p class="text-xs font-medium text-muted-foreground">{charterForm.id ? 'Halaman Edit Customer Carter' : 'Halaman Tambah Customer Carter Baru'}</p>
                        <Button type="button" size="sm" variant="outline" class="h-8 rounded-lg text-xs" onclick={() => setFormMode('data')}>Kembali ke Data</Button>
                    </div>
                    <form class="grid gap-3 md:grid-cols-4" onsubmit={saveCharterCustomer}>
                        <Input placeholder="Nama" bind:value={charterForm.nama} required />
                        <Input placeholder="No HP" bind:value={charterForm.no_hp} required />
                        <Input placeholder="Company" bind:value={charterForm.company} />
                        <Input placeholder="Alamat" bind:value={charterForm.alamat} />
                        <div><LoadingButton type="submit" loading={isSubmitActive('charter-customer')} loadingText={charterForm.id ? 'Menyimpan...' : 'Membuat...'}>{charterForm.id ? 'Update' : 'Create'}</LoadingButton></div>
                    </form>
                {:else}
                    <div class="flex flex-wrap gap-2">
                        <Input placeholder="Cari nama/no hp/company" bind:value={charterQ} />
                        <Button type="button" onclick={() => void loadCharterCustomers(1)}>Search</Button>
                        <Button type="button" variant="outline" onclick={openCreateMasterForm}>Tambah Data Baru</Button>
                    </div>
                    <div class="overflow-x-auto rounded-md border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-muted/50"><tr><th class="px-3 py-2 text-left">Nama</th><th class="px-3 py-2 text-left">No HP</th><th class="px-3 py-2 text-left">Company</th><th class="px-3 py-2 text-left">Aksi</th></tr></thead>
                            <tbody>
                                {#each charterCustomers as row (row.id)}
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{row.nama}</td>
                                        <td class="px-3 py-2">{row.no_hp}</td>
                                        <td class="px-3 py-2">{row.company ?? '-'}</td>
                                        <td class="space-x-2 px-3 py-2">
                                            <Button type="button" size="sm" variant="outline" onclick={() => {
 charterForm = { id: row.id, nama: row.nama, no_hp: row.no_hp, alamat: row.alamat ?? '', company: row.company ?? '' }; setFormMode('form'); 
}}>Edit</Button>
                                            <Button type="button" size="sm" variant="outline" disabled={pendingDeleteKey === `/api/admin/customer-charter/${row.id}`} onclick={() => void removeRow(`/api/admin/customer-charter/${row.id}`, 'Customer charter deleted.')}>{pendingDeleteKey === `/api/admin/customer-charter/${row.id}` ? 'Menghapus...' : 'Delete'}</Button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Total: {charterMeta.total}</p>
                        <div class="flex gap-2">
                            <Button type="button" variant="outline" disabled={charterMeta.page <= 1} onclick={() => void jumpPage('customer-charter', charterMeta.page - 1)}>Prev</Button>
                            <span class="px-2 py-1 text-sm">{charterMeta.page} / {charterMeta.last_page}</span>
                            <Button type="button" variant="outline" disabled={charterMeta.page >= charterMeta.last_page} onclick={() => void jumpPage('customer-charter', charterMeta.page + 1)}>Next</Button>
                        </div>
                    </div>
                {/if}
            {/if}

            {#if activeTab === 'rute-carter' && !busy}
                {#if activeMode === 'form'}
                    <div class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2">
                        <p class="text-xs font-medium text-muted-foreground">{carterRouteForm.id ? 'Halaman Edit Master Carter' : 'Halaman Tambah Master Carter Baru'}</p>
                        <Button type="button" size="sm" variant="outline" class="h-8 rounded-lg text-xs" onclick={() => setFormMode('data')}>Kembali ke Data</Button>
                    </div>
                    <form class="grid gap-3 md:grid-cols-4" onsubmit={saveCarterRoute}>
                        <Input placeholder="Nama rute carter" bind:value={carterRouteForm.name} required />
                        <Input placeholder="Origin" bind:value={carterRouteForm.origin} />
                        <Input placeholder="Destination" bind:value={carterRouteForm.destination} />
                        <Input placeholder="Durasi (Regular/VIP/...)" bind:value={carterRouteForm.duration} />
                        <Input type="number" min="0" step="1000" placeholder="Harga rental" bind:value={carterRouteForm.rental_price} />
                        <Input type="number" min="0" step="1000" placeholder="BOP" bind:value={carterRouteForm.bop_price} />
                        <Input class="md:col-span-2" placeholder="Catatan" bind:value={carterRouteForm.notes} />
                        <div class="flex gap-2">
                            <LoadingButton type="submit" loading={isSubmitActive('carter-route')} loadingText={carterRouteForm.id ? 'Menyimpan...' : 'Membuat...'}>{carterRouteForm.id ? 'Update' : 'Create'}</LoadingButton>
                            <Button type="button" variant="outline" onclick={() => (carterRouteForm = { id: 0, name: '', origin: '', destination: '', duration: 'Regular', rental_price: 0, bop_price: 0, notes: '' })}>Reset</Button>
                        </div>
                    </form>
                {:else}
                    <div class="flex flex-wrap gap-2">
                        <Input placeholder="Cari nama/origin/destination" bind:value={carterRouteQ} />
                        <Button type="button" onclick={() => void loadCarterRoutes(1)}>Search</Button>
                        <Button type="button" variant="outline" onclick={openCreateMasterForm}>Tambah Data Baru</Button>
                    </div>
                    <div class="overflow-x-auto rounded-md border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-muted/50"><tr><th class="px-3 py-2 text-left">Nama</th><th class="px-3 py-2 text-left">Asal/Tujuan</th><th class="px-3 py-2 text-left">Durasi</th><th class="px-3 py-2 text-left">Harga</th><th class="px-3 py-2 text-left">Aksi</th></tr></thead>
                            <tbody>
                                {#each carterRoutes as row (row.id)}
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{row.name}</td>
                                        <td class="px-3 py-2">{row.origin ?? '-'} -> {row.destination ?? '-'}</td>
                                        <td class="px-3 py-2">{row.duration ?? '-'}</td>
                                        <td class="px-3 py-2">Sewa: Rp {Number(row.rental_price).toLocaleString('id-ID')}<br />BOP: Rp {Number(row.bop_price).toLocaleString('id-ID')}</td>
                                        <td class="space-x-2 px-3 py-2">
                                            <Button type="button" size="sm" variant="outline" onclick={() => {
 carterRouteForm = { id: row.id, name: row.name, origin: row.origin ?? '', destination: row.destination ?? '', duration: row.duration ?? 'Regular', rental_price: Number(row.rental_price), bop_price: Number(row.bop_price), notes: row.notes ?? '' }; setFormMode('form'); 
}}>Edit</Button>
                                            <Button type="button" size="sm" variant="outline" disabled={pendingDeleteKey === `/api/admin/charter-routes/${row.id}`} onclick={() => void removeRow(`/api/admin/charter-routes/${row.id}`, 'Rute carter deleted.')}>{pendingDeleteKey === `/api/admin/charter-routes/${row.id}` ? 'Menghapus...' : 'Delete'}</Button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Total: {carterRouteMeta.total}</p>
                        <div class="flex gap-2">
                            <Button type="button" variant="outline" disabled={carterRouteMeta.page <= 1} onclick={() => void jumpPage('rute-carter', carterRouteMeta.page - 1)}>Prev</Button>
                            <span class="px-2 py-1 text-sm">{carterRouteMeta.page} / {carterRouteMeta.last_page}</span>
                            <Button type="button" variant="outline" disabled={carterRouteMeta.page >= carterRouteMeta.last_page} onclick={() => void jumpPage('rute-carter', carterRouteMeta.page + 1)}>Next</Button>
                        </div>
                    </div>
                {/if}
            {/if}

        </CardContent>
    </Card>
</div>
