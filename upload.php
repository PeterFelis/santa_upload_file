<?php
// Voor debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('wp-load.php');

// Lees de ruwe POST-gegevens
$rawData = file_get_contents("php://input");

// Parseer de data als een URL-gecodeerde string
parse_str($rawData, $parsedData);

// Nu zou $parsedData['image'] je afbeeldingsdata moeten bevatten
$imageData = $parsedData['image'] ?? null;

if ($imageData !== null) {
    list($type, $imageData) = explode(';', $imageData);
    list(, $imageData)      = explode(',', $imageData);
    $imageData = base64_decode($imageData);

    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['path'] . '/' . uniqid() . '.jpg';
    file_put_contents($upload_path, $imageData);

    $file_url = $upload_dir['url'] . '/' . basename($upload_path);
    $wp_filetype = wp_check_filetype(basename($upload_path), null);

    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload_path)),
        'post_content' => 'santa',
        'post_status' => 'inherit',
        'guid' => $file_url
    );

    $attach_id = wp_insert_attachment($attachment, $upload_path);

    if ($attach_id != 0) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        error_log('Afbeelding succesvol geüpload, attachment ID: ' . $attach_id);
    } else {
        error_log('Fout bij het uploaden van afbeelding: kon geen attachment aanmaken');
    }
} else {
    error_log('Geen afbeelding ontvangen');
}
