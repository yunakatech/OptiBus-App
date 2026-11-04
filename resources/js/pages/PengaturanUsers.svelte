<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Pengguna',
                href: '/settings/users',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
