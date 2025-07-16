import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import { interventionService, type TypeIntervention, type Intervenant, type CreateInterventionData } from '../../services/interventionService';
import LoadingSpinner from '../common/LoadingSpinner';

interface InterventionFormProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
    selectedPartieIds: number[];
    batimentName: string;
}

const InterventionForm: React.FC<InterventionFormProps> = ({
    isOpen,
    onClose,
    onSuccess,
    selectedPartieIds,
    batimentName
}) => {
    const [loading, setLoading] = useState(false);
    const [loadingData, setLoadingData] = useState(true);
    const [typesInterventions, setTypesInterventions] = useState<TypeIntervention[]>([]);
    const [intervenants, setIntervenants] = useState<Intervenant[]>([]);
    const [selectedIntervenant, setSelectedIntervenant] = useState<Intervenant | null>(null);
    
    const [formData, setFormData] = useState<CreateInterventionData>({
        intitule: '',
        entreprise_nom: '',
        intervenant_nom: '',
        type_intervention_id: 0,
        partie_ids: selectedPartieIds
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    useEffect(() => {
        if (isOpen) {
            loadInitialData();
            setFormData(prev => ({ ...prev, partie_ids: selectedPartieIds }));
        }
    }, [isOpen, selectedPartieIds]);

    const loadInitialData = async () => {
        try {
            setLoadingData(true);
            const [typesData, intervenantsData] = await Promise.all([
                interventionService.getTypesInterventions(),
                interventionService.getIntervenants()
            ]);
            
            console.log('Types d\'interventions récupérés:', typesData);
            console.log('Intervenants récupérés:', intervenantsData);
            
            setTypesInterventions(Array.isArray(typesData) ? typesData : []);
            setIntervenants(Array.isArray(intervenantsData) ? intervenantsData : []);
        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
            setTypesInterventions([]);
            setIntervenants([]);
        } finally {
            setLoadingData(false);
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: name === 'type_intervention_id' ? parseInt(value) : value
        }));
        // Effacer l'erreur du champ modifié
        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: '' }));
        }
    };

    const handleIntervenantChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const intervenantId = parseInt(e.target.value);
        const intervenant = intervenants.find(i => i.id === intervenantId);
        
        if (intervenant) {
            setSelectedIntervenant(intervenant);
            setFormData(prev => ({
                ...prev,
                intervenant_nom: `${intervenant.prenom} ${intervenant.nom}`,
                entreprise_nom: intervenant.organisation
            }));
        } else {
            setSelectedIntervenant(null);
            setFormData(prev => ({
                ...prev,
                intervenant_nom: '',
                entreprise_nom: ''
            }));
        }
        
        // Effacer les erreurs liées
        setErrors(prev => ({ 
            ...prev, 
            intervenant_nom: '',
            entreprise_nom: ''
        }));
    };

    const validateForm = (): boolean => {
        const newErrors: Record<string, string> = {};

        if (!formData.intitule.trim()) {
            newErrors.intitule = 'L\'intitulé est requis';
        }

        if (!formData.entreprise_nom.trim()) {
            newErrors.entreprise_nom = 'Le nom de l\'entreprise est requis';
        }

        if (!formData.intervenant_nom.trim()) {
            newErrors.intervenant_nom = 'Le nom de l\'intervenant est requis';
        }

        if (!formData.type_intervention_id) {
            newErrors.type_intervention_id = 'Le type d\'intervention est requis';
        }

        if (formData.partie_ids.length === 0) {
            newErrors.partie_ids = 'Au moins une partie doit être sélectionnée';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        try {
            setLoading(true);
            await interventionService.create(formData);
            onSuccess();
            onClose();
            resetForm();
        } catch (error: any) {
            console.error('Erreur lors de la création de l\'intervention:', error);
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
        } finally {
            setLoading(false);
        }
    };

    const resetForm = () => {
        setFormData({
            intitule: '',
            entreprise_nom: '',
            intervenant_nom: '',
            type_intervention_id: 0,
            partie_ids: selectedPartieIds
        });
        setSelectedIntervenant(null);
        setErrors({});
    };

    const handleClose = () => {
        resetForm();
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-xl font-bold">Créer une intervention</h2>
                    <button
                        onClick={handleClose}
                        className="text-gray-500 hover:text-gray-700"
                    >
                        <X size={24} />
                    </button>
                </div>

                {loadingData ? (
                    <div className="flex justify-center py-8">
                        <LoadingSpinner />
                    </div>
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Intitulé de l'intervention *
                            </label>
                            <input
                                type="text"
                                name="intitule"
                                value={formData.intitule}
                                onChange={handleInputChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: Contrôle des extincteurs"
                            />
                            {errors.intitule && (
                                <p className="text-red-500 text-sm mt-1">{errors.intitule}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Type d'intervention *
                            </label>
                            <select
                                name="type_intervention_id"
                                value={formData.type_intervention_id}
                                onChange={handleInputChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Sélectionnez un type</option>
                                {Array.isArray(typesInterventions) && typesInterventions.map(type => (
                                    <option key={type.id} value={type.id}>
                                        {type.nom}
                                    </option>
                                ))}
                            </select>
                            {errors.type_intervention_id && (
                                <p className="text-red-500 text-sm mt-1">{errors.type_intervention_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Intervenant *
                            </label>
                            <select
                                value={selectedIntervenant?.id || ''}
                                onChange={handleIntervenantChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Sélectionnez un intervenant</option>
                                {Array.isArray(intervenants) && intervenants.map(intervenant => (
                                    <option key={intervenant.id} value={intervenant.id}>
                                        {intervenant.prenom} {intervenant.nom} ({intervenant.organisation})
                                    </option>
                                ))}
                            </select>
                            {errors.intervenant_nom && (
                                <p className="text-red-500 text-sm mt-1">{errors.intervenant_nom}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Nom de l'intervenant
                            </label>
                            <input
                                type="text"
                                name="intervenant_nom"
                                value={formData.intervenant_nom}
                                onChange={handleInputChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                                placeholder="Automatiquement rempli lors de la sélection"
                                readOnly
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Entreprise/Organisation
                            </label>
                            <input
                                type="text"
                                name="entreprise_nom"
                                value={formData.entreprise_nom}
                                onChange={handleInputChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                                placeholder="Automatiquement rempli lors de la sélection"
                                readOnly
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Parties concernées
                            </label>
                            <div className="bg-gray-50 p-3 rounded-md">
                                <p className="text-sm text-gray-600">
                                    Bâtiment: <span className="font-medium">{batimentName}</span>
                                </p>
                                <p className="text-sm text-gray-600">
                                    Nombre de parties sélectionnées: <span className="font-medium">{selectedPartieIds.length}</span>
                                </p>
                            </div>
                            {errors.partie_ids && (
                                <p className="text-red-500 text-sm mt-1">{errors.partie_ids}</p>
                            )}
                        </div>

                        <div className="flex justify-end space-x-2 pt-4">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                disabled={loading}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                {loading && <LoadingSpinner size="small" />}
                                Créer l'intervention
                            </button>
                        </div>
                    </form>
                )}
            </div>
        </div>
    );
};

export default InterventionForm; 