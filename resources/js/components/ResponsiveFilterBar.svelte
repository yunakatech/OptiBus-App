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
            <span class="responsive-filter-summary-label">{label}</span>
            {#if summary}
                <span class="responsive-filter-summary-value">{summary}</span>
            {/if}
        </div>
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
            {#if activeCount > 0}
                <span
                    class="ml-1.5 rounded-full bg-background/20 px-1.5 py-0.5 text-xs"
                >
                    {activeCount}
                </span>
            {/if}
        </Button>
    </div>

    <Sheet bind:open={sheetOpen}>
        <SheetContent
            side="bottom"
            showCloseButton={false}
            class="responsive-filter-sheet gap-0 rounded-t-3xl p-0"
        >
            <SheetHeader
                class="border-b border-border/70 px-4 pt-4 pb-3 text-left"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <SheetTitle>Filter {label}</SheetTitle>
                        <SheetDescription>
                            Atur pilihan lalu tekan Terapkan.
                        </SheetDescription>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border/70 text-muted-foreground transition hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        aria-label="Tutup filter"
                        onclick={cancelFilters}
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </SheetHeader>

            <div class="responsive-filter-sheet-body px-4 py-4">
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
        border-radius: 0.875rem;
        background: color-mix(in srgb, var(--card) 92%, transparent);
        padding: 0.5rem;
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
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1rem;
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
