import type flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import type { Instance as FlatpickrInstance } from 'flatpickr/dist/types/instance';

type FlatpickrFactory = typeof flatpickr;

let flatpickrLoader: Promise<FlatpickrFactory> | null = null;

export type { FlatpickrInstance };

// Flatpickr creates a separate native input on mobile; keep its label and
// bounds in sync with the original field and remove its positive tab index.
export const prepareMobileDateInput = (picker: FlatpickrInstance): void => {
    const mobileInput = picker.mobileInput;
    if (!mobileInput) return;

    const associatedLabel = picker.input.id
        ? Array.from(document.querySelectorAll('label[for]')).find((item) => item.htmlFor === picker.input.id)
        : null;
    const label = picker.input.getAttribute('aria-label')
        || associatedLabel?.textContent?.trim()
        || picker.input.closest('label')?.textContent?.trim()
        || picker.input.getAttribute('placeholder')
        || 'Tanggal';
    mobileInput.setAttribute('aria-label', label);
    mobileInput.tabIndex = 0;
    mobileInput.min = picker.config.minDate ? picker.formatDate(picker.config.minDate, 'Y-m-d') : '';
    mobileInput.max = picker.config.maxDate ? picker.formatDate(picker.config.maxDate, 'Y-m-d') : '';
};

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
