import React, { useState, useEffect } from 'react';
import { equipementService, type Product } from '../../services/equipementService';
import LoadingSpinner from '../common/LoadingSpinner';
import { Package, AlertCircle, ExternalLink } from 'lucide-react';

interface EquipmentDisplayProps {
    equipmentIds: number[];
    title?: string;
    className?: string;
}

export const EquipmentDisplay: React.FC<EquipmentDisplayProps> = ({
    equipmentIds,
    title = "Équipements techniques",
    className = ""
}) => {
    const [equipments, setEquipments] = useState<Product[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (equipmentIds.length > 0) {
            loadEquipments();
        } else {
            setLoading(false);
        }
    }, [equipmentIds]);

    const loadEquipments = async () => {
        try {
            setLoading(true);
            setError(null);
            const equipmentData = await equipementService.getEquipmentsByIds(equipmentIds);
            setEquipments(equipmentData);
        } catch (err) {
            console.error('Erreur lors du chargement des équipements:', err);
            setError('Erreur lors du chargement des équipements');
        } finally {
            setLoading(false);
        }
    };

    if (equipmentIds.length === 0) {
        return (
            <div className={`bg-gray-50 rounded-lg p-4 ${className}`}>
                <h3 className="text-lg font-medium text-gray-900 mb-2 flex items-center gap-2">
                    <Package size={20} />
                    {title}
                </h3>
                <p className="text-gray-500 text-sm">Aucun équipement sélectionné</p>
            </div>
        );
    }

    if (loading) {
        return (
            <div className={`bg-gray-50 rounded-lg p-4 ${className}`}>
                <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                    <Package size={20} />
                    {title}
                </h3>
                <div className="flex items-center justify-center py-8">
                    <LoadingSpinner />
                    <span className="ml-2">Chargement des équipements...</span>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className={`bg-red-50 rounded-lg p-4 ${className}`}>
                <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                    <Package size={20} />
                    {title}
                </h3>
                <div className="flex items-center gap-2 text-red-700">
                    <AlertCircle size={20} />
                    <span>{error}</span>
                </div>
                <button
                    onClick={loadEquipments}
                    className="mt-2 text-sm text-red-600 hover:text-red-500 underline"
                >
                    Réessayer
                </button>
            </div>
        );
    }

    return (
        <div className={`bg-gray-50 rounded-lg p-4 ${className}`}>
            <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                <Package size={20} />
                {title}
                <span className="text-sm text-gray-500 font-normal">
                    ({equipments.length} équipement{equipments.length > 1 ? 's' : ''})
                </span>
            </h3>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {equipments.map(equipment => (
                    <div key={equipment.id} className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div className="flex items-start justify-between mb-3">
                            <div className="flex-1">
                                <h4 className="font-medium text-gray-900 mb-1">{equipment.name}</h4>
                                <p className="text-sm text-gray-600">
                                    {equipment.equipment_type?.title} • {equipment.equipment_type?.subtitle}
                                </p>
                            </div>
                            <div className="flex items-center gap-1 text-xs text-gray-500">
                                <ExternalLink size={12} />
                                <span>API</span>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-500">Marque:</span>
                                <span className="text-gray-900">{equipment.brand?.name || 'Non spécifiée'}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-500">Numéro de série:</span>
                                <span className="text-gray-900 font-mono text-xs">{equipment.serial_number}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-500">Famille:</span>
                                <span className="text-gray-900">{equipment.equipment_type?.family?.name || 'Non spécifiée'}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-500">Domaine:</span>
                                <span className="text-gray-900">{equipment.equipment_type?.family?.domain?.name || 'Non spécifié'}</span>
                            </div>
                            
                            {equipment.equipment_type?.inventory_required && (
                                <div className="mt-2 flex items-center gap-1 text-xs text-amber-600 bg-amber-50 rounded px-2 py-1">
                                    <AlertCircle size={12} />
                                    <span>Inventaire requis</span>
                                </div>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {equipments.length === 0 && !loading && (
                <div className="text-center py-8 text-gray-500">
                    <Package size={48} className="mx-auto mb-4 text-gray-300" />
                    <p>Aucun équipement trouvé</p>
                    <p className="text-sm">Les équipements sélectionnés ne sont plus disponibles dans l'API</p>
                </div>
            )}
        </div>
    );
}; 