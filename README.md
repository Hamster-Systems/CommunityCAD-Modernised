# CommunityCAD

> [!WARNING]
> **DEVELOPMENT BRANCH — HIGHLY EXPERIMENTAL / NOT PRODUCTION-READY.**
> This repository is an independent development effort to modernise CommunityCAD. This branch contains work-in-progress changes that may be broken, incomplete, unreviewed or incompatible with existing data. Features, APIs, database schemas, deployment procedures and behaviour may change without notice. Some functionality may be incomplete, unstable or untested. **Do not deploy to a live/production CAD or rely on it for real emergency services.** Back up your data before testing upgrades. This project is not the official CommunityCAD release.

Welcome to Community CAD!

- **Website:** [communitycad.app](https://communitycad.app)
- **Documentation:** [communitycad.app/docs](https://communitycad.app/docks)
- **Suggestions/Bugs:** [GitHub - Issues](https://github.com/CommunityCAD/CommunityCAD/issues/new/choose)
- **Discord Link:** [communitycad.app/discord](https://communitycad.app/discord)

## Local development (Docker Compose)

The development setup discussed here uses two directories on the same Linux machine:

- `~/Documents/CommunityCAD-Modernised` — Git checkout containing Laravel, Blade templates, and frontend source.
- `~/Documents/CommunityCAD-Dev` — Docker Compose development environment.

Your own paths may differ. Make sure the Compose build context or bind mounts actually point to the checkout whose files you update. **Do not run `docker compose` from the Git checkout unless it contains your Compose file.**

### Update the development branch

After testing and merging a feature into `develop`:

```bash
cd ~/Documents/CommunityCAD-Modernised
git fetch origin
git switch develop
git pull --ff-only origin develop
```

To test an unmerged feature instead, switch to that feature branch and run `git pull --ff-only`. Check `git status -sb` first and preserve any local edits before switching branches.

### Compile the frontend (Node.js and npm available)

Laravel Mix generates both `public/js/app.js` and `public/css/app.css`. A Docker Compose app rebuild does **not necessarily** compile them.

```bash
cd ~/Documents/CommunityCAD-Modernised
npm ci
npm run dev
```

Use `npm run prod` for optimised assets when appropriate.

### Compile the frontend WITHOUT installing npm on the host

If the Linux VM has Docker but no Node.js/npm, run npm inside a disposable Node container:

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

This compiles assets in the Git checkout, not automatically inside a separately mounted Compose project. Ensure the app container uses these outputs.

### Rebuild and clear Laravel caches

```bash
cd ~/Documents/CommunityCAD-Dev

docker compose up -d --build --no-deps app
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan view:cache
```

**Database safety:** Back up the database before applying new migrations. Avoid `migrate:fresh`, destructive resets, and testing migrations against a live CAD.

### Verify that the running container has the right assets

```bash
cd ~/Documents/CommunityCAD-Modernised
sha256sum public/js/app.js public/css/app.css

cd ~/Documents/CommunityCAD-Dev
docker compose exec -T app sha256sum public/js/app.js public/css/app.css
```

If hashes differ, inspect the Compose build context, image contents, and bind mounts before investigating styling changes. In Firefox, hard-refresh the CAD with **Ctrl+Shift+R** after rebuilding assets.

The `document.querySelector(...) is null` error from the shared Markdown editor is commonly caused by a stale compiled `public/js/app.js`: the source `resources/js/app.js` already guards editor initialisation on pages without the editor.
