import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import {
  Home,
  Building,
  FileText,
  Users
} from 'lucide-react';

interface SidebarProps {
  isOpen: boolean;
}

const Sidebar: React.FC<SidebarProps> = ({ isOpen }) => {
  const location = useLocation();
  const currentPath = location.pathname;

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

          {/* Rapports */}
          <div className="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 cursor-not-allowed">
            <FileText size={20} />
            <span>Rapports</span>
            <span className="ml-auto text-xs bg-gray-200 px-2 py-1 rounded">Bientôt</span>
          </div>

          {/* Entreprises */}
          <div className="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 cursor-not-allowed">
            <Users size={20} />
            <span>Entreprises</span>
            <span className="ml-auto text-xs bg-gray-200 px-2 py-1 rounded">Bientôt</span>
          </div>
        </nav>
      </div>
    </div>
  );
};

export default Sidebar; 