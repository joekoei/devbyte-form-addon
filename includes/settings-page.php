<?php
if (!defined('ABSPATH')) exit;

// Voeg een nieuw menu-item toe in de admin sidebar
function mcf_add_admin_menu() {
    add_menu_page(
        'DevByte - Contact Formulier Instellingen',      // Pagina titel
        'DevByte - Contact Formulier',                   // Menu titel
        'manage_options',                     // Capabilities
        'mcf-settings',                       // Menu slug
        'mcf_render_settings_page',           // Callback functie
        'dashicons-email',                    // Icoon (WordPress dashicon)
        26                                    // Positie in menu (optioneel)
    );
}
add_action('admin_menu', 'mcf_add_admin_menu');

// Pagina-output
function mcf_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Contactformulier Instellingen</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mcf_settings_group');
            do_settings_sections('mcf-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registreer de instellingen
function mcf_register_settings() {
    register_setting('mcf_settings_group', 'mcf_email_to');

    add_settings_section(
        'mcf_main_section',
        'Algemene Instellingen',
        null,
        'mcf-settings'
    );

    add_settings_field(
        'mcf_email_to',
        'E-mailadres om naar te sturen',
        'mcf_email_to_field_html',
        'mcf-settings',
        'mcf_main_section'
    );
}
add_action('admin_init', 'mcf_register_settings');

// Het invoerveld voor het e-mailadres
function mcf_email_to_field_html() {
    $value = esc_attr(get_option('mcf_email_to', ''));
    echo '<input type="email" name="mcf_email_to" value="' . $value . '" class="regular-text">';
}
