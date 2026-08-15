<script module lang="ts">
    export const layout = {
        breadcrumbs: [{ title: 'Langganan', href: '/subscription' }],
    };
</script>

<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        AlertTriangle,
        ArrowRight,
        CheckCircle2,
        CircleHelp,
        CreditCard,
        ExternalLink,
        Receipt,
        ShieldAlert,
        Sparkles,
    } from 'lucide-svelte';
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
        due_date: string | null;
        paid_at: string | null;
        payment_method: string;
        payment_gateway: string;
        gateway_reference: string;
        gateway_checkout_url: string;
        gateway_status: string;
        gateway_paid_at: string | null;
        gateway_error_message?: string;
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

    type BillingAccess = {
        allowed: boolean;
        locked: boolean;
        reason: string;
        plan_slug: string;
        plan_name: string;
        is_trial: boolean;
        trial_ends_at: string | null;
        ends_at: string | null;
        redirect_url: string;
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

    const tenantSub = $derived(
        (page.props.tenant_subscription ?? null) as TenantSub | null,
    );
    const invoices = $derived((page.props.invoices ?? []) as Invoice[]);
    const currentPlan = $derived(
        (page.props.current_plan ?? null) as Plan | null,
    );
    const plans = $derived((page.props.plans ?? []) as Plan[]);
    const accountAccess = $derived(
        (page.props.account_access ?? {
            tenant_id: 0,
            pool_count: 0,
            role_names: [],
        }) as AccountAccess,
    );
    const billingAccess = $derived(
        (page.props.auth?.billing_access ?? page.props.billing_access ?? null) as
            | BillingAccess
            | null,
    );
    const currentPlanSlug = $derived(
        currentPlan?.slug ?? tenantSub?.plan_slug ?? '',
    );
    const currentPlanMonthly = $derived(
        Number(currentPlan?.price_monthly ?? 0),
    );

    const payableInvoice = $derived(
        invoices.find((invoice) =>
            ['pending', 'overdue', 'failed'].includes(invoice.status),
        ) ?? null,
    );
    const latestPaidInvoice = $derived(
        invoices.find((invoice) => invoice.status === 'paid') ?? null,
    );
    const latestCanceledInvoice = $derived(
        invoices.find((invoice) => invoice.status === 'canceled') ?? null,
    );
    const canRetryCheckout = $derived(
        Boolean(
            latestCanceledInvoice &&
                tenantSub?.subscription_status === 'pending_payment',
        ),
    );
    const subscriptionMeta = $derived(
        statusBadge(tenantSub?.subscription_status ?? ''),
    );
    const subscriptionMetaLabel = $derived(
        tenantSub?.subscription_status === 'trial' &&
            tenantSub.plan_slug === 'starter'
            ? 'Trial Starter'
            : subscriptionMeta.label,
    );
    const canAccessDashboard = $derived(
        Boolean(billingAccess?.allowed),
    );
    const paymentLinkReady = $derived(
        Boolean(payableInvoice?.gateway_checkout_url),
    );
    const gatewayHasError = $derived(
        payableInvoice?.gateway_status === 'payment_link_error' ||
            (payableInvoice && !paymentLinkReady),
    );
    const canChoosePlan = $derived(Boolean(!payableInvoice || gatewayHasError));
    const activeBillingTitle = $derived(
        payableInvoice
            ? gatewayHasError
                ? 'Tautan pembayaran belum siap'
                : 'Pembayaran perlu diselesaikan'
            : latestPaidInvoice
              ? 'Langganan Anda sudah aktif'
              : 'Belum ada tagihan aktif',
    );
    const activeBillingDescription = $derived(
            payableInvoice
            ? gatewayHasError
                ? payableInvoice.gateway_error_message ||
                  'Tagihan sudah dibuat, tetapi tautan pembayaran belum tersedia. Silakan hubungi admin untuk dibantu.'
                : `Selesaikan pembayaran sebelum ${formatDate(payableInvoice.due_date)} agar paket dapat digunakan.`
            : latestPaidInvoice
              ? `Pembayaran terakhir diterima pada ${formatDate(latestPaidInvoice.paid_at)}.`
              : latestCanceledInvoice
                ? 'Tagihan sebelumnya sudah dibatalkan. Pilih paket untuk mengajukan pembayaran baru.'
              : 'Pilih paket di bawah untuk memulai langganan.',
    );
    let checkoutPlanSlug = $state('');

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
            pending_payment: {
                variant: 'outline',
                label: 'Menunggu Pembayaran',
            },
            active: { variant: 'default', label: 'Aktif' },
            past_due: { variant: 'outline', label: 'Jatuh Tempo' },
            suspended: { variant: 'destructive', label: 'Ditangguhkan' },
            canceled: { variant: 'destructive', label: 'Dibatalkan' },
            expired: { variant: 'outline', label: 'Kedaluwarsa' },
        };

        return (
            map[status] ?? {
                variant: 'outline',
                label: status || 'Belum aktif',
            }
        );
    }

    function invoiceStatusBadge(invoice: Invoice): BadgeMeta {
        if (
            invoice.status === 'pending' &&
            invoice.gateway_status === 'payment_link_error'
        ) {
            return { variant: 'outline', label: 'Payment Link Error' };
        }

        const map: Record<string, BadgeMeta> = {
            pending: { variant: 'secondary', label: 'Pending' },
            paid: { variant: 'default', label: 'Paid' },
            overdue: { variant: 'destructive', label: 'Overdue' },
            failed: { variant: 'destructive', label: 'Failed' },
            refunded: { variant: 'outline', label: 'Refunded' },
            canceled: { variant: 'outline', label: 'Dibatalkan' },
        };

        return (
            map[invoice.status] ?? {
                variant: 'outline',
                label: invoice.status || '-',
            }
        );
    }

    function gatewayStatusLabel(invoice: Invoice): string {
        const status = invoice.gateway_status || invoice.status;
        const map: Record<string, string> = {
            creating_link: 'Membuat link',
            payment_link_error: 'Link belum tersedia',
            pending: 'Menunggu pembayaran',
            paid: 'Paid',
            failed: 'Failed',
            expired: 'Expired',
            canceled: 'Canceled',
        };

        return map[status] ?? status;
    }

    function planStateLabel(plan: Plan): string {
        if (plan.slug === currentPlanSlug) {
            if (canRetryCheckout) {
                return 'Bayar paket ini lagi';
            }

            return tenantSub?.subscription_status === 'trial'
                ? 'Trial Starter'
                : 'Paket aktif';
        }

        if (payableInvoice && !gatewayHasError) {
            return 'Selesaikan tagihan aktif dulu';
        }

        if (gatewayHasError) {
            return 'Coba pilih paket ini lagi';
        }

        return plan.price_monthly > currentPlanMonthly ? 'Pilih paket ini' : 'Pilih paket';
    }

    function planButtonVariant(plan: Plan): 'default' | 'outline' {
        return plan.slug === currentPlanSlug ? 'default' : 'outline';
    }

    function planCardBadge(plan: Plan): BadgeMeta {
        if (plan.slug === currentPlanSlug) {
            return {
                variant: 'default',
                label:
                    canRetryCheckout
                        ? 'Checkout ulang'
                        : tenantSub?.subscription_status === 'trial'
                        ? 'Aktif Trial'
                        : 'Paket Aktif',
            };
        }

        if (payableInvoice && !gatewayHasError) {
            return {
                variant: 'outline',
                label: 'Tunggu Invoice',
            };
        }

        if (gatewayHasError) {
            return { variant: 'outline', label: 'Coba Lagi' };
        }

        return plan.price_monthly > currentPlanMonthly
            ? { variant: 'secondary', label: 'Upgrade' }
            : { variant: 'outline', label: 'Ganti Paket' };
    }

    function planCardClass(plan: Plan): string {
        if (plan.slug === currentPlanSlug) {
            return 'border-primary/70 bg-[linear-gradient(180deg,rgba(239,246,255,0.95),rgba(224,231,255,0.65))] shadow-[0_18px_45px_-28px_rgba(14,165,233,0.35)] ring-1 ring-primary/15 dark:border-sky-400/30 dark:bg-[linear-gradient(180deg,rgba(8,15,30,0.96),rgba(15,23,42,0.84))] dark:shadow-[0_18px_45px_-28px_rgba(2,132,199,0.28)]';
        }

        if (payableInvoice && !gatewayHasError) {
            return 'border-border/70 bg-muted/20';
        }

        if (gatewayHasError) {
            return 'border-amber-300/70 bg-amber-50/60 dark:border-amber-400/25 dark:bg-amber-950/20';
        }

        return plan.price_monthly > currentPlanMonthly
            ? 'border-sky-300/70 bg-sky-50/60 dark:border-sky-400/25 dark:bg-sky-950/20'
            : 'border-border/70 bg-background';
    }

    function planHint(plan: Plan): string {
        if (plan.slug === currentPlanSlug) {
            if (canRetryCheckout) {
                return 'Tagihan sebelumnya dibatalkan. Bayar lagi untuk mengaktifkan paket ini.';
            }

            return 'Paket yang sedang dipakai tenant ini.';
        }

        if (payableInvoice && !gatewayHasError) {
            return 'Selesaikan tagihan aktif sebelum memilih paket baru.';
        }

        if (gatewayHasError) {
            return 'Tautan pembayaran sebelumnya gagal dibuat. Coba lagi atau hubungi admin.';
        }

        return plan.price_monthly > currentPlanMonthly
            ? 'Cocok untuk naik kelas operasional.'
            : 'Bisa dipilih tanpa menunggu invoice aktif.';
    }

    function startCheckout(plan: Plan): void {
        if (checkoutPlanSlug !== '') {
            return;
        }

        checkoutPlanSlug = plan.slug;
        router.post(
            '/subscription/checkout',
            {
                plan_slug: plan.slug,
                billing_interval: 'monthly',
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    checkoutPlanSlug = '';
                },
            },
        );
    }
