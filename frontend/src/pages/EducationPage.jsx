import Header from '../components/Header.jsx';
import Footer from '../components/Footer.jsx';
import './EducationPage.css';

export default function EducationPage() {
  return (
    <div className="edu-page">
      <Header />

      <main className="edu-main">
        <div className="page-title">
          <h1>Non-Communicable Diseases</h1>
          <p>Understanding the silent conditions that affect millions worldwide.</p>
        </div>

        <section className="edu-section">
          <h2>What is an NCD?</h2>
          <p>Non-communicable diseases (NCDs) are chronic conditions that are not passed from person to person. They develop over long periods of time and are largely influenced by lifestyle, genetics, and environment. The four major types are cardiovascular diseases, cancers, chronic respiratory diseases, and diabetes.</p>
          <div className="stat-row">
            <div className="stat-card">
              <span className="stat-number">74%</span>
              <span className="stat-label">of all global deaths are caused by NCDs</span>
            </div>
            <div className="stat-card">
              <span className="stat-number">17M</span>
              <span className="stat-label">people die from NCDs before age 70 each year</span>
            </div>
            <div className="stat-card">
              <span className="stat-number">77%</span>
              <span className="stat-label">of NCD deaths occur in low and middle income countries</span>
            </div>
          </div>
        </section>

        <section className="edu-section">
          <h2>The Four Key Metrics We Track</h2>
          <div className="condition-grid">
            <div className="condition-card">
              <div className="condition-icon">🩸</div>
              <h3>Blood Glucose & Diabetes</h3>
              <p>Diabetes occurs when the body cannot properly regulate blood glucose. Type 2 diabetes, linked to lifestyle factors, accounts for 95% of all diabetes cases.</p>
              <div className="range-info">
                <span className="range-label">Normal fasting range:</span>
                <span className="range-value">3.9 – 5.6 mmol/L</span>
              </div>
            </div>

            <div className="condition-card">
              <div className="condition-icon">❤️</div>
              <h3>Blood Pressure & Hypertension</h3>
              <p>Hypertension is often called the "silent killer" because it shows no symptoms until serious damage has occurred.</p>
              <div className="range-info">
                <span className="range-label">Normal range:</span>
                <span className="range-value">Below 120/80 mm/Hg</span>
              </div>
            </div>

            <div className="condition-card">
              <div className="condition-icon">⚖️</div>
              <h3>BMI & Obesity</h3>
              <p>Body Mass Index is a simple screening tool for weight categories. Obesity significantly increases the risk of diabetes and cardiovascular disease.</p>
              <div className="range-info">
                <span className="range-label">Normal BMI range:</span>
                <span className="range-value">18.5 – 24.9 kg/m²</span>
              </div>
            </div>

            <div className="condition-card">
              <div className="condition-icon">🫀</div>
              <h3>Cholesterol & Heart Disease</h3>
              <p>High cholesterol leads to a buildup of plaques in arteries, increasing the risk of coronary heart disease and stroke.</p>
              <div className="range-info">
                <span className="range-label">Normal total cholesterol:</span>
                <span className="range-value">Below 5.2 mmol/L</span>
              </div>
            </div>
          </div>
        </section>

        <section className="edu-section">
          <h2>Prevention is Possible</h2>
          <p>Up to 80% of premature heart disease, stroke, and type 2 diabetes could be prevented by eliminating shared risk factors.</p>
          <div className="prevention-list">
            <div className="prevention-item">🚭 <span>Avoid tobacco in all forms</span></div>
            <div className="prevention-item">🥗 <span>Eat a diet rich in fruits, vegetables, and whole grains</span></div>
            <div className="prevention-item">🏃 <span>Get at least 150 minutes of moderate physical activity per week</span></div>
            <div className="prevention-item">🍷 <span>Limit alcohol consumption</span></div>
            <div className="prevention-item">💊 <span>Know your numbers — regular health checks save lives</span></div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}