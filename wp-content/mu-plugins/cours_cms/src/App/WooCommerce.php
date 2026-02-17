<?php

namespace App;

defined('ABSPATH') || exit;

/**
 * Configuration et hooks WooCommerce (templates Twig, panier, checkout).
 */
class WooCommerce
{
    public static function init(): void
    {
        add_theme_support('woocommerce', [
            'thumbnail_image_width' => 600,
            'single_image_width'    => 800,
            'product_grid'          => [
                'default_rows'    => 3,
                'min_rows'        => 2,
                'max_rows'        => 8,
                'default_columns' => 3,
                'min_columns'     => 2,
                'max_columns'     => 5,
            ],
        ]);
        // add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        if (!is_admin()) {
            add_filter('template_include', [self::class, 'forceWooCommerceTemplate'], 100);
            add_filter('woocommerce_locate_template', [self::class, 'locateTemplate'], 10, 3);
            add_filter('body_class', [self::class, 'bodyClassCompositeProduct'], 10, 1);
            add_action('wp', [self::class, 'customizeProductPage'], 20);
            add_action('wp', [self::class, 'removeDefaultNoticeHooks'], 5);
            add_action('wp', [self::class, 'reorderCheckoutCoupon'], 5);
        }
        add_filter('woocommerce_enqueue_styles', [self::class, 'dequeueStyles']);
        add_action('wp_enqueue_scripts', [self::class, 'dequeueScripts'], 99);
        add_filter('loop_shop_columns', [self::class, 'loopColumns']);
        add_filter('loop_shop_per_page', [self::class, 'loopPerPage'], 20);
        add_filter('woocommerce_add_to_cart_fragments', [self::class, 'cartCountFragment']);
        add_filter('woocommerce_checkout_fields', [self::class, 'customCheckoutFields']);
        add_filter('woocommerce_default_address_fields', [self::class, 'addressFieldAddress2LabelVisible']);
        // Désactiver la page "Coming Soon" en local pour afficher la vraie boutique (woocommerce.twig / archive-product.twig).
        add_filter('woocommerce_coming_soon_exclude', [self::class, 'excludeComingSoonInLocal']);
        add_filter('woocommerce_enqueue_styles', '__return_empty_array');
        // Masquer le bloc PayPal sur la page single produit (évite l’apparition au rechargement / incohérence avec la navigation PJAX).
        add_filter('woocommerce_paypal_payments_selected_button_locations', [self::class, 'removePayPalButtonOnSingleProduct'], 10, 2);

