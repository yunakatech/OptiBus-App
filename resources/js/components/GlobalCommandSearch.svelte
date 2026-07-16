<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import Search from 'lucide-svelte/icons/search';
    import X from 'lucide-svelte/icons/x';
    import { onMount, tick } from 'svelte';
    import { Button } from '@/components/ui/button';
    import {
        flattenNavSections,
        getVisibleNavSections,
        type FlatNavItem,
    } from '@/lib/navigation';
    import { toUrl } from '@/lib/utils';

    let open = $state(false);
    let query = $state('');
    let activeIndex = $state(0);
    let searchInput = $state<HTMLInputElement | null>(null);

    const sections = $derived(getVisibleNavSections(page.props.auth));
    const items = $derived(flattenNavSections(sections));
    const shortcutLabel = $derived.by(() => {
        if (typeof navigator === 'undefined') {
            return 'Ctrl K';
        }

        return /Mac|iPhone|iPad|iPod/i.test(navigator.platform)
            ? '⌘ K'
            : 'Ctrl K';
    });
    const filteredItems = $derived.by(() => {
        const needle = query.trim().toLowerCase();

        if (!needle) {
            return items;
        }

        return items.filter((item) => {
            const haystack = `${item.title} ${item.sectionTitle} ${toUrl(
                item.href,
            )}`.toLowerCase();

            return haystack.includes(needle);
        });
    });

    function openCommand(): void {
        open = true;
        query = '';
        activeIndex = 0;
        void tick().then(() => searchInput?.focus());
    }

    function closeCommand(): void {
        open = false;
        query = '';
        activeIndex = 0;
    }

    function visitItem(item: FlatNavItem | undefined): void {
        if (!item) {
            return;
        }

        const href = toUrl(item.href);
        closeCommand();
        router.visit(href, {
            preserveScroll: false,
            preserveState: false,
        });
    }

    function handleInputKeydown(event: KeyboardEvent): void {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = Math.min(activeIndex + 1, filteredItems.length - 1);
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            visitItem(filteredItems[activeIndex]);
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeCommand();
        }
    }

    $effect(() => {
        if (activeIndex > filteredItems.length - 1) {
            activeIndex = Math.max(filteredItems.length - 1, 0);
        }
    });

    onMount(() => {
        const handler = (event: KeyboardEvent) => {
            if (
                (event.metaKey || event.ctrlKey) &&
                event.key.toLowerCase() === 'k'
            ) {
                event.preventDefault();
                openCommand();
            }
        };

        window.addEventListener('keydown', handler);

        return () => window.removeEventListener('keydown', handler);
    });
</script>

<button
    type="button"
    class="hidden h-9 w-full max-w-xl items-center gap-2 rounded-md border border-input bg-card px-3 text-left text-sm text-muted-foreground shadow-xs transition hover:border-primary/35 hover:bg-background focus-visible:border-ring focus-visible:ring-ring/40 focus-visible:ring-2 focus-visible:outline-none md:flex"
    onclick={openCommand}
    aria-label="Cari menu"
>
    <Search class="size-4 shrink-0" />
    <span class="min-w-0 flex-1 truncate">Cari menu</span>
    <kbd
        class="rounded border border-border bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
    >
        {shortcutLabel}
    </kbd>
</button>

{#if open}
    <div
        class="fixed inset-0 z-[120] flex items-start justify-center px-4 pt-[12vh]"
    >
        <button
            type="button"
            class="fixed inset-0 bg-foreground/35 backdrop-blur-[1px]"
            aria-label="Tutup pencarian"
            onclick={closeCommand}
        ></button>

        <div
            role="dialog"
            aria-modal="true"
            aria-label="Cari menu"
            class="relative z-10 w-full max-w-2xl overflow-hidden rounded-lg border border-border bg-popover text-popover-foreground shadow-lg"
        >
            <div
                class="flex h-12 items-center gap-3 border-b border-border px-3"
            >
                <Search class="size-4 shrink-0 text-muted-foreground" />
                <input
                    bind:this={searchInput}
                    bind:value={query}
                    class="h-full min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    placeholder="Cari menu"
                    onkeydown={handleInputKeydown}
                />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-8 rounded-md"
                    onclick={closeCommand}
                    aria-label="Tutup pencarian"
                >
                    <X class="size-4" />
                </Button>
            </div>

            <div class="max-h-[420px] overflow-y-auto p-2">
                {#if filteredItems.length > 0}
                    {#each filteredItems as item, index (toUrl(item.href))}
                        <button
                            type="button"
                            class={`flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm transition ${activeIndex === index ? 'bg-accent text-accent-foreground' : 'hover:bg-accent/70 hover:text-accent-foreground'}`}
                            onmouseenter={() => (activeIndex = index)}
                            onclick={() => visitItem(item)}
                        >
                            {#if item.icon}
                                <item.icon
                                    class="size-4 shrink-0 text-muted-foreground"
                                />
                            {/if}
                            <span class="min-w-0 flex-1 truncate font-medium">
                                {item.title}
                            </span>
                            <span
                                class="shrink-0 text-xs text-muted-foreground"
                            >
                                {item.sectionTitle}
                            </span>
                        </button>
                    {/each}
                {:else}
                    <div
                        class="rounded-md border border-dashed border-border p-4 text-center text-sm text-muted-foreground"
                    >
                        Tidak ada menu
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/if}
