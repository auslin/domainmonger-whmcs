# Changelog

## 1.17.1

- Removed the duplicate InvoiceFix heading from the addon administration page.
- Removed the divider and excess vertical space created by the redundant inner page header.
- Aligned System Check and Edit Text with the WHMCS page title on standard admin themes.
- Preserved a simple stacked action row on narrow screens and themes without the standard WHMCS title element.
- Added no invoice, payment, credit, transaction, ledger, table, or column changes.

## 1.17.0

- Added read-only **My Follow-Ups** and **Overdue** Work Queue filters and summary counts.
- My Follow-Ups shows unfinished relationships assigned to the signed-in administrator.
- Overdue shows unfinished relationships whose internal follow-up due date is before today.
- Excludes Completed relationships from both follow-up filters.
- Preserves both filters across search, date criteria, sorting, pagination, Details, Compare, and Work Queue CSV export.
- Added editable filter wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, service, domain, ledger, table, or column changes.

## 1.16.0

- Added an administrator-only follow-up owner and optional internal due date to Reissue Details.
- Stores assignment data only in `mod_invoicefix_log`; neither invoice due date is changed.
- Added compact Work Queue owner/due information and an Overdue badge for unfinished past-due follow-ups.
- Added follow-up data to Work Queue CSV and Audit CSV exports.
- Records the last updating administrator and time.
- Added WHMCS administrator Activity Log entries when follow-up information is saved or cleared.
- Added follow-up columns to System Check and the versioned upgrade routine.
- Added editable follow-up wording to the existing Edit Text popup.
- Added no WHMCS invoice, payment, credit, transaction, service, domain, or ledger changes.

## 1.15.0

- Added an administrator-only **Internal Reissue Note** to Reissue Details.
- Stores the note, last updating administrator, and update time only in `mod_invoicefix_log`.
- Added save and clear actions protected by InvoiceFix role access, invoice permissions, and WHMCS CSRF validation.
- Added WHMCS administrator Activity Log entries when a note is saved or cleared.
- Added internal-note data to the read-only Audit CSV export.
- Added note-column checks to System Check and the versioned upgrade routine.
- Added editable note wording to the existing Edit Text popup.
- Added no WHMCS invoice, payment, credit, transaction, or ledger changes.

## 1.14.0

- Added a dedicated read-only **Stale** Work Queue filter and summary count.
- Uses the existing configurable **Stale After Days** threshold.
- Includes unfinished Replacement Draft, Original Review, and Needs Attention records that meet the threshold.
- Excludes Completed records from the Stale filter regardless of age.
- Preserves the Stale filter across search, date criteria, sorting, pagination, Details, Compare, and Work Queue CSV export.
- Hides the Stale filter when **Show Work Queue Aging** is disabled.
- Added editable Stale filter wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, table, or column changes.

## 1.13.0

- Added a read-only Age column to the Work Queue.
- Added configurable stale highlighting for unfinished Replacement Draft, Original Review, and Needs Attention records.
- Added **Show Work Queue Aging** and **Stale After Days** addon settings.
- Added editable age and stale-indicator wording to the existing Edit Text popup.
- Completed records show their age but are never marked stale.
- Added no invoice, payment, credit, transaction, ledger, table, or column changes.

## 1.12.0

- Added a read-only **Export Queue CSV** action to the Work Queue.
- Exports all records matching the current workflow filter, search, created-date range, and sort order.
- Includes invoice IDs/numbers/statuses, workflow state, client details, creation metadata, and module messages.
- Added spreadsheet-formula protection through the existing CSV output layer.
- Added editable Work Queue Export wording.
- Added no database tables, columns, invoice changes, or accounting actions.

## 1.11.0

- Added read-only sorting for the Original, Replacement, Workflow Status, and Created Work Queue columns.
- Added ascending/descending toggles with an active sort indicator and accessible sort state.
- Preserved sort order across search, date filters, workflow filters, summary cards, pagination, Details, Compare, and audit-export return paths.
- Added editable sort tooltip wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, table, or column changes.

## 1.10.0

- Added a compact read-only Work Queue summary with counts for All, Replacement Drafts, Original Review, Completed, and Needs Attention.
- Made each summary count a link to the corresponding workflow filter.
- Applied the current search and Created From/Created To criteria to every summary count.
- Preserved search and date criteria when switching filters from the summary.
- Added editable Work Queue summary wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, table, or column changes.

