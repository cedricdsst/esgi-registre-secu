import { useAuth } from '../contexts/AuthContext';

export const usePermissions = () => {
    const { user } = useAuth();

    const hasRole = (role: string) => {
        return user?.role === role;
    };

    const hasAnyRole = (roles: string[]) => {
        return roles.includes(user?.role || '');
    };

    // Permissions spécifiques
    const canCreateSites = () => {
        return hasAnyRole(['super-admin', 'admin', 'client-admin']);
    };

    const canCreateBuildings = () => {
        return hasAnyRole(['super-admin', 'admin', 'client-admin']);
    };

    const canCreateParties = () => {
        return hasAnyRole(['super-admin', 'admin', 'client-admin']);
    };

    const canManageUsers = () => {
        return hasRole('super-admin');
    };

    const canManageOwnership = () => {
        return hasRole('super-admin');
    };

    const canEditSites = () => {
        return hasAnyRole(['super-admin', 'admin', 'client-admin']);
    };

    const canEditBuildings = () => {
        return hasAnyRole(['super-admin', 'admin', 'client-admin']);
    };

    const canEditParties = () => {
        return hasAnyRole(['super-admin', 'admin', 'client-admin']);
    };

    const canDeleteItems = () => {
        return hasAnyRole(['super-admin', 'admin']);
    };

    // Pour les utilisateurs entreprise, ils ne voient que leurs propres données
    const isEnterpriseUser = () => {
        return hasRole('user-entreprise');
    };

    const isIntervenantUser = () => {
        return hasRole('user-intervenant');
    };

    return {
        user,
        hasRole,
        hasAnyRole,
        canCreateSites,
        canCreateBuildings,
        canCreateParties,
        canManageUsers,
        canManageOwnership,
        canEditSites,
        canEditBuildings,
        canEditParties,
        canDeleteItems,
        isEnterpriseUser,
        isIntervenantUser
    };
}; 