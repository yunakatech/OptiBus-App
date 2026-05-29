import type { ExternalToast } from 'svelte-sonner';
import { toast } from 'svelte-sonner';

export type ToastKind = 'success' | 'error' | 'warning' | 'info';

type ToastOptions = ExternalToast & {
    description?: string;
};

const DEFAULT_DURATION = 4200;

const buildOptions = (options: ToastOptions = {}): ExternalToast => ({
    duration: options.duration ?? DEFAULT_DURATION,
    dismissable: options.dismissable ?? true,
    description: options.description,
    ...options,
});

export function showToast(type: ToastKind, message: string, options: ToastOptions = {}) {
    return toast[type](message, buildOptions(options));
}

export function showSuccess(message: string, options: ToastOptions = {}) {
    return showToast('success', message, options);
}

export function showError(message: string, options: ToastOptions = {}) {
    return showToast('error', message, { important: true, ...options });
}

export function showWarning(message: string, options: ToastOptions = {}) {
    return showToast('warning', message, options);
}

export function showInfo(message: string, options: ToastOptions = {}) {
    return showToast('info', message, options);
}

export function dismissToast(id?: string | number) {
    toast.dismiss(id);
}

export function useToast() {
    return {
        showToast,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        dismissToast,
    };
}
