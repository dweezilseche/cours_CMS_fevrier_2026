<?php
use Timber\Timber;
use Tribe__Date_Utils as Dates;

$context = Timber::context();

// Très important : récupérer le post "event" courant.
// Avec TEC, le global $post est OK sur la single.
$context['post'] = Timber::get_post(get_the_ID());

// DEBUG : Vérifier le contexte
$event_id = get_the_ID();
$context['debug_php_event_id'] = $event_id;
$context['debug_post_type'] = get_post_type($event_id);
$context['debug_is_single'] = is_single();
$context['debug_is_singular'] = is_singular();
$context['debug_template_file'] = __FILE__;

// Préparer les données RSVP pour le template Twig
$context['rsvps'] = [];
$context['block_html_id'] = 'tribe-tickets-rsvp-' . $event_id;
$context['post_id'] = $event_id;
$context['has_rsvps'] = false;
$context['rsvp_html'] = '';

// Récupérer tous les tickets/RSVPs pour cet événement - Plusieurs méthodes
$all_tickets = [];
$debug_methods = [];

// Méthode 1 : Tribe__Tickets__Tickets::get_all_event_tickets()
if (class_exists('Tribe__Tickets__Tickets')) {
    $tickets_method1 = Tribe__Tickets__Tickets::get_all_event_tickets($event_id);
    $debug_methods['method1_get_all_event_tickets'] = is_array($tickets_method1) ? count($tickets_method1) : 'null';
    if (!empty($tickets_method1)) {
        $all_tickets = $tickets_method1;
    }
}

// Méthode 2 : tribe_tickets()
if (empty($all_tickets) && function_exists('tribe_tickets')) {
    try {
        $repo = tribe_tickets();
        if ($repo && method_exists($repo, 'where')) {
            $tickets_method2 = $repo->where('event', $event_id)->get();
            $debug_methods['method2_tribe_tickets_where'] = is_array($tickets_method2) ? count($tickets_method2) : 'null';
            if (!empty($tickets_method2)) {
                $all_tickets = $tickets_method2;
            }
        }
    } catch (Exception $e) {
        $debug_methods['method2_error'] = $e->getMessage();
    }
}

// Méthode 3 : Recherche directe de posts de type ticket
if (empty($all_tickets)) {
    $tickets_query = new WP_Query([
        'post_type' => ['tribe_rsvp_tickets'],
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_tribe_rsvp_for_event',
                'value' => $event_id,
            ]
        ]
    ]);
    
    $debug_methods['method3_wp_query_rsvp'] = $tickets_query->found_posts;
    
    if ($tickets_query->have_posts()) {
        while ($tickets_query->have_posts()) {
            $tickets_query->the_post();
            $ticket_id = get_the_ID();
            
            // Créer un objet ticket simple
            $ticket = new stdClass();
            $ticket->ID = $ticket_id;
            $ticket->name = get_the_title($ticket_id);
            $ticket->provider_class = 'Tribe__Tickets__RSVP';
            $ticket->description = get_post_meta($ticket_id, '_tribe_ticket_description', true);
            $ticket->qty_sold = get_post_meta($ticket_id, 'total_sales', true);
            $ticket->stock = get_post_meta($ticket_id, '_stock', true);
            $ticket->capacity = get_post_meta($ticket_id, '_capacity', true);
            $ticket->start_date = get_post_meta($ticket_id, '_ticket_start_date', true);
            $ticket->end_date = get_post_meta($ticket_id, '_ticket_end_date', true);
            
            $all_tickets[] = $ticket;
        }
        wp_reset_postdata();
    }
}

// Méthode 4 : Recherche avec meta_key alternative
if (empty($all_tickets)) {
    $tickets_query2 = new WP_Query([
        'post_type' => ['tribe_rsvp_tickets'],
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_EventID',
                'value' => $event_id,
            ]
        ]
    ]);
    
    $debug_methods['method4_wp_query_EventID'] = $tickets_query2->found_posts;
    
    if ($tickets_query2->have_posts()) {
        while ($tickets_query2->have_posts()) {
            $tickets_query2->the_post();
            $ticket_id = get_the_ID();
            
            $ticket = new stdClass();
            $ticket->ID = $ticket_id;
            $ticket->name = get_the_title($ticket_id);
            $ticket->provider_class = 'Tribe__Tickets__RSVP';
            $ticket->description = get_post_meta($ticket_id, '_tribe_ticket_description', true);
            $ticket->qty_sold = get_post_meta($ticket_id, 'total_sales', true);
            $ticket->stock = get_post_meta($ticket_id, '_stock', true);
            $ticket->capacity = get_post_meta($ticket_id, '_capacity', true);
            $ticket->start_date = get_post_meta($ticket_id, '_ticket_start_date', true);
            $ticket->end_date = get_post_meta($ticket_id, '_ticket_end_date', true);
            
            $all_tickets[] = $ticket;
        }
        wp_reset_postdata();
    }
}

