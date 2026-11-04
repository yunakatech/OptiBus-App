<script lang="ts">
    import type { Snippet } from 'svelte';
    import AuthLayout from '@/layouts/auth/AuthSimpleLayout.svelte';
    import NavigationMetrics from '@/components/NavigationMetrics.svelte';

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
