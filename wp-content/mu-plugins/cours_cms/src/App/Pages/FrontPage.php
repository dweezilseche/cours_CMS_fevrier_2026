<?php

namespace App\Pages;

use Timber\Timber;
use Timber\Post;

defined('ABSPATH') || exit;

class FrontPage extends Post
{
    /**
     * @return \Timber\Post[]
     */
    public function getLatestProducts(): array
    {
        $posts = Timber::get_posts([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='],
            ],
            'tax_query'      => [
                [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => ['exclude-from-catalog'],
                    'operator' => 'NOT IN',
                ],
            ],
        ]);

        return $posts instanceof \Traversable ? iterator_to_array($posts, false) : (array) $posts;
    }
}
