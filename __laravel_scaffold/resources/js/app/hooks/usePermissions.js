import { useAuth } from '@/app/hooks/useAuth';

export function usePermissions() {
    const { user } = useAuth();
    const permissions = user?.permissions ?? [];

    return {
        permissions,
        hasPermission: (permission) =>
            !permission || permissions.includes(permission),
        hasAnyPermission: (required = []) =>
            required.some((permission) => permissions.includes(permission)),
        hasAllPermissions: (required = []) =>
            required.every((permission) => permissions.includes(permission)),
    };
}