## 1.9.0

- Added read-only Work Queue search by original/replacement invoice ID, WHMCS invoice number, client name, company, and email address.
- Added optional Created From and Created To filters based on the InvoiceFix relationship creation date.
- Preserved active search/date criteria across workflow filters, pagination, Details, Compare, and audit-export return paths.
- Added a Clear action that removes search/date criteria without changing the selected workflow filter.
- Added editable search and date-filter wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, table, or column changes.

## 1.8.0

- Added a read-only **System Check** to the InvoiceFix addon page.
- Added diagnostics for required module files, WHMCS Local API availability, administrator context, addon role access, and invoice permissions.
- Added checks for the module schema, audit table and columns, stored settings, and editable-text JSON.
- Added aggregate checks for missing original/replacement invoices, client mismatches, and recorded InvoiceFix creation warnings.
- Added pass, warning, failure, and information summaries with a generated-at timestamp.
- Added editable System Check wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, or database-column changes.

## 1.7.0

- Added the native `invoicefix_upgrade()` addon upgrade callback.
- Added ordered, idempotent migrations that record the completed schema version.
- Added lightweight self-repair checks on the InvoiceFix addon page and administrator invoice hook.
- Added safe repair for a missing or incomplete `mod_invoicefix_log` table.
- Added internal upgrade metadata for schema version, previous version, target version, and completion time.
- Added an exact allowlist scaffold for future removal of deprecated module files; no files are removed in 1.7.0.
- Added a read-only Installation Status panel to the InvoiceFix addon page.
- Added editable Installation Status wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, or ledger changes.

## 1.6.0

- Added a read-only **Download Audit CSV** action to Reissue Details.
- Added relationship, workflow, administrator, creation, current invoice, invoice-detail, and line-item data to the export.
- Added UTF-8 output and spreadsheet-formula neutralization.
- Added editable Audit Export wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, or database-schema changes.

## 1.5.0

- Added a read-only Reissue Details view to the InvoiceFix Work Queue.
- Added current original and replacement invoice summaries.
- Added workflow state, creation date, creating administrator, creation result, original status and total at creation, and saved module message.
- Added navigation to both invoices and the existing Compare view.
- Added editable Reissue Details wording to the Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, or database-schema changes.

## 1.4.0

- Added a read-only Compare view to the InvoiceFix Work Queue.
- Added side-by-side comparison of invoice status, dates, payment method, tax rates, totals, customer notes, and admin notes when available.
- Added line-item comparison for descriptions, amounts, taxable settings, relationships, due dates, payment methods, and notes.
- Added alignment logic for changed, added, removed, and unchanged lines.
- Added Changes Only and Show All display modes.
- Added editable comparison wording to the existing Edit Text popup.
- Added no invoice, payment, credit, transaction, ledger, or database-schema changes.

## 1.3.0

- Added a read-only InvoiceFix Work Queue to the addon administration page.
- Added filters for All, Replacement Drafts, Original Review, Completed, and Needs Attention.
- Added current original and replacement invoice statuses.
- Added Open Original and Open Replacement navigation actions.
- Added 50-row pagination.
- Added editable Work Queue wording to the existing Edit Text popup.
- Added no cancellation, refund, credit, publishing, payment, or invoice-status actions.
- Added no database schema changes.

## 1.2.0

- Centralized InvoiceFix default wording in `lang/english.php`.
- Added an **Edit Text** popup to the InvoiceFix addon page.
- Added grouped editing for invoice-page wording, linked notices, addon-page text, browser messages, and common operational messages.
- Added **Restore Defaults** for removing all saved custom wording.
- Stored custom wording separately in the WHMCS addon settings so module updates do not overwrite it.
- Added a configuration-area hint showing where to open the text editor.
- Preserved the existing invoice workflow, linked notices, audit table, and accounting safeguards.

## 1.1.0

- Added optional original/replacement invoice links.
- Added Compact and Standard linked-notice styles.
- Added a permanent original-to-replacement relationship lookup after the replacement leaves Draft status.
- Prevented additional replacement invoices while an existing InvoiceFix replacement still exists.
- Removed installation-specific namespace, authoring, colors, and path assumptions.
- Switched invoice-page controls and notices to WHMCS-native styling classes.
- Used the hook-provided WHMCS web root for asset loading when available.
- Added standalone installation and upgrade documentation.

## 1.0.0

- Initial release.
