# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This repo holds three independent Cleco product marketing sites **plus a deployment layer that combines them into one static Docker/nginx container**, served under a single subdomain with path-based routing. The three source-site folders are treated as **read-only inputs**; all transformation for serving happens at build time in `scripts/assemble.sh`. The container is the primary deliverable — it's what runs in production.

| Source directory | Product | Original stack | Served at |
|-----------|---------|-------|-----------|
| `cleco_neotek_website/` | NeoTek | Static HTML + prebuilt SCSS assets | `/neotek/` (+ `index-Deutsch/-spanish/-chinese/-uk.html`) |
| `cleco_grinder_website/` | Grinder | Static HTML/CSS (no build) | `/grinder/` (+ `europe.html`, `deutsch.html`) |
| `cleco_cellcore_website/` | CellCore | PHP pages + Gulp/SCSS, `de/es/zh` sub-copies + US/EU (GDPR) variants | `/cellcore/`, `/cellcore/eu/`, `/cellcore/de|es|zh/` |

Design + implementation are documented in `docs/superpowers/specs/2026-07-13-cleco-static-docker-design.md` and `docs/superpowers/plans/2026-07-13-cleco-static-docker.md`.

## The unified Docker app (primary workflow)

```bash
docker compose up -d --build          # build + run; serves on host port 8082
docker compose port cleco-sites 80    # confirm the mapped port
docker compose down                   # stop + remove
```

- **Two-stage build** (`Dockerfile`): stage 1 (`alpine` + bash/rsync/perl) runs `scripts/assemble.sh /src /site`; stage 2 (`nginx:alpine`) serves `/site` with `nginx.conf`. No PHP, no database, no credentials in the final image.
- **`docker-compose.yml`**: fixed host port **`8082:80`**, `restart: unless-stopped`, and `security_opt: [apparmor=unconfined]`. The apparmor setting is deliberate — without it the home-server host's periodic AppArmor reload unloads the container's profile and `docker compose up -d --build` fails with `cannot stop container: permission denied`.
- **Deployment**: runs on a home-network server (`git pull && docker compose up -d --build`), behind a reverse proxy + Cloudflare at `cleco.jasonrhodes.me`. Cloudflare caches aggressively — cold loads can show transient **522s** or briefly-missing fonts/images that self-resolve as the edge cache warms; don't chase those as container bugs. Needs outbound internet for the sites' CDN assets (jQuery/Bootstrap/FontAwesome).
- **`landing/index.html`** is a minimal branch page copied to `/` — the user's real portfolio links into the sites.

### What `assemble.sh` does (and why)

To fix or change served output, add/adjust a transformation here rather than editing the source-site folders (they stay pristine). Per-site conventions differ, so the transforms are targeted:

- **Strips every `*.php`** (mailers with hardcoded prod MySQL credentials + routers) so nothing sensitive ships and nginx never serves `.php` as plaintext.
- **CellCore**: renames `index-us.php`→`index.html`, `index-eu.php`→`eu/index.html`, `de|es|zh/index.php`→`<lang>/index.html`; un-comments the `<?php /* … */ ?>` video-gallery and cordless blocks (they render broken otherwise); rewrites **absolute** `/build/…` refs to the correct per-directory prefix (`/cellcore/build/`, `/cellcore/de/build/`, …) including inside favicon manifests; upgrades the Monotype `@import` from `http://`→`https://` (HTTPS mixed-content fix).
- **NeoTek/Grinder** (`prefix_site_absolute_refs`): rewrites absolute refs to each site's own top-level dirs (e.g. NeoTek `main.css` `url(/images/…)` → `/neotek/images/…`); NeoTek also gets `href="/"`→`/neotek/`.
- **All sites**: neutralizes local `.php` form actions to `#` (keeps external CleverReach actions); removes analytics/marketing tracker `<script>` blocks (GTM + its injected LinkedIn/Bing/DoubleClick/GA, leadmanagerfx) while keeping YouTube embeds; excludes source cruft (`node_modules/`, `src/`, `*.scss`, `*.map`, gulp/npm files).

**Asset-path gotcha:** the sites were authored for a domain root, so they carry absolute paths in HTML *and* CSS (and mixed `"/build/"` vs `url(/build/…)` forms). When something 404s under a subpath, it's almost always an absolute ref that needs prefixing in `assemble.sh` — verify by loading the page and checking the network panel for local non-200s (206 for video and 301 for dir redirects are fine).

## Editing the source sites

The three folders are independent — no shared build, no root `package.json`, no cross-site deps. Changes in one never affect the others. Edit these when you need to change actual site *content*; the container picks it up on the next `assemble.sh` run.

- **CellCore** — the only site with tooling: **Gulp 3** (legacy). Authored source in `src/`; compiled output committed to `build/` (which is what gets served — `assemble.sh` does **not** run Gulp). SCSS is glob-imported (`gulp-sass-glob`) across `base/helpers/components/vendor/`; JS concat order is fixed (`libs/* → components/* → src/js/*`). Never hand-edit `build/`. Gulp 3 needs an older Node toolchain.
  ```bash
  cd cleco_cellcore_website && npm install && npx gulp   # default = watch
  ```
  CellCore's `index-us.php`/`index-eu.php` are effectively static HTML (their PHP is entirely `<?php /* … */ ?>` comment blocks). US vs EU is a **GDPR/consent axis**, not language: EU adds cookie-consent, reCAPTCHA, an explicit-consent CleverReach form, and a `.co.uk` privacy policy.
- **Grinder** — no build. `style.css` is authored; `style-min.css` is a committed minified copy (keep in sync by hand). Region pages are independent full-page copies — apply shared changes to each.
- **NeoTek** — no working in-repo build (`bootstrap-gulp-scss-kit/` is an empty placeholder; edit `assets/css/main.css` directly). Product specs are data-driven from `assets/js/productSpecs.json` / `productSpecs-de.json`. Language versions are sibling `index-*.html` files.

## Source-side PHP (not served, kept for reference)

The `.php` files are excluded from the container but remain in the repo. Mailers (`cleco_cellcore_website/mailer*.php`, Grinder `thankyou/confirm*.php`) open a `mysqli` connection with **hardcoded production DB credentials** — don't leak them in output and prefer not to commit new ones (rotating them is advisable since they're in git history). `rest_client.php` is a standalone `CR\tools\rest` JWT/webauth REST client. The original locale routing used domain-based `index.php` front controllers (`$_SERVER['HTTP_HOST']` switch) — now replaced by path-based routing in the container.

## Testing / verification

No unit-test suite. Verification is the container itself: build it, then confirm every route returns 200, no `.php` or credential strings ship in the image, absolute assets resolve under their subpath, and each site renders in a browser with no local non-200s. The full verification checklist is in the implementation plan under `docs/superpowers/plans/`. When re-testing after a rebuild, **hard-refresh** — nginx sets `expires 30d` on assets, so stale CSS can otherwise mask a fix.
