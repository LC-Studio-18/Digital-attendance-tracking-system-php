# Digital Attendance Tracking System

A PHP and MySQL-based attendance tracking system for onsite staff. The system will allow staff to check in and check out, while providing a live dashboard showing who is currently onsite.

## Project overview

This project is being developed to provide a cost-effective, real-time attendance tracking solution. It will support digital check-in and check-out, user authentication, live attendance visibility, duplicate-entry prevention, secure data transmission, and cross-platform use.

The project deadline is **4 December 2026**.

## Tech stack

- PHP
- MySQL
- PDO for database connectivity
- HTML
- CSS
- JavaScript
- Xneelo hosting

## Project structure

```text
Digital-attendance-tracking-system-php/
├── backend/
│   ├── config/
│   ├── includes/
│   ├── controllers/
│   ├── models/
│   └── views/
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── index.php
├── config/
├── storage/
│   └── logs/
├── .env.example
├── .gitignore
└── README.md
```

## Folder explanation

| Folder | Purpose |
|---|---|
| `backend/` | Contains server-side PHP application code |
| `backend/config/` | Contains reusable application and database configuration |
| `backend/includes/` | Contains shared PHP helper functions and error handling |
| `backend/controllers/` | Will contain files that handle user requests and responses |
| `backend/models/` | Will contain database queries and business logic |
| `backend/views/` | Will contain PHP page templates |
| `frontend/` | Contains files served to the browser |
| `frontend/assets/css/` | Contains CSS stylesheets |
| `frontend/assets/js/` | Contains browser JavaScript files |
| `config/` | Contains protected server-specific configuration and credentials |
| `storage/logs/` | Contains application logs |
| `.env.example` | Shows the environment variables required to run the project locally |
| `.gitignore` | Prevents sensitive files and unnecessary files from being committed |

## Security

- Database credentials must not be committed to GitHub.
- Real credentials must be stored in `config/credentials.php` or environment variables.
- `config/credentials.php` and `.env` must remain outside version control.
- The `frontend/` folder should contain only files intended to be accessed through the browser.
- Backend configuration, credentials, controllers, models, and logs must not be placed inside `frontend/`.
- PDO prepared statements must be used for database queries that include user input.
- Detailed errors should be logged, while users should receive generic error messages.

## Local setup

### Prerequisites

Before setting up the project locally, install:

- PHP 8.2 or later
- MySQL
- Git
- Visual Studio Code
- A local development environment such as XAMPP, Laragon, or PHP’s built-in server

### Steps

1. Clone the repository:

```bash
git clone [https://github.com/LC-Studio-18/Digital-attendance-tracking-system-php.git](https://github.com/LC-Studio-18/Digital-attendance-tracking-system-php.git)
```

2. Move into the project folder:

```bash
cd Digital-attendance-tracking-system-php
```

3. Switch to the team branch:

```bash
git checkout team1
```

4. Pull the latest changes:

```bash
git pull origin team1
```

5. Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

6. Open `.env` and add your local MySQL database details.

7. Create `config/credentials.php` locally using the required database credentials.

8. Start the PHP development server:

```bash
php -S localhost:8000 -t frontend
```

9. Open the application in your browser:

```text
http://localhost:8000
```

## Environment variables

Do not commit the `.env` file or real credentials.

`.env.example` contains placeholders only:

```env
APP_ENV=production
APP_DEBUG=false
BASE_URL=/

DB_HOST=localhost
DB_NAME=attendance_db
DB_USER=your_mysql_username
DB_PASSWORD=your_mysql_password
```

## Xneelo deployment

The application will be deployed to Xneelo. Deployment setup will include:

- Uploading the project files to Xneelo
- Configuring the web root
- Creating or connecting to the MySQL database
- Adding database credentials outside the publicly accessible web root
- Testing the application through the Xneelo test URL
- Confirming that sensitive configuration files cannot be accessed through the browser

## Current status

The project is currently in the initial setup phase. The folder structure and backend environment are being established before authentication, staff management, attendance tracking, and dashboard features are developed.

## Future work

- Create the MySQL database and attendance tables
- Implement user authentication
- Build the login page and shared page layout
- Implement staff management
- Implement attendance check-in and check-out
- Build the live attendance dashboard
- Add duplicate-entry prevention
- Add error handling and monitoring
- Configure automated MySQL backups
- Deploy the completed application to Xneelo

## Team workflow

- Work should be done on the agreed team branch.
- Do not commit real credentials, database dumps, or environment files.
- Create a pull request for review before merging changes.
- Raise blockers early so dependent tickets are not delayed.
- Keep ticket status and blockers updated in the project tracker.