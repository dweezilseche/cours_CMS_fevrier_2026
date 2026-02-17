<?php
defined('ABSPATH') || exit;

$context = Timber\Timber::context();
$context['post'] = Timber\Timber::get_post();
$context['product'] = wc_get_product(get_the_ID());

Timber\Timber::render('pages/single-product.twig', $context);
