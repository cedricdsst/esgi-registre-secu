import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import {
  Home,
  Building,
  FileText,
  UserCog,
  Wrench
} from 'lucide-react';
import { useAuth } from '../../contexts/AuthContext';

interface SidebarProps {
  isOpen: boolean;
}

const Sidebar: React.FC<SidebarProps> = ({ isOpen }) => {
  const location = useLocation();
  const currentPath = location.pathname;
  const { user, isLoading } = useAuth();

  // Fonction pour déterminer si un lien est actif
  const isActiveLink = (path: string) => {
    if (path === '/') {
      return currentPath === '/';
    }
    return currentPath.startsWith(path);
  };

  return (
    <div className={`bg-white shadow-lg transition-all duration-300 ${isOpen ? 'w-64' : 'w-0'} overflow-hidden`}>
      <div className="p-4 h-full pt-20">
        <nav className="space-y-2">
          {/* Tableau de bord */}
          <Link
            to="/"
            className={`flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActiveLink('/')
              ? 'bg-blue-600 text-white'
              : 'text-gray-700 hover:bg-gray-100'
              }`}
          >
            <Home size={20} />
            <span>Tableau de bord</span>
          </Link>

          {/* Sites */}
          <Link
            to="/sites"
            className={`flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActiveLink('/sites')
              ? 'bg-blue-600 text-white'
              : 'text-gray-700 hover:bg-gray-100'
              }`}
          >
            <Building size={20} />
            <span>Sites</span>
          </Link>

          {/* Interventions (utilisateurs entreprise, super-admin et intervenant) */}
          {!isLoading && (user?.role === 'user-entreprise' || user?.role === 'super-admin' || user?.role === 'user-intervenant') && (
            <Link
              to="/interventions"
              className={`flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActiveLink('/interventions')
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
                }`}
            >
              <Wrench size={20} />
              <span>Interventions</span>
            </Link>
          )}

          {/* Gestion utilisateurs (super-admin uniquement) */}
          {!isLoading && user?.role === 'super-admin' && (
            <Link
              to="/users"
              className={`flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActiveLink('/users')
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
                }`}
            >
              <UserCog size={20} />
              <span>Gestion utilisateurs</span>
            </Link>
          )}

          {/* Rapports */}
          <Link
            to="/rapports"
            className={`flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActiveLink('/rapports')
              ? 'bg-blue-600 text-white'
              : 'text-gray-700 hover:bg-gray-100'
              }`}
          >
            <FileText size={20} />
            <span>Rapports</span>
          </Link>
        </nav>
      </div>
    </div>
  );
};

export default Sidebar; 