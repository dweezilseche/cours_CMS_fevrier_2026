<?php
/**
 * Template Name: Mes événements
 * Affiche les événements auxquels l'utilisateur connecté est inscrit (RSVP / billets).
 *
 * @package StarterTheme
 */

use Timber\Timber;

if ( ! defined('ABSPATH')) {
    exit;
}

$context = Timber::context();

if ( ! is_user_logged_in()) {
    $context['my_events_logged_out'] = true;
    $context['events']               = [];
    $context['attendee_by_event']    = [];
    $context['unregister_urls']     = [];
    $context['message']              = '';
    Timber::render('pages/my-events.twig', $context);
    return;
}

$user_events_data = class_exists(\App\Theme::class)
    ? \App\Theme::get_current_user_events_data()
    : [];

$event_ids = array_unique(array_column($user_events_data, 'event_id'));
$attendee_by_event = [];
foreach ($user_events_data as $row) {
    if ( ! empty($row['attendee_id'])) {
        $attendee_by_event[ (int) $row['event_id'] ] = (int) $row['attendee_id'];
    }
}

$my_events_url = get_permalink();
$unregister_urls = [];
foreach ($attendee_by_event as $eid => $aid) {
    $unregister_urls[ $eid ] = wp_nonce_url(
        add_query_arg([
            'action'       => 'unregister_event',
            'attendee_id'  => $aid,
            'redirect_to'  => $my_events_url,
        ], admin_url('admin.php')),
        'unregister_event_' . $aid
    );
}

$context['attendee_by_event'] = $attendee_by_event;
$context['unregister_urls']   = $unregister_urls;
$context['message']          = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';

if (empty($event_ids)) {
    $context['events']          = [];
    $context['unregister_urls'] = [];
    Timber::render('pages/my-events.twig', $context);
    return;
}

$events_query = [
    'post_type'      => 'tribe_events',
    'post__in'       => $event_ids,
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_key'       => '_EventStartDate',
];

$context['events'] = Timber::get_posts($events_query);
Timber::render('pages/my-events.twig', $context);
