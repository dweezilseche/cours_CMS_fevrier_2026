<?php

namespace App\Pages;

defined('ABSPATH') || exit;

/**
 * Contexte pour la page panier WooCommerce (woocommerce/cart/cart.twig).
 * À appeler depuis le thème (woocommerce.php) lorsque is_cart().
 * Regroupe les produits composés (WPC Composite Products) parent + composants dans le même bloc.
 */
class Cart
{
    /**
     * Contexte Twig pour la page panier : lignes du panier (avec regroupement composite), totaux, URL checkout.
     *
     * @return array{cart_items: array, cart_rows: array, wc_cart: \WC_Cart|null, checkout_url: string}
     */
    public static function getCartContext(): array
    {
        $wc_cart = function_exists('WC') && WC()->cart ? WC()->cart : null;
        $cart_items = $wc_cart ? $wc_cart->get_cart() : [];
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '';

        $cart_rows = self::buildCartRows($cart_items, 'cart');

        return [
            'cart_items'    => $cart_items,
            'cart_rows'     => $cart_rows,
            'wc_cart'       => $wc_cart,
            'checkout_url'  => $checkout_url,
        ];
    }

    /**
     * Retourne les lignes d'affichage du panier (regroupement composite) pour un contexte donné.
     * Utilisable depuis le panier (Twig) ou le checkout (review-order.php).
     *
     * @param string $context 'cart' ou 'checkout' (filtre de visibilité appliqué en conséquence).
     * @return array<int, array{type: 'standalone'|'composite', key: string, item: array, components?: array<string, array>}>
     */
    public static function getCartRowsForDisplay(string $context = 'cart'): array
    {
        $wc_cart = function_exists('WC') && WC()->cart ? WC()->cart : null;
        $cart_items = $wc_cart ? $wc_cart->get_cart() : [];

        return self::buildCartRows($cart_items, $context);
    }

    /**
     * Construit les lignes d'affichage du panier en regroupant chaque produit composé (parent + composants) dans un même bloc.
     *
     * @param array<string, array> $cart_items Contenu du panier WC (key => cart item).
     * @param string $context 'cart' ou 'checkout'.
     * @return array<int, array{type: 'standalone'|'composite', key: string, item: array, components?: array<string, array>}>
     */
    private static function buildCartRows(array $cart_items, string $context = 'cart'): array
    {
        $component_keys = [];
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if (! empty($cart_item['wooco_parent_key'])) {
                $component_keys[$cart_item_key] = true;
            }
        }

        $visibility_filter = $context === 'checkout' ? 'woocommerce_checkout_cart_item_visible' : 'woocommerce_cart_item_visible';

        $rows = [];
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if (isset($component_keys[$cart_item_key])) {
                continue;
            }

            $visible = apply_filters($visibility_filter, true, $cart_item, $cart_item_key);
            if (! $visible) {
                continue;
            }

            $is_composite = ! empty($cart_item['wooco_ids']) && ! empty($cart_item['wooco_keys']);

            if ($is_composite) {
                $components = [];
                foreach ($cart_item['wooco_keys'] as $child_key) {
                    if (isset($cart_items[$child_key])) {
                        $components[$child_key] = $cart_items[$child_key];
                    }
                }
                $rows[] = [
                    'type'       => 'composite',
                    'key'        => $cart_item_key,
                    'item'       => $cart_item,
                    'components' => $components,
                ];
            } else {
                $rows[] = [
                    'type'  => 'standalone',
                    'key'   => $cart_item_key,
                    'item'  => $cart_item,
                ];
            }
        }

        return $rows;
    }
}
