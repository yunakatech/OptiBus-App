<script lang="ts">
    import type { Snippet } from 'svelte';
    import { Card, CardHeader, CardContent, CardTitle, CardAction } from '@/components/ui/card';
    import { cn } from '@/lib/utils';

    let {
        title = '',
        subtitle = '',
        class: className = '',
        actions,
        children,
    }: {
        title?: string;
        subtitle?: string;
        class?: string;
        actions?: Snippet;
        children?: Snippet;
    } = $props();
</script>

<div class={cn('relative', className)}>
    <Card class="relative overflow-visible">
        <div class="absolute left-0 top-4 bottom-4 w-1 rounded-r-md bg-accent/90 pointer-events-none"></div>

        <CardHeader class="pl-8">
            <div>
                <CardTitle class="uppercase tracking-tight font-['Space_Grotesk',sans-serif] text-sm">{title}</CardTitle>
                {#if subtitle}
                    <div class="text-xs text-muted mt-0.5">{subtitle}</div>
                {/if}
            </div>

            <CardAction>
                {@render actions?.()}
            </CardAction>
        </CardHeader>

        <CardContent>
            {@render children?.()}
        </CardContent>
    </Card>
</div>
