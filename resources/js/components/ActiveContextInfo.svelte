<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import Building2 from 'lucide-svelte/icons/building-2';
    import MapPin from 'lucide-svelte/icons/map-pin';
    import { cn } from '@/lib/utils';
    import type { ActivePool, ActiveTenant } from '@/types/auth';

    let {
        class: className = '',
    }: {
        class?: string;
    } = $props();

    const auth = $derived(page.props.auth ?? null);
    const activeTenant = $derived((auth?.active_tenant ?? null) as ActiveTenant | null);
    const activePool = $derived((auth?.active_pool ?? null) as ActivePool | null);
    const activeTenantLabel = $derived(activeTenant?.name ?? 'Semua Tenant');
    const activePoolLabel = $derived(activePool?.name ?? 'Semua Pool');
</script>

<div
    class={cn(
        'min-w-0 rounded-lg border border-sidebar-border/70 bg-background/85 px-2.5 py-2 shadow-sm backdrop-blur',
        className,
    )}
>
    <p
        class="mb-1.5 text-[9px] font-semibold uppercase tracking-[0.18em] text-muted-foreground"
    >
        Tenant / Pool aktif
    </p>
    <div class="grid gap-1.5 sm:grid-cols-2 sm:gap-2">
        <div class="min-w-0 rounded-md border border-border/70 bg-background/70 px-2 py-1.5">
            <p
                class="mb-1 inline-flex items-center gap-1.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-muted-foreground"
            >
                <Building2 class="size-3 shrink-0" />
                Tenant
            </p>
            <p class="truncate text-[13px] font-semibold text-foreground">
                {activeTenantLabel}
            </p>
        </div>
        <div class="min-w-0 rounded-md border border-border/70 bg-background/70 px-2 py-1.5">
            <p
                class="mb-1 inline-flex items-center gap-1.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-muted-foreground"
            >
                <MapPin class="size-3 shrink-0" />
                Pool
            </p>
            <p class="truncate text-[13px] font-semibold text-foreground">
                {activePoolLabel}
            </p>
        </div>
    </div>
</div>
