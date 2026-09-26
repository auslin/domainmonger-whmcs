#!/usr/bin/env python3
"""Non-destructive FTPS overlay uploader for DomainMonger staging deployments."""

from __future__ import annotations

import argparse
import os
import ssl
from ftplib import FTP_TLS, error_perm
from pathlib import Path, PurePosixPath


DEFAULT_EXCLUDED_NAMES = {".DS_Store", ".directory", "error_log"}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("local_dir", help="Local directory tree to upload")
    parser.add_argument("remote_dir", help="FTP-root-relative destination directory")
    parser.add_argument(
        "--delete-exact",
        action="append",
        default=[],
        help="Delete one exact FTP-root-relative metadata file after upload",
    )
    return parser.parse_args()


def connect() -> FTP_TLS:
    host = os.environ["SERVER_IP"]
    user = os.environ["SERVER_USER"]
    password = os.environ["SERVER_PASSWORD"]
    port = int(os.environ.get("SERVER_FTPS_PORT", "21"))

    # This matches the prior FTP-Deploy-Action default 'loose' TLS behavior.
    # The deployment accounts are jailed to staging-only trees.
    context = ssl._create_unverified_context()

    ftp = FTP_TLS(context=context, timeout=45)
    ftp.connect(host=host, port=port)
    ftp.login(user=user, passwd=password)
    ftp.prot_p()
    ftp.set_pasv(True)
    return ftp


def normalize_remote(path: str) -> PurePosixPath:
    cleaned = path.strip()
    while cleaned.startswith("./"):
        cleaned = cleaned[2:]
    cleaned = cleaned.strip("/")
    return PurePosixPath(cleaned or ".")


def ensure_remote_dir(ftp: FTP_TLS, ftp_root: str, remote_dir: PurePosixPath) -> None:
    ftp.cwd(ftp_root)
    if str(remote_dir) in ("", "."):
        return

    for part in remote_dir.parts:
        if part in ("", "."):
            continue
        try:
            ftp.cwd(part)
        except error_perm as exc:
            if not str(exc).startswith("550"):
                raise
            ftp.mkd(part)
            ftp.cwd(part)


def upload_tree(ftp: FTP_TLS, local_dir: Path, remote_dir: PurePosixPath) -> int:
    if not local_dir.is_dir():
        raise SystemExit(f"Local deployment directory does not exist: {local_dir}")

    ftp_root = ftp.pwd()
    uploaded = 0

    for source in sorted(local_dir.rglob("*")):
        if not source.is_file():
            continue
        if source.name in DEFAULT_EXCLUDED_NAMES:
            continue

        rel = PurePosixPath(source.relative_to(local_dir).as_posix())
        target = remote_dir / rel
        ensure_remote_dir(ftp, ftp_root, target.parent)

        with source.open("rb") as handle:
            ftp.storbinary(f"STOR {target.name}", handle)

        print(f"UPLOAD {target}")
        uploaded += 1

    ftp.cwd(ftp_root)
    return uploaded


def delete_exact_metadata(ftp: FTP_TLS, paths: list[str]) -> None:
    if not paths:
        return

    ftp_root = ftp.pwd()
    for raw in paths:
        target = normalize_remote(raw)
        parent = target.parent
        name = target.name

        try:
            ensure_remote_dir(ftp, ftp_root, parent)
            ftp.delete(name)
            print(f"REMOVED OLD DEPLOYMENT METADATA {target}")
        except error_perm as exc:
            if str(exc).startswith("550"):
                print(f"OLD DEPLOYMENT METADATA NOT PRESENT {target}")
            else:
                raise
        finally:
            ftp.cwd(ftp_root)


def main() -> None:
    args = parse_args()
    local_dir = Path(args.local_dir).resolve()
    remote_dir = normalize_remote(args.remote_dir)

    ftp = connect()
    try:
        uploaded = upload_tree(ftp, local_dir, remote_dir)
        delete_exact_metadata(ftp, args.delete_exact)
        print(f"Overlay upload complete: {uploaded} files uploaded; no application files deleted.")
    finally:
        try:
            ftp.quit()
        except Exception:
            ftp.close()


if __name__ == "__main__":
    main()
