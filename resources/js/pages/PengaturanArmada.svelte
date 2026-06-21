<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Armada',
                href: '/admin-ops/armada',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
