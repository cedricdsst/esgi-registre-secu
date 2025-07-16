// Service pour l'API externe des équipements
import axios from 'axios';

const EQUIPMENT_API_BASE_URL = 'http://127.0.0.1:8000/api';

// Types pour l'API des équipements
export interface Domain {
    id: number;
    name: string;
    serial_number: string;
    created_at: string;
    updated_at: string;
}

export interface Family {
    id: number;
    name: string;
    domain_id: number;
    serial_number: string;
    created_at: string;
    updated_at: string;
    domain?: Domain;
}

export interface EquipmentType {
    id: number;
    title: string;
    subtitle: string;
    family_id: number;
    inventory_required: boolean;
    additional_fields: string | null;
    serial_number: string;
    created_at: string;
    updated_at: string;
    family?: Family;
}

export interface Brand {
    id: number;
    name: string;
    serial_number: string;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    brand_id: number;
    equipment_type_id: number;
    serial_number: string;
    created_at: string;
    updated_at: string;
    brand?: Brand;
    equipment_type?: EquipmentType;
}

export interface DocumentType {
    id: number;
    name: string;
    serial_number: string;
    created_at: string;
    updated_at: string;
}

export interface Document {
    id: number;
    name: string;
    document_type_id: number;
    file_path: string;
    issue_date: string;
    expiry_date: string;
    version: string;
    reference: string;
    archived: boolean;
    serial_number: string;
    created_at: string;
    updated_at: string;
    document_type?: DocumentType;
    products?: Product[];
}

export interface Inventory {
    id: number;
    product_id: number;
    location: string;
    brand_id: number;
    commissioning_date: string;
    additional_fields: string;
    serial_number: string;
    created_at: string;
    updated_at: string;
    product?: Product;
    brand?: Brand;
}

// Interface générique pour les réponses de l'API
interface ApiResponse<T> {
    success: boolean;
    data: T;
}

// Configuration axios pour l'API des équipements
const equipmentApi = axios.create({
    baseURL: EQUIPMENT_API_BASE_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
    }
});

export const equipementService = {
    // Domaines
    getDomains: async (): Promise<Domain[]> => {
        const response = await equipmentApi.get<ApiResponse<Domain[]>>('/domains');
        return response.data.data;
    },

    getDomain: async (id: number): Promise<Domain> => {
        const response = await equipmentApi.get<ApiResponse<Domain>>(`/domains/${id}`);
        return response.data.data;
    },

    // Familles
    getFamilies: async (): Promise<Family[]> => {
        const response = await equipmentApi.get<ApiResponse<Family[]>>('/families');
        return response.data.data;
    },

    getFamily: async (id: number): Promise<Family> => {
        const response = await equipmentApi.get<ApiResponse<Family>>(`/families/${id}`);
        return response.data.data;
    },

    getFamiliesByDomain: async (domainId: number): Promise<Family[]> => {
        const response = await equipmentApi.get<ApiResponse<Family[]>>(`/domains/${domainId}/families`);
        return response.data.data;
    },

    // Types d'équipements
    getEquipmentTypes: async (): Promise<EquipmentType[]> => {
        const response = await equipmentApi.get<ApiResponse<EquipmentType[]>>('/equipment-types');
        return response.data.data;
    },

    getEquipmentType: async (id: number): Promise<EquipmentType> => {
        const response = await equipmentApi.get<ApiResponse<EquipmentType>>(`/equipment-types/${id}`);
        return response.data.data;
    },

    getEquipmentTypesByFamily: async (familyId: number): Promise<EquipmentType[]> => {
        const response = await equipmentApi.get<ApiResponse<EquipmentType[]>>(`/families/${familyId}/equipment-types`);
        return response.data.data;
    },

    // Marques
    getBrands: async (): Promise<Brand[]> => {
        const response = await equipmentApi.get<ApiResponse<Brand[]>>('/brands');
        return response.data.data;
    },

    getBrand: async (id: number): Promise<Brand> => {
        const response = await equipmentApi.get<ApiResponse<Brand>>(`/brands/${id}`);
        return response.data.data;
    },

    // Produits
    getProducts: async (): Promise<Product[]> => {
        const response = await equipmentApi.get<ApiResponse<Product[]>>('/products');
        return response.data.data;
    },

    getProduct: async (id: number): Promise<Product> => {
        const response = await equipmentApi.get<ApiResponse<Product>>(`/products/${id}`);
        return response.data.data;
    },

    getProductsByBrand: async (brandId: number): Promise<Product[]> => {
        const response = await equipmentApi.get<ApiResponse<Product[]>>(`/brands/${brandId}/products`);
        return response.data.data;
    },

    getProductsByEquipmentType: async (equipmentTypeId: number): Promise<Product[]> => {
        const response = await equipmentApi.get<ApiResponse<Product[]>>(`/equipment-types/${equipmentTypeId}/products`);
        return response.data.data;
    },

    // Documents
    getDocuments: async (): Promise<Document[]> => {
        const response = await equipmentApi.get<ApiResponse<Document[]>>('/documents');
        return response.data.data;
    },

    getDocument: async (id: number): Promise<Document> => {
        const response = await equipmentApi.get<ApiResponse<Document>>(`/documents/${id}`);
        return response.data.data;
    },

    downloadDocument: async (id: number): Promise<Blob> => {
        const response = await equipmentApi.get(`/documents/${id}/download`, {
            responseType: 'blob'
        });
        return response.data;
    },

    // Inventaire
    getInventories: async (): Promise<Inventory[]> => {
        const response = await equipmentApi.get<ApiResponse<Inventory[]>>('/inventories');
        return response.data.data;
    },

    getInventory: async (id: number): Promise<Inventory> => {
        const response = await equipmentApi.get<ApiResponse<Inventory>>(`/inventories/${id}`);
        return response.data.data;
    },

    getInventoriesByProduct: async (productId: number): Promise<Inventory[]> => {
        const response = await equipmentApi.get<ApiResponse<Inventory[]>>(`/products/${productId}/inventories`);
        return response.data.data;
    },

    // Méthodes utilitaires pour les rapports
    getEquipmentsByIds: async (ids: number[]): Promise<Product[]> => {
        if (ids.length === 0) return [];
        
        const promises = ids.map(id => equipementService.getProduct(id));
        return Promise.all(promises);
    },

    // Méthode pour récupérer tous les équipements organisés par catégories
    getEquipmentsByCategories: async () => {
        const domains = await equipementService.getDomains();
        const result: Array<Domain & { families: Array<Family & { equipmentTypes: Array<EquipmentType & { products: Product[] }> }> }> = [];

        for (const domain of domains) {
            const families = await equipementService.getFamiliesByDomain(domain.id);
            const domainData = {
                ...domain,
                families: [] as Array<Family & { equipmentTypes: Array<EquipmentType & { products: Product[] }> }>
            };

            for (const family of families) {
                const equipmentTypes = await equipementService.getEquipmentTypesByFamily(family.id);
                const familyData = {
                    ...family,
                    equipmentTypes: [] as Array<EquipmentType & { products: Product[] }>
                };

                for (const equipmentType of equipmentTypes) {
                    const products = await equipementService.getProductsByEquipmentType(equipmentType.id);
                    familyData.equipmentTypes.push({
                        ...equipmentType,
                        products
                    });
                }

                domainData.families.push(familyData);
            }

            result.push(domainData);
        }

        return result;
    }
}; 