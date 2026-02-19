<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;

class Events extends Page
{
    public function get_all_events(): array
    {
        $args = [
            'post_type' => 'tribe_events',
            'post_status' => 'publish',
            'posts_per_page' => -1,
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

    public function all_events(): array
    {
        return $this->get_all_events();
    }
}
