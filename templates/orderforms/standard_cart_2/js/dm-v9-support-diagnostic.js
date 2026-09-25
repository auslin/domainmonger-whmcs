/* DomainMonger Patch 224: support-only raw v9 namespinner endpoint output. */
(function($) {
    'use strict';

    function endpointUrl() {
        if (window.WHMCS && WHMCS.utils && typeof WHMCS.utils.getRouteUrl === 'function') {
            return WHMCS.utils.getRouteUrl('/domain/check');
        }
        return (window.whmcsBaseUrl || '') + '/index.php?rp=/domain/check';
    }

    function token() {
        if (typeof window.csrfToken !== 'undefined' && window.csrfToken) {
            return window.csrfToken;
        }
        return $('input[name="token"]').val() || '';
    }

    function cleanName(value) {
        return String(value || '').toLowerCase().replace(/^\s+|\s+$/g, '');
    }

    function fieldList(item) {
        if (!item || typeof item !== 'object') {
            return 'string result';
        }
        return Object.keys(item).sort().join(', ');
    }

    function nameFromItem(item) {
        if (typeof item === 'string') {
            return item;
        }
        if (!item) {
            return '';
        }
        if (item.domainName) {
            return item.domainName;
        }
        if (item.idnDomainName) {
            return item.idnDomainName;
        }
        if (item.domain) {
            return item.domain;
        }
        if (item.sld && item.tld) {
            return item.sld + '.' + String(item.tld).replace(/^\./, '');
        }
        return '';
    }

    function currentTerm() {
        return cleanName($('#inputDomain').val());
    }

    function renderStatus(message, className) {
        $('#dmV9SupportRawStatus')
            .removeClass('is-error is-good')
            .addClass(className || '')
            .text(message);
    }

    function renderRaw(data) {
        var rows = [];
        var results = data && $.isArray(data.result) ? data.result : [];
        $('#dmV9SupportRawJson').text(JSON.stringify(data, null, 2));
        $('#dmV9SupportToggleJson').show().text('Show Raw JSON');
        $('#dmV9SupportRawJson').hide();

        if (!results.length) {
            $('#dmV9SupportRawRows').empty();
            $('#dmV9SupportRawTableWrap').hide();
            renderStatus('The v9 endpoint returned no suggestion rows.', 'is-error');
            return;
        }

        $.each(results, function(index, item) {
            var name = nameFromItem(item) || '(blank name field)';
            rows.push(
                '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td><code>' + $('<div>').text(name).html() + '</code></td>' +
                    '<td>' + $('<div>').text(fieldList(item)).html() + '</td>' +
                '</tr>'
            );
        });

        $('#dmV9SupportRawRows').html(rows.join(''));
        $('#dmV9SupportRawTableWrap').show();
        renderStatus('The v9 endpoint returned ' + results.length + ' suggestion row' + (results.length === 1 ? '' : 's') + '.', 'is-good');
    }

    function runRawProbe() {
        var term = currentTerm();
        if (!term) {
            $('#dmV9SupportRawRows').empty();
            $('#dmV9SupportRawTableWrap').hide();
            $('#dmV9SupportToggleJson, #dmV9SupportRawJson').hide();
            renderStatus('Enter a search phrase to view the raw v9 endpoint output.');
            return;
        }

        renderStatus('Checking raw v9 endpoint output...');
        $('#dmV9SupportRawTableWrap').hide();
        $('#dmV9SupportToggleJson, #dmV9SupportRawJson').hide();

        $.ajax({
            url: endpointUrl(),
            method: 'POST',
            dataType: 'json',
            data: {
                source: 'cartAddDomain',
                type: 'suggestions',
                message: term,
                maxLength: 30,
                'tlds[]': ['.com', '.net', '.org', '.us', '.biz', '.ca'],
                token: token()
            }
        }).done(function(data) {
            renderRaw(data);
        }).fail(function(xhr) {
            $('#dmV9SupportRawRows').empty();
            $('#dmV9SupportRawTableWrap').hide();
            $('#dmV9SupportRawJson').text(xhr.responseText || 'No response text returned.');
            $('#dmV9SupportToggleJson').show().text('Show Raw Response');
            renderStatus('The raw v9 endpoint request failed. Status: ' + xhr.status, 'is-error');
        });
    }

    $(document).ready(function() {
        if (!$('#order-standard_cart').hasClass('dm-v9-support-page')) {
            return;
        }

        $('#frmDomainChecker').on('submit.dmV9Support', function() {
            setTimeout(runRawProbe, 50);
        });

        $('#btnCheckAvailability').on('click.dmV9Support', function() {
            setTimeout(runRawProbe, 50);
        });

        $('#dmV9SupportRawRefresh').on('click.dmV9Support', function(e) {
            e.preventDefault();
            runRawProbe();
        });

        $('#dmV9SupportToggleJson').on('click.dmV9Support', function(e) {
            e.preventDefault();
            var raw = $('#dmV9SupportRawJson');
            if (raw.is(':visible')) {
                raw.hide();
                $(this).text($(this).text().replace('Hide', 'Show'));
            } else {
                raw.show();
                $(this).text($(this).text().replace('Show', 'Hide'));
            }
        });

        if (currentTerm()) {
            setTimeout(runRawProbe, 300);
        }
    });
})(jQuery);
