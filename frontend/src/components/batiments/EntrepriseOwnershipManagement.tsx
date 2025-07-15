import React, { useState, useEffect } from 'react';
import {
    Users,
    Building,
    UserCheck,
    UserX,
    AlertCircle,
    CheckCircle,
    Search,
    User
} from 'lucide-react';
import { partieService } from '../../services/partieService';
import type { User as UserType, Partie } from '../../types';
import LoadingSpinner from '../common/LoadingSpinner';

interface EntrepriseOwnershipManagementProps {
    batimentId: number;
    onPartiesUpdate?: (parties: Partie[]) => void;
}

const EntrepriseOwnershipManagement: React.FC<EntrepriseOwnershipManagementProps> = ({
    batimentId,
    onPartiesUpdate
}) => {
    const [entrepriseUsers, setEntrepriseUsers] = useState<UserType[]>([]);
    const [parties, setParties] = useState<Partie[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    // États pour la sélection
    const [selectedUser, setSelectedUser] = useState<UserType | null>(null);
    const [selectedParties, setSelectedParties] = useState<number[]>([]);
    const [assigning, setAssigning] = useState(false);

    // États pour les filtres
    const [searchUser, setSearchUser] = useState('');
    const [searchPartie, setSearchPartie] = useState('');

    useEffect(() => {
        loadData();
    }, [batimentId]);

    const loadData = async () => {
        try {
            setLoading(true);
            setError(null);

            const [usersData, partiesData] = await Promise.all([
                partieService.getEntrepriseUsers(),
                partieService.getPartiesByBatimentWithOwners(batimentId)
            ]);

            setEntrepriseUsers(usersData);
            setParties(partiesData.parties);

            // Notifier le parent si nécessaire
            if (onPartiesUpdate) {
                onPartiesUpdate(partiesData.parties);
            }
        } catch (err: any) {
            console.error('Erreur lors du chargement des données:', err);
            setError(err.response?.data?.message || 'Erreur lors du chargement des données');
        } finally {
            setLoading(false);
        }
    };

    const handleAssignOwner = async (partieId: number, userId: number | null) => {
        try {
            setAssigning(true);
            const response = await partieService.assignOwner(partieId, userId);

            // Mettre à jour la liste des parties
            setParties(prevParties =>
                prevParties.map(partie =>
                    partie.id === partieId
                        ? { ...partie, owner: userId ? entrepriseUsers.find(u => u.id === userId) : undefined, owner_id: userId }
                        : partie
                )
            );

            setSuccess(response.message);
            setTimeout(() => setSuccess(null), 3000);
        } catch (err: any) {
            console.error('Erreur lors de l\'assignation:', err);
            setError(err.response?.data?.message || 'Erreur lors de l\'assignation');
        } finally {
            setAssigning(false);
        }
    };

    const handleBulkAssign = async () => {
        if (!selectedUser || selectedParties.length === 0) {
            setError('Veuillez sélectionner un utilisateur et au moins une partie');
            return;
        }

        try {
            setAssigning(true);
            const response = await partieService.assignOwnerBulk(selectedUser.id, selectedParties);

            // Mettre à jour la liste des parties
            setParties(prevParties =>
                prevParties.map(partie =>
                    selectedParties.includes(partie.id)
                        ? { ...partie, owner: selectedUser, owner_id: selectedUser.id }
                        : partie
                )
            );

            setSuccess(response.message);
            setSelectedParties([]);
            setSelectedUser(null);
            setTimeout(() => setSuccess(null), 3000);
        } catch (err: any) {
            console.error('Erreur lors de l\'assignation en masse:', err);
            setError(err.response?.data?.message || 'Erreur lors de l\'assignation en masse');
        } finally {
            setAssigning(false);
        }
    };

    const handlePartieToggle = (partieId: number) => {
        setSelectedParties(prev =>
            prev.includes(partieId)
                ? prev.filter(id => id !== partieId)
                : [...prev, partieId]
        );
    };



    const filteredUsers = entrepriseUsers.filter(user =>
        `${user.prenom} ${user.nom}`.toLowerCase().includes(searchUser.toLowerCase()) ||
        user.email.toLowerCase().includes(searchUser.toLowerCase()) ||
        user.organisation.toLowerCase().includes(searchUser.toLowerCase())
    );

    const filteredParties = parties.filter(partie =>
        partie.nom.toLowerCase().includes(searchPartie.toLowerCase())
    );

    const handleSelectAll = () => {
        const allPartieIds = filteredParties.map(partie => partie.id);
        const isAllSelected = allPartieIds.every(id => selectedParties.includes(id));

        if (isAllSelected) {
            // Désélectionner toutes les parties filtrées
            setSelectedParties(prev => prev.filter(id => !allPartieIds.includes(id)));
        } else {
            // Sélectionner toutes les parties filtrées
            setSelectedParties(prev => {
                const newSelection = [...prev];
                allPartieIds.forEach(id => {
                    if (!newSelection.includes(id)) {
                        newSelection.push(id);
                    }
                });
                return newSelection;
            });
        }
    };

    const isAllSelected = filteredParties.length > 0 && filteredParties.every(partie => selectedParties.includes(partie.id));
    const isSomeSelected = filteredParties.some(partie => selectedParties.includes(partie.id)) && !isAllSelected;

    if (loading) {
        return <LoadingSpinner />;
    }

    return (
        <div className="bg-white p-6 rounded-lg shadow-sm">
            <div className="flex items-center gap-3 mb-6">
                <div className="bg-purple-100 p-3 rounded-lg">
                    <Users className="h-6 w-6 text-purple-600" />
                </div>
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">
                        Gestion des Entreprises
                    </h2>
                    <p className="text-gray-600">
                        Associez des utilisateurs entreprise aux parties du bâtiment
                    </p>
                </div>
            </div>

            {/* Messages de succès et d'erreur */}
            {success && (
                <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div className="flex items-center">
                        <CheckCircle className="text-green-500 mr-2" size={20} />
                        <p className="text-sm font-medium text-green-800">{success}</p>
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

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Section des utilisateurs entreprise */}
                <div>
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                        Utilisateurs Entreprise ({filteredUsers.length})
                    </h3>

                    {/* Recherche utilisateurs */}
                    <div className="relative mb-4">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                        <input
                            type="text"
                            placeholder="Rechercher un utilisateur..."
                            value={searchUser}
                            onChange={(e) => setSearchUser(e.target.value)}
                            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                    </div>

                    <div className="space-y-3 max-h-96 overflow-y-auto">
                        {filteredUsers.map((user) => (
                            <div
                                key={user.id}
                                className={`p-4 border rounded-lg cursor-pointer transition-colors ${selectedUser?.id === user.id
                                    ? 'bg-purple-50 border-purple-200'
                                    : 'bg-white border-gray-200 hover:border-purple-300'
                                    }`}
                                onClick={() => setSelectedUser(selectedUser?.id === user.id ? null : user)}
                            >
                                <div className="flex items-center gap-3">
                                    <div className="bg-purple-100 p-2 rounded-full">
                                        <User className="h-4 w-4 text-purple-600" />
                                    </div>
                                    <div className="flex-1">
                                        <div className="font-medium text-gray-900">
                                            {user.prenom} {user.nom}
                                        </div>
                                        <div className="text-sm text-gray-500">{user.email}</div>
                                        <div className="text-sm text-gray-500">{user.organisation}</div>
                                    </div>
                                    {selectedUser?.id === user.id && (
                                        <UserCheck className="h-5 w-5 text-purple-600" />
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Section des parties */}
                <div>
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-medium text-gray-900">
                            Parties du Bâtiment ({filteredParties.length})
                        </h3>
                        {selectedParties.length > 0 && (
                            <button
                                onClick={handleBulkAssign}
                                disabled={!selectedUser || assigning}
                                className="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {assigning ? 'Assignation...' : `Assigner à ${selectedParties.length} partie(s)`}
                            </button>
                        )}
                    </div>

                    {/* Recherche parties */}
                    <div className="relative mb-4">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                        <input
                            type="text"
                            placeholder="Rechercher une partie..."
                            value={searchPartie}
                            onChange={(e) => setSearchPartie(e.target.value)}
                            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                        />
                    </div>

                    {/* Checkbox Sélectionner tout */}
                    {filteredParties.length > 0 && (
                        <div className="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                            <input
                                type="checkbox"
                                checked={isAllSelected}
                                ref={(el) => {
                                    if (el) el.indeterminate = isSomeSelected;
                                }}
                                onChange={handleSelectAll}
                                className="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                            />
                            <label className="text-sm font-medium text-gray-700">
                                Sélectionner tout ({filteredParties.length} parties)
                            </label>
                        </div>
                    )}

                    <div className="space-y-3 max-h-96 overflow-y-auto">
                        {filteredParties.map((partie) => (
                            <div
                                key={partie.id}
                                className={`p-4 border rounded-lg transition-colors ${selectedParties.includes(partie.id)
                                    ? 'bg-purple-50 border-purple-200'
                                    : 'bg-white border-gray-200'
                                    }`}
                            >
                                <div className="flex items-center gap-3">
                                    <input
                                        type="checkbox"
                                        checked={selectedParties.includes(partie.id)}
                                        onChange={() => handlePartieToggle(partie.id)}
                                        className="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                    />
                                    <div className="bg-green-100 p-2 rounded-full">
                                        <Building className="h-4 w-4 text-green-600" />
                                    </div>
                                    <div className="flex-1">
                                        <div className="font-medium text-gray-900">
                                            {partie.nom}
                                        </div>
                                        <div className="text-sm text-gray-500">
                                            Type: {partie.type}
                                        </div>
                                        {partie.owner ? (
                                            <div className="text-sm text-green-600 flex items-center gap-1">
                                                <UserCheck className="h-3 w-3" />
                                                {partie.owner.prenom} {partie.owner.nom}
                                            </div>
                                        ) : (
                                            <div className="text-sm text-gray-400 flex items-center gap-1">
                                                <UserX className="h-3 w-3" />
                                                Pas de propriétaire
                                            </div>
                                        )}
                                    </div>
                                    {partie.owner && (
                                        <button
                                            onClick={() => handleAssignOwner(partie.id, null)}
                                            disabled={assigning}
                                            className="p-2 text-red-600 hover:bg-red-50 rounded-md disabled:opacity-50"
                                            title="Retirer le propriétaire"
                                        >
                                            <UserX className="h-4 w-4" />
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Résumé des assignations */}
            {selectedUser && selectedParties.length > 0 && (
                <div className="mt-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <h4 className="font-medium text-purple-900 mb-2">
                        Assignation prête
                    </h4>
                    <p className="text-sm text-purple-700">
                        <strong>{selectedUser.prenom} {selectedUser.nom}</strong> sera assigné(e) comme propriétaire de <strong>{selectedParties.length}</strong> partie(s) sélectionnée(s).
                    </p>
                </div>
            )}
        </div>
    );
};

export default EntrepriseOwnershipManagement; 