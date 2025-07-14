import React from 'react';
import { Building } from 'lucide-react';

const Batiments: React.FC = () => {
    return (
        <div className="space-y-6">
            <div className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center">
                    <Building className="h-8 w-8 text-primary-600 mr-3" />
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Gestion des Bâtiments</h1>
                        <p className="text-gray-600 mt-2">
                            Cette page sera bientôt disponible pour la gestion des bâtiments
                        </p>
                    </div>
                </div>
            </div>

            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">
                    Fonctionnalités à venir :
                </h2>
                <ul className="space-y-2 text-gray-600">
                    <li>• Création et modification de bâtiments</li>
                    <li>• Gestion des typologies (ERP, IGH, HAB, BUP, ICPE)</li>
                    <li>• Association avec les sites</li>
                    <li>• Gestion des niveaux</li>
                    <li>• Visualisation des parties</li>
                </ul>
            </div>
        </div>
    );
};

export default Batiments; 