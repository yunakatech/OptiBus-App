type PageLoader = () => Promise<unknown>;

type ConnectionInformation = {
    saveData?: boolean;
    effectiveType?: string;
};

let pageLoaders: Record<string, PageLoader> = {};
const pendingLoads = new Map<string, Promise<unknown>>();

const pageByPath: Record<string, string> = {
    '/booking-console': 'BookingConsole',
    '/bookings': 'Bookings',
    '/charters': 'AdminOpsFlows',
    '/luggages': 'AdminOpsFlows',
    '/payments': 'Payments',
};

export function registerPageLoaders(loaders: Record<string, PageLoader>) {
    pageLoaders = loaders;
}

function canPreload(): boolean {
    if (typeof navigator === 'undefined') {
        return false;
    }

    const connection = (
        navigator as Navigator & { connection?: ConnectionInformation }
    ).connection;

    return !connection?.saveData && connection?.effectiveType !== 'slow-2g';
}

export function preloadPage(pageName: string): void {
    if (!canPreload()) {
        return;
    }

    const path = `./pages/${pageName}.svelte`;
    const loader = pageLoaders[path];

    if (!loader || pendingLoads.has(path)) {
        return;
    }

    pendingLoads.set(
        path,
        loader().catch(() => undefined),
    );
}

export function preloadPageForHref(href: string): void {
    const pathname = href.split('?')[0].replace(/\/$/, '') || '/';
    const pageName = pageByPath[pathname];

    if (pageName) {
        preloadPage(pageName);
    }
}
