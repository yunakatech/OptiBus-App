<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { ArrowUpCircle, X } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';

    type PlanInfo = {
        plan_name: string;
        plan_slug: string;
        subscription_status: string;
        ends_at: string | null;
    };

    let {
        featureName = '',
        show = true,
        onClose,
    }: {
        featureName?: string;
        show?: boolean;
        onClose?: () => void;
    } = $props();

    const tenantSub = $derived(page.props.auth?.tenant_subscription as PlanInfo | null);

    // svelte-ignore state_referenced_locally
    let visible = $state(show);

    function dismiss() {
        visible = false;
        onClose?.();
    }
</script>

{#if visible && tenantSub}
    <Card class="border-amber-200 bg-amber-50 dark:bg-amber-950/20">
        <CardContent class="py-4">
            <div class="flex items-start gap-4">
                <ArrowUpCircle class="h-6 w-6 text-amber-600 mt-0.5 shrink-0" />
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="font-semibold text-amber-900 dark:text-amber-100">
                                {#if featureName}
                                    Fitur "{featureName}" tidak tersedia di paket {tenantSub.plan_name}
                                {:else}
                                    Paket {tenantSub.plan_name} — {tenantSub.subscription_status}
                                {/if}
                            </h4>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                Upgrade ke paket Pro atau Fleet untuk membuka semua fitur.
                                {#if tenantSub.ends_at}
                                    Langganan {tenantSub.subscription_status === 'trial' ? 'trial' : 'Anda'} berakhir {new Date(tenantSub.ends_at).toLocaleDateString('id-ID')}.
                                {/if}
                            </p>
                        </div>
                        <button onclick={dismiss} class="text-amber-500 hover:text-amber-700 shrink-0"><X class="h-4 w-4" /></button>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <a href="/admin-ops/saas/plans" class="text-sm text-amber-700 dark:text-amber-300 underline hover:text-amber-900">
                            Lihat paket →
                        </a>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
{/if}
