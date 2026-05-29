export type DropdownMenuContext = {
    open: () => boolean;
    setOpen: (value: boolean) => void;
    triggerElement: () => HTMLElement | null;
    setTriggerElement: (value: HTMLElement | null) => void;
    contentElement: () => HTMLElement | null;
    setContentElement: (value: HTMLElement | null) => void;
};

export const DROPDOWN_MENU_CONTEXT = Symbol('dropdown-menu');
