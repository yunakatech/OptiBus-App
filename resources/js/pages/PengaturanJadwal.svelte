<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Jadwal',
                href: '/admin-ops/schedules',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
