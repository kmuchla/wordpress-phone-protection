# SECURITY

## Model zagrożeń
- Scraping numerów przez boty (z JS i bez JS).
- Próby obejścia weryfikacji Turnstile (token replay / podmiana).
- Nadmierne odpytywanie endpointu AJAX.

## Mitigacje
- Weryfikacja tokenu po stronie serwera (`/siteverify`).
- Numer składany i przechowywany po stronie serwera (transient z TTL) – brak numeru w HTML przed weryfikacją.
- `wp_verify_nonce` + sanitizacja danych wejściowych.
- Jednorazowy uchwyt – usuwany po użyciu.
- Zalecany rate limit w Cloudflare (WAF) na `admin-ajax.php?action=km_tel_reveal`.
