{include file="orderforms/standard_cart_2/common-v9-direct-renderer.tpl"}

<div id="order-standard_cart">
    <div class="dm-v8x-patch272-label">DM v8x Register Patch 272 active <span>Patch 221 renderer restore</span></div>
    <div class="row">
        <div class="cart-sidebar col-md-3">
            {include file="orderforms/standard_cart/sidebar-categories.tpl"}
        </div>

        <div class="cart-body col-md-9 dm-v9-direct-page">
            <div class="header-lined">
                <h1>Register a New Domain</h1>
            </div>

            <div class="dm-v9-direct-search-card">
                <div class="dm-v9-direct-search-header">
                    <h2>Find your domain</h2>
                </div>
                <div class="dm-v9-direct-search-body">
                    <form method="post" action="#" id="dmV9DirectForm">
                        <input type="hidden" name="token" value="{$token}">
                        <div class="input-group input-group-lg input-group-box dm-v9-direct-search-box">
                            <input type="text"
                                   id="dmV9DirectInput"
                                   class="form-control"
                                   value="{$message|default:$lookupTerm|escape}"
                                   placeholder="Example: volleyball for women">
                            <span class="input-group-btn input-group-append">
                                <button type="submit" id="dmV9DirectButton" class="btn btn-primary">Search</button>
                            </span>
                        </div>
                        <div class="dm-v9-direct-options">
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".com" checked> .com</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".net" checked> .net</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".org" checked> .org</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".us" checked> .us</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".biz" checked> .biz</label>
                            <label><input type="checkbox" class="dm-v9-direct-tld" value=".ca" checked> .ca</label>
                            <label class="dm-v9-direct-safe"><input type="checkbox" id="dmV9DirectSafeSearch"> Safe Search</label>
                        </div>
                    </form>
                </div>
            </div>

            <div id="dmV9DirectStatus" class="alert dm-v9-direct-status" style="display:none;"></div>

            <div id="dmV9DirectResultsCard" class="dm-v9-direct-results-card" style="display:none;">
                <div class="dm-v9-direct-results-header">
                    <h3>Suggested Domains</h3>
                    <span id="dmV9DirectResultsCount" class="dm-v9-direct-results-count"></span>
                </div>
                <div class="dm-v9-direct-results-body">
                    <div class="dm-v9-direct-table-wrap">
                        <table class="table table-striped dm-v9-direct-results-table">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th class="dm-v9-direct-action-cell">Action</th>
                                </tr>
                            </thead>
                            <tbody id="dmV9DirectResults"></tbody>
                        </table>
                    </div>
                    <div id="dmV9DirectEmpty" class="dm-v9-direct-empty" style="display:none;">
                        No v9 suggestion names or context lookups were returned.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
