<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import Building2 from 'lucide-svelte/icons/building-2';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ContextSwitcher from '@/components/ContextSwitcher.svelte';
    import PoolSwitcher from '@/components/PoolSwitcher.svelte';
    import TenantSwitcher from '@/components/TenantSwitcher.svelte';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { cn } from '@/lib/utils';

    let {
        mode,
        class: className = '',
    }: {
        mode: 'desktop' | 'mobile';
        class?: string;
    } = $props();

    const auth = $derived(page.props.auth ?? null);
    const isSuperAdmin = $derived(Boolean(auth?.user?.is_super_admin));
    const hasTenants = $derived((auth?.tenants ?? []).length > 0);
    const hasPools = $derived((auth?.pools ?? []).length > 0);
    const showTenant = $derived(isSuperAdmin && hasTenants);
    const showPool = $derived(hasPools);
    const activeTenantLabel = $derived(
        (auth?.active_tenant?.name ?? 'Semua Tenant') as string,
    );
    const activePoolLabel = $derived(
        (auth?.active_pool?.name ?? 'Semua Pool') as string,
    );
    const activeSummary = $derived(`${activeTenantLabel} / ${activePoolLabel}`);
</script>

{#if mode === 'desktop'}
    {#if showTenant || showPool}
        <DropdownMenu class={cn('relative w-full', className)}>
            <DropdownMenuTrigger asChild>
                {#snippet children(props)}
                    <button
                        type="button"
                        class={cn(
                            'flex min-h-11 w-full items-center gap-2 rounded-xl border border-sidebar-border/70 bg-background/80 px-3 py-2 text-left text-sm font-medium text-foreground shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus-visible:border-primary/40 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                            props['data-state'] === 'open'
                                ? 'border-primary/35 bg-slate-50'
                                : '',
                        )}
                        aria-label={`Konteks aktif: ${activeSummary}`}
                        onclick={props.onclick}
                        aria-expanded={props['aria-expanded']}
                        data-state={props['data-state']}
                    >
                        <Building2 class="size-4 shrink-0 text-primary" />
                        <span class="min-w-0 flex-1 text-left">
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Konteks aktif
                            </span>
                            <span class="block truncate text-sm font-semibold text-foreground">
                                {activeSummary}
                            </span>
                        </span>
                        <ChevronDown class="size-4 shrink-0 text-muted-foreground" />
                    </button>
                {/snippet}
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align="end"
                sideOffset={8}
                class="w-[22rem] max-w-[calc(100vw-1.5rem)] p-3"
            >
                <div class="space-y-3">
                    <div class="px-1">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                            Konteks aktif
                        </p>
                        <p class="mt-0.5 truncate text-sm font-semibold text-foreground">
                            {activeSummary}
                        </p>
                    </div>

                    {#if showTenant}
                        <div class="space-y-1.5">
                            <p class="px-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Tenant
                            </p>
                            <TenantSwitcher compact class="w-full" />
                        </div>
                    {/if}

                    {#if showTenant && showPool}
                        <div class="border-t border-border/70"></div>
                    {/if}

                    {#if showPool}
                        <div class="space-y-1.5">
                            <p class="px-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Pool
                            </p>
                            <PoolSwitcher compact class="w-full" />
                        </div>
                    {/if}
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    {/if}
{:else}
    {#if showTenant || showPool}
        <div
            class={cn(
                showTenant && showPool ? 'grid gap-2 sm:grid-cols-2' : 'grid gap-2',
                className,
            )}
        >
            {#if showTenant}
                <ContextSwitcher kind="tenant" mode="mobile" />
            {/if}
            {#if showPool}
                <ContextSwitcher kind="pool" mode="mobile" />
            {/if}
        </div>
    {/if}
{/if}
