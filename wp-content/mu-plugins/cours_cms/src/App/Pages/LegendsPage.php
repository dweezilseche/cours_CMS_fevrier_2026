<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;


class LegendsPage extends Page
{
    public function charms(): array
    {
        $posts = Timber::get_posts([
            'post_type'      => 'app_charm',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        return is_iterable($posts) ? array_values(is_array($posts) ? $posts : iterator_to_array($posts)) : [];
    }
}
