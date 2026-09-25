# InvoiceFix 1.17.1

InvoiceFix is a standalone WHMCS addon that creates a separate editable Draft from a published invoice while leaving the original invoice and its accounting history unchanged.

## What it does

- Adds **Create Editable Draft** to eligible WHMCS admin invoice pages.
- Creates a genuine Draft using the WHMCS Local API.
- Copies invoice line descriptions, amounts, taxable flags, dates, payment method, notes, and supported internal line-item relationships.
- Does not copy payments, transactions, applied credit, gateway transaction IDs, `datepaid`, or ledger activity.
- Records original/replacement relationships in `mod_invoicefix_log`.
- Prevents creating another InvoiceFix replacement while an existing replacement invoice still exists.
- Optionally displays compact or standard links between the original and replacement invoices.
- Keeps all default module wording in one file: `lang/english.php`.
- Provides an **Edit Text** popup on the InvoiceFix addon page for common wording changes.
- Provides a read-only **Work Queue** for tracking original/replacement pairs.
- Shows a clickable Work Queue summary with workflow, stale, My Follow-Ups, and Overdue counts.
- Supports sortable Original, Replacement, Workflow Status, and Created columns while preserving active queue filters.
- Exports all Work Queue records matching the current workflow filter, search, date range, and sort order to CSV.
- Provides a read-only **Compare** view for reviewing invoice details and line-item changes side by side.
- Provides a **Reissue Details** view for audit and workflow context, with an administrator-only internal note and follow-up assignment stored only in the InvoiceFix audit record.
- Downloads a portable CSV audit report from Reissue Details.
- Includes a versioned, idempotent upgrade routine with a read-only Installation Status panel.
- Provides a read-only **System Check** for module, permission, settings, audit-table, and invoice-link integrity.

## Requirements

- WHMCS with addon-module and hook support.
- PHP 8.1 or newer is recommended.
- Administrator role access to InvoiceFix.
- Administrator permissions for both **Create Invoice** and **Manage Invoice**.

## Installation

1. Upload the `invoicefix` folder to `modules/addons/invoicefix/`.
2. In WHMCS, open **Configuration > System Settings > Addon Modules**.
3. Activate **InvoiceFix**.
4. Configure **Access Control** for the appropriate administrator roles.
5. Save the InvoiceFix settings.

## Settings

- **Show Linked Invoice Notice** — Shows links between the original and replacement invoices.
- **Linked Notice Style**
  - **Compact** — Shows the linked invoice number and status only.
  - **Standard** — Also shows short workflow guidance.
- **Show Work Queue Aging** — Shows the Age column, stale indicators, and the dedicated Stale filter in the administrator Work Queue.
- **Stale After Days** — Sets the warning threshold for unfinished work; the default is 7 days.

The module description in Addon Modules tells administrators where to find the text editor. The normal invoice screen remains uncluttered.

## Work Queue

Open **Addons > InvoiceFix** to view the read-only Work Queue. It displays the current status of each original and replacement invoice and provides these filters:

- All
- Replacement Drafts
- Original Review
- Completed
- Needs Attention
- Stale
- My Follow-Ups
- Overdue

The queue provides **Open Original**, **Open Replacement**, **Details**, and read-only **Compare** links. It does not cancel, refund, credit, publish, or change either invoice.

A compact Work Queue summary shows counts for All, Replacement Drafts, Original Review, Completed, Needs Attention, Stale, My Follow-Ups, and Overdue. The Stale count uses the configured aging threshold and includes only unfinished relationships. My Follow-Ups shows unfinished relationships assigned to the signed-in administrator. Overdue shows unfinished relationships with an internal follow-up date before today. Summary counts use the current search and date criteria, and each count can be clicked to switch the active filter without clearing those criteria.

Use the Work Queue search controls to find relationships by:

- Original or replacement invoice ID.
- WHMCS invoice number.
- Client first name, last name, full name, company, or email address.
- InvoiceFix relationship creation date, using optional **Created From** and **Created To** fields.

Search and date criteria remain active while switching workflow filters, changing column sorting, paging through results, opening Details or Compare, and returning after a CSV export error. Use **Clear** to remove only the search/date criteria while retaining the selected workflow filter and sort order.

Click **Original**, **Replacement**, **Workflow Status**, or **Created** to sort the queue. Click the active column again to reverse the direction. Sorting is retained across workflow filters, search/date changes, pagination, Details, Compare, and audit-export returns. The default is newest Created date first.

