#!/usr/bin/env bash
set -euo pipefail

usage() {
    echo "Usage: $0 <staging|production> [--dry-run] [--approve-production] [--include-integration]" >&2
    exit 2
}

target_name="${1:-}"
[[ "$target_name" == "staging" || "$target_name" == "production" ]] || usage
shift || true

dry_run=0
approve_production=0
include_integration=0

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run) dry_run=1 ;;
        --approve-production) approve_production=1 ;;
        --include-integration) include_integration=1 ;;
        *) usage ;;
    esac
    shift
done

if [[ "$target_name" == "staging" && "$approve_production" -eq 1 ]]; then
    echo "--approve-production is valid only for production." >&2
    exit 2
fi

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd "$script_dir/.." && pwd)"

git_repo() {
    git -c safe.directory="$repo_root" -C "$repo_root" "$@"
}

git_repo rev-parse --is-inside-work-tree >/dev/null

if [[ -n "$(git_repo status --porcelain)" ]]; then
    echo "Deployment requires a clean tracked Git working tree." >&2
    exit 1
fi

case "$target_name" in
    staging)
        target_root="/home/register/staging/domainmonger.com/public_html/manage"
        compiled_dir="/home/register/staging/whmcsdata/templates_c"
        ;;
    production)
        target_root="/home/register/public_html/manage"
        compiled_dir="/home/register/templates_c"

        [[ "$approve_production" -eq 1 ]] || {
            echo "Production deployment refused: explicit --approve-production is required." >&2
            exit 1
        }

        current_branch="$(git_repo rev-parse --abbrev-ref HEAD)"
        [[ "$current_branch" == "main" ]] || {
            echo "Production deployment requires main; current branch is: $current_branch" >&2
            exit 1
        }

        local_head="$(git_repo rev-parse HEAD)"
        origin_main="$(git_repo rev-parse origin/main)"
        [[ "$local_head" == "$origin_main" ]] || {
            echo "Production deployment requires HEAD to equal origin/main." >&2
            exit 1
        }
        ;;
esac

[[ "$EUID" -eq 0 ]] || {
    echo "Deployment must run as root; runtime file writes are executed as register." >&2
    exit 1
}

[[ -d "$target_root" ]] || {
    echo "Target WHMCS root is missing: $target_root" >&2
    exit 1
}

[[ -d "$compiled_dir" ]] || {
    echo "WHMCS compiled-template directory is missing: $compiled_dir" >&2
    exit 1
}

managed_list="$(mktemp)"
root_custom_list="$(mktemp)"
filtered_list="$(mktemp)"
trap 'rm -f "$managed_list" "$root_custom_list" "$filtered_list"' EXIT

git_repo ls-files     | grep -E '^(autoauth|ds|includes|modules|order|templates|username)/|^lang/overrides/english\.php$'     > "$managed_list"

if [[ "$include_integration" -eq 0 ]]; then
    grep -v '^templates/stellar-software-integration-whmcs/integration/' "$managed_list" > "$filtered_list" || true
    mv "$filtered_list" "$managed_list"
fi

git_repo ls-files 'root-custom/*' > "$root_custom_list"
chmod 644 "$managed_list" "$root_custom_list"

[[ -s "$managed_list" || -s "$root_custom_list" ]] || {
    echo "No managed WHMCS runtime files were found." >&2
    exit 1
}

write_manifest() {
    local output="$1"
    : > "$output"

    while IFS= read -r rel; do
        [[ -n "$rel" ]] || continue
        local src="$target_root/$rel"
        if [[ -f "$src" ]]; then
            printf '%s  %s\n' "$(sha256sum "$src" | awk '{print $1}')" "$rel" >> "$output"
        else
            printf 'MISSING  %s\n' "$rel" >> "$output"
        fi
    done < "$managed_list"

    while IFS= read -r repo_rel; do
        [[ -n "$repo_rel" ]] || continue
        local name="${repo_rel#root-custom/}"
        local src="$target_root/$name"
        if [[ -f "$src" ]]; then
            printf '%s  ROOT:%s\n' "$(sha256sum "$src" | awk '{print $1}')" "$name" >> "$output"
        else
            printf 'MISSING  ROOT:%s\n' "$name" >> "$output"
        fi
    done < "$root_custom_list"
}

