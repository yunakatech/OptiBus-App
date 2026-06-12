<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Langganan', href: '/subscription' }],
    };
</script>

<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import {
        AlertTriangle,
        Banknote,
        Check,
        CheckCircle2,
        Copy,
        CreditCard,
        ExternalLink,
        FileCheck2,
        Landmark,
        QrCode,
        Receipt,
        ShieldAlert,
        Upload,
        X,
    } from 'lucide-svelte';
    import { onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Label } from '@/components/ui/label';

    type TenantSub = {
        subscription_id?: number;
        tenant_id: number;
        tenant_name: string;
        tenant_status?: string;
        plan_id: number;
        plan_name: string;
        plan_slug: string;
        subscription_status: string;
        trial_ends_at: string | null;
        ends_at: string | null;
    };

    type Invoice = {
        id: number;
        invoice_number: string;
        amount: number;
        status: string;
        due_date: string;
        paid_at: string | null;
        payment_method: string;
        payment_proof: string | null;
        payment_proof_url: string | null;
        created_at: string;
    };

    type Plan = {
        id: number;
        name: string;
        slug: string;
        price_monthly: number;
        price_yearly: number;
        description: string;
    };

    type BankAccount = {
        bank_name: string;
        account_number: string;
        account_holder: string;
        note: string;
    };

    type PaymentConfig = {
        qris: {
            enabled: boolean;
            merchant_name: string;
            image_url: string;
            image_path?: string;
            has_image?: boolean;
            storage_status?: string;
            note: string;
        };
        bank_transfer: { enabled: boolean; accounts: BankAccount[] };
        upload_max_kb: number;
    };

    type AccountAccess = {
        tenant_id: number;
        pool_count: number;
        role_names: string[];
    };

    type BadgeMeta = {
        variant: 'default' | 'destructive' | 'outline' | 'secondary';
        label: string;
    };

    const tenantSub = $derived((page.props.tenant_subscription ?? null) as TenantSub | null);
    const invoices = $derived((page.props.invoices ?? []) as Invoice[]);
    const currentPlan = $derived((page.props.current_plan ?? null) as Plan | null);
    const plans = $derived((page.props.plans ?? []) as Plan[]);
    const paymentConfig = $derived((page.props.payment_config ?? {
        qris: { enabled: false, merchant_name: '', image_url: '', has_image: false, storage_status: '', note: '' },
        bank_transfer: { enabled: false, accounts: [] },
        upload_max_kb: 2048,
    }) as PaymentConfig);
    const accountAccess = $derived((page.props.account_access ?? {
        tenant_id: 0,
        pool_count: 0,
        role_names: [],
    }) as AccountAccess);

    const pendingInvoices = $derived(invoices.filter((invoice) => ['pending', 'overdue'].includes(invoice.status) && !invoice.payment_proof));
    const payableInvoice = $derived(
        pendingInvoices[0] ?? null,
    );
    const invoiceInVerification = $derived(
        invoices.find((invoice) => invoice.status === 'verification' || (invoice.status === 'pending' && invoice.payment_proof)) ?? null,
    );
    const paidInvoices = $derived(invoices.filter((invoice) => invoice.status === 'paid'));
    const subscriptionMeta = $derived(statusBadge(tenantSub?.subscription_status ?? ''));
    const canAccessDashboard = $derived(tenantSub?.subscription_status === 'trial' || tenantSub?.subscription_status === 'active');
    const paymentMethodsCount = $derived(
        Number(paymentConfig.qris.enabled) + Number(paymentConfig.bank_transfer.enabled && paymentConfig.bank_transfer.accounts.length > 0),
    );
    const activeBillingTitle = $derived(
        payableInvoice
            ? 'Pembayaran perlu diselesaikan'
            : invoiceInVerification
                ? 'Pembayaran sedang diverifikasi'
                : paidInvoices[0]
                    ? 'Langganan sudah lunas'
                    : 'Belum ada invoice aktif',
    );
    const activeBillingDescription = $derived(
        payableInvoice
            ? `${payableInvoice.invoice_number} jatuh tempo ${formatDate(payableInvoice.due_date)}`
            : invoiceInVerification
                ? `${invoiceInVerification.invoice_number} menunggu verifikasi admin`
                : paidInvoices[0]
                    ? `${paidInvoices[0].invoice_number} lunas pada ${formatDate(paidInvoices[0].paid_at)}`
                    : 'Invoice akan muncul otomatis setelah memilih paket berbayar.',
    );

    let showPaymentModal = $state(false);
    let payingInvoice = $state<Invoice | null>(null);
    let paymentTab = $state<'qris' | 'transfer'>('qris');
    let selectedBank = $state(0);
    let paymentMethodLabel = $state('');
    let proofFile = $state<File | null>(null);
    let uploading = $state(false);
    let uploadMessage = $state('');
    let copiedAccount = $state('');
    let copyResetTimer: number | undefined = undefined;
    let uploadCloseTimer: number | undefined = undefined;

    function formatRupiah(value: number): string {
        return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
    }

    function formatDate(value: string | null | undefined): string {
        if (!value) {
            return '-';
        }

        return new Date(value).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    }

    function statusBadge(status: string): BadgeMeta {
        const map: Record<string, BadgeMeta> = {
            trial: { variant: 'secondary', label: 'Trial' },
            pending_payment: { variant: 'outline', label: 'Menunggu Pembayaran' },
            active: { variant: 'default', label: 'Aktif' },
            past_due: { variant: 'outline', label: 'Jatuh Tempo' },
            suspended: { variant: 'destructive', label: 'Ditangguhkan' },
            canceled: { variant: 'destructive', label: 'Dibatalkan' },
            expired: { variant: 'outline', label: 'Kedaluwarsa' },
        };

        return map[status] ?? { variant: 'outline', label: status || 'Belum aktif' };
    }

    function invoiceStatusBadge(status: string): BadgeMeta {
        const map: Record<string, BadgeMeta> = {
            pending: { variant: 'secondary', label: 'Menunggu' },
            verification: { variant: 'outline', label: 'Verifikasi' },
            paid: { variant: 'default', label: 'Lunas' },
            overdue: { variant: 'destructive', label: 'Overdue' },
            failed: { variant: 'destructive', label: 'Gagal' },
        };

        return map[status] ?? { variant: 'outline', label: status || '-' };
    }

    function planStateLabel(plan: Plan): string {
        if (plan.slug === currentPlan?.slug) {
            return 'Paket aktif';
        }

        return plan.price_monthly > (currentPlan?.price_monthly ?? 0)
            ? 'Upgrade via admin'
            : 'Downgrade via admin';
    }

    function defaultPaymentTab(): 'qris' | 'transfer' {
        if (paymentConfig.qris.enabled) {
            return 'qris';
        }

        return 'transfer';
    }

    function openPayment(invoice: Invoice) {
        payingInvoice = invoice;
        paymentTab = defaultPaymentTab();
        selectedBank = 0;
        paymentMethodLabel = '';
        proofFile = null;
        uploadMessage = '';
        showPaymentModal = true;
    }

    function csrfToken(): string {
        return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';
    }

    function xsrfTokenFromCookie(): string {
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
    }

    function selectedPaymentMethodLabel(): string {
        if (paymentMethodLabel.trim() !== '') {
            return paymentMethodLabel.trim();
        }

        if (paymentTab === 'qris') {
            return `QRIS - ${paymentConfig.qris.merchant_name || 'Qbus'}`;
        }

        const account = paymentConfig.bank_transfer.accounts[selectedBank];
        if (!account) {
            return 'Transfer bank';
        }

        return `Transfer ${account.bank_name} ${account.account_number}`;
    }

    async function uploadProof(invoiceOverride: Invoice | null = null) {
        const invoice = invoiceOverride ?? payingInvoice;
        if (!proofFile || !invoice) {
            return;
        }

        uploading = true;
        uploadMessage = '';

        const token = csrfToken() || xsrfTokenFromCookie();
        const formData = new FormData();
        formData.append('proof_file', proofFile);
        formData.append('payment_method', selectedPaymentMethodLabel());

        try {
            const response = await fetch(`/api/subscription/upload-proof/${invoice.id}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.success === false) {
                uploadMessage = data.error || data.message || 'Gagal upload bukti pembayaran.';
                return;
            }

            uploadMessage = data.message || 'Bukti pembayaran berhasil diupload.';
            if (uploadCloseTimer) {
                window.clearTimeout(uploadCloseTimer);
            }

            uploadCloseTimer = window.setTimeout(() => {
                showPaymentModal = false;
                window.location.reload();
                uploadCloseTimer = undefined;
            }, 1200);
        } catch (error: any) {
            uploadMessage = error.message || 'Network error';
        } finally {
            uploading = false;
        }
    }

    function copyText(text: string) {
        navigator.clipboard?.writeText(text);
        copiedAccount = text;

        if (copyResetTimer) {
            window.clearTimeout(copyResetTimer);
        }

        copyResetTimer = window.setTimeout(() => {
            copiedAccount = '';
        }, 1600);
    }

    function handleQrisError(event: Event) {
        const img = event.target as HTMLImageElement;
        img.src = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="192" height="192"><rect fill="#f8fafc" width="192" height="192"/><text x="96" y="100" text-anchor="middle" fill="#64748b" font-size="14">QRIS</text></svg>');
    }

    function qrisStorageLabel(status: string | undefined): string {
        const labels: Record<string, string> = {
            ready: 'QRIS siap dipakai',
            missing_link: 'QRIS sudah diupload, storage link belum aktif',
            missing_file: 'QRIS belum diupload',
            external: 'QRIS eksternal',
        };

        return labels[status ?? ''] ?? 'Status QRIS belum diketahui';
    }

    onDestroy(() => {
        if (copyResetTimer) {
            window.clearTimeout(copyResetTimer);
        }
        if (uploadCloseTimer) {
            window.clearTimeout(uploadCloseTimer);
        }
    });

    function closePaymentModal() {
        showPaymentModal = false;
    }

    function selectProofFile(event: Event) {
        const input = event.currentTarget as HTMLInputElement;
        proofFile = input.files?.[0] ?? null;
    }
</script>

<AppHead title="Langganan" />

<div class="min-h-full space-y-5 overflow-x-hidden p-3 pb-24 md:p-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">Langganan</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Kelola status paket, invoice, dan upload bukti pembayaran tenant.
            </p>
        </div>
        {#if canAccessDashboard}
            <Button asChild variant="outline" class="h-9 w-fit rounded-lg">
                {#snippet children(props)}
                    <Link {...props} href="/dashboard">
                        Ke Dashboard
                        <ExternalLink class="ml-1.5 h-3.5 w-3.5" />
                    </Link>
                {/snippet}
            </Button>
        {/if}
    </div>

    {#if !tenantSub}
        <Card class="border-dashed">
            <CardContent class="py-10 text-center">
                <ShieldAlert class="mx-auto h-10 w-10 text-muted-foreground" />
                <h2 class="mt-4 text-lg font-semibold text-foreground">Data langganan belum tersedia</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
                    Tenant belum memiliki paket aktif. Pilih paket dari pricing atau hubungi admin SaaS.
                </p>
                <Button asChild class="mt-5 rounded-lg">
                    {#snippet children(props)}
                        <Link {...props} href="/pricing">Lihat Pricing</Link>
                    {/snippet}
                </Button>
            </CardContent>
        </Card>
    {:else}
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-4">
                <Card class="overflow-hidden">
                    <CardHeader class="border-b bg-muted/20">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <Badge variant={subscriptionMeta.variant}>{subscriptionMeta.label}</Badge>
                                    <span class="text-xs text-muted-foreground">{tenantSub.tenant_name}</span>
                                </div>
                                <CardTitle class="mt-3 text-2xl">
                                    {currentPlan?.name ?? tenantSub.plan_name}
                                </CardTitle>
                                <CardDescription class="mt-1">
                                    {currentPlan?.description || 'Paket operasional Qbus untuk tenant ini.'}
                                </CardDescription>
                            </div>
                            <div class="rounded-lg border bg-background px-3 py-2 text-right">
                                <p class="text-xs text-muted-foreground">Biaya bulanan</p>
                                <p class="text-lg font-semibold text-foreground">
                                    {formatRupiah(currentPlan?.price_monthly ?? 0)}
                                </p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-3 p-4 md:grid-cols-3">
                        <div class="rounded-lg border border-border/70 p-3">
                            <p class="text-xs text-muted-foreground">Periode / trial</p>
                            <p class="mt-1 font-semibold text-foreground">
                                {tenantSub.subscription_status === 'trial'
                                    ? formatDate(tenantSub.trial_ends_at)
                                    : formatDate(tenantSub.ends_at)}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/70 p-3">
                            <p class="text-xs text-muted-foreground">Invoice menunggu</p>
                            <p class="mt-1 font-semibold text-foreground">{pendingInvoices.length} invoice</p>
                        </div>
                        <div class="rounded-lg border border-border/70 p-3">
                            <p class="text-xs text-muted-foreground">Metode pembayaran</p>
                            <p class="mt-1 font-semibold text-foreground">{paymentMethodsCount} metode aktif</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class={payableInvoice ? 'border-amber-200 bg-amber-50/40 dark:border-amber-400/20 dark:bg-amber-950/10' : 'overflow-hidden'}>
                    <CardHeader class="border-b bg-background/70">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="flex gap-3">
                                <div class={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${payableInvoice ? 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-100' : invoiceInVerification ? 'bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-100' : paidInvoices[0] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-100' : 'bg-muted text-muted-foreground'}`}>
                                    {#if payableInvoice}
                                        <Receipt class="h-5 w-5" />
                                    {:else if invoiceInVerification}
                                        <FileCheck2 class="h-5 w-5" />
                                    {:else if paidInvoices[0]}
                                        <CheckCircle2 class="h-5 w-5" />
                                    {:else}
                                        <AlertTriangle class="h-5 w-5" />
                                    {/if}
                                </div>
                                <div>
                                    <CardTitle class="text-xl">{activeBillingTitle}</CardTitle>
                                    <CardDescription class="mt-1">{activeBillingDescription}</CardDescription>
                                </div>
                            </div>
                            {#if payableInvoice}
                                <div class="rounded-lg border bg-background px-4 py-3 text-right">
                                    <p class="text-xs text-muted-foreground">Total invoice</p>
                                    <p class="mt-1 text-2xl font-semibold text-foreground">{formatRupiah(payableInvoice.amount)}</p>
                                </div>
                            {/if}
                        </div>
                    </CardHeader>

                    {#if payableInvoice}
                        <CardContent class="grid gap-4 p-4 lg:grid-cols-[minmax(0,0.95fr)_minmax(280px,1fr)]">
                            <div class="space-y-3">
                                <div class="rounded-lg border bg-background p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-muted-foreground">Invoice</p>
                                            <p class="mt-1 font-semibold text-foreground">{payableInvoice.invoice_number}</p>
                                        </div>
                                        <Badge variant={invoiceStatusBadge(payableInvoice.status).variant}>
                                            {invoiceStatusBadge(payableInvoice.status).label}
                                        </Badge>
                                    </div>
                                    <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                        <div class="rounded-md bg-muted/40 p-3">
                                            <p class="text-xs text-muted-foreground">Jatuh tempo</p>
                                            <p class="mt-1 font-medium text-foreground">{formatDate(payableInvoice.due_date)}</p>
                                        </div>
                                        <div class="rounded-md bg-muted/40 p-3">
                                            <p class="text-xs text-muted-foreground">Metode aktif</p>
                                            <p class="mt-1 font-medium text-foreground">{paymentMethodsCount} metode</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 rounded-lg border bg-background p-1">
                                    {#if paymentConfig.qris.enabled}
                                        <button
                                            type="button"
                                            onclick={() => paymentTab = 'qris'}
                                            class={`flex h-10 items-center justify-center gap-2 rounded-md px-3 text-sm font-medium ${paymentTab === 'qris' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'}`}
                                        >
                                            <QrCode class="h-4 w-4" />
                                            QRIS
                                        </button>
                                    {/if}
                                    {#if paymentConfig.bank_transfer.enabled && paymentConfig.bank_transfer.accounts.length > 0}
                                        <button
                                            type="button"
                                            onclick={() => paymentTab = 'transfer'}
                                            class={`flex h-10 items-center justify-center gap-2 rounded-md px-3 text-sm font-medium ${paymentTab === 'transfer' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'}`}
                                        >
                                            <Landmark class="h-4 w-4" />
                                            Transfer
                                        </button>
                                    {/if}
                                </div>

                                {#if paymentTab === 'qris' && paymentConfig.qris.enabled}
                                    <div class="rounded-lg border bg-white p-4 text-slate-950 shadow-sm dark:bg-white">
                                        <div class="flex flex-col items-center gap-3">
                                            {#if paymentConfig.qris.image_url}
                                                <img
                                                    src={paymentConfig.qris.image_url}
                                                    alt="QRIS {paymentConfig.qris.merchant_name}"
                                                    class="h-56 w-56 object-contain"
                                                    onerror={handleQrisError}
                                                />
                                            {:else}
                                                <div class="flex h-56 w-56 items-center justify-center rounded-lg border border-dashed text-sm text-slate-500">
                                                    QRIS belum tersedia
                                                </div>
                                            {/if}
                                            <div class="text-center">
                                                <p class="font-semibold">{paymentConfig.qris.merchant_name || 'Merchant Qbus'}</p>
                                                <p class="mt-1 text-xs text-slate-500">{qrisStorageLabel(paymentConfig.qris.storage_status)}</p>
                                            </div>
                                        </div>
                                    </div>
                                {/if}

                                {#if paymentTab === 'transfer' && paymentConfig.bank_transfer.enabled && paymentConfig.bank_transfer.accounts.length > 0}
                                    <div class="space-y-2">
                                        {#each paymentConfig.bank_transfer.accounts as account, index}
                                            <div
                                                role="button"
                                                tabindex="0"
                                                onclick={() => selectedBank = index}
                                                onkeydown={(event) => { if (event.key === 'Enter' || event.key === ' ') selectedBank = index; }}
                                                class={`w-full cursor-pointer rounded-lg border bg-background p-3 text-left transition ${selectedBank === index ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/30'}`}
                                            >
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="font-semibold text-foreground">{account.bank_name}</p>
                                                        <p class="mt-1 font-mono text-lg tracking-normal">{account.account_number}</p>
                                                        <p class="mt-1 text-xs text-muted-foreground">a.n. {account.account_holder}</p>
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-8 w-8 rounded-lg"
                                                        onclick={(event) => { event.stopPropagation(); copyText(account.account_number); }}
                                                        aria-label="Salin nomor rekening"
                                                    >
                                                        {#if copiedAccount === account.account_number}
                                                            <Check class="h-4 w-4 text-emerald-600" />
                                                        {:else}
                                                            <Copy class="h-4 w-4" />
                                                        {/if}
                                                    </Button>
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}
                            </div>

                            <div class="rounded-lg border bg-background p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-foreground">Upload bukti pembayaran</p>
                                        <p class="mt-1 text-sm text-muted-foreground">
                                            JPG, PNG, atau PDF maksimal {paymentConfig.upload_max_kb}KB.
                                        </p>
                                    </div>
                                    <Upload class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <label class="mt-4 flex cursor-pointer items-center gap-2 rounded-lg border border-dashed px-4 py-4 text-sm hover:bg-muted/30">
                                    <Upload class="h-4 w-4 shrink-0 text-muted-foreground" />
                                    {#if proofFile}
                                        <span class="min-w-0 truncate font-medium text-emerald-700 dark:text-emerald-300">{proofFile.name}</span>
                                    {:else}
                                        <span class="min-w-0 truncate text-muted-foreground">Pilih file bukti pembayaran</span>
                                    {/if}
                                    <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange={selectProofFile} />
                                </label>

                                <Button class="mt-3 h-10 w-full rounded-lg" onclick={() => uploadProof(payableInvoice)} disabled={!proofFile || uploading}>
                                    {#if uploading}
                                        Mengupload...
                                    {:else}
                                        <Upload class="mr-2 h-4 w-4" />
                                        Kirim Bukti Pembayaran
                                    {/if}
                                </Button>

                                {#if uploadMessage}
                                    <div class={`mt-3 rounded-lg px-4 py-2 text-sm ${uploadMessage.toLowerCase().includes('berhasil') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-200' : 'bg-destructive/10 text-destructive'}`}>
                                        {uploadMessage}
                                    </div>
                                {/if}
                            </div>
                        </CardContent>
                    {:else if invoiceInVerification}
                        <CardContent class="p-4">
                            <div class="rounded-lg border bg-background p-4">
                                <p class="font-semibold text-foreground">Bukti pembayaran sudah dikirim</p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Admin SaaS akan memverifikasi invoice ini sebelum akses operasional aktif.
                                </p>
                                {#if invoiceInVerification.payment_proof_url}
                                    <a href={invoiceInVerification.payment_proof_url} target="_blank" rel="noreferrer" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary">
                                        Lihat bukti pembayaran
                                        <ExternalLink class="h-3.5 w-3.5" />
                                    </a>
                                {/if}
                            </div>
                        </CardContent>
                    {:else if !paidInvoices[0] && tenantSub.subscription_status === 'pending_payment'}
                        <CardContent class="p-4">
                            <div class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                Invoice belum tersedia. Sistem akan mencoba membuat invoice otomatis dari subscription pending payment.
                            </div>
                        </CardContent>
                    {/if}
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Paket Tersedia</CardTitle>
                        <CardDescription>Perbandingan paket yang bisa digunakan tenant.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3 md:grid-cols-3">
                            {#each plans as plan}
                                <div class={`rounded-lg border p-3 ${plan.slug === currentPlan?.slug ? 'border-primary bg-primary/5' : 'border-border/70'}`}>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-semibold text-foreground">{plan.name}</p>
                                        {#if plan.slug === currentPlan?.slug}
                                            <CheckCircle2 class="h-4 w-4 text-primary" />
                                        {/if}
                                    </div>
                                    <p class="mt-2 text-lg font-semibold text-foreground">
                                        {formatRupiah(plan.price_monthly)}
                                        <span class="text-xs font-normal text-muted-foreground">/bulan</span>
                                    </p>
                                    <p class="mt-2 min-h-10 text-xs leading-5 text-muted-foreground">
                                        {plan.description}
                                    </p>
                                    <Badge
                                        variant={plan.slug === currentPlan?.slug ? 'default' : 'outline'}
                                        class="mt-3 w-full justify-center"
                                    >
                                        {planStateLabel(plan)}
                                    </Badge>
                                </div>
                            {/each}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <Receipt class="h-5 w-5" />
                            Riwayat Invoice
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {#if invoices.length > 0}
                            <div class="overflow-hidden rounded-lg border">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50 text-left text-xs uppercase text-muted-foreground">
                                        <tr>
                                            <th class="px-3 py-2">Invoice</th>
                                            <th class="px-3 py-2">Jatuh Tempo</th>
                                            <th class="px-3 py-2 text-right">Nominal</th>
                                            <th class="px-3 py-2">Status</th>
                                            <th class="px-3 py-2 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/70">
                                        {#each invoices as invoice}
                                            <tr>
                                                <td class="px-3 py-3">
                                                    <p class="font-semibold text-foreground">{invoice.invoice_number}</p>
                                                    <p class="mt-0.5 text-xs text-muted-foreground">
                                                        {invoice.payment_method || 'Belum ada metode'}
                                                    </p>
                                                </td>
                                                <td class="px-3 py-3 text-muted-foreground">
                                                    {formatDate(invoice.due_date)}
                                                </td>
                                                <td class="px-3 py-3 text-right font-semibold">
                                                    {formatRupiah(invoice.amount)}
                                                </td>
                                                <td class="px-3 py-3">
                                                    {#if invoice.status === 'verification' || (invoice.payment_proof && invoice.status === 'pending')}
                                                        <Badge variant="secondary">Verifikasi</Badge>
                                                    {:else}
                                                        <Badge variant={invoiceStatusBadge(invoice.status).variant}>
                                                            {invoiceStatusBadge(invoice.status).label}
                                                        </Badge>
                                                    {/if}
                                                </td>
                                                <td class="px-3 py-3 text-right">
                                                    {#if ['pending', 'overdue'].includes(invoice.status) && !invoice.payment_proof}
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            class="h-8 rounded-lg"
                                                            onclick={() => openPayment(invoice)}
                                                        >
                                                            Bayar
                                                        </Button>
                                                    {:else if invoice.payment_proof}
                                                        {#if invoice.payment_proof_url}
                                                            <a href={invoice.payment_proof_url} target="_blank" rel="noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                                                                Lihat Bukti
                                                            </a>
                                                        {:else}
                                                            <span class="text-xs text-emerald-700 dark:text-emerald-300">Bukti terupload</span>
                                                        {/if}
                                                    {:else}
                                                        <span class="text-xs text-muted-foreground">-</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        {:else}
                            <div class="rounded-lg border border-dashed p-6 text-center">
                                <Receipt class="mx-auto h-8 w-8 text-muted-foreground" />
                                <p class="mt-3 text-sm text-muted-foreground">Belum ada invoice.</p>
                            </div>
                        {/if}
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <CreditCard class="h-5 w-5" />
                            Metode Pembayaran
                        </CardTitle>
                        <CardDescription>Metode ini diatur dari menu SaaS admin.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        {#if paymentConfig.qris.enabled}
                            <div class="rounded-lg border border-border/70 p-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border bg-white p-1">
                                        {#if paymentConfig.qris.image_url}
                                            <img
                                                src={paymentConfig.qris.image_url}
                                                alt="QRIS {paymentConfig.qris.merchant_name}"
                                                class="h-12 w-12 object-contain"
                                                onerror={handleQrisError}
                                            />
                                        {:else}
                                            <QrCode class="h-6 w-6 text-slate-400" />
                                        {/if}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 font-semibold text-foreground">
                                            <QrCode class="h-4 w-4" />
                                            QRIS
                                        </div>
                                        <p class="mt-1 truncate text-sm text-muted-foreground">
                                            {paymentConfig.qris.merchant_name || 'Merchant Qbus'}
                                        </p>
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            {qrisStorageLabel(paymentConfig.qris.storage_status)}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        {#if paymentConfig.bank_transfer.enabled && paymentConfig.bank_transfer.accounts.length > 0}
                            {#each paymentConfig.bank_transfer.accounts as account}
                                <div class="rounded-lg border border-border/70 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-foreground">{account.bank_name}</p>
                                            <p class="mt-1 font-mono text-sm tracking-wide">{account.account_number}</p>
                                            <p class="mt-1 text-xs text-muted-foreground">a.n. {account.account_holder}</p>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg"
                                            onclick={() => copyText(account.account_number)}
                                            aria-label="Salin nomor rekening"
                                        >
                                            {#if copiedAccount === account.account_number}
                                                <Check class="h-4 w-4 text-emerald-600" />
                                            {:else}
                                                <Copy class="h-4 w-4" />
                                            {/if}
                                        </Button>
                                    </div>
                                </div>
                            {/each}
                        {/if}

                        {#if paymentMethodsCount === 0}
                            <div class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                Metode pembayaran belum aktif. Lengkapi pengaturan pembayaran di menu SaaS.
                            </div>
                        {/if}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <AlertTriangle class="h-5 w-5" />
                            Catatan Pembayaran
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-muted-foreground">
                        <p>Transfer atau scan QRIS sesuai nominal invoice agar verifikasi cepat.</p>
                        <p>Upload bukti pembayaran JPG, PNG, atau PDF maksimal {paymentConfig.upload_max_kb}KB.</p>
                        <p>{paidInvoices.length} invoice sudah lunas dari {invoices.length} invoice terakhir.</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Akses Akun</CardTitle>
                        <CardDescription>Mapping tenant, pool, dan role aktif akun ini.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3 rounded-lg border p-3">
                            <span class="text-muted-foreground">Tenant ID</span>
                            <span class="font-semibold text-foreground">#{accountAccess.tenant_id || tenantSub.tenant_id}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-lg border p-3">
                            <span class="text-muted-foreground">Pool terhubung</span>
                            <span class="font-semibold text-foreground">{accountAccess.pool_count}</span>
                        </div>
                        <div class="rounded-lg border p-3">
                            <span class="text-muted-foreground">Role</span>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                {#each accountAccess.role_names as role}
                                    <Badge variant="outline">{role}</Badge>
                                {:else}
                                    <span class="text-xs text-muted-foreground">Belum ada role</span>
                                {/each}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    {/if}
</div>

{#if showPaymentModal && payingInvoice}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4">
        <button
            type="button"
            class="absolute inset-0 cursor-default"
            aria-label="Tutup modal pembayaran"
            onclick={closePaymentModal}
        ></button>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-background shadow-xl">
            <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b bg-background p-4">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">Pembayaran Invoice</h3>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        {payingInvoice.invoice_number} - {formatRupiah(payingInvoice.amount)}
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                    onclick={closePaymentModal}
                    aria-label="Tutup modal"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-4 p-4">
                <div class="rounded-lg border bg-muted/30 p-3 text-center">
                    <p class="text-xs text-muted-foreground">Total pembayaran</p>
                    <p class="mt-1 text-2xl font-semibold text-foreground">{formatRupiah(payingInvoice.amount)}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 rounded-lg border p-1">
                    {#if paymentConfig.qris.enabled}
                        <button
                            type="button"
                            onclick={() => paymentTab = 'qris'}
                            class={`flex items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-medium ${paymentTab === 'qris' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'}`}
                        >
                            <QrCode class="h-4 w-4" />
                            QRIS
                        </button>
                    {/if}
                    {#if paymentConfig.bank_transfer.enabled && paymentConfig.bank_transfer.accounts.length > 0}
                        <button
                            type="button"
                            onclick={() => paymentTab = 'transfer'}
                            class={`flex items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-medium ${paymentTab === 'transfer' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'}`}
                        >
                            <Landmark class="h-4 w-4" />
                            Transfer
                        </button>
                    {/if}
                </div>

                {#if paymentTab === 'qris' && paymentConfig.qris.enabled}
                    <div class="space-y-3">
                        <p class="text-center text-sm text-muted-foreground">{paymentConfig.qris.note}</p>
                        <div class="flex justify-center rounded-lg border bg-white p-4">
                            <img
                                src={paymentConfig.qris.image_url}
                                alt="QRIS {paymentConfig.qris.merchant_name}"
                                class="h-52 w-52 object-contain"
                                onerror={handleQrisError}
                            />
                        </div>
                        <p class="text-center text-sm font-semibold text-foreground">
                            {paymentConfig.qris.merchant_name}
                        </p>
                    </div>
                {/if}

                {#if paymentTab === 'transfer' && paymentConfig.bank_transfer.enabled && paymentConfig.bank_transfer.accounts.length > 0}
                    <div class="space-y-2">
                        {#each paymentConfig.bank_transfer.accounts as account, index}
                            <button
                                type="button"
                                onclick={() => selectedBank = index}
                                class={`w-full rounded-lg border p-3 text-left transition ${selectedBank === index ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/30'}`}
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-foreground">{account.bank_name}</p>
                                        <p class="mt-1 font-mono text-lg tracking-wide">{account.account_number}</p>
                                        <p class="mt-1 text-xs text-muted-foreground">a.n. {account.account_holder}</p>
                                    </div>
                                    {#if selectedBank === index}
                                        <Check class="h-5 w-5 text-primary" />
                                    {/if}
                                </div>
                            </button>
                        {/each}
                    </div>
                {/if}

                <div class="border-t pt-4">
                    <Label class="mb-2 block text-sm font-medium">Upload Bukti Pembayaran</Label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 text-sm hover:bg-muted/30">
                        <Upload class="h-4 w-4 shrink-0 text-muted-foreground" />
                        {#if proofFile}
                            <span class="truncate font-medium text-emerald-700 dark:text-emerald-300">{proofFile.name}</span>
                        {:else}
                            <span class="truncate text-muted-foreground">
                                Pilih file JPG/PNG/PDF, max {paymentConfig.upload_max_kb}KB
                            </span>
                        {/if}
                        <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange={selectProofFile} />
                    </label>

                    {#if proofFile}
                        <Button class="mt-3 h-10 w-full rounded-lg" onclick={uploadProof} disabled={uploading}>
                            {#if uploading}
                                Mengupload...
                            {:else}
                                <Upload class="mr-2 h-4 w-4" />
                                Kirim Bukti Pembayaran
                            {/if}
                        </Button>
                    {/if}

                    {#if uploadMessage}
                        <div class={`mt-3 rounded-lg px-4 py-2 text-sm ${uploadMessage.toLowerCase().includes('berhasil') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-200' : 'bg-destructive/10 text-destructive'}`}>
                            {uploadMessage}
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