</script>

<AppHead title="Langganan" />

<div
    data-content-density="compact"
    class="min-h-full space-y-4 overflow-x-hidden p-3 pb-24 md:p-4 md:pb-4 lg:space-y-3"
>
    <div
        class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
    >
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                Langganan
            </h1>
            <p class="hidden text-sm text-muted-foreground sm:block">
                Kelola paket, pembayaran, dan akses operasional travel Anda.
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
                <h2 class="mt-4 text-lg font-semibold text-foreground">
                    Data langganan belum tersedia
                </h2>
                <p class="mx-auto mt-2 hidden max-w-md text-sm text-muted-foreground sm:block">
                    Tenant belum memiliki paket aktif. Pilih paket dari pricing
                    atau hubungi admin SaaS.
                </p>
                <Button asChild class="mt-5 rounded-lg">
                    {#snippet children(props)}
                        <Link {...props} href="/pricing">Lihat Pricing</Link>
                    {/snippet}
                </Button>
            </CardContent>
        </Card>
    {:else}
        <div
            class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-[linear-gradient(120deg,#0f766e_0%,#155e75_58%,#164e63_100%)] p-4 text-white shadow-[0_24px_60px_-32px_rgba(15,118,110,0.7)] sm:p-5 lg:p-6"
        >
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-amber-200/15 blur-3xl"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-cyan-200/10 blur-3xl"
            ></div>
            <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/12 ring-1 ring-white/20"
                    >
                        <Sparkles class="h-5 w-5 text-amber-200" />
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-100/80">
                            Langkah berikutnya
                        </p>
                        {#if payableInvoice && paymentLinkReady}
                            <h2 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                                Lanjutkan pembayaran paket Anda
                            </h2>
                            <p class="mt-1 max-w-xl text-sm leading-6 text-cyan-50/85">
                                Klik tombol pembayaran, selesaikan di halaman Mayar,
                                lalu status paket akan diperbarui otomatis.
                            </p>
                        {:else if payableInvoice}
                            <h2 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                                Tautan pembayaran sedang disiapkan
                            </h2>
                            <p class="mt-1 max-w-xl text-sm leading-6 text-cyan-50/85">
                                Tautan belum tersedia. Periksa bagian pembayaran di
                                bawah atau hubungi admin SaaS.
                            </p>
                        {:else if latestCanceledInvoice}
                            <h2 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                                Ajukan pembayaran baru
                            </h2>
                            <p class="mt-1 max-w-xl text-sm leading-6 text-cyan-50/85">
                                Tagihan sebelumnya sudah tidak berlaku. Pilih paket
                                untuk membuat tagihan baru.
                            </p>
                        {:else if latestPaidInvoice}
                            <h2 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                                Paket Anda siap digunakan
                            </h2>
                            <p class="mt-1 max-w-xl text-sm leading-6 text-cyan-50/85">
                                Langganan aktif. Anda dapat melihat masa berlaku dan
                                riwayat pembayaran di halaman ini.
                            </p>
                        {:else}
                            <h2 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                                Pilih paket untuk mulai
                            </h2>
                            <p class="mt-1 max-w-xl text-sm leading-6 text-cyan-50/85">
                                Bandingkan paket di bawah, pilih yang sesuai, lalu
                                ikuti langkah pembayaran.
                            </p>
                        {/if}
                    </div>
                </div>
                {#if payableInvoice && paymentLinkReady}
                    <Button
                        asChild
                        class="h-10 shrink-0 rounded-xl bg-white px-4 font-semibold text-cyan-900 shadow-lg shadow-cyan-950/15 hover:bg-cyan-50"
                    >
                        {#snippet children(props)}
                            <a
                                {...props}
                                href={payableInvoice.gateway_checkout_url}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Buka pembayaran
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </a>
                        {/snippet}
                    </Button>
                {:else if !latestPaidInvoice}
                    <Button
                        asChild
                        variant="outline"
                        class="h-10 shrink-0 rounded-xl border-white/30 bg-white/10 px-4 font-semibold text-white hover:bg-white/20 hover:text-white"
                    >
                        {#snippet children(props)}
                            <a {...props} href="#available-plans">
                                Lihat pilihan paket
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </a>
                        {/snippet}
                    </Button>
                {/if}
            </div>
        </div>

        <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-3 lg:space-y-4">
                <Card class="overflow-hidden">
                    <CardHeader class="border-b bg-muted/20">
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <Badge variant={subscriptionMeta.variant}
                                        >{subscriptionMetaLabel}</Badge
                                    >
                                    <span class="text-xs text-muted-foreground"
                                        >{tenantSub.tenant_name}</span
                                    >
                                </div>
                                <CardTitle class="mt-2 text-xl lg:text-2xl">
                                    Paket {currentPlan?.name ?? tenantSub.plan_name}
                                </CardTitle>
                                <CardDescription class="mt-1">
                                    {currentPlan?.description ||
                                        'Paket untuk membantu mengatur operasional travel Anda.'}
                                </CardDescription>
                            </div>
                            <div
                                class="rounded-lg border bg-background px-3 py-2 text-left md:text-right"
                            >
                                <p class="text-xs text-muted-foreground">
                                    Harga per bulan
                                </p>
                                <p
                                    class="text-lg font-semibold text-foreground"
                                >
                                    {formatRupiah(
                                        currentPlan?.price_monthly ?? 0,
                                    )}
                                </p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-3 p-3 md:grid-cols-3 lg:p-4">
                        <div class="rounded-lg border border-border/70 p-2.5 lg:p-3">
                            <p class="text-xs text-muted-foreground">
                                Masa berlaku
                            </p>
                            <p class="mt-1 font-semibold text-foreground">
                                {tenantSub.subscription_status === 'trial'
                                    ? formatDate(tenantSub.trial_ends_at)
                                    : formatDate(tenantSub.ends_at)}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/70 p-2.5 lg:p-3">
                            <p class="text-xs text-muted-foreground">
                                Tagihan saat ini
                            </p>
                            <p class="mt-1 font-semibold text-foreground">
                                {payableInvoice
                                    ? payableInvoice.invoice_number
                                    : latestPaidInvoice
                                      ? 'Sudah dibayar'
                                      : 'Belum ada'}
                            </p>
                        </div>
                        <div class="rounded-lg border border-border/70 p-2.5 lg:p-3">
                            <p class="text-xs text-muted-foreground">Status pembayaran</p>
                            <p class="mt-1 font-semibold text-foreground">
                                {payableInvoice
                                    ? 'Menunggu pembayaran'
                                    : latestPaidInvoice
                                      ? 'Lunas'
                                      : 'Belum dimulai'}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class={payableInvoice
                        ? 'border-sky-200 bg-sky-50/50 dark:border-sky-400/20 dark:bg-sky-950/10'
                        : 'overflow-hidden'}
                >
                    <CardHeader class="border-b bg-background/70">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                        >
                            <div class="flex gap-3">
                                <div
                                    class={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${
                                        payableInvoice
                                            ? gatewayHasError
                                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-100'
                                                : 'bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-100'
                                            : latestPaidInvoice
                                              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-100'
                                              : 'bg-muted text-muted-foreground'
                                    }`}
                                >
                                    {#if payableInvoice && gatewayHasError}
                                        <AlertTriangle class="h-5 w-5" />
                                    {:else if latestPaidInvoice}
                                        <CheckCircle2 class="h-5 w-5" />
                                    {:else}
                                        <Receipt class="h-5 w-5" />
                                    {/if}
                                </div>
                                <div>
                                    <CardTitle class="text-lg lg:text-xl"
                                        >{activeBillingTitle}</CardTitle
                                    >
                                    <CardDescription class="mt-1"
                                        >{activeBillingDescription}</CardDescription
                                    >
                                </div>
                            </div>
                            {#if payableInvoice}
                                <div
                                    class="rounded-lg border bg-background px-4 py-3 text-left md:text-right"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Jumlah yang perlu dibayar
                                    </p>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-foreground"
                                    >
                                        {formatRupiah(payableInvoice.amount)}
                                    </p>
                                </div>
                            {/if}
                        </div>
                    </CardHeader>

                    {#if payableInvoice}
                        <CardContent
                            class="grid gap-3 p-3 lg:grid-cols-[minmax(0,1fr)_240px] lg:p-4"
                        >
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div
                                    class="rounded-lg border bg-background p-2.5 lg:p-3"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Nomor tagihan
                                    </p>
                                    <p
                                        class="mt-1 font-semibold text-foreground"
                                    >
                                        {payableInvoice.invoice_number}
                                    </p>
                                </div>
                                <div class="rounded-lg border bg-background p-2.5 lg:p-3">
                                    <p class="text-xs text-muted-foreground">
                                        Batas pembayaran
                                    </p>
                                    <p
                                        class="mt-1 font-semibold text-foreground"
                                    >
                                        {formatDate(payableInvoice.due_date)}
                                    </p>
                                </div>
                                <div class="rounded-lg border bg-background p-2.5 lg:p-3">
                                    <p class="text-xs text-muted-foreground">
                                        Status
                                    </p>
                                    <p
                                        class="mt-1 font-semibold text-foreground"
                                    >
                                        {gatewayStatusLabel(payableInvoice)}
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-lg border bg-background p-2.5 lg:p-3">
                                {#if paymentLinkReady}
                                    <Button
                                        asChild
                                        class="h-9 w-full rounded-lg lg:h-10"
                                    >
                                        {#snippet children(props)}
                                            <a
                                                {...props}
                                                href={payableInvoice.gateway_checkout_url}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                <CreditCard
                                                    class="mr-2 h-4 w-4"
                                                />
                                                Lanjutkan pembayaran
                                            </a>
                                        {/snippet}
                                    </Button>
                                    <p
                                        class="mt-2 text-center text-xs text-muted-foreground"
                                    >
                                        Setelah pembayaran berhasil, status paket
                                        akan diperbarui otomatis.
                                    </p>
                                {:else}
                                    <div
                                        class="rounded-lg border border-dashed p-3 text-sm text-muted-foreground"
                                    >
                                        <p>
                                            Tautan pembayaran belum tersedia.
                                            Silakan hubungi admin SaaS untuk
                                            mendapatkan bantuan.
                                        </p>
                                        {#if payableInvoice.gateway_error_message}
                                            <p class="mt-2 font-medium text-amber-700 dark:text-amber-300">
                                                {payableInvoice.gateway_error_message}
                                            </p>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        </CardContent>
                    {:else if !latestPaidInvoice && tenantSub.subscription_status === 'pending_payment'}
                        <CardContent class="p-4">
                            <div
                                class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground"
                            >
                                {#if latestCanceledInvoice}
                                    Tagihan sebelumnya dibatalkan karena melewati
                                    batas waktu pembayaran. Pilih paket di bawah
                                    untuk membuat tagihan baru.
                                {:else}
                                    Belum ada tagihan. Pilih paket di bawah untuk
                                    memulai pembayaran.
                                {/if}
                            </div>
                        </CardContent>
                    {/if}
                </Card>

                <Card id="available-plans">
                    <CardHeader>
                        <CardTitle class="text-lg lg:text-xl"
                            >Pilih paket Anda</CardTitle
                        >
                        <CardDescription
                            >Pilih paket berdasarkan kebutuhan operasional.
                            Setelah memilih, Anda akan diarahkan ke halaman
                            pembayaran yang aman.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="p-3 lg:p-4">
                        <div
                            class="mb-3 rounded-lg border border-border/70 bg-muted/30 px-3 py-2 text-xs text-muted-foreground"
                        >
                            {payableInvoice
                                ? 'Ada tagihan yang belum selesai. Selesaikan pembayaran itu terlebih dahulu.'
                                : latestCanceledInvoice
                                  ? 'Tagihan sebelumnya sudah dibatalkan. Pilih paket untuk membuat tagihan baru.'
                                  : 'Anda dapat mengganti paket kapan saja. Perubahan aktif setelah pembayaran berhasil.'}
                        </div>
                        <div class="grid gap-3 md:grid-cols-3">
                            {#each plans as plan}
                                <div
                                    class={`relative overflow-hidden rounded-lg border p-2.5 transition-all duration-200 lg:p-3 ${planCardClass(plan)}`}
                                >
                                    {#if plan.slug === currentPlanSlug}
                                        <div class="absolute inset-x-0 top-0 h-1 bg-primary"></div>
                                        <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-sky-400/12 blur-3xl"></div>
                                    {/if}
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-semibold text-foreground">
                                                    {plan.name}
                                                </p>
                                                <Badge
                                                    variant={planCardBadge(plan).variant}
                                                    class={plan.slug === currentPlanSlug
                                                        ? 'rounded-full border-primary/20 bg-primary text-primary-foreground shadow-sm'
                                                        : ''}
                                                >
                                                    {planCardBadge(plan).label}
                                                </Badge>
                                            </div>
                                            <p class="mt-1 text-[11px] leading-5 text-muted-foreground">
                                                {planHint(plan)}
                                            </p>
                                        </div>
                                        {#if plan.slug === currentPlanSlug}
                                            <CheckCircle2
                                                class="mt-0.5 h-4 w-4 shrink-0 text-primary"
                                            />
                                        {/if}
                                    </div>
                                    <p class="mt-2 text-base font-semibold text-foreground lg:text-lg">
                                        {formatRupiah(plan.price_monthly)}
                                        <span
                                            class="text-xs font-normal text-muted-foreground"
                                            >/bulan</span
                                        >
                                    </p>
                                    <p
                                        class="mt-2 min-h-10 text-[11px] leading-5 text-muted-foreground lg:text-xs"
                                    >
                                        {plan.description}
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span class={`rounded-full border px-2 py-0.5 text-[10px] font-medium ${plan.slug === currentPlanSlug ? 'border-primary/20 bg-white/80 text-primary dark:border-sky-400/20 dark:bg-slate-900/70 dark:text-sky-200' : 'border-border/70 bg-background text-muted-foreground'}`}>
                                            {plan.price_monthly > currentPlanMonthly
                                                ? `+${formatRupiah(plan.price_monthly - currentPlanMonthly)}`
                                                : currentPlanSlug === plan.slug
                                                  ? 'Sedang aktif'
                                                  : 'Setara atau lebih rendah'}
                                        </span>
                                        <span class={`rounded-full border px-2 py-0.5 text-[10px] font-medium ${plan.slug === currentPlanSlug ? 'border-primary/20 bg-white/80 text-primary dark:border-sky-400/20 dark:bg-slate-900/70 dark:text-sky-200' : 'border-border/70 bg-background text-muted-foreground'}`}>
                                            {plan.slug === currentPlanSlug
                                                ? canRetryCheckout
                                                    ? 'Siap dibayar lagi'
                                                    : 'Paket saat ini'
                                                : payableInvoice
                                                  ? 'Menunggu pembayaran'
                                                  : 'Bisa dipilih'}
                                        </span>
                                    </div>
                                    <Button
                                        type="button"
                                        variant={planButtonVariant(plan)}
                                        class={`mt-3 h-8 w-full rounded-lg lg:h-9 ${plan.slug === currentPlanSlug ? 'shadow-sm shadow-primary/15' : ''}`}
                                        disabled={checkoutPlanSlug !== '' ||
                                            (plan.slug === currentPlanSlug &&
                                                !canRetryCheckout) ||
                                            !canChoosePlan}
                                        onclick={() => startCheckout(plan)}
                                    >
                                        {checkoutPlanSlug === plan.slug
                                            ? 'Membuat tagihan...'
                                            : planStateLabel(plan)}
                                    </Button>
                                </div>
                            {/each}
                        </div>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden">
                    <CardHeader class="border-b border-border/60 bg-muted/20">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/70 bg-background/80 text-primary shadow-sm"
                            >
                                <CircleHelp class="h-5 w-5" />
                            </span>
                            Cara berlangganan
                        </CardTitle>
                        <CardDescription
                            >Ikuti tiga langkah sederhana berikut.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="p-3 lg:p-4">
                        <ol class="space-y-3 text-sm">
                            <li class="flex gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">1</span>
                                <span><strong class="font-semibold text-foreground">Pilih paket</strong><br /><span class="text-xs text-muted-foreground">Sesuaikan dengan kebutuhan travel Anda.</span></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">2</span>
                                <span><strong class="font-semibold text-foreground">Selesaikan pembayaran</strong><br /><span class="text-xs text-muted-foreground">Ikuti petunjuk pembayaran online.</span></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">3</span>
                                <span><strong class="font-semibold text-foreground">Mulai gunakan</strong><br /><span class="text-xs text-muted-foreground">Akses paket aktif setelah pembayaran terverifikasi.</span></span>
                            </li>
                        </ol>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden">
                    <CardHeader class="border-b border-border/60 bg-muted/20">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/70 bg-background/80 text-primary shadow-sm"
                            >
                                <Receipt class="h-5 w-5" />
                            </span>
                            Riwayat pembayaran
                        </CardTitle>
                        <CardDescription>
                            Semua tagihan dan status pembayaran Anda tersimpan di
                            sini.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        {#if invoices.length > 0}
                            <div
                                class="table-container hidden md:block"
                            >
                                <table class="w-full table-fixed text-[12px] lg:text-sm">
                                    <thead
                                        class="bg-muted/70 text-left text-[11px] uppercase tracking-wide text-muted-foreground"
                                    >
                                        <tr>
                                            <th class="w-[24%] px-3 py-2.5">Tagihan</th>
                                            <th class="w-[14%] px-3 py-2.5">Cara bayar</th>
                                            <th class="w-[16%] px-3 py-2.5"
                                                >Batas bayar</th
                                            >
                                            <th class="w-[18%] px-3 py-2.5 text-right"
                                                >Nominal</th
                                            >
                                            <th class="w-[16%] px-3 py-2.5">Status</th>
                                            <th class="w-[12%] px-3 py-2.5 text-right"
                                                >Aksi</th
                                            >
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/70 text-[13px]">
                                        {#each invoices as invoice}
                                            <tr class="transition-colors hover:bg-muted/30">
                                                <td class="px-3 py-2.5 align-top">
                                                    <p class="truncate font-semibold tracking-tight text-foreground">
                                                        {invoice.invoice_number}
                                                    </p>
                                                    <p class="mt-1 text-xs text-muted-foreground">
                                                        Dibuat {formatDate(
                                                            invoice.created_at,
                                                        )}
                                                    </p>
                                                </td>
                                                <td class="px-3 py-2.5 align-top">
                                                    <span class="inline-flex rounded-full border border-border/70 bg-background px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                                        {invoice.payment_gateway ||
                                                            'Mayar'}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2.5 align-top text-muted-foreground">
                                                    {formatDate(
                                                        invoice.due_date,
                                                    )}
                                                </td>
                                                <td class="px-3 py-2.5 text-right font-semibold tracking-tight">
                                                    {formatRupiah(
                                                        invoice.amount,
                                                    )}
                                                </td>
                                                <td class="px-3 py-2.5 align-top">
                                                    <Badge
                                                        variant={invoiceStatusBadge(
                                                            invoice,
                                                        ).variant}
                                                        class="rounded-full px-2 py-0.5"
                                                    >
                                                        {invoiceStatusBadge(
                                                            invoice,
                                                        ).label}
                                                    </Badge>
                                                </td>
                                                <td class="px-3 py-2.5 text-right">
                                                    {#if ['pending', 'overdue', 'failed'].includes(invoice.status) && invoice.gateway_checkout_url}
                                                        <a
                                                            href={invoice.gateway_checkout_url}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            class="inline-flex items-center gap-1 text-xs font-semibold text-primary transition-colors hover:text-primary/80 hover:underline"
                                                        >
                                                            Lanjutkan pembayaran
                                                            <ExternalLink
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </a>
                                                    {:else}
                                                        <span
                                                            class="text-xs text-muted-foreground"
                                                            >{gatewayStatusLabel(
                                                                invoice,
                                                            )}</span
                                                        >
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>

                            <div class="space-y-3 p-4 md:hidden">
                                {#each invoices as invoice}
                                    <div class="rounded-xl border border-border/70 bg-background/80 p-3 shadow-sm">
                                        <div
                                            class="flex items-start justify-between gap-3"
                                        >
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold tracking-tight text-foreground">
                                                    {invoice.invoice_number}
                                                </p>
                                                <p class="mt-1 text-xs text-muted-foreground">
                                                    Pembayaran - {formatDate(
                                                        invoice.due_date,
                                                    )}
                                                </p>
                                            </div>
                                            <Badge
                                                variant={invoiceStatusBadge(
                                                    invoice,
                                                ).variant}
                                                class="rounded-full px-2.5 py-0.5"
                                            >
                                                {invoiceStatusBadge(invoice)
                                                    .label}
                                            </Badge>
                                        </div>
                                        <div
                                            class="mt-3 flex items-center justify-between gap-3"
                                        >
                                            <span class="font-semibold tracking-tight text-foreground"
                                                >{formatRupiah(
                                                    invoice.amount,
                                                )}</span
                                            >
                                            {#if ['pending', 'overdue', 'failed'].includes(invoice.status) && invoice.gateway_checkout_url}
                                                <a
                                                    href={invoice.gateway_checkout_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary transition-colors hover:text-primary/80 hover:underline"
                                                >
                                                    Lanjutkan pembayaran
                                                    <ExternalLink
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </a>
                                            {:else}
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >{gatewayStatusLabel(
                                                        invoice,
                                                    )}</span
                                                >
                                            {/if}
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {:else}
                            <div class="p-4">
                                <div
                                    class="rounded-2xl border border-dashed border-border/70 bg-muted/10 px-6 py-8 text-center"
                                >
                                    <span
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-border/70 bg-background/90 text-muted-foreground shadow-sm"
                                    >
                                        <Receipt class="h-6 w-6" />
                                    </span>
                                    <p class="mt-4 text-sm font-semibold text-foreground">
                                        Belum ada invoice.
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-muted-foreground">
                                        Saat pembayaran dibuat, riwayatnya akan
                                        tampil di sini bersama status dan batas
                                        pembayarannya.
                                    </p>
                                </div>
                            </div>
                        {/if}
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-4">
                <Card class="overflow-hidden">
                    <CardHeader class="border-b border-border/60 bg-muted/20">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/70 bg-background/80 text-primary shadow-sm"
                            >
                                <CreditCard class="h-5 w-5" />
                            </span>
                            Ringkasan pembayaran
                        </CardTitle>
                        <CardDescription
                            >Gunakan satu halaman pembayaran untuk menyelesaikan
                            langganan Anda.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-2.5 p-3 text-sm lg:p-4">
                        <div
                            class="rounded-xl border border-border/70 bg-background/80 p-2.5 shadow-sm lg:p-3"
                        >
                            <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
                                Cara bayar
                            </p>
                            <p class="mt-1 font-semibold tracking-tight text-foreground">
                                Pembayaran online
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-background/80 p-2.5 shadow-sm lg:p-3"
                        >
                            <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
                                Pembayaran berhasil
                            </p>
                            <p class="mt-1 font-semibold tracking-tight text-foreground">
                                {invoices.filter(
                                    (invoice) => invoice.status === 'paid',
                                ).length}
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-background/80 p-2.5 shadow-sm lg:p-3"
                        >
                            <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
                                Tagihan aktif
                            </p>
                            <p class="mt-1 font-semibold tracking-tight text-foreground">
                            {payableInvoice ? 'Perlu dibayar' : 'Tidak ada'}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden">
                    <CardHeader class="border-b border-border/60 bg-muted/20">
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/70 bg-background/80 text-primary shadow-sm"
                            >
                                <ShieldAlert class="h-5 w-5" />
                            </span>
                            Tentang akun
                        </CardTitle>
                        <CardDescription
                            >Informasi akun dan akses operasional Anda.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-2.5 p-3 text-sm lg:p-4">
                        <div
                            class="rounded-xl border border-border/70 bg-background/80 p-2.5 shadow-sm lg:p-3"
                        >
                            <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
                                Tenant
                            </p>
                            <p class="mt-1 font-semibold tracking-tight text-foreground">
                                {tenantSub.tenant_name}
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-background/80 p-2.5 shadow-sm lg:p-3"
                        >
                            <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
                                Pool tersedia
                            </p>
                            <p class="mt-1 font-semibold tracking-tight text-foreground">
                                {accountAccess.pool_count}
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-border/70 bg-background/80 p-2.5 shadow-sm lg:p-3"
                        >
                            <p class="text-[11px] uppercase tracking-wide text-muted-foreground">
                                Peran pengguna
                            </p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                {#each accountAccess.role_names as role}
                                    <Badge variant="outline">{role}</Badge>
                                {:else}
                                    <span class="text-xs text-muted-foreground"
                                        >Belum ada role</span
                                    >
                                {/each}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    {/if}
</div>
