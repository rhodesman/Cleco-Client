# Cleco Sites — Single Static Docker App Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Serve all three Cleco marketing sites (NeoTek, Grinder, CellCore) — with every language variant — from one small static nginx Docker container behind a single subdomain.

**Architecture:** A two-stage Docker build. Stage 1 (Alpine) runs `scripts/assemble.sh`, which copies only servable files from the three source folders into a `/site` tree, drops all `.php` (mailers with prod credentials + routers), renames CellCore's commented-PHP pages to `.html`, rewrites CellCore's absolute `/build/` asset paths per-directory, fixes NeoTek's root link, and neutralizes local PHP form actions. Stage 2 (`nginx:alpine`) serves that tree. The source site folders are never modified.

**Tech Stack:** POSIX/bash shell + `rsync` + `sed` (assembly), Docker multi-stage build, nginx.

## Global Constraints

- **No PHP, no database in the image.** Every `*.php` file is excluded from the served tree. The mailers contain hardcoded production MySQL credentials (`clecocel_formUse` / `2RshO7iuSaoR`); these strings must not appear anywhere in the final image.
- **Source site folders unchanged.** `cleco_neotek_website/`, `cleco_grinder_website/`, `cleco_cellcore_website/` are read-only inputs. All transformation happens in `scripts/assemble.sh` output.
- **CellCore asset rule:** only **absolute** `/build/…` references are rewritten (relative `build/…` refs resolve correctly under the trailing-slash path). Per-directory prefix: `/cellcore/` for top-level US & EU pages, `/cellcore/de/` `…/es/` `…/zh/` for the language folders.
- **Forms:** rewrite any form `action` that targets a local `.php` handler to `action="#"`; leave external `http(s)://` actions (CleverReach) intact.
- **Portability:** the assembly script must run both on macOS (BSD sed, for local iteration) and Alpine (GNU/BusyBox sed, in Docker). Do not use `sed -i` (its syntax differs); use the provided `sed_inplace` helper that writes to a temp file and moves it.
- **CellCore US/EU is a GDPR axis, not a language axis:** top-level `index-us.php` → `/cellcore/`, `index-eu.php` → `/cellcore/eu/`. Both English; EU is the GDPR-consent variant.

---

### Task 1: Branch / index landing page

A minimal static page at the container root linking to the three sites. Kept intentionally plain — the user's real portfolio (built elsewhere) is the actual showcase; this only keeps the subdomain root from being empty. `scripts/assemble.sh` (Task 2) copies this file to `/site/index.html`, so it must exist first.

**Files:**
- Create: `landing/index.html`

**Interfaces:**
- Produces: `landing/index.html` containing anchor links with `href="/neotek/"`, `href="/grinder/"`, `href="/cellcore/"`. Task 2's assembly copies it verbatim to the tree root.

- [ ] **Step 1: Write the failing check**

Run: `test -f landing/index.html && grep -q 'href="/neotek/"' landing/index.html && grep -q 'href="/grinder/"' landing/index.html && grep -q 'href="/cellcore/"' landing/index.html && echo OK`
Expected: FAIL — prints nothing / `No such file or directory` (file does not exist yet).

- [ ] **Step 2: Create the landing page**

Create `landing/index.html` with exactly this content:

