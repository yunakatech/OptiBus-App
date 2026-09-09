/** Moves transient overlays to document.body to escape layout stacking contexts. */
export function portalToBody(node: HTMLElement, enabled = true) {
    let portaled = false;

    const move = () => {
        if (portaled || typeof document === 'undefined') {
            return;
        }

        document.body.appendChild(node);
        portaled = true;
    };

    if (enabled) {
        move();
    }

    return {
        update(nextEnabled: boolean) {
            if (nextEnabled) {
                move();
            }
        },
        destroy() {
            if (portaled && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        },
    };
}
