<script lang="ts">
    import type { Snippet } from 'svelte';
    import { getContext } from 'svelte';
    import X from 'lucide-svelte/icons/x';
    import { fade, fly } from 'svelte/transition';
    import { cn } from '@/lib/utils';
    import { overlay } from '@/lib/mobile-overlay';
    import { SHEET_CONTEXT, type SheetContext } from './context';

    let {
        side = 'right',
        class: className = '',
        showCloseButton = true,
        children,
    }: {
        side?: 'right' | 'left' | 'top' | 'bottom' | 'center' | 'fullscreen';
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
        center: '',
        fullscreen: 'inset-0',
    };

    const sizeClasses: Record<string, string> = {
        right: 'h-full w-3/4 sm:max-w-sm',
        left: 'h-full w-3/4 sm:max-w-sm',
        top: 'h-auto',
        bottom: 'h-auto',
        center: 'h-auto',
        fullscreen: 'h-[var(--mobile-viewport-height,100dvh)] w-full',
    };

    const close = () => setOpen(false);

    const panelClass = () =>
        cn(
            'mobile-sheet-panel fixed flex max-h-[var(--mobile-viewport-height,100dvh)] flex-col gap-4 overflow-y-auto border-border bg-background p-5 shadow-lg',
            sideClasses[side] ?? sideClasses.right,
            sizeClasses[side] ?? sizeClasses.right,
            side === 'center' && 'mobile-sheet-panel--center',
            side === 'fullscreen' && 'mobile-sheet-panel--fullscreen',
            className,
        );

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
        {#if side === 'center' || side === 'fullscreen'}
            <div
                use:overlay={{ close, label: 'Panel', modal: true }}
                class={panelClass()}
                data-sheet-side={side}
                in:fade={{ duration: 160 }}
                out:fade={{ duration: 120 }}
                role="dialog"
                aria-modal="true"
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
        {:else}
            <div
                use:overlay={{ close, label: 'Panel', modal: true }}
                class={panelClass()}
                data-sheet-side={side}
                in:fly={panelTransition()}
                out:fly={panelTransition()}
                role="dialog"
                aria-modal="true"
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
        {/if}
    </div>
{/if}

<style>
    .mobile-sheet-panel--center {
        top: 50%;
        right: auto;
        bottom: auto;
        left: 50%;
        width: min(calc(100vw - 2rem), 32rem);
        max-width: calc(100vw - 2rem);
        max-height: min(
            calc(var(--mobile-viewport-height, 100dvh) - 2rem),
            42rem
        );
        box-sizing: border-box;
        transform: translate(-50%, -50%);
    }

    .mobile-sheet-panel--fullscreen {
        max-height: none;
        min-height: var(--mobile-viewport-height, 100dvh);
        overflow: hidden;
        border: 0;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
    }

</style>