```html
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cleco — Product Sites</title>
  <style>
    :root { --accent: #e35205; --ink: #1a1a1a; --muted: #6b6b6b; }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 2.5rem;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background: #fafafa; color: var(--ink); padding: 2rem;
    }
    header { text-align: center; }
    header h1 { margin: 0 0 .5rem; font-size: 2rem; letter-spacing: -0.02em; }
    header p { margin: 0; color: var(--muted); }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; width: 100%; max-width: 780px; }
    a.card {
      display: block; padding: 1.75rem 1.5rem; border-radius: 12px; text-decoration: none;
      background: #fff; border: 1px solid #ececec; color: var(--ink);
      box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    a.card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.08); border-color: var(--accent); }
    a.card .name { font-size: 1.15rem; font-weight: 600; }
    a.card .desc { margin-top: .35rem; color: var(--muted); font-size: .9rem; }
    a.card .go { margin-top: 1rem; color: var(--accent); font-weight: 600; font-size: .85rem; }
    footer { color: var(--muted); font-size: .8rem; }
  </style>
</head>
<body>
  <header>
    <h1>Cleco Product Sites</h1>
    <p>Three product launch sites built for Cleco</p>
  </header>
  <nav class="cards">
    <a class="card" href="/neotek/">
      <div class="name">NeoTek</div>
      <div class="desc">Cordless assembly tool platform</div>
      <div class="go">View site →</div>
    </a>
    <a class="card" href="/grinder/">
      <div class="name">Grinder</div>
      <div class="desc">Industrial grinding tools</div>
      <div class="go">View site →</div>
    </a>
    <a class="card" href="/cellcore/">
      <div class="name">CellCore</div>
      <div class="desc">Smart cordless assembly system</div>
      <div class="go">View site →</div>
    </a>
  </nav>
  <footer>Portfolio demo — contact forms are inactive.</footer>
</body>
</html>
```

- [ ] **Step 3: Run the check to verify it passes**

Run: `test -f landing/index.html && grep -q 'href="/neotek/"' landing/index.html && grep -q 'href="/grinder/"' landing/index.html && grep -q 'href="/cellcore/"' landing/index.html && echo OK`
Expected: prints `OK`.

- [ ] **Step 4: Commit**

```bash
git add landing/index.html
git commit -m "Add minimal branch/index landing page for Cleco sites"
```

---

### Task 2: Assembly script (`scripts/assemble.sh`)

The core of the project: transforms the three source folders into a clean, servable `/site` tree. Testable standalone by running it against the repo into a temp dir and asserting on the output — no Docker required for this task.

**Files:**
- Create: `scripts/assemble.sh`

**Interfaces:**
- Consumes: `landing/index.html` (from Task 1); the three source folders `cleco_neotek_website/`, `cleco_grinder_website/`, `cleco_cellcore_website/`.
- Produces: a directory tree at the given `OUT_DIR` with this shape (relied on by Tasks 3–4):
  ```
  index.html                      (copied from landing/)
  neotek/index.html + language html + assets/ images/ 360_assets/
  grinder/index.html + europe.html deutsch.html + style.css js/ images/ video/ pdf/
  cellcore/index.html + build/
  cellcore/eu/index.html
  cellcore/de/index.html + build/   (also es/, zh/)
  ```
- Invocation contract: `bash scripts/assemble.sh <SRC_ROOT> <OUT_DIR>` — `SRC_ROOT` is the repo root, `OUT_DIR` is wiped and rebuilt.

- [ ] **Step 1: Write the failing check**

Run: `rm -rf /tmp/cleco-site && bash scripts/assemble.sh . /tmp/cleco-site && echo BUILT`
Expected: FAIL — `bash: scripts/assemble.sh: No such file or directory` (script does not exist yet).

- [ ] **Step 2: Create `scripts/assemble.sh`**

Create `scripts/assemble.sh` with exactly this content:

