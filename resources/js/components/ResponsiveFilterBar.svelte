<script lang="ts">
    import type { Snippet } from 'svelte';
    import { ListFilter, RotateCcw, X } from 'lucide-svelte';
    import {
        Sheet,
        SheetContent,
        SheetDescription,
        SheetFooter,
        SheetHeader,
        SheetTitle,
    } from '@/components/ui/sheet';
    import { Button } from '@/components/ui/button';
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
            <span class="responsive-filter-summary-label">Filter</span>
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
            side="center"
            showCloseButton={false}
            class="responsive-filter-sheet gap-0 rounded-t-3xl p-0"
        >
            <SheetHeader
                class="border-b border-border/70 px-5 pt-5 pb-4 text-left"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <SheetTitle>Filter {label}</SheetTitle>
                        <SheetDescription class="sr-only">
                            Atur filter {label}, lalu terapkan perubahan.
                        </SheetDescription>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-border/70 bg-background text-muted-foreground shadow-sm transition hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        aria-label="Tutup filter"
                        onclick={cancelFilters}
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </SheetHeader>

            <div class="responsive-filter-sheet-body px-5 py-5">
                {@render filters?.()}
            </div>

            <SheetFooter
                class="responsive-filter-sheet-footer border-t border-border/70 bg-background/95 px-4 py-3"
            >
                <Button
                    type="button"
                    variant="outline"
                    class="h-11 rounded-xl"
                    onclick={resetFilters}
                >
                    <RotateCcw class="mr-2 h-4 w-4" />
                    Reset
                </Button>
                <Button
                    type="button"
                    class="h-11 rounded-xl"
                    onclick={applyFilters}
                >
                    Terapkan
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
        flex-direction: column;
        gap: 0.125rem;
        padding-inline: 0.5rem;
    }

    .responsive-filter-summary-label {
        color: var(--muted-foreground);
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        line-height: 1rem;
        text-transform: uppercase;
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

    :global(.responsive-filter-sheet) {
        overflow: hidden;
        max-height: min(
            calc(var(--mobile-viewport-height, 100dvh) - 1rem),
            42rem
        );
    }

    :global(.responsive-filter-sheet-body) {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    :global(.responsive-filter-sheet-footer) {
        display: grid;
        grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr);
        gap: 0.5rem;
        padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));
    }

    @media (max-width: 767px) {
        :global(.responsive-filter-sheet) {
            inset: 50% auto auto 50% !important;
            width: min(calc(100% - 2rem), 32rem) !important;
            max-height: min(
                calc(var(--mobile-viewport-height, 100dvh) - 2rem),
                42rem
            ) !important;
            border: 1px solid color-mix(in srgb, var(--border) 82%, transparent);
            border-radius: 1.5rem;
            box-shadow:
                0 24px 70px rgb(15 23 42 / 0.22),
                0 8px 24px rgb(15 23 42 / 0.12);
            transform: translate(-50%, -50%) !important;
        }

        :global(.responsive-filter-sheet-footer) {
            padding-bottom: 0.75rem;
        }
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
