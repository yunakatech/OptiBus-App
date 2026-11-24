import type flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import type { Instance as FlatpickrInstance } from 'flatpickr/dist/types/instance';

type FlatpickrFactory = typeof flatpickr;

let flatpickrLoader: Promise<FlatpickrFactory> | null = null;

export type { FlatpickrInstance };

export const loadFlatpickr = async (): Promise<FlatpickrFactory> => {
    if (flatpickrLoader === null) {
        flatpickrLoader = Promise.all([
            import('flatpickr'),
            import('flatpickr/dist/flatpickr.css'),
        ])
            .then(([module]) => {
                const fp = module.default;
                fp.localize(Indonesian);

                return fp;
            })
            .catch((error) => {
                flatpickrLoader = null;

                throw error;
            });
    }

    return flatpickrLoader;
};
