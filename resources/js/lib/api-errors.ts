type ErrorPayload = {
    error?: unknown;
    message?: unknown;
    errors?: Record<string, unknown>;
};

function firstValidationMessage(errors: Record<string, unknown> | undefined): string {
    if (!errors) {
        return '';
    }

    for (const value of Object.values(errors)) {
        if (Array.isArray(value)) {
            const first = value.find((item) => typeof item === 'string' && item.trim() !== '');
            if (typeof first === 'string') {
                return first;
            }
        }

        if (typeof value === 'string' && value.trim() !== '') {
            return value;
        }
    }

    return '';
}

function asMessage(value: unknown): string {
    return typeof value === 'string' && value.trim() !== '' ? value : '';
}

export async function extractApiErrorMessage(
    response: Response,
    fallback: string,
): Promise<string> {
    const text = await response.text().catch(() => '');
    let payload: ErrorPayload | null = null;

    if (text.trim() !== '') {
        try {
            const parsed = JSON.parse(text);
            if (parsed && typeof parsed === 'object') {
                payload = parsed as ErrorPayload;
            }
        } catch {
            payload = null;
        }
    }

    return (
        firstValidationMessage(payload?.errors) ||
        asMessage(payload?.error) ||
        asMessage(payload?.message) ||
        asMessage(response.statusText) ||
        fallback
    );
}
