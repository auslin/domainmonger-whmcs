{* DomainMonger patch 872 + 1151: Private Nameservers Stage 10 post-save guidance polish.
   Design-only template override for domainmanagement.php?action=childns.
   Existing ResellerClub form actions, hidden fields, submit names, and values are preserved. *}

<style>
body.whmcsbody .dm-private-ns-wrap {
    --dm-navy: #163a5f;
    --dm-navy-hover: #214e7a;
    --dm-orange: #f58220;
    --dm-orange-soft: #d8741f;
    --dm-red: #b94a48;
    --dm-text: #293f56;
    --dm-muted: #60738a;
    --dm-border: rgba(17, 43, 77, 0.14);
    --dm-border-soft: rgba(17, 43, 77, 0.08);
    --dm-shadow: 0 2px 8px rgba(17, 43, 77, 0.055);
    color: var(--dm-text);
}
body.whmcsbody .sidebar,
body.whmcsbody .secondary-sidebar,
body.whmcsbody .panel-sidebar,
body.whmcsbody .dm-client-area-sidebar,
body.whmcsbody aside,
body.whmcsbody [class*="sidebar"] {
    display: none !important;
}
body.whmcsbody #main-body .primary-content,
body.whmcsbody #main-body .main-content,
body.whmcsbody #main-body .col-md-9,
body.whmcsbody #main-body .col-lg-9,
body.whmcsbody #main-body .col-xl-9 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
}
body.whmcsbody .dm-private-ns-title {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}
body.whmcsbody .dm-private-ns-title h2 {
    color: var(--dm-navy) !important;
    font-size: 24px;
    font-weight: 700;
    margin: 0;
}
body.whmcsbody .dm-private-ns-eyebrow,
body.whmcsbody .dm-card-kicker {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .06em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-subtitle {
    color: var(--dm-muted);
    font-size: 14px;
    margin-top: 4px;
}
body.whmcsbody .dm-private-ns-title-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}
body.whmcsbody .dm-private-ns-title-actions .btn {
    border-radius: 6px !important;
    font-size: 13px;
    font-weight: 700 !important;
    padding: 8px 12px;
}
body.whmcsbody .dm-private-ns-title-actions .btn-primary,
body.whmcsbody .dm-private-ns-title-actions .btn-default {
    background: var(--dm-navy) !important;
    border-color: var(--dm-navy) !important;
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-title-actions .btn-primary:hover,
body.whmcsbody .dm-private-ns-title-actions .btn-default:hover {
    background: var(--dm-navy-hover) !important;
    border-color: var(--dm-navy-hover) !important;
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-menu {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: flex;
    flex-wrap: nowrap;
    gap: 0;
    list-style: none;
    margin: 0 0 14px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 0;
    scrollbar-color: rgba(22, 58, 95, .28) transparent;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
}
body.whmcsbody .dm-private-ns-menu::-webkit-scrollbar { height: 8px; }
body.whmcsbody .dm-private-ns-menu::-webkit-scrollbar-track { background: transparent; }
body.whmcsbody .dm-private-ns-menu::-webkit-scrollbar-thumb {
    background: rgba(22, 58, 95, .24);
    border-radius: 999px;
}
body.whmcsbody .dm-private-ns-menu li { margin: 0; }
body.whmcsbody .dm-private-ns-menu a {
    border-right: 1px solid var(--dm-border-soft);
    color: var(--dm-navy) !important;
    display: block;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.15;
    min-width: max-content;
    padding: 11px 13px;
    text-decoration: none !important;
    white-space: nowrap;
}
body.whmcsbody .dm-private-ns-menu a:hover,
body.whmcsbody .dm-private-ns-menu a:focus {
    background: #fff4eb;
    color: var(--dm-orange-soft) !important;
    text-decoration: none !important;
}
body.whmcsbody .dm-private-ns-menu a.dm-active {
    background: var(--dm-orange);
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-menu span {
    color: inherit;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .04em;
    opacity: .76;
    text-transform: uppercase;
}

body.whmcsbody .dm-private-ns-status-strip {
    align-items: stretch;
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody .dm-private-ns-status-pill {
    align-items: center;
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: flex;
    gap: 10px;
    padding: 10px 12px;
}
body.whmcsbody .dm-private-ns-status-pill .dm-status-dot {
    background: var(--dm-orange);
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(245, 130, 32, .12);
    flex: 0 0 9px;
    height: 9px;
    width: 9px;
}
body.whmcsbody .dm-private-ns-status-pill span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 2px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-status-pill strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody .dm-card,
body.whmcsbody .dm-private-ns-alert-stack > .alert {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    margin-bottom: 14px;
    overflow: hidden;
}
body.whmcsbody .dm-card-header {
    align-items: center;
    background: var(--dm-navy);
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
}
body.whmcsbody .dm-card-header h3 {
    color: #fff !important;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0;
}
body.whmcsbody .dm-card-header span {
    color: rgba(255,255,255,.82);
    font-size: 12px;
    font-weight: 700;
}
body.whmcsbody .dm-card-body { padding: 16px; }
body.whmcsbody .dm-info-card {
    align-items: flex-start;
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .15);
    border-radius: 8px;
    display: flex;
    gap: 12px;
    margin-bottom: 14px;
    padding: 12px 14px;
}
body.whmcsbody .dm-info-icon {
    align-items: center;
    background: var(--dm-navy);
    border-radius: 50%;
    color: #fff;
    display: inline-flex;
    flex: 0 0 28px;
    font-weight: 800;
    height: 28px;
    justify-content: center;
    margin-top: 1px;
    width: 28px;
}
body.whmcsbody .dm-info-card strong {
    color: var(--dm-navy);
    display: block;
    margin-bottom: 2px;
}
body.whmcsbody .dm-info-card p {
    color: var(--dm-text);
    line-height: 1.45;
    margin: 0;
}
body.whmcsbody .dm-private-ns-summary-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 14px;
}
body.whmcsbody .dm-private-ns-summary-card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    padding: 13px 14px;
}
body.whmcsbody .dm-private-ns-summary-card span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-summary-card strong {
    color: var(--dm-navy);
    display: block;
    font-size: 15px;
    margin-bottom: 3px;
}
body.whmcsbody .dm-private-ns-summary-card p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.35;
    margin: 0;
}

