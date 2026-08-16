<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Segment',
                href: '/settings/segments',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
