export const bookingInboxNudge = $state({
    hasPending: false,
});

let pendingRefresh: Promise<boolean> | null = null;
let lastRefreshAt = 0;

export function refreshBookingInboxNudge(force = false): Promise<boolean> {
    if (typeof window === 'undefined') {
        return Promise.resolve(false);
    }

    const now = Date.now();
    if (!force && now - lastRefreshAt < 5_000) {
        return Promise.resolve(bookingInboxNudge.hasPending);
    }

    if (pendingRefresh) {
        return pendingRefresh;
    }

    pendingRefresh = fetch('/api/admin/public-booking-requests/count', {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (response) => {
            const payload = await response.json();
            if (!response.ok || payload.success !== true) {
                return false;
            }

            bookingInboxNudge.hasPending =
                Number(payload.pending_count ?? 0) > 0;
            lastRefreshAt = Date.now();

            return bookingInboxNudge.hasPending;
        })
        .catch(() => bookingInboxNudge.hasPending)
        .finally(() => {
            pendingRefresh = null;
        });

    return pendingRefresh;
}
