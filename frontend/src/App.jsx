import { Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './hooks/useAuth.jsx';

import LandingPage from './pages/LandingPage.jsx';
import DecoderPage from './pages/DecoderPage.jsx';
import RecordsPage from './pages/RecordsPage.jsx';
import EducationPage from './pages/EducationPage.jsx';
import AboutPage from './pages/AboutPage.jsx';
import LoginPage from './pages/LoginPage.jsx';
import RegisterPage from './pages/RegisterPage.jsx';

function ProtectedRoute({ children }) {
  const { user, loading } = useAuth();

  if (loading) return <div style={{ textAlign: 'center', padding: '4rem' }}>Loading...</div>;
  if (!user) return <Navigate to="/login" replace />;

  return children;
}

function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/" element={<LandingPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/education" element={<EducationPage />} />
        <Route path="/about" element={<AboutPage />} />

        <Route path="/decoder" element={
          <ProtectedRoute><DecoderPage /></ProtectedRoute>
        } />
        <Route path="/records" element={
          <ProtectedRoute><RecordsPage /></ProtectedRoute>
        } />
      </Routes>
    </AuthProvider>
  );
}

export default App;