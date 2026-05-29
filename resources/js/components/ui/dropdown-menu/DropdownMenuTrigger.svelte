<script lang="ts">
    import type { Snippet } from 'svelte';
    import { getContext } from 'svelte';
    import { cn } from '@/lib/utils';
    import { DROPDOWN_MENU_CONTEXT, type DropdownMenuContext } from './context';

    type TriggerProps = {
        onclick?: (event: MouseEvent) => void;
        'aria-expanded'?: boolean;
        'data-state'?: 'open' | 'closed';
        [key: string]: any;
    };

    let {
        asChild = false,
        class: className = '',
        children,
    }: { asChild?: boolean; class?: string; children?: Snippet<[TriggerProps]> } = $props();

    const { open, setOpen, setTriggerElement } = getContext<DropdownMenuContext>(DROPDOWN_MENU_CONTEXT);
    let triggerElement = $state<HTMLElement | null>(null);

    $effect(() => {
        setTriggerElement(triggerElement);
    });

    const handleClick = (event?: MouseEvent) => {
        event?.stopPropagation();
        setOpen(!open());
    };

</script>

{#if asChild}
    <span
        bind:this={triggerElement}
        class={cn('inline-flex', className)}
        role="presentation"
        data-state={open() ? 'open' : 'closed'}
        onclick={handleClick}
    >
        {@render children?.({ onclick: handleClick, 'aria-expanded': open(), 'data-state': open() ? 'open' : 'closed' })}
    </span>
{:else}
    <button type="button" class={className} bind:this={triggerElement} onclick={handleClick} aria-expanded={open()} data-state={open() ? 'open' : 'closed'}>
        {@render children?.({})}
    </button>
{/if}
