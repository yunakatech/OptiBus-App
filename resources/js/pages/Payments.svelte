<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Pembayaran',
                href: '/payments',
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { CreditCard, Download, MoreHorizontal, RefreshCw, Search, WalletCards } from 'lucide-svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Input } from '@/components/ui/input';
    import { LoadingButton } from '@/components/ui/loading-button';
    import { runWithFeedback } from '@/lib/action-feedback';
    import {
        formatCurrencyDisplay,
        formatCurrencyInput,
        parseCurrencyInput,
    } from '@/lib/currency';
    import { consumeDataStale, markDataStale } from '@/lib/data-invalidation';

    type StatusKey = 'unpaid' | 'dp' | 'paid';
    type SourceKey = 'all' | 'booking' | 'charter' | 'luggage';

    type PaymentFilters = {
        status: StatusKey;
        source: SourceKey;
        q: string;
        page: number;
        per_page: number;
    };

    type PaymentRow = {
        key: string;
        source: 'booking' | 'charter' | 'luggage';
        source_label: string;
        id: number;
        code: string;
        customer_name: string;
        secondary_name: string;
        contact: string;
        route: string;
        date: string;
        time: string;
        amount: number;
        paid_amount: number;
        down_payment: number;
        remaining_amount: number;
        payment_status: string;
        status_bucket: StatusKey;
        pool_name: string;
        can_update: boolean;
    };

    type PaymentData = {
        filters: PaymentFilters;
        rows: PaymentRow[];
        summary: {
            by_status: Record<
                StatusKey,
                { count: number; amount: number; remaining: number }
            >;
            active: { count: number; amount: number; remaining: number };
        };
        pagination: {
            page: number;
            per_page: number;
            total: number;
            last_page: number;
        };
        source_access: Record<'booking' | 'charter' | 'luggage', boolean>;
    };

    type AuthPoolScope = {
        all?: boolean;
        pool_name?: string;
        route_ids?: number[];
    } | null;

    let {
        filters,
        paymentData = null,
    }: {
        filters: PaymentFilters;
        paymentData?: PaymentData | null;
    } = $props();

    const statusTabs: Array<{ key: StatusKey; label: string; hint: string }> = [
        { key: 'unpaid', label: 'Belum Lunas', hint: 'Perlu ditagih' },
        { key: 'dp', label: 'DP', hint: 'Sudah ada titipan' },
        { key: 'paid', label: 'Lunas', hint: 'Selesai' },
    ];
    const sourceTabs: Array<{ key: SourceKey; label: string }> = [
        { key: 'all', label: 'Semua' },
        { key: 'booking', label: 'Keberangkatan' },
        { key: 'charter', label: 'Carter' },
        { key: 'luggage', label: 'Bagasi' },
    ];
    const paymentOptions = ['Belum Lunas', 'DP', 'Lunas'];

    let activeStatus = $state<StatusKey>('unpaid');
    let activeSource = $state<SourceKey>('all');
    let searchQuery = $state('');
    let perPage = $state(20);
    let localData = $state<PaymentData | null>(null);
    let loading = $state(false);
    let updatingKey = $state('');
    let drafts = $state<Record<string, { payment_status: string; down_payment: string }>>({});
    let initializedFromProps = $state(false);

    const authPoolScope = $derived((page.props.auth?.pool_scope ?? null) as AuthPoolScope);
    const poolContextName = $derived(String(authPoolScope?.pool_name ?? 'Semua Pool'));
    const poolRouteCount = $derived(
        Array.isArray(authPoolScope?.route_ids) ? authPoolScope.route_ids.length : 0,
    );
    const rows = $derived(localData?.rows ?? []);
    const pagination = $derived(
        localData?.pagination ?? {
            page: Number(filters.page || 1),
            per_page: Number(filters.per_page || 20),
            total: 0,
            last_page: 1,
        },
    );
    const summary = $derived(localData?.summary ?? {
        by_status: {
            unpaid: { count: 0, amount: 0, remaining: 0 },
            dp: { count: 0, amount: 0, remaining: 0 },
            paid: { count: 0, amount: 0, remaining: 0 },
        },
        active: { count: 0, amount: 0, remaining: 0 },
    });
    const activeAmount = $derived(Number(summary.active?.amount ?? 0));
    const activeRemaining = $derived(Number(summary.active?.remaining ?? 0));
    const activePaidEstimate = $derived(Math.max(activeAmount - activeRemaining, 0));
    const activeCollectionRate = $derived(
        activeAmount > 0 ? Math.round((activePaidEstimate / activeAmount) * 100) : 0,
    );

    $effect(() => {
        if (initializedFromProps) {
            return;
        }

        activeStatus = filters.status ?? 'unpaid';
        activeSource = filters.source ?? 'all';
        searchQuery = filters.q ?? '';
        perPage = Number(filters.per_page || 20);
        localData = paymentData;
        initializedFromProps = true;
    });

    $effect(() => {
        if (paymentData) {
            localData = paymentData;
            loading = false;
        }
    });

    $effect(() => {
        const nextDrafts = { ...drafts };
        let changed = false;

        for (const row of rows) {
            if (!nextDrafts[row.key]) {
                nextDrafts[row.key] = {
                    payment_status: displayStatusForDraft(row),
                    down_payment: formatCurrencyInput(row.down_payment || row.paid_amount || 0),
                };
                changed = true;
            }
        }

        if (changed) {
            drafts = nextDrafts;
        }
    });

    const displayStatusForDraft = (row: PaymentRow) => {
        if (row.status_bucket === 'paid') {
            return 'Lunas';
        }

        if (row.status_bucket === 'dp') {
            return 'DP';
        }

        return 'Belum Lunas';
    };

    const statusCount = (status: StatusKey) =>
        Number(summary.by_status?.[status]?.count ?? 0);

    const statusAmount = (status: StatusKey) =>
        Number(summary.by_status?.[status]?.amount ?? 0);

    const sourceClass = (source: PaymentRow['source']) => {
        if (source === 'booking') {
            return 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-400/20 dark:bg-sky-950/25 dark:text-sky-200';
        }

        if (source === 'charter') {
            return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/20 dark:bg-amber-950/25 dark:text-amber-200';
        }

        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-950/25 dark:text-emerald-200';
    };

    const statusClass = (status: string) => {
        const normalized = status.toLowerCase();

        if (normalized.includes('lunas') && !normalized.includes('belum')) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700';
        }

        if (normalized === 'dp') {
            return 'border-amber-200 bg-amber-50 text-amber-700';
        }

        return 'border-rose-200 bg-rose-50 text-rose-700';
    };

    const updateDraftStatus = (row: PaymentRow, value: string) => {
        drafts = {
            ...drafts,
            [row.key]: {
                ...(drafts[row.key] ?? {
                    payment_status: displayStatusForDraft(row),
                    down_payment: formatCurrencyInput(row.down_payment || row.paid_amount || 0),
                }),
                payment_status: value,
            },
        };
    };

    const updateDraftDownPayment = (row: PaymentRow, value: string) => {
        drafts = {
            ...drafts,
            [row.key]: {
                ...(drafts[row.key] ?? {
                    payment_status: displayStatusForDraft(row),
                    down_payment: formatCurrencyInput(row.down_payment || row.paid_amount || 0),
                }),
                down_payment: value,
            },
        };
    };

    const csrfToken = () =>
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)
            ?.content ?? '';

    const xsrfTokenFromCookie = () => {
        if (typeof document === 'undefined') {
            return '';
        }

        const part = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='));

        if (!part) {
            return '';
        }

        try {
            return decodeURIComponent(part.split('=')[1] ?? '');
        } catch {
            return '';
        }
    };

    const apiPost = async (url: string, body: Record<string, unknown>) => {
        const token = csrfToken() || xsrfTokenFromCookie();
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ ...body, _token: token }),
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok || payload.success === false) {
            throw new Error(payload.error || payload.message || 'Gagal memproses pembayaran.');
        }

        return payload;
    };

    const reloadData = (pageNumber = 1) => {
        consumeDataStale(['payments']);
        loading = true;
        router.get(
            '/payments',
            {
                status: activeStatus,
                source: activeSource,
                q: searchQuery.trim(),
                page: pageNumber,
                per_page: perPage,
            },
            {
                only: ['paymentData'],
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onFinish: () => {
                    loading = false;
                },
            },
        );
    };

    const reloadIfPaymentDataStale = () => {
        if (!initializedFromProps || loading) {
            return;
        }

        if (consumeDataStale(['payments'])) {
            reloadData(pagination.page);
        }
    };

    $effect(() => {
        if (!initializedFromProps) {
            return;
        }

        reloadIfPaymentDataStale();
    });

    const exportUrl = () => {
        const params = new URLSearchParams({
            status: activeStatus,
            source: activeSource,
        });
        const keyword = searchQuery.trim();

        if (keyword !== '') {
            params.set('q', keyword);
        }

        return `/payments/export?${params.toString()}`;
    };

    const setStatus = (status: StatusKey) => {
        activeStatus = status;
        reloadData(1);
    };

    const setSource = (source: SourceKey) => {
        activeSource = source;
        reloadData(1);
    };

    const updatePayment = async (row: PaymentRow) => {
        const draft = drafts[row.key] ?? {
            payment_status: displayStatusForDraft(row),
            down_payment: formatCurrencyInput(row.down_payment || row.paid_amount || 0),
        };
        updatingKey = row.key;

        try {
            await runWithFeedback(async () => {
                await apiPost(`/api/admin/payments/${row.source}/${row.id}`, {
                    payment_status: draft.payment_status,
                    down_payment: parseCurrencyInput(draft.down_payment),
                });
            }, {
                loadingMessage: 'Memperbarui pembayaran...',
                successMessage: 'Pembayaran berhasil diperbarui.',
                errorMessage: 'Gagal memperbarui pembayaran.',
            });
            markDataStale(['bookings', 'flows', 'dashboard']);
            reloadData(pagination.page);
        } finally {
            updatingKey = '';
        }
    };

    onMount(() => {
        const checkSoon = () => {
            window.setTimeout(reloadIfPaymentDataStale, 0);
        };
        const checkWhenVisible = () => {
            if (document.visibilityState === 'visible') {
                checkSoon();
            }
        };

        checkSoon();
        window.addEventListener('pageshow', checkSoon);
        window.addEventListener('focus', checkSoon);
        document.addEventListener('visibilitychange', checkWhenVisible);

        return () => {
            window.removeEventListener('pageshow', checkSoon);
            window.removeEventListener('focus', checkSoon);
            document.removeEventListener('visibilitychange', checkWhenVisible);
        };
    });

