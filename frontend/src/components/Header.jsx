import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth.jsx';
import './Header.css';

export default function Header() {
  const { user, logout } = useAuth();
  const location = useLocation();

  const isActive = (path) => location.pathname === path ? 'active' : '';

  return (
    <header className="site-header">
      <Link to="/" className="logo">Medical<span>Decoder</span></Link>

      <nav className="nav-links">
        {user ? (
          <>
            <Link to="/decoder" className={isActive('/decoder')}>Decoder</Link>
            <Link to="/records" className={isActive('/records')}>Records</Link>
            <Link to="/education" className={isActive('/education')}>More on NCD</Link>
            <Link to="/about" className={isActive('/about')}>About</Link>
            <button onClick={logout} className="nav-logout">Logout ({user.username})</button>
          </>
        ) : (
          <>
            <Link to="/education" className={isActive('/education')}>More on NCD</Link>
            <Link to="/about" className={isActive('/about')}>About</Link>
            <Link to="/login" className={isActive('/login')}>Login</Link>
            <Link to="/register" className="btn-register">Register</Link>
          </>
        )}
      </nav>
    </header>
  );
}