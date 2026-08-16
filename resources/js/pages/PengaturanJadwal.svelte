<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Jadwal',
                href: '/settings/jadwal',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';
    const props = $props();
</script>

<AdminOps {...props} />
