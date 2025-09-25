# Ochrona numeru telefonu w WordPress (Cloudflare Turnstile)

Projekt pokazuje, jak ukryć numer telefonu na stronie WordPress i ujawniać go **dopiero po pozytywnej weryfikacji** użytkownika przez Cloudflare Turnstile.
Dzięki temu numer nie pojawia się w źródle HTML i jest znacznie trudniejszy do zeskrobania przez boty.

## 🎯 Cele projektu

- 🔒 Ochrona danych kontaktowych przed scrapowaniem.
- 💡 Integracja WordPress + Cloudflare Turnstile.
- 🧩 Przykład mini-wtyczki Wordpress.

## ⚡️ Szybki start

1. **Skopiuj plik wtyczki**
   `src/tel-turnstile-snippet.php` → `wp-content/plugins/phone-protection/phone-protection.php`
2. **Dodaj klucze w `wp-config.php`**

   ```php
   define('KM_TURNSTILE_SITEKEY', 'TWÓJ_SITE_KEY');
   define('KM_TURNSTILE_SECRET',  'TWÓJ_SECRET_KEY');
   ```

3. Włącz wtyczkę i wstaw shortcode na dowolnej stronie/postcie:
<pre>

```text
[tel_turnstile country="+48" parts="601|234|567" label="Pokaż numer" ttl="1800"]
```

</pre>
## ✨ Funkcjonalności

- Weryfikacja użytkownika za pomocą **Cloudflare Turnstile**
- Numer generowany i przechowywany **tylko po stronie serwera**
- Jednorazowy uchwyt (Transient) z konfigurowalnym **czasem życia (TTL)**
- Bezpieczny endpoint AJAX z **nonce** i sanitizacją danych

## 🔧 Parametry shortcode

- **`country`** – _(opcjonalny)_ – domyślnie **`+48`**
  Prefiks kraju.

- **`parts`** – _(wymagany)_ – brak wartości domyślnej
  Numery telefonu rozdzielone znakiem `|`, np. **`601|234|567`**.

- **`label`** – _(opcjonalny)_ – domyślnie **`Pokaż numer`**
  Tekst przycisku wyświetlanego użytkownikowi.

- **`ttl`** – _(opcjonalny)_ – domyślnie **`1800`** (sekundy)
  Czas życia jednorazowego uchwytu (Transient) w bazie WordPress.

## 📝 Przykład użycia

Użyj shortcode’u w treści wpisu lub strony:

[tel_turnstile country="+48" parts="601|234|567" label="Zadzwoń" ttl="900"]

## 🏗 Jak to działa

1. Użytkownik klika przycisk **„Pokaż numer”**.
2. Renderuje się widget **Cloudflare Turnstile**.
3. Po weryfikacji token trafia do serwera.
4. Serwer weryfikuje token w Cloudflare i zwraca numer telefonu.
5. Front-end podmienia przycisk na klikalny link **`tel:+48…`**.

## 🔐 Bezpieczeństwo

- Token Turnstile weryfikowany po stronie serwera (`/siteverify`)
- Numer nigdy nie pojawia się w źródle HTML
- `wp_verify_nonce` + sanitizacja danych wejściowych
- Zalecane dodatkowe reguły **WAF/Rate-Limit** w Cloudflare
  na endpoint `admin-ajax.php?action=km_tel_reveal`

Więcej informacji znajdziesz w pliku **`SECURITY.md`**.

## 📂 Struktura repozytorium

```text
├─ src/
│ └─ tel-turnstile-snippet.php
├─ docs/
├─ SECURITY.md
├─ LICENSE
└─ README.md
```

## 📝 Licencja

Projekt udostępniony na licencji MIT.
