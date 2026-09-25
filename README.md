# DomainMonger WHMCS

DomainMonger production WHMCS customization source.

## Production Baseline

- WHMCS: 9.0.9-release.1
- PHP: 8.3
- Production site: domainmonger.com
- WHMCS path: /manage/
- Baseline date: 2026-09-25

This repository tracks DomainMonger customizations layered on top of WHMCS.
WHMCS core, runtime data, credentials, attachments, caches, and other generated
or sensitive data are not intended to be maintained in this repository.

## Active Templates

- Client template: stellar-software-integration-whmcs
- Order form template: standard_cart_2

## Important

The production installation contains historical development files and obsolete
patch iterations. The initial Git baseline should preserve the current custom
production source before cleanup.

Cleanup should be performed separately in controlled, testable changes after
the baseline has been committed.
