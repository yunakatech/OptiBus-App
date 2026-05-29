<script lang="ts">
    import type { Snippet } from 'svelte';
    import { getContext } from 'svelte';
    import { cn } from '@/lib/utils';
    import { DROPDOWN_MENU_CONTEXT, type DropdownMenuContext } from './context';

    type AsChildProps = {
        class?: string;
        onClick?: (event?: MouseEvent) => void;
        onclick?: (event?: MouseEvent) => void;
        [key: string]: any;
    };

    let {
        asChild = false,
        class: className = '',
        disabled = false,
        onclick,
        onClick,
        children,
    }: {
        asChild?: boolean;
        class?: string;
        disabled?: boolean;
        onclick?: (event: MouseEvent) => void;
        onClick?: (event: MouseEvent) => void;
        children?: Snippet<[AsChildProps]>;
    } = $props();

    const { setOpen } = getContext<DropdownMenuContext>(DROPDOWN_MENU_CONTEXT);

    const handleClick = (event?: MouseEvent) => {
        if (disabled) {
            event?.preventDefault();
            return;
        }

        const callback = onclick ?? onClick;
        callback?.(event as MouseEvent);
        setOpen(false);
    };

    const classes = () =>
        cn(
            'flex w-full cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50',
            className,
        );
</script>

{#if asChild}
    {@render children?.({ class: classes(), onClick: handleClick, onclick: handleClick })}
{:else}
    <button type="button" class={classes()} onclick={handleClick} disabled={disabled}>
        {@render children?.({})}
    </button>
{/if}
