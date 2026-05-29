<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Users',
                href: '/admin-ops/users',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
