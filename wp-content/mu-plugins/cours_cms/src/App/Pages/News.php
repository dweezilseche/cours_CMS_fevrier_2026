<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;

class News extends Page
{
    /**
     * Récupère tous les articles publiés
     */
    public function get_latest_posts(): array
    {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
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
        return $this->get_latest_posts();
    }
}
