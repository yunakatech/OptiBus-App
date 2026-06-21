<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Master Carter',
                href: '/admin-ops/rute-carter',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOpsMaster from '@/pages/AdminOpsMaster.svelte';
    const props = $props();
</script>

<AdminOpsMaster {...props} />
