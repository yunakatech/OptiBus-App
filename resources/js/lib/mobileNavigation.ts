import { dashboard } from '@/routes';
import { toUrl } from '@/lib/utils';

const PRIMARY_BOTTOM_NAV_HREFS = [
    toUrl(dashboard()),
    '/booking-console',
    '/bookings',
    '/luggages',
    '/charters',
] as const;

export function mobileHiddenMenuHrefs(billingLocked: boolean): Set<string> {
    const hidden = new Set<string>(PRIMARY_BOTTOM_NAV_HREFS);

    if (billingLocked) {
        hidden.add('/subscription');
    }

    return hidden;
}
