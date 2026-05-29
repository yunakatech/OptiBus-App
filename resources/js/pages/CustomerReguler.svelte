<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Reguler',
                href: '/admin-ops/customers',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
