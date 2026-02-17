<?php
/**
 * Template pour un événement unique
 * 
 * @package StarterTheme
 */

use Timber\Timber;
use App\Posts\Event;

$context = Timber::context();
$context['post'] = new Event();

// Événements associés (même catégorie)
$terms = wp_get_post_terms(get_the_ID(), 'app_event_category', ['fields' => 'ids']);
if (!empty($terms)) {
    $context['related_events'] = Timber::get_posts([
        'post_type' => 'app_event',
        'posts_per_page' => 3,
        'post__not_in' => [get_the_ID()],
        'tax_query' => [
            [
                'taxonomy' => 'app_event_category',
                'field' => 'term_id',
                'terms' => $terms,
            ],
        ],
    ]);
}

Timber::render('pages/single-event.twig', $context);
