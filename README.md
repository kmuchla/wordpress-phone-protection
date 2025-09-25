# Ochrona numeru telefonu w WordPress (Cloudflare Turnstile)

Mały projekt z zakresu **cyberbezpieczeństwa aplikacji webowych**.
Pokazuje, jak ukryć numer telefonu na stronie WordPress i ujawnić go **dopiero po pozytywnej weryfikacji** użytkownika przez Cloudflare Turnstile – dzięki temu numer nie pojawia się w źródle HTML i nie może być łatwo zebrany przez boty.

## 🎯 Cele projektu

- 🔒 Zabezpieczenie danych kontaktowych przed automatycznym scrapowaniem.
- 💡 Demonstracja integracji WordPress + Cloudflare Turnstile.
- 🧩 Przykład „mini-wtyczki” dla WordPressa jako element portfolio w cyberbezpieczeństwie.

## ✨ Funkcjonalności

- Weryfikacja użytkownika za pomocą Cloudflare Turnstile.
- Brak numeru w źródle strony – numer generowany i zwracany **tylko** po stronie serwera.
- Jednorazowy uchwyt (transient) z czasem życia (TTL).
- Bezpieczny endpoint AJAX (`wp-admin/admin-ajax.php`) z:
  - `wp_verify_nonce`
  - sanitizacją danych wejściowych
- Prosty shortcode:
  ```text
  [tel_turnstile country="+48" parts="601|234|567" label="Pokaż numer" ttl="1800"]
  ```
