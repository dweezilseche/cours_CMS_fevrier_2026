<?php

/**
 * Template Name: Panier
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/page-panier.twig', $context);

?>
