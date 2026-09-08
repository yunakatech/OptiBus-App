<script lang="ts">
    import { ListFilter, RotateCcw, X } from 'lucide-svelte';
    import type { Snippet } from 'svelte';
    import { Button } from '@/components/ui/button';
    import {
        Sheet,
        SheetContent,
        SheetDescription,
        SheetFooter,
        SheetHeader,
        SheetTitle,
    } from '@/components/ui/sheet';
    import { cn } from '@/lib/utils';

    let {
        label = 'Filter',
        activeCount = 0,
        summary = '',
        primary,
        desktop,
        filters,
        onOpen,
        onApply,
        onReset,
        onCancel,
        class: className = '',
    }: {
        label?: string;
        activeCount?: number;
        summary?: string;
        primary?: Snippet;
        desktop?: Snippet;
        filters?: Snippet;
        onOpen?: () => void;
        onApply?: () => void;
        onReset?: () => void;
        onCancel?: () => void;
        class?: string;
    } = $props();

    let sheetOpen = $state(false);
    let wasOpen = false;
    let suppressCancel = false;

    $effect(() => {
        const currentOpen = sheetOpen;

        if (wasOpen && !currentOpen) {
            if (!suppressCancel) {
                onCancel?.();
            }

            suppressCancel = false;
        }

        wasOpen = currentOpen;
    });

    function openFilters() {
        onOpen?.();
        sheetOpen = true;
    }

    function applyFilters() {
        onApply?.();
        suppressCancel = true;
        sheetOpen = false;
    }

    function resetFilters() {
        onReset?.();
    }

    function cancelFilters() {
        suppressCancel = false;
        sheetOpen = false;
    }
</script>

<div class={cn('responsive-filter-bar', className)}>
    <div class="responsive-filter-primary">
        {@render primary?.()}
    </div>

    <div class="responsive-filter-desktop">
        {@render desktop?.()}
    </div>

    <div class="responsive-filter-mobile">
        <div class="responsive-filter-summary">
            <span class="responsive-filter-summary-value"
                >{summary || `Semua ${label.toLowerCase()}`}</span
            >
        </div>
        {#if activeCount > 0}
            <span class="responsive-filter-active-count"
                >{activeCount} aktif</span
            >
        {/if}
        <Button
            type="button"
            variant={activeCount > 0 ? 'default' : 'outline'}
            class="h-11 shrink-0 rounded-xl px-3.5 text-sm"
            aria-haspopup="dialog"
            aria-expanded={sheetOpen}
            onclick={openFilters}
        >
            <ListFilter class="mr-2 h-4 w-4" />
            Filter
        </Button>
    </div>

    <Sheet bind:open={sheetOpen}>
        <SheetContent
            side="fullscreen"
            showCloseButton={false}
            class="responsive-filter-screen gap-0 p-0"
        >
            <SheetHeader
                class="responsive-filter-sheet-header m-0 flex h-14 shrink-0 flex-row items-center justify-between gap-3 border-b border-border/70 px-4 text-left"
            >
                <div class="flex w-full items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <SheetTitle>Filter {label}</SheetTitle>
                        {#if activeCount > 0}
                            <span class="responsive-filter-header-count"
                                >{activeCount}</span
                            >
                        {/if}
                        <SheetDescription class="sr-only">
                            Atur filter {label}, lalu terapkan perubahan.
                        </SheetDescription>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border/70 bg-muted/40 text-muted-foreground transition hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        aria-label="Tutup filter"
                        onclick={cancelFilters}
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </SheetHeader>

            <div class="responsive-filter-sheet-body px-4 py-5">
                {@render filters?.()}
            </div>

            <SheetFooter
                class="responsive-filter-sheet-footer m-0 flex-none border-t border-border/70 bg-background px-4 pt-3"
            >
                <Button
                    type="button"
                    class="h-12 rounded-xl text-sm"
                    onclick={applyFilters}
                >
                    Terapkan
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    class="h-11 rounded-xl text-sm"
                    onclick={resetFilters}
                >
                    <RotateCcw class="mr-2 h-4 w-4" />
                    Reset
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</div>

<style>
    .responsive-filter-bar {
        display: grid;
        gap: 0.75rem;
    }

    .responsive-filter-primary {
        display: none;
        min-width: 0;
    }

    .responsive-filter-desktop {
        display: none;
    }

    .responsive-filter-mobile {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 1rem;
        background: var(--card);
        padding: 0.375rem;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
    }

    .responsive-filter-summary {
        display: flex;
        min-width: 0;
        justify-content: center;
        padding-inline: 0.5rem;
    }

    .responsive-filter-summary-value {
        overflow: hidden;
        color: var(--foreground);
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.125rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .responsive-filter-active-count {
        flex: 0 0 auto;
        border-radius: 999px;
        background: color-mix(in srgb, var(--primary) 12%, transparent);
        padding: 0.25rem 0.5rem;
        color: var(--primary);
        font-size: 0.6875rem;
        font-weight: 700;
        line-height: 1rem;
    }

    .responsive-filter-header-count {
        display: inline-flex;
        min-width: 1.25rem;
        height: 1.25rem;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: color-mix(in srgb, var(--primary) 12%, transparent);
        color: var(--primary);
        font-size: 0.6875rem;
        font-weight: 700;
        line-height: 1;
    }

    :global(.responsive-filter-screen) {
        overflow: hidden;
    }

    :global(.mobile-sheet-panel--fullscreen.responsive-filter-screen) {
        display: grid;
        grid-template-rows: 3.5rem minmax(0, 1fr) auto;
        height: var(--mobile-viewport-height, 100dvh);
        min-height: var(--mobile-viewport-height, 100dvh);
    }

    :global(.responsive-filter-sheet-header) {
        grid-row: 1;
        min-width: 0;
        z-index: 1;
    }

    :global(.responsive-filter-sheet-body) {
        grid-row: 2;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    :global(.responsive-filter-sheet-footer) {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        grid-row: 3;
        gap: 0.5rem;
        z-index: 1;
        padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
    }

    @media (min-width: 768px) {
        .responsive-filter-bar {
            display: block;
        }

        .responsive-filter-mobile {
            display: none;
        }

        .responsive-filter-desktop {
            display: block;
        }

        .responsive-filter-primary {
            display: block;
        }
    }
</style>
