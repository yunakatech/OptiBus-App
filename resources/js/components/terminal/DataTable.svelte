<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    type TableColumn = {
        key: string;
        label?: string;
        align?: string;
        width?: string;
        sticky?: string;
        leftOffset?: string;
        rightOffset?: string;
        numeric?: boolean;
    };

    type TableRow = Record<string, any>;

    type TableSnippetProps = {
        row: TableRow;
        index: number;
        columns: TableColumn[];
    };

    type RowKey = string | number;

    let {
        columns = [],
        rows = [],
        class: className = '',
        density = 'comfortable',
        tone = 'default',
        row,
        actions,
        detail,
        expandedRows = [],
    }: {
        columns?: TableColumn[];
        rows?: TableRow[];
        class?: string;
        density?: 'comfortable' | 'compact';
        tone?: 'terminal' | 'default';
        row?: Snippet<[TableSnippetProps]>;
        actions?: Snippet<[TableSnippetProps]>;
        detail?: Snippet<[TableSnippetProps]>;
        expandedRows?: RowKey[];
    } = $props();

    let computedColumns = $derived(columns);
    const isCompact = $derived(density === 'compact');
    const headerRowClass = $derived(
        tone === 'default'
            ? 'border-b border-border/70 bg-muted/60 text-[11px] font-semibold uppercase tracking-[0.06em] text-muted-foreground'
            : 'border-b border-slate-800 bg-[#151d33] text-[11px] font-semibold uppercase tracking-[0.08em] text-[#8D99AE]',
    );
    const stickyHeaderClass = $derived(
        tone === 'default' ? 'sticky z-30 bg-muted/60' : 'sticky z-30 bg-card',
    );
    const stickyActionHeaderClass = $derived(
        tone === 'default'
            ? 'sticky right-0 z-30 border-l border-border/70 bg-muted/60'
            : 'sticky right-0 z-30 border-l border-slate-800 bg-card',
    );
    const bodyClass = $derived(
        tone === 'default'
            ? 'divide-y divide-border/70 text-xs'
            : 'divide-y divide-slate-800 text-xs',
    );
    const rowClass = $derived(
        tone === 'default'
            ? 'group text-foreground transition-colors odd:bg-background even:bg-muted/20 hover:bg-accent/60 hover:text-foreground dark:hover:bg-slate-800/60'
            : 'group text-foreground transition-colors hover:bg-[#ebf3fc] hover:text-foreground dark:hover:bg-slate-800/60',
    );
    const stickyCellClass = $derived(
        tone === 'default'
            ? 'sticky z-20 bg-background transition-colors group-even:bg-muted/20 group-hover:bg-accent/60 dark:group-hover:bg-slate-800/60'
            : 'sticky z-20 bg-card transition-colors group-hover:bg-[#ebf3fc] dark:group-hover:bg-slate-800/60',
    );
    const actionSeparatorClass = $derived(
        tone === 'default' ? 'text-border' : 'text-slate-700',
    );
    const tablePaddingClass = $derived(
        isCompact ? 'px-2.5 py-1.5' : 'px-3 py-2',
    );
    const tableTextClass = $derived(isCompact ? 'text-[11px]' : 'text-xs');
    const compactHeaderRowClass = $derived(
        isCompact
            ? 'border-b border-border/70 bg-muted/20 text-[10px] font-semibold uppercase tracking-[0.06em] text-muted-foreground'
            : headerRowClass,
    );
    const compactBodyClass = $derived(
        isCompact ? 'divide-y divide-border/70 text-[11px]' : bodyClass,
    );
</script>

<div
    class={cn(
        'w-full max-w-full overflow-x-auto rounded-lg border border-border/70 bg-card shadow-xs',
        className,
    )}