Workflow states are determined as follows:

- **Replacement Draft** — The replacement is still in Draft and the original has not been cancelled.
- **Review Original** — The replacement has left Draft status and the original has not been cancelled.
- **Completed** — The replacement has left Draft status and the original is Cancelled.
- **Needs Attention** — An invoice is missing, the replacement is Cancelled or Refunded, the original was cancelled while the replacement is still Draft, or InvoiceFix recorded a creation warning.

The Work Queue displays 50 records per page and supports Previous and Next navigation. **Export Queue CSV** downloads the complete matching result set, not only the current page. The export includes invoice IDs and numbers, statuses, workflow state, client details, creation metadata, and module messages. CSV cells are protected against spreadsheet-formula execution.

## Reissue details

Click **Details** for any InvoiceFix relationship in the Work Queue. The read-only screen shows:

- The current status, total, invoice date, due date, and paid date when present for both invoices.
- The InvoiceFix workflow status.
- The administrator who created the replacement.
- The date and time the relationship was created.
- The original invoice status and total recorded at creation time.
- The InvoiceFix result and saved module message.
- Navigation to either invoice and, when both invoices exist, the Compare view.
- A **Download Audit CSV** action for saving the relationship, current invoice summaries, invoice-detail comparison, and line-item comparison.

The details screen is useful even when one invoice is missing. Invoice and accounting information remains read-only. Administrators can save or clear an internal reissue note, assign a follow-up owner, and set an optional internal due date without changing either invoice. None of this information is shown to clients, and all visible wording is available in the existing **Edit Text** popup.


## Follow-up assignments

InvoiceFix 1.16.0 added an administrator-only follow-up owner and optional internal due date on each Reissue Details screen.

- Assignments are stored only in `mod_invoicefix_log`.
- The due date is an internal follow-up date and never changes either invoice due date.
- The Work Queue shows compact owner and due-date information only when follow-up data exists.
- Unfinished records with a past due date display an **Overdue** badge. Completed relationships are never marked overdue.
- The last updating administrator and time are recorded.
- Saving or clearing follow-up data creates a WHMCS administrator Activity Log entry.
- Follow-up data is included in the Work Queue CSV and the relationship Audit CSV.
- Follow-up controls are visible only to administrators with InvoiceFix access and the required invoice permissions.

All follow-up wording is available in **Edit Text** under **Follow-Up Assignment**.

## Internal reissue notes

InvoiceFix 1.15.0 adds an administrator-only note on each Reissue Details screen.

- Notes are stored only in `mod_invoicefix_log`.
- Notes are visible only to administrators who have InvoiceFix access and the required invoice permissions.
- Saving or clearing a note does not edit either invoice or any payment, credit, transaction, or ledger record.
- The last updating administrator and time are recorded.
- Saving and clearing a note creates a WHMCS administrator Activity Log entry.
- The note and its update metadata are included in **Download Audit CSV**.
- A blank note saved from Reissue Details clears the existing note.
- Notes are limited to 10,000 characters.

All note labels, messages, and Activity Log wording are available in **Edit Text** under **Internal Reissue Note**.

## Audit CSV export

Click **Download Audit CSV** from Reissue Details to save a portable, read-only record. The CSV includes:

- The InvoiceFix relationship and export time.
- Workflow state, creation administrator, creation date, result, saved module message, and internal reissue note metadata when present.
- Current original and replacement invoice status, total, invoice date, due date, and paid date when present.
- All available invoice-detail comparisons.
- Aligned original and replacement line-item details, including added, removed, changed, and unchanged lines.

The export uses UTF-8 CSV output for common spreadsheet applications and neutralizes formula-style cell prefixes. It does not write to the database or change either invoice. Export wording is available under **Audit Export** in the existing Edit Text popup.

## Comparing invoices

Click **Compare** for any complete original/replacement pair in the Work Queue. The comparison opens inside the InvoiceFix addon page and includes:

- Original and replacement invoice status and total.
- Side-by-side invoice dates, due dates, payment method, tax rates, totals, customer notes, and admin notes when available.
- Side-by-side line descriptions, amounts, taxable settings, and supported internal relationships.
- Detection of changed, added, removed, and unchanged line items.
- **Changes Only** and **Show All** display modes.

The comparison is read-only. It does not edit, publish, cancel, refund, credit, or otherwise change either invoice. Comparison wording is available in the existing **Edit Text** popup.

