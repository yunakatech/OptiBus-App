<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Driver',
                href: '/admin-ops/driver',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
