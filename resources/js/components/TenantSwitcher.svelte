<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import Building2 from 'lucide-svelte/icons/building-2';
    import Check from 'lucide-svelte/icons/check';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import LoaderCircle from 'lucide-svelte/icons/loader-circle';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { extractApiErrorMessage } from '@/lib/api-errors';
    import { cn } from '@/lib/utils';
    import type { TenantOption, ActiveTenant } from '@/types/auth';

    let {
        compact = false,
        class: className = '',
    }: {
        compact?: boolean;
        class?: string;
    } = $props();

    const tenants = $derived((page.props.auth?.tenants ?? []) as TenantOption[]);
    const activeTenant = $derived((page.props.auth?.active_tenant ?? null) as ActiveTenant | null);
    const activeTenantLabel = $derived(activeTenant?.name ?? 'Pilih Tenant');
    const isSuperAdmin = $derived(Boolean(page.props.auth?.user?.is_super_admin));
    const hasTenants = $derived(tenants.length > 0);
    let pendingTenantId = $state<number | null>(null);
    let errorMessage = $state('');

    function csrfToken(): string {
        if (typeof document === 'undefined') {
            return '';
        }

        return (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement | null)?.content ?? '';
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
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ tenant_id: tenantId }),
            });

            if (!response.ok) {
                throw new Error(
                    await extractApiErrorMessage(
                        response,
                        'Gagal mengganti tenant.',
                    ),
                );
            }

            const currentPath = `${window.location.pathname}${window.location.search}`;
            router.visit(currentPath, { preserveScroll: false, preserveState: false });
        } catch (error) {
            errorMessage = error instanceof Error ? error.message : 'Gagal mengganti tenant.';
        } finally {
            pendingTenantId = null;
        }
    }
</script>

{#if isSuperAdmin && hasTenants}
    <DropdownMenu class={cn('relative', className)}>
        <DropdownMenuTrigger asChild>
            {#snippet children(props)}
                <button
                    type="button"
                    class={cn(
                        'inline-flex h-9 max-w-full items-center gap-1.5 rounded-md border text-xs font-medium transition hover:border-primary/35 hover:bg-primary/5 data-[state=open]:border-primary/40 data-[state=open]:bg-primary/8',
                        activeTenant ? 'border-primary/30 bg-primary/8 text-primary' : 'border-border/80 bg-background text-muted-foreground',
                        compact ? 'w-full min-w-0 px-2.5' : 'w-full px-2.5',
                    )}
                    aria-label={`Tenant aktif: ${activeTenantLabel}`}
                    {...props}
                >
                    <Building2 class="size-3.5 shrink-0" />
                    <span class="min-w-0 flex-1 truncate text-left">
                        {activeTenantLabel}
                    </span>
                    {#if pendingTenantId !== null}
                        <LoaderCircle class="size-3.5 shrink-0 animate-spin opacity-70" />
                    {:else}
                        <ChevronDown class="size-3.5 shrink-0 opacity-60" />
                    {/if}
                </button>
            {/snippet}
        </DropdownMenuTrigger>
        <DropdownMenuContent align={compact ? 'end' : 'start'} sideOffset={8} class="w-72 p-1.5">
            <div class="px-2 py-1.5">
                <p class="text-[11px] font-semibold uppercase tracking-normal text-muted-foreground">Tenant aktif</p>
                <p class="mt-0.5 truncate text-sm font-semibold text-foreground">{activeTenantLabel}</p>
                {#if errorMessage}
                    <p class="mt-1 text-[11px] text-destructive">{errorMessage}</p>
                {/if}
            </div>
            <DropdownMenuItem
                class="gap-2 rounded-md text-sm"
                disabled={pendingTenantId !== null}
                onclick={() => switchTenant(0)}
            >
                <Check class={cn('size-4', !activeTenant ? 'opacity-100' : 'opacity-0')} />
                <span class="min-w-0 flex-1 truncate">Semua Tenant</span>
                <span class="shrink-0 text-[11px] text-muted-foreground">Platform</span>
            </DropdownMenuItem>
            {#each tenants as tenant (tenant.id)}
                <DropdownMenuItem
                    class="gap-2 rounded-md text-sm"
                    disabled={pendingTenantId !== null}
                    onclick={() => switchTenant(tenant.id)}
                >
                    <Check class={cn('size-4', activeTenant?.id === tenant.id ? 'opacity-100 text-primary' : 'opacity-0')} />
                    <span class="min-w-0 flex-1 truncate">{tenant.name}</span>
                    <span class="shrink-0 text-[11px] text-muted-foreground">{tenant.slug}</span>
                </DropdownMenuItem>
            {/each}
        </DropdownMenuContent>
    </DropdownMenu>
{/if}
