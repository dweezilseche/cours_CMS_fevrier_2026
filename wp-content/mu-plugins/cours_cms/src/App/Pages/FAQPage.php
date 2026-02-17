<?php

namespace App\Pages;

use Timber\Timber;
use Timber\Post;

defined('ABSPATH') || exit;

class FAQPage extends Post
{
    /**
     * @return \Timber\Post[]
     */
    public function faq(): array
    {
        $posts = Timber::get_posts([
            'post_type'      => 'app_faq',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        return $posts instanceof \Traversable ? iterator_to_array($posts, false) : (array) $posts;
    }
}
