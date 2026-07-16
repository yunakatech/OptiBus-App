export type DataDomain =
    | 'bookings'
    | 'flows'
    | 'payments'
    | 'dashboard'
    | 'masters'
    | 'roles';

const STORAGE_KEY = 'cabooq:data-stale';

const canUseSessionStorage = () =>
    typeof window !== 'undefined' && typeof window.sessionStorage !== 'undefined';

const readDomains = (): Set<DataDomain> => {
    if (!canUseSessionStorage()) {
        return new Set();
    }

    try {
        const raw = window.sessionStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];

        return new Set(Array.isArray(parsed) ? parsed : []);
    } catch {
        return new Set();
    }
};

const writeDomains = (domains: Set<DataDomain>) => {
    if (!canUseSessionStorage()) {
        return;
    }

    try {
        if (domains.size === 0) {
            window.sessionStorage.removeItem(STORAGE_KEY);
        } else {
            window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...domains]));
        }
    } catch {
        // Ignore storage failures; explicit page reload calls still keep the UI fresh.
    }
};

export const markDataStale = (domains: DataDomain[]) => {
    const current = readDomains();

    for (const domain of domains) {
        current.add(domain);
    }

    writeDomains(current);
};

export const clearDataStale = (domains: DataDomain[]) => {
    const current = readDomains();
    let changed = false;

    for (const domain of domains) {
        if (current.delete(domain)) {
            changed = true;
        }
    }

    if (changed) {
        writeDomains(current);
    }
};

export const consumeDataStale = (domains: DataDomain[]): boolean => {
    const current = readDomains();
    const matched = domains.some((domain) => current.has(domain));

    if (!matched) {
        return false;
    }

    for (const domain of domains) {
        current.delete(domain);
    }

    writeDomains(current);

    return true;
};
