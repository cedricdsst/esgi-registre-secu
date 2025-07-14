import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { handleApiError } from '../../utils/api';
import { Link } from 'react-router-dom';
import { UserPlus, Eye, EyeOff } from 'lucide-react';

const RegisterForm: React.FC = () => {
    const { register } = useAuth();
    const [formData, setFormData] = useState({
        nom: '',
        prenom: '',
        email: '',
        password: '',
        password_confirmation: '',
        organisation: ''
    });
    const [errors, setErrors] = useState<string>('');
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
        // Réinitialiser les erreurs quand l'utilisateur tape
        if (errors) setErrors('');
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setErrors('');

        // Validation côté client
        if (formData.password !== formData.password_confirmation) {
            setErrors('Les mots de passe ne correspondent pas');
            setIsLoading(false);
            return;
        }

        try {
            await register(formData);
        } catch (error) {
            setErrors(handleApiError(error));
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8">
                <div>
                    <div className="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100">
                        <UserPlus className="h-6 w-6 text-primary-600" />
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Créer votre compte
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        Ou{' '}
                        <Link
                            to="/login"
                            className="font-medium text-primary-600 hover:text-primary-500"
                        >
                            se connecter à un compte existant
                        </Link>
                    </p>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    {errors && (
                        <div className="bg-red-50 border border-red-200 rounded-md p-4">
                            <div className="text-red-700 text-sm">{errors}</div>
                        </div>
                    )}

                    <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="prenom" className="block text-sm font-medium text-gray-700">
                                    Prénom
                                </label>
                                <input
                                    id="prenom"
                                    name="prenom"
                                    type="text"
                                    required
                                    value={formData.prenom}
                                    onChange={handleChange}
                                    className="form-input mt-1"
                                    placeholder="John"
                                />
                            </div>

                            <div>
                                <label htmlFor="nom" className="block text-sm font-medium text-gray-700">
                                    Nom
                                </label>
                                <input
                                    id="nom"
                                    name="nom"
                                    type="text"
                                    required
                                    value={formData.nom}
                                    onChange={handleChange}
                                    className="form-input mt-1"
                                    placeholder="Doe"
                                />
                            </div>
                        </div>

                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                Adresse email
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                value={formData.email}
                                onChange={handleChange}
                                className="form-input mt-1"
                                placeholder="john.doe@example.com"
                            />
                        </div>

                        <div>
                            <label htmlFor="organisation" className="block text-sm font-medium text-gray-700">
                                Organisation
                            </label>
                            <input
                                id="organisation"
                                name="organisation"
                                type="text"
                                required
                                value={formData.organisation}
                                onChange={handleChange}
                                className="form-input mt-1"
                                placeholder="Nom de votre entreprise"
                            />
                        </div>

                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                                Mot de passe
                            </label>
                            <div className="relative mt-1">
                                <input
                                    id="password"
                                    name="password"
                                    type={showPassword ? 'text' : 'password'}
                                    required
                                    value={formData.password}
                                    onChange={handleChange}
                                    className="form-input pr-10"
                                    placeholder="Votre mot de passe"
                                />
                                <button
                                    type="button"
                                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onClick={() => setShowPassword(!showPassword)}
                                >
                                    {showPassword ? (
                                        <EyeOff className="h-5 w-5 text-gray-400" />
                                    ) : (
                                        <Eye className="h-5 w-5 text-gray-400" />
                                    )}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                                Confirmer le mot de passe
                            </label>
                            <div className="relative mt-1">
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type={showConfirmPassword ? 'text' : 'password'}
                                    required
                                    value={formData.password_confirmation}
                                    onChange={handleChange}
                                    className="form-input pr-10"
                                    placeholder="Confirmer votre mot de passe"
                                />
                                <button
                                    type="button"
                                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                >
                                    {showConfirmPassword ? (
                                        <EyeOff className="h-5 w-5 text-gray-400" />
                                    ) : (
                                        <Eye className="h-5 w-5 text-gray-400" />
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={isLoading}
                            className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {isLoading ? (
                                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                            ) : (
                                <>
                                    <UserPlus className="h-5 w-5 mr-2" />
                                    Créer mon compte
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default RegisterForm; 