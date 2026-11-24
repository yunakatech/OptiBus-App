<script lang="ts">
    import type { Snippet } from 'svelte';
    import NavigationMetrics from '@/components/NavigationMetrics.svelte';
    import AuthLayout from '@/layouts/auth/AuthSimpleLayout.svelte';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<AuthLayout {title} {description}>
    <NavigationMetrics />
    {@render children?.()}
</AuthLayout>
