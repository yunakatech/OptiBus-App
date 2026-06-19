<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import Building2 from 'lucide-svelte/icons/building-2';
    import Check from 'lucide-svelte/icons/check';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import LoaderCircle from 'lucide-svelte/icons/loader-circle';
    import MapPin from 'lucide-svelte/icons/map-pin';
    import Search from 'lucide-svelte/icons/search';
    import X from 'lucide-svelte/icons/x';
    import { onMount, tick } from 'svelte';
    import { cn } from '@/lib/utils';
    import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
    import type { ActivePool, ActiveTenant, PoolOption, TenantOption } from '@/types/auth';

    type Kind = 'tenant' | 'pool';
    type Mode = 'desktop' | 'mobile';

    type SwitcherOption = {
        id: number;
        label: string;
        meta: string;
        searchText: string;
    };

    let {
        kind,
        mode,
        class: className = '',
    }: {
        kind: Kind;
        mode: Mode;
        class?: string;
    } = $props();

    const auth = $derived(page.props.auth ?? null);
    const tenants = $derived((auth?.tenants ?? []) as TenantOption[]);
    const pools = $derived((auth?.pools ?? []) as PoolOption[]);
    const activeTenant = $derived((auth?.active_tenant ?? null) as ActiveTenant | null);
    const activePool = $derived((auth?.active_pool ?? null) as ActivePool | null);
    const isSuperAdmin = $derived(Boolean(auth?.user?.is_super_admin));

    const label = $derived.by(() => (kind === 'tenant' ? 'Tenant' : 'Pool'));
    const Icon = $derived.by(() => (kind === 'tenant' ? Building2 : MapPin));
    const allLabel = $derived.by(() => (kind === 'tenant' ? 'Semua Tenant' : 'Semua Pool'));
    const allMeta = $derived.by(() => (kind === 'tenant' ? 'Platform' : ''));
    const endpoint = $derived.by(() =>
        kind === 'tenant' ? '/api/admin/tenant/switch' : '/api/admin/pool/switch',
    );
    const payloadKey = $derived.by(() =>
        kind === 'tenant' ? 'tenant_id' : 'pool_id',
    );
    const activeId = $derived.by(() =>
        kind === 'tenant' ? activeTenant?.id ?? 0 : activePool?.id ?? 0,
    );
    const activeLabel = $derived.by(() =>
        kind === 'tenant' ? activeTenant?.name ?? allLabel : activePool?.name ?? allLabel,
    );
    const hasAccess = $derived.by(() =>
        kind === 'tenant' ? isSuperAdmin && tenants.length > 0 : pools.length > 0,
    );

    const options = $derived.by((): SwitcherOption[] => {
        if (kind === 'tenant') {
            return tenants.map((tenant) => ({
                id: tenant.id,
                label: tenant.name,
                meta: tenant.slug,
                searchText: `${tenant.name} ${tenant.slug}`.toLowerCase(),
            }));
        }

        return pools.map((pool) => ({
            id: pool.id,
            label: pool.name,
            meta: pool.code ?? '',
            searchText: `${pool.name} ${pool.code ?? ''}`.toLowerCase(),
        }));
    });

    let open = $state(false);
    let pendingId = $state<number | null>(null);
    let errorMessage = $state('');
    let searchQuery = $state('');
    let rootElement = $state<HTMLElement | null>(null);
    let searchInput = $state<HTMLInputElement | null>(null);
    let optionButtons: HTMLButtonElement[] = [];

    const showSearch = $derived(options.length > 5);

    const filteredOptions = $derived(
        options.filter((item) =>
            item.searchText.includes(searchQuery.trim().toLowerCase()),
        ),
    );

    const csrfToken = () => {
        if (typeof document === 'undefined') {
            return '';
        }

        return (
            (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement | null)
                ?.content ?? ''
        );
    };

    const close = () => {
        open = false;
        searchQuery = '';
        errorMessage = '';
    };

    const openPanel = async () => {
        if (pendingId !== null || !hasAccess) {
            return;
        }

        open = !open;
        errorMessage = '';
        searchQuery = '';

        if (open) {
            await tick();
            if (showSearch && searchInput) {
                searchInput.focus();
                searchInput.select();
                return;
            }

            optionButtons[0]?.focus();
        }
    };

    const switchContext = async (id: number) => {
        if (pendingId !== null || id === activeId) {
            close();
            return;
        }

        pendingId = id;
        errorMessage = '';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ [payloadKey]: id }),
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                throw new Error(
                    (payload?.message as string | undefined) ??
                        (payload?.error as string | undefined) ??
                        'Gagal mengganti konteks.',
                );
            }

            close();

            const currentPath = `${window.location.pathname}${window.location.search}`;
            router.visit(currentPath, {
                preserveScroll: false,
                preserveState: false,
            });
        } catch (error) {
            errorMessage =
                error instanceof Error ? error.message : 'Gagal mengganti konteks.';
        } finally {
            pendingId = null;
        }
    };

    const focusOption = (index: number) => {
        const button = optionButtons[index];
        button?.focus();
    };

    const moveFocus = (currentIndex: number, delta: number) => {
        const total = filteredOptions.length + 1;
        if (total <= 0) {
            return;
        }

        const nextIndex = (currentIndex + delta + total) % total;
        focusOption(nextIndex);
    };

    const handleOptionKeydown = (
        event: KeyboardEvent,
        index: number,
        itemId: number,
    ) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            moveFocus(index, 1);
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            moveFocus(index, -1);
            return;
        }

        if (event.key === 'Home') {
            event.preventDefault();
            focusOption(0);
            return;
        }

        if (event.key === 'End') {
            event.preventDefault();
            focusOption(filteredOptions.length);
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            void switchContext(itemId);
        }
    };

    const handleSearchKeydown = (event: KeyboardEvent) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            focusOption(0);
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            focusOption(filteredOptions.length);
        }
    };

    const handleTriggerKeydown = (event: KeyboardEvent) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            void openPanel();
        }
    };

    const handleDesktopOutsideClick = (event: PointerEvent) => {
        if (mode !== 'desktop' || !open) {
            return;
        }

        const target = event.target as Node | null;
        if (rootElement && target && !rootElement.contains(target)) {
            close();
        }
    };

    const handleDesktopKeydown = (event: KeyboardEvent) => {
        if (mode === 'desktop' && event.key === 'Escape' && open) {
            close();
        }
    };

    onMount(() => {
        if (mode !== 'desktop') {
            return;
        }

        document.addEventListener(
            'pointerdown',
            handleDesktopOutsideClick as EventListener,
            true,
        );
        document.addEventListener('keydown', handleDesktopKeydown as EventListener);

        return () => {
            document.removeEventListener(
                'pointerdown',
                handleDesktopOutsideClick as EventListener,
                true,
            );
            document.removeEventListener(
                'keydown',
                handleDesktopKeydown as EventListener,
            );
        };
    });

    $effect(() => {
        if (!open) {
            return;
        }

        void tick().then(() => {
            if (showSearch && searchInput) {
                searchInput.focus();
                return;
            }

            optionButtons[0]?.focus();
        });
    });
