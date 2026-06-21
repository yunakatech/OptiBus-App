<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Jadwal',
                href: '/admin-ops/jadwal',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
