<?php

namespace App\Posts;

use Timber\Timber;

defined('ABSPATH') || exit;

/**
 * Représentation Timber d'un produit WooCommerce (simple, variable, etc.).
 * Expose en Twig : price_html, short_description, image, wc_product, is_variable, variations_list.
 */
class Product extends Post
{
    /** @var \WC_Product|null */
    private ?object $_wc_product = null;

    public function wc_product(): ?object
    {
        if ($this->_wc_product === null && function_exists('wc_get_product')) {
            $this->_wc_product = wc_get_product($this->ID);
        }
        return $this->_wc_product;
    }

    /**
     * Prix formaté (pour produits variables : "À partir de X" ou "X – Y").
     */
    public function price_html(): string
    {
        $wc = $this->wc_product();
        return $wc ? (string) $wc->get_price_html() : '';
    }

    /**
     * Description courte (résumé produit).
     */
    public function short_description(): string
    {
        $wc = $this->wc_product();
        return $wc ? (string) $wc->get_short_description() : '';
    }

    /**
     * Image principale : Timber\Image ou null (thumbnail du post ou image du produit WC).
     */
    public function image(): ?object
    {
        $img = $this->thumbnail();
        if ($img !== null) {
            return $img;
        }
        $wc = $this->wc_product();
        if ($wc && $wc->get_image_id()) {
            return new \Timber\Image($wc->get_image_id());
        }
        return null;
    }

    /**
     * Première image de la galerie produit (pour hover sur la carte).
     * Retourne un objet { src, alt } pour éviter Timber\Image::src() qui peut retourner null.
     *
     * @return object{src: string, alt: string}|null
     */
    public function first_gallery_image(): ?object
    {
        $wc = $this->wc_product();
        if (!$wc || !method_exists($wc, 'get_gallery_image_ids')) {
            return null;
        }
        $ids = $wc->get_gallery_image_ids();
        if (empty($ids) || !is_array($ids)) {
            return null;
        }
        $id = (int) $ids[0];
        $src = wp_get_attachment_image_url($id, 'full');
        if ($src === false || $src === '') {
            return null;
        }
        return (object) [
            'src' => $src,
            'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true),
        ];
    }

    /**
     * Produit variable ou non.
     */
    public function is_variable(): bool
    {
        $wc = $this->wc_product();
        return $wc && $wc->is_type('variable');
    }

    /**
     * Liste des attributs de variation (ex. pa_taille => [S, M, L]) pour affichage.
     *
     * @return array<string, array<int, string>>
     */
    public function variations_list(): array
    {
        $wc = $this->wc_product();
        if (!$wc || !$wc->is_type('variable')) {
            return [];
        }
        $attributes = $wc->get_variation_attributes();
        $list = [];
        foreach ($attributes as $name => $values) {
            $list[$name] = is_array($values) ? array_values($values) : [];
        }
        return $list;
    }

    /**
     * Prix minimum (pour produits variables).
     */
    public function min_price(): string
    {
        $wc = $this->wc_product();
        if (!$wc) {
            return '';
        }
        if ($wc->is_type('variable')) {
            return (string) wc_price($wc->get_variation_prices()['price'] ? min($wc->get_variation_prices()['price']) : 0);
        }
        return (string) wc_price($wc->get_price());
    }

    /**
     * Prix maximum (pour produits variables).
     */
    public function max_price(): string
    {
        $wc = $this->wc_product();
        if (!$wc || !$wc->is_type('variable')) {
            return '';
        }
        $prices = $wc->get_variation_prices()['price'] ?? [];
        return $prices ? (string) wc_price(max($prices)) : '';
    }

