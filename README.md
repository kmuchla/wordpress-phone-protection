# Ochrona numeru telefonu w WordPress (Cloudflare Turnstile)

Projekt pokazuje, jak ukryć numer telefonu na stronie WordPress i ujawniać go **dopiero po pozytywnej weryfikacji** użytkownika przez Cloudflare Turnstile.
Dzięki temu numer nie pojawia się w źródle HTML i jest znacznie trudniejszy do zeskrobania przez boty.

## 🎯 Cele projektu

- 🔒 Ochrona danych kontaktowych przed scrapowaniem.
- 💡 Integracja WordPress + Cloudflare Turnstile.
- 🧩 Przykład mini-wtyczki jako element portfolio w cyberbezpieczeństwie.

## ⚡️ Szybki start

1. **Skopiuj plik wtyczki**
   `src/tel-turnstile-snippet.php` → `wp-content/plugins/phone-protection/phone-protection.php`
2. **Dodaj klucze w `wp-config.php`** (nie commituj do repo):

   ```php
   define('KM_TURNSTILE_SITEKEY', 'TWÓJ_SITE_KEY');
   define('KM_TURNSTILE_SECRET',  'TWÓJ_SECRET_KEY');

   3.	Włącz wtyczkę i wstaw shortcode na dowolnej stronie/postcie:
   ```

[tel_turnstile country="+48" parts="601|234|567" label="Pokaż numer" ttl="1800"]

✨ Funkcjonalności
• Weryfikacja użytkownika za pomocą Cloudflare Turnstile.
• Numer generowany i przechowywany tylko po stronie serwera.
• Jednorazowy uchwyt (transient) z czasem życia (TTL).
• Bezpieczny endpoint AJAX z nonce i sanitizacją danych.

🔧 Parametry shortcode

Parametr Wymagany Domyślna Opis
country nie +48 Prefiks kraju.
parts tak — Numery telefonu rozdzielone | (np. 601|234|567).
label nie Pokaż numer Tekst przycisku.
ttl nie 1800 Czas (sekundy) przechowywania uchwytu.

Przykład:

[tel_turnstile country="+48" parts="601|234|567" label="Zadzwoń" ttl="900"]

🏗 Jak to działa 1. Użytkownik klika przycisk „Pokaż numer”. 2. Renderuje się widget Turnstile. 3. Po weryfikacji token trafia do serwera. 4. Serwer weryfikuje token u Cloudflare i zwraca numer telefonu. 5. Front-end podmienia przycisk na klikalny link tel:+48….

🔐 Bezpieczeństwo
• Token Turnstile weryfikowany po stronie serwera (/siteverify).
• Numer nigdy nie pojawia się w źródle HTML.
• wp_verify_nonce + sanitizacja danych wejściowych.
• Zalecane dodatkowe reguły WAF/Rate-Limit w Cloudflare na admin-ajax.php?action=km_tel_reveal.

Szczegóły w SECURITY.md.

📂 Struktura repozytorium

├─ src/
│ └─ tel-turnstile-snippet.php # główny plik wtyczki
├─ docs/ # zrzuty ekranu, diagramy (opcjonalnie)
├─ SECURITY.md
├─ LICENSE
└─ README.md

📝 Licencja

Projekt udostępniony na licencji MIT.
