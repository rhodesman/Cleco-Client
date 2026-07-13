# Cleco Sites — Single Static Docker App

**Date:** 2026-07-13
**Status:** Approved design, pending implementation plan

## Goal

Combine the three Cleco product marketing sites (NeoTek, Grinder, CellCore) into a
single Docker container that serves all three behind one subdomain, with a portfolio
landing page as the entry point. The container runs on a home-network server; a reverse
proxy points a subdomain at it.

This replaces the current broken setup (NeoTek served directly, CSS/JS failing to load
because of relative asset paths and a non-executing PHP router).

## Requirements

- **Single entry point:** a landing page at `/` with three cards linking to each site.
- **All three sites reachable and rendering correctly** (CSS/JS/images/video/fonts load).
- **All language versions preserved and reachable** (NeoTek EN/DE/ES/CN/UK; CellCore
  US/EU + de/es/zh; Grinder US/EU/DE).
- **Static only** — no PHP, no database. Forms display and run their client-side
  validation but do not submit anywhere (server-side handlers are removed).
- **Source repo unchanged** — the three site folders are not modified; all assembly and
  rewriting happens inside the Docker build.
- **No production credentials in the image** — the `.php` mailers contain hardcoded prod
  MySQL credentials and must never be shipped (nginx would serve them as plaintext).

## Non-Goals

- Making contact/demo forms actually submit or store leads.
- Running the CellCore Gulp/SCSS build (its compiled `build/` output is already committed).
- Self-hosting third-party CDN assets (jQuery, Bootstrap, FontAwesome) — they load from
  the internet, which the host has.
- Domain-based locale routing (replaced by path-based access).

## Architecture

Two-stage Docker build producing one small `nginx:alpine` container.

### Stage 1 — builder (`alpine`)

Runs `scripts/assemble.sh`, which produces a clean `/site` tree:

1. **Copy servable files only** from each source folder — HTML, committed `build/` output,
   `assets/`, `images/`, `video/`, `Fonts/`/`webfonts/`, `pdf/`, `360_assets/`, `js/`, css.
   **Exclude:** `node_modules/`, `src/`, `gulpfile.js`, `package*.json`, `package-lock.json`,
   `.git`, `error_log`, and **every `*.php` file**.
2. **Rename CellCore pages:** `index-us.php` → `index.html`, `index-eu.php` → `eu.html`
   (their PHP is entirely commented out — effectively static HTML). Same for each nested
   language copy under `de/`, `es/`, `zh/`.
3. **Rewrite CellCore absolute paths** (see below).
4. **Neutralize dead form actions** (see Forms).
5. **Drop in the landing page** at `/site/index.html` plus its CSS.

### Stage 2 — runtime (`nginx:alpine`)

- `COPY --from=builder /site /usr/share/nginx/html`
- `COPY nginx.conf /etc/nginx/conf.d/default.conf`
- Final image: static tree + nginx only. No PHP, no credentials.

## Container URL Layout

```
/                     landing page (3 product cards)
/neotek/              index.html (EN) + index-Deutsch.html, index-spanish.html,
                      index-chinese.html, index-uk.html; assets/, images/, 360_assets/
/grinder/             index.html (US) + europe.html, deutsch.html; style.css, js/,
                      images/, video/, pdf/, Fonts/
/cellcore/            index.html (was index-us.php) + eu.html (was index-eu.php); build/
/cellcore/de/         German copy: index.html + build/
/cellcore/es/         Spanish copy: index.html + build/
/cellcore/zh/         Chinese copy: index.html + build/
```

nginx auto-redirects `/neotek` → `/neotek/` (directory + index), which makes NeoTek's and
Grinder's **relative** asset paths resolve correctly. Language versions remain reachable
via each site's existing in-page language switcher and by direct URL, so we do **not**
rewrite every internal link.

## Asset-Path Rewriting (`assemble.sh`)

Only **CellCore** uses absolute asset paths and needs rewrites. Applied per-directory so
nested language copies get the correct prefix:

