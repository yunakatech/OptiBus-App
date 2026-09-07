import { registerBackHandler } from '@/lib/mobile-runtime';

type Options = {
    close: () => void;
    label?: string;
    modal?: boolean | 'mobile';
};
const stack: HTMLElement[] = [];
let locks = 0;
let savedOverflow = '';

export function overlay(node: HTMLElement, initial: Options) {
    let options = initial;
    const trigger =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
    const mobile = window.matchMedia('(max-width: 767px)');
    let locked = false;
    const top = () => stack[stack.length - 1] === node;
    const isModal = () =>
        options.modal === 'mobile' ? mobile.matches : options.modal !== false;
    const focusable = () =>
        Array.from(
            node.querySelectorAll<HTMLElement>(
                'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            ),
        ).filter(
            (element) =>
                element.getClientRects().length > 0 &&
                !element.closest('[inert]'),
        );
    const sync = () => {
        const modal = isModal();

        if (modal && !locked) {
            if (locks++ === 0) {
                savedOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
            }

            locked = true;
        } else if (!modal && locked) {
            if (--locks === 0) {
                document.body.style.overflow = savedOverflow;
            }

            locked = false;
        }

        if (options.label) {
            node.setAttribute('aria-label', options.label);
        }
    };
    stack.push(node);
    node.tabIndex = -1;
    sync();
    const frame = requestAnimationFrame(() => {
        if (!top()) {
            return;
        }

        const selected = node.querySelector<HTMLElement>(
            '[aria-selected="true"], [autofocus]',
        );
        (selected ?? focusable()[0] ?? node).focus({ preventScroll: true });
    });
    const unregister = registerBackHandler(() => {
        if (!top()) {
            return false;
        }

        options.close();

        return true;
    }, 100);
    const keydown = (event: KeyboardEvent) => {
        if (!top()) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopImmediatePropagation();
            options.close();
        } else if (event.key === 'Tab' && isModal()) {
            const elements = focusable();
            const current = elements.indexOf(
                document.activeElement as HTMLElement,
            );

            if (!elements.length) {
                event.preventDefault();
                node.focus();
            } else if (event.shiftKey && current <= 0) {
                event.preventDefault();
                elements[elements.length - 1].focus();
            } else if (
                !event.shiftKey &&
                (current === elements.length - 1 || current < 0)
            ) {
                event.preventDefault();
                elements[0].focus();
            }
        } else if (
            ['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)
        ) {
            const active = document.activeElement;

            if (
                active instanceof HTMLElement &&
                active.matches('input,textarea,select')
            ) {
                return;
            }

            const items = Array.from(
                node.querySelectorAll<HTMLElement>(
                    '[role="option"]:not([disabled])',
                ),
            );

            if (!items.length) {
                return;
            }

            event.preventDefault();
            const index = items.indexOf(active as HTMLElement);
            const next =
                event.key === 'Home'
                    ? 0
                    : event.key === 'End'
                      ? items.length - 1
                      : (index +
                            (event.key === 'ArrowDown' ? 1 : -1) +
                            items.length) %
                        items.length;
            items[next].focus();
        }
    };
    const focusin = (event: FocusEvent) => {
        if (
            top() &&
            isModal() &&
            event.target instanceof Node &&
            !node.contains(event.target)
        ) {
            (focusable()[0] ?? node).focus({ preventScroll: true });
        }
    };
    const outside = (event: PointerEvent) => {
        if (
            top() &&
            !isModal() &&
            event.target instanceof Node &&
            !node.contains(event.target) &&
            !trigger?.contains(event.target)
        ) {
            options.close();
        }
    };
    document.addEventListener('keydown', keydown, true);
    document.addEventListener('focusin', focusin);
    document.addEventListener('pointerdown', outside);
    mobile.addEventListener('change', sync);

    return {
        update(value: Options) {
            options = value;
            sync();
        },
        destroy() {
            cancelAnimationFrame(frame);
            unregister();
            const wasTop = top();
            stack.splice(stack.indexOf(node), 1);

            if (locked && --locks === 0) {
                document.body.style.overflow = savedOverflow;
            }

            document.removeEventListener('keydown', keydown, true);
            document.removeEventListener('focusin', focusin);
            document.removeEventListener('pointerdown', outside);
            mobile.removeEventListener('change', sync);

            if (wasTop && trigger?.isConnected) {
                trigger.focus({ preventScroll: true });
            }
        },
    };
}
