<?php
if (!defined('ABSPATH')) exit;

function mcf_contact_form_shortcode() {
    ob_start();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mcf_form_submitted'])) {
        mcf_handle_form_submission();
    }

    ?>
    <form action="" method="post" class="mcf-form">
        <h2>Neem contact met ons op</h2>

        <!-- Honeypot veld -->
        <div style="display:none;">
            <label for="mcf_website">Laat dit veld leeg</label>
            <input type="text" name="mcf_website" id="mcf_website">
        </div>

        <p>
            <label for="mcf_name">Naam</label>
            <input type="text" name="mcf_name" id="mcf_name" required>
        </p>

        <p>
            <label for="mcf_email">E-mailadres</label>
            <input type="email" name="mcf_email" id="mcf_email" required>
        </p>

        <p>
            <label for="mcf_message">Bericht</label>
            <textarea name="mcf_message" id="mcf_message" required></textarea>
        </p>

        <p>
            <button type="submit" name="mcf_submit">Verzenden</button>
        </p>
    </form>

    <?php

    return ob_get_clean();
}
add_shortcode('devbyte-contactform', 'mcf_contact_form_shortcode');
