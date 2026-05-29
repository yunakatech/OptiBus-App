<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Tarif Bagasi',
                href: '/admin-ops/services',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />

