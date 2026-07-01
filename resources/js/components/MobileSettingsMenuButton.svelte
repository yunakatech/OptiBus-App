<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { Button } from '@/components/ui/button';
    import Settings2 from 'lucide-svelte/icons/settings-2';
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
    size="icon"
    aria-label="Buka halaman menu navigasi"
    class={`inline-flex size-9 items-center justify-center rounded-md border border-input bg-background text-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:hidden ${className}`}
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
        </Link>
    {/snippet}
</Button>
