import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { siteService } from '../services/api';
import type { Site } from '../types';
import { Plus, Edit, Trash2, MapPin, Building, Eye, ChevronRight } from 'lucide-react';
import LoadingSpinner from '../components/common/LoadingSpinner';
import Modal from '../components/common/Modal';
import SiteForm from '../components/sites/SiteForm';

const Sites: React.FC = () => {
    const [sites, setSites] = useState<Site[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string>('');
    const [showModal, setShowModal] = useState(false);
    const [selectedSite, setSelectedSite] = useState<Site | undefined>();
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [siteToDelete, setSiteToDelete] = useState<Site | null>(null);

    useEffect(() => {
        loadSites();
    }, []);

    const loadSites = async () => {
        try {
            setLoading(true);
            const data = await siteService.getAll();
            setSites(data);
        } catch (err) {
            setError('Erreur lors du chargement des sites');
            console.error('Erreur:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!siteToDelete) return;

        try {
            await siteService.delete(siteToDelete.id);
            setSites(sites.filter(site => site.id !== siteToDelete.id));
            setShowDeleteModal(false);
            setSiteToDelete(null);
        } catch (err) {
            setError('Erreur lors de la suppression');
            console.error('Erreur:', err);
        }
    };

    const handleEdit = (site: Site) => {
        setSelectedSite(site);
        setShowModal(true);
    };

    const handleCreate = () => {
        setSelectedSite(undefined);
        setShowModal(true);
    };

    const handleSave = (site: Site) => {
        if (selectedSite) {
            // Modification
            setSites(sites.map(s => s.id === site.id ? site : s));
        } else {
            // Création
            setSites([...sites, site]);
        }
        setShowModal(false);
        setSelectedSite(undefined);
    };

    const handleCancel = () => {
        setShowModal(false);
        setSelectedSite(undefined);
    };

    if (loading) {
        return <LoadingSpinner />;
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Gestion des Sites</h1>
                        <p className="text-gray-600 mt-2">
                            Gérez tous vos sites et leurs informations ({sites.length} site(s))
                        </p>
                    </div>
                    <button
                        onClick={handleCreate}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        <Plus size={16} />
                        Nouveau Site
                    </button>
                </div>
            </div>

            {/* Error Message */}
            {error && (
                <div className="bg-red-50 border border-red-200 rounded-md p-4">
                    <div className="text-red-700 text-sm">{error}</div>
                </div>
            )}

            {/* Sites List */}
            <div className="bg-white rounded-lg shadow-sm">
                {sites.length === 0 ? (
                    <div className="text-center py-12">
                        <MapPin className="mx-auto h-12 w-12 text-gray-400" />
                        <h3 className="mt-4 text-lg font-medium text-gray-900">
                            Aucun site trouvé
                        </h3>
                        <p className="mt-2 text-gray-500">
                            Commencez par créer votre premier site
                        </p>
                        <button
                            onClick={handleCreate}
                            className="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <Plus size={16} />
                            Créer un Site
                        </button>
                    </div>
                ) : (
                    <div className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {sites.map((site) => (
                                <div
                                    key={site.id}
                                    className="border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md transition-all"
                                >
                                    <div className="flex items-start gap-4 mb-4">
                                        <div className="bg-blue-100 p-3 rounded-lg">
                                            <MapPin className="h-6 w-6 text-blue-600" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="text-lg font-semibold text-gray-900 mb-1">
                                                {site.nom}
                                            </h3>
                                            {site.description && (
                                                <p className="text-sm text-gray-600 mb-2">
                                                    {site.description}
                                                </p>
                                            )}
                                            <div className="text-sm text-gray-500">
                                                <p>{site.adresse}</p>
                                                <p>{site.code_postal} {site.ville}, {site.pays}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4 mb-4 text-sm">
                                        <div className="flex items-center gap-1">
                                            <Building className="h-4 w-4 text-gray-400" />
                                            <span className="text-gray-600">
                                                {site.batiments?.length || 0} bâtiment(s)
                                            </span>
                                        </div>
                                        {site.client && (
                                            <div className="text-gray-500">
                                                Client: {site.client.prenom} {site.client.nom}
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Link
                                            to={`/sites/${site.id}`}
                                            className="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                        >
                                            <Eye size={16} />
                                            Voir détails
                                            <ChevronRight size={14} />
                                        </Link>
                                        <button
                                            onClick={() => handleEdit(site)}
                                            className="p-2 text-blue-600 hover:bg-blue-50 rounded-md"
                                        >
                                            <Edit size={16} />
                                        </button>
                                        <button
                                            onClick={() => {
                                                setSiteToDelete(site);
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
                    </div>
                )}
            </div>

            {/* Modal pour créer/éditer un site */}
            <Modal
                isOpen={showModal}
                onClose={handleCancel}
                title={selectedSite ? 'Modifier le site' : 'Créer un nouveau site'}
            >
                <SiteForm
                    site={selectedSite}
                    onSave={handleSave}
                    onCancel={handleCancel}
                />
            </Modal>

            {/* Modal de suppression */}
            <Modal
                isOpen={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                title="Supprimer le site"
            >
                <div className="space-y-4">
                    <p className="text-gray-600">
                        Êtes-vous sûr de vouloir supprimer le site "{siteToDelete?.nom}" ?
                        Cette action est irréversible et supprimera également tous les bâtiments associés.
                    </p>
                    <div className="flex gap-3 justify-end">
                        <button
                            onClick={() => setShowDeleteModal(false)}
                            className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={handleDelete}
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

export default Sites; 