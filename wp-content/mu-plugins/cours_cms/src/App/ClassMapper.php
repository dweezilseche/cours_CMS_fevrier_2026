<?php

namespace App;

use Wkn\Timber\ClassMapper as WknClassMapper;

defined('ABSPATH') || exit;

class ClassMapper extends WknClassMapper
{
    public function posts(): array
    {
        return [
            'page' => fn (\WP_Post $post) => match (get_page_template_slug($post->ID)) {
                'front-page.php'                       => \App\Pages\FrontPage::class,
                'page-events.php'                      => \App\Pages\Events::class,
                default                                => \App\Pages\Page::class,
            },

            'post'                => \App\Posts\Post::class,
            'tribe_events'        => \App\Posts\Event::class,
        ];
    }

    public function terms(): array
    {
        return [
        ];
    }

    public function comments(): array
    {
        return [];
    }

    public function menu(): array
    {
        return [];
    }

    public function menuitem(): array
    {
        return [];
    }

    public function user(): array
    {
        return [];
    }
}
