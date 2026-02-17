<?php

namespace App\Pages;

defined('ABSPATH') || exit;

/**
 * Contexte pour la page "commande reçue" (order-received endpoint).
 * À appeler depuis le thème (woocommerce.php) lorsque is_wc_endpoint_url('order-received').
 * Regroupe les produits composés (WPC Composite Products) parent + composants dans le même bloc.
 */
class OrderReceived
{
    /**
     * Contexte Twig pour la page commande reçue : commande, items regroupés, etc.
     *
     * @param \WC_Order|null $order Commande WooCommerce.
     * @return array{order: \WC_Order|null, order_items: array, order_rows: array, order_status: string|null, order_failed: bool}
     */
    public static function getOrderReceivedContext(?\WC_Order $order): array
    {
        if (!$order) {
            return [
                'order'        => null,
                'order_items'  => [],
                'order_rows'   => [],
                'order_status' => null,
                'order_failed' => false,
            ];
        }

        $order_items = $order->get_items();
        $order_rows = self::buildOrderRows($order_items, $order);

        return [
            'order'        => $order,
            'order_items'  => $order_items,
            'order_rows'   => $order_rows,
            'order_status' => $order->get_status(),
            'order_failed' => $order->has_status('failed'),
        ];
    }

    /**
     * Construit les lignes d'affichage de la commande en regroupant chaque produit composé (parent + composants) dans un même bloc.
     *
     * @param array<int, \WC_Order_Item_Product> $order_items Items de la commande.
     * @param \WC_Order $order Commande.
     * @return array<int, array{type: 'standalone'|'composite', item: \WC_Order_Item_Product, components?: array<int, \WC_Order_Item_Product>}>
     */
    private static function buildOrderRows(array $order_items, \WC_Order $order): array
    {
        // Identifier les items qui sont des composants (ont wooco_parent_id)
        $component_item_ids = [];
        foreach ($order_items as $item_id => $item) {
            $parent_id = $item->get_meta('wooco_parent_id') ?: $item->get_meta('_wooco_parent_id');
            if ($parent_id) {
                $component_item_ids[$item_id] = (int) $parent_id; // parent product_id
            }
        }

        $rows = [];
        foreach ($order_items as $item_id => $item) {
            // Ignorer les composants (ils seront ajoutés sous leur parent)
            if (isset($component_item_ids[$item_id])) {
                continue;
            }

            $visible = apply_filters('woocommerce_order_item_visible', true, $item);
            if (!$visible) {
                continue;
            }

            // Vérifier si c'est un produit composé (a wooco_ids)
            $wooco_ids = $item->get_meta('wooco_ids') ?: $item->get_meta('_wooco_ids');
            $is_composite = !empty($wooco_ids);

            if ($is_composite) {
                // Trouver les items enfants qui ont ce produit comme parent
                $parent_product_id = $item->get_product_id();
                $components = [];
                foreach ($order_items as $child_item_id => $child_item) {
                    if (isset($component_item_ids[$child_item_id]) && 
                        (int) $component_item_ids[$child_item_id] === $parent_product_id) {
                        $components[$child_item_id] = $child_item;
                    }
                }
                $rows[] = [
                    'type'       => 'composite',
                    'item'       => $item,
                    'components' => $components,
                ];
            } else {
                $rows[] = [
                    'type' => 'standalone',
                    'item' => $item,
                ];
            }
        }

        return $rows;
    }
}
