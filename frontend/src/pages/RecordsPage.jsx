import { useState, useEffect } from 'react';
import { dashboard, results } from '../utils/api.jsx';
import Header from '../components/Header.jsx';
import Footer from '../components/Footer.jsx';
import './RecordsPage.css';

export default function RecordsPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [deletingId, setDeletingId] = useState(null);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = () => {
    setLoading(true);
    dashboard.get()
      .then(data => {
        setData(data);
        setLoading(false);
      })
      .catch(err => {
        setError(err.message);
        setLoading(false);
      });
  };

  const handleDelete = async (id) => {
    if (!confirm('Are you sure you want to delete this record?')) return;

    setDeletingId(id);
    try {
      await results.delete(id);
      loadDashboard();
    } catch (err) {
      setError(err.message);
    } finally {
      setDeletingId(null);
    }
  };

  if (loading) return (
    <div className="records-page">
      <Header />
      <main className="records-main"><p>Loading your health data...</p></main>
      <Footer />
    </div>
  );

  if (error) return (
    <div className="records-page">
      <Header />
      <main className="records-main"><p className="error-text">Error: {error}</p></main>
      <Footer />
    </div>
  );

  return (
    <div className="records-page">
      <Header />

      <main className="records-main">
        <div className="page-title">
          <h1>Your Health Records</h1>
          <p>A history of your decoded results over time.</p>
        </div>

        <div className="summary-row">
          {data.latest_values.map(item => (
            <div key={item.test_name} className="summary-card">
              <span className="summary-label">Last {item.test_name}</span>
              <span className="summary-value">
                {item.value} <small>{item.unit}</small>
              </span>
              <span className={`summary-tag badge-${item.status}`}>
                {item.status_label}
              </span>
            </div>
          ))}

          {data.latest_values.length === 0 && (
            <div className="summary-card empty">
              <span className="summary-label">No records yet</span>
              <span className="summary-value">—</span>
            </div>
          )}
        </div>

        <div className="content-row">
          <div className="chart-box">
            <h3>Results Over Time</h3>
            {Object.entries(data.trends).slice(0, 1).map(([testName, trendData]) => (
              <div key={testName} className="chart-content">
                <p className="chart-title">{testName} ({trendData.unit})</p>
                <div className="bars">
                  {trendData.data.map((point, i) => {
                    const maxVal = Math.max(...trendData.data.map(d => d.value));
                    const height = maxVal > 0 ? (point.value / maxVal) * 100 : 0;
                    return (
                      <div key={i} className="bar-wrap">
                        <div 
                          className={`bar ${i === trendData.data.length - 1 ? 'highlight' : ''}`}
                          style={{ height: `${Math.max(height, 5)}%` }}
                          title={`${point.date}: ${point.value}`}
                        ></div>
                        <span>{new Date(point.date).toLocaleDateString('en', { month: 'short' })}</span>
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
            {Object.keys(data.trends).length === 0 && (
              <p className="chart-empty">Add results to see trends</p>
            )}
          </div>

          <div className="table-box">
            <h3>Decode History</h3>
            <table className="records-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Test</th>
                  <th>Value</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {data.recent_history.map(item => (
                  <tr key={item.id}>
                    <td>{new Date(item.date_recorded).toLocaleDateString()}</td>
                    <td>{item.test_name}</td>
                    <td>{item.value} {item.unit}</td>
                    <td>
                      <span className={`tag badge-${item.status}`}>
                        {item.status_label}
                      </span>
                    </td>
                    <td>
                      <button 
                        className="btn-delete"
                        onClick={() => handleDelete(item.id)}
                        disabled={deletingId === item.id}
                      >
                        {deletingId === item.id ? '...' : '🗑️'}
                      </button>
                    </td>
                  </tr>
                ))}
                {data.recent_history.length === 0 && (
                  <tr>
                    <td colSpan="5" className="table-empty">No records yet. Go to the Decoder to add your first result.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        <div className="stats-footer">
          <p>Total records: <strong>{data.statistics.total_records}</strong> | 
             Tests tracked: <strong>{data.statistics.tests_tracked}</strong> | 
             First record: <strong>{data.statistics.first_record ? new Date(data.statistics.first_record).toLocaleDateString() : '—'}</strong></p>
        </div>
      </main>

      <Footer />
    </div>
  );
}