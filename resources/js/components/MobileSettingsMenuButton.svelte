<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Settings2 from 'lucide-svelte/icons/settings-2';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';

    let {
        class: className = '',
    }: {
        class?: string;
    } = $props();

    const url = currentUrlState();
    const isActive = $derived(url.isCurrentUrl('/menu', url.currentUrl));
</script>

<Link
    href={toUrl('/menu')}
    aria-label="Buka menu pengaturan"
    class={`inline-flex size-9 items-center justify-center rounded-md border border-input bg-background transition-colors hover:bg-accent hover:text-accent-foreground md:hidden ${
        isActive ? 'border-primary/40 bg-primary/10 text-primary' : 'text-foreground'
    } ${className}`}
>
    <Settings2 class="size-4" />
</Link>
