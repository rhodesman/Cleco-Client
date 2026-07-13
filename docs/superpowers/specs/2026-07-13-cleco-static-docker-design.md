# Cleco Sites — Single Static Docker App

**Date:** 2026-07-13
**Status:** Approved design, pending implementation plan

## Goal

Combine the three Cleco product marketing sites (NeoTek, Grinder, CellCore) into a
single Docker container that serves all three behind one subdomain, with a minimal
branch/index page at the root. The container runs on a home-network server; a reverse
proxy points a subdomain at it.

This replaces the current broken setup (NeoTek served directly, CSS/JS failing to load
because of relative asset paths and a non-executing PHP router).

## Requirements

- **Single entry point:** a minimal branch/index page at `/` with three links, one per
  site. This is *not* a designed portfolio piece — the user already has a portfolio page
  (built by a counterpart) that links into these sites; `/` is just a plain fallback index
  so the subdomain root isn't empty.
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
2. **Rename CellCore pages:** `index-us.php` → `index.html`, `index-eu.php` →
   `eu/index.html`, and each `de|es|zh/index.php` → `<lang>/index.html` (their PHP is
   entirely commented out — effectively static HTML). See CellCore page-file mapping below.
3. **Rewrite CellCore absolute paths** (see below).
4. **Neutralize dead form actions** (see Forms).
5. **Drop in the landing page** at `/site/index.html` plus its CSS.

### Stage 2 — runtime (`nginx:alpine`)

- `COPY --from=builder /site /usr/share/nginx/html`
- `COPY nginx.conf /etc/nginx/conf.d/default.conf`
- Final image: static tree + nginx only. No PHP, no credentials.

## Container URL Layout

```
/                     minimal branch/index page (3 links)
/neotek/              index.html (EN) + index-Deutsch.html, index-spanish.html,
                      index-chinese.html, index-uk.html; assets/, images/, 360_assets/
/grinder/             index.html (US) + europe.html, deutsch.html; style.css, js/,
                      images/, video/, pdf/, Fonts/
/cellcore/            index.html (was index-us.php, US English); build/
/cellcore/eu/         index.html (was index-eu.php, EU English / GDPR variant); build/
/cellcore/de/         German page: index.html (was de/index.php) + build/
/cellcore/es/         Spanish page: index.html (was es/index.php) + build/
/cellcore/zh/         Chinese page: index.html (was zh/index.php) + build/
```

**CellCore US/EU is a GDPR axis, not a language axis.** Only the top-level English page has
two variants: `index-us.php` (simple opt-in → `mailer.php`) and `index-eu.php` (full GDPR
treatment: cookie-consent, reCAPTCHA, explicit consent checkbox, CleverReach form,
`.co.uk` privacy policy). Both are exposed (`/cellcore/` and `/cellcore/eu/`) — the GDPR
variant is a nice thing to showcase. The `de/`, `es/`, `zh/` language folders each contain a
single `index.php` page (no us/eu split) that renames straight to `index.html`.

nginx auto-redirects `/neotek` → `/neotek/` (directory + index), which makes NeoTek's and
Grinder's **relative** asset paths resolve correctly. Language versions remain reachable
via each site's existing in-page language switcher and by direct URL, so we do **not**
rewrite every internal link.

## Asset-Path Rewriting (`assemble.sh`)

Only **CellCore** uses absolute asset paths and needs rewrites. **CellCore mixes absolute
and relative references within the same file** (e.g. `de/index.php` uses relative
`build/css/theme.min.css` but absolute `/build/favicons/...`). Relative refs resolve
correctly under the trailing-slash path and need no change; only **absolute** refs are
rewritten. Applied per-directory so each page gets the correct prefix for its location:

| Source reference (absolute only) | Rewritten to |
|---|---|
| `="/build/…"` at `/cellcore/` | `="/cellcore/build/…"` |
| `="/build/…"` in `eu/`, `de/`, `es/`, `zh/` | `="/cellcore/<sub>/build/…"` |
| home link `href="/"` | site's subpath root (`/cellcore/`, `/cellcore/de/`, …) |

The rewrite must scan for absolute `/build/` **anywhere** in the file (css, js, favicons,
manifest, images) — do not assume a page uses one convention throughout.

**NeoTek:** one fix — home link `href="/"` → `href="/neotek/"`. Its `assets/…` refs are
relative and need no change.

**Grinder:** no asset rewrites needed (relative paths + CDN links).

**CellCore page-file mapping:** top-level `index-us.php` → `index.html`, `index-eu.php` →
`eu/index.html`; `de|es|zh/index.php` → `<lang>/index.html`. No us/eu resolution is needed
in the language folders — each has a single page file.

## Forms

Server-side handlers (`mailer.php`, `mailer-eu.php`, `mailer-signup.php`, Grinder
`confirm*.php`) are removed with all other PHP. To keep the forms' client-side
validation/verification UX intact while ensuring a submit does nothing:

**Rule:** rewrite form `action`s that target a **removed local `.php` handler** to `#`;
**leave external `http(s)://` actions intact.** Client-side JS validation (jQuery Validate,
reCAPTCHA, CleverReach `cr_form` checks) still runs and demonstrates; a valid submit to a
neutralized form is a no-op.

Concretely:
- Neutralize → `#`: CellCore `mailer.php` / `mailer-eu.php` / `mailer-signup.php` demo &
  signup forms (all pages incl. `eu/`, `de/`, `es/`, `zh/`); Grinder-US
  `/thankyou/confirm*.php`.
- Leave real: CellCore EU/DE newsletter forms and Grinder EU/DE forms already POST to
  external CleverReach (`https://eu.cleverreach.com/...`).

## Third-Party Scripts

Left as-is: jQuery/Bootstrap/FontAwesome/popper CDNs, plus tracking (`leadmanagerfx`,
CleverReach). They load over the internet. No offline support required.

## Branch / Index Page

Minimal self-contained static page at `/` — plain HTML + a little inline CSS, no framework,
no heavy design. Just the three site names as links to `/neotek/`, `/grinder/`,
`/cellcore/`. Its only job is to keep the subdomain root from being empty; the user's real
portfolio page (built elsewhere) is what actually showcases the work and links into these
sites. Lives in `landing/index.html` in the repo, copied to `/site/index.html` at build
time.

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
landing/index.html          (minimal branch page, inline CSS)
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
   `/`, `/neotek/`, `/grinder/`, `/cellcore/`, `/cellcore/eu/`, plus one language variant
   each (`/neotek/index-Deutsch.html`, `/grinder/europe.html`, `/cellcore/de/`).
5. CellCore assets resolve from every variant, including favicons (absolute-path refs):
   `GET /cellcore/build/css/theme.min.css` → 200 and
   `GET /cellcore/de/build/js/theme.min.js` → 200.
6. Browser smoke test: each site renders with CSS/JS/images; a language variant per site
   renders; a form shows client-side validation and does not navigate away on submit.

## Risks / Open Items

- **CellCore path rewriting** is the most fragile part; pages mix absolute and relative
  `/build/` refs, so the rewrite must catch absolute refs anywhere (css, js, favicons,
  manifest) and be verified against actual served pages (Verification #5), not assumed.
- **`sed`-based HTML rewriting** must be scoped carefully to avoid over-matching; prefer
  anchored patterns (`="/build/`, `href="/"`) over broad substitutions, and apply the
  correct per-directory prefix (`/cellcore/` vs `/cellcore/eu/`, `/cellcore/de/`, …).
