import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import PublicRoute from './components/PublicRoute';
import Layout from './components/layout/Layout';
import LoginForm from './components/auth/LoginForm';
import RegisterForm from './components/auth/RegisterForm';
import Dashboard from './pages/Dashboard';
import Sites from './pages/Sites';
import SiteDetail from './pages/SiteDetail';
import Buildings from './pages/Buildings';
import BatimentDetail from './pages/BatimentDetail';

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="App">
          <Routes>
            {/* Routes publiques */}
            <Route path="/login" element={
              <PublicRoute>
                <LoginForm />
              </PublicRoute>
            } />
            <Route path="/register" element={
              <PublicRoute>
                <RegisterForm />
              </PublicRoute>
            } />

            {/* Routes protégées */}
            <Route path="/" element={
              <ProtectedRoute>
                <Layout>
                  <Dashboard />
                </Layout>
              </ProtectedRoute>
            } />

            {/* Routes hiérarchiques des sites */}
            <Route path="/sites" element={
              <ProtectedRoute>
                <Layout>
                  <Sites />
                </Layout>
              </ProtectedRoute>
            } />

            <Route path="/sites/:id" element={
              <ProtectedRoute>
                <Layout>
                  <SiteDetail />
                </Layout>
              </ProtectedRoute>
            } />

            <Route path="/sites/:siteId/buildings" element={
              <ProtectedRoute>
                <Layout>
                  <Buildings />
                </Layout>
              </ProtectedRoute>
            } />

            <Route path="/sites/:siteId/batiments/:batimentId" element={
              <ProtectedRoute>
                <Layout>
                  <BatimentDetail />
                </Layout>
              </ProtectedRoute>
            } />

            {/* Routes temporaires - à implémenter */}
            <Route path="/inventaire" element={
              <ProtectedRoute>
                <Layout>
                  <div className="text-center py-8">
                    <h2 className="text-2xl font-bold text-gray-900 mb-4">Inventaire</h2>
                    <p className="text-gray-600">Module en cours de développement</p>
                  </div>
                </Layout>
              </ProtectedRoute>
            } />

            <Route path="/settings" element={
              <ProtectedRoute>
                <Layout>
                  <div className="text-center py-8">
                    <h2 className="text-2xl font-bold text-gray-900 mb-4">Paramètres</h2>
                    <p className="text-gray-600">Module en cours de développement</p>
                  </div>
                </Layout>
              </ProtectedRoute>
            } />
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;
