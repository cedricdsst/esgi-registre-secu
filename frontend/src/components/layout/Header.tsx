import React from 'react';
import { LogOut, Menu } from 'lucide-react';
import { useAuth } from '../../contexts/AuthContext';

interface HeaderProps {
  onToggleSidebar: () => void;
}

const Header: React.FC<HeaderProps> = ({ onToggleSidebar }) => {
  const { user, logout, isLoading } = useAuth();

  const handleLogout = async () => {
    try {
      await logout();
    } catch (error) {
      console.error('Erreur lors de la déconnexion:', error);
    }
  };

  // Formatter le rôle pour l'affichage
  const formatRole = (role: string) => {
    const roleMap: Record<string, string> = {
      'super-admin': 'Super Admin',
      'admin': 'Admin',
      'client-admin': 'Admin Client',
      'user': 'Utilisateur',
      'viewer': 'Lecteur',
      'user-entreprise': 'Utilisateur Entreprise',
      'user-intervenant': 'Utilisateur Intervenant'
    };
    return roleMap[role] || role;
  };

  return (
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="flex items-center justify-between px-6 py-4">
        <div className="flex items-center gap-4">
          <button
            onClick={onToggleSidebar}
            className="p-2 rounded-md hover:bg-gray-100 transition-colors"
          >
            <Menu size={20} />
          </button>
          <h1 className="text-xl font-semibold text-gray-800">
            Registre de Sécurité
          </h1>
        </div>

        <div className="flex items-center gap-4">
          {!isLoading && user && (
            <div className="text-right">
              <div className="text-sm font-medium text-gray-900">
                {user.prenom} {user.nom}
              </div>
              <div className="text-xs text-gray-500">
                {user.organisation}
              </div>
              <div className="text-xs text-blue-600 font-medium">
                {formatRole(user.role)}
              </div>
            </div>
          )}

          <button
            onClick={handleLogout}
            className="flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors"
          >
            <LogOut size={16} />
            Déconnexion
          </button>
        </div>
      </div>
    </header>
  );
};

export default Header; 