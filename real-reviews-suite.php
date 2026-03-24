<?php
/*
Plugin Name: Real Reviews Suite 
Description: Sistema profesional de reseñas: Masonry Grid, Google Carousel y Formulario. Incluye Caché y Panel Externo.
Version: 4.5
Author: Xperience Marketing Solutions
*/

if (!defined('ABSPATH')) exit;

// Definimos la ruta del plugin
define('RR_SUITE_PATH',    plugin_dir_path(__FILE__));
define('RR_SUITE_URL',     plugin_dir_url(__FILE__));
define('RR_SUITE_VERSION', '4.5');

/* ==============================================================
   1. REGISTRO DE ASSETS (CSS y JS)
============================================================== */
add_action('wp_enqueue_scripts', function() {
    // Registramos pero NO encolamos (se cargarán dentro de los shortcodes solo si se usan)
    wp_register_style('rr-suite-style',   RR_SUITE_URL . 'assets/css/rr-style.css',  [], RR_SUITE_VERSION);
    wp_register_script('rr-suite-scripts', RR_SUITE_URL . 'assets/js/rr-scripts.js', ['jquery'], RR_SUITE_VERSION, true);
    wp_register_script('rr-suite-form',    RR_SUITE_URL . 'assets/js/rr-form.js',    ['jquery'], RR_SUITE_VERSION, true);
});

/* ==============================================================
   2. CARGA DE MÓDULOS (INCLUDES)
============================================================== */

// Auto-updater (GitHub Releases)
require_once RR_SUITE_PATH . 'includes/updater.php';

// Lógica de API y SEO
require_once RR_SUITE_PATH . 'includes/api-handler.php';

// Administración y Ajustes
require_once RR_SUITE_PATH . 'includes/admin-ui.php';

// Registro de Shortcodes
require_once RR_SUITE_PATH . 'includes/shortcodes.php';