</script>

{#if hasAccess}
    {#if mode === 'desktop'}
        <div
            bind:this={rootElement}
            class={cn('relative w-full', className)}
        >
            <button
                type="button"
                class={cn(
                    'flex min-h-11 w-full items-center gap-2 rounded-xl border border-sidebar-border/70 bg-background/80 px-3 py-2 text-left text-sm font-medium text-foreground shadow-sm transition hover:border-slate-300 hover:bg-slate-50 active:bg-slate-100 focus-visible:border-primary/40 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                    open ? 'border-primary/35 bg-slate-50' : '',
                )}
                aria-haspopup="listbox"
                aria-expanded={open}
                aria-label={`${label} aktif: ${activeLabel}`}
                onclick={openPanel}
                onkeydown={handleTriggerKeydown}
            >
                <Icon class="size-4 shrink-0 text-primary" />
                <span class="min-w-0 flex-1 text-left">
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        {label}
                    </span>
                    <span class="block truncate text-sm font-semibold text-foreground">
                        {activeLabel}
                    </span>
                </span>
                {#if pendingId !== null}
                    <LoaderCircle class="size-4 shrink-0 animate-spin text-muted-foreground" />
                {:else}
                    <ChevronDown class="size-4 shrink-0 text-muted-foreground" />
                {/if}
            </button>

            {#if open}
                <div
                    class="absolute left-0 top-full z-50 mt-2 w-[min(22rem,calc(100vw-1.5rem))] rounded-2xl border border-sidebar-border/70 bg-background p-3 shadow-xl"
                    role="dialog"
                    tabindex="-1"
                    aria-modal="false"
                    aria-label={`${label} switcher`}
                    onkeydown={(event) => {
                        if (event.key === 'Escape') {
                            close();
                        }
                    }}
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                {label}
                            </p>
                            <p class="mt-0.5 truncate text-sm font-semibold text-foreground">
                                {activeLabel}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-slate-50 hover:text-foreground active:bg-slate-100"
                            aria-label="Tutup"
                            onclick={close}
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    {#if showSearch}
                        <div class="mt-3 flex items-center gap-2 rounded-xl border border-input bg-background px-3">
                            <Search class="size-4 shrink-0 text-muted-foreground" />
                            <input
                                bind:this={searchInput}
                                bind:value={searchQuery}
                                type="search"
                                class="h-10 w-full min-w-0 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                                placeholder={`Cari ${kind === 'tenant' ? 'tenant' : 'pool'}...`}
                                aria-label={`Cari ${label.toLowerCase()}`}
                                onkeydown={handleSearchKeydown}
                            />
                        </div>
                    {/if}

                    <div
                        class="mt-3 max-h-60 overflow-y-auto pr-1 scrollbar-thin"
                        role="listbox"
                        aria-label={`${label} options`}
                    >
                        <button
                            type="button"
                            class={cn(
                                'flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-50 active:bg-slate-100 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                activeId === 0 ? 'bg-slate-50 font-semibold text-foreground' : 'text-foreground',
                            )}
                            role="option"
                            aria-selected={activeId === 0}
                            disabled={pendingId !== null}
                            bind:this={optionButtons[0]}
                            onclick={() => void switchContext(0)}
                            onkeydown={(event) => handleOptionKeydown(event, 0, 0)}
                        >
                            <Check
                                class={cn(
                                    'size-4 shrink-0',
                                    activeId === 0 ? 'opacity-100 text-primary' : 'opacity-0',
                                )}
                            />
                            <span class="min-w-0 flex-1 truncate">{allLabel}</span>
                            {#if allMeta}
                                <span class="shrink-0 text-[11px] text-muted-foreground">
                                    {allMeta}
                                </span>
                            {/if}
                        </button>

                        {#each filteredOptions as item, index (item.id)}
                            <button
                                type="button"
                                class={cn(
                                    'flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-50 active:bg-slate-100 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                    activeId === item.id ? 'bg-slate-50 font-semibold text-foreground' : 'text-foreground',
                                )}
                                role="option"
                                aria-selected={activeId === item.id}
                                disabled={pendingId !== null}
                                bind:this={optionButtons[index + 1]}
                                onclick={() => void switchContext(item.id)}
                                onkeydown={(event) => handleOptionKeydown(event, index + 1, item.id)}
                            >
                                <Check
                                    class={cn(
                                        'size-4 shrink-0',
                                        activeId === item.id ? 'opacity-100 text-primary' : 'opacity-0',
                                    )}
                                />
                                <span class="min-w-0 flex-1 truncate">{item.label}</span>
                                {#if item.meta}
                                    <span class="shrink-0 text-[11px] text-muted-foreground">
                                        {item.meta}
                                    </span>
                                {/if}
                            </button>
                        {/each}

                        {#if filteredOptions.length === 0}
                            <div class="rounded-xl border border-dashed border-border/70 px-3 py-3 text-sm text-muted-foreground">
                                Tidak ada pilihan yang cocok.
                            </div>
                        {/if}
                    </div>

                    {#if errorMessage}
                        <p class="mt-3 text-xs text-destructive">{errorMessage}</p>
                    {/if}
                </div>
            {/if}
        </div>
    {:else}
        <Sheet bind:open={open}>
            <button
                type="button"
                class={cn(
                    'flex min-h-11 w-full items-center gap-2 rounded-xl border border-sidebar-border/70 bg-background/80 px-3 py-2 text-left text-sm font-medium text-foreground shadow-sm transition hover:border-slate-300 hover:bg-slate-50 active:bg-slate-100 focus-visible:border-primary/40 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                    className,
                )}
                aria-haspopup="dialog"
                aria-expanded={open}
                aria-label={`${label} aktif: ${activeLabel}`}
                onclick={openPanel}
                onkeydown={handleTriggerKeydown}
            >
                <Icon class="size-4 shrink-0 text-primary" />
                <span class="min-w-0 flex-1 text-left">
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        {label}
                    </span>
                    <span class="block truncate text-sm font-semibold text-foreground">
                        {activeLabel}
                    </span>
                </span>
                {#if pendingId !== null}
                    <LoaderCircle class="size-4 shrink-0 animate-spin text-muted-foreground" />
                {:else}
                    <ChevronDown class="size-4 shrink-0 text-muted-foreground" />
                {/if}
            </button>

            {#if open}
                <SheetContent
                    side="bottom"
                    class="max-h-[85svh] rounded-t-2xl border-t border-sidebar-border/70 p-0"
                >
                    <SheetHeader class="border-b border-sidebar-border/70 px-4 py-4 text-left">
                        <SheetTitle class="text-base">{label}</SheetTitle>
                        <p class="truncate text-sm text-muted-foreground">{activeLabel}</p>
                    </SheetHeader>

                    <div class="flex max-h-[calc(85svh-6.5rem)] flex-col gap-3 px-4 py-4">
                        {#if showSearch}
                            <div class="flex items-center gap-2 rounded-xl border border-input bg-background px-3">
                                <Search class="size-4 shrink-0 text-muted-foreground" />
                                <input
                                    bind:this={searchInput}
                                    bind:value={searchQuery}
                                    type="search"
                                    class="h-10 w-full min-w-0 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                                    placeholder={`Cari ${kind === 'tenant' ? 'tenant' : 'pool'}...`}
                                    aria-label={`Cari ${label.toLowerCase()}`}
                                    onkeydown={handleSearchKeydown}
                                />
                            </div>
                        {/if}

                        <div
                            class="max-h-60 overflow-y-auto pr-1 scrollbar-thin"
                            role="listbox"
                            tabindex="-1"
                            aria-label={`${label} options`}
                            onkeydown={(event) => {
                                if (event.key === 'Escape') {
                                    close();
                                }
                            }}
                        >
                            <button
                                type="button"
                                class={cn(
                                    'flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-50 active:bg-slate-100 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                    activeId === 0 ? 'bg-slate-50 font-semibold text-foreground' : 'text-foreground',
                                )}
                                role="option"
                                aria-selected={activeId === 0}
                                disabled={pendingId !== null}
                                bind:this={optionButtons[0]}
                                onclick={() => void switchContext(0)}
                                onkeydown={(event) => handleOptionKeydown(event, 0, 0)}
                            >
                                <Check
                                    class={cn(
                                        'size-4 shrink-0',
                                        activeId === 0 ? 'opacity-100 text-primary' : 'opacity-0',
                                    )}
                                />
                                <span class="min-w-0 flex-1 truncate">{allLabel}</span>
                                {#if allMeta}
                                    <span class="shrink-0 text-[11px] text-muted-foreground">
                                        {allMeta}
                                    </span>
                                {/if}
                            </button>

                            {#each filteredOptions as item, index (item.id)}
                                <button
                                    type="button"
                                    class={cn(
                                        'flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-50 active:bg-slate-100 focus-visible:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                        activeId === item.id ? 'bg-slate-50 font-semibold text-foreground' : 'text-foreground',
                                    )}
                                    role="option"
                                    aria-selected={activeId === item.id}
                                    disabled={pendingId !== null}
                                    bind:this={optionButtons[index + 1]}
                                    onclick={() => void switchContext(item.id)}
                                    onkeydown={(event) => handleOptionKeydown(event, index + 1, item.id)}
                                >
                                    <Check
                                        class={cn(
                                            'size-4 shrink-0',
                                            activeId === item.id ? 'opacity-100 text-primary' : 'opacity-0',
                                        )}
                                    />
                                    <span class="min-w-0 flex-1 truncate">{item.label}</span>
                                    {#if item.meta}
                                        <span class="shrink-0 text-[11px] text-muted-foreground">
                                            {item.meta}
                                        </span>
                                    {/if}
                                </button>
                            {/each}

                            {#if filteredOptions.length === 0}
                                <div class="rounded-xl border border-dashed border-border/70 px-3 py-3 text-sm text-muted-foreground">
                                    Tidak ada pilihan yang cocok.
                                </div>
                            {/if}
                        </div>

                        {#if errorMessage}
                            <p class="text-xs text-destructive">{errorMessage}</p>
                        {/if}
                    </div>
                </SheetContent>
            {/if}
        </Sheet>
    {/if}
{/if}
