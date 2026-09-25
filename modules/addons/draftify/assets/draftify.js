(function () {
  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  function getToken() {
    var el = document.querySelector('input[name="token"]');
    if (el && el.value) return el.value;
    if (window.WHMCS && WHMCS.csrfToken) return WHMCS.csrfToken;
    return "";
  }

  function postMarkDraft(invoiceId, token) {
    // Prefer jQuery if available (WHMCS admin loads it), fallback to fetch.
    var url = "addonmodules.php?module=draftify&action=markdraft";
    var payload = "token=" + encodeURIComponent(token) + "&invoiceid=" + encodeURIComponent(String(invoiceId));

    if (window.jQuery) {
      return new Promise(function (resolve, reject) {
        jQuery.ajax({
          url: url,
          method: "POST",
          data: payload,
          dataType: "json",
          headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
          xhrFields: { withCredentials: true },
          success: function (res) { resolve(res); },
          error: function (xhr) {
            var msg = "Request failed";
            try {
              if (xhr && xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
              else if (xhr && xhr.responseText) msg = xhr.responseText;
            } catch (e) {}
            reject(new Error(msg));
          }
        });
      });
    }

    if (window.fetch) {
      return fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: payload,
        credentials: "same-origin"
      }).then(function (r) {
        return r.json().catch(function () {
          return { ok: false, error: "Invalid JSON response" };
        });
      });
    }

    return Promise.reject(new Error("No AJAX method available (missing jQuery/fetch)."));
  }

  ready(function () {
    var btn = document.getElementById("draftifyMarkDraftBtn");
    if (!btn) return;

    btn.addEventListener("click", function () {
      var invoiceId = btn.getAttribute("data-invoiceid");
      var confirmText = btn.getAttribute("data-confirm") || "Mark this invoice as Draft?";
      var token = getToken();

      if (!token) {
        alert("Draftify: CSRF token not found. Please refresh and try again.");
        return;
      }

      if (!invoiceId) {
        alert("Draftify: invoice id not found on button.");
        return;
      }

      if (!window.confirm(confirmText)) return;

      var oldHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = "Working...";

      postMarkDraft(invoiceId, token)
        .then(function (res) {
          if (!res || !res.ok) {
            throw new Error((res && res.error) ? res.error : "Unknown error");
          }
          alert("Invoice #" + res.invoiceid + " was set to Draft (previous: " + res.prev_status + "). Reloading...");
          window.location.reload();
        })
        .catch(function (err) {
          alert("Draftify error: " + err.message);
          btn.disabled = false;
          btn.innerHTML = oldHtml;
        });
    });
  });
})();