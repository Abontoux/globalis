<?php

namespace Globalis\WP\Test;

define('REGISTRATION_ACF_KEY_LAST_NAME', 'field_64749cfff238e');
define('REGISTRATION_ACF_KEY_FIRST_NAME', 'field_64749d4bf238f');
define('REGISTRATION_ACF_KEY_EMAIL', 'field_64749d780cd14');
define('REGISTRATION_ACF_KEY_EVENT', 'field_64749cde33fd7');

add_filter('wp_insert_post_data', __NAMESPACE__ . '\\save_auto_title', 99, 2);
add_action('edit_form_after_title', __NAMESPACE__ . '\\display_custom_title_field');

function save_auto_title($data, $postarr)
{
    if (! $data['post_type'] === 'registrations') {
        return $data;
    }
    if ('auto-draft' == $data['post_status']) {
        return $data;
    }

    if (!isset($postarr['acf'][REGISTRATION_ACF_KEY_LAST_NAME]) || !isset($postarr['acf'][REGISTRATION_ACF_KEY_FIRST_NAME])) {
        return $data;
    }

    $data['post_title'] = "#" . $postarr['ID'] .  " (" . $postarr['acf'][REGISTRATION_ACF_KEY_LAST_NAME] . " " . $postarr['acf'][REGISTRATION_ACF_KEY_FIRST_NAME] . ")";

    $data['post_name']  = wp_unique_post_slug(sanitize_title(str_replace('/', '-', $data['post_title'])), $postarr['ID'], $postarr['post_status'], $postarr['post_type'], $postarr['post_parent']);

    send_registration_email($postarr);

    return $data;
}

function display_custom_title_field($post)
{
    if ($post->post_type !== 'registrations' || $post->post_status === 'auto-draft') {
        return;
    }
    ?>
    <h1><?= $post->post_title ?></h1>
    <?php
}
//Send Registration Email & ticket
function send_registration_email($postarr) {
    $event = get_post($postarr['acf'][REGISTRATION_ACF_KEY_EVENT]);
    $event->acf = get_fields($event->ID);
    $eventDate = date("F j, Y", strtotime($event->acf['event_date']));
    $eventTime = $event->acf['event_time'];
    $attachment = get_attached_file($event->acf['event_pdf_entrance_ticket']);

    $email = $postarr['acf'][REGISTRATION_ACF_KEY_EMAIL];
    $firstName = $postarr['acf'][REGISTRATION_ACF_KEY_FIRST_NAME];
    $lastName = $postarr['acf'][REGISTRATION_ACF_KEY_LAST_NAME];
    $subject = 'Votre inscription à ' . $event->post_title;

    $message = "Bonjour " . $firstName . " " . $lastName . ",<br/>
    cet email confirme votre inscription à l'évènement " . $event->post_title . "qui aura lieu le " . $eventDate . "à " . $eventTime . ". <br/>
    Vous trouverez votre billet en pièce jointe.";

    wp_mail($email, $subject, $message, $attachment);
}