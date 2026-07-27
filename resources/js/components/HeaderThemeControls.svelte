<script lang="ts">
    import Moon from 'lucide-svelte/icons/moon';
    import Sun from 'lucide-svelte/icons/sun';
    import { Button } from '@/components/ui/button';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from '@/components/ui/tooltip';
    import { themeState } from '@/lib/theme.svelte';

    const { appearance, resolvedAppearance, updateAppearance } = themeState();

    const isDark = $derived(resolvedAppearance() === 'dark');

    function toggleAppearance(): void {
        updateAppearance(isDark ? 'light' : 'dark');
    }
</script>

<div class="flex items-center gap-1">
    <Tooltip>
        <TooltipTrigger>
            {#snippet child({ props })}
                <Button
                    {...props}
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    onclick={toggleAppearance}
                    aria-label={isDark
                        ? 'Gunakan light mode'
                        : 'Gunakan dark mode'}
                    aria-pressed={appearance.value === 'dark'}
                >
                    {#if isDark}
                        <Sun class="size-4" />
                    {:else}
                        <Moon class="size-4" />
                    {/if}
                </Button>
            {/snippet}
        </TooltipTrigger>
        <TooltipContent side="bottom">
            {isDark ? 'Light' : 'Dark'}
        </TooltipContent>
    </Tooltip>
</div>
