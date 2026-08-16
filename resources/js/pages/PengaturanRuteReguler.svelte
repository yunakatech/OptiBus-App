<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Rute Induk',
                href: '/settings/rute-induk',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
