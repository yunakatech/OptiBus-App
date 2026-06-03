<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Live Booking Console',
                href: '/booking-console',
            },
        ],
    };
</script>

<script lang="ts">
    import BookingsPage from '@/pages/Bookings.svelte';

    type Totals = {
        bookings: number;
        customers: number;
        routes: number;
        schedules: number;
    };

    type BookingRow = {
        id: number;
        name: string;
        phone: string;
        rute: string;
        tanggal: string;
        jam: string;
        unit: number;
        seat: string;
        status: string;
        pembayaran: string;
    };

    let {
        totals,
        latestBookings = [],
        migrationChecklist,
        serverNow = '',
        listOnly = false,
    }: {
        totals: Totals;
        latestBookings: BookingRow[];
        migrationChecklist: string[];
        serverNow?: string;
        listOnly?: boolean;
    } = $props();
</script>

<BookingsPage {totals} {latestBookings} {migrationChecklist} {serverNow} consoleOnly={true} listOnly={listOnly} />
