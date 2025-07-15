import React, { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, UserCog, AlertCircle } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { userService } from '../services/userService';
import type { User } from '../types';
import type { CreateUserData } from '../services/userService';

const UserManagement: React.FC = () => {
    const { user: currentUser, isLoading: authLoading } = useAuth();
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [creating, setCreating] = useState(false);
    const [success, setSuccess] = useState<string | null>(null);

    // Formulaire de création
    const [formData, setFormData] = useState<CreateUserData>({
        nom: '',
        prenom: '',
        email: '',
        organisation: '',
        role: 'user-entreprise'
    });

    // Vérifier les droits d'accès
    useEffect(() => {
        // Attendre que le contexte d'authentification soit chargé
        if (authLoading) return;

        // Vérifier si l'utilisateur est connecté et est super-admin
        if (!currentUser) {
            setError('Vous devez être connecté pour accéder à cette page.');
            setLoading(false);
            return;
        }

        if (currentUser.role !== 'super-admin') {
            setError('Accès interdit. Seuls les super-admins peuvent accéder à cette page.');
            setLoading(false);
            return;
        }

        // Si tout est ok, charger les utilisateurs
        loadUsers();
    }, [currentUser, authLoading]); // Dépendre de currentUser et authLoading

    const loadUsers = async () => {
        try {
            setLoading(true);
            setError(null);

            console.log('🔄 Chargement des utilisateurs...');
            const usersData = await userService.getAll();
            console.log('✅ Utilisateurs chargés:', usersData.length, 'utilisateurs');
            console.log('📋 Données utilisateurs:', usersData);

            setUsers(usersData);
        } catch (err: any) {
            console.error('❌ Erreur chargement utilisateurs:', err);
            console.error('📋 Détails erreur loadUsers:', {
                status: err.response?.status,
                data: err.response?.data,
                message: err.message
            });

            // Ne pas afficher l'erreur si c'est un 401/403 (problème d'auth)
            if (err.response?.status === 401 || err.response?.status === 403) {
                console.log('⚠️ Erreur d\'authentification, laisser le contexte gérer');
                // L'intercepteur s'occupera de la redirection
                return;
            }
            const errorMessage = err.response?.data?.message ||
                err.message ||
                'Erreur lors du chargement des utilisateurs';
            setError(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateUser = async (e: React.FormEvent) => {
        e.preventDefault();

        console.log('🚀 Début création utilisateur:', formData);

        try {
            setCreating(true);
            setError(null);

            console.log('📤 Envoi requête création...');
            const response = await userService.createUser(formData);
            console.log('✅ Réponse création reçue:', response);

            // Réinitialiser le formulaire
            setFormData({
                nom: '',
                prenom: '',
                email: '',
                organisation: '',
                role: 'user-entreprise'
            });

            // Fermer le formulaire
            setShowCreateForm(false);

            // Afficher le message de succès avec plus d'informations
            let successMessage = response.message;
            if (response.temporary_password) {
                successMessage += ` Mot de passe temporaire: ${response.temporary_password}`;
            }
            setSuccess(successMessage);

            // Recharger la liste des utilisateurs
            console.log('🔄 Rechargement liste utilisateurs...');
            try {
                await loadUsers();
                console.log('✅ Liste utilisateurs rechargée avec succès');
            } catch (loadError) {
                console.error('❌ Erreur lors du rechargement:', loadError);
                // Ne pas faire échouer la création si le rechargement échoue
            }

            // Masquer le message de succès après 5 secondes
            setTimeout(() => setSuccess(null), 5000);

        } catch (err: any) {
            console.error('❌ Erreur création utilisateur:', err);
            console.error('📋 Détails erreur:', {
                status: err.response?.status,
                data: err.response?.data,
                message: err.message
            });

            let errorMessage = 'Erreur lors de la création de l\'utilisateur';

            if (err.response?.status === 422) {
                // Erreur de validation
                if (err.response.data?.errors) {
                    const errors = Object.values(err.response.data.errors).flat();
                    errorMessage = errors.join(' ');
                } else if (err.response.data?.message) {
                    errorMessage = err.response.data.message;
                }
            } else if (err.response?.data?.message) {
                errorMessage = err.response.data.message;
            } else if (err.message) {
                errorMessage = err.message;
            }

            setError(errorMessage);
            // Ne pas fermer le formulaire en cas d'erreur
        } finally {
            setCreating(false);
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const getRoleDisplayName = (role: string) => {
        switch (role) {
            case 'super-admin':
                return 'Super Administrateur';
            case 'admin':
                return 'Administrateur';
            case 'client-admin':
                return 'Admin Client';
            case 'user':
                return 'Utilisateur';
            case 'viewer':
                return 'Lecteur';
            case 'user-entreprise':
                return 'Utilisateur Entreprise';
            case 'user-intervenant':
                return 'Utilisateur Intervenant';
            default:
                return role;
        }
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role) {
            case 'super-admin':
                return 'bg-red-100 text-red-800';
            case 'admin':
                return 'bg-orange-100 text-orange-800';
            case 'client-admin':
                return 'bg-yellow-100 text-yellow-800';
            case 'user-entreprise':
                return 'bg-blue-100 text-blue-800';
            case 'user-intervenant':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    if (authLoading || loading) {
        return (
            <div className="flex items-center justify-center h-96">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (error && (!currentUser || currentUser.role !== 'super-admin')) {
        return (
            <div className="flex items-center justify-center h-96">
                <div className="text-center">
                    <AlertCircle className="mx-auto h-16 w-16 text-red-500 mb-4" />
                    <h2 className="text-xl font-semibold text-gray-900 mb-2">Accès interdit</h2>
                    <p className="text-gray-600">{error}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="p-6">
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <UserCog className="text-blue-600" />
                            Gestion des utilisateurs
                        </h1>
                        <p className="text-gray-600 mt-1">Gérez les comptes utilisateurs de la plateforme</p>
                    </div>
                    <button
                        onClick={() => {
                            setShowCreateForm(true);
                            setError(null);
                        }}
                        className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                    >
                        <Plus size={20} />
                        Créer un utilisateur
                    </button>
                </div>
            </div>

            {/* Messages de succès et d'erreur */}
            {success && (
                <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <div className="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                                <span className="text-white text-xs">✓</span>
                            </div>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm font-medium text-green-800">{success}</p>
                        </div>
                    </div>
                </div>
            )}

            {error && (
                <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div className="flex items-center">
                        <AlertCircle className="text-red-500 mr-2" size={20} />
                        <p className="text-sm font-medium text-red-800">{error}</p>
                    </div>
                </div>
            )}

            {/* Tableau des utilisateurs */}
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Utilisateur
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rôle
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Organisation
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {users.map((user) => (
                                <tr key={user.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0 h-10 w-10">
                                                <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span className="text-blue-600 font-medium">
                                                        {user.prenom[0]}{user.nom[0]}
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="ml-4">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {user.prenom} {user.nom}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ID: {user.id}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {user.email}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getRoleBadgeColor(user.role)}`}>
                                            {getRoleDisplayName(user.role)}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {user.organisation}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div className="flex items-center gap-2">
                                            <button
                                                className="text-blue-600 hover:text-blue-900 p-1 rounded transition-colors"
                                                title="Modifier"
                                            >
                                                <Edit2 size={16} />
                                            </button>
                                            <button
                                                className="text-red-600 hover:text-red-900 p-1 rounded transition-colors"
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

            {/* Formulaire de création d'utilisateur */}
            {showCreateForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-xl font-bold">Créer un nouvel utilisateur</h2>
                            <button
                                type="button"
                                onClick={() => {
                                    setShowCreateForm(false);
                                    setError(null);
                                }}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Erreur dans le formulaire */}
                        {error && (
                            <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                <p className="text-sm text-red-600">{error}</p>
                            </div>
                        )}

                        <form onSubmit={handleCreateUser} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Nom
                                </label>
                                <input
                                    type="text"
                                    name="nom"
                                    value={formData.nom}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Prénom
                                </label>
                                <input
                                    type="text"
                                    name="prenom"
                                    value={formData.prenom}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    value={formData.email}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Organisation
                                </label>
                                <input
                                    type="text"
                                    name="organisation"
                                    value={formData.organisation}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Rôle
                                </label>
                                <select
                                    name="role"
                                    value={formData.role}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="user-entreprise">Utilisateur Entreprise</option>
                                    <option value="user-intervenant">Utilisateur Intervenant</option>
                                </select>
                            </div>

                            <div className="flex justify-end gap-3 mt-6">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowCreateForm(false);
                                        setError(null);
                                    }}
                                    className="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="submit"
                                    disabled={creating}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    {creating ? 'Création...' : 'Créer'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default UserManagement; 