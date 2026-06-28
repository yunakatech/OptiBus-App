<script lang="ts">
    import { Button } from '@/components/ui/button';
    import Settings2 from 'lucide-svelte/icons/settings-2';

    let {
        class: className = '',
    }: {
        class?: string;
    } = $props();

    const openMobileMenu = () => {
        if (typeof window === 'undefined') {
            return;
        }

        window.dispatchEvent(new CustomEvent('open-mobile-menu'));
    };
</script>

<Button
    type="button"
    variant="ghost"
    size="icon"
    aria-label="Buka menu navigasi"
    class={`inline-flex size-9 items-center justify-center rounded-md border border-input bg-background text-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:hidden ${className}`}
    onclick={openMobileMenu}
>
    <Settings2 class="size-4" />
</Button>
