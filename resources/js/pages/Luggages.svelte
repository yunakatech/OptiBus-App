<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Bagasi',
                href: '/luggages',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOpsFlows from '@/pages/AdminOpsFlows.svelte';

    const props = $props();
</script>

<AdminOpsFlows {...props} />
