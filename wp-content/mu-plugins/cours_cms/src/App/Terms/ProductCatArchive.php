<?php

namespace App\Terms;

use Timber\Term;
use Timber\Timber;

defined('ABSPATH') || exit;

/**
 * Term de catégorie produit WooCommerce.
 * Utilisé pour les archives product_cat ; fournit le contexte complet de la page.
 */
class ProductCatArchive extends Term
{
    /**
     * Contexte d'archive pour le template woocommerce/archive-product.twig.
     *
     * @return array{is_woocommerce: bool, shop_page: \Timber\Post|null, shop_url: string, products: iterable, shop_parent_categories: iterable, current_product_cat: self, current_product_tag: null}
     */
    public function getArchiveContext(): array
    {
        $uncategorized = get_term_by('slug', 'non-classe', 'product_cat');
        $exclude_ids   = $uncategorized ? [$uncategorized->term_id] : [];

        $context = [
            'is_woocommerce'         => true,
            'shop_page'              => null,
            'shop_url'               => '',
            'products'               => Timber::get_posts(),
            'shop_parent_categories' => Timber::get_terms([
                'taxonomy'   => 'product_cat',
                'parent'     => 0,
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'exclude'    => $exclude_ids,
            ]),
            'current_product_cat'    => $this,
            'current_product_tag'    => null,
        ];

        if (function_exists('wc_get_page_id')) {
            $shop_page_id = wc_get_page_id('shop');
            if ($shop_page_id > 0) {
                $context['shop_page'] = Timber::get_post($shop_page_id);
                $context['shop_url']  = (string) get_permalink($shop_page_id);
            }
        }

        return $context;
    }

    /**
     * Fil d'Ariane pour l'archive catégorie : Boutique > Catégorie.
     *
     * @return array<int, array{title: string, link?: string}>
     */
    public function breadcrumb(): array
    {
        $items = [
            [
                'title' => get_the_title(HOME_ID),
                'link'  => get_permalink(HOME_ID),
            ],
            [
                'title' => get_the_title(SHOP_ID),
                'link'  => get_permalink(SHOP_ID),
            ],
            [
                'title' => $this->name,
            ],
        ];
      
        return $items;
    }
}
