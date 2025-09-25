/**
 * Plugin Name: Phone Protection via Cloudflare Turnstile
 * Description: Shortcode [tel_turnstile] – ujawnia numer telefonu dopiero po pozytywnej weryfikacji Cloudflare Turnstile.
 * Version:     1.1.0
 * Author:      KM
 * Requires PHP: 7.4
 * Requires at least: 5.0
 */

/**
 * USTAW KLUCZE:
 *  - Najlepiej w wp-config.php:
 *      define('KM_TURNSTILE_SITEKEY', 'twój_site_key');
 *      define('KM_TURNSTILE_SECRET',  'twój_secret_key');
 *  - Poniżej fallback z ENV (na potrzeby dev/dockera). NIE commituj prawdziwych kluczy do repo!
 */
if (!defined('KM_TURNSTILE_SITEKEY')) {
    $envSite = getenv('KM_TURNSTILE_SITEKEY') ?: 'twój_site_key';
    define('KM_TURNSTILE_SITEKEY', $envSite);
}
if (!defined('KM_TURNSTILE_SECRET')) {
    $envSec = getenv('KM_TURNSTILE_SECRET') ?: 'twój_secret_key';
    define('KM_TURNSTILE_SECRET', $envSec);
}

/**
 * Shortcode: [tel_turnstile country="+48" parts="601|234|567" label="Pokaż numer" ttl="1800"]
 */
add_shortcode('tel_turnstile', function ($atts) {
    if (is_admin()) return '';

    $a = shortcode_atts([
        'country' => '+48',
        'parts'   => '',              // "601|234|567"
        'label'   => 'Pokaż numer',
        'ttl'     => 1800,            // sekundy przechowywania uchwytu po stronie serwera
    ], $atts, 'tel_turnstile');

    // sanity
    $country = preg_replace('/\s+/', '', (string)$a['country']);
    $digits  = preg_replace('/\D+/', '', (string)str_replace('|', '', $a['parts'] ?? ''));
    if ($digits === '') return ''; // brak numeru – nic nie renderujemy

    // Numer składamy TYLKO po stronie serwera
    $plain  = $country . $digits;                            // np. +48601234567
    $pretty = trim($country . ' ' . implode(' ', preg_split('//', $digits, -1, PREG_SPLIT_NO_EMPTY)));

    // Jednorazowy uchwyt (transient)
    $handle = 'km_tel_' . wp_generate_uuid4();
    set_transient($handle, [
        'tel_href' => 'tel:' . $plain,
        'tel_text' => $pretty,
        'created'  => time(),
        'host'     => wp_parse_url(home_url(), PHP_URL_HOST),
    ], (int)$a['ttl']);

    // Jednorazowe wstrzyknięcie API Turnstile + JS (bez plików zewnętrznych)
    add_action('wp_footer', function () {
        if (is_admin()) return;
        static $once = false; if ($once) return; $once = true;

        // API Cloudflare Turnstile
        echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';

        // Dane runtime
        $data = [
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('km_tel_reveal'),
            'site'  => KM_TURNSTILE_SITEKEY,
        ];
        ?>
<script>
(function(){
  var KM = <?php echo wp_json_encode($data); ?>;

  function reveal(handle, token, node){
    if(!handle || !token) return;
    var fd = new FormData();
    fd.append('action','km_tel_reveal');
    fd.append('nonce', KM.nonce);
    fd.append('handle', handle);
    fd.append('cf_token', token);

    fetch(KM.ajax, {method:'POST', body:fd, credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(res){
        if(!res || !res.success){ throw new Error((res && res.message) || 'Błąd'); }
        var wrap = node.closest('.km-tel-wrap'); if(!wrap) return;
        var a = document.createElement('a');
        a.className = 'km-tel-link';
        a.rel = 'nofollow';
        a.href = res.data.tel_href;
        a.textContent = res.data.tel_text;
        wrap.innerHTML = '';
        wrap.appendChild(a);
      })
      .catch(function(){
        alert('Nie udało się zweryfikować. Spróbuj ponownie.');
        try { node.remove(); } catch(e){}
      });
  }

  // Delegacja: po kliknięciu w przycisk renderujemy widget Turnstile
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.km-tel-btn'); if(!btn) return;
    var wrap = btn.closest('.km-tel-wrap');    if(!wrap) return;
    var handle = wrap.getAttribute('data-handle'); if(!handle) return;
    btn.disabled = true;

    var wid = document.createElement('div');
    wid.className = 'cf-turnstile';
    wid.setAttribute('data-sitekey', KM.site);

    var cbName = 'kmTelCb_' + Math.random().toString(36).slice(2);
    window[cbName] = function(token){ reveal(handle, token, wid); };
    wid.setAttribute('data-callback', cbName);

    btn.style.display = 'none';
    wrap.appendChild(wid);
    if (window.turnstile && turnstile.render) { turnstile.render(wid); }
  }, {passive:true});
})();
</script>
        <?php
    }, 20);

    // HTML shortcodu – w DOM nie ma jawnego numeru
    $html  = '<span class="km-tel-wrap" data-handle="' . esc_attr($handle) . '">';
    $html .= '  <button type="button" class="km-tel-btn">' . esc_html($a['label']) . '</button>';
    $html .= '  <noscript><span>Włącz JavaScript, aby zobaczyć numer.</span></noscript>';
    $html .= '</span>';
    return $html;
});

