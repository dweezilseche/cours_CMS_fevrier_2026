<?php
defined('ABSPATH') || exit;

$context = Timber\Timber::context();
$context['posts'] = Timber\Timber::get_posts();

Timber\Timber::render('pages/archive-product.twig', $context);
