import React, { useState, useEffect } from 'react';
import { siteService } from '../../services/siteService';
import { handleApiError } from '../../utils/api';
import type { Site, SiteFormData } from '../../types';
import { Save, X } from 'lucide-react';

interface SiteFormProps {
    site?: Site;
    onSave: (site: Site) => void;
    onCancel: () => void;
}

const SiteForm: React.FC<SiteFormProps> = ({ site, onSave, onCancel }) => {
    const [formData, setFormData] = useState<SiteFormData>({
        nom: '',
        adresse: '',
        code_postal: '',
        ville: '',
        pays: 'France',
        description: ''
    });
    const [errors, setErrors] = useState<string>('');
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        if (site) {
            setFormData({
                nom: site.nom,
                adresse: site.adresse,
                code_postal: site.code_postal,
                ville: site.ville,
                pays: site.pays,
                description: site.description || ''
            });
        }
    }, [site]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
        // Réinitialiser les erreurs quand l'utilisateur tape
        if (errors) setErrors('');
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setErrors('');

        try {
            let response;
            if (site) {
                // Modification
                response = await siteService.update(site.id, formData);
            } else {
                // Création
                response = await siteService.create(formData);
            }

            onSave(response.site);
        } catch (error) {
            setErrors(handleApiError(error));
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {errors && (
                <div className="bg-red-50 border border-red-200 rounded-md p-4">
                    <div className="text-red-700 text-sm">{errors}</div>
                </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label htmlFor="nom" className="block text-sm font-medium text-gray-700">
                        Nom du site *
                    </label>
                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        required
                        value={formData.nom}
                        onChange={handleChange}
                        className="form-input mt-1"
                        placeholder="Ex: Siège social"
                    />
                </div>

                <div>
                    <label htmlFor="adresse" className="block text-sm font-medium text-gray-700">
                        Adresse *
                    </label>
                    <input
                        type="text"
                        id="adresse"
                        name="adresse"
                        required
                        value={formData.adresse}
                        onChange={handleChange}
                        className="form-input mt-1"
                        placeholder="123 rue de la Paix"
                    />
                </div>

                <div>
                    <label htmlFor="code_postal" className="block text-sm font-medium text-gray-700">
                        Code postal *
                    </label>
                    <input
                        type="text"
                        id="code_postal"
                        name="code_postal"
                        required
                        value={formData.code_postal}
                        onChange={handleChange}
                        className="form-input mt-1"
                        placeholder="75001"
                    />
                </div>

                <div>
                    <label htmlFor="ville" className="block text-sm font-medium text-gray-700">
                        Ville *
                    </label>
                    <input
                        type="text"
                        id="ville"
                        name="ville"
                        required
                        value={formData.ville}
                        onChange={handleChange}
                        className="form-input mt-1"
                        placeholder="Paris"
                    />
                </div>

                <div>
                    <label htmlFor="pays" className="block text-sm font-medium text-gray-700">
                        Pays
                    </label>
                    <input
                        type="text"
                        id="pays"
                        name="pays"
                        value={formData.pays}
                        onChange={handleChange}
                        className="form-input mt-1"
                        placeholder="France"
                    />
                </div>
            </div>

            <div>
                <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows={3}
                    value={formData.description}
                    onChange={handleChange}
                    className="form-input mt-1"
                    placeholder="Description du site..."
                />
            </div>

            <div className="flex justify-end space-x-3 pt-4">
                <button
                    type="button"
                    onClick={onCancel}
                    className="btn btn-secondary"
                >
                    <X className="h-4 w-4 mr-2" />
                    Annuler
                </button>
                <button
                    type="submit"
                    disabled={isLoading}
                    className="btn btn-primary"
                >
                    {isLoading ? (
                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    ) : (
                        <Save className="h-4 w-4 mr-2" />
                    )}
                    {site ? 'Modifier' : 'Créer'}
                </button>
            </div>
        </form>
    );
};

export default SiteForm; 