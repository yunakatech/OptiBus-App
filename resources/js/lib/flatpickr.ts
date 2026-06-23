import type { Instance as FlatpickrInstance } from 'flatpickr/dist/types/instance';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

type FlatpickrFactory = (typeof import('flatpickr'))['default'];

let flatpickrLoader: Promise<FlatpickrFactory> | null = null;

export type { FlatpickrInstance };

export const loadFlatpickr = async (): Promise<FlatpickrFactory> => {
    if (flatpickrLoader === null) {
        flatpickrLoader = Promise.all([import('flatpickr'), import('flatpickr/dist/flatpickr.css')]).then(
            ([module]) => {
                const fp = module.default;
                fp.localize(Indonesian);
                return fp;
            }
        );
    }

    return flatpickrLoader;
};