(function($) {
    'use strict';

    var activeRun = 0;
    var maxConcurrentChecks = 5;

    function getEndpoint() {
        if (window.WHMCS && WHMCS.utils && typeof WHMCS.utils.getRouteUrl === 'function') {
            return WHMCS.utils.getRouteUrl('/domain/check');
        }
        return (window.whmcsBaseUrl || '') + '/index.php?rp=/domain/check';
    }

    function getToken() {
        if (typeof window.csrfToken !== 'undefined' && window.csrfToken) {
            return window.csrfToken;
        }
        return $('input[name="token"]').val() || '';
    }

    function cleanDomain(fullName) {
        return String(fullName || '')
            .toLowerCase()
            .replace(/^\s+|\s+$/g, '')
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .split('/')[0]
            .replace(/[^a-z0-9.-]/g, '');
    }

    function splitDomain(fullName) {
        fullName = cleanDomain(fullName);
        if (fullName.indexOf('.') === -1) {
            return null;
        }
        var firstDot = fullName.indexOf('.');
        var sld = fullName.substring(0, firstDot);
        var tld = fullName.substring(firstDot);
        if (!sld || !tld || sld.length < 2) {
            return null;
        }
        return { domain: sld + tld, sld: sld, tld: tld };
    }

    function getSelectedTlds() {
        var tlds = [];
        $('.dm-v9-direct-tld:checked').each(function() {
            tlds.push($(this).val());
        });
        return tlds.length ? tlds : ['.com'];
    }

    function extractSuggestionDomains(data) {
        var out = [];
        var seen = {};
        if (!data || !data.result || !$.isArray(data.result)) {
            return out;
        }
        $.each(data.result, function(index, item) {
            var name = '';
            var parts;
            if (!item) {
                return;
            }
            if (typeof item === 'string') {
                name = item;
            } else {
                name = item.domainName || item.idnDomainName || item.domain || '';
                if (!name && item.sld && item.tld) {
                    name = item.sld + '.' + String(item.tld).replace(/^\./, '');
                }
            }
            parts = splitDomain(name);
            if (!parts || seen[parts.domain]) {
                return;
            }
            seen[parts.domain] = true;
            out.push(parts);
        });
        return out;
    }


    function cleanSld(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '')
            .replace(/^-+|-+$/g, '')
            .replace(/-{2,}/g, '-');
    }

    function splitCamelAndBoundaries(value) {
        return String(value || '')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/([a-zA-Z])([0-9])/g, '$1 $2')
            .replace(/([0-9])([a-zA-Z])/g, '$1 $2');
    }

    function wordsFromInput(input) {
        var cleaned = splitCamelAndBoundaries(input)
            .toLowerCase()
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .replace(/\.[a-z0-9.-]+$/i, '')
            .replace(/[^a-z0-9]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        var allWords = cleaned ? cleaned.split(' ') : [];
        var stopWords = {
            'a': true,
            'an': true,
            'and': true,
            'of': true,
            'the': true,
            'to': true,
            'with': true
        };
        var meaningful = [];
        var seenWords = {};
        $.each(allWords, function(index, word) {
            if (word && !stopWords[word] && !seenWords[word]) {
                seenWords[word] = true;
                meaningful.push(word);
            }
        });
        return {
            all: allWords,
            meaningful: meaningful.length ? meaningful : allWords
        };
    }

    function addContextDomain(list, seen, sld, tld, source) {
        var parts;
        sld = cleanSld(sld);
        tld = String(tld || '.com').toLowerCase();
        if (!sld || sld.length < 3 || sld.length > 63 || !/^[a-z0-9][a-z0-9-]*[a-z0-9]$/.test(sld)) {
            return;
        }
        if (!/^\.[a-z0-9.-]+$/.test(tld)) {
            return;
        }
        parts = splitDomain(sld + tld);
        if (!parts || seen[parts.domain]) {
            return;
        }
        seen[parts.domain] = true;
        parts.source = source || 'context lookup';
        list.push(parts);
    }

    function replaceWord(words, from, to) {
        var out = [];
        var changed = false;
        $.each(words, function(index, word) {
            if (word === from) {
                out.push(to);
                changed = true;
            } else {
                out.push(word);
            }
        });
        return changed ? out : null;
    }

    function singularizeLast(words) {
        var out;
        if (!words.length) {
            return null;
        }
        out = words.slice(0);
        if (out[out.length - 1] === 'women') {
            out[out.length - 1] = 'woman';
            return out;
        }
        if (out[out.length - 1] === 'men') {
            out[out.length - 1] = 'man';
            return out;
        }
        if (out[out.length - 1].length > 4 && /s$/.test(out[out.length - 1])) {
            out[out.length - 1] = out[out.length - 1].replace(/s$/, '');
            return out;
        }
        return null;
    }

    function generateContextLookupDomains(input, existingDomains) {
        var parts = wordsFromInput(input);
        var phraseWords = parts.all.length ? parts.all : parts.meaningful;
        var meaningful = parts.meaningful;
        var base = phraseWords.join('');
        var hyphenBase = phraseWords.join('-');
        var singularWords = singularizeLast(phraseWords);
        var numberWords = replaceWord(phraseWords, 'for', '4');
        var list = [];
        var seen = {};
        var relatedFirst = [];
        var sportRelated = {
            volleyball: ['basketball', 'soccer'],
            basketball: ['volleyball', 'soccer'],
            soccer: ['volleyball', 'basketball'],
            football: ['basketball', 'soccer'],
            baseball: ['softball', 'basketball'],
            softball: ['baseball', 'volleyball']
        };
        var i;

        $.each(existingDomains || [], function(index, item) {
            if (item && item.domain) {
                seen[item.domain] = true;
            }
        });

        if (!phraseWords.length) {
            return list;
        }

        if (sportRelated[phraseWords[0]]) {
            relatedFirst = sportRelated[phraseWords[0]];
            addContextDomain(list, seen, [relatedFirst[0]].concat(phraseWords.slice(1)).join(''), '.com', 'context related word');
        }

        if (singularWords) {
            addContextDomain(list, seen, singularWords.join(''), '.com', 'context singular/plural');
        }

        if (numberWords) {
            addContextDomain(list, seen, numberWords.join(''), '.com', 'context number substitution');
        }

        addContextDomain(list, seen, 'little' + base, '.com', 'context prefix');
        if (relatedFirst.length > 1) {
            addContextDomain(list, seen, [relatedFirst[1]].concat(phraseWords.slice(1)).join(''), '.net', 'context related word');
        }
        addContextDomain(list, seen, base + 'today', '.com', 'context suffix');
        addContextDomain(list, seen, base, '.ca', 'context alternate TLD');
        addContextDomain(list, seen, base, '.us', 'context alternate TLD');
        if (singularWords) {
            addContextDomain(list, seen, singularWords.join(''), '.net', 'context singular/plural alternate TLD');
        }
        addContextDomain(list, seen, base + 'today', '.net', 'context suffix alternate TLD');
        if (phraseWords.length > 1) {
            addContextDomain(list, seen, hyphenBase, '.ca', 'context hyphen alternate TLD');
            addContextDomain(list, seen, hyphenBase, '.biz', 'context hyphen alternate TLD');
        }
        if (relatedFirst.length > 1) {
            addContextDomain(list, seen, [relatedFirst[1]].concat(phraseWords.slice(1)).join(''), '.biz', 'context related word');
        }
        addContextDomain(list, seen, base, '.info', 'context alternate TLD');
        addContextDomain(list, seen, base, '.co', 'context alternate TLD');
        if (numberWords) {
            addContextDomain(list, seen, numberWords.join(''), '.net', 'context number substitution alternate TLD');
            addContextDomain(list, seen, numberWords.join(''), '.org', 'context number substitution alternate TLD');
        }
        addContextDomain(list, seen, 'little' + base, '.net', 'context prefix alternate TLD');
        addContextDomain(list, seen, base + 'today', '.org', 'context suffix alternate TLD');
        addContextDomain(list, seen, base + 'online', '.com', 'context suffix');
        addContextDomain(list, seen, 'my' + base, '.com', 'context prefix');
        addContextDomain(list, seen, 'get' + base, '.com', 'context prefix');

        if (relatedFirst.length) {
            $.each(relatedFirst, function(index, relatedWord) {
                addContextDomain(list, seen, [relatedWord].concat(phraseWords.slice(1)).join(''), '.org', 'context related word alternate TLD');
                addContextDomain(list, seen, [relatedWord].concat(phraseWords.slice(1)).join(''), '.ca', 'context related word alternate TLD');
                addContextDomain(list, seen, [relatedWord].concat(phraseWords.slice(1)).join(''), '.us', 'context related word alternate TLD');
            });
        }

        if (meaningful.length >= 2) {
            addContextDomain(list, seen, meaningful.join(''), '.com', 'context meaningful words');
            addContextDomain(list, seen, meaningful.join('-'), '.com', 'context meaningful words');
            for (i = 0; i < meaningful.length - 1; i++) {
                addContextDomain(list, seen, meaningful[i] + meaningful[i + 1], '.com', 'context adjacent word pair');
            }
            addContextDomain(list, seen, meaningful[meaningful.length - 1] + meaningful[0], '.com', 'context last + first');
        }

        return list.slice(0, 35);
    }

    function parseAvailabilityResponse(request, data) {
        var result = {
            requestedDomain: request.domain,
            returnedDomain: request.domain,
            status: 'Unknown',
            price: '',
            raw: data
        };
        var item = null;
        var pricing;
        var firstTerm;

        if (data && data.result && $.isArray(data.result) && data.result.length) {
            item = data.result[0];
        } else if (data && data.result && typeof data.result === 'object') {
            item = data.result;
        }

        if (!item) {
            result.status = 'No response item';
            return result;
        }

        if (typeof item === 'string') {
            result.status = item;
            return result;
        }

        result.returnedDomain = item.domainName || item.idnDomainName || request.domain;

        if (item.error) {
            result.status = item.error;
            return result;
        }

        if (item.isValidDomain === false) {
            result.status = item.domainErrorMessage || 'Invalid';
            return result;
        }

        pricing = item.pricing;
        if (item.isAvailable && typeof pricing !== 'string') {
            result.status = 'Available';
            if (pricing) {
                firstTerm = Object.keys(pricing)[0];
                if (firstTerm && pricing[firstTerm] && pricing[firstTerm].register) {
                    result.price = pricing[firstTerm].register;
                }
            }
            return result;
        }

        if (typeof pricing === 'string' && pricing) {
            result.status = pricing === 'ContactUs' ? 'Contact Support' : pricing;
            return result;
        }

        result.status = 'Unavailable';
        return result;
    }

    function statusClass(status) {
        if (status === 'Available') {
            return 'text-success';
        }
        if (status === 'Unavailable') {
            return 'text-danger';
        }
        if (status === 'Checking...') {
            return 'text-muted';
        }
        return 'text-warning';
    }

    function addPlaceholderRow(request, index) {
        $('<tr/>', { id: 'dmV9DirectRow' + index })
            .append($('<td/>').append($('<span/>').addClass('dm-v9-direct-domain-name').text(request.domain)))
            .append($('<td/>').append($('<strong/>').addClass('text-muted').text('Checking...')))
            .append($('<td/>').addClass('dm-v9-direct-price').text(''))
            .append($('<td/>').addClass('dm-v9-direct-action-cell'))
            .appendTo('#dmV9DirectResults');
    }

    function buildActionCell(result, domain) {
        var $cell = $('<td/>').addClass('dm-v9-direct-action-cell');
        if (result.status === 'Available') {
            $('<button/>', {
                type: 'button',
                class: 'btn btn-primary btn-sm dm-v9-direct-add-btn',
                'data-domain': domain
            }).append($('<span/>').addClass('dm-add-label').text('Add'))
              .append($('<span/>').addClass('dm-add-loading').hide().text('Adding...'))
              .append($('<span/>').addClass('dm-add-added').hide().text('Checkout'))
              .appendTo($cell);
        }
        return $cell;
    }

    function updateRow(index, result) {
        var domain = result.returnedDomain || result.requestedDomain;
        var $row = $('#dmV9DirectRow' + index);
        var $domainName = $('<span/>').addClass('dm-v9-direct-domain-name').text(domain);
        if (result.status === 'Unavailable') {
            $domainName.addClass('dm-v9-direct-domain-unavailable');
        }
        $row.empty()
            .append($('<td/>').append($domainName))
            .append($('<td/>').append($('<strong/>').addClass(statusClass(result.status)).text(result.status)))
            .append($('<td/>').addClass('dm-v9-direct-price').text(result.price || ''))
            .append(buildActionCell(result, domain));
    }

    function checkDomain(request) {
        return $.ajax({
            url: getEndpoint(),
            method: 'POST',
            dataType: 'json',
            data: {
                token: getToken(),
                type: 'domain',
                source: 'cartAddDomain',
                domain: request.domain,
                sld: request.sld,
                tld: request.tld
            }
        }).then(function(data) {
            return parseAvailabilityResponse(request, data);
        }, function(xhr) {
            return {
                requestedDomain: request.domain,
                returnedDomain: request.domain,
                status: 'Request failed: HTTP ' + xhr.status,
                price: '',
                raw: xhr.responseText || ''
            };
        });
    }

    function checkDomains(domains, runId) {
        var nextIndex = 0;
        var running = 0;
        var completed = 0;

        function doneOne() {
            if (runId !== activeRun) {
                return;
            }
            completed++;
            $('#dmV9DirectStatus').removeClass('alert-warning alert-success').addClass('alert-info')
                .text('Checked ' + completed + ' of ' + domains.length + ' v9 + context suggestion(s)...')
                .show();
            if (completed >= domains.length) {
                $('#dmV9DirectStatus').removeClass('alert-info alert-warning').addClass('alert-success')
                    .text('Finished checking v9 + context suggestions.')
                    .show();
            }
        }

        function runNext() {
            var currentIndex;
            var request;
            if (runId !== activeRun) {
                return;
            }
            while (running < maxConcurrentChecks && nextIndex < domains.length) {
                currentIndex = nextIndex++;
                request = domains[currentIndex];
                running++;
                checkDomain(request).then(function(rowIndex) {
                    return function(result) {
                        if (runId === activeRun) {
                            updateRow(rowIndex, result);
                        }
                    };
                }(currentIndex)).always(function() {
                    running--;
                    if (runId === activeRun) {
                        doneOne();
                        runNext();
                    }
                });
            }
        }

        runNext();
    }

    function addToCart($button) {
        var domain = $button.attr('data-domain');
        if (!domain) {
            return;
        }
        if ($button.hasClass('checkout')) {
            window.location = (window.whmcsBaseUrl || '') + '/cart.php?a=confdomains';
            return;
        }
        $button.prop('disabled', true).addClass('disabled')
            .find('.dm-add-label').hide().end()
            .find('.dm-add-loading').show().end()
            .find('.dm-add-added').hide();

        $.ajax({
            url: (window.whmcsBaseUrl || '') + '/cart.php',
            method: 'POST',
            dataType: 'json',
            data: {
                a: 'addToCart',
                domain: domain,
                token: getToken(),
                sideorder: 1
            }
        }).done(function(data) {
            $button.find('.dm-add-loading').hide();
            if (data && data.result === 'added') {
                $button.removeClass('disabled').prop('disabled', false).addClass('checkout')
                    .find('.dm-add-added').show();
                if (data.cartCount) {
                    $('#cartItemCount').html(data.cartCount);
                }
            } else {
                $button.removeClass('disabled btn-primary').prop('disabled', false).addClass('btn-danger')
                    .find('.dm-add-label').show().text('Try Again');
            }
        }).fail(function() {
            $button.find('.dm-add-loading').hide();
            $button.removeClass('disabled btn-primary').prop('disabled', false).addClass('btn-danger')
                .find('.dm-add-label').show().text('Try Again');
        });
    }

    function runV9DirectSearch(input) {
        var runId = ++activeRun;
        var tlds = getSelectedTlds();
        var payload = {
            token: getToken(),
            type: 'suggestions',
            source: 'cartAddDomain',
            maxLength: 30,
            message: input
        };
        if ($('#dmV9DirectSafeSearch').is(':checked')) {
            payload.filter = 'on';
        }
        $.each(tlds, function(index, tld) {
            payload['tlds[' + index + ']'] = tld;
        });

        $('#dmV9DirectResults').empty();
        $('#dmV9DirectEmpty').hide();
        $('#dmV9DirectResultsCard').show();
        $('#dmV9DirectResultsCount').text('');
        $('#dmV9DirectStatus').removeClass('alert-warning alert-success').addClass('alert-info')
            .text('Calling native v9 namespinner endpoint and preparing context lookups...')
            .show();

        $.ajax({
            url: getEndpoint(),
            method: 'POST',
            dataType: 'json',
            data: payload
        }).done(function(data) {
            var domains;
            if (runId !== activeRun) {
                return;
            }
            domains = extractSuggestionDomains(data);
            domains = domains.concat(generateContextLookupDomains(input, domains)).slice(0, 40);
            $('#dmV9DirectResultsCount').text(domains.length + ' v9 + context suggestion(s)');
            if (!domains.length) {
                $('#dmV9DirectStatus').removeClass('alert-info alert-success').addClass('alert-warning')
                    .text('The v9 endpoint and context lookup did not return suggestion names for that input.')
                    .show();
                $('#dmV9DirectEmpty').show();
                return;
            }
            $.each(domains, function(index, request) {
                addPlaceholderRow(request, index);
            });
            checkDomains(domains, runId);
        }).fail(function(xhr) {
            if (runId !== activeRun) {
                return;
            }
            $('#dmV9DirectStatus').removeClass('alert-info alert-success').addClass('alert-warning')
                .text('v9 namespinner request failed. HTTP ' + xhr.status)
                .show();
        });
    }

    $(function() {
        $('#dmV9DirectForm').on('submit', function(e) {
            e.preventDefault();
            var input = $.trim($('#dmV9DirectInput').val());
            if (!input) {
                activeRun++;
                $('#dmV9DirectStatus').removeClass('alert-info alert-success').addClass('alert-warning')
                    .text('Enter a domain idea first.')
                    .show();
                $('#dmV9DirectResultsCard').hide();
                return;
            }
            runV9DirectSearch(input);
        });

        $('#dmV9DirectResults').on('click', '.dm-v9-direct-add-btn', function() {
            addToCart($(this));
        });

        if ($.trim($('#dmV9DirectInput').val())) {
            $('#dmV9DirectForm').trigger('submit');
        }
    });
})(jQuery);
</script>
{/literal}