/**
 * AJAX: weryfikacja tokenu Cloudflare Turnstile + zwrot numeru
 */
add_action('wp_ajax_km_tel_reveal',        'km_tel_reveal_cb');
add_action('wp_ajax_nopriv_km_tel_reveal', 'km_tel_reveal_cb');

function km_tel_reveal_cb() {
    // 0) nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'km_tel_reveal')) {
        wp_send_json(['success'=>false, 'message'=>'Błędny nonce.'], 400);
    }

    // 1) wejście
    $handle   = isset($_POST['handle'])   ? sanitize_text_field($_POST['handle'])   : '';
    $cf_token = isset($_POST['cf_token']) ? sanitize_text_field($_POST['cf_token']) : '';
    if (!$handle || !$cf_token) {
        wp_send_json(['success'=>false, 'message'=>'Brak danych.'], 400);
    }

    // 2) weryfikacja Turnstile
    if (empty(KM_TURNSTILE_SECRET)) {
        wp_send_json(['success'=>false, 'message'=>'Brak klucza Turnstile po stronie serwera.'], 500);
    }

    $resp = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'timeout' => 10,
        'body'    => [
            'secret'   => KM_TURNSTILE_SECRET,
            'response' => $cf_token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
    ]);

    if (is_wp_error($resp)) {
        wp_send_json(['success'=>false, 'message'=>'Błąd połączenia z Cloudflare.'], 502);
    }

    $code = wp_remote_retrieve_response_code($resp);
    $body = json_decode(wp_remote_retrieve_body($resp), true);

    if ($code !== 200 || empty($body['success'])) {
        wp_send_json(['success'=>false, 'message'=>'Weryfikacja nieudana.'], 403);
    }

    // (opcjonalnie) dopasuj hosta zwróconego przez Turnstile do hosta strony
    // if (!empty($body['hostname'])) {
    //     $expected = wp_parse_url(home_url(), PHP_URL_HOST);
    //     if (strcasecmp($body['hostname'], $expected) !== 0) {
    //         wp_send_json(['success'=>false, 'message'=>'Nieprawidłowy host.'], 403);
    //     }
    // }

    // 3) pobierz i unieważnij transient (jednorazowy uchwyt)
    $data = get_transient($handle);
    delete_transient($handle);

    if (!$data || empty($data['tel_href']) || empty($data['tel_text'])) {
        wp_send_json(['success'=>false, 'message'=>'Nie znaleziono danych.'], 404);
    }

    // 4) zwróć numer
    wp_send_json([
        'success' => true,
        'data'    => [
            'tel_href' => esc_url_raw($data['tel_href']),
            'tel_text' => sanitize_text_field($data['tel_text']),
        ],
    ]);
}