{* DomainMonger Patch 218: native v9 standard_cart_2 diagnostic with pre-parse suggestion normalization. *}
{* Purpose: test whether blank native suggestion names are caused by /domain/check returning domainName without sld/tld. *}
{* Does not touch english.php. *}
<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/templates/orderforms/standard_cart_2/css/all.min.css?v={$versionHash}" />

{literal}
<script>
(function($) {
    'use strict';

    function requestIsSuggestions(data) {
        if (!data) {
            return false;
        }
        if (typeof data === 'string') {
            return data.indexOf('type=suggestions') !== -1;
        }
        if ($.isArray(data)) {
            return data.some(function(item) {
                return item && item.name === 'type' && item.value === 'suggestions';
            });
        }
        return data.type === 'suggestions';
    }

    function parseFullName(domain) {
        if (!domain || typeof domain !== 'object') {
            return null;
        }
        var fullName = domain.domainName || domain.idnDomainName || domain.domain || '';
        fullName = String(fullName || '').toLowerCase()
            .replace(/^\s+|\s+$/g, '')
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .split('/')[0];
        if (fullName.indexOf('.') === -1) {
            return null;
        }
        var firstDot = fullName.indexOf('.');
        return {
            sld: fullName.substring(0, firstDot),
            tld: fullName.substring(firstDot + 1),
            domainName: fullName
        };
    }

    function normalizeDomain(domain) {
        var parsed = parseFullName(domain);
        if (!parsed) {
            return;
        }

        if (!domain.sld) {
            domain.sld = parsed.sld;
        }
        if (!domain.tld) {
            domain.tld = parsed.tld;
        }
        domain.tld = String(domain.tld || '').replace(/^\./, '');

        if (!domain.tldNoDots && domain.tld) {
            domain.tldNoDots = domain.tld.replace(/\./g, '');
        }
        if (!domain.domainName) {
            domain.domainName = parsed.domainName;
        }
        if (!domain.idnDomainName) {
            domain.idnDomainName = domain.domainName;
        }
    }

    function normalizeResponseObject(obj) {
        if (!obj || !$.isArray(obj.result)) {
            return obj;
        }
        $.each(obj.result, function(index, domain) {
            normalizeDomain(domain);
        });
        return obj;
    }

    $.ajaxPrefilter(function(options, originalOptions) {
        var requestData = options.data || originalOptions.data;
        if (!requestIsSuggestions(requestData)) {
            return;
        }

        var existingFilter = options.dataFilter;
        options.dataFilter = function(rawData, dataType) {
            var filtered = existingFilter ? existingFilter(rawData, dataType) : rawData;
            if (typeof filtered !== 'string') {
                return filtered;
            }
            try {
                var parsed = JSON.parse(filtered);
                normalizeResponseObject(parsed);
                return JSON.stringify(parsed);
            } catch (e) {
                return filtered;
            }
        };
    });
})(jQuery);
</script>
{/literal}

<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/standard_cart_2/js/scripts.min.js?v={$versionHash}"></script>

{literal}
<script>
(function($) {
    'use strict';

    function splitDomain(fullName) {
        fullName = String(fullName || '').toLowerCase()
            .replace(/^\s+|\s+$/g, '')
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .split('/')[0];
        if (fullName.indexOf('.') === -1) {
            return null;
        }
        var firstDot = fullName.indexOf('.');
        return {
            sld: fullName.substring(0, firstDot),
            tld: fullName.substring(firstDot)
        };
    }

    function repairVisibleSuggestionRows() {
        $('#domainSuggestions li.domain-suggestion').each(function() {
            var $row = $(this);
            var $domain = $row.find('span.domain:first');
            var $extension = $row.find('span.extension:first');
            if ($.trim($domain.text()) || $.trim($extension.text())) {
                return;
            }
            var fullName = $row.find('button.btn-add-to-cart:first').attr('data-domain') || '';
            var parts = splitDomain(fullName);
            if (!parts) {
                return;
            }
            $domain.text(parts.sld);
            $extension.text(parts.tld);
        });
    }

    $(function() {
        repairVisibleSuggestionRows();
        var target = document.getElementById('domainSuggestions');
        if (target && window.MutationObserver) {
            new MutationObserver(function() {
                repairVisibleSuggestionRows();
            }).observe(target, { childList: true, subtree: true, attributes: true, characterData: true });
        }
        var attempts = 0;
        var interval = setInterval(function() {
            repairVisibleSuggestionRows();
            attempts++;
            if (attempts > 40) {
                clearInterval(interval);
            }
        }, 250);
    });
})(jQuery);
</script>
{/literal}
