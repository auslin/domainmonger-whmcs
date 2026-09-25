<?php
/**
 * DomainMonger - Patch 730 rollback cleanup.
 *
 * This file was used by earlier email-management guard attempts. The rewrite
 * rule that called it has been removed, so this direct-access fallback simply
 * returns visitors to the normal WHMCS client area.
 */

header('Location: clientarea.php', true, 302);
exit;
