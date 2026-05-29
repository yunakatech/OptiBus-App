<script lang="ts">
    import type { Snippet } from 'svelte';
    import { getContext } from 'svelte';
    import { cn } from '@/lib/utils';
    import { DROPDOWN_MENU_CONTEXT, type DropdownMenuContext } from './context';

    let {
        align = 'start',
        side = 'bottom',
        sideOffset = 0,
        class: className = '',
        children,
    }: {
        align?: 'start' | 'center' | 'end';
        side?: 'top' | 'right' | 'bottom' | 'left';
        sideOffset?: number;
        class?: string;
        children?: Snippet;
    } = $props();

    const { open, setOpen, triggerElement, setContentElement } = getContext<DropdownMenuContext>(DROPDOWN_MENU_CONTEXT);
    let contentElement = $state<HTMLDivElement | null>(null);

    const portal = (node: HTMLElement) => {
        if (typeof document === 'undefined') {
            return {};
        }

        document.body.appendChild(node);

        return {
            destroy() {
                if (node.parentNode) {
                    node.parentNode.removeChild(node);
                }
            },
        };
    };

    $effect(() => {
        setContentElement(contentElement);

        return () => {
            setContentElement(null);
        };
    });

    const alignClasses: Record<string, string> = {
        start: 'left-0',
        center: 'left-1/2 -translate-x-1/2',
        end: 'right-0',
    };

    const sideClasses: Record<string, string> = {
        bottom: 'top-full',
        top: 'bottom-full',
        left: 'right-full',
        right: 'left-full',
    };

    const close = () => setOpen(false);

    const offsetStyle = () => {
        const trigger = triggerElement();
        if (trigger && typeof window !== 'undefined') {
            const rect = trigger.getBoundingClientRect();
            const vertical =
                side === 'top'
                    ? `bottom: ${Math.max(window.innerHeight - rect.top + sideOffset, 8)}px;`
                    : `top: ${Math.min(rect.bottom + sideOffset, window.innerHeight - 8)}px;`;

            if (align === 'end') {
                return `${vertical} right: ${Math.max(window.innerWidth - rect.right, 8)}px;`;
            }

            if (align === 'center') {
                return `${vertical} left: ${rect.left + rect.width / 2}px; transform: translateX(-50%);`;
            }

            return `${vertical} left: ${Math.max(rect.left, 8)}px;`;
        }

        switch (side) {
            case 'top':
                return `margin-bottom: ${sideOffset}px;`;
            case 'left':
                return `margin-right: ${sideOffset}px;`;
            case 'right':
                return `margin-left: ${sideOffset}px;`;
            default:
                return `margin-top: ${sideOffset}px;`;
        }
    };
</script>

{#if open()}
    <div
        bind:this={contentElement}
        use:portal
        class={cn(
            'fixed z-50 min-w-48 rounded-md border bg-popover p-2 text-popover-foreground shadow-md',
            triggerElement() ? '' : (alignClasses[align] ?? alignClasses.start),
            triggerElement() ? '' : (sideClasses[side] ?? sideClasses.bottom),
            className,
        )}
        style={offsetStyle()}
        role="menu"
        tabindex="-1"
        onkeydown={(event) => event.key === 'Escape' && close()}
    >
        {@render children?.()}
    </div>
{/if}
