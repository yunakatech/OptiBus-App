<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Langganan', href: '/subscription' }],
    };
</script>

<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { Banknote, Check, Copy, CreditCard, FileImage, History, Landmark, QrCode, Receipt, ShieldAlert, Upload, X } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';

    type TenantSub = { tenant_id: number; tenant_name: string; plan_id: number; plan_name: string; plan_slug: string; subscription_status: string; trial_ends_at: string | null; ends_at: string | null };
    type Invoice = { id: number; invoice_number: string; amount: number; status: string; due_date: string; paid_at: string | null; payment_method: string; payment_proof: string | null; created_at: string };
    type Plan = { id: number; name: string; slug: string; price_monthly: number; price_yearly: number; description: string };
    type BankAccount = { bank_name: string; account_number: string; account_holder: string; note: string };
    type PaymentConfig = {
        qris: { enabled: boolean; merchant_name: string; image_url: string; note: string };
        bank_transfer: { enabled: boolean; accounts: BankAccount[] };
        upload_max_kb: number;
    };

    const tenantSub = $derived((page.props.tenant_subscription ?? null) as TenantSub | null);
    const invoices = $derived((page.props.invoices ?? []) as Invoice[]);
    const currentPlan = $derived((page.props.current_plan ?? null) as Plan | null);
    const plans = $derived((page.props.plans ?? []) as Plan[]);
    const paymentConfig = $derived((page.props.payment_config ?? {
        qris: { enabled: false, merchant_name: '', image_url: '', note: '' },
        bank_transfer: { enabled: false, accounts: [] },
        upload_max_kb: 2048,
    }) as PaymentConfig);

    // Payment modal state
    let showPaymentModal = $state(false);
    let payingInvoice = $state<Invoice | null>(null);
    let paymentTab = $state<'qris' | 'transfer'>('qris');
    let selectedBank = $state(0);
    let paymentMethodLabel = $state('');
    let proofFile = $state<File | null>(null);
    let uploading = $state(false);
    let uploadMessage = $state('');

    function formatRupiah(v: number): string {
        return `Rp ${v.toLocaleString('id-ID')}`;
    }

    function statusBadge(status: string) {
        const m: Record<string, { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string }> = {
            trial: { variant: 'secondary', label: 'Trial' }, active: { variant: 'default', label: 'Aktif' },
            past_due: { variant: 'outline', label: 'Jatuh Tempo' }, suspended: { variant: 'destructive', label: 'Ditangguhkan' },
            canceled: { variant: 'destructive', label: 'Dibatalkan' }, expired: { variant: 'outline', label: 'Kadaluarsa' },
        };
        return m[status] ?? { variant: 'outline', label: status };
    }

    function invoiceStatusBadge(status: string) {
        const m: Record<string, { variant: 'default' | 'destructive' | 'outline' | 'secondary'; label: string }> = {
            pending: { variant: 'secondary', label: 'Menunggu' }, paid: { variant: 'default', label: 'Lunas' },
            overdue: { variant: 'destructive', label: 'Overdue' }, failed: { variant: 'destructive', label: 'Gagal' },
        };
        return m[status] ?? { variant: 'outline', label: status };
    }

    function openPayment(invoice: Invoice) {
        payingInvoice = invoice;
        paymentTab = paymentConfig.qris.enabled ? 'qris' : 'transfer';
        selectedBank = 0;
        paymentMethodLabel = '';
        proofFile = null;
        uploadMessage = '';
        showPaymentModal = true;
    }

    async function uploadProof() {
        if (!proofFile || !payingInvoice) return;
        uploading = true;
        uploadMessage = '';

        const formData = new FormData();
        formData.append('proof_file', proofFile);

        let methodLabel = paymentMethodLabel || (paymentTab === 'qris'
            ? `QRIS — ${paymentConfig.qris.merchant_name}`
            : `Transfer ${paymentConfig.bank_transfer.accounts[selectedBank]?.bank_name} ${paymentConfig.bank_transfer.accounts[selectedBank]?.account_number}`);
        formData.append('payment_method', methodLabel);

        try {
            const res = await fetch(`/api/subscription/upload-proof/${payingInvoice.id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': (page.props as any).csrf_token || '' },
                body: formData,
            });
            const data = await res.json();
            if (data.success) {
                uploadMessage = data.message;
                setTimeout(() => { showPaymentModal = false; window.location.reload(); }, 1500);
            } else {
                uploadMessage = data.error || 'Gagal upload.';
            }
        } catch (e: any) {
            uploadMessage = e.message || 'Network error';
        } finally {
            uploading = false;
        }
    }

    function copyText(text: string) {
        navigator.clipboard?.writeText(text);
        alert('Nomor rekening disalin!');
    }
</script>

<AppHead title="Langganan" />

<div class="space-y-6 pb-8 max-w-2xl mx-auto">
    <div>
        <h1 class="text-2xl font-bold">Langganan Anda</h1>
        <p class="text-muted-foreground mt-1">Kelola paket dan pembayaran</p>
    </div>

    {#if !tenantSub}
        <Card>
            <CardContent class="py-8 text-center">
                <ShieldAlert class="mx-auto h-10 w-10 text-muted-foreground mb-3" />
                <p class="text-muted-foreground">Tidak ada data langganan.</p>
            </CardContent>
        </Card>
    {:else}
        <!-- Current Plan -->
        <Card class="border-primary/30 bg-primary/5">
            <CardHeader>
                <div class="flex items-center gap-2">
                    <Badge variant={statusBadge(tenantSub.subscription_status).variant}>{statusBadge(tenantSub.subscription_status).label}</Badge>
                </div>
                <CardTitle class="text-2xl mt-2">{currentPlan?.name ?? tenantSub.plan_name}</CardTitle>
                <CardDescription>{currentPlan?.description ?? ''}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-bold">{formatRupiah(currentPlan?.price_monthly ?? 0)}</span>
                    <span class="text-muted-foreground">/bulan</span>
                </div>
                <div class="text-sm text-muted-foreground mt-1">
                    {formatRupiah(currentPlan?.price_yearly ?? 0)}/tahun
                </div>
                {#if tenantSub.subscription_status === 'trial'}
                    <p class="text-sm text-amber-600 mt-3">
                        Trial berakhir: {tenantSub.trial_ends_at ? new Date(tenantSub.trial_ends_at).toLocaleDateString('id-ID') : '—'}
                    </p>
                {:else if tenantSub.ends_at}
                    <p class="text-sm text-muted-foreground mt-3">
                        Periode: {new Date(tenantSub.ends_at).toLocaleDateString('id-ID')}
                    </p>
                {/if}
            </CardContent>
        </Card>

        <!-- Available Plans -->
        <Card>
            <CardHeader><CardTitle class="text-lg">Paket Tersedia</CardTitle></CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {#each plans as plan}
                        <div class="border rounded-lg p-4 {plan.slug === currentPlan?.slug ? 'border-primary bg-primary/5' : ''}">
                            <div class="font-semibold">{plan.name}</div>
                            <div class="text-lg font-bold mt-1">{formatRupiah(plan.price_monthly)}<span class="text-xs font-normal text-muted-foreground">/bln</span></div>
                            <p class="text-xs text-muted-foreground mt-2">{plan.description}</p>
                            {#if plan.slug !== currentPlan?.slug}
                                <Badge variant="outline" class="mt-3 w-full justify-center text-xs">
                                    {plan.price_monthly > (currentPlan?.price_monthly ?? 0) ? 'Hubungi admin untuk upgrade' : 'Hubungi admin untuk downgrade'}
                                </Badge>
                            {:else}
                                <Badge variant="default" class="mt-3 w-full justify-center">Paket Aktif</Badge>
                            {/if}
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>

        <!-- Invoice History -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg flex items-center gap-2"><Receipt class="h-5 w-5" /> Riwayat Invoice</CardTitle>
            </CardHeader>
            <CardContent>
                {#if invoices.length > 0}
                    <div class="space-y-3">
                        {#each invoices as inv}
                            <div class="flex items-center justify-between text-sm border rounded-lg p-3 hover:bg-muted/20">
                                <div>
                                    <div class="font-medium">{inv.invoice_number}</div>
                                    <div class="text-xs text-muted-foreground">
                                        Jatuh tempo: {new Date(inv.due_date).toLocaleDateString('id-ID')}
                                        {#if inv.payment_method} · {inv.payment_method}{/if}
                                    </div>
                                    {#if inv.payment_proof}
                                        <div class="text-xs text-green-600 mt-0.5">✓ Bukti terupload</div>
                                    {/if}
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-medium mb-1">{formatRupiah(inv.amount)}</div>
                                    {#if inv.status === 'paid'}
                                        <Badge variant="default" class="text-[10px]">Lunas</Badge>
                                    {:else if inv.status === 'pending'}
                                        {#if inv.payment_proof}
                                            <Badge variant="secondary" class="text-[10px]">Verifikasi</Badge>
                                        {:else}
                                            <Button size="sm" variant="outline" class="text-xs h-7 px-2" onclick={() => openPayment(inv)}>
                                                <Banknote class="h-3 w-3 mr-1" /> Bayar
                                            </Button>
                                        {/if}
                                    {:else}
                                        <Badge variant={invoiceStatusBadge(inv.status).variant} class="text-[10px]">{invoiceStatusBadge(inv.status).label}</Badge>
                                    {/if}
                                </div>
                            </div>
                        {/each}
                    </div>
                {:else}
                    <p class="text-sm text-muted-foreground text-center py-4">Belum ada invoice.</p>
                {/if}
            </CardContent>
        </Card>
    {/if}
</div>

<!-- Payment Modal -->
{#if showPaymentModal && payingInvoice}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick={() => showPaymentModal = false}>
        <div class="bg-background rounded-xl shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto" onclick={(e) => e.stopPropagation()}>
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-background z-10">
                <div>
                    <h3 class="font-bold text-lg">Pembayaran</h3>
                    <p class="text-xs text-muted-foreground">{payingInvoice.invoice_number} · {formatRupiah(payingInvoice.amount)}</p>
                </div>
                <button onclick={() => showPaymentModal = false} class="p-1 hover:bg-muted rounded-full"><X class="h-5 w-5" /></button>
            </div>

            <div class="p-4 space-y-4">
                <!-- Amount display -->
                <div class="text-center bg-muted rounded-lg py-3">
                    <div class="text-xs text-muted-foreground">Total Pembayaran</div>
                    <div class="text-2xl font-bold">{formatRupiah(payingInvoice.amount)}</div>
                </div>

                <!-- Payment method tabs -->
                <div class="flex gap-1 border rounded-lg p-1">
                    {#if paymentConfig.qris.enabled}
                        <button
                            onclick={() => paymentTab = 'qris'}
                            class="flex-1 flex items-center justify-center gap-1 py-2 text-sm rounded-md {paymentTab === 'qris' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground'}"
                        >
                            <QrCode class="h-4 w-4" /> QRIS
                        </button>
                    {/if}
                    {#if paymentConfig.bank_transfer.enabled}
                        <button
                            onclick={() => paymentTab = 'transfer'}
                            class="flex-1 flex items-center justify-center gap-1 py-2 text-sm rounded-md {paymentTab === 'transfer' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground'}"
                        >
                            <Landmark class="h-4 w-4" /> Transfer
                        </button>
                    {/if}
                </div>

                <!-- QRIS Tab -->
                {#if paymentTab === 'qris' && paymentConfig.qris.enabled}
                    <div class="space-y-3">
                        <div class="text-sm text-muted-foreground text-center">{paymentConfig.qris.note}</div>
                        <div class="bg-white rounded-lg p-4 flex flex-col items-center">
                            <img
                                src={paymentConfig.qris.image_url}
                                alt="QRIS {paymentConfig.qris.merchant_name}"
                                class="w-48 h-48 object-contain"
                                onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22192%22 height=%22192%22><rect fill=%22%23f0f0f0%22 width=%22192%22 height=%22192%22/><text x=%2296%22 y=%22100%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2214%22>QRIS</text></svg>'"
                            />
                        </div>
                        <div class="text-center text-sm font-medium">{paymentConfig.qris.merchant_name}</div>
                        <div class="text-center text-xs text-amber-600 bg-amber-50 rounded-lg py-2 px-3">
                            ⚠️ Masukkan nominal <strong>{formatRupiah(payingInvoice.amount)}</strong> saat scan QRIS
                        </div>
                    </div>
                {/if}

                <!-- Transfer Tab -->
                {#if paymentTab === 'transfer' && paymentConfig.bank_transfer.enabled}
                    <div class="space-y-2">
                        {#each paymentConfig.bank_transfer.accounts as account, i}
                            <button
                                onclick={() => selectedBank = i}
                                class="w-full text-left border rounded-lg p-3 hover:bg-muted/20 transition-colors {selectedBank === i ? 'border-primary bg-primary/5' : ''}"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold">{account.bank_name}</div>
                                        <div class="text-lg font-mono tracking-wider">{account.account_number}</div>
                                        <div class="text-xs text-muted-foreground">a.n. {account.account_holder}</div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick={(e) => { e.stopPropagation(); copyText(account.account_number); }} class="p-1 text-muted-foreground hover:text-foreground" title="Salin nomor rekening">
                                            <Copy class="h-4 w-4" />
                                        </button>
                                        {#if selectedBank === i}
                                            <Check class="h-5 w-5 text-primary" />
                                        {/if}
                                    </div>
                                </div>
                                <div class="text-xs text-muted-foreground mt-2">Transfer tepat <strong>{formatRupiah(payingInvoice.amount)}</strong></div>
                            </button>
                        {/each}
                    </div>
                {/if}

                <!-- Proof Upload -->
                <div class="border-t pt-4">
                    <Label class="text-sm font-medium mb-2 block">Upload Bukti Pembayaran</Label>
                    <div class="flex items-center gap-3">
                        <label class="flex-1 flex items-center gap-2 border rounded-lg px-4 py-3 cursor-pointer hover:bg-muted/20 text-sm">
                            <Upload class="h-4 w-4 text-muted-foreground" />
                            {#if proofFile}
                                <span class="text-green-600 truncate">{proofFile.name}</span>
                            {:else}
                                <span class="text-muted-foreground">Pilih file (JPG/PNG/PDF, max {paymentConfig.upload_max_kb}KB)</span>
                            {/if}
                            <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="hidden" onchange={(e) => { proofFile = e.target?.files?.[0] ?? null; }} />
                        </label>
                    </div>
                    {#if proofFile}
                        <Button class="w-full mt-3" onclick={uploadProof} disabled={uploading}>
                            {#if uploading}
                                Mengupload...
                            {:else}
                                <Upload class="h-4 w-4 mr-2" /> Kirim Bukti Pembayaran
                            {/if}
                        </Button>
                    {/if}
                    {#if uploadMessage}
                        <div class="mt-3 text-sm rounded-lg px-4 py-2 {uploadMessage.includes('berhasil') ? 'bg-green-50 text-green-700' : 'bg-destructive/10 text-destructive'}">
                            {uploadMessage}
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