## Editing module text

1. Open **Addons > InvoiceFix**.
2. Click **Edit Text**.
3. Change the desired wording and click **Save Text**.
4. Use **Restore Defaults** to remove all saved custom wording.

All default wording is centralized in `lang/english.php`. The popup stores only changed values in the WHMCS `tbladdonmodules` settings table under `text_overrides_json`; it does not rewrite the language file. This allows custom wording to survive normal module-file upgrades.

## Upgrade safety

InvoiceFix uses WHMCS's native addon-module upgrade callback. When the module version changes, InvoiceFix:

- Detects the previously installed module or schema version.
- Runs each required migration only once and records the completed schema version.
- Repairs a missing or incomplete InvoiceFix audit table without changing invoice, payment, credit, transaction, or ledger data.
- Preserves Work Queue relationships, audit history, module settings, access-role settings, linked-invoice preferences, and custom Edit Text overrides.
- Supports an exact allowlist for removing deprecated module files in future releases. InvoiceFix 1.17.0 does not remove any files.

A lightweight self-check also runs when the InvoiceFix addon page or its admin invoice hook is used. This protects installations where files were refreshed manually but the normal WHMCS upgrade callback did not complete.

Open **Addons > InvoiceFix** and review **Installation Status** to see:

- Module version
- Recorded schema version
- Last successful upgrade time
- Upgrade path
- Current health status

Installation Status labels and messages are available in the existing **Edit Text** popup. No upgrade information appears in the client area or on normal customer invoice pages.


## Work Queue aging indicators

InvoiceFix 1.13.0 can show the age of each Work Queue relationship and highlight unfinished work that has become stale.

Configure this under **Configuration > System Settings > Addon Modules > InvoiceFix**:

- **Show Work Queue Aging** enables or hides the Age column, stale highlighting, and Stale filter.
- **Stale After Days** sets the warning threshold. The default is 7 days.

Draft, Review Original, and Needs Attention records are highlighted after the threshold. The **Stale** filter and summary card show those unfinished records together. Completed records continue to show their age but are never included in Stale. Aging and filtering are read-only and do not change invoices, statuses, payments, credit, transactions, or ledger data.

## System Check

Open **Addons > InvoiceFix** and click **System Check**. The read-only diagnostics review:

- Required InvoiceFix files and the WHMCS Local API.
- The current administrator context, addon role access, and Create Invoice/Manage Invoice permissions.
- Module and schema versions.
- The InvoiceFix audit table and required columns.
- Stored addon settings and editable-text JSON.
- Missing original or replacement invoices in recorded relationships.
- Original/replacement client mismatches.
- InvoiceFix creation warnings recorded in the audit table.

The report groups results as Pass, Warning, Fail, or Info. It performs no repairs and does not edit, publish, cancel, refund, credit, or otherwise change invoices. All visible System Check wording is available in the existing **Edit Text** popup.

## Safe workflow

1. Open a published invoice.
2. Click **Create Editable Draft**.
3. Edit and publish the replacement invoice.
4. Handle the original invoice through WHMCS supported controls.

InvoiceFix does not automatically cancel, refund, credit, or alter the original invoice.

## Database

Activation creates `mod_invoicefix_log`. Deactivation preserves the table for audit history. Saving custom wording adds or updates one module-setting row in `tbladdonmodules`. InvoiceFix stores internal `schema_version`, `last_upgrade_from`, `last_upgrade_to`, and `last_upgrade_at` settings in `tbladdonmodules` so upgrades are repeatable and auditable. InvoiceFix 1.15.0 adds `internal_note`, `internal_note_admin_id`, and `internal_note_updated_at` columns only to `mod_invoicefix_log`. InvoiceFix 1.16.0 adds `follow_up_admin_id`, `follow_up_due_date`, `follow_up_updated_by_admin_id`, and `follow_up_updated_at` only to the same InvoiceFix audit table. No WHMCS invoice, payment, credit, transaction, or ledger table is changed.

## Upgrade from 1.0.0–1.16.0

Replace the existing InvoiceFix module files with the 1.17.0 files. Do not deactivate the addon. WHMCS will detect the version change and call the InvoiceFix upgrade routine the first time the module is accessed. Existing audit records, Work Queue relationships, linked-invoice settings, access roles, and text overrides are preserved.

After uploading, open **Addons > InvoiceFix** and confirm that Installation Status shows **Up to Date**.