```bash
#!/usr/bin/env bash
# assemble.sh — Build a static /site tree from the three Cleco source sites.
# Usage: assemble.sh <SRC_ROOT> <OUT_DIR>
set -euo pipefail
shopt -s nullglob

SRC="${1:?usage: assemble.sh SRC_ROOT OUT_DIR}"
OUT="${2:?usage: assemble.sh SRC_ROOT OUT_DIR}"

NEOTEK_SRC="$SRC/cleco_neotek_website"
GRINDER_SRC="$SRC/cleco_grinder_website"
CELLCORE_SRC="$SRC/cleco_cellcore_website"

# Portable in-place sed (GNU sed in Alpine, BSD sed on macOS both work).
sed_inplace() {
  local expr="$1" file="$2"
  sed -E "$expr" "$file" > "$file.tmp"
  mv "$file.tmp" "$file"
}

# Copy a site tree, excluding source/build cruft and ALL php files.
copy_site() {
  local from="$1" to="$2"
  mkdir -p "$to"
  rsync -a \
    --exclude='node_modules/' \
    --exclude='src/' \
    --exclude='gulpfile.js' \
    --exclude='package.json' \
    --exclude='package-lock.json' \
    --exclude='.git/' \
    --exclude='.gitignore' \
    --exclude='.sass-cache/' \
    --exclude='error_log' \
    --exclude='*.php' \
    --exclude='*.scss' \
    --exclude='*.map' \
    --exclude='.DS_Store' \
    "$from"/ "$to"/
}

# Rewrite delimiter-anchored absolute refs to a site's OWN top-level directories
# (e.g. /images/ -> /neotek/images/) across its html/css/js. These sites were authored
# for a domain root, so their CSS/JS carry absolute /images/, /360_assets/, ... paths
# that break under a subpath. Anchoring on a preceding delimiter (" ' or () leaves any
# already-prefixed /<name>/<dir>/ untouched. CellCore is excluded — it has bespoke
# per-directory /build/ handling in rewrite_cellcore_page.
prefix_site_absolute_refs() {
  local dir="$1" name="$2" d base
  local exprs=()
  for d in "$dir"/*/; do
    base="$(basename "$d")"
    exprs+=(-e "s#([\"'(])/${base}/#\1/${name}/${base}/#g")
  done
  [ ${#exprs[@]} -eq 0 ] && return 0
  find "$dir" -type f \( -name '*.html' -o -name '*.css' -o -name '*.js' \) | while IFS= read -r f; do
    sed -E "${exprs[@]}" "$f" > "$f.tmp" && mv "$f.tmp" "$f"
  done
}

# Generate a CellCore page from its .php source, rewriting absolute /build/ refs.
# rewrite_cellcore_page <src.php> <dest.html> <asset_prefix> <home_prefix>
rewrite_cellcore_page() {
  local src="$1" dest="$2" asset_prefix="$3" home_prefix="$4"
  mkdir -p "$(dirname "$dest")"
  # Rewrite absolute /build/ refs regardless of the delimiter that precedes them:
  # " and ' for href/src/content attributes, ( for unquoted CSS url(/build/...).
  # Anchoring on a delimiter avoids re-rewriting an already-prefixed /cellcore/.../build/.
  sed -E \
    -e "s#([\"'(])/build/#\1${asset_prefix}build/#g" \
    -e "s#href=\"/\"#href=\"${home_prefix}\"#g" \
    "$src" > "$dest"
}

echo "==> Resetting $OUT"
rm -rf "$OUT"
mkdir -p "$OUT"

echo "==> Copying static sites (php excluded)"
copy_site "$NEOTEK_SRC"   "$OUT/neotek"
copy_site "$GRINDER_SRC"  "$OUT/grinder"
copy_site "$CELLCORE_SRC" "$OUT/cellcore"

echo "==> Generating CellCore pages (rename + absolute /build/ rewrite)"
rewrite_cellcore_page "$CELLCORE_SRC/index-us.php" "$OUT/cellcore/index.html"    "/cellcore/"    "/cellcore/"
rewrite_cellcore_page "$CELLCORE_SRC/index-eu.php" "$OUT/cellcore/eu/index.html" "/cellcore/"    "/cellcore/eu/"
rewrite_cellcore_page "$CELLCORE_SRC/de/index.php" "$OUT/cellcore/de/index.html" "/cellcore/de/" "/cellcore/de/"
rewrite_cellcore_page "$CELLCORE_SRC/es/index.php" "$OUT/cellcore/es/index.html" "/cellcore/es/" "/cellcore/es/"
rewrite_cellcore_page "$CELLCORE_SRC/zh/index.php" "$OUT/cellcore/zh/index.html" "/cellcore/zh/" "/cellcore/zh/"

echo "==> Rewriting absolute /build/ refs inside CellCore favicon manifests"
# theme.min.css/js use relative url()s, but site.webmanifest and browserconfig.xml
# carry absolute /build/favicons/... icon paths. Rewrite each to the prefix of the
# build/ dir it actually lives in, so PWA icons / MS tiles resolve under the subpath.
find "$OUT/cellcore" -type f \( -name '*.webmanifest' -o -name 'browserconfig.xml' \) | while IFS= read -r f; do
  rel="${f#"$OUT"/}"                     # e.g. cellcore/de/build/favicons/site.webmanifest
  build_path="${rel%%/build/*}/build/"   # e.g. cellcore/de/build/
  sed_inplace "s#/build/#/${build_path}#g" "$f"
done

echo "==> Prefixing absolute asset refs for NeoTek and Grinder (CSS/JS/HTML)"
prefix_site_absolute_refs "$OUT/neotek"  neotek
prefix_site_absolute_refs "$OUT/grinder" grinder

echo "==> Fixing NeoTek root links (href=\"/\" -> /neotek/)"
for f in "$OUT"/neotek/*.html; do
  sed_inplace 's#href="/"#href="/neotek/"#g' "$f"
done

echo "==> Neutralizing local PHP form actions (external http(s) actions left intact)"
find "$OUT" -name '*.html' | while IFS= read -r f; do
  sed_inplace 's|action="[^"]*\.php"|action="#"|g' "$f"
done

echo "==> Installing branch/index page at site root"
cp "$SRC/landing/index.html" "$OUT/index.html"

echo "==> Done. Static tree assembled at $OUT"
```

