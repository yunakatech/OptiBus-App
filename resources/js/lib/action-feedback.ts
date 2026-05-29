import { confirmAction } from '@/lib/confirm';
import type { LoadingScope } from '@/lib/loading';
import { startLoading, stopLoading } from '@/lib/loading';
import { showError, showSuccess, showWarning } from '@/lib/toast';

type FeedbackOptions<T> = {
    loadingMessage?: string;
    successMessage?: string | ((result: T) => string | null | undefined);
    errorMessage?: string | ((error: unknown) => string);
    scope?: LoadingScope;
    blocking?: boolean;
    showSuccessToast?: boolean;
    showErrorToast?: boolean;
    showWarningToast?: boolean;
    warningMessage?: string;
    onSuccess?: (result: T) => void | Promise<void>;
    onError?: (error: unknown) => void | Promise<void>;
    onFinally?: () => void | Promise<void>;
};

const resolveErrorMessage = (error: unknown, fallback: string) => {
    if (error instanceof Error && error.message.trim()) {
        return error.message;
    }

    return fallback;
};

export async function runWithFeedback<T>(
    action: () => Promise<T>,
    options: FeedbackOptions<T> = {},
): Promise<T> {
    const token = startLoading({
        message: options.loadingMessage ?? 'Memproses data...',
        scope: options.scope ?? 'action',
        blocking: options.blocking ?? true,
    });

    try {
        if (options.warningMessage && options.showWarningToast !== false) {
            showWarning(options.warningMessage);
        }

        const result = await action();
        const successMessage = typeof options.successMessage === 'function'
            ? options.successMessage(result)
            : options.successMessage;

        if (successMessage && options.showSuccessToast !== false) {
            showSuccess(successMessage);
        }

        await options.onSuccess?.(result);

        return result;
    } catch (error) {
        const errorMessage = typeof options.errorMessage === 'function'
            ? options.errorMessage(error)
            : resolveErrorMessage(error, options.errorMessage ?? 'Terjadi kesalahan, silakan coba lagi.');

        if (options.showErrorToast !== false) {
            showError(errorMessage);
        }

        await options.onError?.(error);

        throw error;
    } finally {
        stopLoading(token);
        await options.onFinally?.();
    }
}

export async function confirmAndRun<T>(
    confirmMessage: string,
    action: () => Promise<T>,
    options: FeedbackOptions<T> = {},
): Promise<T | null> {
    const confirmed = await confirmAction({
        title: 'Konfirmasi Aksi',
        message: confirmMessage,
        confirmText: 'Ya, lanjutkan',
        cancelText: 'Batal',
        variant: 'destructive',
    });

    if (!confirmed) {
        return null;
    }

    return runWithFeedback(action, options);
}
