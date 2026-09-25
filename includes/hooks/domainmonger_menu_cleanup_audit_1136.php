<?php
/**
 * DomainMonger patch 1136: Menu cleanup audit diagnostic.
 *
 * Diagnostic-only hook. Runs only when &dmmenuaudit=1136 is present.
 * Lists likely menu/navigation links on the current WHMCS client page so the
 * next menu cleanup patch can target the live rendered markup instead of
 * guessing.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaFooterOutput', 9999, function ($vars) {
    if ((string) ($_GET['dmmenuaudit'] ?? '') !== '1136') {
        return '';
    }

    $url = htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? ''), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<style>
#dm-menu-audit-1136 {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 999999;
    width: min(720px, calc(100vw - 36px));
    max-height: min(78vh, 760px);
    overflow: auto;
    background: #fff;
    border: 2px solid #163a5f;
    border-radius: 10px;
    box-shadow: 0 14px 42px rgba(17, 43, 77, .22);
    color: #293f56;
    font: 13px/1.4 Arial, sans-serif;
}
#dm-menu-audit-1136 .dm-menu-audit-head {
    align-items: center;
    background: #163a5f;
    color: #fff;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 10px 12px;
}
#dm-menu-audit-1136 .dm-menu-audit-head strong {
    font-size: 15px;
}
#dm-menu-audit-1136 .dm-menu-audit-close {
    appearance: none;
    background: transparent;
    border: 0;
    color: #fff;
    cursor: pointer;
    font-size: 22px;
    font-weight: 700;
    line-height: 1;
    padding: 0 4px;
}
#dm-menu-audit-1136 .dm-menu-audit-body {
    padding: 12px;
}
#dm-menu-audit-1136 code {
    background: #f5f7fb;
    border: 1px solid #dce4ef;
    border-radius: 4px;
    color: #163a5f;
    display: block;
    margin: 4px 0 10px;
    overflow-wrap: anywhere;
    padding: 6px;
}
#dm-menu-audit-1136 table {
    border-collapse: collapse;
    width: 100%;
}
#dm-menu-audit-1136 th,
#dm-menu-audit-1136 td {
    border: 1px solid #dce4ef;
    padding: 6px 7px;
    text-align: left;
    vertical-align: top;
}
#dm-menu-audit-1136 th {
    background: #163a5f;
    color: #fff;
    position: sticky;
    top: 0;
}
#dm-menu-audit-1136 td:nth-child(4) {
    overflow-wrap: anywhere;
}
#dm-menu-audit-1136 .dm-menu-audit-muted {
    color: #60738a;
}
</style>
<script>
(function () {
    'use strict';

    function cleanText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function shortSelector(el) {
        if (!el || !el.tagName) return '';
        var parts = [];
        var cur = el;
        var depth = 0;
        while (cur && cur.nodeType === 1 && depth < 4) {
            var part = cur.tagName.toLowerCase();
            if (cur.id) {
                part += '#' + cur.id;
                parts.unshift(part);
                break;
            }
            if (cur.className && typeof cur.className === 'string') {
                var classes = cur.className.split(/\s+/).filter(Boolean).slice(0, 3);
                if (classes.length) part += '.' + classes.join('.');
            }
            parts.unshift(part);
            cur = cur.parentElement;
            depth += 1;
        }
        return parts.join(' > ');
    }

    function groupForAnchor(a) {
        var groups = [
            ['Domain section menu', '.dm-domain-section-menu'],
            ['DNS flow tabs', '.dm-dns-flow-tabs'],
            ['DNS tool groups', '.dm-dns-tool-groups'],
            ['DNS record shortcuts', '.dm-dns-record-shortcuts'],
            ['DNS task launcher', '.dm-dns-task-launcher'],
            ['DNS module toolbar', '.dm-dns-module-toolbar'],
            ['DNS active actions', '.dm-dns-active-actions'],
            ['WHMCS sidebar/panel', '.sidebar, .secondary-sidebar, .panel-sidebar, .list-group, .panel'],
            ['Main navbar', '#main-menu, .navbar-main, .main-navbar, .navbar'],
            ['Breadcrumb/header area', '.breadcrumb, .header-lined, .page-header']
        ];
        for (var i = 0; i < groups.length; i += 1) {
            try {
                if (a.closest(groups[i][1])) return groups[i][0];
            } catch (e) {}
        }
        return 'Other links';
    }

    function build() {
        if (document.getElementById('dm-menu-audit-1136')) return;

        var anchors = Array.prototype.slice.call(document.querySelectorAll('a[href]'));
        var rows = [];
        var seen = Object.create(null);

        anchors.forEach(function (a) {
            var text = cleanText(a.textContent);
            var href = a.getAttribute('href') || '';
            if (!text && !href) return;

            var group = groupForAnchor(a);
            var key = group + '|' + text + '|' + href;
            if (seen[key]) return;
            seen[key] = true;

            rows.push({
                group: group,
                text: text || '(no text)',
                selector: shortSelector(a),
                href: href,
                visible: !!(a.offsetWidth || a.offsetHeight || a.getClientRects().length)
            });
        });

        rows.sort(function (a, b) {
            return (a.group + a.text).localeCompare(b.group + b.text);
        });

        var panel = document.createElement('div');
        panel.id = 'dm-menu-audit-1136';
        panel.innerHTML = '<div class="dm-menu-audit-head"><strong>DM Menu Cleanup Audit 1136</strong><button type="button" class="dm-menu-audit-close" aria-label="Close">×</button></div>'
            + '<div class="dm-menu-audit-body">'
            + '<div class="dm-menu-audit-muted">URL</div><code>{$url}</code>'
            + '<div class="dm-menu-audit-muted">Visible/available menu links found: ' + rows.length + '</div>'
            + '<table><thead><tr><th>Group</th><th>Text</th><th>Visible</th><th>Href</th><th>Selector</th></tr></thead><tbody>'
            + rows.map(function (row) {
                return '<tr><td>' + escapeHtml(row.group) + '</td><td>' + escapeHtml(row.text) + '</td><td>' + (row.visible ? 'yes' : 'no') + '</td><td>' + escapeHtml(row.href) + '</td><td>' + escapeHtml(row.selector) + '</td></tr>';
            }).join('')
            + '</tbody></table></div>';

        document.body.appendChild(panel);
        panel.querySelector('.dm-menu-audit-close').addEventListener('click', function () {
            panel.remove();
        });
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (m) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', build);
    } else {
        build();
    }
})();
</script>
HTML;
});
