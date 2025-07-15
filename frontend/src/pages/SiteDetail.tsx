import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Building as BuildingIcon,
    MapPin,
    Plus,
    Edit,
    Trash2,
    ChevronRight,
    Calendar,
    User,
    Info,
    X,
    Layers
} from 'lucide-react';
import { siteService, buildingService, levelService } from '../services/api';
import type { Site, Building, BatimentFormData, NiveauTemp } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';
import Modal from '../components/common/Modal';

const SiteDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [site, setSite] = useState<Site | null>(null);
    const [buildings, setBuildings] = useState<Building[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [buildingToDelete, setBuildingToDelete] = useState<Building | null>(null);

    // États pour la modal de création
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [createLoading, setCreateLoading] = useState(false);
    const [formData, setFormData] = useState<BatimentFormData>({
        nom: '',
        site_id: 0,
        typologie: 'ERP'
    });
    const [formErrors, setFormErrors] = useState<Record<string, string[]>>({});

    // États pour la gestion des niveaux
    const [niveaux, setNiveaux] = useState<NiveauTemp[]>([]);
    const [showNiveauxSection, setShowNiveauxSection] = useState(false);

    useEffect(() => {
        if (id) {
            fetchSiteData();
            setFormData(prev => ({ ...prev, site_id: Number(id) }));
        }
    }, [id]);

    const fetchSiteData = async () => {
        try {
            setLoading(true);
            const [siteData, allBuildings] = await Promise.all([
                siteService.getById(Number(id)),
                buildingService.getAll(Number(id))
            ]);

            // Filtrer côté frontend en attendant la correction backend
            const filteredBuildings = allBuildings.filter(building => building.site_id === Number(id));

            // Trier par date de création (plus récent en premier)
            const sortedBuildings = filteredBuildings.sort((a, b) =>
                new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
            );

            setSite(siteData);
            setBuildings(sortedBuildings);
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

    const handleCreateBuilding = async () => {
        try {
            setCreateLoading(true);
            setFormErrors({});

            // Créer le bâtiment
            const newBuilding = await buildingService.create(formData);

            // Créer les niveaux si il y en a
            if (niveaux.length > 0) {
                const niveauxPromises = niveaux.map(niveau =>
                    levelService.create({
                        nom: niveau.nom,
                        batiment_id: newBuilding.id,
                        numero_etage: niveau.numero_etage,
                        description: niveau.description
                    })
                );

                try {
                    await Promise.all(niveauxPromises);

                    // Recharger les données du bâtiment pour inclure les niveaux créés
                    const updatedBuilding = await buildingService.getById(newBuilding.id);

                    // Ajouter le bâtiment mis à jour au début de la liste
                    setBuildings([updatedBuilding, ...buildings]);
                } catch (niveauError) {
                    console.error('Erreur lors de la création des niveaux:', niveauError);
                    // Le bâtiment est créé mais pas tous les niveaux
                    // Ajouter le bâtiment sans les niveaux
                    setBuildings([newBuilding, ...buildings]);
                }
            } else {
                // Pas de niveaux à créer, ajouter directement le bâtiment
                setBuildings([newBuilding, ...buildings]);
            }

            setShowCreateModal(false);
            resetForm();
        } catch (err: any) {
            if (err.response?.data?.errors) {
                setFormErrors(err.response.data.errors);
            } else {
                console.error('Erreur lors de la création:', err);
            }
        } finally {
            setCreateLoading(false);
        }
    };

    const handleInputChange = (field: keyof BatimentFormData, value: string) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));
        // Clear error for this field
        if (formErrors[field]) {
            setFormErrors(prev => ({
                ...prev,
                [field]: []
            }));
        }
    };

    // Fonctions pour la gestion des niveaux
    const addNiveau = () => {
        const newNiveau: NiveauTemp = {
            id: Date.now().toString(), // ID temporaire
            nom: '',
            numero_etage: niveaux.length === 0 ? 0 : Math.max(...niveaux.map(n => n.numero_etage)) + 1,
            description: ''
        };
        setNiveaux([...niveaux, newNiveau]);
        setShowNiveauxSection(true);
    };

    const removeNiveau = (id: string) => {
        setNiveaux(niveaux.filter(n => n.id !== id));
        if (niveaux.length === 1) {
            setShowNiveauxSection(false);
        }
    };

    const updateNiveau = (id: string, field: keyof Omit<NiveauTemp, 'id'>, value: string | number) => {
        setNiveaux(niveaux.map(n =>
            n.id === id ? { ...n, [field]: value } : n
        ));
    };

    const resetForm = () => {
        setFormData({
            nom: '',
            site_id: Number(id),
            typologie: 'ERP'
        });
        setFormErrors({});
        setNiveaux([]);
        setShowNiveauxSection(false);
    };

    const getTypologyColor = (typologie: string) => {
        const colors = {
            'ERP': 'bg-blue-100 text-blue-800',
            'IGH': 'bg-red-100 text-red-800',
            'HAB': 'bg-green-100 text-green-800',
            'BUP': 'bg-yellow-100 text-yellow-800',
            'ICPE': 'bg-purple-100 text-purple-800'
        };
        return colors[typologie as keyof typeof colors] || 'bg-gray-100 text-gray-800';
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
                    Retour aux sites
                </Link>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header avec informations du site */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-center justify-between mb-6">
                    <nav className="flex items-center space-x-2 text-sm text-gray-500">
                        <Link to="/sites" className="hover:text-gray-700">Sites</Link>
                        <ChevronRight size={16} />
                        <span className="text-gray-900">{site.nom}</span>
                    </nav>
                    <div className="flex items-center gap-2">
                        <Link
                            to={`/sites/${site.id}/edit`}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <Edit size={16} />
                            Modifier le site
                        </Link>
                    </div>
                </div>

                <div className="flex items-start gap-6">
                    <div className="bg-blue-100 p-4 rounded-xl">
                        <BuildingIcon className="h-10 w-10 text-blue-600" />
                    </div>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold text-gray-900 mb-3">
                            {site.nom}
                        </h1>
                        <div className="flex items-center gap-2 text-gray-600 mb-3">
                            <MapPin size={18} />
                            <span className="text-lg">
                                {site.adresse}, {site.code_postal} {site.ville}, {site.pays}
                            </span>
                        </div>
                        {site.description && (
                            <div className="flex items-start gap-2 mb-3">
                                <Info size={18} className="text-gray-400 mt-0.5" />
                                <p className="text-gray-700 text-lg">{site.description}</p>
                            </div>
                        )}
                        <div className="flex items-center gap-4 text-sm text-gray-500">
                            <div className="flex items-center gap-1">
                                <Calendar size={16} />
                                <span>Créé le {new Date(site.created_at).toLocaleDateString('fr-FR')}</span>
                            </div>
                            {site.client && (
                                <div className="flex items-center gap-1">
                                    <User size={16} />
                                    <span>Client: {site.client.prenom} {site.client.nom}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Liste des bâtiments */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">
                        Bâtiments ({buildings.length})
                    </h2>
                    <button
                        onClick={() => setShowCreateModal(true)}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                    >
                        <Plus size={16} />
                        Nouveau bâtiment
                    </button>
                </div>

                {buildings.length === 0 ? (
                    <div className="text-center py-12">
                        <BuildingIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun bâtiment</h3>
                        <p className="text-gray-500 mb-6">Ce site ne contient encore aucun bâtiment.</p>
                        <button
                            onClick={() => setShowCreateModal(true)}
                            className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <Plus size={18} />
                            Créer le premier bâtiment
                        </button>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {buildings.map((building) => (
                            <div
                                key={building.id}
                                className="border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md transition-all"
                            >
                                <div className="flex items-start justify-between">
                                    <div className="flex items-start gap-4 flex-1">
                                        <div className="bg-blue-100 p-3 rounded-lg">
                                            <BuildingIcon className="h-6 w-6 text-blue-600" />
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-2">
                                                <h3 className="text-xl font-semibold text-gray-900">
                                                    {building.nom}
                                                </h3>
                                                <span className={`px-2 py-1 text-xs font-medium rounded-full ${getTypologyColor(building.typologie)}`}>
                                                    {building.typologie}
                                                </span>
                                            </div>

                                            {building.description && (
                                                <p className="text-gray-600 mb-3">{building.description}</p>
                                            )}

                                            <div className="grid grid-cols-2 gap-4 mb-4">
                                                <div className="text-center p-3 bg-gray-50 rounded-lg">
                                                    <p className="text-lg font-semibold text-gray-900">
                                                        {building.niveaux?.length || 0}
                                                    </p>
                                                    <p className="text-sm text-gray-500">Niveaux</p>
                                                </div>
                                                <div className="text-center p-3 bg-gray-50 rounded-lg">
                                                    <p className="text-lg font-semibold text-gray-900">
                                                        {building.parties?.length || 0}
                                                    </p>
                                                    <p className="text-sm text-gray-500">Parties</p>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-2 text-sm text-gray-500">
                                                <Calendar size={14} />
                                                <span>Créé le {new Date(building.created_at).toLocaleDateString('fr-FR')}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2 ml-4">
                                        <Link
                                            to={`/sites/${site.id}/batiments/${building.id}`}
                                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
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

            {/* Modal de création de bâtiment */}
            <Modal
                isOpen={showCreateModal}
                onClose={() => {
                    setShowCreateModal(false);
                    resetForm();
                }}
                title="Créer un nouveau bâtiment"
            >
                <div className="space-y-6">
                    <div className="grid grid-cols-1 gap-4">
                        {/* Nom du bâtiment */}
                        <div>
                            <label htmlFor="nom" className="block text-sm font-medium text-gray-700 mb-1">
                                Nom du bâtiment *
                            </label>
                            <input
                                type="text"
                                id="nom"
                                value={formData.nom}
                                onChange={(e) => handleInputChange('nom', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${formErrors.nom ? 'border-red-500' : 'border-gray-300'
                                    }`}
                                placeholder="Ex: Bâtiment A, Tour Nord..."
                            />
                            {formErrors.nom && (
                                <p className="mt-1 text-sm text-red-600">{formErrors.nom[0]}</p>
                            )}
                        </div>

                        {/* Typologie */}
                        <div>
                            <label htmlFor="typologie" className="block text-sm font-medium text-gray-700 mb-1">
                                Typologie *
                            </label>
                            <select
                                id="typologie"
                                value={formData.typologie}
                                onChange={(e) => handleInputChange('typologie', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${formErrors.typologie ? 'border-red-500' : 'border-gray-300'
                                    }`}
                            >
                                <option value="ERP">ERP - Établissement Recevant du Public</option>
                                <option value="IGH">IGH - Immeuble de Grande Hauteur</option>
                                <option value="HAB">HAB - Habitation</option>
                                <option value="BUP">BUP - Bâtiment à Usage Principal</option>
                                <option value="ICPE">ICPE - Installation Classée</option>
                            </select>
                            {formErrors.typologie && (
                                <p className="mt-1 text-sm text-red-600">{formErrors.typologie[0]}</p>
                            )}
                        </div>

                        {/* Section Niveaux */}
                        <div className="border-t pt-4">
                            <div className="flex items-center justify-between mb-3">
                                <label className="block text-sm font-medium text-gray-700">
                                    Niveaux du bâtiment
                                </label>
                                <button
                                    type="button"
                                    onClick={addNiveau}
                                    className="inline-flex items-center gap-2 px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700"
                                >
                                    <Plus size={14} />
                                    Ajouter un niveau
                                </button>
                            </div>

                            {niveaux.length === 0 ? (
                                <p className="text-sm text-gray-500 italic">
                                    Aucun niveau ajouté. Vous pourrez en créer après la création du bâtiment.
                                </p>
                            ) : (
                                <div className="space-y-3 max-h-60 overflow-y-auto">
                                    {niveaux.map((niveau, index) => (
                                        <div key={niveau.id} className="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                            <div className="flex items-start gap-3">
                                                <div className="bg-blue-100 p-2 rounded-md">
                                                    <Layers size={16} className="text-blue-600" />
                                                </div>
                                                <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <div>
                                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                                            Nom du niveau *
                                                        </label>
                                                        <input
                                                            type="text"
                                                            value={niveau.nom}
                                                            onChange={(e) => updateNiveau(niveau.id, 'nom', e.target.value)}
                                                            className="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                            placeholder="Ex: Rez-de-chaussée"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                                            Numéro d'étage *
                                                        </label>
                                                        <input
                                                            type="number"
                                                            value={niveau.numero_etage}
                                                            onChange={(e) => updateNiveau(niveau.id, 'numero_etage', parseInt(e.target.value) || 0)}
                                                            className="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                            placeholder="0"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-xs font-medium text-gray-600 mb-1">
                                                            Description
                                                        </label>
                                                        <input
                                                            type="text"
                                                            value={niveau.description || ''}
                                                            onChange={(e) => updateNiveau(niveau.id, 'description', e.target.value)}
                                                            className="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                            placeholder="Description (optionnel)"
                                                        />
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() => removeNiveau(niveau.id)}
                                                    className="p-1 text-red-600 hover:bg-red-50 rounded"
                                                >
                                                    <X size={16} />
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Boutons d'action */}
                    <div className="flex gap-3 justify-end pt-4 border-t">
                        <button
                            onClick={() => {
                                setShowCreateModal(false);
                                resetForm();
                            }}
                            disabled={createLoading}
                            className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md disabled:opacity-50"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={handleCreateBuilding}
                            disabled={createLoading || !formData.nom.trim() || niveaux.some(n => !n.nom.trim())}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            {createLoading && (
                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            )}
                            {createLoading ? 'Création...' : 'Créer le bâtiment'}
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    );
};

export default SiteDetail; 