<?php
use Timber\Timber;

$context = Timber::context();

// Très important : récupérer le post “event” courant.
// Avec TEC, le global $post est OK sur la single.
$context['post'] = Timber::get_post(get_the_ID());

// die('SINGLE EVENT OVERRIDE OK');


Timber::render('pages/single-event.twig', $context);
