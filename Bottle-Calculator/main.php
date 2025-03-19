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
    if (!isset($_POST['email']) || !isset($_POST['dataURL']) || !isset($_POST['fileName'])) {
        wp_send_json_error('Brak wymaganego parametru.');
        wp_die();
    }

    $email = ($_POST['email']);
    $dataURL = wp_unslash($_POST['dataURL']);
    $fileName = sanitize_text_field($_POST['fileName']);
    $dataURL = str_replace('\/', '/', $dataURL);

    // Sprawdź, czy dataURL zawiera prefiks base64
    if (!str_starts_with($dataURL, 'data:image/png;base64,')) {
        wp_send_json_error('Nieprawidłowy format danych obrazu.');
        wp_die();
    }

    // Odkodowanie base64 do binarnego obrazu
    $data = str_replace('data:image/png;base64,', '', $dataURL);
    $image_data = base64_decode($data);

    if ($image_data === false) {
        wp_send_json_error('Nie udało się dekodować danych obrazu.');
        wp_die();
    }

    $file_name = $fileName . '_' . time() . '.png';
    $file_path = BOOTLE_CALCULATOR_PLUGIN_DIR . '/uploads/' . $file_name;

    // Upewnij się, że katalog uploads istnieje
    if (!file_exists(BOOTLE_CALCULATOR_PLUGIN_DIR . '/uploads')) {
        mkdir(BOOTLE_CALCULATOR_PLUGIN_DIR . '/uploads', 0755, true);
    }

    // Zapisz plik
    if (file_put_contents($file_path, $image_data) === false) {
        wp_send_json_error('Nie udało się zapisać pliku.');
        wp_die();
    }

    $file_url = content_url('/plugins/bottle-calculator/uploads/'.$file_name);

    // Przygotowanie treści e-maila
    $mail = new PHPMailer(true);
    $subject = 'Link do pobrania obrazu';
    $message = "
            <html lang='pl'>
            <head>
                <title>Link do pobrania obrazu</title>
            </head>
            <body>
                <h2>Link do pobrania obrazu</h2>
                <p>Kliknij poniższy link, aby pobrać obraz etykiety wygenerowanej butelki:</p>
                <a href='$file_url' style='display: inline-block; padding: 10px 20px; color: #ffffff; background-color: #0073aa; text-decoration: none; border-radius: 5px;'>Pobierz obraz</a>
            </body>
            </html>
            ";


    try {
        // Serwer SMTP
        $mail->isSMTP();
        $mail->Host = 'c1896.lh.pl';  // Adres serwera SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'dev@programigo.com';  // Adres e-mail nadawcy
        $mail->Password = '6Oa0H5QyZ';  // Hasło e-maila nadawcy
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // TLS
        $mail->Port = 587;

        $mail->setFrom('dev@programigo.com', 'Your Name');
        $mail->addAddress('szymon.palka@programigo.com', 'Recipient Name');

        // Treść wiadomości
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->CharSet = "UTF-8";

        $mail->send();
        wp_send_json_success('Wiadomość została wysłana pomyślnie!');
    } catch (Exception $e) {
        wp_send_json_error('Nie udało się wysłać email.');
    }

    wp_die();
}
add_action('wp_ajax_save_image_and_send_email', 'save_image_and_send_email');
add_action('wp_ajax_nopriv_save_image_and_send_email', 'save_image_and_send_email');




