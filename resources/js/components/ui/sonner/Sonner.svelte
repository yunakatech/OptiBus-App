<script lang="ts">
    import { themeState } from '@/lib/theme.svelte';
    import { Toaster as SonnerPrimitive, type ToasterProps } from 'svelte-sonner';

    let { ...restProps }: ToasterProps = $props();

    const { appearance } = themeState();
</script>

<SonnerPrimitive
    theme={appearance.value}
    class="toaster group"
    position="top-right"
    richColors={true}
    closeButton={true}
    visibleToasts={5}
    expand={true}
    duration={4200}
    offset="16px"
    toastOptions={{
        class: 'rounded-lg border border-slate-200/70 bg-background/95 shadow-[0_22px_55px_rgba(15,23,42,0.15)] backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/94',
        descriptionClass: 'text-xs text-muted-foreground',
    }}
    style="--normal-bg: var(--popover); --normal-text: var(--popover-foreground); --normal-border: var(--border);"
    {...restProps}
/>
