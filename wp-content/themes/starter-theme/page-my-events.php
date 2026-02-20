<?php
/**
 * Template Name: Mes événements
 * Affiche les événements auxquels l'utilisateur connecté est inscrit (RSVP / billets).
 *
 * @package StarterTheme
 */

use Timber\Timber;

if (!defined('ABSPATH')) {
    exit;
}

// Éviter le cache navigateur/serveur : après une désinscription, la liste doit être à jour.
if (is_user_logged_in()) {
    nocache_headers();
}

$context = Timber::context();
$context['post'] = Timber::get_post(); // Instance de App\Pages\MyEvents

// Rendre le template Twig
Timber::render('pages/my-events.twig', $context);
