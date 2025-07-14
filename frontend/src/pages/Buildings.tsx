import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Building as BuildingIcon,
    Plus,
    Edit,
    Trash2,
    ChevronRight,
    ArrowLeft
} from 'lucide-react';
import { siteService, buildingService } from '../services/api';
import type { Site, Building } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';
import Modal from '../components/common/Modal';

const Buildings: React.FC = () => {
    const { siteId } = useParams<{ siteId: string }>();
    const [site, setSite] = useState<Site | null>(null);
    const [buildings, setBuildings] = useState<Building[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [buildingToDelete, setBuildingToDelete] = useState<Building | null>(null);

    useEffect(() => {
        if (siteId) {
            fetchData();
        }
    }, [siteId]);

    const fetchData = async () => {
        try {
            setLoading(true);
            const [siteData, buildingsData] = await Promise.all([
                siteService.getById(Number(siteId)),
                buildingService.getAll(Number(siteId))
            ]);
            setSite(siteData);
            setBuildings(buildingsData);
        } catch (err) {
            setError('Erreur lors du chargement des données');
            console.error('Erreur:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteBuilding = async () => {
        if (!buildingToDelete) return;

        try {
            await buildingService.delete(buildingToDelete.id);
            setBuildings(buildings.filter(b => b.id !== buildingToDelete.id));
            setShowDeleteModal(false);
            setBuildingToDelete(null);
        } catch (err) {
            console.error('Erreur lors de la suppression:', err);
        }
    };

    if (loading) {
        return <LoadingSpinner />;
    }

    if (error || !site) {
        return (
            <div className="text-center py-8">
                <p className="text-red-600">{error || 'Site non trouvé'}</p>
                <Link
                    to="/sites"
                    className="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    <ArrowLeft size={16} />
                    Retour aux sites
                </Link>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-center justify-between mb-4">
                    <Link
                        to={`/sites/${site.id}`}
                        className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900"
                    >
                        <ArrowLeft size={20} />
                        Retour au site
                    </Link>
                    <Link
                        to={`/sites/${site.id}/buildings/new`}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                    >
                        <Plus size={16} />
                        Nouveau bâtiment
                    </Link>
                </div>

                <div className="flex items-center gap-4">
                    <div className="bg-blue-100 p-3 rounded-lg">
                        <BuildingIcon className="h-8 w-8 text-blue-600" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">
                            Bâtiments de {site.nom}
                        </h1>
                        <p className="text-gray-600">
                            {buildings.length} bâtiment(s) dans ce site
                        </p>
                    </div>
                </div>
            </div>

            {/* Liste des bâtiments */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                {buildings.length === 0 ? (
                    <div className="text-center py-8">
                        <BuildingIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <p className="text-gray-500 mb-4">Aucun bâtiment pour ce site</p>
                        <Link
                            to={`/sites/${site.id}/buildings/new`}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <Plus size={16} />
                            Créer le premier bâtiment
                        </Link>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {buildings.map((building) => (
                            <div
                                key={building.id}
                                className="border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md transition-all"
                            >
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="bg-blue-100 p-3 rounded-lg">
                                        <BuildingIcon className="h-6 w-6 text-blue-600" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="text-lg font-semibold text-gray-900">
                                            {building.nom}
                                        </h3>
                                        <p className="text-sm text-gray-500">
                                            {building.typologie}
                                        </p>
                                    </div>
                                </div>

                                {building.description && (
                                    <p className="text-sm text-gray-600 mb-4">
                                        {building.description}
                                    </p>
                                )}

                                <div className="grid grid-cols-2 gap-4 mb-4 text-sm">
                                    <div className="text-center p-3 bg-gray-50 rounded-lg">
                                        <p className="font-medium text-gray-900">
                                            {building.niveaux?.length || 0}
                                        </p>
                                        <p className="text-gray-500">Niveaux</p>
                                    </div>
                                    <div className="text-center p-3 bg-gray-50 rounded-lg">
                                        <p className="font-medium text-gray-900">
                                            {building.parties?.length || 0}
                                        </p>
                                        <p className="text-gray-500">Parties</p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <Link
                                        to={`/sites/${site.id}/buildings/${building.id}`}
                                        className="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                    >
                                        Voir détails
                                        <ChevronRight size={16} />
                                    </Link>
                                    <Link
                                        to={`/sites/${site.id}/buildings/${building.id}/edit`}
                                        className="p-2 text-blue-600 hover:bg-blue-50 rounded-md"
                                    >
                                        <Edit size={16} />
                                    </Link>
                                    <button
                                        onClick={() => {
                                            setBuildingToDelete(building);
                                            setShowDeleteModal(true);
                                        }}
                                        className="p-2 text-red-600 hover:bg-red-50 rounded-md"
                                    >
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Modal de suppression */}
            <Modal
                isOpen={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                title="Supprimer le bâtiment"
            >
                <div className="space-y-4">
                    <p className="text-gray-600">
                        Êtes-vous sûr de vouloir supprimer le bâtiment "{buildingToDelete?.nom}" ?
                        Cette action est irréversible et supprimera également tous les niveaux et parties associés.
                    </p>
                    <div className="flex gap-3 justify-end">
                        <button
                            onClick={() => setShowDeleteModal(false)}
                            className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={handleDeleteBuilding}
                            className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    );
};

export default Buildings; 