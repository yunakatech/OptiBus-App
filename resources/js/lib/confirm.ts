import { writable } from 'svelte/store';

export type ConfirmVariant = 'default' | 'destructive';

export type ConfirmOptions = {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: ConfirmVariant;
};

type ConfirmState = {
    open: boolean;
    title: string;
    message: string;
    confirmText: string;
    cancelText: string;
    variant: ConfirmVariant;
};

const defaultState: ConfirmState = {
    open: false,
    title: 'Konfirmasi Aksi',
    message: '',
    confirmText: 'Lanjutkan',
    cancelText: 'Batal',
    variant: 'default',
};

let activeResolver: ((value: boolean) => void) | null = null;

export const confirmState = writable<ConfirmState>(defaultState);

const closeConfirm = (value: boolean) => {
    activeResolver?.(value);
    activeResolver = null;
    confirmState.set(defaultState);
};

export function confirmAction(options: ConfirmOptions): Promise<boolean> {
    if (activeResolver) {
        closeConfirm(false);
    }

    confirmState.set({
        open: true,
        title: options.title ?? defaultState.title,
        message: options.message,
        confirmText: options.confirmText ?? 'Ya, lanjutkan',
        cancelText: options.cancelText ?? 'Batal',
        variant: options.variant ?? 'default',
    });

    return new Promise<boolean>((resolve) => {
        activeResolver = resolve;
    });
}

export function confirmAccept(): void {
    closeConfirm(true);
}

export function confirmCancel(): void {
    closeConfirm(false);
}

export function useConfirm() {
    return {
        confirmState,
        confirmAction,
        confirmAccept,
        confirmCancel,
    };
}
