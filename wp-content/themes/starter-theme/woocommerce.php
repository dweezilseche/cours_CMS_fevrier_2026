<?php
/**
 * Template principal WooCommerce
 * 
 * Ce fichier sert de fallback pour toutes les pages WooCommerce
 */

use Timber\Timber;

$context = Timber::context();
$context['is_woocommerce'] = true;

// Déterminer quel template afficher
if (is_shop()) {
    $templates = ['woocommerce/archive-product.twig'];
} elseif (is_product()) {
    $templates = ['woocommerce/single-product.twig'];
} elseif (is_cart()) {
    $templates = ['woocommerce/cart.twig'];
} elseif (is_checkout()) {
    $templates = ['woocommerce/checkout.twig'];
} elseif (is_account_page()) {
    $templates = ['woocommerce/account.twig'];
} else {
    $templates = ['woocommerce/woocommerce.twig'];
}

Timber::render($templates, $context);
