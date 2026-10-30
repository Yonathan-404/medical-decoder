const API_BASE = '/api';

async function apiFetch(endpoint, options = {}) {
  const url = `${API_BASE}${endpoint}`;

  const config = {
    ...options,
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  };

  if (options.body instanceof FormData) {
    delete config.headers['Content-Type'];
  }

  try {
    const response = await fetch(url, config);
    const data = await response.json();

    if (!data.success) {
      throw new Error(data.data || data.error || 'Request failed');
    }

    return data.data;
  } catch (error) {
    console.error('API Error:', error.message);
    throw error;
  }
}

export const auth = {
  register: (username, email, password) =>
    apiFetch('/auth.php?action=register', {
      method: 'POST',
      body: JSON.stringify({ username, email, password }),
    }),

  login: (username, password) =>
    apiFetch('/auth.php?action=login', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    }),

  logout: () =>
    apiFetch('/auth.php?action=logout', { method: 'POST' }),

  me: () =>
    apiFetch('/auth.php?action=me'),
};

export const results = {
  list: () => apiFetch('/results.php'),
  get: (id) => apiFetch(`/results.php?id=${id}`),
  create: (testId, value, dateRecorded, notes = '') =>
    apiFetch('/results.php', {
      method: 'POST',
      body: JSON.stringify({ test_id: testId, value, date_recorded: dateRecorded, notes }),
    }),
  update: (id, updates) =>
    apiFetch(`/results.php?id=${id}`, {
      method: 'PUT',
      body: JSON.stringify(updates),
    }),
  delete: (id) => apiFetch(`/results.php?id=${id}`, { method: 'DELETE' }),
};

export const tests = {
  list: () => apiFetch('/tests.php'),
  get: (id) => apiFetch(`/tests.php?id=${id}`),
  byCategory: (categoryId) => apiFetch(`/tests.php?category=${categoryId}`),
};

export const profile = {
  get: () => apiFetch('/profile.php'),
  update: (data) => apiFetch('/profile.php', { method: 'PUT', body: JSON.stringify(data) }),
};

export const dashboard = {
  get: () => apiFetch('/dashboard.php'),
};