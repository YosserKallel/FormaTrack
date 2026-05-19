# FormaTrack

Web application for attendance management in training programs (apprenant, formateur, admin), built with plain PHP, MySQL, vanilla JavaScript, and Bootstrap 5.

## Tech Stack

- PHP (no framework)
- MySQL / MariaDB
- Vanilla JavaScript
- Bootstrap 5 (CDN)
- XAMPP (recommended local environment)

## Project Structure

```text
/index.php
/login.php
/register.php
/logout.php
/setup_demo_data.php
/includes/db.php
/includes/config.php
/includes/header.php
/includes/footer.php
/apprenant/dashboard.php
/apprenant/historique.php
/apprenant/profil.php
/formateur/sessions.php
/formateur/pointage.php
/admin/users/list.php
/admin/users/create.php
/admin/users/edit.php
/admin/users/delete.php
/admin/formations/list.php
/admin/formations/create.php
/admin/formations/edit.php
/admin/formations/delete.php
/admin/formations/inscriptions.php
/admin/sessions/list.php
/admin/sessions/create.php
/admin/sessions/edit.php
/admin/sessions/delete.php
/admin/presences/list.php
/admin/presences/edit.php
/admin/presences/rapport.php
```

## Prerequisites

- Windows + XAMPP installed
- Apache and MySQL services available
- Browser (Chrome/Edge/Firefox)

## Installation (exact local setup)

1. Clone or download this repository.
2. Copy project folder to:
   - `C:\xampp\htdocs\assiduite`
3. Start XAMPP:
   - Start **Apache**
   - Start **MySQL**
4. Open phpMyAdmin:
   - `http://localhost/phpmyadmin`
5. Create database and tables:
   - Go to SQL tab and run:

```sql
CREATE DATABASE IF NOT EXISTS gestion_assiduite CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gestion_assiduite;

CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('apprenant','formateur','admin') NOT NULL DEFAULT 'apprenant',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS formations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  intitule VARCHAR(255) NOT NULL,
  description TEXT,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  formateur_id INT NULL,
  FOREIGN KEY (formateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  formation_id INT NOT NULL,
  formateur_id INT NULL,
  date DATE NOT NULL,
  heure_debut TIME NOT NULL,
  heure_fin TIME NOT NULL,
  salle VARCHAR(100) NOT NULL,
  FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE,
  FOREIGN KEY (formateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS inscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  formation_id INT NOT NULL,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_inscription (utilisateur_id, formation_id),
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS presences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  utilisateur_id INT NOT NULL,
  statut ENUM('present','absent','retard') NOT NULL,
  commentaire TEXT,
  date_saisie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
```

## Generate full demo data (recommended)

To get the same ready-to-test content:

1. Open:
   - `http://localhost/assiduite/setup_demo_data.php`
2. Click:
   - **Generer les donnees demo**

This will reset data and insert:
- demo users
- formations
- sessions
- inscriptions
- presences

## Demo Credentials

- Admin: `yosser@gmail.com` / `Yosser21042004`
- Formateur: `amine@gmail.com` / `Yosser21042004`
- Apprenant: `ichrak@gmail.com` / `Yosser21042004`

## Run the application

- Home page:
  - `http://localhost/assiduite/index.php`
- Login:
  - `http://localhost/assiduite/login.php`

Role redirects:
- `admin` -> `/admin/users/list.php`
- `formateur` -> `/formateur/sessions.php`
- `apprenant` -> `/apprenant/dashboard.php`

## What to test quickly

1. Login as admin:
   - Manage users, formations, sessions, presences, reports
2. Login as formateur:
   - Open assigned sessions and do pointage
3. Login as apprenant:
   - See dashboard, historique, profile

## Notes

- Do not open `.php` files directly from filesystem (`file:///...`).
- Always use `http://localhost/assiduite/...`.
- If styles/scripts look outdated, do hard refresh with `Ctrl + F5`.

## API and fetch demo

- API endpoint: [api/server_time.php](api/server_time.php) returns JSON with the server time.
- Fetch call: [index.php](index.php#L33-L52) calls the API on page load and logs the result in the browser console.

## Screenshots

Add your project screenshots inside a folder named `screenshots/` at the repository root, then reference them here.

Suggested screenshots:

1. Home page (`index.php`)
2. Login page (`login.php`)
3. Admin users list (`admin/users/list.php`)
4. Admin formations list (`admin/formations/list.php`)
5. Formateur pointage (`formateur/pointage.php`)
6. Apprenant dashboard (`apprenant/dashboard.php`)

Example markdown (replace file names with your real images):

```md
![Home](screenshots/home.png)
![Login](screenshots/login.png)
![Admin Users](screenshots/admin-users.png)
![Formations](screenshots/formations.png)
![Pointage](screenshots/pointage.png)
![Dashboard](screenshots/dashboard.png)
```

## Known Limitations

- No framework/CMS is used by design (plain PHP only).
- No advanced export format (PDF/Excel) yet for reports.
- No email notifications implemented.
- No audit log/history tracking for admin actions.
- No pagination yet on large lists.

## Future Improvements

- Add CSV/PDF export in `admin/presences/rapport.php`.
- Add search and pagination for users/formations/sessions.
- Add stronger security hardening (CSRF token, stricter input sanitization).
- Add dashboard widgets for admin and formateur.
- Add deployment guide for shared hosting.

