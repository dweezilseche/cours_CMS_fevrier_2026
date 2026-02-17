<?php

namespace App\Pages;

use Timber\Timber;
use Timber\Post;

defined('ABSPATH') || exit;

class ArchiveProducts extends Post
{
    /**
     * Contexte d'archive pour la page boutique (woocommerce/archive-product.twig).
     * À appeler depuis le thème (woocommerce.php) lorsque is_shop().
     *
     * @return array{shop_page: \Timber\Post|null, shop_url: string, products: iterable, shop_parent_categories: iterable, current_product_cat: null, current_product_tag: null, breadcrumb: array<int, array{title: string, link?: string}>}
     */
    public static function getArchiveContext(): array
    {
        $shop_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
        $shop_page    = null;
        $shop_url     = '';
        if ($shop_page_id > 0) {
            $shop_page = Timber::get_post($shop_page_id);
            $shop_url  = (string) get_permalink($shop_page_id);
        }

        $uncategorized = get_term_by('slug', 'non-classe', 'product_cat');
        $exclude_ids   = $uncategorized ? [$uncategorized->term_id] : [];

        $context = [
            'shop_page'              => $shop_page,
            'shop_url'               => $shop_url,
            'products'               => Timber::get_posts(),
            'shop_parent_categories' => Timber::get_terms([
                'taxonomy'   => 'product_cat',
                'parent'     => 0,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'exclude'    => $exclude_ids,
            ]),
            'current_product_cat'    => null,
            'current_product_tag'    => null,
            'breadcrumb'             => defined('HOME_ID') && defined('SHOP_ID')
                ? [
                    ['title' => get_the_title(HOME_ID), 'link' => get_permalink(HOME_ID)],
                    ['title' => get_the_title(SHOP_ID), 'link' => get_permalink(SHOP_ID)],
                ]
                : [],
        ];

        return $context;
    }

    /**
     * @return \Timber\Post[]
     */
    public function getProducts(): array
    {
        $posts = Timber::get_posts([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);

        return $posts instanceof \Traversable ? iterator_to_array($posts, false) : (array) $posts;
    }

    /**
     * Fil d'Ariane pour la page boutique seule.
     *
     * @return array<int, array{title: string, link: string}>
     */
    public function getBreadcrumb(): array
    {
        return [
            ['title' => get_the_title(HOME_ID), 'link' => get_permalink(HOME_ID)],
            ['title' => get_the_title(SHOP_ID), 'link' => get_permalink(SHOP_ID)],
        ];
    }
}
