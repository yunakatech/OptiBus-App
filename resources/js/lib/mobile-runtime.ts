type BackHandler = { handler: () => boolean; priority: number; order: number };
const backHandlers: BackHandler[] = [];
let order = 0;

declare global {
    interface Window {
        OptibusWebView?: { handleBack: () => boolean };
    }
}

export function registerBackHandler(handler: () => boolean, priority = 0) {
    const entry = { handler, priority, order: ++order };
    backHandlers.push(entry);

    return () => {
        const index = backHandlers.indexOf(entry);

        if (index >= 0) {
            backHandlers.splice(index, 1);
        }
    };
}

export function handleBack(): boolean {
    return [...backHandlers]
        .sort((a, b) => b.priority - a.priority || b.order - a.order)
        .some(({ handler }) => handler());
}

export function initializeMobileRuntime() {
    if (typeof window === 'undefined') {
        return;
    }

    window.OptibusWebView = { handleBack };
    const root = document.documentElement;
    const viewport = window.visualViewport;
    let baseline = window.innerHeight;
    let frame = 0;
    let previousKeyboard = false;
    const update = () => {
        frame = 0;
        const active = document.activeElement;
        const editing =
            active instanceof HTMLElement &&
            active.matches(
                'input:not([readonly]):not([type="checkbox"]):not([type="radio"]), textarea:not([readonly]), [contenteditable="true"]',
            );
        const height = viewport?.height ?? window.innerHeight;
        const zoomed = Math.abs((viewport?.scale ?? 1) - 1) > 0.1;

        if (!editing && !zoomed) {
            baseline = window.innerHeight;
        }

        const keyboard = Boolean(
            editing &&
            !zoomed &&
            Math.max(baseline, window.innerHeight) - height > 120,
        );
        const bottom = keyboard
            ? Math.max(
                  0,
                  window.innerHeight - height - (viewport?.offsetTop ?? 0),
              )
            : 0;
        root.dataset.keyboardOpen = String(keyboard);
        root.style.setProperty('--mobile-viewport-height', `${height}px`);
        root.style.setProperty(
            '--mobile-viewport-top',
            `${viewport?.offsetTop ?? 0}px`,
        );
        root.style.setProperty('--mobile-keyboard-offset', `${bottom}px`);

        if (keyboard && !previousKeyboard && active instanceof HTMLElement) {
            active.scrollIntoView({ block: 'nearest', behavior: 'auto' });
        }

        previousKeyboard = keyboard;
    };
    const schedule = () => {
        if (!frame) {
            frame = window.requestAnimationFrame(update);
        }
    };
    viewport?.addEventListener('resize', schedule);
    viewport?.addEventListener('scroll', schedule);
    window.addEventListener('resize', schedule);
    document.addEventListener('focusin', schedule);
    document.addEventListener('focusout', schedule);
    update();

    return () => {
        window.cancelAnimationFrame(frame);
        viewport?.removeEventListener('resize', schedule);
        viewport?.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
        document.removeEventListener('focusin', schedule);
        document.removeEventListener('focusout', schedule);
    };
}

// Measure the real rendered bar, including safe-area and wrapped text.
export function measureBar(node: HTMLElement, property: string) {
    const root = document.documentElement;
    const update = () =>
        root.style.setProperty(
            property,
            `${node.getBoundingClientRect().height}px`,
        );
    const observer = new ResizeObserver(update);
    observer.observe(node);
    update();

    return {
        destroy() {
            observer.disconnect();
            root.style.removeProperty(property);
        },
    };
}
