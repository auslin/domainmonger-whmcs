<?php
/**
 * DomainMonger Patch 1690
 * Registrar DNS: read the current registrar-level nameservers from the same
 * authenticated Domain Settings -> Nameservers page that is confirmed to show
 * and save the correct values.
 *
 * Scope: clientarea.php?action=domaindns only.
 * This does not alter DNS record forms, sorting, record IDs, edits, adds, or deletes.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaFooterOutput', 1000, function ($vars) {
    $script = strtolower((string) basename($_SERVER['SCRIPT_NAME'] ?? ''));
    $action = strtolower(trim((string) ($_GET['action'] ?? '')));

    if ($script !== 'clientarea.php' || $action !== 'domaindns') {
        return '';
    }

    $userId = !empty($_SESSION['uid']) ? (int) $_SESSION['uid'] : 0;
    $domainId = !empty($_GET['domainid']) ? (int) $_GET['domainid'] : 0;
    if ($userId < 1 || $domainId < 1) {
        return '';
    }

    try {
        $domain = Capsule::table('tbldomains')
            ->where('id', $domainId)
            ->where('userid', $userId)
            ->select('id', 'domain', 'registrar')
            ->first();
    } catch (\Throwable $e) {
        return '';
    }

    if (!$domain) {
        return '';
    }

    $registrar = strtolower(trim((string) ($domain->registrar ?? '')));
    $isRegistrarDns = $registrar !== '' && (
        strpos($registrar, 'resellerclub') !== false
        || strpos($registrar, 'netearth') !== false
        || $registrar === 'directi'
    );
    if (!$isRegistrarDns) {
        return '';
    }

    $nameserversUrl = 'clientarea.php?action=domaindetails&id=' . $domainId
        . '&dmsection=nameservers&dmdesign=1&dmconverted=1';

    $config = json_encode([
        'domainId' => $domainId,
        'nameserversUrl' => $nameserversUrl,
        'pageSystem' => 'resellerclub',
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return <<<HTML
<script id="dm-registrar-dns-nameserver-indicator-fix-1690">
(function () {
    'use strict';

    var config = {$config};
    var indicator = document.getElementById('dm-dns-system-indicator-1337');
    if (!indicator) {
        return;
    }

    var resetTimer = 0;

    function normalize(value) {
        return String(value || '').trim().toLowerCase().replace(/\.+$/, '');
    }

    function uniqueSorted(values) {
        var seen = Object.create(null);
        var out = [];
        values.forEach(function (value) {
            value = normalize(value);
            if (!value || seen[value]) {
                return;
            }
            seen[value] = true;
            out.push(value);
        });
        out.sort(function (a, b) {
            return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
        });
        return out;
    }

    function classify(nameservers) {
        var rc = [
            'ns5.domainmonger.com',
            'ns6.domainmonger.com',
            'ns7.domainmonger.com',
            'ns8.domainmonger.com'
        ];
        var cloud = [
            'ns31.domainmonger.com',
            'ns32.domainmonger.com',
            'ns33.domainmonger.com',
            'ns34.domainmonger.com'
        ];
        var cpanel = [
            'ns50a.domainmonger.com',
            'ns50b.domainmonger.com'
        ];

        function matchesOnly(list) {
            var matches = nameservers.filter(function (ns) { return list.indexOf(ns) !== -1; });
            var outside = nameservers.filter(function (ns) { return list.indexOf(ns) === -1; });
            return matches.length >= 2 && outside.length === 0;
        }

        if (!nameservers.length) {
            return 'unknown';
        }
        if (matchesOnly(rc)) {
            return 'resellerclub';
        }
        if (matchesOnly(cloud)) {
            return 'cloudns';
        }
        if (matchesOnly(cpanel)) {
            return 'cpanel';
        }

        var known = rc.concat(cloud, cpanel);
        var knownCount = nameservers.filter(function (ns) { return known.indexOf(ns) !== -1; }).length;
        if (knownCount > 0) {
            return 'mixed';
        }
        return 'external';
    }

    function stateFor(activeSystem) {
        var pageSystem = config.pageSystem || 'resellerclub';
        var activeLabels = {
            resellerclub: 'Register DNS',
            cloudns: 'DNSPlus',
            cpanel: 'cPanel',
            external: 'External nameservers',
            mixed: 'Mixed nameservers',
            unknown: 'Unknown'
        };
        var activeLabel = activeLabels[activeSystem] || 'Unknown';

        if (activeSystem === pageSystem) {
            return {
                className: 'dm-dns-system-current',
                pill: 'Active',
                headline: 'Register DNS is the active DNS system for this domain.'
            };
        }
        if (activeSystem === 'mixed') {
            return {
                className: 'dm-dns-system-mixed',
                pill: 'Changing',
                headline: 'The nameserver change may still be in progress.'
            };
        }
        if (activeSystem === 'unknown') {
            return {
                className: 'dm-dns-system-unknown',
                pill: 'Unknown',
                headline: 'The active DNS system could not be confirmed.'
            };
        }
        if (activeSystem === 'external') {
            return {
                className: 'dm-dns-system-external',
                pill: 'Not Active',
                headline: 'Register DNS is not active. This domain uses external DNS.'
            };
        }
        return {
            className: 'dm-dns-system-other',
            pill: 'Not Active',
            headline: 'Register DNS is not active. Active DNS system: ' + activeLabel + '.'
        };
    }

    function render(nameservers) {
        var activeSystem = classify(nameservers);
        var state = stateFor(activeSystem);
        var pill = indicator.querySelector('.dm-dns-system-pill');
        var copy = indicator.querySelector('.dm-dns-system-copy');
        var headline = copy ? copy.querySelector('strong') : null;
        var detail = copy ? copy.querySelector('span') : null;

        indicator.className = state.className;
        if (pill) {
            pill.textContent = state.pill;
        }
        if (headline) {
            headline.textContent = state.headline;
        }
        if (detail) {
            detail.textContent = nameservers.length
                ? 'Current domain nameservers: ' + nameservers.join(', ')
                : 'No current nameservers were available from Domain Settings.';
        }

        var accessNote = indicator.querySelector('.dm-dns-system-access-note');
        if (accessNote) {
            accessNote.remove();
        }
        var detectionNote = indicator.querySelector('.dm-dns-system-detection-note');
        if (detectionNote) {
            detectionNote.textContent = 'Nameservers shown here are read from Domain Settings.';
        }

        indicator.hidden = false;
    }

    function parseNameservers(html) {
        var parsed = new DOMParser().parseFromString(html, 'text/html');
        var nameservers = [];
        for (var i = 1; i <= 5; i += 1) {
            var field = parsed.querySelector('#tabNameservers input[name="ns' + i + '"]')
                || parsed.querySelector('input[name="ns' + i + '"]');
            if (field && field.value) {
                nameservers.push(field.value);
            }
        }
        return uniqueSorted(nameservers);
    }

    function setButtonState(button, state, label) {
        if (!button) {
            return;
        }
        var text = button.querySelector('.dm-dns-system-refresh-label');
        window.clearTimeout(resetTimer);
        button.disabled = state === 'loading';
        button.classList.toggle('dm-is-refreshing', state === 'loading');
        if (text) {
            text.textContent = label;
        }
    }

    function resetButtonLater(button) {
        window.clearTimeout(resetTimer);
        resetTimer = window.setTimeout(function () {
            if (button && document.documentElement.contains(button)) {
                setButtonState(button, 'idle', 'Refresh Nameservers');
            }
        }, 1600);
    }

    function readCurrentNameservers(button, isInitial) {
        var requestUrl = new URL(config.nameserversUrl, window.location.href);
        requestUrl.searchParams.set('dmns1690', String(Date.now()));
        requestUrl.hash = '';

        if (button) {
            setButtonState(button, 'loading', 'Checking Nameservers…');
        } else if (isInitial) {
            indicator.hidden = true;
        }

        return window.fetch(requestUrl.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            redirect: 'follow',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Domain Settings nameserver lookup failed with HTTP ' + response.status + '.');
            }
            return response.text();
        }).then(function (html) {
            var nameservers = parseNameservers(html);
            if (!nameservers.length) {
                throw new Error('No nameserver fields were found on the Domain Settings page.');
            }
            render(nameservers);
            var currentButton = indicator.querySelector('.dm-dns-system-refresh');
            if (currentButton) {
                setButtonState(currentButton, 'success', 'Nameservers Updated');
                resetButtonLater(currentButton);
            }
        }).catch(function () {
            indicator.hidden = false;
            var currentButton = indicator.querySelector('.dm-dns-system-refresh') || button;
            if (currentButton) {
                setButtonState(currentButton, 'error', 'Refresh Failed');
                resetButtonLater(currentButton);
            }
        });
    }

    function replaceAndBindRefreshButton() {
        var oldButton = indicator.querySelector('.dm-dns-system-refresh');
        if (!oldButton || !oldButton.parentNode) {
            return null;
        }

        var button = oldButton.cloneNode(true);
        button.removeAttribute('data-dm-refresh-bound');
        oldButton.parentNode.replaceChild(button, oldButton);
        button.setAttribute('data-dm-refresh-bound-1690', '1');
        button.addEventListener('click', function () {
            readCurrentNameservers(button, false);
        });
        return button;
    }

    replaceAndBindRefreshButton();
    readCurrentNameservers(null, true).then(function () {
        replaceAndBindRefreshButton();
    });
})();
</script>
HTML;
});
