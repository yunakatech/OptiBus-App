<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    type Variant =
        | 'default'
        | 'secondary'
        | 'ghost'
        | 'destructive'
        | 'outline'
        | 'link';
    type Size = 'default' | 'sm' | 'lg' | 'icon';
    type AsChildProps = {
        class?: string;
        onClick?: (event: MouseEvent) => void;
        [key: string]: any;
    };

    const base =
        'inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium whitespace-nowrap transition-[background-color,border-color,color,box-shadow] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/45 focus-visible:ring-offset-0 disabled:pointer-events-none disabled:opacity-50 md:[html[data-density=compact]_&]:text-[13px]';

    const variants: Record<Variant, string> = {
        default: 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
        secondary:
            'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
        ghost: 'hover:bg-accent hover:text-accent-foreground',
        destructive:
            'bg-destructive text-destructive-foreground shadow-xs hover:bg-destructive/90',
        outline: 'border border-input bg-card shadow-xs hover:border-primary/30 hover:bg-accent hover:text-accent-foreground',
        link: 'text-primary underline-offset-4 hover:underline',
    };

    const sizes: Record<Size, string> = {
        default:
            'h-9 px-4 py-2 md:[html[data-density=compact]_&]:h-8 md:[html[data-density=compact]_&]:px-3.5',
        sm: 'h-8 rounded-md px-3 text-xs md:[html[data-density=compact]_&]:h-7 md:[html[data-density=compact]_&]:px-2.5',
        lg: 'h-10 rounded-md px-8 md:[html[data-density=compact]_&]:h-9 md:[html[data-density=compact]_&]:px-6',
        icon: 'h-9 w-9 md:[html[data-density=compact]_&]:h-8 md:[html[data-density=compact]_&]:w-8',
    };

    let {
        children,
        asChild = false,
        variant = 'default',
        size = 'default',
        class: className = '',
        type = 'button',
        ...rest
    }: {
        children?: Snippet<[AsChildProps]>;
        asChild?: boolean;
        variant?: Variant;
        size?: Size;
        class?: string;
        type?: 'button' | 'submit' | 'reset';
        [key: string]: unknown;
    } = $props();

    const classes = () => cn(base, variants[variant], sizes[size], className);
</script>

{#if asChild}
    {@render children?.({ class: classes(), ...rest })}
{:else}
    <button class={classes()} type={type} {...rest}>
        {@render children?.({})}
    </button>
{/if}
