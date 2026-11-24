<script lang="ts">
    import type { Snippet } from 'svelte';
    import NavigationMetrics from '@/components/NavigationMetrics.svelte';
    import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppLayout {breadcrumbs}>
    <NavigationMetrics />
    {@render children?.()}
</AppLayout>
