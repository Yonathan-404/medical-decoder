# Medical Decoder — Setup Guide

## Prerequisites
- XAMPP (Apache + MySQL) installed
- Node.js installed (for npm)

---

## STEP 1: Database (MySQL)

1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**
3. Open browser → http://localhost/phpmyadmin
4. Click **Import** tab
5. Choose File: `backend/sql/schema.sql`
6. Click **Go**

✅ Database `medical_decoder` is created with 6 tables + WHO data

---

## STEP 2: Backend (PHP)

1. Copy the `backend/` folder to:
   ```
   C:/xampp/htdocs/medical-decoder/backend/
   ```

2. Edit `backend/config.php` if your MySQL credentials differ:
   ```php
   $username = 'root';     // your MySQL username
   $password = '';         // your MySQL password (empty for XAMPP default)
   ```

3. Test: http://localhost/medical-decoder/backend/api/tests.php
   → Should show JSON with WHO test data

---

## STEP 3: Frontend (React)

1. Open terminal/command prompt
2. Navigate to frontend folder:
   ```bash
   cd path/to/medical-decoder/frontend
   ```

3. Install dependencies:
   ```bash
   npm install
   ```

4. Start dev server:
   ```bash
   npm run dev
   ```

5. Open http://localhost:5173

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| "Database connection failed" | Check config.php credentials match your MySQL |
| "CORS error" in browser | Make sure Apache is running, check config.php CORS headers |
| "Cannot GET /api/..." | Check .htaccess is in place, or try direct URL with .php extension |
| npm not found | Install Node.js from nodejs.org |

---

## Project Structure

```
medical-decoder/
├── backend/
│   ├── config.php          # DB connection + CORS + helpers
│   ├── .htaccess           # Apache config
│   ├── api/
│   │   ├── .htaccess
│   │   ├── auth.php        # Register/Login/Logout
│   │   ├── results.php     # CRUD + Decoder Engine
│   │   ├── tests.php       # WHO baselines
│   │   ├── profile.php     # User profile
│   │   └── dashboard.php   # Stats & charts data
│   └── sql/
│       └── schema.sql      # Database schema + seed data
│
└── frontend/
    ├── index.html
    ├── package.json
    ├── vite.config.js
    └── src/
        ├── main.jsx
        ├── App.jsx
        ├── index.css
        ├── utils/api.js
        ├── hooks/useAuth.js
        ├── components/
        │   ├── Header.jsx
        │   ├── Header.css
        │   ├── Footer.jsx
        │   └── Footer.css
        └── pages/
            ├── LandingPage.jsx
            ├── LandingPage.css
            ├── LoginPage.jsx
            ├── RegisterPage.jsx
            ├── AuthPage.css
            ├── DecoderPage.jsx
            ├── DecoderPage.css
            ├── RecordsPage.jsx
            ├── RecordsPage.css
            ├── EducationPage.jsx
            ├── EducationPage.css
            ├── AboutPage.jsx
            └── AboutPage.css
```
