<?php
/**
 * Template personnalisé pour afficher le formulaire de tickets Event Tickets
 * 
 * Ce template surcharge le template par défaut d'Event Tickets
 * pour afficher le formulaire d'inscription sur la page d'événement
 * 
 * Ce fichier est inclus automatiquement par Event Tickets si présent dans le thème
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupérer l'ID de l'événement
$event_id = get_the_ID();

// Vérifier si Event Tickets est actif
if (!class_exists('Tribe__Tickets__Tickets')) {
    return;
}

// Récupérer tous les tickets pour cet événement
$tickets = Tribe__Tickets__Tickets::get_all_event_tickets($event_id);

if (empty($tickets)) {
    return;
}

// Event Tickets affiche automatiquement les formulaires via les hooks
// On utilise les hooks natifs pour afficher les formulaires de tickets

// Hook pour les tickets RSVP (gratuits)
do_action('tribe_tickets_rsvp_tickets_form_hook', $event_id);

// Hook pour les tickets commerciaux (WooCommerce, EDD, etc.)
do_action('tribe_tickets_commerce_tickets_form_hook', $event_id);

// Hook général utilisé par Event Tickets (fallback)
do_action('tribe_events_single_event_after_the_meta', $event_id);
