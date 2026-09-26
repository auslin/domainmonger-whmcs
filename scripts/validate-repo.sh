#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"

fail=0

echo "== Bash syntax =="
while IFS= read -r file; do
    [[ -n "$file" ]] || continue
    echo "CHECK $file"
    if ! bash -n "$file"; then
        fail=1
    fi
done < <(git ls-files 'scripts/*.sh')

echo "== Python syntax =="
while IFS= read -r file; do
    [[ -n "$file" ]] || continue
    echo "CHECK $file"
    if ! python3 - "$file" <<'PY'
import pathlib
import sys

path = pathlib.Path(sys.argv[1])
source = path.read_text(encoding="utf-8")
compile(source, str(path), "exec")
PY
    then
        fail=1
    fi
done < <(git ls-files 'scripts/*.py')

echo "== PHP syntax =="
command -v php >/dev/null 2>&1 || {
    echo "php CLI is required for repository validation." >&2
    exit 1
}

php_count=0
php_skipped=0

while IFS= read -r file; do
    [[ -n "$file" ]] || continue

    if head -c 8192 "$file" | grep -Eqi 'ionCube|ioncube_loader'; then
        echo "SKIP encoded PHP $file"
        php_skipped=$((php_skipped + 1))
        continue
    fi

    echo "CHECK $file"
    php_count=$((php_count + 1))
    if ! php -l "$file" >/dev/null; then
        fail=1
    fi
done < <(git ls-files '*.php')

echo "PHP source files checked: $php_count"
echo "Encoded PHP files skipped: $php_skipped"

if [[ "$fail" -ne 0 ]]; then
    echo "Repository validation failed." >&2
    exit 1
fi

echo "Repository validation passed."
