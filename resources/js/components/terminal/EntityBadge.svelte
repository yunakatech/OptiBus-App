<script lang="ts">
    import { cn } from '@/lib/utils';

    let { code = '', variant = 'accent', class: className = '' }: { code?: string; variant?: 'accent' | 'muted'; class?: string } = $props();
</script>

<span class={cn('inline-block border border-dashed px-3 py-1 rounded font-mono text-xs font-bold tracking-wider', variant === 'accent' ? 'border-accent text-accent bg-transparent' : 'border-border text-muted-foreground bg-transparent', className)}>
    {code}
</span>
