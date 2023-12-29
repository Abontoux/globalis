<?php

add_action('admin_post_export_registrations', __NAMESPACE__ . '\\export_registrations');

function export_registrations()
{
    $writer = new \OpenSpout\Writer\XLSX\Writer();

    $event_id = $_GET['event_id'];
    $event = get_post($event_id);
    $event = $event->post_name;
    
    $writer->openToBrowser($event . '-id-' . $event_id . '.xlsx');

    $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['Nom', 'Prénom', 'Email', 'Téléphone']));

    $args = [
        'post_type'=> 'registrations',
        'meta_query' => [
            [
                'key' => 'registration_event_id',
                'value' => $event_id,
                'compare' => '='
            ]
        ]
    ];
    $registrations = get_posts($args);

    foreach($registrations as $registration) :
        $registration->acf = get_fields($registration->ID);
        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
            $registration->acf['registration_last_name'],
            $registration->acf['registration_first_name'],
            $registration->acf['registration_email'],
            $registration->acf['registration_phone'],
        ]));
    endforeach;

    $writer->close();

}
