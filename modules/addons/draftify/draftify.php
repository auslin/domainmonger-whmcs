<?php
/**
 * WHMCS Addon: Draftify
 * Adds an admin-area button on the invoice page to force-mark an invoice as Draft,
 * allowing edits after WHMCS behavior changes that restrict editing in Unpaid/Paid statuses.
 *
 * Module folder/name: draftify
 * Main file: modules/addons/draftify/draftify.php
 *
 * Compatible: WHMCS 8.x / 9.x (no external deps)
 */

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('This file cannot be accessed directly');
}

function draftify_config()
{
    return [
        'name'        => 'Draftify',
        'description' => 'Adds an admin invoice-page button to force-mark invoices as Draft (workaround to edit invoices locked in Unpaid/Paid).',
        'author'      => 'Angvlar SRL',
        'language'    => 'english',
        'version'     => '1.0',
        'fields'      => [
            'confirmText' => [
                'FriendlyName' => 'Confirmation Text',
                'Type'         => 'text',
                'Size'         => '80',
                'Default'      => 'Mark this invoice as Draft? This is a workaround to unlock editing. Use with care.',
                'Description'  => 'Text shown to admins before changing invoice status.',
            ],
            'storeSnapshot' => [
                'FriendlyName' => 'Store Snapshot',
                'Type'         => 'yesno',
                'Default'      => 'on',
                'Description'  => 'Store previous invoice fields in the addon log table for easier rollback/audit.',
            ],
        ],
    ];
}

function draftify_activate()
{
    try {
        if (!Capsule::schema()->hasTable('mod_draftify_log')) {
            Capsule::schema()->create('mod_draftify_log', function ($table) {
                /** @var \Illuminate\Database\Schema\Blueprint $table */
                $table->increments('id');
                $table->integer('invoice_id')->unsigned()->index();
                $table->integer('admin_id')->unsigned()->nullable()->index();
                $table->string('action', 32)->default('mark_draft');
                $table->text('snapshot_json')->nullable();
                $table->string('prev_status', 32)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['invoice_id', 'created_at']);
            });
        }
        return [
            'status' => 'success',
            'description' => 'Draftify activated successfully.',
        ];
    } catch (\Exception $e) {
        return [
            'status' => 'error',
            'description' => 'Activation failed: ' . $e->getMessage(),
        ];
    }
}

function draftify_deactivate()
{
    // Preserve audit log table on deactivation.
    return [
        'status' => 'success',
        'description' => 'Draftify deactivated. (Log table preserved.)',
    ];
}

function draftify_output($vars)
{
    echo '<div class="alert alert-info" style="margin:10px 0;">';
    echo '<strong>Draftify</strong> — This addon injects a "Mark as Draft" button on the admin invoice page.';
    echo '<br>Use the button on an invoice to force its status to <code>Draft</code> so you can edit line items/discounts.';
    echo '</div>';

    // Show recent actions
    try {
        $rows = Capsule::table('mod_draftify_log')
            ->orderBy('id', 'desc')
            ->limit(25)
            ->get();

        echo '<h3>Recent actions</h3>';
        echo '<table class="table table-striped table-bordered">';
        echo '<thead><tr><th>ID</th><th>Invoice</th><th>Admin ID</th><th>Action</th><th>Prev. Status</th><th>Date</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $inv = (int) $r->invoice_id;
            $invUrl = 'invoices.php?action=edit&id=' . $inv;
            echo '<tr>';
            echo '<td>' . (int) $r->id . '</td>';
            echo '<td><a href="' . htmlspecialchars($invUrl) . '">#' . $inv . '</a></td>';
            echo '<td>' . htmlspecialchars((string) $r->admin_id) . '</td>';
            echo '<td>' . htmlspecialchars((string) $r->action) . '</td>';
            echo '<td>' . htmlspecialchars((string) $r->prev_status) . '</td>';
            echo '<td>' . htmlspecialchars((string) $r->created_at) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } catch (\Exception $e) {
        echo '<div class="alert alert-warning">Could not load log: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    echo '<hr>';
    echo '<p><em>Tip:</em> Control access to this addon via WHMCS admin role permissions (Addon Modules access).</p>';
}

/**
 * Admin-only endpoint used by the injected button (AJAX).
 * Called at: addonmodules.php?module=draftify&action=markdraft
 */
function draftify_admin_dispatch()
{
    $action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : '';
    if ($action !== 'markdraft') {
        return;
    }

    header('Content-Type: application/json; charset=utf-8');

    $adminId = isset($_SESSION['adminid']) ? (int)$_SESSION['adminid'] : 0;
    if ($adminId <= 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Not authenticated (admin).']);
        exit;
    }

    // CSRF token check (WHMCS helper will exit/throw on failure)
    if (function_exists('check_token')) {
        try {
            @check_token();
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'CSRF token check failed.']);
            exit;
        }
    }

    $invoiceId = isset($_POST['invoiceid']) ? (int)$_POST['invoiceid'] : 0;
    if ($invoiceId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing/invalid invoice id.']);
        exit;
    }

    try {
        $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();
        if (!$invoice) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Invoice not found.']);
            exit;
        }

        $prevStatus = (string)($invoice->status ?? '');

        $snapshot = [
            'id' => (int)$invoice->id,
            'status' => $prevStatus,
            'datepaid' => $invoice->datepaid ?? null,
            'total' => $invoice->total ?? null,
            'subtotal' => $invoice->subtotal ?? null,
            'credit' => $invoice->credit ?? null,
            'tax' => $invoice->tax ?? null,
            'tax2' => $invoice->tax2 ?? null,
            'duedate' => $invoice->duedate ?? null,
            'date' => $invoice->date ?? null,
            'paymentmethod' => $invoice->paymentmethod ?? null,
        ];

        Capsule::table('tblinvoices')->where('id', $invoiceId)->update([
            'status' => 'Draft',
        ]);

        if (function_exists('logAdminActivity')) {
            logAdminActivity("Draftify: forced invoice #{$invoiceId} status from '{$prevStatus}' to 'Draft'", $adminId);
        }

        $storeSnapshot = draftify_get_setting('storeSnapshot', 'on') === 'on';
        Capsule::table('mod_draftify_log')->insert([
            'invoice_id'    => $invoiceId,
            'admin_id'      => $adminId,
            'action'        => 'mark_draft',
            'snapshot_json' => $storeSnapshot ? json_encode($snapshot) : null,
            'prev_status'   => $prevStatus,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        echo json_encode([
            'ok' => true,
            'invoiceid' => $invoiceId,
            'prev_status' => $prevStatus,
            'new_status' => 'Draft',
        ]);
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

function draftify_get_setting($key, $default = '')
{
    try {
        $row = Capsule::table('tbladdonmodules')
            ->where('module', 'draftify')
            ->where('setting', $key)
            ->first();
        if ($row && isset($row->value)) {
            return (string)$row->value;
        }
    } catch (\Throwable $e) {
        // ignore
    }
    return $default;
}

// If included via addonmodules.php, dispatch admin endpoint if requested.
draftify_admin_dispatch();
