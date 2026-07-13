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

# Remove analytics/marketing tracker <script>/<noscript> blocks from served HTML.
# Deletes any script/noscript block whose contents reference a known tracker domain
# (GTM, which also injects LinkedIn/Bing/DoubleClick/GA at runtime; plus leadmanagerfx
# and the LinkedIn insight tag). YouTube product embeds are intentionally left intact.
strip_trackers() {
  local f
  find "$OUT" -name '*.html' | while IFS= read -r f; do
    perl -0777 -pe '
      s{<script\b[^>]*>(?:(?!</script>).)*?(?:googletagmanager|leadmanagerfx|licdn\.com|_linkedin_partner_id|bat\.bing\.com|doubleclick|google-analytics)(?:(?!</script>).)*?</script>}{}gis;
      s{<script\b[^>]*\bsrc\s*=\s*[\x22\x27][^\x22\x27]*(?:googletagmanager|leadmanagerfx|licdn\.com|bat\.bing\.com|doubleclick|google-analytics)[^\x22\x27]*[\x22\x27][^>]*>\s*</script>}{}gis;
      s{<noscript>(?:(?!</noscript>).)*?googletagmanager(?:(?!</noscript>).)*?</noscript>}{}gis;
    ' "$f" > "$f.tmp" && mv "$f.tmp" "$f"
  done
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

echo "==> Removing analytics/marketing tracker script blocks"
strip_trackers

echo "==> Installing branch/index page at site root"
cp "$SRC/landing/index.html" "$OUT/index.html"

echo "==> Done. Static tree assembled at $OUT"
