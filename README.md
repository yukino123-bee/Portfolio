# Native PHP Portfolio

A simple monochrome portfolio with a public guest view and a single-owner editing system. It uses native PHP 8, MySQL, Tailwind CSS through its browser CDN, plain JavaScript, and an optional Python PDF export utility.

## Requirements

- PHP 8.2 or newer with `PDO`, `pdo_mysql`, `json`, and `session`
- MySQL 8 or MariaDB 10.6+
- Python 3.10+ only if command-line PDF export is needed

## Setup

Create the database:

```sql
CREATE DATABASE portfolio_php CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Copy the environment template and add your own credentials:

```bash
cp .env.example .env
```

Initialize the tables, owner, and sample content:

```bash
php database/seed.php
```

Start the site:

```bash
composer start
```

The start script serves `public/` as the web root so stylesheets and JavaScript are available at `/assets/...`.

Open `http://localhost:8000`. Owner login is at `http://localhost:8000/?page=login`.

## Documents

The resume and reflections have browser print buttons. Their print stylesheet uses A4 paper and a one-inch (`25.4mm`) margin. These margins apply only to document printing, not to the system interface.

Optional Python export:

```bash
python3 -m venv .venv
. .venv/bin/activate
pip install -r python/requirements.txt
playwright install chromium
python python/export_pdf.py 'http://localhost:8000/?page=resume' storage/resume.pdf
```

## MVC structure

- `app/Controllers`: request handling, authentication actions, and content-management actions
- `app/Models`: content queries and application data access
- `app/Views`: portfolio HTML templates
- `app/Core`: shared framework utilities such as view rendering
- `app/bootstrap.php`: application configuration and class loading
- `public/index.php`: minimal public entry point that dispatches to the controller
- `public/assets`: monochrome styles and mobile navigation JavaScript
- `database`: MySQL schema and seed script
- `python`: optional PDF export utility
