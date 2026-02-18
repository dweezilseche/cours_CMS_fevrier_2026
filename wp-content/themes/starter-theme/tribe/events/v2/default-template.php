<?php
/**
 * View: Default Template for Events
 * Override pour utiliser Timber/Twig
 *
 * @version 5.0.0
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/single-event.twig', $context);
