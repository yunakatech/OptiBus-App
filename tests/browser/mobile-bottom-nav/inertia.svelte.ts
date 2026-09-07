export const page = $state({
    url: '/dashboard',
    props: {
        auth: {
            permissions: [
                'dashboard.view',
                'booking.view',
                'luggage.view',
                'charter.view',
            ],
            user: { is_super_admin: false },
            active_tenant: { name: 'Tenant QA' },
            billing_access: { locked: false },
        },
    },
});

export const activity = $state({
    visits: 0,
    prefetches: 0,
    slow: false,
    fail: false,
});

export const router = {
    prefetch() {
        activity.prefetches++;
    },
    visit(href: string, options: { onFinish: () => void }) {
        activity.visits++;
        window.setTimeout(
            () => {
                if (!activity.fail) page.url = href;
                options.onFinish();
            },
            activity.slow ? 3000 : 100,
        );
    },
};
