import { usePermissions } from '@/app/hooks/usePermissions';

export default function PermissionWrapper({
    children,
    fallback = null,
    permission,
    permissions = [],
    requireAll = false,
}) {
    const { hasAllPermissions, hasAnyPermission, hasPermission } =
        usePermissions();

    let allowed = true;

    if (permission) {
        allowed = hasPermission(permission);
    } else if (permissions.length > 0) {
        allowed = requireAll
            ? hasAllPermissions(permissions)
            : hasAnyPermission(permissions);
    }

    return allowed ? children : fallback;
}