- [ ] **Step 3: Make it executable**

Run: `chmod +x scripts/assemble.sh`

- [ ] **Step 4: Run the assembly**

Run: `rm -rf /tmp/cleco-site && bash scripts/assemble.sh . /tmp/cleco-site && echo BUILT`
Expected: ends with `==> Done. Static tree assembled at /tmp/cleco-site` then `BUILT`.

- [ ] **Step 5: Verify NO php shipped**

Run: `find /tmp/cleco-site -name '*.php' | wc -l | tr -d ' '`
Expected: `0`

- [ ] **Step 6: Verify no credential strings leaked**

Run: `grep -rl '2RshO7iuSaoR' /tmp/cleco-site || echo CLEAN`
Expected: `CLEAN`

- [ ] **Step 7: Verify tree structure and page renames**

Run:
```bash
for p in index.html neotek/index.html neotek/index-Deutsch.html \
         grinder/index.html grinder/europe.html \
         cellcore/index.html cellcore/eu/index.html \
         cellcore/de/index.html cellcore/es/index.html cellcore/zh/index.html \
         cellcore/build/css/theme.min.css cellcore/de/build/js/theme.min.js; do
  test -f "/tmp/cleco-site/$p" && echo "ok $p" || echo "MISSING $p"
done
```
Expected: every line starts with `ok`, none `MISSING`.

- [ ] **Step 8: Verify CellCore absolute-path rewrites (per directory)**

Run:
```bash
echo "top-level should reference /cellcore/build/:"
grep -c '"/cellcore/build/' /tmp/cleco-site/cellcore/index.html
echo "eu page should reference /cellcore/build/ (shares top-level build):"
grep -c '"/cellcore/build/' /tmp/cleco-site/cellcore/eu/index.html
echo "de page should reference /cellcore/de/build/:"
grep -c '"/cellcore/de/build/' /tmp/cleco-site/cellcore/de/index.html
echo "NO unrewritten absolute /build/ should remain anywhere in cellcore:"
grep -rl '"/build/' /tmp/cleco-site/cellcore || echo CLEAN
```
Expected: the three counts are all greater than 0; the last line prints `CLEAN`.

- [ ] **Step 9: Verify form actions neutralized and external actions preserved**

Run:
```bash
echo "no local .php form actions should remain:"
grep -rho 'action="[^"]*\.php"' /tmp/cleco-site || echo NONE
echo "external CleverReach actions should still be present:"
grep -rl 'action="https://eu.cleverreach.com' /tmp/cleco-site >/dev/null && echo PRESENT || echo MISSING
```
Expected: first block prints `NONE`; second prints `PRESENT`.

- [ ] **Step 10: Verify NeoTek root link fixed**

Run: `grep -c 'href="/neotek/"' /tmp/cleco-site/neotek/index.html`
Expected: greater than 0 (at least one).

- [ ] **Step 11: Commit**

```bash
git add scripts/assemble.sh
git commit -m "Add assemble.sh: build static site tree from three Cleco sites"
```

