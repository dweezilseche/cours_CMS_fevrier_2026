<?php

namespace App\Pages;

use Timber;
use Timber\Post;

class FrontPage extends Post
{
    public function getLatestProducts()
    {
        return Timber::get_posts(array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'     => '_stock_status',
                    'value'   => 'instock',
                    'compare' => '=',
                ],
            ],
        ));
    }
}
