<?php

/**
 * Template Name: Boutique
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('woocommerce/archive-product.twig', $context);

?>
