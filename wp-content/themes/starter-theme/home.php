<?php

/**
 * Template Name: Actualités
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/news.twig', $context);

?>