>
    <table
        class={cn(
            'min-w-full border-collapse text-left text-foreground',
            tableTextClass,
        )}
    >
        <thead>
            <tr class={compactHeaderRowClass}>
                {#each columns as col}
                    {#if col}
                        <th
                            class={cn(
                                'whitespace-nowrap',
                                tablePaddingClass,
                                col.align === 'right'
                                    ? 'text-right'
                                    : col.align === 'center'
                                      ? 'text-center'
                                      : 'text-left',
                                col.numeric ? 'tabular-nums font-mono' : '',
                                col.width ? col.width : '',
                                col.sticky ? stickyHeaderClass : '',
                            )}
                            style={col.sticky === 'left'
                                ? `left: ${col.leftOffset ?? '0px'}`
                                : col.sticky === 'right'
                                  ? `right: ${col.rightOffset ?? '0px'}`
                                  : undefined}
                        >
                            {col.label ?? col.key}
                        </th>
                    {/if}
                {/each}
                <th
                    class={cn(
                        'sticky right-0 text-right',
                        tablePaddingClass,
                        stickyActionHeaderClass,
                    )}>Aksi</th
                >
            </tr>
        </thead>

        <tbody class={compactBodyClass}>
            {#each rows as entry, idx (entry.id ?? idx)}
                {@const rowId = (entry.id ?? idx) as RowKey}
                {@const snippetProps = {
                    row: entry,
                    index: idx,
                    columns: computedColumns,
                }}
                {@const isExpanded = detail && expandedRows.includes(rowId)}
                <tr class={rowClass}>
                    {#if row}
                        {@render row(snippetProps)}
                    {:else}
                        {#each computedColumns as col}
                            <td
                                class={cn(
                                    'whitespace-nowrap align-middle',
                                    tablePaddingClass,
                                    col.align === 'right'
                                        ? 'text-right'
                                        : col.align === 'center'
                                          ? 'text-center'
                                          : '',
                                    col.numeric ? 'tabular-nums font-mono' : '',
                                    col.sticky ? stickyCellClass : '',
                                )}
                                style={col.sticky === 'left'
                                    ? `left: ${col.leftOffset ?? '0px'}`
                                    : col.sticky === 'right'
                                      ? `right: ${col.rightOffset ?? '0px'}`
                                      : undefined}
                            >
                                {String(entry[col.key] ?? '')}
                            </td>
                        {/each}
                    {/if}

                    <td
                        class={cn(
                            'sticky right-0 whitespace-nowrap text-right',
                            tablePaddingClass,
                            tone === 'default'
                                ? 'z-20 border-l border-border/60 bg-background transition-colors group-even:bg-muted/20 group-hover:bg-accent/60 dark:group-hover:bg-slate-800/60'
                                : 'z-20 border-l border-slate-800 bg-card transition-colors group-hover:bg-[#ebf3fc] dark:group-hover:bg-slate-800/60',
                        )}
                    >
                        {#if actions}
                            {@render actions(snippetProps)}
                        {:else}
                            <div
                                class="flex justify-end gap-2 opacity-80 transition-opacity group-hover:opacity-100"
                            >
                                <button
                                    class="text-[10px] font-medium text-primary hover:underline focus:outline-none"
                                    >Ubah</button
                                >
                                <span class={actionSeparatorClass}>|</span>
                                <button
                                    class="text-[10px] font-medium text-destructive hover:underline focus:outline-none"
                                    >Hapus</button
                                >
                            </div>
                        {/if}
                    </td>
                </tr>

                {#if detail && isExpanded}
                    <tr
                        class={tone === 'default'
                            ? 'bg-muted/10'
                            : 'bg-[#11182a]'}
                    >
                        <td
                            colspan={computedColumns.length + 1}
                            class={tone === 'default'
                                ? 'border-b border-border/70 px-3 py-3'
                                : 'border-b border-slate-800 px-3 py-3'}
                        >
                            {@render detail(snippetProps)}
                        </td>
                    </tr>
                {/if}
            {/each}
        </tbody>
    </table>
</div>
