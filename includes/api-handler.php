<?php
/**
 * GESTIÓN DE API Y CACHÉ - REAL REVIEWS SUITE
 */

if (!defined('ABSPATH')) exit;

/* ==============================================================
   AYUDA PARA API URL
============================================================== */
function rr_get_api_url() {
    $company_id = get_option('real_reviews_company_id', 'Com0000demo');
    return 'https://api.realreviewsbyrp.com/6wlgncta5/' . $company_id;
}

/* ==============================================================
   BUSINESS INFO API
============================================================== */
function rr_get_business_api_url() {
    $company_id = get_option('real_reviews_company_id', 'Com0000demo');
    return 'https://api.realreviewsbyrp.com/2s5drk7x2/' . $company_id;
}

function rr_fetch_business_info() {
    $api_url       = rr_get_business_api_url();
    $transient_key = 'rr_cache_biz_' . md5($api_url);
    $cached        = get_transient($transient_key);
    if ($cached !== false) return $cached;

    $response = wp_remote_get($api_url, ['timeout' => 15]);
    if (is_wp_error($response)) return $response;

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) return new WP_Error('empty_body', 'Empty business info response.');

    $first   = json_decode($body, true);
    $decoded = null;

    if (!is_array($first) || !isset($first['key'])) {
        $maybe = base64_decode($body, true);
        if ($maybe !== false) $decoded = json_decode($maybe, true);
    } else {
        $decoded_key = base64_decode($first['key'], true);
        if ($decoded_key !== false) $decoded = json_decode($decoded_key, true);
        if (!$decoded) $decoded = json_decode($first['key'], true);
    }

    if (is_array($decoded)) {
        set_transient($transient_key, $decoded, 12 * HOUR_IN_SECONDS);
        return $decoded;
    }

    return new WP_Error('decode_error', 'Could not decode business info.');
}

/* ==============================================================
   FETCH DATA DESDE API (CON CACHÉ)
============================================================== */
function real_reviews_fetch_and_decode($api_url) {
    $transient_key = 'rr_cache_' . md5($api_url);
    $cached = get_transient($transient_key);
    
    // Si hay caché, la devolvemos de inmediato
    if ($cached !== false) return $cached;

    $response = wp_remote_get($api_url, ['timeout' => 15]);
    if (is_wp_error($response)) {
        return new WP_Error('http_error', $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        return new WP_Error('empty_body', 'Respuesta vacía de la API.');
    }

    $first = json_decode($body, true);
    $decoded = null;

    if (!is_array($first) || !isset($first['key'])) {
        $maybe = base64_decode($body, true);
        if ($maybe !== false) {
            $decoded = json_decode($maybe, true);
        }
    } else {
        $decoded_key = base64_decode($first['key'], true);
        if ($decoded_key !== false) {
            $decoded = json_decode($decoded_key, true);
        }
        if (!$decoded) {
            $decoded = json_decode($first['key'], true);
        }
    }

    if (is_array($decoded)) {
        // Guardamos en caché por 12 horas
        set_transient($transient_key, $decoded, 12 * HOUR_IN_SECONDS);
        return $decoded;
    }

    return new WP_Error('decode_error', 'No se pudo decodificar la data de reseñas.');
}

/* ==============================================================
   JSON-LD SEO
============================================================== */
add_action('wp_head', function() {
    if (is_admin()) return;

    $api_url = rr_get_api_url();
    $data = real_reviews_fetch_and_decode($api_url);
    if(is_wp_error($data) || !is_array($data)) return;

    $reviews = array_values(array_filter($data, fn($r) => isset($r['comment'])));
    
    $jsonld = [
      '@context' => 'https://schema.org',
      '@type' => 'LocalBusiness',
      'name' => get_bloginfo('name'),
      'url' => get_site_url(),
      'review' => array_map(fn($r) => [
        '@type' => 'Review',
        'author' => ['@type' => 'Person', 'name' => $r['guest_name'] ?? 'Anonymous'],
        'reviewBody' => $r['comment'] ?? '',
        'reviewRating' => ['@type' => 'Rating', 'ratingValue' => intval($r['product_evaluation'] ?? 0), 'bestRating' => 5],
        'datePublished' => $r['comment_date'] ?? ''
      ], $reviews)
    ];
    
    echo '<script type="application/ld+json">' . wp_json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
});
