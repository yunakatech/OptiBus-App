<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Bagasi',
                href: '/admin-ops/customer-bagasi',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOpsMaster from '@/pages/AdminOpsMaster.svelte';
    const props = $props();
</script>

<AdminOpsMaster {...props} />
