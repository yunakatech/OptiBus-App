<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Rute Induk',
                href: '/admin-ops/routes',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
