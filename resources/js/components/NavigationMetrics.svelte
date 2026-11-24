<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { markNavigationDataReady } from '@/lib/navigation-metrics';

    $effect(() => {
        const props = page.props;
        const required: Record<string, string> = {
            Bookings: 'bookingListReady',
            Payments: 'paymentData',
            AdminOpsFlows: 'flowData',
            Luggages: 'flowData',
            AdminOpsMaster: 'masterData',
            CustomerBagasi: 'masterData',
            CustomerCarter: 'masterData',
            PengaturanRuteCarter: 'masterData',
            PengaturanRoles: 'roleData',
        };
        let key =
            required[page.component] ??
            (page.component.startsWith('Pengaturan') ||
            page.component === 'CustomerReguler'
                ? 'settingsData'
                : null);

        if (
            ['AdminOpsFlows', 'Luggages'].includes(page.component) &&
            ['form', 'view'].includes(String(props.initialMode))
        ) {
key = 'flowMasters';
}

        if (!key || props[key]) {
markNavigationDataReady();
}
    });
</script>
