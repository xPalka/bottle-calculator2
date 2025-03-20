<?php

/*
Plugin Name: Bottle Calculator
Description: Plastic bottles and labels calculator. Create, edit, have fun! Use it with shortcode [bottle_calculator].
Version: 1.0.0
Author: <a href="https://programigo.com/">Programigo</a>
by: Szymon Palka
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
const BOOTLE_CALCULATOR_PLUGIN = __FILE__;
const BOOTLE_CALCULATOR_PLUGIN_DIR = __DIR__;

function bottle_calc_shortcode() {
    $plugin_url = plugin_dir_url(__FILE__)  . '/vue-app/dist';
    $css_file = '/css/app.css';
    $chunk_file = '/js/chunk-vendors.js';
    $app_file = '/js/app.js';


    ob_start();
    ?>
    <div style="max-width: unset;">
        <link href="<?php echo $plugin_url . $css_file; ?>" rel="stylesheet">
        <div id="app">Ładowanie kalkulatora ...</div>
        <script defer src="<?php echo $plugin_url . $app_file; ?>"></script>
        <script defer src="<?php echo $plugin_url . $chunk_file; ?>"></script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bottle_calculator', 'bottle_calc_shortcode');

function save_image_and_send_email() {

    // Sprawdź, czy dane są przekazywane
    if (!isset($_POST['email']) || !isset($_POST['dataURL']) || !isset($_POST['fileName']) || !isset($_POST['telephone'])) {
        wp_send_json_error('Brak wymaganego parametru.');
        wp_die();
    }

    $email = ($_POST['email']);
    $telephone = ($_POST['telephone']);
    $dataURL = wp_unslash($_POST['dataURL']);
    $fileName = sanitize_text_field($_POST['fileName']);

// Zapis butelki i wysłanie linku
//    $dataURL = str_replace('\/', '/', $dataURL);
//
//    // Sprawdź, czy dataURL zawiera prefiks base64
//    if (!str_starts_with($dataURL, 'data:image/png;base64,')) {
//        wp_send_json_error('Nieprawidłowy format danych obrazu.');
//        wp_die();
//    }
//
//    // Odkodowanie base64 do binarnego obrazu
//    $data = str_replace('data:image/png;base64,', '', $dataURL);
//    $image_data = base64_decode($data);
//
//    if ($image_data === false) {
//        wp_send_json_error('Nie udało się dekodować danych obrazu.');
//        wp_die();
//    }
//
//    $file_name = $fileName . '_' . time() . '.png';
//    $file_path = BOOTLE_CALCULATOR_PLUGIN_DIR . '/uploads/' . $file_name;
//
//    // Upewnij się, że katalog uploads istnieje
//    if (!file_exists(BOOTLE_CALCULATOR_PLUGIN_DIR . '/uploads')) {
//        mkdir(BOOTLE_CALCULATOR_PLUGIN_DIR . '/uploads', 0755, true);
//    }
//
//    // Zapisz plik
//    if (file_put_contents($file_path, $image_data) === false) {
//        wp_send_json_error('Nie udało się zapisać pliku.');
//        wp_die();
//    }
//
//    $file_url = content_url('/plugins/bottle-calculator/uploads/'.$file_name);

    $subject = 'Marki Własne - Wygenerowano wizualizacje';
    $message = "
            <html lang='pl'>
            <head>
                <title>Marki Własne - Wygenerowano wizualizacje</title>
            </head>
            <body>
                <h2>Dane klienta, który wygenerował wizualizację:</h2>
                <ul>
                    <li>Nazwa wizualizacji: $fileName</li>
                    <li>Email klienta: $email</li>
                    <li>Numer telefonu: $telephone</li>
                    <li></li>
                </ul>
            </body>
            </html>
            ";


    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Marki Własne <no-reply@markiwlasne.pl>'
    );

    if (wp_mail($email, $subject, $message, $headers)) {
        wp_send_json_success('Wiadomość została wysłana pomyślnie!');
    } else {
        wp_send_json_error('Nie udało się wysłać email.');
    }


    wp_die();
}
add_action('wp_ajax_save_image_and_send_email', 'save_image_and_send_email');
add_action('wp_ajax_nopriv_save_image_and_send_email', 'save_image_and_send_email');