body.whmcsbody .dm-private-ns-quickbar {
    align-items: stretch;
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin: 0 0 14px;
}
body.whmcsbody .dm-private-ns-quickitem {
    background: #fbfcfd;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 11px 12px;
}
body.whmcsbody .dm-private-ns-quickitem span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 3px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-quickitem strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    line-height: 1.25;
}
body.whmcsbody .dm-private-ns-helpnote {
    background: #fff8f1;
    border: 1px solid rgba(245, 130, 32, .18);
    border-radius: 8px;
    color: var(--dm-text);
    font-size: 12px;
    line-height: 1.45;
    margin: 10px 0 0;
    padding: 9px 10px;
}

body.whmcsbody .dm-private-ns-nextstep {
    align-items: flex-start;
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .14);
    border-radius: 8px;
    display: flex;
    gap: 10px;
    margin-top: 12px;
    padding: 10px;
}
body.whmcsbody .dm-private-ns-nextstep strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    margin-bottom: 2px;
}
body.whmcsbody .dm-private-ns-nextstep span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
}
body.whmcsbody .dm-private-ns-nextstep .btn {
    flex: 0 0 auto;
    margin-left: auto;
}

body.whmcsbody .dm-private-ns-example-row {
    background: #f7f9fb;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin: 10px 0 0;
    padding: 10px;
}
body.whmcsbody .dm-private-ns-example-row span {
    color: var(--dm-orange-soft);
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    margin-bottom: 2px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-example-row strong,
body.whmcsbody .dm-private-ns-example-row code {
    color: var(--dm-navy);
    font-size: 13px;
    font-weight: 700;
}
body.whmcsbody .dm-private-ns-example-row code {
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 5px;
    display: inline-block;
    padding: 3px 6px;
}

body.whmcsbody .dm-private-ns-workflow {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: grid;
    gap: 0;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin: 0 0 14px;
    overflow: hidden;
}
body.whmcsbody .dm-private-ns-workflow-step {
    align-items: flex-start;
    border-right: 1px solid var(--dm-border-soft);
    display: flex;
    gap: 10px;
    padding: 13px 14px;
}
body.whmcsbody .dm-private-ns-workflow-step:last-child { border-right: 0; }
body.whmcsbody .dm-private-ns-step-num {
    align-items: center;
    background: var(--dm-orange);
    border-radius: 50%;
    color: #fff;
    display: inline-flex;
    flex: 0 0 26px;
    font-size: 12px;
    font-weight: 800;
    height: 26px;
    justify-content: center;
    width: 26px;
}
body.whmcsbody .dm-private-ns-workflow-step strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    margin-bottom: 2px;
}
body.whmcsbody .dm-private-ns-workflow-step span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.35;
}
body.whmcsbody .dm-private-ns-host-name {
    display: block;
    overflow-wrap: anywhere;
}
body.whmcsbody .dm-private-ns-wrap .input-group .form-control {
    min-height: 38px;
}
body.whmcsbody .dm-private-ns-wrap .input-group-btn > .btn,
body.whmcsbody .dm-private-ns-wrap .input-group-addon,
body.whmcsbody .dm-private-ns-wrap .input-group-text {
    min-height: 38px;
}

