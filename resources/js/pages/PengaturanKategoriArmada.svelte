<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Kategori Armada',
                href: '/admin-ops/units',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
