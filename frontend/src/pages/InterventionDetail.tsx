import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Wrench,
    Calendar,
    User,
    Building,
    FileText,
    ArrowLeft,
    CheckCircle,
    Clock,
    AlertCircle,
    XCircle,
    Eye,
    Download,
    Info,
    MapPin,
    Users,
    Briefcase,
    Plus
} from 'lucide-react';
import { interventionService, type Intervention } from '../services/interventionService';
import { useAuth } from '../contexts/AuthContext';
import LoadingSpinner from '../components/common/LoadingSpinner';
import RapportForm from '../components/rapports/RapportForm';

const InterventionDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const { user } = useAuth();
    const [intervention, setIntervention] = useState<Intervention | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showCreateRapportModal, setShowCreateRapportModal] = useState(false);

    useEffect(() => {
        if (id) {
            fetchIntervention();
        }
    }, [id]);

    const fetchIntervention = async () => {
        try {
            setLoading(true);
            setError(null);
            const data = await interventionService.getById(Number(id));
            setIntervention(data);
        } catch (error) {
            console.error('Erreur lors de la récupération de l\'intervention:', error);
            setError('Erreur lors du chargement de l\'intervention');
        } finally {
            setLoading(false);
        }
    };

    const handleStatusChange = async (newStatus: string) => {
        try {
            const updatedIntervention = await interventionService.updateStatus(Number(id), newStatus);
            setIntervention(updatedIntervention);
        } catch (error) {
            console.error('Erreur lors de la mise à jour du statut:', error);
            setError('Erreur lors de la mise à jour du statut');
        }
    };

    const getStatusColor = (statut: string) => {
        switch (statut) {
            case 'planifie':
                return 'bg-blue-100 text-blue-800';
            case 'en_cours':
                return 'bg-yellow-100 text-yellow-800';
            case 'termine':
                return 'bg-green-100 text-green-800';
            case 'annule':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusIcon = (statut: string) => {
        switch (statut) {
            case 'planifie':
                return <Clock size={20} />;
            case 'en_cours':
                return <AlertCircle size={20} />;
            case 'termine':
                return <CheckCircle size={20} />;
            case 'annule':
                return <XCircle size={20} />;
            default:
                return <Clock size={20} />;
        }
    };

    const getStatusLabel = (statut: string) => {
        switch (statut) {
            case 'planifie':
                return 'Planifiée';
            case 'en_cours':
                return 'En cours';
            case 'termine':
                return 'Terminée';
            case 'annule':
                return 'Annulée';
            default:
                return statut;
        }
    };

    const getRapportStatusColor = (statut: string) => {
        switch (statut) {
            case 'brouillon':
                return 'bg-gray-100 text-gray-800';
            case 'finalise':
                return 'bg-blue-100 text-blue-800';
            case 'signe':
                return 'bg-green-100 text-green-800';
            case 'archive':
                return 'bg-purple-100 text-purple-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getRapportStatusLabel = (statut: string) => {
        switch (statut) {
            case 'brouillon':
                return 'Brouillon';
            case 'finalise':
                return 'Finalisé';
            case 'signe':
                return 'Signé';
            case 'archive':
                return 'Archivé';
            default:
                return statut;
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <LoadingSpinner />
            </div>
        );
    }

    if (error || !intervention) {
        return (
            <div className="text-center py-12">
                <div className="text-red-600 mb-4">
                    <AlertCircle size={48} className="mx-auto mb-2" />
                    <p className="text-lg">{error || 'Intervention non trouvée'}</p>
                </div>
                <Link
                    to="/interventions"
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Retour aux interventions
                </Link>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <Link
                        to="/interventions"
                        className="flex items-center gap-2 text-blue-600 hover:text-blue-700"
                    >
                        <ArrowLeft size={20} />
                        Retour
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <Wrench className="text-blue-600" size={28} />
                            {intervention.intitule}
                        </h1>
                        <p className="text-gray-600">
                            Détails de l'intervention
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <span className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(intervention.statut)}`}>
                        {getStatusIcon(intervention.statut)}
                        {getStatusLabel(intervention.statut)}
                    </span>
                    {user?.role === 'user-intervenant' && (
                        <div className="flex items-center gap-2">
                            {intervention.statut === 'planifie' && (
                                <button
                                    onClick={() => handleStatusChange('en_cours')}
                                    className="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700"
                                >
                                    Commencer
                                </button>
                            )}
                            {intervention.statut === 'en_cours' && (
                                <button
                                    onClick={() => handleStatusChange('termine')}
                                    className="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700"
                                >
                                    Terminer
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Informations principales */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Détails de l'intervention */}
                <div className="bg-white rounded-lg shadow-sm p-6">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <Info size={20} />
                        Informations générales
                    </h2>
                    <div className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-500 mb-1">
                                    Type d'intervention
                                </label>
                                <p className="text-gray-900 font-medium">
                                    {intervention.type_intervention?.nom || 'Non spécifié'}
                                </p>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-500 mb-1">
                                    Statut
                                </label>
                                <span className={`inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(intervention.statut)}`}>
                                    {getStatusIcon(intervention.statut)}
                                    {getStatusLabel(intervention.statut)}
                                </span>
                            </div>
                        </div>

                        {user?.role === 'super-admin' && (
                            <div>
                                <label className="block text-sm font-medium text-gray-500 mb-1">
                                    Créée par
                                </label>
                                <div className="flex items-center gap-2">
                                    <User size={16} className="text-gray-400" />
                                    <span className="text-gray-900">
                                        {intervention.entreprise_nom}
                                    </span>
                                </div>
                            </div>
                        )}

                        <div>
                            <label className="block text-sm font-medium text-gray-500 mb-1">
                                Date de création
                            </label>
                            <div className="flex items-center gap-2">
                                <Calendar size={16} className="text-gray-400" />
                                <span className="text-gray-900">
                                    {new Date(intervention.created_at).toLocaleDateString('fr-FR', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    })}
                                </span>
                            </div>
                        </div>

                        {intervention.signed_at && (
                            <div>
                                <label className="block text-sm font-medium text-gray-500 mb-1">
                                    Signée le
                                </label>
                                <div className="flex items-center gap-2">
                                    <CheckCircle size={16} className="text-green-500" />
                                    <span className="text-gray-900">
                                        {new Date(intervention.signed_at).toLocaleDateString('fr-FR')}
                                    </span>
                                    {intervention.signed_by && (
                                        <span className="text-gray-500">
                                            par {intervention.signed_by}
                                        </span>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Détails de l'intervenant */}
                <div className="bg-white rounded-lg shadow-sm p-6">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <User size={20} />
                        Intervenant
                    </h2>
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-500 mb-1">
                                Nom de l'intervenant
                            </label>
                            <div className="flex items-center gap-2">
                                <User size={16} className="text-gray-400" />
                                <span className="text-gray-900 font-medium">
                                    {intervention.intervenant_nom}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-500 mb-1">
                                Entreprise/Organisation
                            </label>
                            <div className="flex items-center gap-2">
                                <Briefcase size={16} className="text-gray-400" />
                                <span className="text-gray-900 font-medium">
                                    {intervention.entreprise_nom}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Parties concernées */}
            <div className="bg-white rounded-lg shadow-sm p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <Building size={20} />
                    Parties concernées ({intervention.parties?.length || 0})
                </h2>
                {intervention.parties && intervention.parties.length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {intervention.parties.map((partie: any, index: number) => (
                            <div key={index} className="border border-gray-200 rounded-lg p-4">
                                <div className="flex items-center gap-2 mb-2">
                                    <Building size={16} className="text-blue-600" />
                                    <h3 className="font-medium text-gray-900">
                                        {partie.nom || `Partie ${index + 1}`}
                                    </h3>
                                </div>
                                <div className="text-sm text-gray-600 space-y-1">
                                    {partie.type && (
                                        <div className="flex items-center gap-1">
                                            <span className="font-medium">Type:</span>
                                            <span className={`px-2 py-1 rounded-full text-xs ${partie.type === 'privative' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}`}>
                                                {partie.type}
                                            </span>
                                        </div>
                                    )}
                                    {partie.batiment && (
                                        <div className="flex items-center gap-1">
                                            <span className="font-medium">Bâtiment:</span>
                                            <span>{partie.batiment.nom || partie.batiment.name}</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-gray-500">Aucune partie associée à cette intervention</p>
                )}
            </div>

            {/* Rapports associés */}
            <div className="bg-white rounded-lg shadow-sm p-6">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <FileText size={20} />
                        Rapports associés ({intervention.rapports?.length || 0})
                    </h2>
                    {user?.role === 'user-intervenant' && (
                        <button
                            onClick={() => setShowCreateRapportModal(true)}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2"
                        >
                            <Plus size={16} />
                            Nouveau rapport
                        </button>
                    )}
                </div>
                
                {intervention.rapports && intervention.rapports.length > 0 ? (
                    <div className="space-y-4">
                        {intervention.rapports.map((rapport: any, index: number) => (
                            <div key={index} className="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                <div className="flex items-center justify-between mb-3">
                                    <div className="flex items-center gap-2">
                                        <FileText size={16} className="text-blue-600" />
                                        <h3 className="font-medium text-gray-900">
                                            {rapport.type_rapport?.nom || `Rapport ${index + 1}`}
                                        </h3>
                                    </div>
                                    <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getRapportStatusColor(rapport.statut)}`}>
                                        {getRapportStatusLabel(rapport.statut)}
                                    </span>
                                </div>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span className="font-medium text-gray-500">Date d'émission:</span>
                                        <div className="flex items-center gap-1 mt-1">
                                            <Calendar size={14} className="text-gray-400" />
                                            <span className="text-gray-900">
                                                {rapport.date_emission 
                                                    ? new Date(rapport.date_emission).toLocaleDateString('fr-FR')
                                                    : 'Non spécifiée'
                                                }
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span className="font-medium text-gray-500">Créé le:</span>
                                        <div className="flex items-center gap-1 mt-1">
                                            <Calendar size={14} className="text-gray-400" />
                                            <span className="text-gray-900">
                                                {new Date(rapport.created_at).toLocaleDateString('fr-FR')}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {rapport.observations && rapport.observations.length > 0 && (
                                    <div className="mt-3">
                                        <span className="font-medium text-gray-500">Observations:</span>
                                        <div className="flex items-center gap-1 mt-1">
                                            <Eye size={14} className="text-gray-400" />
                                            <span className="text-gray-900">
                                                {rapport.observations.length} observation{rapport.observations.length > 1 ? 's' : ''}
                                            </span>
                                        </div>
                                    </div>
                                )}

                                {rapport.fichiers && rapport.fichiers.length > 0 && (
                                    <div className="mt-3">
                                        <span className="font-medium text-gray-500">Fichiers:</span>
                                        <div className="flex items-center gap-1 mt-1">
                                            <Download size={14} className="text-gray-400" />
                                            <span className="text-gray-900">
                                                {rapport.fichiers.length} fichier{rapport.fichiers.length > 1 ? 's' : ''}
                                            </span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-8">
                        <FileText size={48} className="mx-auto text-gray-400 mb-4" />
                        <h3 className="text-lg font-medium text-gray-900 mb-2">
                            Aucun rapport associé
                        </h3>
                        <p className="text-gray-500">
                            Aucun rapport n'a encore été créé pour cette intervention. Les rapports seront créés par les intervenants.
                        </p>
                    </div>
                )}
            </div>

            {/* Modal de création de rapport */}
            <RapportForm
                isOpen={showCreateRapportModal}
                onClose={() => setShowCreateRapportModal(false)}
                onSuccess={() => {
                    setShowCreateRapportModal(false);
                    fetchIntervention(); // Recharger les données de l'intervention
                }}
                interventionId={Number(id)}
                partiesInterventions={intervention.parties || []}
            />
        </div>
    );
};

export default InterventionDetail; 