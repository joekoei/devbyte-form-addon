<?php
if (!defined('ABSPATH')) exit;

/**
 * Logfunctie voor geblokkeerde inzendingen
 */
function mcf_log_blocked_submission($email, $reason) {
    $logs = get_option('mcf_blocked_logs', []);
    if (!is_array($logs)) {
        $logs = [];
    }

    $logs[] = [
        'time'   => current_time('mysql'),
        'email'  => $email,
        'reason' => $reason,
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'onbekend',
    ];

    // Max 100 entries bewaren
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100); // pak de laatste 100
    }

    update_option('mcf_blocked_logs', $logs);
}

/**
 * Handler contactformulier
 */
function mcf_handle_form_submission() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['mcf_submit'])) {
        return;
    }

    // 🛡️ Honeypot
    if (!empty($_POST['mcf_website'])) {
        return;
    }

    // 🧼 Opschonen
    $name    = sanitize_text_field($_POST['mcf_name'] ?? '');
    $email   = sanitize_email($_POST['mcf_email'] ?? '');
    $message = sanitize_textarea_field($_POST['mcf_message'] ?? '');

    if (empty($name) || empty($email) || empty($message) || !is_email($email)) {
        echo '<div class="mcf-form-message" style="color: red;">Ongeldige invoer. Controleer je gegevens.</div>';
        return;
    }

    // 🚫 Blacklist check
    $blocked_list = get_option('mcf_blocked_emails', '');
    if (!empty($blocked_list)) {
        $blocked_items = array_filter(array_map('trim', explode("\n", $blocked_list)));
        $email_lc = strtolower($email);

        foreach ($blocked_items as $blocked) {
            $blocked = strtolower($blocked);

            // Wildcard (*)
            if (str_contains($blocked, '*')) {
                $pattern = '/^' . str_replace('\*', '.*', preg_quote($blocked, '/')) . '$/i';
                if (preg_match($pattern, $email_lc)) {
                    mcf_log_blocked_submission($email, "Wildcard match: $blocked");
                    echo '<div class="mcf-form-message" style="color: red;">Dit e-mailadres is geblokkeerd.</div>';
                    return;
                }
            }
            // Exact match
            elseif ($email_lc === $blocked) {
                mcf_log_blocked_submission($email, "Exact match: $blocked");
                echo '<div class="mcf-form-message" style="color: red;">Dit e-mailadres is geblokkeerd.</div>';
                return;
            }
            // Domein
            elseif (strpos($blocked, '@') === 0 && str_ends_with($email_lc, substr($blocked, 1))) {
                mcf_log_blocked_submission($email, "Domein match: $blocked");
                echo '<div class="mcf-form-message" style="color: red;">E-mails van dit domein zijn geblokkeerd.</div>';
                return;
            }
            // Eindcode (.ru enz.)
            elseif (strpos($blocked, '.') === 0 && str_ends_with($email_lc, $blocked)) {
                mcf_log_blocked_submission($email, "Eindcode match: $blocked");
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

    // 📦 Mail
    $subject = 'Nieuw bericht via contactformulier';
    $body = "Naam: $name\nE-mailadres: $email\n\nBericht:\n$message";
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: $name <$email>"
    ];

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        echo '<div class="mcf-form-message" style="color: green;">Bedankt voor je bericht! We nemen spoedig contact op.</div>';
    } else {
        echo '<div class="mcf-form-message" style="color: red;">Er ging iets mis bij het verzenden. Probeer het later opnieuw.</div>';
    }
}
add_action('wp', 'mcf_handle_form_submission');
