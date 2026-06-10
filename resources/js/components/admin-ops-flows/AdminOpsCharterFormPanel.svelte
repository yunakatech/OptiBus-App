<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';

    type ViewMode = 'data' | 'form' | 'view';
    type CharterForm = {
        id: number;
        pool_id: number;
        master_carter_id: number;
        name: string;
        company_name: string;
        phone: string;
        start_date: string;
        end_date: string;
        departure_time: string;
        pickup_point: string;
        drop_point: string;
        unit_id: number;
        armada_id: number;
        armada_nopol: string;
        driver_name: string;
        price: number;
        layanan: string;
        bop_price: number;
        bop_status: string;
        down_payment: number;
        payment_status: string;
    };
    type CharterCustomer = {
        id: number;
        nama: string;
        no_hp: string;
        alamat: string | null;
        company: string | null;
    };
    type CharterRoute = {
        id: number;
        name: string;
        origin: string | null;
        destination: string | null;
        duration: string | null;
        rental_price: number;
        bop_price: number;
    };
    type PoolOption = { id: number; name: string; code?: string | null; status?: string | null; route_ids?: number[] };
    type Unit = {
        id: number;
        nopol: string;
        merek?: string | null;
        type?: string | null;
        category?: string | null;
        kapasitas?: number | null;
        status?: string | null;
        layout?: string | null;
    };
    type Armada = {
        id: number;
        nopol: string;
        merk: string | null;
        kategori: string | null;
        tahun: number | null;
        warna: string | null;
        ac_type: string | null;
    };
    type Driver = { id: number; nama: string; phone?: string | null };

    const fallbackCharterForm: CharterForm = {
        id: 0,
        pool_id: 0,
        master_carter_id: 0,
        name: '',
        company_name: '',
        phone: '',
        start_date: '',
        end_date: '',
        departure_time: '08:00',
        pickup_point: '',
        drop_point: '',
        unit_id: 0,
        armada_id: 0,
        armada_nopol: '',
        driver_name: '',
        price: 0,
        layanan: 'DROPOFF',
        bop_price: 0,
        bop_status: 'pending',
        down_payment: 0,
        payment_status: 'Belum Lunas',
    };

    let {
        charterForm = $bindable<CharterForm>(fallbackCharterForm),
        charterCustomerQuery = '',
        charterCustomerBusy = false,
        charterCustomerLookupOpen = false,
        charterCustomerResults = [],
        charterServiceOptions = [],
        charterRouteSearch = $bindable(''),
        charterRouteLookupOpen = $bindable(false),
        charterStartDateInput = $bindable<HTMLInputElement | null>(null),
        charterEndDateInput = $bindable<HTMLInputElement | null>(null),
        charterDepartureTimeInput = $bindable<HTMLInputElement | null>(null),
        charterUnitSearch = $bindable(''),
        charterUnitLookupOpen = $bindable(false),
        charterArmadaSearch = $bindable(''),
        charterArmadaLookupOpen = $bindable(false),
        charterArmadaBusy = false,
        armadas = [],
        charterDriverSearch = $bindable(''),
        charterDriverLookupOpen = $bindable(false),
        charterPaymentStatusOptions = [],
        savingCharter = false,
        activePools,
        poolLabel,
        poolNameById,
        onCharterCustomerQueryInput,
        applyCharterCustomer,
        onCharterRouteBlur,
        filteredCharterRoutes,
        selectCharterRoute,
        charterRouteLabel,
        charterRouteMeta,
        onCharterUnitBlur,
        filteredCharterUnits,
        selectCharterUnit,
        charterUnitLabel,
        charterUnitMeta,
        loadCharterArmadas,
        queueCharterArmadaSearch,
        onCharterArmadaBlur,
        filteredCharterArmadas,
        selectCharterArmada,
        onCharterDriverBlur,
        filteredCharterDrivers,
        selectCharterDriver,
        selectedCharterDriver,
        selectedCharterUnit,
        formatCurrencyInput,
        parseCurrencyInput,
        formatCurrencyId,
        saveCharter,
        setFormMode,
        resetCharterFormState,
    }: {
        charterForm?: CharterForm;
        charterCustomerQuery?: string;
        charterCustomerBusy?: boolean;
        charterCustomerLookupOpen?: boolean;
        charterCustomerResults?: CharterCustomer[];
        charterServiceOptions?: string[];
        charterRouteSearch?: string;
        charterRouteLookupOpen?: boolean;
        charterStartDateInput?: HTMLInputElement | null;
        charterEndDateInput?: HTMLInputElement | null;
        charterDepartureTimeInput?: HTMLInputElement | null;
        charterUnitSearch?: string;
        charterUnitLookupOpen?: boolean;
        charterArmadaSearch?: string;
        charterArmadaLookupOpen?: boolean;
        charterArmadaBusy?: boolean;
        armadas?: Armada[];
        charterDriverSearch?: string;
        charterDriverLookupOpen?: boolean;
        charterPaymentStatusOptions?: string[];
        savingCharter?: boolean;
        activePools: () => PoolOption[];
        poolLabel: (pool: PoolOption | null | undefined) => string;
        poolNameById: (poolId: number | null | undefined) => string;
        onCharterCustomerQueryInput: (value: string) => void;
        applyCharterCustomer: (customer: CharterCustomer) => void;
        onCharterRouteBlur: () => void;
        filteredCharterRoutes: () => CharterRoute[];
        selectCharterRoute: (route: CharterRoute) => void;
        charterRouteLabel: (route: CharterRoute | null | undefined) => string;
        charterRouteMeta: (route: CharterRoute | null | undefined) => string;
        onCharterUnitBlur: () => void;
        filteredCharterUnits: () => Unit[];
        selectCharterUnit: (unit: Unit) => void;
        charterUnitLabel: (unit: Unit | null | undefined) => string;
        charterUnitMeta: (unit: Unit | null | undefined) => string;
        loadCharterArmadas: (keywordRaw?: string) => void | Promise<void>;
        queueCharterArmadaSearch: (value: string) => void;
        onCharterArmadaBlur: () => void;
        filteredCharterArmadas: () => Armada[];
        selectCharterArmada: (armada: Armada) => void;
        onCharterDriverBlur: () => void;
        filteredCharterDrivers: () => Driver[];
        selectCharterDriver: (driver: Driver) => void;
        selectedCharterDriver: () => Driver | null;
        selectedCharterUnit: () => Unit | null;
        formatCurrencyInput: (value: number | string | null | undefined) => string;
        parseCurrencyInput: (value: string | number | null | undefined) => number;
        formatCurrencyId: (value: number | string | null | undefined) => string;
        saveCharter: (event: SubmitEvent) => void | Promise<void>;
        setFormMode: (mode: ViewMode) => void;
        resetCharterFormState: () => void;
    } = $props();
