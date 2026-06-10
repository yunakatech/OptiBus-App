<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    let {
        current = 0,
        max = 0,
        label = '',
    }: {
        current?: number;
        max?: number;
        label?: string;
    } = $props();

    const pct = $derived(max > 0 ? Math.round((current / max) * 100) : 0);
    const isFull = $derived(max > 0 && current >= max);
</script>

{#if max > 0}
    <div class="flex items-center gap-2 text-xs">
        {#if label}
            <span class="text-muted-foreground">{label}:</span>
        {/if}
        <span class="font-medium">{current}/{max}</span>
        <div class="w-16 h-1.5 bg-muted rounded-full overflow-hidden">
            <div
                class="h-full rounded-full transition-all {isFull ? 'bg-destructive' : pct >= 80 ? 'bg-amber-500' : 'bg-primary'}"
                style="width: {Math.min(pct, 100)}%"
            ></div>
        </div>
        {#if isFull}
            <Badge variant="destructive" class="text-[10px] py-0">Penuh</Badge>
        {:else if pct >= 80}
            <Badge variant="outline" class="text-[10px] py-0 text-amber-600">{pct}%</Badge>
        {/if}
    </div>
{/if}
