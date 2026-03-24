<?php
/**
 * AUTO-UPDATER — REAL REVIEWS SUITE
 *
 * Lee version.json desde el repo de GitHub (rama master) y lo integra
 * con el sistema de actualizaciones nativo de WordPress.
 *
 * PARA PUBLICAR UNA ACTUALIZACIÓN:
 *   1. Sube tus cambios al repo.
 *   2. Edita version.json: cambia "version" al nuevo número.
 *   3. Haz push. WordPress lo detecta en las próximas 12 horas
 *      (o inmediatamente con ?force-check=1 en la página de Updates).
 */

if (!defined('ABSPATH')) exit;

define('RR_SUITE_GITHUB_USER', 'XMS-Ai');
define('RR_SUITE_GITHUB_REPO', 'XMS-real-reviews-Wordpress-plugin');
define('RR_SUITE_UPDATE_URL',
    'https://raw.githubusercontent.com/'
    . RR_SUITE_GITHUB_USER . '/'
    . RR_SUITE_GITHUB_REPO
    . '/master/version.json'
);

/* ==============================================================
   INYECTAR UPDATE EN EL TRANSIENT DE WP
============================================================== */
add_filter('pre_set_site_transient_update_plugins', 'rrsuite_check_for_update');
function rrsuite_check_for_update($transient) {
    if (empty($transient->checked)) return $transient;

    $remote      = rrsuite_get_remote_info();
    $plugin_file = plugin_basename(RR_SUITE_PATH . 'real-reviews-suite.php');

    if ($remote && version_compare($remote->version, RR_SUITE_VERSION, '>')) {
        $transient->response[$plugin_file] = (object) [
            'slug'        => dirname($plugin_file),
            'plugin'      => $plugin_file,
            'new_version' => $remote->version,
            'url'         => 'https://realreviewsbyrealpeople.com/',
            'package'     => $remote->download_url,
            'requires'    => $remote->requires ?? '5.6',
            'tested'      => $remote->tested   ?? '6.7',
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

    $slug = dirname(plugin_basename(RR_SUITE_PATH . 'real-reviews-suite.php'));
    if (empty($args->slug) || $args->slug !== $slug) return $result;

    $remote = rrsuite_get_remote_info();
    if (!$remote) return $result;

    return (object) [
        'name'          => 'Real Reviews Suite',
        'slug'          => $slug,
        'version'       => $remote->version,
        'author'        => '<a href="https://realreviewsbyrealpeople.com/">Xperience Marketing Solutions</a>',
        'requires'      => $remote->requires ?? '5.6',
        'tested'        => $remote->tested   ?? '6.7',
        'download_link' => $remote->download_url,
        'sections'      => [
            'description' => '<p>Sistema profesional de reseñas: Masonry Grid, Carousel y Formulario. Incluye caché, JSON-LD SEO y panel de administración.</p>',
            'changelog'   => $remote->changelog ?? '',
        ],
    ];
}

/* ==============================================================
   CORREGIR NOMBRE DE CARPETA AL INSTALAR
   GitHub extrae el ZIP como "Repo-hash/" — lo renombramos al
   nombre real del plugin para que WordPress lo reemplace bien.
============================================================== */
add_filter('upgrader_source_selection', 'rrsuite_fix_folder_name', 10, 4);
function rrsuite_fix_folder_name($source, $remote_source, $upgrader, $hook_extra) {
    global $wp_filesystem;

    if (empty($hook_extra['plugin'])) return $source;

    $plugin_file = plugin_basename(RR_SUITE_PATH . 'real-reviews-suite.php');
    if ($hook_extra['plugin'] !== $plugin_file) return $source;

    $correct_folder = trailingslashit($remote_source) . dirname($plugin_file);

    if ($source !== trailingslashit($correct_folder)
        && $wp_filesystem->move($source, $correct_folder)
    ) {
        return trailingslashit($correct_folder);
    }

    return $source;
}

/* ==============================================================
   LIMPIAR CACHÉ AL ACTIVAR
============================================================== */
register_activation_hook(RR_SUITE_PATH . 'real-reviews-suite.php', function() {
    delete_transient('rrsuite_update_info');
});

/* ==============================================================
   FETCH version.json DESDE GITHUB (CON CACHÉ 12H)
============================================================== */
function rrsuite_get_remote_info() {
    $cached = get_transient('rrsuite_update_info');
    if ($cached !== false) return $cached;

    $response = wp_remote_get(RR_SUITE_UPDATE_URL, [
        'timeout'    => 10,
        'user-agent' => 'RealReviewsSuite/' . RR_SUITE_VERSION . '; ' . get_bloginfo('url'),
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response));

    if (empty($data->version) || empty($data->download_url)) return false;

    set_transient('rrsuite_update_info', $data, 12 * HOUR_IN_SECONDS);

    return $data;
}
