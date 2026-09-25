export function usePermissions() {
    // Read permissions attached to window.AppUser or window.permissions if present
    const getPermissions = () => {
        if (window.__USER_PERMISSIONS__) {
            return window.__USER_PERMISSIONS__;
        }
        return [];
    };

    const can = (permission) => {
        const perms = getPermissions();
        if (perms.includes('*') || perms.includes('admin') || perms.includes('super_admin')) {
            return true;
        }
        return perms.includes(permission);
    };

    return {
        can,
        getPermissions,
    };
}
