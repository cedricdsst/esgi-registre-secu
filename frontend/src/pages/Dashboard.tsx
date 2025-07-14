import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    Building,
    FileText,
    AlertCircle,
    Users,
    TrendingUp,
    ChevronRight,
    Boxes
} from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { siteService } from '../services/api';
import type { Site } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';

const Dashboard: React.FC = () => {
    const { user } = useAuth();
    const [sites, setSites] = useState<Site[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchSites();
    }, []);

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

    // Calcul des statistiques globales
    const totalSites = sites.length;
    const totalBatiments = sites.reduce((sum, site) => sum + (site.batiments?.length || 0), 0);

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
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <h1 className="text-2xl font-bold text-gray-900 mb-2">
                    Tableau de bord
                </h1>
                <p className="text-gray-600">
                    Bienvenue, {user?.prenom} {user?.nom} - {user?.organisation}
                </p>
            </div>

            {/* Statistiques globales */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Total Sites</p>
                            <p className="text-2xl font-bold text-gray-900">{totalSites}</p>
                        </div>
                        <div className="bg-blue-100 p-3 rounded-full">
                            <Building className="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Total Bâtiments</p>
                            <p className="text-2xl font-bold text-gray-900">{totalBatiments}</p>
                        </div>
                        <div className="bg-green-100 p-3 rounded-full">
                            <Building className="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Rapports</p>
                            <p className="text-2xl font-bold text-gray-900">-</p>
                            <p className="text-xs text-gray-500">À venir</p>
                        </div>
                        <div className="bg-yellow-100 p-3 rounded-full">
                            <FileText className="h-6 w-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Observations</p>
                            <p className="text-2xl font-bold text-gray-900">-</p>
                            <p className="text-xs text-gray-500">À venir</p>
                        </div>
                        <div className="bg-red-100 p-3 rounded-full">
                            <AlertCircle className="h-6 w-6 text-red-600" />
                        </div>
                    </div>
                </div>
            </div>

            {/* Actions rapides */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">
                        Navigation principale
                    </h2>
                    <div className="space-y-3">
                        <Link
                            to="/sites"
                            className="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group"
                        >
                            <div className="flex items-center gap-3">
                                <div className="bg-blue-100 p-2 rounded-lg">
                                    <Building className="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p className="font-medium text-gray-900">Gérer les sites</p>
                                    <p className="text-sm text-gray-500">Accéder à tous vos sites</p>
                                </div>
                            </div>
                            <ChevronRight className="h-5 w-5 text-gray-400 group-hover:text-gray-600" />
                        </Link>

                        <Link
                            to="/inventaire"
                            className="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors group"
                        >
                            <div className="flex items-center gap-3">
                                <div className="bg-green-100 p-2 rounded-lg">
                                    <Boxes className="h-5 w-5 text-green-600" />
                                </div>
                                <div>
                                    <p className="font-medium text-gray-900">Inventaire</p>
                                    <p className="text-sm text-gray-500">Gérer les équipements</p>
                                </div>
                            </div>
                            <ChevronRight className="h-5 w-5 text-gray-400 group-hover:text-gray-600" />
                        </Link>
                    </div>
                </div>

                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">
                        Activité récente
                    </h2>
                    <div className="space-y-3">
                        <div className="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                            <div className="bg-blue-100 p-2 rounded-lg">
                                <TrendingUp className="h-4 w-4 text-blue-600" />
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-900">
                                    Système initialisé
                                </p>
                                <p className="text-xs text-gray-500">
                                    Commencez par créer vos premiers sites
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Liste des sites récents */}
            {sites.length > 0 && (
                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-lg font-semibold text-gray-900">
                            Sites récents
                        </h2>
                        <Link
                            to="/sites"
                            className="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                            Voir tous
                        </Link>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {sites.slice(0, 6).map((site) => (
                            <Link
                                key={site.id}
                                to={`/sites/${site.id}`}
                                className="block p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all group"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="bg-blue-100 p-2 rounded-lg">
                                        <Building className="h-5 w-5 text-blue-600" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-medium text-gray-900 group-hover:text-blue-600">
                                            {site.nom}
                                        </h3>
                                        <p className="text-sm text-gray-500">
                                            {site.ville}, {site.pays}
                                        </p>
                                        <p className="text-xs text-gray-400 mt-1">
                                            {site.batiments?.length || 0} bâtiment(s)
                                        </p>
                                    </div>
                                    <ChevronRight className="h-4 w-4 text-gray-400 group-hover:text-blue-600" />
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default Dashboard; 