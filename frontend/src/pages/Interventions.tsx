import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    Wrench,
    Calendar,
    User,
    Building,
    FileText,
    Edit,
    Trash2,
    Plus,
    CheckCircle,
    Clock,
    AlertCircle,
    XCircle,
    Eye
} from 'lucide-react';
import { interventionService, type Intervention } from '../services/interventionService';
import { useAuth } from '../contexts/AuthContext';
import LoadingSpinner from '../components/common/LoadingSpinner';

const Interventions: React.FC = () => {
    const { user } = useAuth();
    const [interventions, setInterventions] = useState<Intervention[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchInterventions();
    }, []);

    const fetchInterventions = async () => {
        try {
            setLoading(true);
            setError(null);
            const data = await interventionService.getAll();
            setInterventions(data);
        } catch (error) {
            console.error('Erreur lors de la récupération des interventions:', error);
            setError('Erreur lors du chargement des interventions');
        } finally {
            setLoading(false);
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
                return <Clock size={16} />;
            case 'en_cours':
                return <AlertCircle size={16} />;
            case 'termine':
                return <CheckCircle size={16} />;
            case 'annule':
                return <XCircle size={16} />;
            default:
                return <Clock size={16} />;
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

    const handleDelete = async (id: number) => {
        if (window.confirm('Êtes-vous sûr de vouloir supprimer cette intervention ?')) {
            try {
                await interventionService.delete(id);
                setInterventions(prev => prev.filter(intervention => intervention.id !== id));
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                alert('Erreur lors de la suppression de l\'intervention');
            }
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <LoadingSpinner />
            </div>
        );
    }

    if (error) {
        return (
            <div className="text-center py-12">
                <div className="text-red-600 mb-4">
                    <AlertCircle size={48} className="mx-auto mb-2" />
                    <p className="text-lg">{error}</p>
                </div>
                <button
                    onClick={fetchInterventions}
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Réessayer
                </button>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <Wrench className="text-blue-600" size={28} />
                        {user?.role === 'super-admin' ? 'Toutes les Interventions' : 
                         user?.role === 'user-intervenant' ? 'Mes Interventions Assignées' : 
                         'Mes Interventions'}
                    </h1>
                    <p className="text-gray-600">
                        {user?.role === 'super-admin' 
                            ? 'Supervisez toutes les interventions du système' 
                            : user?.role === 'user-intervenant'
                            ? 'Gérez les interventions qui vous sont assignées'
                            : 'Gérez toutes vos interventions et suivez leur progression'
                        }
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    <span className="text-sm text-gray-500">
                        Total: {interventions.length} intervention{interventions.length > 1 ? 's' : ''}
                    </span>
                </div>
            </div>

            {interventions.length === 0 ? (
                <div className="text-center py-12">
                    <Wrench size={48} className="mx-auto text-gray-400 mb-4" />
                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                        {user?.role === 'super-admin' ? 'Aucune intervention dans le système' : 
                         user?.role === 'user-intervenant' ? 'Aucune intervention assignée' :
                         'Aucune intervention créée'}
                    </h3>
                    <p className="text-gray-500 mb-6">
                        {user?.role === 'super-admin' 
                            ? 'Aucune intervention n\'a encore été créée dans le système.' 
                            : user?.role === 'user-intervenant'
                            ? 'Aucune intervention ne vous a encore été assignée.'
                            : 'Vous n\'avez encore créé aucune intervention. Rendez-vous dans les détails d\'un bâtiment pour créer votre première intervention.'
                        }
                    </p>
                    {user?.role === 'user-entreprise' && (
                        <Link
                            to="/sites"
                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            <Building size={16} />
                            Voir les sites
                        </Link>
                    )}
                </div>
            ) : (
                <div className="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Intervention
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Intervenant
                                    </th>
                                    {user?.role === 'super-admin' && (
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Créateur
                                        </th>
                                    )}
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statut
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Parties
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date création
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {interventions.map((intervention) => (
                                    <tr key={intervention.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center">
                                                <div className="flex-shrink-0">
                                                    <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <Wrench className="h-5 w-5 text-blue-600" />
                                                    </div>
                                                </div>
                                                <div className="ml-4">
                                                    <div className="text-sm font-medium text-gray-900">
                                                        {intervention.intitule}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {intervention.entreprise_nom}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="text-sm text-gray-900">
                                                {intervention.type_intervention?.nom || 'N/A'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center">
                                                <User className="h-4 w-4 text-gray-400 mr-2" />
                                                <span className="text-sm text-gray-900">
                                                    {intervention.intervenant_nom}
                                                </span>
                                            </div>
                                        </td>
                                        {user?.role === 'super-admin' && (
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="text-sm text-gray-900">
                                                    {intervention.entreprise_nom}
                                                </span>
                                            </td>
                                        )}
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(intervention.statut)}`}>
                                                {getStatusIcon(intervention.statut)}
                                                {getStatusLabel(intervention.statut)}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center">
                                                <Building className="h-4 w-4 text-gray-400 mr-2" />
                                                <span className="text-sm text-gray-900">
                                                    {intervention.parties?.length || 0} partie{(intervention.parties?.length || 0) > 1 ? 's' : ''}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div className="flex items-center">
                                                <Calendar className="h-4 w-4 mr-2" />
                                                {new Date(intervention.created_at).toLocaleDateString('fr-FR')}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    to={`/interventions/${intervention.id}`}
                                                    className="text-green-600 hover:text-green-900"
                                                    title="Voir les détails"
                                                >
                                                    <Eye size={16} />
                                                </Link>
                                                <button
                                                    onClick={() => {
                                                        // TODO: Implement edit functionality
                                                    }}
                                                    className="text-blue-600 hover:text-blue-900"
                                                    title="Modifier"
                                                >
                                                    <Edit size={16} />
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(intervention.id)}
                                                    className="text-red-600 hover:text-red-900"
                                                    title="Supprimer"
                                                >
                                                    <Trash2 size={16} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Interventions; 