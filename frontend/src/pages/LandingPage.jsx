import { Link } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth.jsx';
import Header from '../components/Header.jsx';
import Footer from '../components/Footer.jsx';
import './LandingPage.css';

export default function LandingPage() {
  const { user } = useAuth();

  return (
    <div className="landing-page">
      <Header />

      <main className="hero">
        <h1 className="hero-title">
          Understand your lab results<br />in <em>plain English</em>
        </h1>

        <div className="cards-row">
          <div className="card">
            <div className="card-icon">⬆</div>
            <h4>Enter Your Data</h4>
            <ul>
              <li>Basic medical data such as Age, Sex and height/weight</li>
              <li>Medical Test Results</li>
            </ul>
          </div>

          <div className="card">
            <div className="card-icon">🧠</div>
            <h4>Analyze and compare your data</h4>
            <ul>
              <li>Compares your numbers against real-time benchmarks from the WHO</li>
            </ul>
          </div>

          <div className="card">
            <div className="card-icon">👨‍⚕️</div>
            <h4>Show Analysis</h4>
            <ul>
              <li>Provide results in an easy to understand format</li>
              <li>Suggests basic lifestyle advice in relation to the results</li>
            </ul>
          </div>
        </div>

        <Link to={user ? "/decoder" : "/login"} className="btn-start">
          Start my decoder
        </Link>

        <p className="privacy-note">🔒 Privacy first: data is stored securely in your account</p>
      </main>

      <Footer />
    </div>
  );
}