backup_root=""
backup_production() {
    local stamp short_commit
    stamp="$(date +%Y%m%d-%H%M%S)"
    short_commit="$(git_repo rev-parse --short HEAD)"
    backup_root="/home/register/production-backups/whmcs-release-${stamp}-${short_commit}"

    mkdir -p "$backup_root/files" "$backup_root/root-custom"

    while IFS= read -r rel; do
        [[ -n "$rel" ]] || continue
        local src="$target_root/$rel"
        if [[ -e "$src" || -L "$src" ]]; then
            mkdir -p "$backup_root/files/$(dirname "$rel")"
            cp -a "$src" "$backup_root/files/$rel"
        else
            printf '%s\n' "$rel" >> "$backup_root/absent-before.txt"
        fi
    done < "$managed_list"

    while IFS= read -r repo_rel; do
        [[ -n "$repo_rel" ]] || continue
        local name="${repo_rel#root-custom/}"
        local src="$target_root/$name"
        if [[ -e "$src" || -L "$src" ]]; then
            mkdir -p "$backup_root/root-custom/$(dirname "$name")"
            cp -a "$src" "$backup_root/root-custom/$name"
        else
            printf 'ROOT:%s\n' "$name" >> "$backup_root/absent-before.txt"
        fi
    done < "$root_custom_list"

    write_manifest "$backup_root/manifest-before.sha256"

    cat > "$backup_root/release.txt" <<META
Project: DomainMonger WHMCS
Target: production
Source commit: $(git_repo rev-parse HEAD)
Source branch: $(git_repo rev-parse --abbrev-ref HEAD)
Created: $(date -Iseconds)
Integration fragments included: $include_integration
META

    echo "Production rollback bundle: $backup_root"
}

rsync_args=(-rlt --checksum --itemize-changes)
file_args=(-lt --checksum --itemize-changes)

if [[ "$dry_run" -eq 1 ]]; then
    rsync_args+=(--dry-run)
    file_args+=(--dry-run)
fi

target_runner=(sudo -u register)

echo "Deploy target: $target_name"
echo "Source commit: $(git_repo rev-parse --short HEAD)"
echo "Target root: $target_root"
echo "Mode: $([[ "$dry_run" -eq 1 ]] && echo dry-run || echo apply)"
echo "Integration fragments: $([[ "$include_integration" -eq 1 ]] && echo included || echo excluded)"

if [[ "$target_name" == "production" && "$dry_run" -eq 0 ]]; then
    backup_production
fi

"${target_runner[@]}" rsync "${rsync_args[@]}"     --files-from="$managed_list"     "$repo_root/"     "$target_root/"

while IFS= read -r repo_rel; do
    [[ -n "$repo_rel" ]] || continue
    name="${repo_rel#root-custom/}"
    "${target_runner[@]}" rsync "${file_args[@]}" "$repo_root/$repo_rel" "$target_root/$name"
done < "$root_custom_list"

if [[ "$dry_run" -eq 0 ]]; then
    find "$compiled_dir" -maxdepth 1 -type f ! -name index.php ! -name .htaccess -delete

    if [[ "$target_name" == "production" ]]; then
        write_manifest "$backup_root/manifest-after.sha256"
        echo "Production deployment completed from $(git_repo rev-parse --short HEAD)."
        echo "Rollback bundle retained at: $backup_root"
    else
        echo "Staging deployment completed from $(git_repo rev-parse --short HEAD)."
    fi
else
    echo "Dry-run completed; no runtime files, caches, or backups were changed."
fi
