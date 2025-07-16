import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    Building as BuildingIcon,
    Plus,
    MapPin,
    Search,
    Calendar,
    User,
    ChevronRight
} from 'lucide-react';
import { siteService } from '../services/api';
import { usePermissions } from '../hooks/usePermissions';
import type { Site } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';

const Sites: React.FC = () => {
    const { canCreateSites, isEnterpriseUser, user } = usePermissions();
    const [sites, setSites] = useState<Site[]>([]);
    const [filteredSites, setFilteredSites] = useState<Site[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        fetchSites();
    }, []);

    useEffect(() => {
        filterSites();
    }, [sites, searchTerm, user]);

    const fetchSites = async () => {
        try {
            setLoading(true);
            const sitesData = await siteService.getAll();
            setSites(sitesData);
        } catch (err) {
            setError('Erreur lors du chargement des sites');
            console.error('Erreur:', err);
        } finally {
            setLoading(false);
        }
    };

    const filterSites = () => {
        let filtered = [...sites];

        // Filtrer selon le rôle de l'utilisateur
        if (isEnterpriseUser() && user) {
            // Pour les utilisateurs entreprise, ne montrer que les sites qui contiennent
            // des bâtiments avec des parties dont l'utilisateur est propriétaire
            filtered = sites.filter(site => {
                if (!site.batiments) return false;
                
                return site.batiments.some(batiment => {
                    if (!batiment.parties) return false;
                    
                    return batiment.parties.some(partie => 
                        partie.owner_id === user.id
                    );
                });
            });
        }

        // Filtrer par terme de recherche
        if (searchTerm) {
            filtered = filtered.filter(site =>
                site.nom.toLowerCase().includes(searchTerm.toLowerCase()) ||
                site.ville.toLowerCase().includes(searchTerm.toLowerCase()) ||
                site.adresse.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }

        setFilteredSites(filtered);
    };

    if (loading) {
        return <LoadingSpinner />;
    }

    if (error) {
        return (
            <div className="text-center py-8">
                <p className="text-red-600">{error}</p>
                <button
                    onClick={fetchSites}
                    className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Réessayer
                </button>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold text-gray-900">Sites</h1>
                {canCreateSites() && (
                    <Link
                        to="/sites/create"
                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        <Plus size={16} />
                        Nouveau site
                    </Link>
                )}
            </div>

            {/* Barre de recherche */}
            <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
                <input
                    type="text"
                    placeholder="Rechercher un site par nom, ville ou adresse..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            {/* Message pour les utilisateurs entreprise */}
            {isEnterpriseUser() && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div className="flex items-center gap-2">
                        <User className="text-blue-600" size={20} />
                        <p className="text-blue-800">
                            <span className="font-medium">Utilisateur Entreprise :</span> Vous ne voyez que les sites contenant des bâtiments avec des parties qui vous sont assignées.
                        </p>
                    </div>
                </div>
            )}

            {/* Liste des sites */}
            {filteredSites.length === 0 ? (
                <div className="text-center py-12">
                    {sites.length === 0 ? (
                        <>
                            <BuildingIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                            <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun site</h3>
                            <p className="text-gray-500 mb-6">Commencez par créer votre premier site.</p>
                            {canCreateSites() && (
                                <Link
                                    to="/sites/create"
                                    className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    <Plus size={18} />
                                    Créer un site
                                </Link>
                            )}
                        </>
                    ) : (
                        <>
                            <BuildingIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                            <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun site trouvé</h3>
                            <p className="text-gray-500">
                                {isEnterpriseUser() 
                                    ? "Aucun site ne contient de parties qui vous sont assignées."
                                    : "Aucun site ne correspond à votre recherche."
                                }
                            </p>
                        </>
                    )}
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {filteredSites.map((site) => (
                        <div
                            key={site.id}
                            className="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
                        >
                            <div className="p-6">
                                <div className="flex items-start justify-between mb-4">
                                    <div className="bg-blue-100 p-3 rounded-lg">
                                        <BuildingIcon className="h-6 w-6 text-blue-600" />
                                    </div>
                                    <span className="text-sm text-gray-500">
                                        {site.batiments?.length || 0} bâtiment{(site.batiments?.length || 0) > 1 ? 's' : ''}
                                    </span>
                                </div>

                                <h3 className="text-xl font-semibold text-gray-900 mb-2">
                                    {site.nom}
                                </h3>

                                <div className="flex items-center gap-2 text-gray-600 mb-3">
                                    <MapPin size={16} />
                                    <span className="text-sm">
                                        {site.ville}, {site.pays}
                                    </span>
                                </div>

                                {site.description && (
                                    <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                                        {site.description}
                                    </p>
                                )}

                                <div className="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <div className="flex items-center gap-1">
                                        <Calendar size={14} />
                                        <span>Créé le {new Date(site.created_at).toLocaleDateString('fr-FR')}</span>
                                    </div>
                                    {site.client && (
                                        <div className="flex items-center gap-1">
                                            <User size={14} />
                                            <span>{site.client.prenom} {site.client.nom}</span>
                                        </div>
                                    )}
                                </div>

                                <Link
                                    to={`/sites/${site.id}`}
                                    className="inline-flex items-center gap-2 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors justify-center"
                                >
                                    Voir détails
                                    <ChevronRight size={16} />
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Statistiques en bas */}
            <div className="bg-gray-50 rounded-lg p-4">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div>
                        <p className="text-2xl font-bold text-gray-900">{filteredSites.length}</p>
                        <p className="text-sm text-gray-600">
                            Site{filteredSites.length > 1 ? 's' : ''} {isEnterpriseUser() ? 'accessible' : 'total'}{filteredSites.length > 1 ? 's' : ''}
                        </p>
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-gray-900">
                            {filteredSites.reduce((sum, site) => sum + (site.batiments?.length || 0), 0)}
                        </p>
                        <p className="text-sm text-gray-600">Bâtiments</p>
                    </div>
                    <div>
                        <p className="text-2xl font-bold text-gray-900">
                            {filteredSites.reduce((sum, site) => 
                                sum + (site.batiments?.reduce((batSum, bat) => 
                                    batSum + (bat.parties?.length || 0), 0) || 0), 0
                            )}
                        </p>
                        <p className="text-sm text-gray-600">Parties</p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Sites; 