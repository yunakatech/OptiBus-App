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
    import type { PoolOption } from '@/types/auth';

    let {
        compact = false,
        class: className = '',
    }: {
        compact?: boolean;
        class?: string;
    } = $props();

    const pools = $derived((page.props.auth?.pools ?? []) as PoolOption[]);
    const activePool = $derived(page.props.auth?.active_pool ?? null);
    const activePoolLabel = $derived(activePool?.name ?? 'Semua Pool');
    const hasPools = $derived(pools.length > 0);
    let pendingPoolId = $state<number | null>(null);
    let errorMessage = $state('');

    function csrfToken(): string {
        if (typeof document === 'undefined') {
            return '';
        }

        return (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement | null)?.content ?? '';
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
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ pool_id: poolId }),
            });

            if (!response.ok) {
                throw new Error(
                    await extractApiErrorMessage(
                        response,
                        'Gagal mengganti pool.',
                    ),
                );
            }

            const currentPath = `${window.location.pathname}${window.location.search}`;
            router.visit(currentPath, { preserveScroll: false, preserveState: false });
        } catch (error) {
            errorMessage = error instanceof Error ? error.message : 'Gagal mengganti pool.';
        } finally {
            pendingPoolId = null;
        }
    }
</script>

{#if hasPools}
    <DropdownMenu class={cn('relative', className)}>
        <DropdownMenuTrigger asChild>
            {#snippet children(props)}
                <button
                    type="button"
                    class={cn(
                        'inline-flex h-9 max-w-full items-center gap-1.5 rounded-md border text-xs font-medium transition hover:border-primary/35 hover:bg-primary/5 data-[state=open]:border-primary/40 data-[state=open]:bg-primary/8',
                        activePool ? 'border-primary/30 bg-primary/8 text-primary' : 'border-border/80 bg-background text-muted-foreground',
                        compact ? 'w-full min-w-0 px-2.5' : 'w-full px-2.5',
                    )}
                    aria-label={`Pool aktif: ${activePoolLabel}`}
                    {...props}
                >
                    <Building2 class="size-3.5 shrink-0" />
                    <span class="min-w-0 flex-1 truncate text-left">
                        {compact ? activePoolLabel : activePoolLabel}
                    </span>
                    {#if pendingPoolId !== null}
                        <LoaderCircle class="size-3.5 shrink-0 animate-spin opacity-70" />
                    {:else}
                        <ChevronDown class="size-3.5 shrink-0 opacity-60" />
                    {/if}
                </button>
            {/snippet}
        </DropdownMenuTrigger>
        <DropdownMenuContent align={compact ? 'end' : 'start'} sideOffset={8} class="w-64 p-1.5">
            <div class="px-2 py-1.5">
                <p class="text-[11px] font-semibold uppercase tracking-normal text-muted-foreground">Pool aktif</p>
                <p class="mt-0.5 truncate text-sm font-semibold text-foreground">{activePoolLabel}</p>
                {#if errorMessage}
                    <p class="mt-1 text-[11px] text-destructive">{errorMessage}</p>
                {/if}
            </div>
            <DropdownMenuItem
                class="gap-2 rounded-md text-sm"
                disabled={pendingPoolId !== null}
                onclick={() => switchPool(0)}
            >
                <Check class={cn('size-4', !activePool ? 'opacity-100' : 'opacity-0')} />
                <span class="min-w-0 flex-1 truncate">Semua Pool</span>
            </DropdownMenuItem>
            {#each pools as pool (pool.id)}
                <DropdownMenuItem
                    class="gap-2 rounded-md text-sm"
                    disabled={pendingPoolId !== null}
                    onclick={() => switchPool(pool.id)}
                >
                    <Check class={cn('size-4', activePool?.id === pool.id ? 'opacity-100 text-primary' : 'opacity-0')} />
                    <span class="min-w-0 flex-1 truncate">{pool.name}</span>
                    {#if pool.code}
                        <span class="shrink-0 text-[11px] text-muted-foreground">{pool.code}</span>
                    {/if}
                </DropdownMenuItem>
            {/each}
        </DropdownMenuContent>
    </DropdownMenu>
{/if}
