import React, { useState, useEffect } from 'react';
import { 
    equipementService, 
    type Domain, 
    type Family, 
    type EquipmentType, 
    type Product 
} from '../../services/equipementService';
import LoadingSpinner from '../common/LoadingSpinner';

interface EquipmentSelectorProps {
    selectedEquipments: number[];
    onSelectionChange: (selectedIds: number[]) => void;
}

type DomainWithChildren = Domain & { 
    families: Array<Family & { 
        equipmentTypes: Array<EquipmentType & { 
            products: Product[] 
        }> 
    }> 
};

export const EquipmentSelector: React.FC<EquipmentSelectorProps> = ({
    selectedEquipments,
    onSelectionChange
}) => {
    const [domains, setDomains] = useState<DomainWithChildren[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [expandedDomains, setExpandedDomains] = useState<Set<number>>(new Set());
    const [expandedFamilies, setExpandedFamilies] = useState<Set<number>>(new Set());
    const [expandedEquipmentTypes, setExpandedEquipmentTypes] = useState<Set<number>>(new Set());

    useEffect(() => {
        loadEquipments();
    }, []);

    const loadEquipments = async () => {
        try {
            setLoading(true);
            setError(null);
            const equipmentData = await equipementService.getEquipmentsByCategories();
            setDomains(equipmentData);
        } catch (err) {
            console.error('Erreur lors du chargement des équipements:', err);
            setError('Erreur lors du chargement des équipements');
        } finally {
            setLoading(false);
        }
    };

    const toggleExpanded = (type: 'domain' | 'family' | 'equipmentType', id: number) => {
        const setFunction = type === 'domain' ? setExpandedDomains :
                          type === 'family' ? setExpandedFamilies :
                          setExpandedEquipmentTypes;
        
        setFunction(prev => {
            const newSet = new Set(prev);
            if (newSet.has(id)) {
                newSet.delete(id);
            } else {
                newSet.add(id);
            }
            return newSet;
        });
    };

    const handleProductToggle = (productId: number) => {
        const newSelection = selectedEquipments.includes(productId)
            ? selectedEquipments.filter(id => id !== productId)
            : [...selectedEquipments, productId];
        
        onSelectionChange(newSelection);
    };

    const selectAllInCategory = (products: Product[]) => {
        const productIds = products.map(p => p.id);
        const allSelected = productIds.every(id => selectedEquipments.includes(id));
        
        if (allSelected) {
            // Désélectionner tous les produits de cette catégorie
            const newSelection = selectedEquipments.filter(id => !productIds.includes(id));
            onSelectionChange(newSelection);
        } else {
            // Sélectionner tous les produits de cette catégorie
            const newSelection = [...new Set([...selectedEquipments, ...productIds])];
            onSelectionChange(newSelection);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center py-8">
                <LoadingSpinner />
                <span className="ml-2">Chargement des équipements...</span>
            </div>
        );
    }

    if (error) {
        return (
            <div className="bg-red-50 border border-red-200 rounded-md p-4">
                <div className="flex">
                    <div className="text-red-600">
                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                        </svg>
                    </div>
                    <div className="ml-3">
                        <p className="text-sm text-red-800">{error}</p>
                        <button
                            onClick={loadEquipments}
                            className="mt-2 text-sm text-red-600 hover:text-red-500 underline"
                        >
                            Réessayer
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <h3 className="text-lg font-medium text-gray-900">Équipements techniques</h3>
                <span className="text-sm text-gray-500">
                    {selectedEquipments.length} équipement(s) sélectionné(s)
                </span>
            </div>

            <div className="border border-gray-200 rounded-md max-h-96 overflow-y-auto">
                {domains.map(domain => (
                    <div key={domain.id} className="border-b border-gray-100 last:border-b-0">
                        <div
                            className="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 cursor-pointer"
                            onClick={() => toggleExpanded('domain', domain.id)}
                        >
                            <div className="flex items-center">
                                <svg
                                    className={`w-4 h-4 mr-2 transition-transform ${
                                        expandedDomains.has(domain.id) ? 'rotate-90' : ''
                                    }`}
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                </svg>
                                <span className="font-medium text-gray-900">{domain.name}</span>
                            </div>
                            <span className="text-sm text-gray-500">{domain.serial_number}</span>
                        </div>

                        {expandedDomains.has(domain.id) && (
                            <div className="pl-4">
                                {domain.families.map(family => (
                                    <div key={family.id} className="border-b border-gray-100 last:border-b-0">
                                        <div
                                            className="flex items-center justify-between p-3 hover:bg-gray-50 cursor-pointer"
                                            onClick={() => toggleExpanded('family', family.id)}
                                        >
                                            <div className="flex items-center">
                                                <svg
                                                    className={`w-4 h-4 mr-2 transition-transform ${
                                                        expandedFamilies.has(family.id) ? 'rotate-90' : ''
                                                    }`}
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                                </svg>
                                                <span className="text-gray-800">{family.name}</span>
                                            </div>
                                            <span className="text-sm text-gray-500">{family.serial_number}</span>
                                        </div>

                                        {expandedFamilies.has(family.id) && (
                                            <div className="pl-4">
                                                {family.equipmentTypes.map(equipmentType => (
                                                    <div key={equipmentType.id} className="border-b border-gray-100 last:border-b-0">
                                                        <div
                                                            className="flex items-center justify-between p-3 hover:bg-gray-50 cursor-pointer"
                                                            onClick={() => toggleExpanded('equipmentType', equipmentType.id)}
                                                        >
                                                            <div className="flex items-center">
                                                                <svg
                                                                    className={`w-4 h-4 mr-2 transition-transform ${
                                                                        expandedEquipmentTypes.has(equipmentType.id) ? 'rotate-90' : ''
                                                                    }`}
                                                                    fill="currentColor"
                                                                    viewBox="0 0 20 20"
                                                                >
                                                                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                                                </svg>
                                                                <div>
                                                                    <div className="text-gray-700">{equipmentType.title}</div>
                                                                    <div className="text-sm text-gray-500">{equipmentType.subtitle}</div>
                                                                </div>
                                                            </div>
                                                            <div className="flex items-center space-x-2">
                                                                <button
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        selectAllInCategory(equipmentType.products);
                                                                    }}
                                                                    className="text-xs text-blue-600 hover:text-blue-500"
                                                                >
                                                                    Tout sélectionner
                                                                </button>
                                                                <span className="text-sm text-gray-500">
                                                                    {equipmentType.products.length} produit(s)
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {expandedEquipmentTypes.has(equipmentType.id) && (
                                                            <div className="pl-4 bg-gray-50">
                                                                {equipmentType.products.map(product => (
                                                                    <div key={product.id} className="flex items-center p-2 hover:bg-gray-100">
                                                                        <input
                                                                            type="checkbox"
                                                                            id={`product-${product.id}`}
                                                                            checked={selectedEquipments.includes(product.id)}
                                                                            onChange={() => handleProductToggle(product.id)}
                                                                            className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                                        />
                                                                        <label
                                                                            htmlFor={`product-${product.id}`}
                                                                            className="ml-2 block text-sm text-gray-900 cursor-pointer"
                                                                        >
                                                                            <div>{product.name}</div>
                                                                            <div className="text-xs text-gray-500">
                                                                                {product.brand?.name} • {product.serial_number}
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {domains.length === 0 && (
                <div className="text-center py-8 text-gray-500">
                    Aucun équipement disponible
                </div>
            )}
        </div>
    );
}; 