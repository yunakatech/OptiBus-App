const DEFAULT_CURRENCY_SYMBOL = 'Rp';

export const currencyDigits = (
    value: string | number | null | undefined,
) => String(value ?? '').replace(/\D/g, '');

export const parseCurrencyInput = (
    value: string | number | null | undefined,
) => {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? Math.max(0, Math.round(value)) : 0;
    }

    const raw = String(value ?? '').trim();
    const decimalValue = raw.replace(',', '.');

    if (/^\d+\.\d{1,2}$/.test(decimalValue)) {
        return Math.max(0, Math.round(Number(decimalValue)));
    }

    const digits = currencyDigits(raw);

    return digits === '' ? 0 : Number(digits);
};

export const formatCurrencyInput = (
    value: string | number | null | undefined,
    symbol = DEFAULT_CURRENCY_SYMBOL,
) => {
    const amount = parseCurrencyInput(value);

    return amount > 0 ? `${symbol} ${amount.toLocaleString('id-ID')}` : '';
};

export const formatCurrencyDisplay = (
    value: string | number | null | undefined,
    symbol = DEFAULT_CURRENCY_SYMBOL,
) => `${symbol} ${parseCurrencyInput(value).toLocaleString('id-ID')}`;
