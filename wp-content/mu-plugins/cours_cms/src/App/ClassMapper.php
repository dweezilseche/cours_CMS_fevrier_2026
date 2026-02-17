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
                'woocommerce/archive-product.php'      => \App\Pages\ArchiveProducts::class,
                'page-legends.php'                     => \App\Pages\LegendsPage::class,
                'page-our-story.php'                   => \App\Pages\OurStoryPage::class,
                'page-custom-made.php'                 => \App\Pages\CustomMadePage::class,
                'page-custom-product.php'              => \App\Pages\CustomProductPage::class,
                'page-faq.php'                         => \App\Pages\FAQPage::class,
                default                                => \App\Pages\Page::class,
            },

            'post'                => \App\Posts\Post::class,
            'product'             => \App\Posts\Product::class,
            'app_charm'           => \App\Posts\Charm::class,
            'app_faq'             => \App\Posts\FAQ::class,
        ];
    }

    public function terms(): array
    {
        return [
            'product_tag'           => \App\Terms\ProductTagArchive::class,
            'product_cat'           => \App\Terms\ProductCatArchive::class,
            'app_charm_taxonomy'    => \App\Taxonomies\CharmsTypeTaxonomy::class,
            'app_faq_taxonomy'      => \App\Taxonomies\FAQTypeTaxonomy::class,
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