</script>

<AppHead title="Pembayaran" />

{#snippet PaymentActionMenu(row: PaymentRow)}
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            {#snippet children(props)}
                <Button
                    {...props}
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 rounded-full border border-border/70 bg-background/80 shadow-sm hover:border-cyan-200 hover:bg-cyan-50 dark:hover:bg-cyan-950/20"
                    aria-label={`Aksi pembayaran ${row.code}`}
                >
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            {/snippet}
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" sideOffset={8} class="z-[120] w-72 rounded-2xl p-3">
            <div class="space-y-3">
                <div class="rounded-xl bg-muted/40 p-2">
                    <p class="truncate text-xs font-semibold text-foreground">{row.customer_name || row.code}</p>
                    <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{row.code} | {formatCurrencyDisplay(row.remaining_amount)} sisa</p>
                </div>
                <label class="block space-y-1.5">
                    <span class="text-xs font-semibold text-muted-foreground">Status pembayaran</span>
                    <select
                        class="h-10 w-full rounded-xl border border-input bg-background px-3 text-sm"
                        value={drafts[row.key]?.payment_status ?? displayStatusForDraft(row)}
                        disabled={!row.can_update}
                        onchange={(event) => updateDraftStatus(row, (event.currentTarget as HTMLSelectElement).value)}
                    >
                        {#each paymentOptions as option (option)}
                            <option value={option}>{option}</option>
                        {/each}
                    </select>
                </label>
                {#if row.source === 'charter' && (drafts[row.key]?.payment_status ?? displayStatusForDraft(row)) === 'DP'}
                    <label class="block space-y-1.5">
                        <span class="text-xs font-semibold text-muted-foreground">Nominal DP</span>
                        <Input
                            class="h-10 rounded-xl text-right"
                            value={drafts[row.key]?.down_payment ?? formatCurrencyInput(row.down_payment)}
                            oninput={(event) => updateDraftDownPayment(row, (event.currentTarget as HTMLInputElement).value)}
                        />
                    </label>
                {/if}
                <LoadingButton
                    type="button"
                    class="h-10 w-full rounded-xl"
                    loading={updatingKey === row.key}
                    disabled={!row.can_update}
                    loadingText="Menyimpan..."
                    onclick={() => updatePayment(row)}
                >
                    Simpan Pembayaran
                </LoadingButton>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
{/snippet}

<div class="min-h-full space-y-4 overflow-x-hidden p-3 pb-28 md:p-4">
    <Card class="overflow-hidden border-sidebar-border/70 bg-linear-to-br from-background via-background to-cyan-50/30 dark:border-sidebar-border dark:to-cyan-950/15">
        <CardHeader class="space-y-4 border-b bg-background/80 backdrop-blur">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-800 dark:border-cyan-400/20 dark:bg-cyan-950/30 dark:text-cyan-100">
                        <WalletCards class="h-3.5 w-3.5" />
                        Payment Desk
                    </div>
                    <div>
                        <CardTitle class="text-xl md:text-2xl">Pembayaran Operasional</CardTitle>
                        <p class="mt-1 max-w-2xl text-sm text-muted-foreground">
                            Kelola pembayaran Data Keberangkatan, Carter, dan Bagasi dalam satu halaman sesuai pool user.
                        </p>
                    </div>
                </div>
                <div class="rounded-2xl border border-cyan-200/70 bg-cyan-50/70 px-3 py-2 text-xs text-cyan-950 dark:border-cyan-500/20 dark:bg-cyan-950/20 dark:text-cyan-100">
                    <p class="font-semibold">Pool aktif: {poolContextName}</p>
                    <p class="mt-0.5 text-[11px] text-cyan-800/80 dark:text-cyan-200/75">
                        {authPoolScope?.all ? 'Semua pool' : `${poolRouteCount} rute mapped ke user ini`}
                    </p>
                </div>
            </div>

            <div class="grid gap-2 md:grid-cols-3">
                {#each statusTabs as tab (tab.key)}
                    <button
                        type="button"
                        class={`rounded-2xl border p-3 text-left transition ${activeStatus === tab.key ? 'border-cyan-400 bg-cyan-50 shadow-sm dark:border-cyan-500/40 dark:bg-cyan-950/25' : 'border-border bg-card hover:border-cyan-200 hover:bg-cyan-50/40 dark:hover:bg-cyan-950/10'}`}
                        onclick={() => setStatus(tab.key)}
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold">{tab.label}</span>
                            <span class="rounded-full bg-background px-2 py-0.5 text-xs font-bold text-foreground">
                                {statusCount(tab.key)}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">{tab.hint}</p>
                        <p class="mt-2 text-sm font-semibold text-foreground">
                            {formatCurrencyDisplay(statusAmount(tab.key))}
                        </p>
                    </button>
                {/each}
            </div>

            <div class="grid gap-2 md:grid-cols-4">
                <div class="rounded-xl border border-border/70 bg-card p-3">
                    <p class="text-xs text-muted-foreground">Data aktif</p>
                    <p class="mt-1 text-lg font-semibold text-foreground">{summary.active.count}</p>
                </div>
                <div class="rounded-xl border border-border/70 bg-card p-3">
                    <p class="text-xs text-muted-foreground">Nilai tagihan</p>
                    <p class="mt-1 text-lg font-semibold text-foreground">{formatCurrencyDisplay(activeAmount)}</p>
                </div>
                <div class="rounded-xl border border-border/70 bg-card p-3">
                    <p class="text-xs text-muted-foreground">Sisa ditagih</p>
                    <p class="mt-1 text-lg font-semibold text-foreground">{formatCurrencyDisplay(activeRemaining)}</p>
                </div>
                <div class="rounded-xl border border-border/70 bg-card p-3">
                    <p class="text-xs text-muted-foreground">Estimasi tertagih</p>
                    <p class="mt-1 text-lg font-semibold text-foreground">{activeCollectionRate}%</p>
                </div>
            </div>
        </CardHeader>

        <CardContent class="space-y-4 p-4">
            <div class="flex flex-col gap-3 rounded-2xl border border-border/70 bg-card/80 p-3 shadow-sm md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap gap-2">
                    {#each sourceTabs as tab (tab.key)}
                        <Button
                            type="button"
                            variant={activeSource === tab.key ? 'default' : 'outline'}
                            class="h-8 rounded-full px-3 text-xs"
                            onclick={() => setSource(tab.key)}
                        >
                            {tab.label}
                        </Button>
                    {/each}
                </div>
                <div class="flex flex-col gap-2 md:flex-row md:items-center">
                    <div class="relative min-w-0 md:w-80">
                        <Search class="pointer-events-none absolute top-2.5 left-3 h-4 w-4 text-muted-foreground" />
                        <Input
                            class="h-9 rounded-full pl-9"
                            placeholder="Cari nama, kode, no HP, rute..."
                            bind:value={searchQuery}
                            onkeydown={(event) => event.key === 'Enter' && reloadData(1)}
                        />
                    </div>
                    <select
                        class="h-9 rounded-full border border-input bg-background px-3 text-sm"
                        bind:value={perPage}
                        onchange={() => reloadData(1)}
                    >
                        <option value={10}>10/baris</option>
                        <option value={20}>20/baris</option>
                        <option value={50}>50/baris</option>
                    </select>
                    <Button type="button" variant="outline" class="h-9 rounded-full" onclick={() => reloadData(1)}>
                        <RefreshCw class={`mr-1.5 h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
                        Muat
                    </Button>
                    <Button asChild variant="outline" class="h-9 rounded-full border-cyan-200 bg-cyan-50/70 text-cyan-800 hover:bg-cyan-100 dark:border-cyan-400/20 dark:bg-cyan-950/20 dark:text-cyan-100">
                        {#snippet children(props)}
                            <a {...props} href={exportUrl()} target="_blank" rel="noreferrer">
                                <Download class="mr-1.5 h-4 w-4" />
                                Export CSV
                            </a>
                        {/snippet}
                    </Button>
                </div>
            </div>

            {#if !localData}
                <div class="space-y-2 rounded-2xl border border-border/70 bg-card p-4">
                    <div class="h-8 w-48 animate-pulse rounded bg-muted"></div>
                    <div class="h-12 w-full animate-pulse rounded bg-muted"></div>
                    <div class="h-12 w-full animate-pulse rounded bg-muted"></div>
                    <div class="h-12 w-full animate-pulse rounded bg-muted"></div>
                </div>
            {:else if rows.length === 0}
                <div class="rounded-2xl border border-dashed border-border bg-muted/20 p-8 text-center">
                    <CreditCard class="mx-auto h-10 w-10 text-muted-foreground" />
                    <p class="mt-3 font-semibold text-foreground">Data pembayaran belum ada.</p>
                    <p class="mt-1 text-sm text-muted-foreground">Coba pindah tab status, ubah filter sumber data, atau kosongkan pencarian.</p>
                </div>
            {:else}
                <div class="hidden overflow-hidden rounded-2xl border border-border/70 bg-card shadow-sm md:block">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Transaksi</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Pool/Rute</th>
                                <th class="px-4 py-3 text-right">Tagihan</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/70">
                            {#each rows as row (row.key)}
                                <tr class="align-top">
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            <Badge class={`rounded-full border px-2 py-0.5 text-[10px] ${sourceClass(row.source)}`}>
                                                {row.source_label}
                                            </Badge>
                                            <p class="font-semibold text-foreground">{row.code}</p>
                                            <p class="text-xs text-muted-foreground">{row.date || '-'} {row.time || ''}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-foreground">{row.customer_name || '-'}</p>
                                        <p class="text-xs text-muted-foreground">{row.secondary_name || row.contact || '-'}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-foreground">{row.pool_name || '-'}</p>
                                        <p class="text-xs text-muted-foreground">{row.route}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <p class="font-semibold text-foreground">{formatCurrencyDisplay(row.amount)}</p>
                                        <p class="text-xs text-muted-foreground">Sisa {formatCurrencyDisplay(row.remaining_amount)}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge class={`rounded-full border px-2 py-0.5 text-[10px] ${statusClass(row.payment_status)}`}>
                                            {row.payment_status}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {@render PaymentActionMenu(row)}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    {#each rows as row (row.key)}
                        <div class="rounded-2xl border border-border/70 bg-card p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <Badge class={`rounded-full border px-2 py-0.5 text-[10px] ${sourceClass(row.source)}`}>
                                        {row.source_label}
                                    </Badge>
                                    <p class="mt-2 font-semibold text-foreground">{row.customer_name || row.code}</p>
                                    <p class="text-xs text-muted-foreground">{row.code} | {row.date || '-'}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge class={`rounded-full border px-2 py-0.5 text-[10px] ${statusClass(row.payment_status)}`}>
                                        {row.payment_status}
                                    </Badge>
                                    {@render PaymentActionMenu(row)}
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-muted/30 p-2 text-xs">
                                <div>
                                    <p class="text-muted-foreground">Pool</p>
                                    <p class="font-semibold text-foreground">{row.pool_name || '-'}</p>
                                </div>
                                <div>
                                    <p class="text-muted-foreground">Tagihan</p>
                                    <p class="font-semibold text-foreground">{formatCurrencyDisplay(row.amount)}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-muted-foreground">Rute</p>
                                    <p class="font-semibold text-foreground">{row.route}</p>
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}

            <div class="flex flex-wrap items-center justify-between gap-3 border-t pt-3">
                <p class="text-sm text-muted-foreground">
                    Total {pagination.total} data, halaman {pagination.page} dari {pagination.last_page}
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-9 rounded-full px-3"
                        disabled={pagination.page <= 1 || loading}
                        onclick={() => reloadData(pagination.page - 1)}
                    >
                        Prev
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="h-9 rounded-full px-3"
                        disabled={pagination.page >= pagination.last_page || loading}
                        onclick={() => reloadData(pagination.page + 1)}
                    >
                        Next
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</div>
