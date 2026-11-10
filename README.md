# CommunityCAD

> [!WARNING]
> **DEVELOPMENT BRANCH — HIGHLY EXPERIMENTAL / NOT PRODUCTION-READY.**
> This repository is an independent development effort to modernise CommunityCAD. This branch contains work-in-progress changes that may be broken, incomplete, unreviewed or incompatible with existing data. Features, APIs, database schemas, deployment procedures and behaviour may change without notice. Some functionality may be incomplete, unstable or untested. **Do not deploy to a live/production CAD or rely on it for real emergency services.** Back up your data before testing upgrades. This project is not the official CommunityCAD release.

> [!IMPORTANT]
> **Help shape CommunityCAD — feature requests and feedback wanted!**
> We're actively looking for suggestions on what you would like to see added, improved, restored or changed in this modernised CAD. Whether it's a missing feature, a better dispatcher/MDT workflow, accessibility, integrations or a bug you've encountered, **we want your input**.
> **[Request a feature or share feedback](https://github.com/Hamster-Systems/CommunityCAD-Modernised/issues/new/choose)** — check existing issues first to avoid duplicates, and describe your idea, why it would help, and any examples or screenshots you can share. Requests are welcome, but aren't guarantees of implementation.
>
Welcome to Community CAD!

- **Hamster Systems website:** [hamster-sys.com](https://hamster-sys.com)
- **CommunityCAD website:** Coming soon
- **Documentation:** Coming soon
- **Discord community:** Coming soon
- **Feature requests / bug reports:** [CommunityCAD-Modernised Issues](https://github.com/Hamster-Systems/CommunityCAD-Modernised/issues/new/choose)

## Original CommunityCAD credits

This project is built upon the **original open-source CommunityCAD codebase** developed and released by the **[CommunityCAD project and its contributors](https://github.com/CommunityCAD/CommunityCAD)**. Full credit for the original CAD/MDT application, its foundations and original functionality belongs to those developers.

**Hamster Systems** is independently modernising and extending that existing project. Our enhancements do not make us the original creators of CommunityCAD, and this repository is **not an official release of or endorsed by the original CommunityCAD team**.

Thank you to the original developers and everyone who contributed to making the source code available. Please refer to the [original repository and its contribution history](https://github.com/CommunityCAD/CommunityCAD/graphs/contributors) for upstream contributors. Existing third-party attribution and applicable copyright/license notices must continue to be respected.

## Unified Docker development layout

The repository now contains the Laravel application, `Dockerfile` and `compose.yaml` together. **This is a development configuration**, not a supported production installer. Its Compose project name is intentionally set to `communitycad-modernised-dev` to preserve the current development stack's Docker volumes.

```text
CommunityCAD-Modernised/
├── app/                 # Laravel source
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── Dockerfile           # PHP 8.2 / Apache image
├── compose.yaml         # App + MariaDB, bound to localhost:8096
├── .dockerignore
├── .env                 # Private local environment — NOT committed
├── artisan
└── README.md
```

The database is stored in persistent Docker volume `communitycad-modernised-dev_mariadb-data`; Laravel storage and framework cache use `communitycad-modernised-dev_app-storage` and `communitycad-modernised-dev_app-cache`. Do not remove these volumes or change the Compose project name during migration.

### Migrating the existing two-folder development deployment

The original deployment uses `~/Documents/CommunityCAD-Dev/compose.yaml`, its private `.env`, and a Dockerfile located there. The new `compose.yaml` replaces the old build paths with `context: .` and `dockerfile: Dockerfile`, while retaining the same project name, services, container names, volumes, internal network and `127.0.0.1:8096` port.

**Before switching over:** back up the development database, application storage/uploads and the old `.env`. Verify backups before making changes. **Do not** use `docker compose down -v`, `migrate:fresh`, or delete `CommunityCAD-Dev`.

If you previously copied `compose.yaml` into the Git checkout before this file was tracked, preserve that untracked file *outside the checkout* to avoid a Git pull conflict:

```bash
cd ~/Documents/CommunityCAD-Modernised
mv compose.yaml ~/Documents/CommunityCAD-Dev/compose-unified-local-backup.yaml
git pull --ff-only origin docs/unified-communitycad-compose-layout
```

The `mv` step is only needed when the root `compose.yaml` is **untracked**; check `git status --short compose.yaml` first. Never overwrite locally customised settings without comparing them.

Copy the original environment file securely and keep its secrets unchanged:

```bash
cd ~/Documents/CommunityCAD-Modernised
if [ ! -e .env ]; then
    cp -p ~/Documents/CommunityCAD-Dev/.env .env
    chmod 600 .env
else
    echo "Existing .env found — compare safely; do not overwrite"
fi
```

The new Compose file expects `./.env` for both variable substitution and the read-only application mount. Retain the same `APP_KEY`, `DB_PASSWORD` and `DB_ROOT_PASSWORD` from the old environment. A different `APP_KEY` prevents decryption of existing encrypted SMTP passwords.

Validate before starting anything:

```bash
cd ~/Documents/CommunityCAD-Modernised
docker compose config --quiet
docker compose config --no-interpolate --format json | python3 -c '
import json,sys
cfg=json.load(sys.stdin)
print("Project:", cfg["name"])
for service, data in cfg["services"].items():
    print("Service:", service)
    print("Build:", data.get("build"))
    print("Volumes:", [(v.get("source"),v.get("target")) for v in data.get("volumes",[])])
print("Volume names:", {k:v.get("name") for k,v in cfg.get("volumes",{}).items()})
'
```

Expected project: `communitycad-modernised-dev`. Expected volume names: `communitycad-modernised-dev_mariadb-data`, `communitycad-modernised-dev_app-storage`, `communitycad-modernised-dev_app-cache`. The app's bind mount should now read `CommunityCAD-Modernised/.env`; build context and Dockerfile should be inside `CommunityCAD-Modernised`. **Do not share a full resolved Compose config or `.env` publicly**, as it may expose secrets.

Only after verifying the project name, named volumes, environment and container mappings, switch over during a suitable maintenance window:

```bash
cd ~/Documents/CommunityCAD-Modernised
docker compose up -d --build --no-deps app
docker compose ps
docker compose exec -T -u www-data app php artisan optimize:clear
docker compose exec -T -u www-data app php artisan view:cache
```

Check application login, stored data, uploaded files and SMTP settings. Run `php artisan migrate --force` only when an intended schema update is required and the database backup is confirmed. Keep the original `CommunityCAD-Dev` directory and backups until the unified deployment works fully.

### Normal development workflow (after migration)

```bash
cd ~/Documents/CommunityCAD-Modernised
git status -sb
git fetch origin
git switch develop
git pull --ff-only origin develop
docker compose up -d --build --no-deps app
docker compose exec -T -u www-data app php artisan optimize:clear
docker compose exec -T -u www-data app php artisan view:cache
```

For frontend changes, rebuild Laravel Mix assets. If npm is installed locally, run `npm ci && npm run dev`. Otherwise use disposable Node:

```bash
docker run --rm --user "$(id -u):$(id -g)" \
  -e HOME=/tmp -e npm_config_cache=/tmp/.npm \
  -v "$PWD:/app" -w /app node:20-bookworm \
  sh -c 'npm ci && npm run dev'
```

Compiled frontend files `public/css/app.css` and `public/js/app.js` can be modified by the build: preserve their changes before switching branches. Confirm the running image contains the right assets by comparing checksums:

```bash
sha256sum public/js/app.js public/css/app.css
docker compose exec -T app sha256sum public/js/app.js public/css/app.css
```

### Fix for recurring HTTP 500 after Docker rebuilds

The unified image includes `docker/entrypoint.sh`. Every time the **app container starts**, the entrypoint repairs ownership/write permissions for Laravel's mounted compiled Blade view cache, sessions, framework cache, logs and `bootstrap/cache`. This prevents previously root-owned compiled Blade templates from breaking pages with `file_put_contents(...storage/framework/views/...): Permission denied` after an app rebuild. It does **not** clear the database or remove uploaded files.

Run Laravel cache maintenance as Apache's PHP user (`www-data`) so newly compiled files remain writable:

```bash
cd ~/Documents/CommunityCAD-Modernised
docker compose exec -T -u www-data app php artisan view:clear
docker compose exec -T -u www-data app php artisan view:cache
```

If you ran Artisan as root *after* container startup and a permission error returns, restart/rebuild the app container to re-run the startup repair, or fix ownership of the specific cache directory. Keep the existing persistent volumes; never delete them to repair a view-cache error.

To verify the **running** deployment no longer depends on the old folder, inspect the project name and the app container's bind mount, without exposing environment values:

```bash
docker compose ps
docker inspect communitycad-modernised-app --format '{{range .Mounts}}{{println .Source "->" .Destination}}{{end}}'
```

The `.env` mount should point under `CommunityCAD-Modernised`. Both named volumes should retain the `communitycad-modernised-dev_...` names. A running container does not directly record the directory of its original Compose file; use `docker compose ls` to check its configuration path.

### Production considerations

This Compose deployment is intentionally a **development** installation. A production release still needs production-oriented secret handling, deployment settings, image build/update/rollback processes, writable storage strategy, backups and security testing. Keep `.env` out of Git, store durable data outside image layers, and do not expose MariaDB publicly.