---

### Task 3: Dockerfile, nginx config, and image-level checks

Wrap the assembly in a two-stage Docker build and serve with nginx. Deliverable: a built image that contains the static tree, no PHP, and no credentials. Requires Docker installed and running.

**Files:**
- Create: `nginx.conf`
- Create: `Dockerfile`
- Create: `.dockerignore`

**Interfaces:**
- Consumes: `scripts/assemble.sh` (Task 2), `landing/index.html` (Task 1).
- Produces: a Docker image tagged `cleco-sites` serving `/usr/share/nginx/html` on port 80. Task 4 runs it.

- [ ] **Step 1: Write the failing check**

Run: `docker build -t cleco-sites . && echo BUILT`
Expected: FAIL — `failed to read dockerfile` / `Dockerfile: no such file or directory` (no Dockerfile yet).

- [ ] **Step 2: Create `nginx.conf`**

Create `nginx.conf` with exactly this content:

```nginx
server {
    listen 80;
    server_name _;
    root /usr/share/nginx/html;
    index index.html;

    gzip on;
    gzip_comp_level 5;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/javascript application/json image/svg+xml;

    # Serve files; append trailing slash for directories (nginx 301 redirect).
    location / {
        try_files $uri $uri/ =404;
    }

    # Long cache for fingerprinted/static assets.
    location ~* \.(?:css|js|png|jpe?g|gif|svg|ico|woff2?|ttf|eot|otf|mp4|webm|pdf)$ {
        expires 30d;
        add_header Cache-Control "public";
    }

    # HTML should always revalidate.
    location ~* \.html?$ {
        add_header Cache-Control "no-cache";
    }
}
```

- [ ] **Step 3: Create `.dockerignore`**

Create `.dockerignore` with exactly this content:

```
.git
**/node_modules
**/.sass-cache
**/npm-debug.log
**/src
docs
*.md
```

- [ ] **Step 4: Create `Dockerfile`**

Create `Dockerfile` with exactly this content:

```dockerfile
# syntax=docker/dockerfile:1

# ---- Stage 1: assemble the static site tree ----
FROM alpine:3.20 AS builder
RUN apk add --no-cache bash rsync
WORKDIR /src
COPY . /src
RUN bash scripts/assemble.sh /src /site

# ---- Stage 2: serve with nginx ----
FROM nginx:1.27-alpine
COPY --from=builder /site /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
```

- [ ] **Step 5: Build the image**

Run: `docker build -t cleco-sites . && echo BUILT`
Expected: build completes, prints `BUILT`.

- [ ] **Step 6: Verify nginx config is valid inside the image**

Run: `docker run --rm cleco-sites nginx -t`
Expected: `syntax is ok` and `test is successful`.

- [ ] **Step 7: Verify no PHP and no credentials in the image**

Run:
```bash
docker run --rm cleco-sites sh -c "find /usr/share/nginx/html -name '*.php' | wc -l"
docker run --rm cleco-sites sh -c "grep -rl '2RshO7iuSaoR' /usr/share/nginx/html || echo CLEAN"
```
Expected: first prints `0`; second prints `CLEAN`.

- [ ] **Step 8: Commit**

```bash
git add nginx.conf .dockerignore Dockerfile
git commit -m "Add Dockerfile, nginx config, and .dockerignore for Cleco sites"
```

---

### Task 4: docker-compose and runtime route verification

Add a compose file for the home server and verify the running container serves every route and asset correctly. Requires Docker + docker compose.

**Files:**
- Create: `docker-compose.yml`

**Interfaces:**
- Consumes: the `cleco-sites` image / build context from Task 3.
- Produces: a running service on host port `8080`. Terminal deliverable; nothing depends on it.

- [ ] **Step 1: Write the failing check**

Run: `test -f docker-compose.yml && echo OK`
Expected: FAIL — prints nothing (no compose file yet).

- [ ] **Step 2: Create `docker-compose.yml`**

Create `docker-compose.yml` with exactly this content:

