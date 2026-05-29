<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        eyebrow = '',
        title = '',
        description = '',
        badgeText = '',
        class: className = '',
        toneClass = '',
        bodyClass = '',
        badgeClass = '',
        children,
        actions,
    }: {
        eyebrow?: string;
        title?: string;
        description?: string;
        badgeText?: string;
        class?: string;
        toneClass?: string;
        bodyClass?: string;
        badgeClass?: string;
        children?: Snippet;
        actions?: Snippet;
    } = $props();
</script>

<section class={cn('overflow-hidden rounded-2xl border border-border/70 bg-background/95 shadow-sm', className)}>
    <div class={cn('border-b border-border/70 px-5 py-4', toneClass)}>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                {#if eyebrow !== ''}
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted-foreground">{eyebrow}</p>
                {/if}
                {#if title !== ''}
                    <h3 class="mt-1 text-lg font-semibold">{title}</h3>
                {/if}
                {#if description !== ''}
                    <p class="mt-1 max-w-3xl text-sm text-muted-foreground">{description}</p>
                {/if}
            </div>
            {#if badgeText !== '' || actions}
                <div class="flex flex-wrap gap-2">
                    {#if badgeText !== ''}
                        <span class={cn('inline-flex w-fit rounded-full border border-border/70 bg-background/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground', badgeClass)}>
                            {badgeText}
                        </span>
                    {/if}
                    {@render actions?.()}
                </div>
            {/if}
        </div>
    </div>
    <div class={cn('p-5', bodyClass)}>
        {@render children?.()}
    </div>
</section>
