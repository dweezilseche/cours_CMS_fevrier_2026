<?php
/**
 * Template principal
 * 
 * @package Starter Theme WordPress + WooCommerce
 */

$context = Timber::context();
$context['posts'] = new Timber\PostQuery();

Timber::render('pages/index.twig', $context);