| Source reference | Rewritten to (top level) | Inside `de/`, `es/`, `zh/` |
|---|---|---|
| `="/build/…"` | `="/cellcore/build/…"` | `="/cellcore/<lang>/build/…"` |
| home link `href="/"` | `href="/cellcore/"` | `href="/cellcore/<lang>/"` |

**NeoTek:** one fix — home link `href="/"` → `href="/neotek/"`. Its `assets/…` refs are
relative and need no change.

**Grinder:** no asset rewrites needed (relative paths + CDN links).

**CellCore language default resolution:** each of `de/`, `es/`, `zh/` is a full nested copy
containing both `index-us.php` and `index-eu.php`. During implementation, verify which file
holds the translated content and map it to that language's `index.html`. (Flagged as the
one detail requiring inspection during implementation.)

## Forms

Server-side handlers (`mailer.php`, `mailer-signup.php`, Grinder `confirm*.php`) are removed
with all other PHP. To keep the forms' client-side validation/verification UX intact while
ensuring a submit does nothing:

- Rewrite dead form `action` attributes to `#` (e.g. CellCore `action="mailer.php"` → `#`,
  Grinder-US `action="/thankyou/confirm1-en.php"` → `#`). Client-side JS validation
  (jQuery Validate, etc.) still runs and demonstrates; a valid submit is a no-op.
- **Leave untouched:** Grinder EU/DE forms already POST to external CleverReach
  (`https://eu.cleverreach.com/...`) — these stay as real, working external actions.

## Third-Party Scripts

Left as-is: jQuery/Bootstrap/FontAwesome/popper CDNs, plus tracking (`leadmanagerfx`,
CleverReach). They load over the internet. No offline support required.

## Landing Page

New self-contained static page at `/` — plain HTML + CSS, no framework. Three cards
(NeoTek, Grinder, CellCore) linking to `/neotek/`, `/grinder/`, `/cellcore/`, framed as a
portfolio piece ("Three product launch sites built for Cleco"). Intentional, non-templated
visual style. Lives in `landing/` in the repo, copied to `/site/` at build time.

## nginx Configuration

- `server` on port 80, root `/usr/share/nginx/html`.
- `location / { try_files $uri $uri/ =404; }`; rely on nginx directory-index redirects for
  trailing slashes.
- `gzip on` for text/css/js/html; long cache headers for static assets
  (images/video/fonts/css/js), no-cache for HTML.
- Optional: custom 404 → landing page.

## How It Runs

```bash
docker build -t cleco-sites .
docker run -d -p 8080:80 --restart unless-stopped cleco-sites
```

Plus a `docker-compose.yml` (port map + `restart: unless-stopped`) for the home server.
Reverse proxy points the subdomain → `localhost:8080`.

## Files Added to Repo

```
Dockerfile
nginx.conf
scripts/assemble.sh
landing/index.html
landing/assets/…            (landing page CSS/any images)
docker-compose.yml
.dockerignore
docs/superpowers/specs/2026-07-13-cleco-static-docker-design.md
```

The three existing site folders are **not modified**.

## Verification

1. `docker build` succeeds.
2. `find <image> -name '*.php'` returns nothing (no PHP shipped).
3. No DB-credential string present in the image (grep the built tree).
4. Every route returns HTTP 200 with correct content-type:
   `/`, `/neotek/`, `/grinder/`, `/cellcore/`, plus one language variant each
   (`/neotek/index-Deutsch.html`, `/grinder/europe.html`, `/cellcore/de/`).
5. CellCore assets resolve: `GET /cellcore/build/css/theme.min.css` → 200.
6. Browser smoke test: each site renders with CSS/JS/images; a language variant per site
   renders; a form shows client-side validation and does not navigate away on submit.

## Risks / Open Items

- **CellCore path rewriting** is the most fragile part; the rewrite must be verified against
  actual served pages (Verification #5), not just assumed.
- **CellCore language default file** mapping (`de/es/zh` → `index.html`) needs inspection.
- **`sed`-based HTML rewriting** must be scoped carefully to avoid over-matching; prefer
  anchored patterns (`="/build/`, `href="/"`) over broad substitutions.
