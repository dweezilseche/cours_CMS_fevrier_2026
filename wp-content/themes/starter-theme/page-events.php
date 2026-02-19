<?php
/**
 * Template Name: Tous les événements
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/page-events.twig', $context);