    /**
     * Variations disponibles avec image pour affichage (produit variable).
     * Chaque entrée : variation_id, image (src, alt), attributes (libellé => valeur), price_html.
     *
     * @return array<int, array{variation_id: int, image: array{src: string, alt: string}|null, attributes: array<string, string>, price_html: string}>
     */
    public function available_variations_display(): array
    {
        $wc = $this->wc_product();
        if (!$wc || !$wc->is_type('variable') || !method_exists($wc, 'get_available_variations')) {
            return [];
        }
        $variations = $wc->get_available_variations();
        $out       = [];
        foreach ($variations as $v) {
            $img = null;
            if (!empty($v['image']) && is_array($v['image'])) {
                $src = $v['image']['src'] ?? $v['image']['url'] ?? $v['image_src'] ?? '';
                $img = [
                    'src' => $src,
                    'alt' => $v['image']['alt'] ?? '',
                ];
            }
            if (($img === null || (is_array($img) && ($img['src'] ?? '') === '')) && !empty($v['image_id'])) {
                $src = wp_get_attachment_image_url((int) $v['image_id'], 'woocommerce_thumbnail');
                $img = [
                    'src' => $src ?: '',
                    'alt' => (string) get_post_meta((int) $v['image_id'], '_wp_attachment_image_alt', true),
                ];
            }
            $attrs = [];
            if (!empty($v['attributes'])) {
                foreach ($v['attributes'] as $key => $value) {
                    $label = wc_attribute_label(str_replace('attribute_', '', $key), $wc);
                    $attrs[$label] = $value;
                }
            }
            $out[] = [
                'variation_id' => (int) $v['variation_id'],
                'image'       => $img,
                'attributes'  => $attrs,
                'price_html'  => $v['price_html'] ?? '',
            ];
        }
        return $out;
    }

    /**
     * Poids formaté avec unité (ex. "12 g").
     */
    public function weight_formatted(): string
    {
        $wc = $this->wc_product();
        if (!$wc || !$wc->has_weight()) {
            return '';
        }
        return wc_format_weight($wc->get_weight());
    }

    /**
     * Dimensions formatées (L × l × H avec unité).
     */
    public function dimensions_formatted(): string
    {
        $wc = $this->wc_product();
        if (!$wc || !$wc->has_dimensions()) {
            return '';
        }
        return wc_format_dimensions($wc->get_dimensions(false));
    }

    /**
     * Attributs à afficher (hors variations) : label => valeur.
     * Inclut poids et dimensions s'ils sont renseignés.
     *
     * @return array<string, string>
     */
    public function display_attributes(): array
    {
        $wc = $this->wc_product();
        if (!$wc) {
            return [];
        }
        $out = [];
        if ($wc->has_weight()) {
            $out[__('Weight', 'woocommerce')] = wc_format_weight($wc->get_weight());
        }
        if ($wc->has_dimensions()) {
            $out[__('Dimensions', 'woocommerce')] = wc_format_dimensions($wc->get_dimensions(false));
        }
        $attributes = $wc->get_attributes();
        foreach ($attributes as $attr) {
            if (!$attr->get_visible()) {
                continue;
            }
            if ($attr->get_variation()) {
                continue;
            }
            $value = $wc->get_attribute($attr->get_name());
            if ($value === '' || $value === null) {
                continue;
            }
            $label = wc_attribute_label($attr->get_name(), $wc);
            $out[$label] = $value;
        }
        return $out;
    }

    /**
     * Fil d'Ariane pour l'archive catégorie : Boutique > Catégorie > Produit.
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

            // Boutique
            [
                'title' => get_the_title(SHOP_ID),
                'link'  => get_permalink(SHOP_ID),
            ],
        ];

        // Catégorie produit (première product_cat assignée, hors "non-classe")
        $terms = get_the_terms($this->ID, 'product_cat');
        if ($terms && ! is_wp_error($terms)) {
            $terms = array_values(array_filter($terms, static function (\WP_Term $t) {
                return $t->slug !== 'non-classe';
            }));
            if ($terms !== []) {
                $term = $terms[0];
                $link = get_term_link($term);
                $items[] = [
                    'title' => $term->name,
                    'link'  => is_wp_error($link) ? '' : (string) $link,
                ];
            }
        }

        // Produit (page courante, pas de lien)
        $items[] = [
            'title' => $this->title,
        ];

        return $items;
    }

    /**
     * Charms associés à ce produit (champ ACF relationnel related_charms sur le produit).
     *
     * @return \Timber\Post[]
     */
    public function related_charms(): array
    {
        if (!function_exists('get_field')) {
            return [];
        }
        $value = get_field('related_charms', $this->ID);
        if (empty($value) || !is_array($value)) {
            return [];
        }
        $ids = array_map(static function ($item) {
            return is_object($item) && isset($item->ID) ? (int) $item->ID : (int) $item;
        }, $value);
        $ids = array_filter(array_unique($ids));
        if (empty($ids)) {
            return [];
        }
        $posts = Timber::get_posts([
            'post_type'      => 'app_charm',
            'post__in'       => $ids,
            'orderby'        => 'post__in',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);
        
        return is_iterable($posts) ? array_values(is_array($posts) ? $posts : iterator_to_array($posts)) : [];
        
    }


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
