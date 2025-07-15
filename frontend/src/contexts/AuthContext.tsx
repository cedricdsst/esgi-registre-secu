import React, { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { authService } from '../services/api';
import type { User, LoginData, RegisterData } from '../types';

interface AuthContextType {
    user: User | null;
    isAuthenticated: boolean;
    isLoading: boolean;
    login: (data: LoginData) => Promise<void>;
    register: (data: RegisterData) => Promise<void>;
    logout: () => Promise<void>;
    refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};

interface AuthProviderProps {
    children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
    const [user, setUser] = useState<User | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    const isAuthenticated = !!user;

    // Vérifier l'authentification au chargement
    useEffect(() => {
        const checkAuth = async () => {
            try {
                const token = localStorage.getItem('authToken');
                const storedUser = localStorage.getItem('user');

                if (token && storedUser) {
                    // Vérifier si le token est toujours valide
                    const userData = await authService.me();
                    setUser(userData);
                }
            } catch (error) {
                // Token invalide, nettoyer les données
                localStorage.removeItem('authToken');
                localStorage.removeItem('user');
                setUser(null);
            } finally {
                setIsLoading(false);
            }
        };

        checkAuth();
    }, []);

    const login = async (data: LoginData) => {
        try {
            const response = await authService.login(data.email, data.password);
            localStorage.setItem('authToken', response.token);
            localStorage.setItem('user', JSON.stringify(response.user));
            setUser(response.user);
        } catch (error) {
            throw error;
        }
    };

    const register = async (data: RegisterData) => {
        try {
            const registerData = {
                ...data,
                role: data.role || 'user'
            };
            const response = await authService.register(registerData);
            localStorage.setItem('authToken', response.token);
            localStorage.setItem('user', JSON.stringify(response.user));
            setUser(response.user);
        } catch (error) {
            throw error;
        }
    };

    const logout = async () => {
        try {
            await authService.logout();
        } catch (error) {
            // Même si la déconnexion échoue côté serveur, on nettoie côté client
            console.error('Erreur lors de la déconnexion:', error);
        } finally {
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
            setUser(null);
        }
    };

    const refreshUser = async () => {
        try {
            const userData = await authService.me();
            setUser(userData);
            // Mettre à jour les données stockées
            localStorage.setItem('user', JSON.stringify(userData));
        } catch (error) {
            // Si on ne peut pas rafraîchir, déconnecter
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
            setUser(null);
            throw error;
        }
    };

    const value: AuthContextType = {
        user,
        isAuthenticated,
        isLoading,
        login,
        register,
        logout,
        refreshUser
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}; 