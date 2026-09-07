import { writable } from 'svelte/store';

export const externalFallback = writable<string | null>(null);

export async function copyText(text: string): Promise<boolean> {
    if (typeof navigator === 'undefined' || !navigator.clipboard?.writeText) {
        return false;
    }

    try {
        await navigator.clipboard.writeText(text);

        return true;
    } catch {
        return false;
    }
}

export function openExternal(value: string): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    let url: URL;

    try {
        url = new URL(value, window.location.origin);
    } catch {
        return false;
    }

    if (!['https:', 'http:', 'tel:', 'mailto:'].includes(url.protocol)) {
        return false;
    }

    // Using noopener in window.open returns null even on a successful open.
    // Detach opener synchronously, then navigate the single new window.
    let opened: Window | null = null;

    try {
        opened = window.open('about:blank', '_blank');

        if (opened) {
            opened.opener = null;
            opened.location.replace(url.href);

            return true;
        }
    } catch {
        opened?.close();
    }

    externalFallback.set(url.href);

    return false;
}

export function externalLink(node: HTMLAnchorElement) {
    const click = (event: MouseEvent) => {
        if (
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        event.preventDefault();
        openExternal(node.href);
    };
    node.addEventListener('click', click);

    return {
        destroy() {
            node.removeEventListener('click', click);
        },
    };
}
