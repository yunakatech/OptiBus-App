<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Settings2 from 'lucide-svelte/icons/settings-2';
    import { Button } from '@/components/ui/button';
    import { toUrl } from '@/lib/utils';
    import menu from '@/routes/menu';

    let {
        class: className = '',
    }: {
        class?: string;
    } = $props();
</script>

<Button
    asChild
    variant="ghost"
    size="default"
    aria-label="Buka halaman menu navigasi"
    class={`inline-flex min-h-11 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-semibold text-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:hidden ${className}`}
>
    {#snippet children(props)}
        <Link
            {...props}
            href={toUrl(menu.index())}
            prefetch
            cacheFor={30000}
            aria-label="Buka halaman menu navigasi"
        >
            <Settings2 class="size-4" />
            <span>Menu</span>
        </Link>
    {/snippet}
</Button>
