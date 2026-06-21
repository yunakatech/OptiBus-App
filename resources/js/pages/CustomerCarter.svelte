<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Carter',
                href: '/admin-ops/customer-charter',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOpsMaster from '@/pages/AdminOpsMaster.svelte';
    const props = $props();
</script>

<AdminOpsMaster {...props} />
