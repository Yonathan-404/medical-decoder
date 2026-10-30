import Header from '../components/Header.jsx';
import Footer from '../components/Footer.jsx';
import './AboutPage.css';

export default function AboutPage() {
  return (
    <div className="about-page">
      <Header />

      <main className="edu-main">
        <div className="page-title">
          <h1>About Medical Decoder</h1>
          <p>Built for patients, not hospitals.</p>
        </div>

        <section className="edu-section">
          <h2>Our Mission</h2>
          <p>Medical Decoder was built out of a simple frustration — lab results are confusing. Numbers on a piece of paper mean nothing to most people without context, and doctors rarely have time to explain every value in detail. We built this tool to bridge that gap: to translate clinical data into plain language that anyone can understand and act on.</p>
          <p style={{ marginTop: '1rem' }}>We focus specifically on non-communicable diseases because they are the leading cause of death globally, yet many are highly preventable with early awareness and lifestyle changes.</p>
        </section>

        <section className="edu-section privacy-section">
          <div className="privacy-header">
            <span className="privacy-icon">🔒</span>
            <h2>Your Data Stays Secure</h2>
          </div>
          <p>While we store data on our server for convenience, your privacy is paramount:</p>

          <div className="privacy-grid">
            <div className="privacy-card">
              <span className="picon">🔐</span>
              <h4>Password Protected</h4>
              <p>Your account is secured with bcrypt-hashed passwords. Only you can access your health records.</p>
            </div>
            <div className="privacy-card">
              <span className="picon">🚫</span>
              <h4>No Third-Party Sharing</h4>
              <p>We do not sell, share, or transmit your health data to advertisers or external services.</p>
            </div>
            <div className="privacy-card">
              <span className="picon">📡</span>
              <h4>Encrypted Storage</h4>
              <p>All data is stored in a secure MySQL database with proper access controls and prepared statements.</p>
            </div>
            <div className="privacy-card">
              <span className="picon">🗑️</span>
              <h4>You Control Deletion</h4>
              <p>Delete any record or your entire account at any time. Your data, your control.</p>
            </div>
          </div>
        </section>

        <section className="edu-section disclaimer-section">
          <h2>Important Disclaimer</h2>
          <p>Medical Decoder is an <strong>educational tool</strong>, not a medical device or diagnostic service. The analysis it provides is based on general WHO guidelines and population-level benchmarks. It does not account for your full medical history, medications, or individual circumstances.</p>
          <p style={{ marginTop: '1rem' }}>Always consult a qualified healthcare professional before making any decisions about your health. If you are experiencing symptoms or a medical emergency, please contact your doctor or emergency services immediately.</p>
        </section>
      </main>

      <Footer />
    </div>
  );
}