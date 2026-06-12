<script lang="ts">
    import { cn } from '@/lib/utils';
    import type { Snippet } from 'svelte';

    let {
        columns = [],
        rows = [],
        class: className = '',
    }: {
        columns?: Array<{ key: string; label?: string; align?: string; width?: string; sticky?: 'left' | 'right'; leftOffset?: string; rightOffset?: string; numeric?: boolean }>;
        rows?: Array<Record<string, any>>;
        class?: string;
    } = $props();
</script>

<div class={cn('w-full overflow-x-auto', className)}>
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-800 bg-[#151d33] text-xs font-semibold tracking-wider text-[#8D99AE] uppercase">
                {#each columns as col}
                    {#if col}
                        <th
                            class={cn(
                                'py-3 px-4',
                                col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                                col.numeric ? 'tabular-nums font-mono' : '',
                                col.width ? col.width : '',
                                col.sticky ? 'sticky z-30 bg-card' : '',
                            )}
                            style={col.sticky === 'left' ? `left: ${col.leftOffset ?? '0px'}` : col.sticky === 'right' ? `right: ${col.rightOffset ?? '0px'}` : undefined}
                        >
                            {col.label ?? col.key}
                        </th>
                    {/if}
                {/each}
                <th class="py-3 px-4 text-right sticky right-0 z-30 bg-card">Aksi</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-800 text-sm">
            {#each rows as row, idx (row.id ?? idx)}
                <tr class="hover:bg-[#222d4f] transition-colors group">
                    <slot name="row" row={row} index={idx} columns={computedColumns}>
                        {#each columns as col}
                            <td
                                class={cn(
                                    'py-3 px-4 align-middle',
                                    col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : '',
                                    col.numeric ? 'tabular-nums font-mono' : '',
                                    col.sticky ? 'sticky z-20 bg-card' : '',
                                )}
                                style={col.sticky === 'left' ? `left: ${col.leftOffset ?? '0px'}` : col.sticky === 'right' ? `right: ${col.rightOffset ?? '0px'}` : undefined}
                            >
                                {String(row[col.key] ?? '')}
                            </td>
                        {/each}

                        <td class="py-3 px-4 text-right">
                            <slot name="actions" row={row} index={idx} columns={computedColumns}>
                                <div class="opacity-80 group-hover:opacity-100 transition-opacity flex justify-end gap-3">
                                    <button class="text-xs font-medium text-accent hover:underline focus:outline-none">Ubah</button>
                                    <span class="text-slate-700">|</span>
                                    <button class="text-xs font-medium text-red-400 hover:underline focus:outline-none">Hapus</button>
                                </div>
                            </slot>
                        </td>
                    </slot>
                </tr>
            {/each}
        </tbody>
    </table>
</div>