        // Galerie produit : scripts sur toutes les pages (PJAX/Taxi) + FlexSlider même pour 1 image.
        add_action('wp_enqueue_scripts', [self::class, 'enqueueProductGalleryOnAllPages'], 25);
        add_action('wp_enqueue_scripts', [self::class, 'injectProductGalleryReinitScript'], 30);
        add_filter('woocommerce_single_product_carousel_options', [self::class, 'productGalleryAllowOneSlide'], 10, 1);
        add_filter('woocommerce_get_price_html', [self::class, 'addPriceHtmlClasses'], 10, 2);
    }

    /**
     * Ajoute des classes CSS au prix régulier et au prix promo dans le HTML du prix.
     *
     * @param string    $html    HTML du prix WooCommerce.
     * @param \WC_Product $product Produit.
     * @return string
     */
    public static function addPriceHtmlClasses(string $html, $product): string
    {
        if ($html === '') {
            return $html;
        }
        $class_regular = 'price-format__regular';
        $class_sale    = 'price-format__sale';

        // Prix promo : WooCommerce utilise <del> pour le prix barré et <ins> pour le prix soldé
        if (str_contains($html, '<ins')) {
            $html = str_replace('<del ', '<del class="price-format__sale" ', $html);
            $html = str_replace('<ins ', '<ins class="price-format__regular" ', $html);
        } else {
            // Pas de promo : tout le prix est le prix régulier
            $html = '<span class="price-format__regular">' . $html . '</span>';
        }

        return $html;
    }

    /**
     * Enqueue wc-single-product et wc-flexslider sur toutes les pages pour que la galerie
     * s'initialise après une navigation Taxi vers une page produit.
     */
    public static function enqueueProductGalleryOnAllPages(): void
    {
        if (!function_exists('is_product') || is_product()) {
            return;
        }
        if (!wp_script_is('wc-single-product', 'registered')) {
            return;
        }
        wp_enqueue_script('wc-flexslider');
        wp_enqueue_script('wc-single-product');
        wp_localize_script('wc-single-product', 'wc_single_product_params', self::getDefaultProductGalleryParams());
        // Script variations nécessaire après navigation Taxi vers une page produit
        if (wp_script_is('wc-add-to-cart-variation', 'registered')) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }
    }

    /**
     * Paramètres par défaut pour la galerie (pages non-produit), utilisés après navigation Taxi.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultProductGalleryParams(): array
    {
        return [
            'i18n_required_rating_text'         => __('Please select a rating', 'woocommerce'),
            'i18n_rating_options'               => [],
            'i18n_product_gallery_trigger_text' => __('View full-screen image gallery', 'woocommerce'),
            'review_rating_required'            => 'no',
            'flexslider'                        => [
                'rtl'            => is_rtl(),
                'animation'      => 'slide',
                'smoothHeight'   => true,
                'directionNav'   => false,
                'controlNav'     => 'thumbnails',
                'slideshow'      => false,
                'animationSpeed' => 850,
                'animationLoop'  => false,
                'allowOneSlide'  => true,
            ],
            'zoom_enabled'                      => (bool) get_theme_support('wc-product-gallery-zoom'),
            'zoom_options'                      => [],
            'photoswipe_enabled'                => (bool) get_theme_support('wc-product-gallery-lightbox'),
            'photoswipe_options'               => [],
            'flexslider_enabled'               => true,
        ];
    }

    /**
     * Autorise FlexSlider avec une seule slide pour unifier la structure HTML (flex-viewport, etc.).
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function productGalleryAllowOneSlide(array $options): array
    {
        $options['allowOneSlide'] = true;
        return $options;
    }

    /**
     * Injecte un script qui (re)initialise la galerie produit au chargement et après navigation Taxi.
     * FlexSlider désactivé sur les produits composés (.is-custom-product).
     */
    public static function injectProductGalleryReinitScript(): void
    {
        if (!wp_script_is('wc-single-product', 'enqueued')) {
            return;
        }
        $js = <<<JS
(function(\$){
  function forceNoFlexsliderOnComposite(){
    if (document.body.classList.contains('is-custom-product') && typeof wc_single_product_params !== 'undefined') {
      wc_single_product_params.flexslider_enabled = false;
    }
  }
  function initProductGalleries(){
    if (typeof wc_single_product_params === 'undefined') return;
    forceNoFlexsliderOnComposite();
    var params = \$.extend({}, wc_single_product_params, { flexslider_enabled: wc_single_product_params.flexslider_enabled });
    \$('.woocommerce-product-gallery').each(function(){
      var \$gallery = \$(this);
      if (\$gallery.find('.flex-viewport').length === 0) {
        \$gallery.wc_product_gallery(params);
      }
    });
  }
  function initVariationForms(){
    if (typeof wc_add_to_cart_variation_params === 'undefined' || typeof \$.fn.wc_variation_form !== 'function') return;
    \$('.variations_form').each(function(){
      var \$form = \$(this);
      if (!\$form.data('wc_variation_form')) {
        \$form.wc_variation_form();
      }
    });
  }
  \$(function(){
    forceNoFlexsliderOnComposite();
    initProductGalleries();
    initVariationForms();
  });
  \$(document).on('taxi:afterEnter', function(){
    forceNoFlexsliderOnComposite();
    initProductGalleries();
    initVariationForms();
  });
  \$(document).on('wooco_gallery_loaded', function(){
    forceNoFlexsliderOnComposite();
  });
})(jQuery);
JS;
        wp_add_inline_script('wc-single-product', $js, 'after');
    }

    /**
     * Exclut le mode "Coming Soon" en environnement local pour que la boutique s'affiche.
     */
    public static function excludeComingSoonInLocal(): bool
    {
        return function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local';
    }

    /**
     * Ajoute la classe body « is-custom-product » sur les pages produit composé (WPC Composite Products).
     *
     * @param array<int, string> $classes
     * @return array<int, string>
     */
    public static function bodyClassCompositeProduct(array $classes): array
    {
        if (!is_product() || !function_exists('wc_get_product')) {
            return $classes;
        }
        $product = wc_get_product(get_the_ID());
        if ($product && is_a($product, 'WC_Product') && $product->is_type('composite')) {
            $classes[] = 'is-custom-product';
        }
        return $classes;
    }

    public static function customizeProductPage(): void
    {
        if (!is_product()) {
            return;
        }
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 15);
        add_filter('woocommerce_product_tabs', static function ($tabs) {
            unset($tabs['reviews']);
            return $tabs;
        }, 98);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
    }

    /**
     * @param array<string, mixed> $enqueue_styles
     * @return array<string, mixed>
     */
    public static function dequeueStyles($enqueue_styles): array
    {
        unset($enqueue_styles['woocommerce-general'], $enqueue_styles['woocommerce-smallscreen']);
        return $enqueue_styles;
    }

    public static function dequeueScripts(): void
    {
    }

    /**
     * Force le template woocommerce.php du thème pour panier, checkout et compte.
     * Sans cela, WordPress charge page.php (page-template-default) car ce sont des Pages.
     */
    public static function forceWooCommerceTemplate(string $template): string
    {
        $is_wc_page = false;

        if (function_exists('wc_get_page_id')) {
            $cart_id     = wc_get_page_id('cart');
            $checkout_id = wc_get_page_id('checkout');
            $account_id  = wc_get_page_id('myaccount');
            $is_wc_page  = ($cart_id > 0 && is_page($cart_id))
                || ($checkout_id > 0 && is_page($checkout_id))
                || ($account_id > 0 && is_page($account_id));
        }

        if (!$is_wc_page && function_exists('is_cart') && function_exists('is_checkout') && function_exists('is_account_page')) {
            $is_wc_page = is_cart() || is_checkout() || is_account_page();
        }

        if ($is_wc_page) {
            $woocommerce_php = get_stylesheet_directory() . '/woocommerce.php';
            if (!is_file($woocommerce_php)) {
                $woocommerce_php = get_template_directory() . '/woocommerce.php';
            }
            if (is_file($woocommerce_php)) {
                return $woocommerce_php;
            }
        }

        return $template;
    }

    public static function locateTemplate(string $template, string $template_name, string $template_path): string
    {
        $paths = [
            get_stylesheet_directory() . '/views/woocommerce/' . $template_name,
            get_template_directory() . '/views/woocommerce/' . $template_name,
        ];
        foreach ($paths as $custom_template) {
            if (is_file($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    public static function loopColumns(): int
    {
        return 3;
    }

    public static function loopPerPage(): int
    {
        return 12;
    }

    /**
     * Désaccroche l’affichage des notices des emplacements par défaut pour les gérer dans les templates.
     */
    public static function removeDefaultNoticeHooks(): void
    {
        remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
        remove_action('woocommerce_before_cart', 'woocommerce_output_all_notices', 10);
    }

    /**
     * Affiche le bloc coupon en dessous du tableau récap (shop_table) dans le checkout.
     */
    public static function reorderCheckoutCoupon(): void
    {
        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_coupon_form', 15);
    }

    /**
     * @param array<string, string> $fragments
     * @return array<string, string>
     */
    public static function cartCountFragment($fragments): array
    {
        ob_start();
        if (function_exists('WC') && WC()->cart) {
            echo '<span class="cart-count" data-cart-count>' . (int) WC()->cart->get_cart_contents_count() . '</span>';
        }
        $fragments['.cart-count'] = ob_get_clean();

        $notices_html = function_exists('wc_print_notices') ? wc_print_notices(true) : '';
        $fragments['#single-product-notices'] = '<div id="single-product-notices" class="woocommerce-notices-wrapper">' . $notices_html . '</div>';
        $fragments['#cart-notices'] = '<div id="cart-notices" class="woocommerce-notices-wrapper">' . $notices_html . '</div>';

        return $fragments;
    }

    /**
     * Rendre le label du champ Adresse ligne 2 visible et cliquable (WooCommerce le met en screen-reader-text par défaut).
     *
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    public static function addressFieldAddress2LabelVisible($fields): array
    {
        if (isset($fields['address_2']['label_class']) && is_array($fields['address_2']['label_class'])) {
            $fields['address_2']['label_class'] = array_values(array_filter(
                $fields['address_2']['label_class'],
                static fn(string $class): bool => $class !== 'screen-reader-text'
            ));
        }
        return $fields;
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    public static function customCheckoutFields($fields): array
    {
        return $fields;
    }

    public static function getCartCount(): int
    {
        return (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
    }

    public static function getCartTotal(): string
    {
        return (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_total() : '';
    }

    public static function isInCart(int $product_id): bool
    {
        if (!function_exists('WC')) {
            return false;
        }
        foreach (WC()->cart->get_cart() as $cart_item) {
            if (($cart_item['product_id'] ?? 0) === $product_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retire la page produit des emplacements du bouton PayPal pour éviter
     * l’affichage du bloc au rechargement (incohérent avec la navigation PJAX).
     *
     * @param array<string> $locations
     * @return array<string>
     */
    public static function removePayPalButtonOnSingleProduct($locations, string $setting_name): array
    {
        if ($setting_name !== 'smart_button_locations' || !is_array($locations)) {
            return $locations;
        }
        return array_values(array_diff($locations, ['product']));
    }
}