// DEBUG : voir tous les tickets
$context['debug_tickets_count'] = is_array($all_tickets) ? count($all_tickets) : 0;
$context['debug_tickets'] = [];
$context['debug_methods'] = $debug_methods;

if (!empty($all_tickets)) {
    $active_rsvps = [];
    
    foreach ($all_tickets as $ticket) {
        // DEBUG : capturer les infos de chaque ticket
        $context['debug_tickets'][] = [
            'id' => isset($ticket->ID) ? $ticket->ID : 'N/A',
            'name' => isset($ticket->name) ? $ticket->name : (isset($ticket->post_title) ? $ticket->post_title : 'N/A'),
            'provider_class' => isset($ticket->provider_class) ? $ticket->provider_class : 'N/A',
            'type' => get_class($ticket),
        ];
        
        // Filtrer uniquement les RSVPs (pas les tickets commerciaux)
        if (isset($ticket->provider_class) && $ticket->provider_class === 'Tribe__Tickets__RSVP') {
            $active_rsvps[] = $ticket;
        }
    }
        
        // Préparer les données pour le template Twig
        if (!empty($active_rsvps)) {
            $context['has_rsvps'] = true;
            
            foreach ($active_rsvps as $rsvp) {
                // Calculer les jours restants
                $days_to_rsvp = 0;
                if (!empty($rsvp->end_date)) {
                    $days_to_rsvp = Dates::date_diff(current_time('mysql'), $rsvp->end_date);
                    $days_to_rsvp = floor($days_to_rsvp);
                }
                
                // Calculer les places disponibles
                $remaining_tickets = method_exists($rsvp, 'remaining') ? $rsvp->remaining() : -1;
                $is_in_stock = method_exists($rsvp, 'is_in_stock') ? $rsvp->is_in_stock() : true;
                
                // Nombre de participants (vendus)
                $qty_sold = isset($rsvp->qty_sold) ? (int) $rsvp->qty_sold : 0;
                
                // Vérifier si dans la plage de dates
                $in_date_range = true;
                $now = current_time('timestamp');
                
                if (!empty($rsvp->start_date)) {
                    $start_timestamp = strtotime($rsvp->start_date);
                    if ($now < $start_timestamp) {
                        $in_date_range = false;
                    }
                }
                
                if (!empty($rsvp->end_date)) {
                    $end_timestamp = strtotime($rsvp->end_date);
                    if ($now > $end_timestamp) {
                        $in_date_range = false;
                    }
                }
                
                // Données normalisées pour le template
                $context['rsvps'][] = [
                    'id' => $rsvp->ID,
                    'name' => isset($rsvp->name) ? $rsvp->name : get_the_title($rsvp->ID),
                    'description' => isset($rsvp->description) ? $rsvp->description : '',
                    'show_description' => method_exists($rsvp, 'show_description') ? $rsvp->show_description() : !empty($rsvp->description),
                    'qty_sold' => $qty_sold,
                    'remaining' => $remaining_tickets,
                    'available' => $remaining_tickets,
                    'is_in_stock' => $is_in_stock,
                    'in_date_range' => $in_date_range,
                    'days_to_rsvp' => max(0, $days_to_rsvp),
                    'days_remaining' => max(0, $days_to_rsvp),
                    'start_date' => isset($rsvp->start_date) ? $rsvp->start_date : '',
                    'end_date' => isset($rsvp->end_date) ? $rsvp->end_date : '',
                    'max_purchase' => isset($rsvp->max_purchase) ? $rsvp->max_purchase : 10,
                    'rsvp_object' => $rsvp, // Pour accès complet si nécessaire
                ];
            }
            
            // Capturer le HTML du formulaire RSVP
            ob_start();
            
            // Utiliser le système de templates v2 d'Event Tickets
            if (function_exists('tribe')) {
                $template = tribe('tickets.editor.template');
                if ($template && method_exists($template, 'template')) {
                    $template->template(
                        'v2/rsvp',
                        [
                            'post_id' => $event_id,
                            'has_rsvps' => true,
                            'active_rsvps' => $active_rsvps,
                            'block_html_id' => 'tribe-tickets-rsvp-' . $event_id,
                        ]
                    );
                }
            }
            
            $context['rsvp_html'] = ob_get_clean();
        }
    }
}

// die('SINGLE EVENT OVERRIDE OK');


Timber::render('pages/single-event.twig', $context);
