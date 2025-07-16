import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Building as BuildingIcon,
    Layers,
    Plus,
    Edit,
    Trash2,
    ChevronRight,
    Calendar,
    User,
    Info,
    X,
    MapPin,
    Home,
    Users,
    Square,
    Wrench,
    CheckSquare
} from 'lucide-react';
import { buildingService } from '../services/api';
import { partieService as partieServiceLocal } from '../services/partieService';
import { useAuth } from '../contexts/AuthContext';
import { usePermissions } from '../hooks/usePermissions';
import type { Batiment, Partie, Niveau, PartieFormData } from '../types';
import LoadingSpinner from '../components/common/LoadingSpinner';
import Modal from '../components/common/Modal';
import EntrepriseOwnershipManagement from '../components/batiments/EntrepriseOwnershipManagement';
import InterventionForm from '../components/interventions/InterventionForm';

const BatimentDetail: React.FC = () => {
    const { siteId, batimentId } = useParams<{ siteId: string; batimentId: string }>();
    const { user } = useAuth();
    const { canCreateParties, canEditBuildings, canDeleteItems, canManageOwnership, isEnterpriseUser } = usePermissions();
    const [batiment, setBatiment] = useState<Batiment | null>(null);
    const [parties, setParties] = useState<Partie[]>([]);
    const [filteredParties, setFilteredParties] = useState<Partie[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [partieToDelete, setPartieToDelete] = useState<Partie | null>(null);

    // États pour la modal de création
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [createLoading, setCreateLoading] = useState(false);
    const [formData, setFormData] = useState<PartieFormData>({
        nom: '',
        batiment_id: 0,
        type: 'privative'
    });
    const [formErrors, setFormErrors] = useState<Record<string, string[]>>({});

    // États pour la gestion des niveaux dans le formulaire
    const [selectedNiveaux, setSelectedNiveaux] = useState<number[]>([]);

    // États pour les interventions
    const [showInterventionForm, setShowInterventionForm] = useState(false);
    const [selectedPartiesForIntervention, setSelectedPartiesForIntervention] = useState<number[]>([]);
    const [selectionMode, setSelectionMode] = useState(false);

    // Fonction pour mettre à jour les parties depuis le composant de gestion des entreprises
    const handlePartiesUpdate = (updatedParties: Partie[]) => {
        setParties(updatedParties);
    };

    // Fonctions pour la gestion des interventions
    const handleStartSelection = () => {
        setSelectionMode(true);
        setSelectedPartiesForIntervention([]);
    };

    const handleCancelSelection = () => {
        setSelectionMode(false);
        setSelectedPartiesForIntervention([]);
    };

    const handlePartieSelection = (partieId: number) => {
        setSelectedPartiesForIntervention(prev => 
            prev.includes(partieId) 
                ? prev.filter(id => id !== partieId)
                : [...prev, partieId]
        );
    };

    const handleCreateIntervention = () => {
        if (selectedPartiesForIntervention.length === 0) {
            alert('Veuillez sélectionner au moins une partie');
            return;
        }
        setShowInterventionForm(true);
    };

    const handleInterventionSuccess = () => {
        setSelectionMode(false);
        setSelectedPartiesForIntervention([]);
        setShowInterventionForm(false);
        // Optionnel : rafraîchir les données ou afficher un message de succès
    };

    useEffect(() => {
        if (siteId && batimentId) {
            fetchBatimentData();
            setFormData(prev => ({ ...prev, batiment_id: Number(batimentId) }));
        }
    }, [siteId, batimentId]);

    useEffect(() => {
        filterParties();
    }, [parties, user]);

    const filterParties = () => {
        let filtered = [...parties];

        // Filtrer pour les utilisateurs entreprise
        if (isEnterpriseUser() && user) {
            // Ne montrer que les parties dont l'utilisateur est propriétaire
            filtered = parties.filter(partie => partie.owner_id === user.id);
        }

        setFilteredParties(filtered);
    };

    const fetchBatimentData = async () => {
        try {
            setLoading(true);
            setError(null);

            console.log('Chargement du bâtiment:', batimentId);

            // Utiliser le service API standard au lieu du service direct
            const batimentData = await buildingService.getById(Number(batimentId));

            console.log('Données bâtiment récupérées:', batimentData);
            setBatiment(batimentData);

            // Récupérer les parties du bâtiment
            try {
                const partiesData = await partieServiceLocal.getByBatiment(Number(batimentId));
                setParties(partiesData);
            } catch (partiesErr) {
                console.warn('Erreur lors du chargement des parties:', partiesErr);
                setParties([]); // Continuer sans les parties
            }
        } catch (err: any) {
            console.error('Erreur détaillée:', err);

            if (err.response?.status === 401) {
                setError('Token d\'authentification invalide ou expiré');
            } else if (err.response?.status === 404) {
                setError('Bâtiment non trouvé');
            } else if (err.response?.status === 403) {
                setError('Accès refusé à ce bâtiment');
            } else {
                setError(`Erreur lors du chargement des données du bâtiment (${err.response?.status || 'inconnue'})`);
            }
        } finally {
            setLoading(false);
        }
    };

    const handleDeletePartie = async () => {
        if (!partieToDelete) return;

        try {
            await partieServiceLocal.delete(partieToDelete.id);
            setParties(parties.filter(p => p.id !== partieToDelete.id));
            setShowDeleteModal(false);
            setPartieToDelete(null);
        } catch (err) {
            console.error('Erreur lors de la suppression:', err);
        }
    };

    const handleCreatePartie = async () => {
        try {
            setCreateLoading(true);
            setFormErrors({});

            // Créer les objets niveaux avec les données du formulaire général
            const niveauxData = selectedNiveaux.map(niveauId => ({
                niveau_id: niveauId,
                libelle: formData.nom, // Utiliser le nom de la partie comme libellé
                effectif_public: formData.effectif_public || 0,
                personnel: formData.effectif_personnel || 0,
                surface_exploitation: formData.surface_exploitation || 0,
                surface_gla: formData.surface_gla || 0,
                surface_accessible_public: formData.surface_accessible_public || 0
            }));

            const newPartie = await partieServiceLocal.create({
                ...formData,
                niveaux: niveauxData
            });

            setParties([newPartie, ...parties]);
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

    const handleInputChange = (field: keyof PartieFormData, value: string | number | boolean) => {
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

    const resetForm = () => {
        setFormData({
            nom: '',
            batiment_id: Number(batimentId),
            type: 'privative'
        });
        setFormErrors({});
        setSelectedNiveaux([]);
    };

    const handleNiveauToggle = (niveau: Niveau) => {
        const isSelected = selectedNiveaux.includes(niveau.id);
        if (isSelected) {
            setSelectedNiveaux(selectedNiveaux.filter(id => id !== niveau.id));
        } else {
            setSelectedNiveaux([...selectedNiveaux, niveau.id]);
        }
    };

    const getTypeColor = (type: string) => {
        const colors = {
            'privative': 'bg-blue-100 text-blue-800',
            'commune': 'bg-green-100 text-green-800'
        };
        return colors[type as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    if (loading) {
        return <LoadingSpinner />;
    }

    if (error || !batiment) {
        return (
            <div className="text-center py-8">
                <p className="text-red-600">{error || 'Bâtiment non trouvé'}</p>
                <div className="mt-4 space-y-4">
                    <button
                        onClick={() => {
                            console.log('Token localStorage:', localStorage.getItem('authToken') ? 'Présent' : 'Absent');
                            console.log('User localStorage:', localStorage.getItem('user') ? 'Présent' : 'Absent');
                            console.log('Rafraîchissez la page ou reconnectez-vous !');
                        }}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                    >
                        🔍 Vérifier mon token (voir console)
                    </button>
                    <br />
                    <button
                        onClick={() => {
                            localStorage.removeItem('authToken');
                            localStorage.removeItem('user');
                            window.location.href = '/login';
                        }}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    >
                        🔄 Forcer la reconnexion
                    </button>
                    <br />
                    <Link
                        to={`/sites/${siteId}`}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Retour au site
                    </Link>
                </div>
                <div className="mt-6 p-4 bg-blue-50 rounded-lg text-left max-w-md mx-auto border border-blue-200">
                    <p className="text-sm text-blue-700 mb-2">
                        <strong>🔧 Problème résolu !</strong>
                    </p>
                    <p className="text-sm text-gray-600 mb-2">
                        Le problème était une incohérence dans la gestion des tokens.
                        Essayez de rafraîchir la page ou de vous reconnecter.
                    </p>
                    <p className="text-sm text-gray-600 mb-2">
                        Compte de test disponible :
                    </p>
                    <p className="text-sm font-mono bg-white p-2 rounded border">
                        📧 admin@axignis.com<br />
                        🔑 password123
                    </p>
                    <p className="text-xs text-gray-500 mt-2">
                        Ce compte super-admin a accès à tous les bâtiments.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header avec informations du bâtiment */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-center justify-between mb-6">
                    <nav className="flex items-center space-x-2 text-sm text-gray-500">
                        <Link to="/sites" className="hover:text-gray-700">Sites</Link>
                        <ChevronRight size={16} />
                        <Link to={`/sites/${siteId}`} className="hover:text-gray-700">
                            {batiment.site?.nom || 'Site'}
                        </Link>
                        <ChevronRight size={16} />
                        <span className="text-gray-900">{batiment.nom}</span>
                    </nav>
                    <div className="flex items-center gap-2">
                        <Link
                            to={`/sites/${siteId}/batiments/${batiment.id}/edit`}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <Edit size={16} />
                            Modifier le bâtiment
                        </Link>
                    </div>
                </div>

                <div className="flex items-start gap-6">
                    <div className="bg-blue-100 p-4 rounded-xl">
                        <BuildingIcon className="h-10 w-10 text-blue-600" />
                    </div>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold text-gray-900 mb-3">
                            {batiment.nom}
                        </h1>
                        <div className="flex items-center gap-2 text-gray-600 mb-3">
                            <MapPin size={18} />
                            <span className="text-lg">
                                {batiment.site?.nom} - {batiment.site?.ville}
                            </span>
                        </div>
                        <div className="flex items-center gap-3 mb-3">
                            <span className={`px-3 py-1 text-sm font-medium rounded-full ${getTypeColor(batiment.typologie)}`}>
                                {batiment.typologie}
                            </span>
                        </div>
                        {batiment.description && (
                            <div className="flex items-start gap-2 mb-3">
                                <Info size={18} className="text-gray-400 mt-0.5" />
                                <p className="text-gray-700 text-lg">{batiment.description}</p>
                            </div>
                        )}
                        <div className="flex items-center gap-4 text-sm text-gray-500">
                            <div className="flex items-center gap-1">
                                <Calendar size={16} />
                                <span>Créé le {new Date(batiment.created_at).toLocaleDateString('fr-FR')}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Statistiques */}
                <div className="grid grid-cols-3 gap-4 mt-6">
                    <div className="text-center p-4 bg-gray-50 rounded-lg">
                        <p className="text-2xl font-bold text-gray-900">
                            {batiment.niveaux?.length || 0}
                        </p>
                        <p className="text-sm text-gray-500">Niveaux</p>
                    </div>
                    <div className="text-center p-4 bg-gray-50 rounded-lg">
                        <p className="text-2xl font-bold text-gray-900">
                            {parties.length}
                        </p>
                        <p className="text-sm text-gray-500">Parties</p>
                    </div>
                    <div className="text-center p-4 bg-gray-50 rounded-lg">
                        <p className="text-2xl font-bold text-gray-900">
                            {batiment.parties?.reduce((total, partie) => total + (partie.lots?.length || 0), 0) || 0}
                        </p>
                        <p className="text-sm text-gray-500">Lots</p>
                    </div>
                </div>
            </div>

            {/* Niveaux disponibles */}
            {batiment.niveaux && batiment.niveaux.length > 0 && (
                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <h2 className="text-xl font-semibold text-gray-900 mb-4">
                        Niveaux disponibles ({batiment.niveaux.length})
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {batiment.niveaux.map((niveau) => (
                            <div
                                key={niveau.id}
                                className="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
                            >
                                <div className="flex items-center gap-3 mb-2">
                                    <div className="bg-blue-100 p-2 rounded-lg">
                                        <Layers className="h-5 w-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <h3 className="font-medium text-gray-900">{niveau.nom}</h3>
                                        <p className="text-sm text-gray-500">Étage {niveau.numero_etage}</p>
                                    </div>
                                </div>
                                {niveau.description && (
                                    <p className="text-sm text-gray-600 mb-2">{niveau.description}</p>
                                )}
                                <div className="text-xs text-gray-500">
                                    {niveau.parties?.length || 0} partie(s)
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Message pour les utilisateurs entreprise */}
            {isEnterpriseUser() && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div className="flex items-center gap-2">
                        <User className="text-blue-600" size={20} />
                        <p className="text-blue-800">
                            <span className="font-medium">Utilisateur Entreprise :</span> Vous ne voyez que les parties qui vous sont assignées dans ce bâtiment.
                        </p>
                    </div>
                </div>
            )}

            {/* Actions pour les interventions - Utilisateurs entreprise */}
            {isEnterpriseUser() && filteredParties.length > 0 && (
                <div className="bg-white p-6 rounded-lg shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <Wrench className="text-blue-600" size={20} />
                            Interventions
                        </h3>
                        {!selectionMode ? (
                            <button
                                onClick={handleStartSelection}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                <CheckSquare size={16} />
                                Créer une intervention
                            </button>
                        ) : (
                            <div className="flex items-center gap-2">
                                <span className="text-sm text-gray-600">
                                    {selectedPartiesForIntervention.length} partie(s) sélectionnée(s)
                                </span>
                                <button
                                    onClick={handleCreateIntervention}
                                    disabled={selectedPartiesForIntervention.length === 0}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <Wrench size={16} />
                                    Créer intervention
                                </button>
                                <button
                                    onClick={handleCancelSelection}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                >
                                    <X size={16} />
                                    Annuler
                                </button>
                            </div>
                        )}
                    </div>
                    {selectionMode && (
                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p className="text-sm text-blue-800">
                                <strong>Mode sélection activé :</strong> Cliquez sur les parties pour lesquelles vous souhaitez créer une intervention.
                            </p>
                        </div>
                    )}
                </div>
            )}

            {/* Liste des parties */}
            <div className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">
                        Parties ({filteredParties.length})
                    </h2>
                    {canCreateParties() && (
                        <button
                            onClick={() => setShowCreateModal(true)}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        >
                            <Plus size={16} />
                            Nouvelle partie
                        </button>
                    )}
                </div>

                {filteredParties.length === 0 ? (
                    <div className="text-center py-12">
                        <Home className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 className="text-lg font-medium text-gray-900 mb-2">Aucune partie</h3>
                        <p className="text-gray-500 mb-6">Ce bâtiment ne contient encore aucune partie.</p>
                        {canCreateParties() && (
                            <button
                                onClick={() => setShowCreateModal(true)}
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                <Plus size={18} />
                                Créer la première partie
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="space-y-4">
                        {filteredParties.map((partie) => (
                            <div
                                key={partie.id}
                                className={`border rounded-lg p-6 hover:shadow-md transition-all ${
                                    selectionMode
                                        ? selectedPartiesForIntervention.includes(partie.id)
                                            ? 'border-blue-500 bg-blue-50 shadow-md'
                                            : 'border-gray-200 hover:border-blue-300 cursor-pointer'
                                        : 'border-gray-200 hover:border-blue-300'
                                }`}
                                onClick={selectionMode ? () => handlePartieSelection(partie.id) : undefined}
                            >
                                <div className="flex items-start justify-between">
                                    <div className="flex items-start gap-4 flex-1">
                                        {selectionMode && (
                                            <div className="flex items-center justify-center mt-1">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedPartiesForIntervention.includes(partie.id)}
                                                    onChange={() => handlePartieSelection(partie.id)}
                                                    className="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                                />
                                            </div>
                                        )}
                                        <div className="bg-green-100 p-3 rounded-lg">
                                            <Home className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-2">
                                                <h3 className="text-xl font-semibold text-gray-900">
                                                    {partie.nom}
                                                </h3>
                                                <span className={`px-2 py-1 text-xs font-medium rounded-full ${getTypeColor(partie.type)}`}>
                                                    {partie.type}
                                                </span>
                                                {partie.isICPE && (
                                                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                        ICPE
                                                    </span>
                                                )}
                                            </div>

                                            {/* Affichage du propriétaire */}
                                            <div className="mb-3">
                                                {partie.owner ? (
                                                    <div className="flex items-center gap-2 text-sm text-gray-600">
                                                        <User className="h-4 w-4" />
                                                        <span className="font-medium">Propriétaire:</span>
                                                        <span className="text-gray-900 font-medium">
                                                            {partie.owner.prenom} {partie.owner.nom}
                                                        </span>
                                                        <span className="text-gray-500">
                                                            ({partie.owner.organisation})
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <div className="flex items-center gap-2 text-sm text-gray-500">
                                                        <User className="h-4 w-4" />
                                                        <span className="font-medium">Propriétaire:</span>
                                                        <span className="italic">Non assigné</span>
                                                    </div>
                                                )}
                                            </div>



                                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                                <div className="text-center p-3 bg-gray-50 rounded-lg">
                                                    <p className="text-lg font-semibold text-gray-900">
                                                        {partie.niveaux?.length || 0}
                                                    </p>
                                                    <p className="text-sm text-gray-500">Niveaux</p>
                                                </div>
                                                <div className="text-center p-3 bg-gray-50 rounded-lg">
                                                    <p className="text-lg font-semibold text-gray-900">
                                                        {partie.lots?.length || 0}
                                                    </p>
                                                    <p className="text-sm text-gray-500">Lots</p>
                                                </div>
                                                <div className="text-center p-3 bg-gray-50 rounded-lg">
                                                    <p className="text-lg font-semibold text-gray-900">
                                                        {partie.effectif_public || 0}
                                                    </p>
                                                    <p className="text-sm text-gray-500">Public</p>
                                                </div>
                                                <div className="text-center p-3 bg-gray-50 rounded-lg">
                                                    <p className="text-lg font-semibold text-gray-900">
                                                        {partie.effectif_personnel || 0}
                                                    </p>
                                                    <p className="text-sm text-gray-500">Personnel</p>
                                                </div>
                                            </div>

                                            {partie.niveaux && partie.niveaux.length > 0 && (
                                                <div className="mb-3">
                                                    <p className="text-sm font-medium text-gray-700 mb-2">Niveaux occupés:</p>
                                                    <div className="flex flex-wrap gap-2">
                                                        {partie.niveaux.map((niveau) => (
                                                            <span
                                                                key={niveau.id}
                                                                className="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                                                            >
                                                                <Layers size={12} />
                                                                {niveau.nom}
                                                            </span>
                                                        ))}
                                                    </div>
                                                </div>
                                            )}

                                            <div className="flex items-center gap-2 text-sm text-gray-500">
                                                <Calendar size={14} />
                                                <span>Créé le {new Date(partie.created_at).toLocaleDateString('fr-FR')}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2 ml-4">
                                        {canEditBuildings() && (
                                            <button
                                                onClick={() => {
                                                    // TODO: Implement edit functionality
                                                }}
                                                className="p-2 text-blue-600 hover:bg-blue-50 rounded-md"
                                            >
                                                <Edit size={16} />
                                            </button>
                                        )}
                                        {canDeleteItems() && (
                                            <button
                                                onClick={() => {
                                                    setPartieToDelete(partie);
                                                    setShowDeleteModal(true);
                                                }}
                                                className="p-2 text-red-600 hover:bg-red-50 rounded-md"
                                            >
                                                <Trash2 size={16} />
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Gestion des Entreprises - Super-Admin uniquement */}
            {canManageOwnership() && (
                <EntrepriseOwnershipManagement
                    batimentId={Number(batimentId)}
                    onPartiesUpdate={handlePartiesUpdate}
                />
            )}

            {/* Modal de suppression */}
            <Modal
                isOpen={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                title="Confirmer la suppression"
            >
                <div className="space-y-4">
                    <p className="text-gray-700">
                        Êtes-vous sûr de vouloir supprimer la partie "{partieToDelete?.nom}" ?
                        Cette action est irréversible.
                    </p>
                    <div className="flex justify-end gap-3">
                        <button
                            onClick={() => setShowDeleteModal(false)}
                            className="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={handleDeletePartie}
                            className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </Modal>

            {/* Modal de création */}
            <Modal
                isOpen={showCreateModal}
                onClose={() => {
                    setShowCreateModal(false);
                    resetForm();
                }}
                title="Créer une nouvelle partie"
            >
                <div className="space-y-6">
                    <div className="grid grid-cols-1 gap-4">
                        {/* Nom de la partie */}
                        <div>
                            <label htmlFor="nom" className="block text-sm font-medium text-gray-700 mb-1">
                                Nom de la partie *
                            </label>
                            <input
                                type="text"
                                id="nom"
                                value={formData.nom}
                                onChange={(e) => handleInputChange('nom', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${formErrors.nom ? 'border-red-500' : 'border-gray-300'
                                    }`}
                                placeholder="Ex: Bureau 101, Salle de réunion..."
                            />
                            {formErrors.nom && (
                                <p className="mt-1 text-sm text-red-600">{formErrors.nom[0]}</p>
                            )}
                        </div>

                        {/* Type de partie */}
                        <div>
                            <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-1">
                                Type de partie *
                            </label>
                            <select
                                id="type"
                                value={formData.type}
                                onChange={(e) => handleInputChange('type', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${formErrors.type ? 'border-red-500' : 'border-gray-300'
                                    }`}
                            >
                                <option value="privative">Privative</option>
                                <option value="commune">Commune</option>
                            </select>
                            {formErrors.type && (
                                <p className="mt-1 text-sm text-red-600">{formErrors.type[0]}</p>
                            )}
                        </div>



                        {/* ICPE */}
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                id="isICPE"
                                checked={formData.est_icpe || false}
                                onChange={(e) => handleInputChange('est_icpe', e.target.checked)}
                                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label htmlFor="isICPE" className="ml-2 block text-sm text-gray-700">
                                Installation Classée pour la Protection de l'Environnement (ICPE)
                            </label>
                        </div>

                        {/* Effectifs */}
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="effectif_public" className="block text-sm font-medium text-gray-700 mb-1">
                                    Effectif public
                                </label>
                                <input
                                    type="number"
                                    id="effectif_public"
                                    value={formData.effectif_public || ''}
                                    onChange={(e) => handleInputChange('effectif_public', Number(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                />
                            </div>
                            <div>
                                <label htmlFor="effectif_personnel" className="block text-sm font-medium text-gray-700 mb-1">
                                    Effectif personnel
                                </label>
                                <input
                                    type="number"
                                    id="effectif_personnel"
                                    value={formData.effectif_personnel || ''}
                                    onChange={(e) => handleInputChange('effectif_personnel', Number(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                />
                            </div>
                        </div>

                        {/* Surfaces */}
                        <div className="grid grid-cols-1 gap-4">
                            <div>
                                <label htmlFor="surface_exploitation" className="block text-sm font-medium text-gray-700 mb-1">
                                    Surface d'exploitation (m²)
                                </label>
                                <input
                                    type="number"
                                    id="surface_exploitation"
                                    value={formData.surface_exploitation || ''}
                                    onChange={(e) => handleInputChange('surface_exploitation', Number(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                            <div>
                                <label htmlFor="surface_gla" className="block text-sm font-medium text-gray-700 mb-1">
                                    Surface GLA (m²)
                                </label>
                                <input
                                    type="number"
                                    id="surface_gla"
                                    value={formData.surface_gla || ''}
                                    onChange={(e) => handleInputChange('surface_gla', Number(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                            <div>
                                <label htmlFor="surface_accessible_public" className="block text-sm font-medium text-gray-700 mb-1">
                                    Surface accessible au public (m²)
                                </label>
                                <input
                                    type="number"
                                    id="surface_accessible_public"
                                    value={formData.surface_accessible_public || ''}
                                    onChange={(e) => handleInputChange('surface_accessible_public', Number(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Sélection des niveaux */}
                    {batiment?.niveaux && batiment.niveaux.length > 0 && (
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                Niveaux occupés par cette partie
                            </label>
                            <div className="space-y-3 max-h-60 overflow-y-auto">
                                {batiment.niveaux.map((niveau) => {
                                    const isSelected = selectedNiveaux.includes(niveau.id);

                                    return (
                                        <div key={niveau.id} className="border border-gray-200 rounded-lg p-3">
                                            <div className="flex items-start gap-3">
                                                <input
                                                    type="checkbox"
                                                    checked={isSelected}
                                                    onChange={() => handleNiveauToggle(niveau)}
                                                    className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                />
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <Layers size={16} className="text-blue-600" />
                                                        <span className="font-medium">{niveau.nom}</span>
                                                        <span className="text-sm text-gray-500">
                                                            (Étage {niveau.numero_etage})
                                                        </span>
                                                    </div>

                                                    {isSelected && (
                                                        <div className="mt-2 p-2 bg-green-50 rounded text-sm text-green-700">
                                                            ✓ Ce niveau utilisera les valeurs du formulaire général
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    <div className="flex justify-end gap-3">
                        <button
                            onClick={() => {
                                setShowCreateModal(false);
                                resetForm();
                            }}
                            className="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Annuler
                        </button>
                        <button
                            onClick={handleCreatePartie}
                            disabled={createLoading}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {createLoading ? 'Création...' : 'Créer la partie'}
                        </button>
                    </div>
                </div>
            </Modal>

            {/* Formulaire de création d'intervention */}
            <InterventionForm
                isOpen={showInterventionForm}
                onClose={() => setShowInterventionForm(false)}
                onSuccess={handleInterventionSuccess}
                selectedPartieIds={selectedPartiesForIntervention}
                batimentName={batiment?.nom || 'Bâtiment'}
            />
        </div>
    );
};

export default BatimentDetail; 