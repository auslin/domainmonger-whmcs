#!/usr/bin/env bash
set -euo pipefail
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
case " $* " in
    *" --approve-production "*) ;;
    *)
        echo "Production deployment refused: invoke this command with --approve-production only after explicit user approval." >&2
        exit 1
        ;;
esac
exec "$script_dir/deploy-whmcs.sh" production "$@"