```yaml
services:
  cleco-sites:
    build: .
    image: cleco-sites:latest
    container_name: cleco-sites
    ports:
      - "8080:80"
    restart: unless-stopped
```

- [ ] **Step 3: Start the container**

Run: `docker compose up -d --build && sleep 3 && echo UP`
Expected: service starts; prints `UP`.

- [ ] **Step 4: Verify every route returns 200**

Run:
```bash
for path in / /neotek/ /grinder/ /cellcore/ /cellcore/eu/ /cellcore/de/ /cellcore/es/ /cellcore/zh/ \
            /neotek/index-Deutsch.html /grinder/europe.html; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:8080$path")
  echo "$code  $path"
done
```
Expected: every line begins with `200`.

- [ ] **Step 5: Verify CellCore assets resolve (absolute-path rewrite works at runtime)**

Run:
```bash
for path in /cellcore/build/css/theme.min.css /cellcore/de/build/js/theme.min.js \
            /cellcore/de/build/favicons/favicon-32x32.png; do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:8080$path")
  echo "$code  $path"
done
```
Expected: every line begins with `200`.

- [ ] **Step 6: Verify directory trailing-slash redirect works**

Run: `curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/neotek`
Expected: `301` (nginx redirects `/neotek` → `/neotek/`, which is what makes NeoTek's relative asset paths resolve).

- [ ] **Step 7: Browser smoke test (check the network panel, not just the eye)**

Load each site in a real browser and inspect the network requests for any **local**
(`localhost:8080`) response that is not `200`/`206`/`301`. This catches absolute asset paths
that a static grep of HTML misses — the sites carry absolute `/images/`, `/360_assets/`, and
`/build/` refs in their **CSS** (and `url("/…)` / `url(/360…)` forms), which is exactly the
class of bug that broke the original deploy.

**Cache caveat:** `nginx.conf` sets `expires 30d` on assets, so after rebuilding the image
you MUST hard-reload (ignore cache) or you will see stale-CSS 404s that are not real. Verify
with a cache-bypassing reload.

Confirm for `/`, `/neotek/`, `/grinder/`, `/cellcore/`, and one language variant each
(`/cellcore/de/`, `/neotek/index-Deutsch.html`, `/grinder/europe.html`):
- No local request returns 404 (206 for video range requests and 301 for directory
  redirects are expected and fine; external tracker/ads failures are irrelevant).
- Each site renders with CSS, JS, images, and (Grinder/NeoTek) video.
- A contact/demo form validates on submit and does **not** navigate away or 404.

Expected: zero local 404s on every page; all sites and language variants render; forms
validate but do nothing on submit.

- [ ] **Step 8: Stop the test container**

Run: `docker compose down`
Expected: container stops and is removed.

- [ ] **Step 9: Commit**

```bash
git add docker-compose.yml
git commit -m "Add docker-compose for Cleco sites container"
```

---

## Deployment note (after all tasks)

On the home server: `docker compose up -d --build` (or `docker run -d -p 8080:80 --restart unless-stopped cleco-sites`), then point the reverse proxy for the chosen subdomain at `http://<host>:8080`. The container needs outbound internet so the sites' CDN assets (jQuery, Bootstrap, FontAwesome) and tracking/CleverReach scripts load.

## Self-Review Notes

- **Spec coverage:** landing page (Task 1) ✓; static assembly + php exclusion + credential safety (Task 2, steps 5–6) ✓; CellCore GDPR US/EU split + per-directory `/build/` rewrite (Task 2, steps 7–8) ✓; all languages present/reachable (Task 2 step 7, Task 4 step 4) ✓; form neutralization keeping external actions (Task 2 step 9) ✓; NeoTek root-link fix (Task 2 step 10) ✓; nginx config + trailing-slash behavior (Task 3, Task 4 step 6) ✓; Docker two-stage build (Task 3) ✓; compose + run instructions (Task 4, deployment note) ✓.
- **Type/name consistency:** `assemble.sh` invocation `SRC_ROOT OUT_DIR`, helper names `copy_site` / `rewrite_cellcore_page` / `sed_inplace`, image tag `cleco-sites`, and port `8080:80` are used consistently across Tasks 2–4.
