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

    let {
        columns = [],
        rows = [],
        class: className = '',
        tone = 'terminal',
        row,
        actions,
    }: {
        columns?: TableColumn[];
        rows?: TableRow[];
        class?: string;
        tone?: 'terminal' | 'default';
        row?: Snippet<[TableSnippetProps]>;
        actions?: Snippet<[TableSnippetProps]>;
    } = $props();

    let computedColumns = $derived(columns);
    const headerRowClass = $derived(
        tone === 'default'
            ? 'border-b border-border/70 bg-muted/20 text-xs font-semibold tracking-wider text-muted-foreground uppercase'
            : 'border-b border-slate-800 bg-[#151d33] text-xs font-semibold tracking-wider text-[#8D99AE] uppercase',
    );
    const stickyHeaderClass = $derived(tone === 'default' ? 'sticky z-30 bg-muted/20' : 'sticky z-30 bg-card');
    const bodyClass = $derived(tone === 'default' ? 'divide-y divide-border/70 text-sm' : 'divide-y divide-slate-800 text-sm');
    const rowClass = $derived(
        tone === 'default'
            ? 'group transition-colors hover:bg-muted/15'
            : 'hover:bg-[#222d4f] transition-colors group',
    );
    const stickyCellClass = $derived(tone === 'default' ? 'sticky z-20 bg-background' : 'sticky z-20 bg-card');
    const actionSeparatorClass = $derived(tone === 'default' ? 'text-border' : 'text-slate-700');
</script>

<div class={cn('w-full overflow-x-auto', className)}>
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class={headerRowClass}>
                {#each columns as col}
                    {#if col}
                        <th
                            class={cn(
                                'py-3 px-4',
                                col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                                col.numeric ? 'tabular-nums font-mono' : '',
                                col.width ? col.width : '',
                                col.sticky ? stickyHeaderClass : '',
                            )}
                            style={col.sticky === 'left' ? `left: ${col.leftOffset ?? '0px'}` : col.sticky === 'right' ? `right: ${col.rightOffset ?? '0px'}` : undefined}
                        >
                            {col.label ?? col.key}
                        </th>
                    {/if}
                {/each}
                <th class={cn('py-3 px-4 text-right sticky right-0', stickyHeaderClass)}>Aksi</th>
            </tr>
        </thead>

        <tbody class={bodyClass}>
            {#each rows as entry, idx (entry.id ?? idx)}
                {@const snippetProps = { row: entry, index: idx, columns: computedColumns }}
                <tr class={rowClass}>
                    {#if row}
                        {@render row(snippetProps)}
                    {:else}
                        {#each computedColumns as col}
                            <td
                                class={cn(
                                    'py-3 px-4 align-middle',
                                    col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : '',
                                    col.numeric ? 'tabular-nums font-mono' : '',
                                    col.sticky ? stickyCellClass : '',
                                )}
                                style={col.sticky === 'left' ? `left: ${col.leftOffset ?? '0px'}` : col.sticky === 'right' ? `right: ${col.rightOffset ?? '0px'}` : undefined}
                            >
                                {String(entry[col.key] ?? '')}
                            </td>
                        {/each}
                    {/if}

                    <td class="py-3 px-4 text-right">
                        {#if actions}
                            {@render actions(snippetProps)}
                        {:else}
                            <div class="opacity-80 group-hover:opacity-100 transition-opacity flex justify-end gap-3">
                                <button class="text-xs font-medium text-accent hover:underline focus:outline-none">Ubah</button>
                                <span class={actionSeparatorClass}>|</span>
                                <button class="text-xs font-medium text-red-400 hover:underline focus:outline-none">Hapus</button>
                            </div>
                        {/if}
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
</div>
