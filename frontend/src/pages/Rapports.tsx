import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    FileText,
    Calendar,
    User,
    Building,
    Eye,
    Download,
    Filter,
    Search,
    AlertCircle,
    Plus,
    MapPin,
    Edit,
    CheckCircle,
    Clock,
    Archive,
    FileSignature
} from 'lucide-react';
import { rapportService, type Rapport } from '../services/rapportService';
import { useAuth } from '../contexts/AuthContext';
import LoadingSpinner from '../components/common/LoadingSpinner';

const Rapports: React.FC = () => {
    const { user } = useAuth();
    const [rapports, setRapports] = useState<Rapport[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState<string>('');

    useEffect(() => {
        fetchRapports();
    }, []);

    const fetchRapports = async () => {
        try {
            setLoading(true);
            setError(null);
            const data = await rapportService.getAll();
            setRapports(data);
        } catch (error) {
            console.error('Erreur lors de la récupération des rapports:', error);
            setError('Erreur lors du chargement des rapports');
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

    // Filtrer les rapports
    const filteredRapports = rapports.filter(rapport => {
        const matchesSearch = !searchTerm || 
            rapport.type_rapport?.nom?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            rapport.type_rapport?.libelle?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            rapport.intervention?.intitule?.toLowerCase().includes(searchTerm.toLowerCase());
        
        const matchesStatus = !statusFilter || rapport.statut === statusFilter;
        
        return matchesSearch && matchesStatus;
    });

    if (loading) {
        return <LoadingSpinner />;
    }

    if (error) {
        return (
            <div className="text-center py-8">
                <div className="text-red-600 mb-4">
                    <AlertCircle size={48} className="mx-auto mb-2" />
                    <p className="text-lg">{error}</p>
                </div>
                <button
                    onClick={fetchRapports}
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Réessayer
                </button>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {/* Header */}
            <div className="flex items-center justify-between mb-8">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileText size={28} className="text-blue-600" />
                        Rapports
                    </h1>
                    <p className="text-gray-600 mt-1">
                        Gestion des rapports d'interventions
                    </p>
                </div>
                <div className="flex items-center gap-4">
                    <div className="text-sm text-gray-500">
                        {filteredRapports.length} rapport{filteredRapports.length > 1 ? 's' : ''}
                    </div>
                </div>
            </div>

            {/* Filtres */}
            <div className="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Rechercher
                        </label>
                        <div className="relative">
                            <Search size={16} className="absolute left-3 top-3 text-gray-400" />
                            <input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder="Rechercher par nom, type ou intervention..."
                                className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Statut
                        </label>
                        <select
                            value={statusFilter}
                            onChange={(e) => setStatusFilter(e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="brouillon">Brouillon</option>
                            <option value="finalise">Finalisé</option>
                            <option value="signe">Signé</option>
                            <option value="archive">Archivé</option>
                        </select>
                    </div>
                    <div className="flex items-end">
                        <button
                            onClick={() => {
                                setSearchTerm('');
                                setStatusFilter('');
                            }}
                            className="px-4 py-2 text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            <Filter size={16} className="inline mr-1" />
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            {/* Liste des rapports */}
            {filteredRapports.length === 0 ? (
                <div className="text-center py-12">
                    <FileText size={48} className="mx-auto text-gray-400 mb-4" />
                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                        {searchTerm || statusFilter ? 'Aucun rapport trouvé' : 'Aucun rapport'}
                    </h3>
                    <p className="text-gray-500">
                        {searchTerm || statusFilter 
                            ? 'Essayez de modifier vos critères de recherche.'
                            : 'Les rapports apparaîtront ici une fois créés lors des interventions.'
                        }
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 gap-6">
                    {filteredRapports.map((rapport) => (
                        <Link
                            key={rapport.id}
                            to={`/rapports/${rapport.id}`}
                            className="block bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow"
                        >
                            <div className="p-6">
                                <div className="flex items-start justify-between mb-4">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2 mb-2">
                                            <FileText size={20} className="text-blue-600" />
                                            <h3 className="text-lg font-semibold text-gray-900">
                                                {rapport.type_rapport?.nom || rapport.type_rapport?.libelle || 'Rapport'}
                                            </h3>
                                        </div>
                                        {rapport.type_rapport?.sous_titre && (
                                            <p className="text-gray-600 text-sm mb-2">
                                                {rapport.type_rapport.sous_titre}
                                            </p>
                                        )}
                                        {rapport.intervention && (
                                            <p className="text-gray-600 text-sm">
                                                Intervention: {rapport.intervention.intitule}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {getStatusIcon(rapport.statut)}
                                        <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getRapportStatusColor(rapport.statut)}`}>
                                            {getRapportStatusLabel(rapport.statut)}
                                        </span>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
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
                                    {rapport.intervention && (
                                        <div>
                                            <span className="font-medium text-gray-500">Intervenant:</span>
                                            <div className="flex items-center gap-1 mt-1">
                                                <User size={14} className="text-gray-400" />
                                                <span className="text-gray-900">
                                                    {rapport.intervention.intervenant_nom}
                                                </span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {(rapport.observations?.length || rapport.fichiers?.length || rapport.parties?.length) && (
                                    <div className="mt-4 pt-4 border-t border-gray-200">
                                        <div className="flex items-center gap-6 text-sm text-gray-500">
                                            {rapport.parties && rapport.parties.length > 0 && (
                                                <div className="flex items-center gap-1">
                                                    <MapPin size={14} />
                                                    <span>{rapport.parties.length} partie{rapport.parties.length > 1 ? 's' : ''}</span>
                                                </div>
                                            )}
                                            {rapport.observations && rapport.observations.length > 0 && (
                                                <div className="flex items-center gap-1">
                                                    <Eye size={14} />
                                                    <span>{rapport.observations.length} observation{rapport.observations.length > 1 ? 's' : ''}</span>
                                                </div>
                                            )}
                                            {rapport.fichiers && rapport.fichiers.length > 0 && (
                                                <div className="flex items-center gap-1">
                                                    <Download size={14} />
                                                    <span>{rapport.fichiers.length} fichier{rapport.fichiers.length > 1 ? 's' : ''}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
};

export default Rapports; 