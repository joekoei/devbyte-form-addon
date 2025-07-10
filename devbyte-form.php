<?php
/**
 * Plugin Name: Devbyte - Formulier addon
 * Description: Een addon voor DevByte Sites die een contact formulier hebben.
 * Version: 1.1
 * Author: Joey Blommers
 */

if (!defined('ABSPATH')) exit;

// Bestandjes inladen
require_once plugin_dir_path(__FILE__) . 'includes/form-display.php';
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

// CSS laden (optioneel)
function mcf_enqueue_assets() {
    wp_enqueue_style('devbyte-form-style', plugin_dir_url(__FILE__) . 'assets/style.css');
}
add_action('wp_enqueue_scripts', 'mcf_enqueue_assets');
