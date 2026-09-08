<script lang="ts">
    import type { Snippet } from 'svelte';
    import { getContext } from 'svelte';
    import X from 'lucide-svelte/icons/x';
    import { fly } from 'svelte/transition';
    import { cn } from '@/lib/utils';
    import { overlay } from '@/lib/mobile-overlay';
    import { SHEET_CONTEXT, type SheetContext } from './context';

    let {
        side = 'right',
        class: className = '',
        showCloseButton = true,
        children,
    }: {
        side?: 'right' | 'left' | 'top' | 'bottom' | 'center';
        class?: string;
        showCloseButton?: boolean;
        children?: Snippet;
    } = $props();

    const { open, setOpen } = getContext<SheetContext>(SHEET_CONTEXT);

    const sideClasses: Record<string, string> = {
        right: 'inset-y-0 right-0',
        left: 'inset-y-0 left-0',
        top: 'inset-x-0 top-0',
        bottom: 'inset-x-0 bottom-0',
        center: 'inset-1/2 -translate-x-1/2 -translate-y-1/2',
    };

    const sizeClasses: Record<string, string> = {
        right: 'h-full w-3/4 sm:max-w-sm',
        left: 'h-full w-3/4 sm:max-w-sm',
        top: 'h-auto',
        bottom: 'h-auto',
        center: 'h-auto w-[calc(100%-2rem)] max-w-lg',
    };

    const close = () => setOpen(false);

    const panelTransition = () => {
        const axis =
            side === 'center'
                ? { x: 0, y: 18 }
                : side === 'left'
                ? { x: -320, y: 0 }
                : side === 'right'
                  ? { x: 320, y: 0 }
                  : side === 'top'
                    ? { x: 0, y: -320 }
                    : { x: 0, y: 320 };

        return { ...axis, duration: 260, opacity: 1 };
    };
</script>

{#if open()}
    <div class="fixed inset-0 z-50">
        <button
            type="button"
            class="fixed inset-0 border-0 bg-black/50"
            aria-label="Close"
            onclick={close}
        ></button>
        <div
            use:overlay={{ close, label: 'Panel', modal: true }}
            class={cn(
                'mobile-sheet-panel fixed flex max-h-[var(--mobile-viewport-height,100dvh)] flex-col gap-4 overflow-y-auto border-border bg-background p-5 shadow-lg',
                sideClasses[side] ?? sideClasses.right,
                sizeClasses[side] ?? sizeClasses.right,
                className,
            )}
            in:fly={panelTransition()}
            out:fly={panelTransition()}
        >
            {#if showCloseButton}
                <button
                    type="button"
                    class="ring-offset-background focus-visible:ring-ring absolute top-4 right-4 rounded-md opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-2 focus-visible:outline-hidden disabled:pointer-events-none"
                    aria-label="Close"
                    onclick={close}
                >
                    <X class="size-4" />
                    <span class="sr-only">Close</span>
                </button>
            {/if}
            {@render children?.()}
        </div>
    </div>
{/if}
