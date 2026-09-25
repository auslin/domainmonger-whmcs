(function () {
  "use strict";

  var text = window.InvoiceFixText || {};

  function message(key) {
    return Object.prototype.hasOwnProperty.call(text, key) && text[key] !== "" ? String(text[key]) : key;
  }

  function ready(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  function csrfToken(button) {
    var buttonToken = button.getAttribute("data-token");
    if (buttonToken) return buttonToken;

    var input = document.querySelector('input[name="token"]');
    if (input && input.value) return input.value;

    if (window.WHMCS && window.WHMCS.csrfToken) return window.WHMCS.csrfToken;
    if (window.csrfToken) return window.csrfToken;

    return "";
  }

  function parseError(xhr) {
    if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
      return xhr.responseJSON.error;
    }

    if (xhr && xhr.responseText) {
      try {
        var parsed = JSON.parse(xhr.responseText);
        if (parsed && parsed.error) return parsed.error;
      } catch (ignore) {}

      var responseText = String(xhr.responseText).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
      if (responseText) return responseText;
    }

    return message("js_request_failed");
  }

  function createDraft(invoiceId, token) {
    var url = "addonmodules.php?module=invoicefix&action=create_editable_draft";
    var data = {
      token: token,
      invoiceid: invoiceId
    };

    if (window.jQuery) {
      return new Promise(function (resolve, reject) {
        window.jQuery.ajax({
          url: url,
          method: "POST",
          data: data,
          dataType: "json",
          success: resolve,
          error: function (xhr) {
            reject(new Error(parseError(xhr)));
          }
        });
      });
    }

    if (window.fetch) {
      return window.fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: "token=" + encodeURIComponent(token) + "&invoiceid=" + encodeURIComponent(invoiceId)
      }).then(function (response) {
        return response.text().then(function (responseText) {
          var parsed;
          try {
            parsed = JSON.parse(responseText);
          } catch (error) {
            throw new Error(
              responseText.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim() ||
                message("js_invalid_response")
            );
          }
          if (!response.ok || !parsed.ok) {
            throw new Error(parsed.error || message("js_request_failed"));
          }
          return parsed;
        });
      });
    }

    return Promise.reject(
      new Error(message("js_ajax_unavailable"))
    );
  }

  ready(function () {
    var button = document.getElementById("invoicefixCreateDraftBtn");
    if (!button) return;

    button.addEventListener("click", function () {
      var invoiceId = button.getAttribute("data-invoiceid");
      var token = csrfToken(button);
      var confirmation = button.getAttribute("data-confirm") || message("js_default_confirmation");

      if (!invoiceId) {
        window.alert(message("js_missing_invoice_id"));
        return;
      }

      if (!token) {
        window.alert(
          message("js_missing_token")
        );
        return;
      }

      if (!window.confirm(confirmation)) return;

      var originalHtml = button.innerHTML;
      button.disabled = true;
      button.innerHTML =
        '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> ' +
        message("button_creating_draft");

      createDraft(invoiceId, token)
        .then(function (response) {
          if (!response || !response.ok) {
            throw new Error(
              response && response.error ? response.error : message("js_unknown_error")
            );
          }

          if (response.warning) {
            window.alert(response.warning);
          }

          window.location.href = response.redirect || ("invoices.php?action=edit&id=" + response.draft_invoice_id);
        })
        .catch(function (error) {
          window.alert(message("js_error_prefix") + " " + error.message);
          button.disabled = false;
          button.innerHTML = originalHtml;
        });
    });
  });
})();
