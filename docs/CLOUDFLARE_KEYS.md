# Generowanie kluczy Cloudflare Turnstile

Instrukcja krok po kroku, jak utworzyć klucze **Site Key** i **Secret Key**, potrzebne do działania wtyczki.

---

## 1. Zaloguj się do Cloudflare

1. Wejdź na [https://dash.cloudflare.com](https://dash.cloudflare.com) i zaloguj się na swoje konto.

## 2. Utwórz nową aplikację Turnstile

1. W menu bocznym przejdź do **Turnstile** → **Add widget**.
2. Podaj nazwę (np. _WordPress Phone Protection_).
3. Wybierz **Widget Type** → _Managed_.
4. W polu **Domain** wpisz swoją domenę (np. `example.com`) i zapisz.

## 3. Skopiuj klucze

Po utworzeniu aplikacji zobaczysz:

- **Site Key** – publiczny klucz, używany w froncie (shortcode).
- **Secret Key** – prywatny klucz, używany po stronie serwera.

> **Uwaga:** _Secret Key_ traktuj jak hasło.

## 4. Konfiguracja w WordPress

W pliku `wp-config.php` dodaj:

```php
define( 'KM_TURNSTILE_SITEKEY', '...' );
define( 'KM_TURNSTILE_SECRET',  '...' );
```
