# CommunityCAD

> [!WARNING]
> **DEVELOPMENT BRANCH — HIGHLY EXPERIMENTAL / NOT PRODUCTION-READY.**
> This repository is an independent development effort to modernise CommunityCAD. This branch contains work-in-progress changes that may be broken, incomplete, unreviewed or incompatible with existing data. Features, APIs, database schemas, deployment procedures and behaviour may change without notice. Some functionality may be incomplete, unstable or untested. **Do not deploy to a live/production CAD or rely on it for real emergency services.** Back up your data before testing upgrades. This project is not the official CommunityCAD release.

Welcome to Community CAD!

- **Website:** [communitycad.app](https://communitycad.app)
- **Documentation:** [communitycad.app/docs](https://communitycad.app/docks)
- **Suggestions/Bugs:** [GitHub - Issues](https://github.com/CommunityCAD/CommunityCAD/issues/new/choose)
- **Discord Link:** [communitycad.app/discord](https://communitycad.app/discord)

## Local development and deployment — unified directory

**Target layout:** Laravel source code and its Docker Compose deployment files live in **one installation directory**. This is the recommended structure for both developer machines and eventual self-hosted installations.

```text
CommunityCAD-Modernised/
├── app/                   # Laravel backend
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/               # Runtime data; preserve across deployments
├── docker/                # Dockerfiles and deployment helpers, if needed
├── compose.yml            # Deploy-specific Compose config (see migration below)
├── .env                   # Local secrets; never commit
├── artisan
├── composer.json
└── README.md
```

> **Current transition status:** This repository now includes the **working PHP 8.2 Apache `Dockerfile`**, but does **not yet ship the existing server's `compose.yaml`**; it must be migrated from the current deployment. The existing development server runs Docker Compose from `~/Documents/CommunityCAD-Dev`. The instructions below describe how to consolidate that **existing, working** deployment safely. Until its Compose configuration and mounts have been inspected and migrated, continue to run Docker commands from `CommunityCAD-Dev`. Merely updating the README does **not** change the running deployment. Do not remove that directory until all checks pass.

### One-time migration from the previous two-folder setup

Original paths:

```text
~/Documents/CommunityCAD-Modernised/  # Git checkout, Laravel source
~/Documents/CommunityCAD-Dev/         # Existing Compose stack, settings/volumes
```

**1. Inventory and back up your current stack.** Before moving any files, make a verified backup of the development database, uploads, environment settings and any host-mounted persistent directories. Find where the running Compose stack stores them:

```bash
cd ~/Documents/CommunityCAD-Dev
docker compose ls
docker compose ps
docker compose config --no-interpolate
```

**CAUTION:** Compose configuration may contain secrets. Never post the full output or a populated `.env` publicly; redact passwords/tokens before sharing. Record the current Compose project name, build context, Dockerfile path, bind mount sources, named volumes, network names, `env_file` paths and container/service names. Review any `docker compose -f ...` flags already used in your deployment.

**2. Copy, don't move, the actual working Compose file.** On this development server the current deployment uses `CommunityCAD-Dev/compose.yaml` and a Dockerfile with PHP 8.2/Apache. That Dockerfile is now tracked at the repository root. Copy the existing Compose YAML, **not the whole deployment folder**:

```bash
cd ~/Documents/CommunityCAD-Modernised
cp -n ~/Documents/CommunityCAD-Dev/compose.yaml ./compose.yaml
```

**Do not commit `compose.yaml` yet**: the server-specific file may contain database credentials or private settings. Before adding a portable version to Git, review and remove all secrets and server-specific identifiers. You can use a sanitized example for sharing.

Do not blindly copy the whole development directory over the Git checkout. Preserve local `.env` settings and all persistent data separately and securely; only copy a secret file if required and with restricted permissions. Never commit a populated `.env`, credentials or customer data.

**3. Adjust paths in the copied Compose file.** The existing deployment resolves its application build context to `/home/charleq/Documents/CommunityCAD-Modernised` and points at Dockerfile `../CommunityCAD-Dev/Dockerfile`. Once the Compose file is located in the Git checkout, change the app's build configuration to use `context: .` and `dockerfile: Dockerfile`, rather than a path referencing `CommunityCAD-Dev`. Keep the existing service names, MariaDB image, app port, and the project name `communitycad-modernised-dev`.

On the current server the existing app configuration mounts `CommunityCAD-Dev/.env` into `/var/www/html/.env` **read-only**. Either retain that absolute mount temporarily or copy the secret file securely to `CommunityCAD-Modernised/.env`, then update the mount to `./.env:/var/www/html/.env:ro`. Check that only the file's path changes: its **contents, APP_KEY and database settings must stay unchanged**. Keep `.env` out of Git.

The known persistent volumes are `communitycad-modernised-dev_mariadb-data`, `communitycad-modernised-dev_app-storage` and `communitycad-modernised-dev_app-cache`. Do not remove, rename or recreate them. Make sure new `docker compose config --format json` still resolves to the same named volumes. Confirm that persistent DB volumes, uploads and storage still reference the **original existing data** (or a carefully copied/migrated equivalent). Beware that relative host bind paths resolve from the Compose project directory; moving the YAML can silently change their meaning.

**4. Keep the existing Compose project identity.** An implicit project name can change when the Compose working directory changes, causing Docker to create a **second** stack, network and named volumes. Before starting anything, retain the current project name through `name:`, `COMPOSE_PROJECT_NAME`, or the existing `-p` invocation, as appropriate. Check for container-name and port conflicts.

**5. Validate before switching the running deployment.** Compare the new resolved Compose configuration with the existing deployment — especially volume sources and project identity:

```bash
cd ~/Documents/CommunityCAD-Modernised
docker compose config --no-interpolate
docker compose config --quiet
```

If the new `compose.yml` isn't present or validation fails, **do not** run `docker compose up` from the new directory yet. If necessary, schedule a maintenance window to migrate host bind mounts and restart the existing stack without changing its data. Keep `CommunityCAD-Dev` and backups intact until the unified deployment has been tested and all data is accounted for.

### Day-to-day workflow (after the unified Compose setup has been verified)

Switch branches or update the source from **the same** directory that holds Compose:

```bash
cd ~/Documents/CommunityCAD-Modernised
git status -sb
git fetch origin
git switch develop
git pull --ff-only origin develop
```

To test an open feature PR, switch to its branch instead and use `git pull --ff-only origin <branch-name>`. Back up or stash local modifications before switching branches. Never overwrite uncommitted deployment configuration.

#### Compile the frontend with npm

Laravel Mix generates `public/js/app.js` and `public/css/app.css`. Rebuilding a Laravel app image does **not necessarily** compile frontend assets.

```bash
cd ~/Documents/CommunityCAD-Modernised
npm ci
npm run dev
```

For optimised assets when appropriate use `npm run prod`.

#### Compile the frontend **without** installing npm on the host

Use a disposable Node container, as with the working development setup:

```bash
cd ~/Documents/CommunityCAD-Modernised

docker run --rm \
  --user "$(id -u):$(id -g)" \
  -e HOME=/tmp \
  -e npm_config_cache=/tmp/.npm \
  -v "$PWD:/app" \
  -w /app \
  node:20-bookworm \
  sh -c 'npm ci && npm run dev'
```

These commands compile assets in the project directory. Verify the running app image or bind mounts contain the compiled outputs.

#### Docker rebuild and Laravel updates

**Only after completing and validating the migration** (and with `compose.yml` present in the Git checkout):

```bash
cd ~/Documents/CommunityCAD-Modernised

docker compose up -d --build --no-deps app
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan view:cache
```

**Database safety:** Back up before applying migrations. Never use `migrate:fresh`, `docker compose down -v` or destructive resets on an installation with data to preserve.

#### Verify the running assets

```bash
cd ~/Documents/CommunityCAD-Modernised
sha256sum public/js/app.js public/css/app.css
docker compose exec -T app sha256sum public/js/app.js public/css/app.css
```

If the checksums differ, inspect the Compose build context, image files and bind mounts. In Firefox, use **Ctrl+Shift+R** to reload cached frontend assets.

The historic `document.querySelector(...) is null` Markdown editor error can be caused by stale compiled `public/js/app.js`. The source `resources/js/app.js` guards editor initialisation where the editor is absent.

### Production deployment considerations

- Source files and Compose configuration can live together, but **runtime data and secrets must survive code updates**. Keep database storage in durable Docker volumes or separate managed storage, and configure writable Laravel storage/uploads as persistent.
- Exclude `.env`, local Compose overrides, credentials, backups, runtime data and generated secrets from version control. Do not embed host-specific absolute paths in distributed Compose defaults.
- A truly one-command production installer still requires a reviewed, supported Compose configuration and first-install process; that work is **not yet completed** by this documentation update.
- Test upgrade, backup/restore, permission and rollback procedures before declaring production support.
