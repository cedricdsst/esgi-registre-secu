import React, { useState, useEffect } from 'react';
import {
    X,
    Save,
    Plus,
    FileText,
    Calendar,
    AlertCircle,
    MapPin,
    Upload,
    Trash2
} from 'lucide-react';
import { rapportService, type CreateRapportData, type TypeRapport, type Observation } from '../../services/rapportService';
import LoadingSpinner from '../common/LoadingSpinner';

interface RapportFormProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
    interventionId: number;
    partiesInterventions: any[];
}

const RapportForm: React.FC<RapportFormProps> = ({
    isOpen,
    onClose,
    onSuccess,
    interventionId,
    partiesInterventions
}) => {
    const [formData, setFormData] = useState<CreateRapportData>({
        intervention_id: interventionId,
        type_rapport_id: 0,
        date_emission: new Date().toISOString().split('T')[0],
        equipements_selection: [],
        partie_ids: [],
        observations: []
    });
    const [typesRapports, setTypesRapports] = useState<TypeRapport[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [files, setFiles] = useState<File[]>([]);

    useEffect(() => {
        if (isOpen) {
            fetchTypesRapports();
        }
    }, [isOpen]);

    const fetchTypesRapports = async () => {
        try {
            const types = await rapportService.getTypesRapports();
            setTypesRapports(types);
        } catch (error) {
            console.error('Erreur lors de la récupération des types de rapports:', error);
            setError('Erreur lors du chargement des types de rapports');
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!formData.type_rapport_id || formData.partie_ids.length === 0) {
            setError('Veuillez sélectionner un type de rapport et au moins une partie');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            // Créer le rapport
            const newRapport = await rapportService.create(formData);
            
            // Uploader les fichiers s'il y en a
            if (files.length > 0) {
                for (const file of files) {
                    await rapportService.uploadFile(newRapport.id, file);
                }
            }

            onSuccess();
            onClose();
            resetForm();
        } catch (error) {
            console.error('Erreur lors de la création du rapport:', error);
            setError('Erreur lors de la création du rapport');
        } finally {
            setLoading(false);
        }
    };

    const resetForm = () => {
        setFormData({
            intervention_id: interventionId,
            type_rapport_id: 0,
            date_emission: new Date().toISOString().split('T')[0],
            equipements_selection: [],
            partie_ids: [],
            observations: []
        });
        setFiles([]);
        setError(null);
    };

    const addObservation = () => {
        const newObservation: Omit<Observation, 'id'> = {
            identification: '',
            libelle: '',
            localisation: '',
            priorite: 'normal',
            statut_traitement: 'nouveau',
            deja_signalee: false,
            partie_ids: []
        };
        setFormData(prev => ({
            ...prev,
            observations: [...prev.observations, newObservation]
        }));
    };

    const removeObservation = (index: number) => {
        setFormData(prev => ({
            ...prev,
            observations: prev.observations.filter((_, i) => i !== index)
        }));
    };

    const updateObservation = (index: number, field: keyof Observation, value: any) => {
        setFormData(prev => ({
            ...prev,
            observations: prev.observations.map((obs, i) => 
                i === index ? { ...obs, [field]: value } : obs
            )
        }));
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setFiles(Array.from(e.target.files));
        }
    };

    const removeFile = (index: number) => {
        setFiles(prev => prev.filter((_, i) => i !== index));
    };

    const handlePartieChange = (partieId: number, checked: boolean) => {
        setFormData(prev => ({
            ...prev,
            partie_ids: checked
                ? [...prev.partie_ids, partieId]
                : prev.partie_ids.filter(id => id !== partieId)
        }));
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div className="flex justify-between items-center p-6 border-b">
                    <h2 className="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <FileText className="text-blue-600" size={24} />
                        Nouveau Rapport
                    </h2>
                    <button
                        onClick={onClose}
                        className="text-gray-400 hover:text-gray-600"
                    >
                        <X size={24} />
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="p-6 space-y-6">
                    {error && (
                        <div className="bg-red-50 border border-red-200 rounded-md p-4">
                            <div className="flex items-center gap-2 text-red-700">
                                <AlertCircle size={20} />
                                <span>{error}</span>
                            </div>
                        </div>
                    )}

                    {/* Informations de base */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Type de rapport *
                            </label>
                            <select
                                value={formData.type_rapport_id}
                                onChange={(e) => setFormData(prev => ({ ...prev, type_rapport_id: parseInt(e.target.value) }))}
                                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value={0}>Sélectionnez un type</option>
                                {typesRapports.map(type => (
                                    <option key={type.id} value={type.id}>
                                        {type.libelle}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Date d'émission *
                            </label>
                            <input
                                type="date"
                                value={formData.date_emission}
                                onChange={(e) => setFormData(prev => ({ ...prev, date_emission: e.target.value }))}
                                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                    </div>

                    {/* Parties concernées */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Parties concernées *
                        </label>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2">
                            {partiesInterventions.map(partie => (
                                <label key={partie.id} className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        checked={formData.partie_ids.includes(partie.id)}
                                        onChange={(e) => handlePartieChange(partie.id, e.target.checked)}
                                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span className="text-sm text-gray-700">{partie.nom}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    {/* Observations */}
                    <div>
                        <div className="flex justify-between items-center mb-2">
                            <label className="block text-sm font-medium text-gray-700">
                                Observations
                            </label>
                            <button
                                type="button"
                                onClick={addObservation}
                                className="flex items-center gap-1 text-blue-600 hover:text-blue-700 text-sm"
                            >
                                <Plus size={16} />
                                Ajouter une observation
                            </button>
                        </div>

                        {formData.observations.map((observation, index) => (
                            <div key={index} className="border border-gray-200 rounded-md p-4 mb-4">
                                <div className="flex justify-between items-start mb-3">
                                    <h4 className="font-medium text-gray-900">Observation {index + 1}</h4>
                                    <button
                                        type="button"
                                        onClick={() => removeObservation(index)}
                                        className="text-red-600 hover:text-red-700"
                                    >
                                        <Trash2 size={16} />
                                    </button>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Identification
                                        </label>
                                        <input
                                            type="text"
                                            value={observation.identification}
                                            onChange={(e) => updateObservation(index, 'identification', e.target.value)}
                                            className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ex: OBS-001"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Priorité
                                        </label>
                                        <select
                                            value={observation.priorite}
                                            onChange={(e) => updateObservation(index, 'priorite', e.target.value)}
                                            className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="faible">Faible</option>
                                            <option value="normal">Normal</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Libellé
                                        </label>
                                        <input
                                            type="text"
                                            value={observation.libelle}
                                            onChange={(e) => updateObservation(index, 'libelle', e.target.value)}
                                            className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Description de l'observation"
                                        />
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Localisation
                                        </label>
                                        <input
                                            type="text"
                                            value={observation.localisation}
                                            onChange={(e) => updateObservation(index, 'localisation', e.target.value)}
                                            className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ex: Couloir niveau 1"
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Fichiers */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Fichiers
                        </label>
                        <div className="border-2 border-dashed border-gray-300 rounded-md p-4">
                            <input
                                type="file"
                                multiple
                                onChange={handleFileChange}
                                className="w-full"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                            />
                            <p className="text-sm text-gray-500 mt-1">
                                Formats acceptés: JPG, PNG, PDF, DOC, DOCX (max 5MB par fichier)
                            </p>
                        </div>

                        {files.length > 0 && (
                            <div className="mt-2 space-y-2">
                                {files.map((file, index) => (
                                    <div key={index} className="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <span className="text-sm text-gray-700">{file.name}</span>
                                        <button
                                            type="button"
                                            onClick={() => removeFile(index)}
                                            className="text-red-600 hover:text-red-700"
                                        >
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Boutons */}
                    <div className="flex justify-end space-x-3 pt-4 border-t">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            disabled={loading}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                        >
                            {loading ? <LoadingSpinner /> : <Save size={16} />}
                            {loading ? 'Création...' : 'Créer le rapport'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default RapportForm; 