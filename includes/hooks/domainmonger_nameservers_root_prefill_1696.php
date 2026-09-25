<?php
/**
 * Patch 1696 - RegistrarDNS root NS -> active nameserver prefill handoff.
 * Patch 1697 - Fill the WHMCS configured default nameservers when the user
 * selects Use default nameservers.
 *
 * Page-specific only. Reads the root NS set stored by the RegistrarDNS DNS
 * Management button and fills the existing WHMCS Domain Settings -> Nameservers
 * form. It never submits the form and never changes registrar delegation itself.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

add_hook('ClientAreaHeaderOutput', 1696, function ($vars) {
    $action = isset($_GET['action']) ? strtolower((string) $_GET['action']) : '';
    if ($action !== 'domaindetails') {
        return '';
    }

    return <<<'HTML'
<style id="dm-root-ns-prefill-1696-css">
#dm-root-ns-prefill-1696 {
    background: #fff8df;
    border: 1px solid #ecd58d;
    border-radius: 6px;
    color: #5f5226;
    font-size: 13px;
    line-height: 1.45;
    margin: 0 0 14px;
    padding: 10px 12px;
}
#dm-root-ns-prefill-1696 strong {
    color: #163a5f;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1696, function ($vars) {
    $action = isset($_GET['action']) ? strtolower((string) $_GET['action']) : '';
    if ($action !== 'domaindetails') {
        return '';
    }

    $defaultNameserverSettings1697 = [
        'DefaultNameserver1',
        'DefaultNameserver2',
        'DefaultNameserver3',
        'DefaultNameserver4',
        'DefaultNameserver5',
    ];
    $defaultNameserverMap1697 = [];
    try {
        $defaultRows1697 = Capsule::table('tblconfiguration')
            ->select('setting', 'value')
            ->whereIn('setting', $defaultNameserverSettings1697)
            ->get();
        foreach ($defaultRows1697 as $defaultRow1697) {
            $defaultNameserverMap1697[(string) $defaultRow1697->setting] = trim((string) $defaultRow1697->value);
        }
    } catch (\Throwable $e) {
        $defaultNameserverMap1697 = [];
    }

    $defaultNameservers1697 = [];
    for ($defaultIndex1697 = 1; $defaultIndex1697 <= 5; $defaultIndex1697++) {
        $defaultNameservers1697[] = (string) ($defaultNameserverMap1697['DefaultNameserver' . $defaultIndex1697] ?? '');
    }
    $defaultNameserversJson1697 = json_encode(
        $defaultNameservers1697,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );
    if (!is_string($defaultNameserversJson1697)) {
        $defaultNameserversJson1697 = '[]';
    }

    return <<<HTML
<script id="dm-root-ns-prefill-1696-js">
(function () {
    var storageKey1696 = 'dmRootNsPrefill1696';
    var maxAge1696 = 10 * 60 * 1000;
    var defaultNameservers1697 = {$defaultNameserversJson1697};

    function loadPayload1696() {
        var raw1696 = '';
        try {
            raw1696 = window.sessionStorage.getItem(storageKey1696) || '';
        } catch (storageReadError1696) {
            return null;
        }
        if (!raw1696) {
            return null;
        }
        try {
            var payload1696 = JSON.parse(raw1696);
            if (!payload1696 || !Array.isArray(payload1696.values) || !payload1696.values.length) {
                return null;
            }
            if (payload1696.createdAt && (Date.now() - Number(payload1696.createdAt)) > maxAge1696) {
                window.sessionStorage.removeItem(storageKey1696);
                return null;
            }
            return payload1696;
        } catch (payloadError1696) {
            try { window.sessionStorage.removeItem(storageKey1696); } catch (ignore1696) {}
            return null;
        }
    }

    function normalizeNs1696(value1696) {
        return String(value1696 == null ? '' : value1696).trim().replace(/\.+$/, '');
    }

    function findNameserverForm1697() {
        var sub1697 = document.querySelector('#tabNameservers input[name="sub"][value="savens"]')
            || document.querySelector('input[name="sub"][value="savens"]');
        return sub1697 && sub1697.form ? sub1697.form : null;
    }

    function applyDefaultNameservers1697(form1697) {
        if (!form1697 || !Array.isArray(defaultNameservers1697)) {
            return;
        }
        for (var index1697 = 1; index1697 <= 5; index1697++) {
            var field1697 = form1697.querySelector('input[name="ns' + String(index1697) + '"]');
            if (!field1697) {
                continue;
            }
            field1697.value = normalizeNs1696(defaultNameservers1697[index1697 - 1] || '');
            field1697.dispatchEvent(new Event('input', { bubbles: true }));
            field1697.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (typeof window.disableFields === 'function') {
            try {
                window.disableFields('domnsinputs', true);
                return;
            } catch (disableDefaultsError1697) {}
        }
        for (var disableIndex1697 = 1; disableIndex1697 <= 5; disableIndex1697++) {
            var disableField1697 = form1697.querySelector('input[name="ns' + String(disableIndex1697) + '"]');
            if (disableField1697) {
                disableField1697.disabled = true;
            }
        }
    }

    function bindDefaultNameservers1697() {
        var form1697 = findNameserverForm1697();
        if (!form1697) {
            return false;
        }
        var defaultChoice1697 = form1697.querySelector('input[name="nschoice"][value="default"]');
        if (!defaultChoice1697) {
            return true;
        }
        if (defaultChoice1697.dataset.dmDefaultNsFill1697 !== '1') {
            defaultChoice1697.addEventListener('change', function () {
                if (defaultChoice1697.checked) {
                    applyDefaultNameservers1697(form1697);
                }
            });
            defaultChoice1697.dataset.dmDefaultNsFill1697 = '1';
        }
        if (defaultChoice1697.checked) {
            applyDefaultNameservers1697(form1697);
        }
        return true;
    }

    function applyPrefill1696() {
        var payload1696 = loadPayload1696();
        if (!payload1696) {
            return true;
        }

        var sub1696 = document.querySelector('#tabNameservers input[name="sub"][value="savens"]')
            || document.querySelector('input[name="sub"][value="savens"]');
        var form1696 = sub1696 && sub1696.form ? sub1696.form : null;
        if (!form1696) {
            return false;
        }

        var idField1696 = form1696.querySelector('input[name="id"]');
        var currentDomainId1696 = idField1696 ? String(idField1696.value || '') : '';
        if (payload1696.domainId && currentDomainId1696 && String(payload1696.domainId) !== currentDomainId1696) {
            return true;
        }

        var seen1696 = {};
        var values1696 = payload1696.values.map(normalizeNs1696).filter(function (value1696) {
            var key1696 = value1696.toLowerCase();
            if (!key1696 || seen1696[key1696]) {
                return false;
            }
            seen1696[key1696] = true;
            return true;
        }).slice(0, 5);
        if (!values1696.length) {
            return true;
        }

        var default1696 = form1696.querySelector('input[name="nschoice"][value="default"]');
        var custom1696 = form1696.querySelector('input[name="nschoice"][value="custom"]');
        if (default1696) {
            default1696.checked = false;
        }
        if (custom1696) {
            custom1696.checked = true;
            custom1696.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (typeof window.disableFields === 'function') {
            try { window.disableFields('domnsinputs', false); } catch (disableError1696) {}
        }

        for (var index1696 = 1; index1696 <= 5; index1696++) {
            var field1696 = form1696.querySelector('input[name="ns' + String(index1696) + '"]');
            if (!field1696) {
                continue;
            }
            field1696.disabled = false;
            field1696.value = values1696[index1696 - 1] || '';
            field1696.dispatchEvent(new Event('input', { bubbles: true }));
            field1696.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (!document.getElementById('dm-root-ns-prefill-1696')) {
            var notice1696 = document.createElement('div');
            notice1696.id = 'dm-root-ns-prefill-1696';
            notice1696.setAttribute('role', 'note');
            notice1696.innerHTML = '<strong>RegistrarDNS root NS records loaded.</strong> These values are not active yet. Review them below, then click <strong>Change Nameservers</strong> to replace the current active nameservers.';
            form1696.parentNode.insertBefore(notice1696, form1696);
        }

        try { window.sessionStorage.removeItem(storageKey1696); } catch (storageRemoveError1696) {}
        return true;
    }

    function start1696() {
        var defaultsBound1697 = bindDefaultNameservers1697();
        var prefillDone1696 = applyPrefill1696();
        if (defaultsBound1697 && prefillDone1696) {
            return;
        }
        var observer1696 = new MutationObserver(function () {
            defaultsBound1697 = bindDefaultNameservers1697();
            prefillDone1696 = applyPrefill1696();
            if (defaultsBound1697 && prefillDone1696) {
                observer1696.disconnect();
            }
        });
        observer1696.observe(document.documentElement, { childList: true, subtree: true });
        window.setTimeout(function () {
            observer1696.disconnect();
            bindDefaultNameservers1697();
            applyPrefill1696();
        }, 5000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start1696, { once: true });
    } else {
        start1696();
    }
})();
</script>
HTML;
});