body.whmcsbody .dm-private-ns-form-caption {
    color: var(--dm-muted);
    display: block;
    font-size: 11px;
    line-height: 1.35;
    margin-top: 5px;
}
body.whmcsbody .dm-private-ns-host-meta {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}
body.whmcsbody .dm-private-ns-host-meta span {
    background: #f7f9fb;
    border: 1px solid var(--dm-border-soft);
    border-radius: 999px;
    color: var(--dm-navy);
    font-size: 11px;
    font-weight: 700;
    padding: 4px 8px;
}
body.whmcsbody .dm-private-ns-current-values {
    align-items: center;
    background: #fff;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 9px;
    padding: 8px;
}
body.whmcsbody .dm-private-ns-current-values span,
body.whmcsbody .dm-private-ns-current-field span,
body.whmcsbody .dm-private-ns-live-preview span {
    color: var(--dm-orange-soft);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-current-values code,
body.whmcsbody .dm-private-ns-current-field code,
body.whmcsbody .dm-private-ns-live-preview code {
    background: #f7f9fb;
    border: 1px solid var(--dm-border-soft);
    border-radius: 5px;
    color: var(--dm-navy);
    display: inline-block;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 6px;
}
body.whmcsbody .dm-private-ns-current-field,
body.whmcsbody .dm-private-ns-live-preview {
    background: #fbfcfd;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    margin: 8px 0 10px;
    padding: 9px 10px;
}
body.whmcsbody .dm-private-ns-current-field span,
body.whmcsbody .dm-private-ns-live-preview span {
    display: block;
    margin-bottom: 4px;
}
body.whmcsbody .dm-private-ns-danger-note {
    background: #fff7f7;
    border: 1px solid rgba(185, 74, 72, .18);
    border-radius: 8px;
    color: var(--dm-text);
    font-size: 12px;
    line-height: 1.4;
    margin-top: 10px;
    padding: 8px 10px;
}
body.whmcsbody .dm-private-ns-host-toolbar {
    align-items: center;
    border-bottom: 1px solid var(--dm-border-soft);
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin: -2px 0 12px;
    padding-bottom: 10px;
}
body.whmcsbody .dm-private-ns-host-toolbar strong {
    color: var(--dm-navy);
    font-size: 13px;
}
body.whmcsbody .dm-private-ns-host-toolbar span {
    color: var(--dm-muted);
    font-size: 12px;
}
body.whmcsbody .dm-private-ns-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: minmax(280px, .82fr) minmax(360px, 1.18fr);
}
body.whmcsbody .dm-private-ns-card-mini {
    background: #fbfcfd;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    padding: 14px;
}
body.whmcsbody .dm-private-ns-card-mini h4 {
    color: var(--dm-navy);
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 5px;
}
body.whmcsbody .dm-private-ns-card-mini p {
    color: var(--dm-muted);
    font-size: 13px;
    line-height: 1.45;
    margin: 0 0 12px;
}
body.whmcsbody .dm-private-ns-form-row { margin-bottom: 12px; }
body.whmcsbody .dm-private-ns-form-row label,
body.whmcsbody .dm-private-ns-card-mini > label {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
}
body.whmcsbody .dm-private-ns-wrap .form-control {
    border-color: var(--dm-border) !important;
    box-shadow: none !important;
}
body.whmcsbody .dm-private-ns-wrap .form-control:focus {
    border-color: var(--dm-orange) !important;
    box-shadow: 0 0 0 .15rem rgba(245, 130, 32, .18) !important;
}
body.whmcsbody .dm-private-ns-wrap .input-group-addon,
body.whmcsbody .dm-private-ns-wrap .input-group-text {
    background: #f7f9fb;
    border-color: var(--dm-border);
    color: var(--dm-navy);
    font-weight: 700;
}
body.whmcsbody .dm-private-ns-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
body.whmcsbody .dm-private-ns-actions input,
body.whmcsbody .dm-private-ns-actions button,
body.whmcsbody .dm-private-ns-actions .btn {
    min-height: 36px;
}
body.whmcsbody .dm-private-ns-wrap .btn,
body.whmcsbody .dm-private-ns-wrap input[type="submit"].btn {
    border-radius: 6px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
}
body.whmcsbody .dm-private-ns-wrap a:focus-visible,
body.whmcsbody .dm-private-ns-wrap .btn:focus-visible,
body.whmcsbody .dm-private-ns-wrap input:focus-visible,
body.whmcsbody .dm-private-ns-wrap button:focus-visible {
    outline: 3px solid rgba(245, 130, 32, .35) !important;
    outline-offset: 2px !important;
}
body.whmcsbody .dm-private-ns-wrap .btn-success,
body.whmcsbody .dm-private-ns-wrap input.btn-success,
body.whmcsbody .dm-private-ns-wrap button.btn-success {
    background: var(--dm-orange) !important;
    border-color: var(--dm-orange) !important;
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-wrap .btn-success:hover,
body.whmcsbody .dm-private-ns-wrap input.btn-success:hover,
body.whmcsbody .dm-private-ns-wrap button.btn-success:hover {
    background: var(--dm-orange-soft) !important;
    border-color: var(--dm-orange-soft) !important;
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-wrap .btn-danger,
body.whmcsbody .dm-private-ns-wrap input.btn-danger {
    background: var(--dm-red) !important;
    border-color: var(--dm-red) !important;
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-host-list {
    display: grid;
    gap: 12px;
}
body.whmcsbody .dm-private-ns-host-card {
    background: #fff;
    border: 1px solid var(--dm-border);
    border-radius: 8px;
    overflow: hidden;
}
body.whmcsbody .dm-private-ns-host-header {
    align-items: center;
    background: #fbfcfd;
    border-bottom: 1px solid var(--dm-border-soft);
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 12px;
}
body.whmcsbody .dm-private-ns-host-header strong {
    color: var(--dm-navy);
    font-size: 15px;
}
body.whmcsbody .dm-private-ns-host-header span {
    background: #fff4eb;
    border: 1px solid rgba(245,130,32,.2);
    border-radius: 999px;
    color: var(--dm-orange-soft);
    font-size: 11px;
    font-weight: 800;
    padding: 4px 8px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-host-body {
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(240px, 1fr) minmax(240px, 1fr);
    padding: 12px;
}
body.whmcsbody .dm-private-ns-host-body strong {
    color: var(--dm-navy);
    display: block;
    font-size: 13px;
    margin-bottom: 2px;
}
body.whmcsbody .dm-private-ns-host-body p {
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.35;
    margin: 0 0 9px;
}
body.whmcsbody .dm-private-ns-ip-block {
    border-top: 1px solid var(--dm-border-soft);
    margin-top: 10px;
    padding-top: 10px;
}
body.whmcsbody .dm-private-ns-empty {
    background: #fbfcfd;
    border: 1px dashed var(--dm-border);
    border-radius: 8px;
    color: var(--dm-muted);
    padding: 14px;
}
body.whmcsbody .dm-private-ns-empty .btn {
    margin-top: 10px;
}
body.whmcsbody .dm-private-ns-required-note {
    background: #fff4eb;
    border: 1px solid rgba(245, 130, 32, .18);
    border-radius: 999px;
    color: var(--dm-orange-soft);
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .04em;
    margin-left: 6px;
    padding: 3px 7px;
    text-transform: uppercase;
}

body.whmcsbody .dm-private-ns-card-note {
    background: #f7f9fb;
    border: 1px solid var(--dm-border-soft);
    border-radius: 8px;
    color: var(--dm-muted);
    font-size: 12px;
    line-height: 1.45;
    margin: 10px 0 0;
    padding: 9px 10px;
}
body.whmcsbody .dm-private-ns-maintenance-note {
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .14);
    border-radius: 8px;
    color: var(--dm-text);
    font-size: 12px;
    line-height: 1.45;
    margin: 0 0 12px;
    padding: 10px 12px;
}
body.whmcsbody .dm-private-ns-maintenance-note strong {
    color: var(--dm-navy);
}
body.whmcsbody .dm-private-ns-section-label {
    align-items: center;
    color: var(--dm-orange-soft);
    display: flex;
    font-size: 10px;
    font-weight: 800;
    gap: 6px;
    letter-spacing: .05em;
    margin-bottom: 7px;
    text-transform: uppercase;
}
body.whmcsbody .dm-private-ns-section-label:before {
    background: var(--dm-orange);
    border-radius: 999px;
    content: "";
    display: inline-block;
    height: 6px;
    width: 6px;
}
body.whmcsbody .dm-private-ns-form-hint {
    color: var(--dm-muted);
    display: block;
    font-size: 11px;
    line-height: 1.35;
    margin: 6px 0 0;
}
body.whmcsbody .dm-private-ns-host-actions-note {
    border-top: 1px solid var(--dm-border-soft);
    color: var(--dm-muted);
    font-size: 11px;
    line-height: 1.35;
    margin-top: 10px;
    padding-top: 9px;
}

body.whmcsbody .dm-private-ns-save-note {
    background: #eef5fc;
    border: 1px solid rgba(22, 58, 95, .14);
    border-radius: 8px;
    color: var(--dm-text);
    font-size: 12px;
    line-height: 1.45;
    margin-top: 10px;
    padding: 9px 10px;
}
body.whmcsbody .dm-private-ns-save-note strong {
    color: var(--dm-navy);
}
body.whmcsbody .dm-private-ns-final-guidance {
    align-items: center;
    background: #fff;
    border: 1px solid var(--dm-border);
    border-left: 4px solid var(--dm-orange);
    border-radius: 8px;
    box-shadow: var(--dm-shadow);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-top: 14px;
    padding: 12px 14px;
}
body.whmcsbody .dm-private-ns-final-guidance strong {
    color: var(--dm-navy);
    display: block;
    font-size: 14px;
    margin-bottom: 2px;
}
body.whmcsbody .dm-private-ns-final-guidance span {
    color: var(--dm-muted);
    display: block;
    font-size: 12px;
    line-height: 1.4;
}
body.whmcsbody .dm-private-ns-final-guidance-actions {
    align-items: center;
    display: flex;
    flex: 0 0 auto;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}
body.whmcsbody .dm-private-ns-final-guidance-actions .btn-default {
    background: var(--dm-navy) !important;
    border-color: var(--dm-navy) !important;
    color: #fff !important;
}
body.whmcsbody .dm-private-ns-final-guidance-actions .btn-default:hover {
    background: var(--dm-navy-hover) !important;
    border-color: var(--dm-navy-hover) !important;
    color: #fff !important;
}

body.whmcsbody .dm-private-ns-alert-stack > br { display: none; }
body.whmcsbody .dm-private-ns-alert-stack .alert { box-shadow: none; overflow: visible; padding: 12px 14px; }
@media (max-width: 991px) {
    body.whmcsbody .dm-private-ns-title,
    body.whmcsbody .dm-card-header,
    body.whmcsbody .dm-private-ns-host-header,
    body.whmcsbody .dm-private-ns-final-guidance { align-items: flex-start; flex-direction: column; }
    body.whmcsbody .dm-private-ns-final-guidance-actions { justify-content: flex-start; }
    body.whmcsbody .dm-private-ns-grid,
    body.whmcsbody .dm-private-ns-host-body,
    body.whmcsbody .dm-private-ns-summary-grid,
    body.whmcsbody .dm-private-ns-quickbar,
    body.whmcsbody .dm-private-ns-status-strip,
    body.whmcsbody .dm-private-ns-example-row,
    body.whmcsbody .dm-private-ns-workflow { grid-template-columns: 1fr; }
    body.whmcsbody .dm-private-ns-workflow-step { border-right: 0; border-bottom: 1px solid var(--dm-border-soft); }
    body.whmcsbody .dm-private-ns-workflow-step:last-child { border-bottom: 0; }
}
</style>

<script language="javascript" type="text/javascript">
    function confirmDelete(){literal}{{/literal}return confirm("{$LANG.rcdom_childnsdeletewarn}");{literal}}{/literal}
</script>

<div class="dm-private-ns-wrap">
    <ul class="dm-private-ns-menu" aria-label="Domain management sections">
        <li><a href="clientarea.php?action=domaindetails&id={$domainid}"><span>Domain</span>Overview</a></li>
        <li><a href="clientarea.php?action=domaindetails&id={$domainid}#tabAutorenew"><span>Domain</span>Auto Renew</a></li>
        <li><a href="clientarea.php?action=domaindetails&id={$domainid}#tabNameservers"><span>DNS</span>Nameservers</a></li>
        <li><a href="clientarea.php?action=domaindetails&id={$domainid}#tabReglock"><span>Security</span>Registrar Lock</a></li>
        <li><a href="clientarea.php?action=domaindetails&id={$domainid}#tabAddons"><span>Domain</span>Addons</a></li>
        <li><a href="clientarea.php?action=domaincontacts&domainid={$domainid}"><span>Contacts</span>WHOIS Contact Info</a></li>
        <li><a class="dm-active" aria-current="page" href="domainmanagement.php?action=childns&id={$domainid}"><span>DNS</span>Private Nameservers</a></li>
        <li><a href="dnsmanagement.php?action=managednszone&domain={$domain|escape:'url'}&domainid={$domainid}"><span>DNS</span>DNS Management</a></li>
        <li><a href="domainmanagement.php?action=dnssec&id={$domainid}"><span>DNS</span>DNSSEC Management</a></li>
        <li><a href="domainforwarding.php?domainid={$domainid}"><span>Forwarding</span>Domain Forwarding</a></li>
        <li><a href="emailmanagement.php?domainid={$domainid}"><span>Forwarding</span>Email Forwarding</a></li>
        <li><a href="clientarea.php?action=domaingetepp&id={$domainid}"><span>Security</span>Get EPP Code</a></li>
    </ul>

    {* Patch 1151: removed the temporary conversion/status/workflow scaffold.
       The shared domain menu, live alerts, and registrar-backed forms remain. *}

    <div class="dm-private-ns-alert-stack">
        {if $modifydnshosterror}
            <br />
            <div class="alert alert-danger">
                <p>{$LANG.clientareaerrors}</p>
                <ul>{$modifydnshosterror}</ul>
            </div>
        {/if}
        {if $modifydnshostsuccess}
            <br />
            <div class="alert alert-success">
                <p>{$LANG.moduleactionsuccess}</p>
                <ul>{$modifydnshostsuccess}</ul>
            </div>
        {/if}
        {if $modifydnsiperror}
            <br />
            <div class="alert alert-danger">
                <p>{$LANG.clientareaerrors}</p>
                <ul>{$modifydnsiperror}</ul>
            </div>
        {/if}
        {if $modifydnsipsuccess}
            <br />
            <div class="alert alert-success">
                <p>{$LANG.moduleactionsuccess}</p>
                <ul>{$modifydnsipsuccess}</ul>
            </div>
        {/if}
        {if $deletednshosterror}
            <br />
            <div class="alert alert-danger">
                <p>{$LANG.clientareaerrors}</p>
                <ul>{$deletednshosterror}</ul>
            </div>
        {/if}
        {if $deletednshostsuccess}
            <br />
            <div class="alert alert-success">
                <p>{$LANG.moduleactionsuccess}</p>
                <ul>{$deletednshostsuccess}</ul>
            </div>
        {/if}
        {if $addchildnserror}
            <br />
            <div class="alert alert-danger">
                <p>{$LANG.clientareaerrors}</p>
                <ul>{$addchildnserror}</ul>
            </div>
        {/if}
        {if $addchildnssuccess}
            <br />
            <div class="alert alert-success">
                <p>{$LANG.moduleactionsuccess}</p>
                <ul>{$addchildnssuccess}</ul>
            </div>
        {/if}
    </div>

    <div class="dm-card">
        <div class="dm-card-header">
            <h3>Private Nameservers</h3>
            <span>Custom DNS Hosts</span>
        </div>
        <div class="dm-card-body">
            <div class="dm-private-ns-grid">
                <div class="dm-private-ns-card-mini" id="dm-private-ns-create">
                    <span class="dm-card-kicker">Create</span>
                    <h4>{$LANG.rcdom_newregisterns}</h4>
                    <p>Create a new private nameserver host and point it to an IP address. Enter only the host label, such as <strong>ns1</strong>; the domain suffix is added automatically.</p>

                    <form method="post" action="domainmanagement.php?action=childns">
                        <input type="hidden" name="addregchildns" value="true"/>
                        <input type="hidden" name="domainid" value="{$domainid}"/>
                        <input type="hidden" name="domain" value="{$domain}"/>
                        <div class="dm-private-ns-form-row">
                            <label>{$LANG.rcdom_dnsnametitle}<span class="dm-private-ns-required-note">Required</span></label>
                            <div class="input-group">
                                <input name="cnsname" class="form-control" type="text" value="{$smarty.post.cnsname}" size="10" placeholder="ns1"/>
                                <span class="input-group-addon" id="basic-addon2">.{$domain}</span>
                            </div>
                            <span class="dm-private-ns-form-caption">Enter only the host label. DomainMonger adds the domain suffix automatically.</span>
                            <div class="dm-private-ns-live-preview">
                                <span>Full Hostname Example</span>
                                <code>ns1.{$domain}</code>
                            </div>
                        </div>
                        <div class="dm-private-ns-form-row">
                            <label>{$LANG.rcdom_dnsiptitle}<span class="dm-private-ns-required-note">Required</span></label>
                            <input name="cnsip" class="form-control" type="text" value="{$smarty.post.cnsip}" size="15" placeholder="192.0.2.10"/>
                            <span class="dm-private-ns-form-caption">Use the IP address for the server that should answer for this private nameserver.</span>
                        </div>
                        <div class="dm-private-ns-example-row" aria-label="Private nameserver example">
                            <div>
                                <span>Example Host</span>
                                <code>ns1.{$domain}</code>
                            </div>
                            <div>
                                <span>Example IP</span>
                                <strong>192.0.2.10</strong>
                            </div>
                        </div>
                        <div class="dm-private-ns-helpnote">Enter the host label only. For example, enter <strong>ns1</strong>, not <strong>ns1.{$domain}</strong>.</div>
                        <div class="dm-private-ns-card-note"><strong>Recommended:</strong> Create both <strong>ns1</strong> and <strong>ns2</strong> when your server or DNS setup provides two usable IP addresses.</div>
                        <div class="dm-private-ns-actions">
                            <input type="submit" value="{$LANG.rcdom_regcnsbutton}" class="btn btn-success"/>
                        </div>
                        <div class="dm-private-ns-save-note"><strong>After saving:</strong> This creates the private nameserver host at the registry. To make this domain use it, open Nameservers and set the domain to use the new hostnames.</div>
                    </form>
                </div>

                <div class="dm-private-ns-card-mini" id="dm-private-ns-registered">
                    <span class="dm-card-kicker">Existing Hosts</span>
                    <h4>{$LANG.rcdom_registeredchildnstitle}</h4>
                    <p>Review registered hosts below. Each existing host keeps the original update and delete forms, only restyled into this card layout.</p>
                    <div class="dm-private-ns-maintenance-note"><strong>Safe editing:</strong> Each update button submits only the matching hostname or IP address form. Delete remains a separate action and still asks for confirmation.</div>

                    {if !empty($childnsarray.dns0.0)}
                        <div class="dm-private-ns-host-toolbar">
                            <strong>Registered Private Nameservers</strong>
                            <span>Update hostnames, update IP addresses, or delete unused hosts.</span>
                        </div>
                        <div class="dm-private-ns-host-list">
                            {foreach key=dnskey item=dnsname from=$childnsarray}
                                <div class="dm-private-ns-host-card">
                                    <div class="dm-private-ns-host-header">
                                        <div>
                                            <strong class="dm-private-ns-host-name">{$dnsname.0}</strong>
                                            <div class="dm-private-ns-host-meta">
                                                <span>Host record</span>
                                                <span>Private nameserver</span>
                                            </div>
                                            <div class="dm-private-ns-current-values" aria-label="Current private nameserver values">
                                                <span>Host</span>
                                                <code>{$dnsname.0}</code>
                                                {foreach key=currentIpKey item=currentDnsIp from=$dnsname.1}
                                                    <span>IP</span>
                                                    <code>{$currentDnsIp}</code>
                                                {/foreach}
                                            </div>
                                        </div>
                                        <span>Registered</span>
                                    </div>
                                    <div class="dm-private-ns-host-body">
                                        <div>
                                            <div class="dm-private-ns-section-label">Hostname</div>
                                            <strong>{$LANG.rcdom_changenshostnametitle}</strong>
                                            <p>{$LANG.rcdom_changenshostnamedesc}</p>
                                            <div class="dm-private-ns-current-field">
                                                <span>Current Hostname</span>
                                                <code>{$dnsname.0}</code>
                                            </div>
                                            <form method="post" action="domainmanagement.php?action=childns">
                                                <input type="hidden" name="modifydnshost" value="true"/>
                                                <input type="hidden" name="oldcns" value="{$dnsname.0}"/>
                                                <input type="hidden" name="domainid" value="{$domainid}"/>
                                                <input type="hidden" name="domain" value="{$domain}"/>
                                                <div class="input-group">
                                                    <input name="newcns" type="text" class="form-control" value="{$dnsname.0}" size="25"/>
                                                    <span class="input-group-btn">
                                                        <button name="submit" type="submit" class="btn btn-success">{$LANG.rcdom_modcnsbutton}</button>
                                                    </span>
                                                </div>
                                                <span class="dm-private-ns-form-hint">Use this only when the registered private nameserver hostname itself needs to change.</span>
                                            </form>
                                        </div>
                                        <div>
                                            {foreach key=ipkey item=dnsip from=$dnsname.1}
                                                <div class="dm-private-ns-ip-block">
                                                    <div class="dm-private-ns-section-label">IP Address</div>
                                                    <strong>{$LANG.rcdom_changenshostiptitle}</strong>
                                                    <p>{$LANG.rcdom_changenshostipdesc}</p>
                                                    <div class="dm-private-ns-current-field">
                                                        <span>Current IP Address</span>
                                                        <code>{$dnsip}</code>
                                                    </div>
                                                    <form method="post" action="domainmanagement.php?action=childns">
                                                        <input type="hidden" name="modifydnsip" value="true"/>
                                                        <input type="hidden" name="currentcns" value="{$dnsname.0}"/>
                                                        <input type="hidden" name="oldip" value="{$dnsip}"/>
                                                        <input type="hidden" name="domainid" value="{$domainid}"/>
                                                        <input type="hidden" name="domain" value="{$domain}"/>
                                                        <div class="input-group">
                                                            <input class="form-control" name="newip" type="text" value="{$dnsip}" size="15"/>
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-success" type="submit">{$LANG.rcdom_modcnsipbutton}</button>
                                                            </span>
                                                        </div>
                                                        <span class="dm-private-ns-form-hint">Use this when the server IP for this private nameserver changes.</span>
                                                    </form>
                                                    <form method="post" action="domainmanagement.php?action=childns">
                                                        <input type="hidden" name="delregchildns" value="true"/>
                                                        <input type="hidden" name="currentcns" value="{$dnsname.0}"/>
                                                        <input type="hidden" name="currentip" value="{$dnsip}"/>
                                                        <input type="hidden" name="domainid" value="{$domainid}"/>
                                                        <input type="hidden" name="domain" value="{$domain}"/>
                                                        <div class="dm-private-ns-danger-note">Deleting this host removes the private nameserver record from the registry. You will still be asked to confirm before it is deleted.</div>
                                                        <div class="dm-private-ns-actions">
                                                            <input class="btn btn-danger" type="submit" value="{$LANG.rcdom_delcnsbutton}" onclick="return confirmDelete();" />
                                                        </div>
                                                        <div class="dm-private-ns-host-actions-note">This delete action uses the existing registrar delete flow and confirmation prompt.</div>
                                                    </form>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                        <div class="dm-private-ns-save-note"><strong>Before updating:</strong> Review the current-value chips above each field. Hostname, IP address, and delete actions submit through separate existing registrar forms.</div>
                    {else}
                        <div class="dm-private-ns-empty">No private nameservers are currently registered for this domain.<br /><a class="btn btn-success" href="#dm-private-ns-create">Add First Host</a></div>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="dm-private-ns-final-guidance">
        <div>
            <strong>Private nameserver hosts are separate from the domain nameserver setting.</strong>
            <span>Create or update hosts here, then use the Nameservers section when you want this domain to point to those private nameservers.</span>
        </div>
        <div class="dm-private-ns-final-guidance-actions">
            <a class="btn btn-default" href="clientarea.php?action=domaindetails&id={$domainid}#tabNameservers">Open Nameservers</a>
            <a class="btn btn-default" href="clientarea.php?action=domaindetails&id={$domainid}">Domain Overview</a>
        </div>
    </div>
</div>
