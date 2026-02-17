<?php

namespace App\Pages;

defined('ABSPATH') || exit;

/**
 * Comportement de la page single product WooCommerce.
 * À utiliser depuis le template single-product du thème.
 */
class SingleProduct
{
    private const SESSION_STORAGE_KEY = 'cours_cms_sp_reload';

    /**
     * Enregistre un rechargement forcé à l’arrivée sur la page produit (une fois par produit).
     * Le script est injecté via wp_footer.
     *
     * @param int $product_id ID du post produit (get_the_ID()).
     */
    public static function registerForceReload(int $product_id): void
    {
        if ($product_id <= 0) {
            return;
        }
        add_action('wp_footer', function () use ($product_id) {
            $id = (int) $product_id;
            if ($id <= 0) {
                return;
            }
            $key = self::SESSION_STORAGE_KEY;
            echo '<script>(function(){var id=' . $id . ';var k="' . esc_js($key) . '";if(sessionStorage.getItem(k)!==String(id)){sessionStorage.setItem(k,String(id));location.reload();}})();</script>';
        }, 5);
    }
}
