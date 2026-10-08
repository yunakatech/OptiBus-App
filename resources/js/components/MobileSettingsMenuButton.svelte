<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Settings2 from 'lucide-svelte/icons/settings-2';
    import { Button } from '@/components/ui/button';
    import { hasPermission } from '@/lib/access';
    import { bookingInboxNudge } from '@/lib/bookingInboxNudge.svelte';
    import { toUrl } from '@/lib/utils';
    import menu from '@/routes/menu';

    let {
        class: className = '',
    }: {
        class?: string;
    } = $props();

    const showInboxNudge = $derived(
        hasPermission(page.props.auth?.permissions, 'booking.view') &&
            Boolean(page.props.auth?.active_tenant) &&
            bookingInboxNudge.hasPending,
    );
</script>

<Button
    asChild
    variant="ghost"
    size="default"
    aria-label={showInboxNudge
        ? 'Buka halaman menu navigasi, ada booking masuk'
        : 'Buka halaman menu navigasi'}
    class={`inline-flex min-h-11 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-semibold text-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:hidden ${className}`}
>
    {#snippet children(props)}
        <Link
            {...props}
            href={toUrl(menu.index())}
            prefetch
            cacheFor={30000}
            aria-label={showInboxNudge
                ? 'Buka halaman menu navigasi, ada booking masuk'
                : 'Buka halaman menu navigasi'}
        >
            <span class="relative inline-flex">
                <Settings2 class="size-4" />
                {#if showInboxNudge}
                    <span class="absolute -top-1 -right-1 size-2 rounded-full bg-red-500 ring-2 ring-background" aria-hidden="true"></span>
                {/if}
            </span>
            <span>Menu</span>
        </Link>
    {/snippet}
</Button>
