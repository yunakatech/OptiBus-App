<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import Bell from 'lucide-svelte/icons/bell';
    import Moon from 'lucide-svelte/icons/moon';
    import Sun from 'lucide-svelte/icons/sun';
    import Rows3 from 'lucide-svelte/icons/rows-3';
    import Rows4 from 'lucide-svelte/icons/rows-4';
    import { Button } from '@/components/ui/button';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from '@/components/ui/tooltip';
    import { themeState } from '@/lib/theme.svelte';

    const {
        appearance,
        density,
        resolvedAppearance,
        updateAppearance,
        updateDensity,
    } = themeState();

    const isDark = $derived(resolvedAppearance() === 'dark');
    const isCompact = $derived(density.value === 'compact');
    const unreadCount = $derived.by(() => {
        const props = page.props as Record<string, any>;
        const summary =
            props.notification_summary ??
            props.notificationSummary ??
            props.notifications ??
            {};
        const raw = Number(
            summary.unread_count ?? summary.unreadCount ?? summary.count ?? 0,
        );

        return Number.isFinite(raw) ? Math.max(0, raw) : 0;
    });

    function toggleAppearance(): void {
        updateAppearance(isDark ? 'light' : 'dark');
    }

    function toggleDensity(): void {
        updateDensity(isCompact ? 'comfortable' : 'compact');
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
                    onclick={toggleDensity}
                    aria-label={isCompact
                        ? 'Gunakan density nyaman'
                        : 'Gunakan density compact'}
                >
                    {#if isCompact}
                        <Rows4 class="size-4" />
                    {:else}
                        <Rows3 class="size-4" />
                    {/if}
                </Button>
            {/snippet}
        </TooltipTrigger>
        <TooltipContent side="bottom">
            {isCompact ? 'Comfortable' : 'Compact'}
        </TooltipContent>
    </Tooltip>

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

    <Tooltip>
        <TooltipTrigger>
            {#snippet child({ props })}
                <Button
                    {...props}
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="relative size-8 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    aria-label="Notifikasi"
                >
                    <Bell class="size-4" />
                    {#if unreadCount > 0}
                        <span
                            class="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold leading-4 text-primary-foreground"
                        >
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    {/if}
                </Button>
            {/snippet}
        </TooltipTrigger>
        <TooltipContent side="bottom">Notifikasi</TooltipContent>
    </Tooltip>
</div>
