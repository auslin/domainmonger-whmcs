# Test Checklist - Status Check Zone Page Link

1. Open a managed master zone.
2. Confirm `Status` still appears on the main menu.
3. Open the `Advanced` dropdown and confirm `Check on dns.computer` / `Check Zone File` is no longer listed there.
4. Open the `Status` page.
5. Confirm the Status page shows both action buttons:
   - `Check Zone File`
   - `Request New Update`
6. Click `Check Zone File` and confirm it opens the zone check page in a new tab.
7. Confirm `Request New Update` still redirects back to Status after a successful update request.
