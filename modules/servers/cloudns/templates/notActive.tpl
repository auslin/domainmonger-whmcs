
<!-- DomainMonger Patch 64 ClouDNS global late visual override loader -->
<script type="text/javascript">
(function () {
    var cssHref = '{$WEB_ROOT}/modules/servers/cloudns/templates/cloudns-v65-late-override.css?v=1309';
    var cssId = 'dm-cloudns-v65-late-override';
    function loadCloudnsLateOverride() {
        if (document.getElementById(cssId)) {
            return;
        }
        var link = document.createElement('link');
        link.id = cssId;
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = cssHref;
        (document.body || document.getElementsByTagName('body')[0] || document.head).appendChild(link);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadCloudnsLateOverride);
    } else {
        loadCloudnsLateOverride();
    }
})();
</script>

<h3>This service is not active and you can't manage the DNS. If you think this is a misstake please contact the support.</h3>