</script>

<div class="flex items-center justify-between gap-2 rounded-xl border border-border/70 bg-muted/20 px-3 py-2">
    <p class="text-xs font-medium text-muted-foreground">Halaman Form Charter</p>
    <Button type="button" size="sm" variant="outline" class="h-8 rounded-lg text-xs" onclick={() => setFormMode('data')}>
        Kembali ke Data Carter
    </Button>
</div>

<div class="overflow-hidden rounded-[28px] border border-border/70 bg-card shadow-sm">
    <div class="border-b border-border/70 bg-[linear-gradient(135deg,rgba(15,23,42,0.03),rgba(8,145,178,0.07))] px-4 py-4 md:px-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <p class="text-lg font-semibold tracking-tight">{charterForm.id ? 'Edit Data Charter' : 'Form Data Charter'}</p>
                <p class="text-xs text-muted-foreground">Susun data customer, jadwal perjalanan, armada, dan pembayaran dalam satu workspace yang ringkas.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full border border-border/70 bg-background/80 px-2.5 py-1 text-[11px] font-medium text-muted-foreground">
                    {charterForm.id ? 'Mode Edit' : 'Data Baru'}
                </span>
                <span class="rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-[11px] font-medium text-cyan-700">
                    {charterForm.payment_status || 'Belum Lunas'}
                </span>
            </div>
        </div>
    </div>
    <form class="grid gap-4 p-4 md:p-5 xl:grid-cols-[minmax(0,1.65fr)_320px]" onsubmit={saveCharter}>
        <div class="space-y-4">
            <section class="rounded-2xl border border-border/70 bg-muted/10 p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">Customer</p>
                        <p class="text-sm font-medium text-foreground">Data pemesan dan layanan</p>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="space-y-1 md:col-span-2 xl:col-span-4">
                        <label for="charter-customer-lookup" class="text-xs font-medium text-muted-foreground">Cari Customer Lama</label>
                        <Input
                            id="charter-customer-lookup"
                            class="rounded-xl"
                            placeholder="Cari nama customer atau nomor HP..."
                            value={charterCustomerQuery}
                            oninput={(event) => onCharterCustomerQueryInput((event.currentTarget as HTMLInputElement).value)}
                        />
                        {#if charterCustomerBusy}
                            <p class="text-[11px] text-muted-foreground">Mencari customer...</p>
                        {/if}
                        {#if charterCustomerLookupOpen && charterCustomerResults.length > 0}
                            <div class="max-h-44 overflow-y-auto rounded-xl border border-border/70 bg-background p-1.5">
                                {#each charterCustomerResults as customer (customer.id)}
                                    <button
                                        type="button"
                                        class="flex w-full items-start justify-between gap-2 rounded-lg px-2.5 py-2 text-left text-xs transition-colors hover:bg-muted/60"
                                        onclick={() => applyCharterCustomer(customer)}
                                    >
                                        <span class="min-w-0">
                                            <span class="block truncate font-medium text-foreground">{customer.nama}</span>
                                            <span class="block truncate text-muted-foreground">{customer.company ?? '-'}</span>
                                        </span>
                                        <span class="shrink-0 text-muted-foreground">{customer.no_hp}</span>
                                    </button>
                                {/each}
                            </div>
                        {:else if charterCustomerLookupOpen && !charterCustomerBusy}
                            <p class="text-[11px] text-muted-foreground">Customer tidak ditemukan.</p>
                        {/if}
                    </div>
                    <div class="space-y-1 xl:col-span-2">
                        <label for="charter-name" class="text-xs font-medium text-muted-foreground">Nama Charter</label>
                        <Input
                            id="charter-name"
                            class="rounded-xl"
                            placeholder="Nama customer / PIC"
                            bind:value={charterForm.name}
                            oninput={(event) => onCharterCustomerQueryInput((event.currentTarget as HTMLInputElement).value)}
                            required
                        />
                    </div>
                    <div class="space-y-1">
                        <label for="charter-phone" class="text-xs font-medium text-muted-foreground">Telepon</label>
                        <Input
                            id="charter-phone"
                            class="rounded-xl"
                            placeholder="Nomor telepon"
                            bind:value={charterForm.phone}
                            oninput={(event) => onCharterCustomerQueryInput((event.currentTarget as HTMLInputElement).value)}
                        />
                    </div>
                    <div class="space-y-1">
                        <label for="charter-company" class="text-xs font-medium text-muted-foreground">Company</label>
                        <Input id="charter-company" class="rounded-xl" placeholder="Nama perusahaan" bind:value={charterForm.company_name} />
                    </div>
                    <div class="space-y-1 xl:col-span-2">
                        <label for="charter-service" class="text-xs font-medium text-muted-foreground">Layanan</label>
                        <select id="charter-service" class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm" bind:value={charterForm.layanan}>
                            {#if charterForm.layanan && !charterServiceOptions.includes(charterForm.layanan)}
                                <option value={charterForm.layanan}>{charterForm.layanan} (data lama)</option>
                            {/if}
                            {#each charterServiceOptions as service (service)}
                                <option value={service}>{service}</option>
                            {/each}
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-border/70 bg-muted/10 p-4">
                <div class="mb-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">Perjalanan</p>
                    <p class="text-sm font-medium text-foreground">Atur tanggal, jam, dan rute charter</p>
                </div>
                <div class="grid gap-3 md:grid-cols-6">
                    <div class="relative space-y-1 md:col-span-6">
                        <label for="charter-route-master" class="text-xs font-medium text-muted-foreground">Master Carter</label>
                        <Input
                            id="charter-route-master"
                            class="rounded-xl"
                            placeholder="Cari rute dari Master Carter untuk auto isi rute, durasi, harga, dan BOP"
                            bind:value={charterRouteSearch}
                            onfocus={() => {
                                charterRouteLookupOpen = true;
                            }}
                            oninput={() => {
                                charterRouteLookupOpen = true;
                            }}
                            onblur={onCharterRouteBlur}
                        />
                        {#if charterRouteLookupOpen}
                            <div class="absolute z-30 mt-2 max-h-64 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl">
                                {#if filteredCharterRoutes().length === 0}
                                    <p class="px-2 py-2 text-xs text-muted-foreground">Master Carter belum ditemukan. Data rute baru akan ikut disimpan saat Carter disimpan.</p>
                                {:else}
                                    <div class="space-y-1">
                                        {#each filteredCharterRoutes() as route (`charter-route-${route.id}`)}
                                            <button
                                                type="button"
                                                class="flex w-full items-start justify-between gap-3 rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70 dark:hover:border-cyan-500/30 dark:hover:bg-cyan-950/25"
                                                onmousedown={(event) => {
                                                    event.preventDefault();
                                                    selectCharterRoute(route);
                                                }}
                                            >
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-semibold text-foreground">{charterRouteLabel(route)}</span>
                                                    <span class="block truncate text-[11px] text-muted-foreground">{charterRouteMeta(route)}</span>
                                                </span>
                                                <span class="shrink-0 rounded-full border border-cyan-200/70 bg-cyan-50 px-2 py-0.5 text-[10px] font-semibold text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-950/40 dark:text-cyan-100">
                                                    Pakai
                                                </span>
                                            </button>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                        <p class="text-[11px] text-muted-foreground">Pilih master untuk mengisi titik jemput, titik antar, durasi/layanan, harga rental, dan BOP secara otomatis.</p>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label for="charter-start-date" class="text-xs font-medium text-muted-foreground">Tanggal Mulai</label>
                        <input
                            id="charter-start-date"
                            bind:this={charterStartDateInput}
                            type="text"
                            value={charterForm.start_date}
                            readonly
                            autocomplete="off"
                            required
                            class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        />
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label for="charter-end-date" class="text-xs font-medium text-muted-foreground">Tanggal Selesai</label>
                        <input
                            id="charter-end-date"
                            bind:this={charterEndDateInput}
                            type="text"
                            value={charterForm.end_date}
                            readonly
                            autocomplete="off"
                            required
                            class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        />
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label for="charter-departure-time" class="text-xs font-medium text-muted-foreground">Jam Berangkat</label>
                        <input
                            id="charter-departure-time"
                            bind:this={charterDepartureTimeInput}
                            type="text"
                            value={charterForm.departure_time}
                            readonly
                            autocomplete="off"
                            class="flex h-9 w-full rounded-xl border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        />
                    </div>
                    <div class="space-y-1 md:col-span-3">
                        <label for="charter-pickup" class="text-xs font-medium text-muted-foreground">Titik Jemput</label>
                        <Input id="charter-pickup" class="rounded-xl" placeholder="Pickup point" bind:value={charterForm.pickup_point} oninput={() => (charterForm.master_carter_id = 0)} />
                    </div>
                    <div class="space-y-1 md:col-span-3">
                        <label for="charter-drop" class="text-xs font-medium text-muted-foreground">Titik Antar</label>
                        <Input id="charter-drop" class="rounded-xl" placeholder="Drop point" bind:value={charterForm.drop_point} oninput={() => (charterForm.master_carter_id = 0)} />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-border/70 bg-muted/10 p-4">
                <div class="mb-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted-foreground">Operasional & Pembayaran</p>
                    <p class="text-sm font-medium text-foreground">Mapping armada, driver, dan nilai transaksi</p>
                </div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                    <div class="space-y-1 xl:col-span-2">
                        <label for="charter-pool" class="text-xs font-medium text-muted-foreground">Perwakilan / Pool</label>
                        <select id="charter-pool" class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm" bind:value={charterForm.pool_id} required={activePools().length > 0}>
                            <option value={0}>Pilih pool</option>
                            {#each activePools() as pool (pool.id)}
                                <option value={pool.id}>{poolLabel(pool)}</option>
                            {/each}
                        </select>
                        <p class="text-[11px] leading-snug text-muted-foreground">Dipakai untuk akses user dan laporan. Tidak ditampilkan di card Carter.</p>
                    </div>
                    <div class="relative space-y-1 xl:col-span-2">
                        <label for="charter-unit" class="text-xs font-medium text-muted-foreground">Kategori Armada</label>
                        <Input
                            id="charter-unit"
                            class="rounded-xl"
                            placeholder="Cari data dari menu Kategori Armada"
                            bind:value={charterUnitSearch}
                            onfocus={() => {
                                charterUnitLookupOpen = true;
                            }}
                            oninput={() => {
                                charterUnitLookupOpen = true;
                            }}
                            onblur={onCharterUnitBlur}
                        />
                        {#if charterUnitLookupOpen}
                            <div class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl">
                                {#if filteredCharterUnits().length === 0}
                                    <p class="px-2 py-2 text-xs text-muted-foreground">Kategori armada tidak ditemukan.</p>
                                {:else}
                                    <div class="space-y-1">
                                        {#each filteredCharterUnits() as unit (`charter-unit-${unit.id}`)}
                                            <button
                                                type="button"
                                                class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70"
                                                onmousedown={(event) => {
                                                    event.preventDefault();
                                                    selectCharterUnit(unit);
                                                }}
                                            >
                                                <span>
                                                    <span class="block text-sm font-semibold text-foreground">{charterUnitLabel(unit) || unit.nopol}</span>
                                                    <span class="block text-[11px] text-muted-foreground">{charterUnitMeta(unit)}</span>
                                                </span>
                                            </button>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                    <div class="relative space-y-1 xl:col-span-2">
                        <label for="charter-armada" class="text-xs font-medium text-muted-foreground">Nopol</label>
                        <Input
                            id="charter-armada"
                            class="rounded-xl"
                            placeholder={Number(charterForm.unit_id || 0) > 0 ? 'Cari data dari menu Armada' : 'Pilih kategori armada terlebih dahulu'}
                            bind:value={charterArmadaSearch}
                            disabled={Number(charterForm.unit_id || 0) <= 0}
                            onfocus={() => {
                                if (Number(charterForm.unit_id || 0) <= 0) {
                                    return;
                                }

                                charterArmadaLookupOpen = true;

                                if (armadas.length === 0) {
                                    void loadCharterArmadas(charterArmadaSearch);
                                }
                            }}
                            oninput={(event) => {
                                charterArmadaLookupOpen = true;
                                queueCharterArmadaSearch((event.currentTarget as HTMLInputElement).value);
                            }}
                            onblur={onCharterArmadaBlur}
                        />
                        {#if charterArmadaLookupOpen}
                            <div class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl">
                                {#if charterArmadaBusy}
                                    <p class="px-2 py-2 text-xs text-muted-foreground">Memuat armada...</p>
                                {:else if filteredCharterArmadas().length === 0}
                                    <p class="px-2 py-2 text-xs text-muted-foreground">Armada tidak ditemukan.</p>
                                {:else}
                                    <div class="space-y-1">
                                        {#each filteredCharterArmadas() as armada (`charter-armada-${armada.id}`)}
                                            <button
                                                type="button"
                                                class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70"
                                                onmousedown={(event) => {
                                                    event.preventDefault();
                                                    selectCharterArmada(armada);
                                                }}
                                            >
                                                <span>
                                                    <span class="block text-sm font-semibold text-foreground">{armada.nopol}</span>
                                                    <span class="block text-[11px] text-muted-foreground">{[armada.kategori, armada.merk, armada.tahun].filter(Boolean).join(' | ') || 'Armada aktif'}</span>
                                                </span>
                                            </button>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                    <div class="relative space-y-1 xl:col-span-2">
                        <label for="charter-driver" class="text-xs font-medium text-muted-foreground">Driver</label>
                        <Input
                            id="charter-driver"
                            class="rounded-xl"
                            placeholder="Cari nama driver"
                            bind:value={charterDriverSearch}
                            onfocus={() => {
                                charterDriverLookupOpen = true;
                            }}
                            oninput={() => {
                                charterDriverLookupOpen = true;
                            }}
                            onblur={onCharterDriverBlur}
                        />
                        {#if charterDriverLookupOpen}
                            <div class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-border/80 bg-popover p-2 shadow-xl">
                                {#if filteredCharterDrivers().length === 0}
                                    <p class="px-2 py-2 text-xs text-muted-foreground">Driver tidak ditemukan.</p>
                                {:else}
                                    <div class="space-y-1">
                                        {#each filteredCharterDrivers() as driver (`charter-driver-${driver.id}`)}
                                            <button
                                                type="button"
                                                class="flex w-full items-start justify-between rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-cyan-200 hover:bg-cyan-50/70"
                                                onmousedown={(event) => {
                                                    event.preventDefault();
                                                    selectCharterDriver(driver);
                                                }}
                                            >
                                                <span>
                                                    <span class="block text-sm font-semibold text-foreground">{driver.nama}</span>
                                                    <span class="block text-[11px] text-muted-foreground">{driver.phone || 'Driver aktif'}</span>
                                                </span>
                                                {#if selectedCharterDriver()?.id === driver.id}
                                                    <span class="text-[11px] font-medium text-cyan-700">Terpilih</span>
                                                {/if}
                                            </button>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                    <div class="space-y-1 xl:col-span-2">
                        <label for="charter-payment-status" class="text-xs font-medium text-muted-foreground">Payment Status</label>
                        <select id="charter-payment-status" class="h-9 w-full rounded-xl border border-input bg-background px-3 text-sm" bind:value={charterForm.payment_status}>
                            {#each charterPaymentStatusOptions as paymentStatus (paymentStatus)}
                                <option value={paymentStatus}>{paymentStatus}</option>
                            {/each}
                        </select>
                    </div>
                    <div class="space-y-1 xl:col-span-2">
                        <label for="charter-price" class="text-xs font-medium text-muted-foreground">Harga Charter</label>
                        <Input
                            id="charter-price"
                            class="rounded-xl"
                            type="text"
                            inputmode="numeric"
                            placeholder="Rp 0"
                            value={formatCurrencyInput(charterForm.price)}
                            oninput={(event) => {
                                const target = event.currentTarget as HTMLInputElement;
                                charterForm.price = parseCurrencyInput(target.value);
                            }}
                        />
                    </div>
                    <div class="space-y-1 xl:col-span-2">
                        <label for="charter-down-payment" class="text-xs font-medium text-muted-foreground">Nominal DP</label>
                        <Input
                            id="charter-down-payment"
                            class="rounded-xl"
                            type="text"
                            inputmode="numeric"
                            placeholder="Rp 0"
                            value={formatCurrencyInput(charterForm.down_payment)}
                            oninput={(event) => {
                                const target = event.currentTarget as HTMLInputElement;
                                charterForm.down_payment = parseCurrencyInput(target.value);
                            }}
                        />
                        <p class="text-[11px] leading-snug text-muted-foreground">Isi jika customer sudah membayar uang muka. Status pembayaran tetap Lunas atau Belum Lunas.</p>
                    </div>
                    <div class="space-y-1 md:col-span-2 xl:col-span-2">
                        <label for="charter-bop-price" class="text-xs font-medium text-muted-foreground">Nominal BOP</label>
                        <Input
                            id="charter-bop-price"
                            class="rounded-xl"
                            type="text"
                            inputmode="numeric"
                            placeholder="Rp 0"
                            value={formatCurrencyInput(charterForm.bop_price)}
                            oninput={(event) => {
                                const target = event.currentTarget as HTMLInputElement;
                                charterForm.bop_price = parseCurrencyInput(target.value);
                            }}
                        />
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-border/70 bg-slate-950 p-4 text-slate-50 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-300">Ringkasan Charter</p>
                <div class="mt-3 space-y-3">
                    <div>
                        <p class="truncate text-sm font-semibold">{charterForm.name || 'Nama charter belum diisi'}</p>
                        <p class="truncate text-[11px] text-slate-300">{charterForm.company_name || 'Perusahaan belum diisi'}</p>
                        <p class="truncate text-[11px] text-slate-300">{charterForm.phone || 'Nomor telepon belum diisi'}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                            <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Mulai</p>
                            <p class="mt-1 font-medium">{charterForm.start_date || '-'}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                            <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Selesai</p>
                            <p class="mt-1 font-medium">{charterForm.end_date || '-'}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                            <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Jam</p>
                            <p class="mt-1 font-medium">{charterForm.departure_time || '-'}</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                            <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Layanan</p>
                            <p class="mt-1 font-medium">{charterForm.layanan || '-'}</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-xs">
                        <p class="text-[10px] uppercase tracking-[0.08em] text-slate-300">Operasional</p>
                        <div class="mt-2 space-y-1.5">
                            <div class="flex items-center justify-between gap-2"><span class="text-slate-300">Kategori Armada</span><span class="font-medium text-right">{charterUnitLabel(selectedCharterUnit()) || '-'}</span></div>
                            <div class="flex items-center justify-between gap-2"><span class="text-slate-300">Pool</span><span class="font-medium text-right">{poolNameById(charterForm.pool_id)}</span></div>
                            <div class="flex items-center justify-between gap-2"><span class="text-slate-300">Nopol</span><span class="font-medium text-right">{charterForm.armada_nopol || '-'}</span></div>
                            <div class="flex items-center justify-between gap-2"><span class="text-slate-300">Driver</span><span class="font-medium text-right">{charterForm.driver_name || '-'}</span></div>
                            <div class="flex items-center justify-between gap-2"><span class="text-slate-300">Status Bayar</span><span class="font-medium text-right">{charterForm.payment_status || '-'}</span></div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 p-3 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-cyan-100">Harga Charter</span>
                            <span class="font-semibold">{formatCurrencyId(charterForm.price)}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="text-cyan-100">Nominal BOP</span>
                            <span class="font-semibold">{formatCurrencyId(charterForm.bop_price)}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="text-cyan-100">Margin</span>
                            <span class={`font-semibold ${(Number(charterForm.price || 0) - Number(charterForm.bop_price || 0)) >= 0 ? 'text-emerald-300' : 'text-rose-300'}`}>{formatCurrencyId(Number(charterForm.price || 0) - Number(charterForm.bop_price || 0))}</span>
                        </div>
                        {#if Number(charterForm.down_payment || 0) > 0}
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span class="text-cyan-100">DP Masuk</span>
                                <span class="font-semibold">{formatCurrencyId(Number(charterForm.down_payment || 0))}</span>
                            </div>
                        {/if}
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-2 border-t border-border/70 pt-1 xl:border-0 xl:pt-0">
                <LoadingButton
                    type="submit"
                    class="h-9 rounded-xl px-4"
                    loading={savingCharter}
                    loadingText={charterForm.id ? 'Menyimpan perubahan...' : 'Menyimpan charter...'}
                >
                    {charterForm.id ? 'Update Charter' : 'Simpan Charter'}
                </LoadingButton>
                <Button
                    type="button"
                    variant="outline"
                    class="h-9 rounded-xl px-4"
                    onclick={() => {
                        resetCharterFormState();
                    }}
                >
                    Reset Form
                </Button>
            </div>
        </aside>
    </form>
</div>
