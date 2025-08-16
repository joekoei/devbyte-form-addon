<?php
if (!defined('ABSPATH')) exit;

// Voeg een nieuw menu-item toe in de admin sidebar
function mcf_add_admin_menu() {
    add_menu_page(
        'DevByte - Contact Formulier Instellingen',
        'DevByte - Contact Formulier',
        'manage_options',
        'mcf-settings',
        'mcf_render_settings_page',
        'dashicons-email',
        26
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
    register_setting('mcf_settings_group', 'mcf_blocked_emails');

    add_settings_section(
        'mcf_main_section',
        'Algemene Instellingen',
        null,
        'mcf-settings'
    );

    // Ontvanger e-mail
    add_settings_field(
        'mcf_email_to',
        'E-mailadres om naar te sturen',
        'mcf_email_to_field_html',
        'mcf-settings',
        'mcf_main_section'
    );

    // Geblokkeerde adressen/domeinen
    add_settings_field(
        'mcf_blocked_emails',
        'Geblokkeerde e-mails of domeinen',
        'mcf_blocked_emails_field_html',
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

// Het invoerveld voor geblokkeerde e-mails/domeinen
function mcf_blocked_emails_field_html() {
    $value = esc_textarea(get_option('mcf_blocked_emails', ''));
    echo '<textarea name="mcf_blocked_emails" rows="5" cols="50" class="large-text">' . $value . '</textarea>';
    echo '<p class="description">Voer één e-mailadres of domein per regel in. Voorbeelden:<br>
          <code>spam@voorbeeld.com</code><br>
          <code>@spamsite.com</code> (blokkeert alles van dit domein)<br>
          <code>.ru</code> (blokkeert alle adressen die eindigen op .ru)</p>';
}
