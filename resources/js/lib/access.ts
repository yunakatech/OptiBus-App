export type PermissionRequirement = string | string[] | null | undefined;

export function hasPermission(
    permissions: string[] | null | undefined,
    requirement: PermissionRequirement,
): boolean {
    if (!requirement || (Array.isArray(requirement) && requirement.length === 0)) {
        return true;
    }

    const granted = new Set(permissions ?? []);
    const required = Array.isArray(requirement) ? requirement : [requirement];

    return required.some((permission) => granted.has(permission));
}
