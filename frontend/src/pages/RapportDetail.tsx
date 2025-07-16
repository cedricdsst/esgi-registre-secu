import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import {
    FileText,
    ArrowLeft,
    Calendar,
    User,
    Building,
    MapPin,
    Eye,
    Download,
    AlertCircle,
    CheckCircle,
    Clock,
    Archive,
    Edit,
    FileSignature,
    Info
} from 'lucide-react';
import { rapportService, type Rapport } from '../services/rapportService';
import { useAuth } from '../contexts/AuthContext';
import LoadingSpinner from '../components/common/LoadingSpinner';

const RapportDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const { user } = useAuth();
    const navigate = useNavigate();
    const [rapport, setRapport] = useState<Rapport | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (id) {
            fetchRapport();
        }
    }, [id]);

    const fetchRapport = async () => {
        try {
            setLoading(true);
            setError(null);
            const data = await rapportService.getById(Number(id));
            setRapport(data);
        } catch (error) {
            console.error('Erreur lors de la récupération du rapport:', error);
            setError('Erreur lors du chargement du rapport');
        } finally {
            setLoading(false);
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

    const getStatusIcon = (statut: string) => {
        switch (statut) {
            case 'brouillon':
                return <Edit size={16} className="text-gray-600" />;
            case 'finalise':
                return <CheckCircle size={16} className="text-blue-600" />;
            case 'signe':
                return <FileSignature size={16} className="text-green-600" />;
            case 'archive':
                return <Archive size={16} className="text-purple-600" />;
            default:
                return <Clock size={16} className="text-gray-600" />;
        }
    };

    const getPriorityColor = (priorite: string) => {
        switch (priorite) {
            case 'urgent':
                return 'bg-red-100 text-red-800 border-red-200';
            case 'normal':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'faible':
                return 'bg-green-100 text-green-800 border-green-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const getStatutTraitementColor = (statut: string) => {
        switch (statut) {
            case 'nouveau':
                return 'bg-blue-100 text-blue-800';
            case 'en_cours':
                return 'bg-yellow-100 text-yellow-800';
            case 'traite':
                return 'bg-green-100 text-green-800';
            case 'reporte':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const handleFileDownload = async (fileId: number) => {
        try {
            const blob = await rapportService.downloadFile(Number(id), fileId);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rapport_${id}_fichier_${fileId}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Erreur lors du téléchargement du fichier:', error);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <LoadingSpinner />
            </div>
        );
    }

    if (error || !rapport) {
        return (
            <div className="text-center py-12">
                <div className="text-red-600 mb-4">
                    <AlertCircle size={48} className="mx-auto mb-2" />
                    <p className="text-lg">{error || 'Rapport non trouvé'}</p>
                </div>
                <button
                    onClick={() => navigate(-1)}
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Retour
                </button>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {/* Header */}
            <div className="mb-8">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <button
                            onClick={() => navigate(-1)}
                            className="flex items-center gap-2 text-gray-600 hover:text-gray-900"
                        >
                            <ArrowLeft size={20} />
                            Retour
                        </button>
                        <div>
                                                         <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                 <FileText size={28} className="text-blue-600" />
                                 {rapport.type_rapport?.libelle || 'Rapport'}
                             </h1>
                            <p className="text-gray-600">
                                {rapport.type_rapport?.sous_titre && (
                                    <span>{rapport.type_rapport.sous_titre}</span>
                                )}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {getStatusIcon(rapport.statut)}
                        <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getRapportStatusColor(rapport.statut)}`}>
                            {getRapportStatusLabel(rapport.statut)}
                        </span>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Colonne principale */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Informations générales */}
                    <div className="bg-white rounded-lg shadow-sm border p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <Info size={20} />
                            Informations générales
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-500 mb-1">
                                    Date d'émission
                                </label>
                                <div className="flex items-center gap-2">
                                    <Calendar size={16} className="text-gray-400" />
                                    <span className="text-gray-900">
                                        {rapport.date_emission
                                            ? new Date(rapport.date_emission).toLocaleDateString('fr-FR')
                                            : 'Non spécifiée'
                                        }
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-500 mb-1">
                                    Créé le
                                </label>
                                <div className="flex items-center gap-2">
                                    <Calendar size={16} className="text-gray-400" />
                                    <span className="text-gray-900">
                                        {new Date(rapport.created_at).toLocaleDateString('fr-FR')}
                                    </span>
                                </div>
                            </div>
                            {rapport.type_rapport?.periodicite && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-500 mb-1">
                                        Périodicité
                                    </label>
                                    <span className="text-gray-900">{rapport.type_rapport.periodicite}</span>
                                </div>
                            )}
                            {rapport.type_rapport?.typologie_batiment && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-500 mb-1">
                                        Typologie bâtiment
                                    </label>
                                    <span className="text-gray-900">{rapport.type_rapport.typologie_batiment}</span>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Intervention associée */}
                    {rapport.intervention && (
                        <div className="bg-white rounded-lg shadow-sm border p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Building size={20} />
                                Intervention associée
                            </h2>
                            <div className="bg-gray-50 rounded-lg p-4">
                                <h3 className="font-medium text-gray-900 mb-2">
                                    {rapport.intervention.intitule}
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span className="font-medium text-gray-500">Entreprise:</span>
                                        <div className="flex items-center gap-1 mt-1">
                                            <User size={14} className="text-gray-400" />
                                            <span className="text-gray-900">{rapport.intervention.entreprise_nom}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span className="font-medium text-gray-500">Intervenant:</span>
                                        <div className="flex items-center gap-1 mt-1">
                                            <User size={14} className="text-gray-400" />
                                            <span className="text-gray-900">{rapport.intervention.intervenant_nom}</span>
                                        </div>
                                    </div>
                                </div>
                                <div className="mt-3">
                                    <Link
                                        to={`/interventions/${rapport.intervention.id}`}
                                        className="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm"
                                    >
                                        <Eye size={14} />
                                        Voir l'intervention
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Parties concernées */}
                    {rapport.parties && rapport.parties.length > 0 && (
                        <div className="bg-white rounded-lg shadow-sm border p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <MapPin size={20} />
                                Parties concernées ({rapport.parties.length})
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {rapport.parties.map((partie: any) => (
                                    <div key={partie.id} className="bg-gray-50 rounded-lg p-4">
                                        <h3 className="font-medium text-gray-900 mb-2">
                                            {partie.nom}
                                        </h3>
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
                        </div>
                    )}

                    {/* Observations */}
                    {rapport.observations && rapport.observations.length > 0 && (
                        <div className="bg-white rounded-lg shadow-sm border p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Eye size={20} />
                                Observations ({rapport.observations.length})
                            </h2>
                            <div className="space-y-4">
                                {rapport.observations.map((observation: any, index: number) => (
                                    <div key={index} className="border border-gray-200 rounded-lg p-4">
                                        <div className="flex items-start justify-between mb-3">
                                            <div className="flex-1">
                                                <h3 className="font-medium text-gray-900">
                                                    {observation.identification}
                                                </h3>
                                                <p className="text-gray-600 text-sm mt-1">
                                                    {observation.libelle}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getPriorityColor(observation.priorite)}`}>
                                                    {observation.priorite}
                                                </span>
                                                <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getStatutTraitementColor(observation.statut_traitement)}`}>
                                                    {observation.statut_traitement}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span className="font-medium text-gray-500">Localisation:</span>
                                                <div className="flex items-center gap-1 mt-1">
                                                    <MapPin size={14} className="text-gray-400" />
                                                    <span className="text-gray-900">{observation.localisation}</span>
                                                </div>
                                            </div>
                                            {observation.deja_signalee && (
                                                <div>
                                                    <span className="font-medium text-gray-500">Déjà signalée:</span>
                                                    <div className="flex items-center gap-1 mt-1">
                                                        <AlertCircle size={14} className="text-orange-500" />
                                                        <span className="text-orange-600">Oui</span>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Colonne latérale */}
                <div className="space-y-6">
                    {/* Fichiers attachés */}
                    {rapport.fichiers && rapport.fichiers.length > 0 && (
                        <div className="bg-white rounded-lg shadow-sm border p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <Download size={20} />
                                Fichiers ({rapport.fichiers.length})
                            </h2>
                            <div className="space-y-3">
                                {rapport.fichiers.map((fichier: any) => (
                                    <div key={fichier.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div className="flex-1">
                                            <p className="font-medium text-gray-900 text-sm">
                                                {fichier.nom_original}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                {fichier.taille} • Version {fichier.version}
                                            </p>
                                        </div>
                                        <button
                                            onClick={() => handleFileDownload(fichier.id)}
                                            className="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md"
                                        >
                                            <Download size={16} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Résumé */}
                    <div className="bg-white rounded-lg shadow-sm border p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            Résumé
                        </h2>
                        <div className="space-y-3">
                            <div className="flex justify-between items-center">
                                <span className="text-gray-600">Statut:</span>
                                <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getRapportStatusColor(rapport.statut)}`}>
                                    {getRapportStatusLabel(rapport.statut)}
                                </span>
                            </div>
                            <div className="flex justify-between items-center">
                                <span className="text-gray-600">Parties:</span>
                                <span className="text-gray-900">{rapport.parties?.length || 0}</span>
                            </div>
                            <div className="flex justify-between items-center">
                                <span className="text-gray-600">Observations:</span>
                                <span className="text-gray-900">{rapport.observations?.length || 0}</span>
                            </div>
                            <div className="flex justify-between items-center">
                                <span className="text-gray-600">Fichiers:</span>
                                <span className="text-gray-900">{rapport.fichiers?.length || 0}</span>
                            </div>
                            {rapport.type_rapport?.organisme_agree_requis && (
                                <div className="flex justify-between items-center">
                                    <span className="text-gray-600">Organisme agréé:</span>
                                    <span className="text-green-600 text-sm">Requis</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default RapportDetail; 