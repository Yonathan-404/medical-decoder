import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { tests, results } from '../utils/api.jsx';
import Header from '../components/Header.jsx';
import Footer from '../components/Footer.jsx';
import './DecoderPage.css';

export default function DecoderPage() {
  const [availableTests, setAvailableTests] = useState([]);
  const [selectedTest, setSelectedTest] = useState('');
  const [value, setValue] = useState('');
  const [dateRecorded, setDateRecorded] = useState(new Date().toISOString().split('T')[0]);
  const [notes, setNotes] = useState('');
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [fetchingTests, setFetchingTests] = useState(true);

  useEffect(() => {
    tests.list()
      .then(data => {
        setAvailableTests(data);
        setFetchingTests(false);
      })
      .catch(err => {
        setError('Failed to load tests: ' + err.message);
        setFetchingTests(false);
      });
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    setResult(null);

    try {
      const data = await results.create(
        parseInt(selectedTest),
        parseFloat(value),
        dateRecorded,
        notes
      );
      setResult(data);
      setValue('');
      setNotes('');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const selectedTestData = availableTests.find(t => t.id === parseInt(selectedTest));

  return (
    <div className="decoder-page">
      <Header />

      <div className="decoder-container">
        <aside className="input-panel">
          <div className="panel-header">
            <h3>Analyze Lab Results</h3>
            <p>Select a test and enter your value to decode.</p>
          </div>

          <form onSubmit={handleSubmit} className="decoder-form">
            {error && <div className="decoder-error">{error}</div>}

            <div className="form-group">
              <label>Test Type</label>
              <select 
                value={selectedTest} 
                onChange={e => setSelectedTest(e.target.value)}
                required
              >
                <option value="">Select a test...</option>
                {fetchingTests ? (
                  <option disabled>Loading tests...</option>
                ) : (
                  availableTests.map(test => (
                    <option key={test.id} value={test.id}>
                      {test.icon} {test.name} ({test.unit})
                    </option>
                  ))
                )}
              </select>
            </div>

            {selectedTestData && (
              <div className="who-info">
                <small>WHO Range: {selectedTestData.who_min}–{selectedTestData.who_max} {selectedTestData.unit}</small>
              </div>
            )}

            <div className="form-group">
              <label>Your Value</label>
              <input 
                type="number" 
                step="0.01"
                value={value}
                onChange={e => setValue(e.target.value)}
                placeholder={`Enter value in ${selectedTestData?.unit || 'units'}`}
                required
              />
            </div>

            <div className="form-group">
              <label>Date Recorded</label>
              <input 
                type="date" 
                value={dateRecorded}
                onChange={e => setDateRecorded(e.target.value)}
                required
              />
            </div>

            <div className="form-group">
              <label>Notes (optional)</label>
              <textarea 
                value={notes}
                onChange={e => setNotes(e.target.value)}
                placeholder="Any additional context..."
                rows={3}
              />
            </div>

            <button type="submit" className="btn-decode" disabled={loading}>
              {loading ? 'Decoding...' : 'Decode'}
            </button>
          </form>
        </aside>

        <main className="results-panel">
          {result ? (
            <div className="result">
              <div className={`summary-card ${result.interpretation.status}`}>
                <div className="summary-icon">
                  {result.interpretation.status === 'normal' ? '✅' : 
                   result.interpretation.status === 'borderline' ? '⚠️' : '🚨'}
                </div>
                <div className="summary-text">
                  <h4>{result.interpretation.status_label}</h4>
                  <p>{result.interpretation.message}</p>
                </div>
              </div>

              <div className="metric-card">
                <div className="metric-header">
                  <h5>{selectedTestData?.name}</h5>
                  <span className={`metric-badge badge-${result.interpretation.status}`}>
                    {result.interpretation.status_label}
                  </span>
                </div>
                <div className="metric-value">
                  {value} <small>{selectedTestData?.unit}</small>
                </div>

                <div className="scale">
                  <div className="scale-bar">
                    <div className="scale-range"></div>
                    <div 
                      className={`scale-marker status-${result.interpretation.status}`}
                      style={{ left: calculateMarkerPosition(value, selectedTestData) }}
                    ></div>
                  </div>
                  <div className="scale-labels">
                    <span>Low</span>
                    <span>Normal ({selectedTestData?.who_min}–{selectedTestData?.who_max})</span>
                    <span>High</span>
                  </div>
                </div>

                <div className="rec">
                  <span className="rec-label">Recommendation</span>
                  <p>{result.interpretation.recommendation}</p>
                </div>
              </div>

              <div className="results-footer">
                <Link to="/records" className="btn-records">View Past Records →</Link>
                <Link to="/education" className="btn-learn">Learn about NCDs →</Link>
              </div>
            </div>
          ) : (
            <div className="results-empty">
              <div className="empty-icon">🔬</div>
              <h3>Ready to Decode</h3>
              <p>Select a test type and enter your value to see your WHO comparison and personalized health insights.</p>
            </div>
          )}
        </main>
      </div>

      <Footer />
    </div>
  );
}

function calculateMarkerPosition(value, test) {
  if (!test) return '50%';
  const min = parseFloat(test.who_min) * 0.5;
  const max = parseFloat(test.who_max) * 1.5;
  const val = parseFloat(value);
  const pct = ((val - min) / (max - min)) * 100;
  return `${Math.max(0, Math.min(100, pct))}%`;
}