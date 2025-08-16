<?php
if (!defined('ABSPATH')) exit;

function mcf_handle_form_submission() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['mcf_submit'])) {
        return;
    }

    // 🛡️ Honeypot-spambescherming
    if (!empty($_POST['mcf_website'])) {
        return; // Spam → formulier wordt genegeerd
    }

    // 🧼 Velden opschonen
    $name    = sanitize_text_field($_POST['mcf_name'] ?? '');
    $email   = sanitize_email($_POST['mcf_email'] ?? '');
    $message = sanitize_textarea_field($_POST['mcf_message'] ?? '');

    if (empty($name) || empty($email) || empty($message) || !is_email($email)) {
        echo '<div class="mcf-form-message" style="color: red;">Ongeldige invoer. Controleer je gegevens.</div>';
        return;
    }

    // 🚫 Controleer geblokkeerde adressen/domeinen
    $blocked_list = get_option('mcf_blocked_emails', '');
    if (!empty($blocked_list)) {
        $blocked_items = array_filter(array_map('trim', explode("\n", $blocked_list)));

        foreach ($blocked_items as $blocked) {
            $blocked = strtolower($blocked);
            $email_lc = strtolower($email);

            // Exact e-mailadres
            if ($email_lc === $blocked) {
                echo '<div class="mcf-form-message" style="color: red;">Dit e-mailadres is geblokkeerd.</div>';
                return;
            }

            // Domein (@voorbeeld.com)
            if (strpos($blocked, '@') === 0 && str_ends_with($email_lc, substr($blocked, 1))) {
                echo '<div class="mcf-form-message" style="color: red;">E-mails van dit domein zijn geblokkeerd.</div>';
                return;
            }

            // Eindcode (.ru, .cn, .xyz)
            if (strpos($blocked, '.') === 0 && str_ends_with($email_lc, $blocked)) {
                echo '<div class="mcf-form-message" style="color: red;">E-mails met dit domein-eindstuk zijn geblokkeerd.</div>';
                return;
            }
        }
    }

    $to = get_option('mcf_email_to');
    if (!$to || !is_email($to)) {
        echo '<div class="mcf-form-message" style="color: red;">Fout: Geen geldig e-mailadres ingesteld.</div>';
        return;
    }

    // 📦 E-mail inhoud
    $subject = 'Nieuw bericht via contactformulier';
    $body = "Naam: $name\nE-mailadres: $email\n\nBericht:\n$message";
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: $name <$email>"
    ];

    // 📤 Verzenden
    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        echo '<div class="mcf-form-message" style="color: green;">Bedankt voor je bericht! We nemen spoedig contact op.</div>';
    } else {
        echo '<div class="mcf-form-message" style="color: red;">Er ging iets mis bij het verzenden. Probeer het later opnieuw.</div>';
    }
}
add_action('wp', 'mcf_handle_form_submission');
