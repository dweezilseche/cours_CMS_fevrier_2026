<?php
/**
 * Template pour afficher le formulaire d'inscription Event Tickets
 * Inclus dans single-event.twig
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupérer l'ID de l'événement depuis le contexte Timber
$post_id = get_the_ID();

// Vérifier si Event Tickets est actif
if (!class_exists('Tribe__Tickets__Tickets')) {
    return;
}

// Récupérer les tickets pour cet événement
$tickets = Tribe__Tickets__Tickets::get_all_event_tickets($post_id);

if (empty($tickets)) {
    echo '<p><em>Aucun ticket ou RSVP n\'est configuré pour cet événement.</em></p>';
    return;
}

// Créer un conteneur pour le formulaire de tickets
echo '<div id="tribe-tickets-form-container" class="tribe-tickets-form-container">';

// Capturer le rendu pour vérifier si quelque chose a été affiché
ob_start();

// Une seule source d'affichage : soit le template du thème, soit les hooks (éviter le doublon)
$theme_template = locate_template(['tribe-events/tickets.php']);
if ($theme_template) {
    include $theme_template;
} else {
    // Hooks natifs d'Event Tickets (uniquement si pas de template inclus)
    do_action('tribe_tickets_rsvp_tickets_form_hook', $post_id);
    do_action('tribe_tickets_commerce_tickets_form_hook', $post_id);
    do_action('tribe_events_single_event_after_the_meta', $post_id);
}

$output = ob_get_clean();

// Si on a du contenu, l'afficher
if (!empty(trim($output))) {
    echo $output;
} else {
    // Fallback : utiliser le shortcode d'Event Tickets
    // Le shortcode [tribe_tickets] est la méthode la plus fiable
    $shortcode_output = do_shortcode('[tribe_tickets post_id="' . esc_attr($post_id) . '"]');
    
    if (!empty(trim($shortcode_output))) {
        echo $shortcode_output;
    } else {
        // Dernier recours : afficher un message avec un lien vers la page d'événement
        // où Event Tickets affiche normalement le formulaire
        $event_url = get_permalink($post_id);
        echo '<div class="tribe-tickets-fallback">';
        echo '<p><strong>Inscription à l\'événement</strong></p>';
        echo '<p>Pour vous inscrire à cet événement, veuillez consulter la <a href="' . esc_url($event_url) . '">page de l\'événement</a>.</p>';
        echo '</div>';
    }
}

echo '</div>';
