<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import Building2 from 'lucide-svelte/icons/building-2';
    import Check from 'lucide-svelte/icons/check';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import LoaderCircle from 'lucide-svelte/icons/loader-circle';
    import MapPin from 'lucide-svelte/icons/map-pin';
    import ContextSwitcher from '@/components/ContextSwitcher.svelte';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { cn } from '@/lib/utils';
    import type { ActivePool, ActiveTenant, PoolOption, TenantOption } from '@/types/auth';

    let {
        mode,
        class: className = '',
    }: {
        mode: 'desktop' | 'mobile';
        class?: string;
    } = $props();

    const auth = $derived(page.props.auth ?? null);
    const isSuperAdmin = $derived(Boolean(auth?.user?.is_super_admin));
    const tenants = $derived((auth?.tenants ?? []) as TenantOption[]);
    const pools = $derived((auth?.pools ?? []) as PoolOption[]);
    const activeTenant = $derived((auth?.active_tenant ?? null) as ActiveTenant | null);
    const activePool = $derived((auth?.active_pool ?? null) as ActivePool | null);
    const hasTenants = $derived(tenants.length > 0);
    const hasPools = $derived(pools.length > 0);
    const showTenant = $derived(isSuperAdmin && hasTenants);
    const showPool = $derived(hasPools);
    const activeTenantLabel = $derived(activeTenant?.name ?? 'Semua Tenant');
    const activePoolLabel = $derived(activePool?.name ?? 'Semua Pool');
    const activeSummary = $derived(`${activeTenantLabel} / ${activePoolLabel}`);
    let pendingTenantId = $state<number | null>(null);
    let pendingPoolId = $state<number | null>(null);
    let errorMessage = $state('');

    function csrfToken(): string {
        if (typeof document === 'undefined') {
            return '';
        }

        return (
            (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement | null)
                ?.content ?? ''
        );
    }

    async function switchTenant(tenantId: number): Promise<void> {
        if (pendingTenantId !== null || (activeTenant?.id ?? 0) === tenantId) {
            return;
        }

        pendingTenantId = tenantId;
        errorMessage = '';

        try {
            const response = await fetch('/api/admin/tenant/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ tenant_id: tenantId }),
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));

                throw new Error((payload?.error as string | undefined) ?? 'Gagal mengganti tenant.');
            }

            const currentPath = `${window.location.pathname}${window.location.search}`;
            router.visit(currentPath, {
                preserveScroll: false,
                preserveState: false,
            });
        } catch (error) {
            errorMessage = error instanceof Error ? error.message : 'Gagal mengganti tenant.';
        } finally {
            pendingTenantId = null;
        }
    }

    async function switchPool(poolId: number): Promise<void> {
        if (pendingPoolId !== null || (activePool?.id ?? 0) === poolId) {
            return;
        }

        pendingPoolId = poolId;
        errorMessage = '';

        try {
            const response = await fetch('/api/admin/pool/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ pool_id: poolId }),
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));

                throw new Error((payload?.message as string | undefined) ?? 'Gagal mengganti pool.');
            }

            const currentPath = `${window.location.pathname}${window.location.search}`;
            router.visit(currentPath, {
                preserveScroll: false,
                preserveState: false,
            });
        } catch (error) {
            errorMessage = error instanceof Error ? error.message : 'Gagal mengganti pool.';
        } finally {
            pendingPoolId = null;
        }
    }
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
                class="w-[22rem] max-w-[calc(100vw-1.5rem)] p-2"
            >
                <div class="max-h-[70svh] space-y-1 overflow-y-auto pr-1 scrollbar-thin">
                    {#if showTenant}
                        <DropdownMenuItem
                            class="gap-2 rounded-md text-sm"
                            disabled={pendingTenantId !== null}
                            onclick={() => switchTenant(0)}
                        >
                            <Building2 class="size-4 shrink-0" />
                            <Check class={cn('size-4 shrink-0', !activeTenant ? 'opacity-100 text-primary' : 'opacity-0')} />
                            <span class="min-w-0 flex-1 truncate">Semua Tenant</span>
                            <span class="shrink-0 text-[11px] text-muted-foreground">Platform</span>
                            {#if pendingTenantId !== null}
                                <LoaderCircle class="size-4 shrink-0 animate-spin text-muted-foreground" />
                            {/if}
                        </DropdownMenuItem>

                        {#each tenants as tenant (tenant.id)}
                            <DropdownMenuItem
                                class="gap-2 rounded-md text-sm"
                                disabled={pendingTenantId !== null}
                                onclick={() => switchTenant(tenant.id)}
                            >
                                <Building2 class="size-4 shrink-0" />
                                <Check class={cn('size-4 shrink-0', activeTenant?.id === tenant.id ? 'opacity-100 text-primary' : 'opacity-0')} />
                                <span class="min-w-0 flex-1 truncate">{tenant.name}</span>
                                <span class="shrink-0 text-[11px] text-muted-foreground">{tenant.slug}</span>
                                {#if pendingTenantId === tenant.id}
                                    <LoaderCircle class="size-4 shrink-0 animate-spin text-muted-foreground" />
                                {/if}
                            </DropdownMenuItem>
                        {/each}
                    {/if}

                    {#if showTenant && showPool}
                        <div class="my-1 border-t border-border/70"></div>
                    {/if}

                    {#if showPool}
                        <DropdownMenuItem
                            class="gap-2 rounded-md text-sm"
                            disabled={pendingPoolId !== null}
                            onclick={() => switchPool(0)}
                        >
                            <MapPin class="size-4 shrink-0" />
                            <Check class={cn('size-4 shrink-0', !activePool ? 'opacity-100 text-primary' : 'opacity-0')} />
                            <span class="min-w-0 flex-1 truncate">Semua Pool</span>
                            {#if pendingPoolId !== null}
                                <LoaderCircle class="size-4 shrink-0 animate-spin text-muted-foreground" />
                            {/if}
                        </DropdownMenuItem>

                        {#each pools as pool (pool.id)}
                            <DropdownMenuItem
                                class="gap-2 rounded-md text-sm"
                                disabled={pendingPoolId !== null}
                                onclick={() => switchPool(pool.id)}
                            >
                                <MapPin class="size-4 shrink-0" />
                                <Check class={cn('size-4 shrink-0', activePool?.id === pool.id ? 'opacity-100 text-primary' : 'opacity-0')} />
                                <span class="min-w-0 flex-1 truncate">{pool.name}</span>
                                {#if pool.code}
                                    <span class="shrink-0 text-[11px] text-muted-foreground">{pool.code}</span>
                                {/if}
                                {#if pendingPoolId === pool.id}
                                    <LoaderCircle class="size-4 shrink-0 animate-spin text-muted-foreground" />
                                {/if}
                            </DropdownMenuItem>
                        {/each}
                    {/if}
                </div>
                {#if errorMessage}
                    <p class="px-2 pt-2 text-xs text-destructive">{errorMessage}</p>
                {/if}
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
