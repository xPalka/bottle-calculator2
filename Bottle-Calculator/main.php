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
    $plugin_url = plugin_dir_url(__FILE__)  . 'vue-app';
    $css_file = '/css/app.css';
    $chunk_file = '/js/chunk-vendors.js';
    $app_file = '/js/app.js';

    // Załaduj tłumaczenia w kontekście tego shortcode
    $translations = array(
        'productName0' => __('HoReCa - Preparat do mycia samochodów', 'bottle-calculator'),
        'productName1' => __('Automotive - Szampon samochodowy z woskiem', 'bottle-calculator'),
        'productName2' => __('Chemia techniczna - Preparaty do myjni samochodowych', 'bottle-calculator'),

        'loadingCalculator' => __('Ładowanie kalkulatora ...', 'bottle-calculator'),
        'visualizationName' => __('Nazwa wizualizacji: ', 'bottle-calculator'),
        'clientEmail' => __('Email klienta: ', 'bottle-calculator'),
        'clientPhone' => __('Numer telefonu: ', 'bottle-calculator'),
        'emailSentSuccess' => __('Wiadomość została wysłana pomyślnie!', 'bottle-calculator'),
        'emailSendError' => __('Nie udało się wysłać email.', 'bottle-calculator'),
        'missingParameter' => __('Brak wymaganego parametru.', 'bottle-calculator'),
        'visualizationSubject' => __('Marki Własne - Wygenerowano wizualizacje', 'bottle-calculator'),
        // Tłumaczenia dla formularza
        'step1' => __('1. Wybierz produkt', 'bottle-calculator'),
        'step2' => __('2. Wybierz butelkę', 'bottle-calculator'),
        'step3' => __('3. Prześlij etykietę', 'bottle-calculator'),
        'step4' => __('4. Pobierz swoją wizualizację', 'bottle-calculator'),
        'product' => __('Produkt', 'bottle-calculator'),
        'bottle' => __('Butelka', 'bottle-calculator'),
        'placeLabel' => __('Umieść etykietę', 'bottle-calculator'),
        'enterName' => __('Podaj nazwę wizualizacji', 'bottle-calculator'),
        'chooseFile' => __('Wybierz plik', 'bottle-calculator'),
        'modalTitle' => __('5. Pobierz wizualizacje', 'bottle-calculator'),
        'email' => __('Adres email', 'bottle-calculator'),
        'phone' => __('Telefon kontaktowy', 'bottle-calculator'),
        'emailPlaceholder' => __('Adres email', 'bottle-calculator'),
        'phonePlaceholder' => __('Telefon', 'bottle-calculator'),
        'acceptMarketing' => __('Akceptuję zgodę marketingową, która zawiera', 'bottle-calculator'),
        'acceptTerms' => __('Akceptuję regulamin, który zawiera', 'bottle-calculator'),
        'showMore' => __('[...]', 'bottle-calculator'),
        'hideText' => __('[ukryj]', 'bottle-calculator'),
        'termsText' => __('ipsum dolor sit amet, consectetur adipiscing elit. Quisque id diam sit amet odio tempus auctor...', 'bottle-calculator'),
        'termsText2' => __('Nam eget risus at magna dictum bibendum. Vivamus ut sapien ac quam condimentum suscipit...', 'bottle-calculator'),
        'downloadImage' => __('Pobierz obraz', 'bottle-calculator'),
        'cancel' => __('Anuluj', 'bottle-calculator'),
        'close' => __('Zamknij', 'bottle-calculator'),
        'errorMessage' => __('Wystąpił błąd. Spróbuj ponownie.', 'bottle-calculator')
    );

    ob_start();
    ?>
    <div style="max-width: unset;">
        <link href="<?php echo $plugin_url . $css_file; ?>" rel="stylesheet">
        <div id="app">Ładowanie kalkulatora ...</div>
        <script>
            window.translations__bottleApp = <?php echo json_encode($translations); ?>;
        </script>
        <script defer src="<?php echo $plugin_url . $app_file; ?>"></script>
        <script defer src="<?php echo $plugin_url . $chunk_file; ?>"></script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('bottle_calculator', 'bottle_calc_shortcode');

function save_image_and_send_email() {
    // Sprawdź, czy dane są przekazywane
    if (!isset($_POST['email']) || !isset($_POST['fileName']) || !isset($_POST['telephone'])) {
        wp_send_json_error(__('Brak wymaganego parametru.', 'bottle-calculator'));
        wp_die();
    }

    $email = ($_POST['email']);
    $telephone = ($_POST['telephone']);
    $fileName = sanitize_text_field($_POST['fileName']);

    $subject = __('Marki Własne - Wygenerowano wizualizacje', 'bottle-calculator');
    $message = "
            <html lang='pl'>
            <head>
                <title>" . __('Marki Własne - Wygenerowano wizualizacje', 'bottle-calculator') . "</title>
            </head>
            <body>
                <h2>" . __('Dane klienta, który wygenerował wizualizację:', 'bottle-calculator') . "</h2>
                <ul>
                    <li>" . __('Nazwa wizualizacji: ', 'bottle-calculator') . "$fileName</li>
                    <li>" . __('Email klienta: ', 'bottle-calculator') . "$email</li>
                    <li>" . __('Numer telefonu: ', 'bottle-calculator') . "$telephone</li>
                </ul>
            </body>
            </html>
            ";

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Marki Własne <no-reply@markiwlasne.pl>'
    );

    if (wp_mail($email, $subject, $message, $headers)) {
        wp_send_json_success(__('Wiadomość została wysłana pomyślnie!', 'bottle-calculator'));
    } else {
        wp_send_json_error(__('Nie udało się wysłać email.', 'bottle-calculator'));
    }

    wp_die();
}
add_action('wp_ajax_save_image_and_send_email', 'save_image_and_send_email');
add_action('wp_ajax_nopriv_save_image_and_send_email', 'save_image_and_send_email');




