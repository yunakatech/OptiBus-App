const DEFAULT_SUPPORT_WHATSAPP_NUMBER = '6287778110950';

function normalizeWhatsappNumber(value: string): string {
    const digits = value.replace(/[^0-9]/g, '');

    if (digits.startsWith('0')) {
        return `62${digits.slice(1)}`;
    }

    if (digits.startsWith('8')) {
        return `62${digits}`;
    }

    return digits;
}

export function getSupportWhatsappHref(): string {
    const configuredNumber = import.meta.env.VITE_SUPPORT_WHATSAPP_NUMBER;
    const phone = normalizeWhatsappNumber(
        configuredNumber || DEFAULT_SUPPORT_WHATSAPP_NUMBER,
    );
    const message = 'Halo tim OptiBus, saya membutuhkan bantuan.';

    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
}
