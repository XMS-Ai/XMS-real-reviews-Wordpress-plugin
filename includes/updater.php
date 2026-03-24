<?php
/**
 * AUTO-UPDATER — REAL REVIEWS SUITE
 *
 * Detecta nuevas versiones publicadas como GitHub Releases y las integra
 * con el sistema de actualizaciones nativo de WordPress.
 *
 * CONFIGURACIÓN:
 *   1. Cambia RR_SUITE_GITHUB_USER por tu usuario de GitHub.
 *   2. Cambia RR_SUITE_GITHUB_REPO por el nombre del repositorio.
 *   3. Para publicar un update: crea un Release en GitHub con tag "v4.2",
 *      sube el ZIP del plugin como asset, y WordPress lo detecta solo.
 */

if (!defined('ABSPATH')) exit;

define('RR_SUITE_GITHUB_USER', 'cristiancodg');
define('RR_SUITE_GITHUB_REPO', 'XMS-real-reviews-wordpress-plugin');

/* ==============================================================
   INYECTAR UPDATE EN EL TRANSIENT DE WP
============================================================== */
add_filter('pre_set_site_transient_update_plugins', 'rrsuite_check_for_update');
function rrsuite_check_for_update($transient) {
    if (empty($transient->checked)) return $transient;

    $plugin_slug = plugin_basename(RR_SUITE_PATH . 'real-reviews-suite.php');
    $remote      = rrsuite_get_remote_info();

    if ($remote && version_compare($remote->version, RR_SUITE_VERSION, '>')) {
        $transient->response[$plugin_slug] = (object) [
            'slug'        => dirname($plugin_slug),
            'plugin'      => $plugin_slug,
            'new_version' => $remote->version,
            'url'         => 'https://realreviewsbyrealpeople.com/',
            'package'     => $remote->download_url,
            'requires'    => '5.6',
            'tested'      => '6.7',
            'icons'       => [],
        ];
    }

    return $transient;
}

/* ==============================================================
   INFORMACIÓN EN EL MODAL "VER DETALLES"
============================================================== */
add_filter('plugins_api', 'rrsuite_plugins_api_info', 20, 3);
function rrsuite_plugins_api_info($result, $action, $args) {
    if ($action !== 'plugin_information') return $result;

    $plugin_slug = dirname(plugin_basename(RR_SUITE_PATH . 'real-reviews-suite.php'));
    if (!isset($args->slug) || $args->slug !== $plugin_slug) return $result;

    $remote = rrsuite_get_remote_info();
    if (!$remote) return $result;

    return (object) [
        'name'          => 'Real Reviews Suite',
        'slug'          => $plugin_slug,
        'version'       => $remote->version,
        'author'        => '<a href="https://realreviewsbyrealpeople.com/">Xperience Marketing Solutions</a>',
        'requires'      => '5.6',
        'tested'        => '6.7',
        'last_updated'  => $remote->last_updated,
        'download_link' => $remote->download_url,
        'sections'      => [
            'description' => '<p>Sistema profesional de reseñas: Masonry Grid, Google Carousel y Formulario de envío. Incluye caché, JSON-LD SEO y panel de administración.</p>',
            'changelog'   => $remote->changelog,
        ],
    ];
}

/* ==============================================================
   LIMPIAR CACHÉ DE UPDATE AL ACTIVAR/DESACTIVAR
============================================================== */
register_activation_hook(RR_SUITE_PATH . 'real-reviews-suite.php', function() {
    delete_transient('rrsuite_update_info');
});

/* ==============================================================
   FETCH INFO DESDE GITHUB RELEASES (CON CACHÉ 12H)
============================================================== */
function rrsuite_get_remote_info() {
    $cached = get_transient('rrsuite_update_info');
    if ($cached !== false) return $cached;

    $url = sprintf(
        'https://api.github.com/repos/%s/%s/releases/latest',
        RR_SUITE_GITHUB_USER,
        RR_SUITE_GITHUB_REPO
    );

    $response = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => [
            'Accept'     => 'application/vnd.github.v3+json',
            'User-Agent' => 'RealReviewsSuite/' . RR_SUITE_VERSION,
        ],
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    if (empty($body->tag_name)) return false;

    // Buscar el ZIP entre los assets del release
    $download_url = '';
    if (!empty($body->assets)) {
        foreach ($body->assets as $asset) {
            if (substr($asset->name, -4) === '.zip') {
                $download_url = $asset->browser_download_url;
                break;
            }
        }
    }

    // Fallback: zipball automático de GitHub si no hay asset manual
    if (!$download_url) {
        $download_url = $body->zipball_url ?? '';
    }

    if (!$download_url) return false;

    // Convertir markdown del changelog a HTML básico
    $changelog = rrsuite_md_to_html($body->body ?? '');

    $data = (object) [
        'version'      => ltrim($body->tag_name, 'v'),
        'download_url' => $download_url,
        'last_updated' => isset($body->published_at)
            ? date('Y-m-d', strtotime($body->published_at))
            : '',
        'changelog'    => $changelog,
    ];

    set_transient('rrsuite_update_info', $data, 12 * HOUR_IN_SECONDS);

    return $data;
}

/* ==============================================================
   HELPER: MARKDOWN BÁSICO A HTML (PARA CHANGELOG)
============================================================== */
function rrsuite_md_to_html($text) {
    if (empty($text)) return '<p>No changelog available.</p>';

    $text = esc_html($text);
    // Headings
    $text = preg_replace('/^### (.+)$/m', '<h4>$1</h4>', $text);
    $text = preg_replace('/^## (.+)$/m',  '<h3>$1</h3>', $text);
    // Lists
    $text = preg_replace('/^[\*\-] (.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Line breaks
    $text = nl2br($text);

    return $text;
}
