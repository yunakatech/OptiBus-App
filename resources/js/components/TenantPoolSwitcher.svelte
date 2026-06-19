<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import ContextSwitcher from '@/components/ContextSwitcher.svelte';
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
</script>

{#if mode === 'desktop'}
    {#if showTenant || showPool}
        <div
            class={cn(
                'space-y-2 rounded-2xl border border-sidebar-border/70 bg-sidebar/70 p-2 shadow-sm',
                className,
            )}
        >
            {#if showTenant}
                <ContextSwitcher kind="tenant" mode="desktop" />
            {/if}
            {#if showPool}
                <ContextSwitcher kind="pool" mode="desktop" />
            {/if}
        </div>
    {/if}
{:else}
    {#if showTenant || showPool}
        <div class={cn(showTenant && showPool ? 'grid grid-cols-2 gap-2' : 'grid gap-2', className)}>
            {#if showTenant}
                <ContextSwitcher kind="tenant" mode="mobile" />
            {/if}
            {#if showPool}
                <ContextSwitcher kind="pool" mode="mobile" />
            {/if}
        </div>
    {/if}
{/if}
