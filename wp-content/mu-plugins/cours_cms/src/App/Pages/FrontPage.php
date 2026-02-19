<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;

class FrontPage extends Page
{
    /**
     * Récupère les 3 prochains événements à venir
     */
    public function getUpcomingEvents(int $limit = 3): array
    {
        $args = [
            'post_type' => 'tribe_events',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_key' => '_EventStartDate',
            'meta_query' => [
                [
                    'key' => '_EventStartDate',
                    'value' => current_time('Y-m-d H:i:s'),
                    'compare' => '>=',
                    'type' => 'DATETIME'
                ]
            ]
        ];
        
        $events = Timber::get_posts($args);
        return is_array($events) ? $events : (method_exists($events, 'to_array') ? $events->to_array() : iterator_to_array($events));
    }
    
    /**
     * Expose les événements à venir directement dans le contexte Twig
     */
    public function upcoming_events(): array
    {
        return $this->getUpcomingEvents(3);
    }

    /**
     * Récupère les derniers articles publiés
     */
    public function getLatestPosts(int $limit = 3): array
    {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $posts = Timber::get_posts($args);
        return is_array($posts) ? $posts : (method_exists($posts, 'to_array') ? $posts->to_array() : iterator_to_array($posts));
    }

    /**
     * Expose les derniers articles directement dans le contexte Twig
     */
    public function latest_posts(): array
    {
        return $this->getLatestPosts(3);
    }
}
