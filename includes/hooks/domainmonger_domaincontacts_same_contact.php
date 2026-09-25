<?php
/**
 * DomainMonger WHOIS contact same-contact helper.
 *
 * Patch 798:
 * - The "Use same contact..." option follows the active tab.
 * - Copies the active tab contact to the other three contact tabs.
 * - Reorders tabs to: Registrant, Admin, Billing, Technical.
 *
 * Scope:
 * manage/clientarea.php?action=domaincontacts&domainid=...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

if (!function_exists('dm_domaincontacts_same_contact_is_page')) {
    function dm_domaincontacts_same_contact_is_page(): bool
    {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $action = strtolower((string) ($_REQUEST['action'] ?? ''));

        return ($script === 'clientarea.php' || strpos($uri, '/manage/clientarea.php') !== false)
            && ($action === 'domaincontacts' || strpos($uri, 'action=domaincontacts') !== false);
    }
}

add_hook('ClientAreaHeadOutput', 1, function () {
    if (!dm_domaincontacts_same_contact_is_page()) {
        return '';
    }

    return <<<'HTML'
<style id="dm-domaincontacts-same-contact-style-v798">
.dm-domaincontacts-same-contact {
    background: #fff8f3;
    border: 1px solid #f1c29c;
    border-radius: 8px;
    color: #163a5f;
    margin: 0 0 16px 0;
    padding: 12px 14px;
}

.dm-domaincontacts-same-contact label {
    align-items: flex-start;
    color: #163a5f;
    cursor: pointer;
    display: flex;
    font-size: 14px;
    font-weight: 700;
    gap: 10px;
    line-height: 1.35;
    margin: 0;
}

.dm-domaincontacts-same-contact input[type="checkbox"] {
    accent-color: #f58220;
    cursor: pointer;
    flex: 0 0 auto;
    height: 17px;
    margin-top: 2px;
    width: 17px;
}

.dm-domaincontacts-same-contact small {
    color: #163a5f;
    display: block;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.35;
    margin-top: 4px;
}
</style>
HTML;
});

add_hook('ClientAreaFooterOutput', 1, function () {
    if (!dm_domaincontacts_same_contact_is_page()) {
        return '';
    }

    return <<<'HTML'
<script id="dm-domaincontacts-same-contact-js-v798">
(function () {
    'use strict';

    var orderedTypes = ['registrant', 'admin', 'billing', 'technical'];
    var displayNames = {
        registrant: 'Registrant',
        admin: 'Admin',
        billing: 'Billing',
        technical: 'Technical'
    };

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function normalize(text) {
        return String(text || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function parseContactFieldName(name) {
        var match = String(name || '').match(/^contactdetails\[([^\]]+)\]\[([^\]]+)\]$/);
        if (!match) {
            return null;
        }

        return {
            contact: match[1],
            field: match[2]
        };
    }

    function contactTypeFromText(text) {
        var t = normalize(text);

        if (t.indexOf('registrant') !== -1) {
            return 'registrant';
        }

        if (t.indexOf('billing') !== -1) {
            return 'billing';
        }

        if (t.indexOf('technical') !== -1 || /\btech\b/.test(t)) {
            return 'technical';
        }

        if (t.indexOf('administrator') !== -1 || t.indexOf('administrative') !== -1 || /\badmin\b/.test(t)) {
            return 'admin';
        }

        return '';
    }

    function contactTypeFromContactName(contactName) {
        return contactTypeFromText(contactName);
    }

    function contactTypeFromTab(tab) {
        var link = tab ? tab.querySelector('a, .nav-link') : null;
        return contactTypeFromText(link ? link.textContent : tab ? tab.textContent : '');
    }

    function getContactFields(form) {
        var fields = {};
        var inputs = form.querySelectorAll('input[name^="contactdetails["], select[name^="contactdetails["], textarea[name^="contactdetails["]');
        var i;
        var input;
        var parsed;
        var type;

        for (i = 0; i < inputs.length; i += 1) {
            input = inputs[i];
            parsed = parseContactFieldName(input.name);

            if (!parsed) {
                continue;
            }

            type = contactTypeFromContactName(parsed.contact);
            if (!type) {
                continue;
            }

            if (!fields[type]) {
                fields[type] = {
                    contactName: parsed.contact,
                    fields: {}
                };
            }

            fields[type].fields[parsed.field] = input;
        }

        return fields;
    }

    function triggerNativeChange(el) {
        var event;

        if (!el) {
            return;
        }

        if (typeof Event === 'function') {
            event = new Event('change', { bubbles: true });
        } else {
            event = document.createEvent('Event');
            event.initEvent('change', true, true);
        }

        el.dispatchEvent(event);
    }

    function setCustomMode(contactName) {
        var customRadio = document.getElementById(contactName + '2');

        if (customRadio) {
            customRadio.checked = true;

            if (typeof window.useCustomWhois === 'function') {
                try {
                    window.useCustomWhois(customRadio.id);
                } catch (e) {}
            }

            triggerNativeChange(customRadio);
        }
    }

    function getActiveTabType(form) {
        var nav = form.querySelector('ul.nav-tabs, .nav.nav-tabs, .dm-domain-contact-tabs, .responsive-tabs-sm');
        var active;
        var type;

        if (nav) {
            active = nav.querySelector('li.active, li.dm-tab-active, .nav-link.active');
            if (active && active.classList && active.classList.contains('nav-link')) {
                active = active.closest('li, .nav-item') || active;
            }

            type = contactTypeFromTab(active);
            if (type) {
                return type;
            }

            active = nav.querySelector('li a[aria-expanded="true"], .nav-link[aria-expanded="true"]');
            if (active) {
                type = contactTypeFromText(active.textContent);
                if (type) {
                    return type;
                }
            }
        }

        /*
         * Fallback: visible pane heading/fieldset/tab-pane.
         */
        active = form.querySelector('.tab-pane.active, .tab-pane.in, .tab-pane.show.active');
        type = contactTypeFromText(active ? active.textContent : '');
        if (type) {
            return type;
        }

        return 'registrant';
    }

    function otherTypes(sourceType) {
        var result = [];
        var i;

        for (i = 0; i < orderedTypes.length; i += 1) {
            if (orderedTypes[i] !== sourceType) {
                result.push(orderedTypes[i]);
            }
        }

        return result;
    }

    function optionLabel(sourceType) {
        var others = otherTypes(sourceType);
        var labels = [];
        var i;

        for (i = 0; i < others.length; i += 1) {
            labels.push(displayNames[others[i]]);
        }

        return 'Use same contact for ' + labels[0] + ', ' + labels[1] + ' & ' + labels[2];
    }

    function updateOptionText(form) {
        var checkbox = document.getElementById('dmSameContactForOtherWhoisTabs');
        var labelSpan = document.getElementById('dmSameContactForOtherWhoisTabsLabel');
        var helpText = document.getElementById('dmSameContactForOtherWhoisTabsHelp');
        var sourceType;

        if (!checkbox || !labelSpan) {
            return;
        }

        sourceType = getActiveTabType(form);
        checkbox.setAttribute('data-dm-source-contact-type', sourceType);
        labelSpan.textContent = optionLabel(sourceType);

        if (helpText) {
            helpText.textContent = 'When checked, the ' + displayNames[sourceType] + ' tab is copied to the other contact tabs before saving.';
        }
    }

    function copyValues(form) {
        var checkbox = document.getElementById('dmSameContactForOtherWhoisTabs');
        var fields = getContactFields(form);
        var sourceType = checkbox ? checkbox.getAttribute('data-dm-source-contact-type') : '';
        var source;
        var targets;
        var i;
        var targetType;
        var field;
        var target;

        if (!sourceType) {
            sourceType = getActiveTabType(form);
        }

        if (!fields[sourceType]) {
            return false;
        }

        source = fields[sourceType].fields;
        targets = otherTypes(sourceType);

        for (i = 0; i < targets.length; i += 1) {
            targetType = targets[i];

            if (!fields[targetType]) {
                continue;
            }

            setCustomMode(fields[targetType].contactName);

            for (field in source) {
                if (!Object.prototype.hasOwnProperty.call(source, field) || !fields[targetType].fields[field]) {
                    continue;
                }

                target = fields[targetType].fields[field];

                if (target.disabled) {
                    target.disabled = false;
                }

                if (target.value !== source[field].value) {
                    target.value = source[field].value;
                    triggerNativeChange(target);
                }
            }
        }

        return true;
    }

    function tabOrder(tab) {
        var type = contactTypeFromTab(tab);
        var index = orderedTypes.indexOf(type);

        return index === -1 ? 99 : index;
    }

    function paneForTab(tab) {
        var link = tab ? tab.querySelector('a[href], .nav-link[href], a[data-target], .nav-link[data-target]') : null;
        var target = '';
        var pane;

        if (!link) {
            return null;
        }

        target = link.getAttribute('href') || link.getAttribute('data-target') || '';
        if (!target || target.charAt(0) !== '#') {
            return null;
        }

        try {
            pane = document.querySelector(target);
        } catch (e) {
            pane = null;
        }

        return pane;
    }

    function reorderTabs(form) {
        var nav = form.querySelector('ul.nav-tabs, .nav.nav-tabs, .responsive-tabs-sm');
        var tabContent = form.querySelector('.tab-content');
        var tabs;
        var panes = [];
        var i;
        var pane;

        if (!nav || nav.getAttribute('data-dm-contact-tabs-reordered') === '1') {
            return;
        }

        tabs = Array.prototype.slice.call(nav.children || []).filter(function (child) {
            return child && child.querySelector && child.querySelector('a, .nav-link');
        });

        if (tabs.length < 4) {
            return;
        }

        tabs.sort(function (a, b) {
            return tabOrder(a) - tabOrder(b);
        });

        for (i = 0; i < tabs.length; i += 1) {
            pane = paneForTab(tabs[i]);
            panes.push(pane);
            nav.appendChild(tabs[i]);

            /*
             * Shorten "Administrative" tab label to "Admin" to match the requested order.
             */
            if (contactTypeFromTab(tabs[i]) === 'admin') {
                tabs[i].querySelector('a, .nav-link').textContent = 'Admin';
            }
        }

        if (tabContent) {
            for (i = 0; i < panes.length; i += 1) {
                if (panes[i] && panes[i].parentNode === tabContent) {
                    tabContent.appendChild(panes[i]);
                }
            }
        }

        nav.setAttribute('data-dm-contact-tabs-reordered', '1');
    }

    function installLiveSync(form, checkbox) {
        var fields = getContactFields(form);
        var type;
        var field;
        var input;

        for (type in fields) {
            if (!Object.prototype.hasOwnProperty.call(fields, type)) {
                continue;
            }

            for (field in fields[type].fields) {
                if (!Object.prototype.hasOwnProperty.call(fields[type].fields, field)) {
                    continue;
                }

                input = fields[type].fields[field];

                input.addEventListener('input', function () {
                    if (checkbox.checked) {
                        updateOptionText(form);
                        copyValues(form);
                    }
                });

                input.addEventListener('change', function () {
                    if (checkbox.checked) {
                        updateOptionText(form);
                        copyValues(form);
                    }
                });
            }
        }
    }

    function findInsertPoint(form) {
        var tabContent = form.querySelector('.tab-content');
        var tabs = form.querySelector('ul.nav-tabs, .nav.nav-tabs, .responsive-tabs-sm');
        var lockOptOut = form.querySelector('.dm-domaincontacts-lock-optout');
        var buttonRow;

        if (tabContent) {
            return {
                parent: tabContent.parentNode,
                before: tabContent
            };
        }

        if (tabs && tabs.nextSibling) {
            return {
                parent: tabs.parentNode,
                before: tabs.nextSibling
            };
        }

        if (lockOptOut) {
            return {
                parent: lockOptOut.parentNode,
                before: lockOptOut
            };
        }

        buttonRow = form.querySelector('p.text-center, .text-center, .form-actions');
        if (buttonRow) {
            return {
                parent: buttonRow.parentNode,
                before: buttonRow
            };
        }

        return {
            parent: form,
            before: form.firstChild
        };
    }

    function installTabEvents(form) {
        var nav = form.querySelector('ul.nav-tabs, .nav.nav-tabs, .dm-domain-contact-tabs, .responsive-tabs-sm');

        if (!nav || nav.getAttribute('data-dm-same-contact-tab-events') === '1') {
            return;
        }

        nav.addEventListener('click', function () {
            window.setTimeout(function () {
                updateOptionText(form);
            }, 60);
            window.setTimeout(function () {
                updateOptionText(form);
            }, 250);
        });

        nav.setAttribute('data-dm-same-contact-tab-events', '1');
    }

    function addSameContactOption() {
        var form = document.getElementById('frmDomainContactModification');
        var wrap;
        var checkbox;
        var insertPoint;

        if (!form) {
            return;
        }

        reorderTabs(form);

        if (!form.querySelector('#dmSameContactForOtherWhoisTabs')) {
            wrap = document.createElement('div');
            wrap.className = 'dm-domaincontacts-same-contact';
            wrap.innerHTML =
                '<label for="dmSameContactForOtherWhoisTabs">' +
                    '<input id="dmSameContactForOtherWhoisTabs" name="dm_same_contact_for_other_whois_tabs" type="checkbox" value="1">' +
                    '<span>' +
                        '<span id="dmSameContactForOtherWhoisTabsLabel">Use same contact for Admin, Billing &amp; Technical</span>' +
                        '<small id="dmSameContactForOtherWhoisTabsHelp">When checked, the active tab is copied to the other contact tabs before saving.</small>' +
                    '</span>' +
                '</label>';

            insertPoint = findInsertPoint(form);
            insertPoint.parent.insertBefore(wrap, insertPoint.before);

            checkbox = document.getElementById('dmSameContactForOtherWhoisTabs');

            checkbox.addEventListener('change', function () {
                updateOptionText(form);
                if (checkbox.checked) {
                    copyValues(form);
                }
            });

            form.addEventListener('submit', function () {
                updateOptionText(form);
                if (checkbox.checked) {
                    copyValues(form);
                }
            }, true);

            installLiveSync(form, checkbox);
        }

        installTabEvents(form);
        updateOptionText(form);
    }

    ready(addSameContactOption);
    window.setTimeout(addSameContactOption, 150);
    window.setTimeout(addSameContactOption, 500);
    window.setTimeout(addSameContactOption, 1000);
}());
</script>
HTML;
});
