# InfinityFree automatic deployment

The workflow in `.github/workflows/deploy-infinityfree.yml` validates and deploys the application whenever `main` is updated. It can also be run manually from the GitHub Actions tab.

Create a GitHub environment named `production`, then add these environment secrets:

- `INFINITYFREE_FTP_USERNAME` — the FTP username shown by InfinityFree
- `INFINITYFREE_FTP_PASSWORD` — the hosting account/FTP password
- `APP_URL` — the complete public website URL, including `https://`
- `DB_HOST` — `sql105.infinityfree.com` for the account shown
- `DB_PORT` — `3306`
- `DB_NAME` — the complete database name created in the InfinityFree control panel
- `DB_USER` — the MySQL username shown by InfinityFree
- `DB_PASSWORD` — the hosting account/database password
- `OWNER_EMAIL` — the portfolio administrator email
- `OWNER_PASSWORD` — a strong, unique portfolio administrator password

The deployment package contains only `app`, `public`, `.htaccess`, and a generated production `.env`. Server-uploaded Resume and Reflection documents under `public/uploads/documents` are excluded so later deployments do not delete them.

The database must be created in the InfinityFree control panel and initialized once through phpMyAdmin using `database/schema.sql`. The automatic workflow deploys application changes but does not connect remotely to InfinityFree MySQL.
