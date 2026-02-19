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
$event_id = get_the_ID();

// Bloc d'enregistrement (RSVP + billets payants) pour single-registration.twig
$context['tickets_html'] = '';
if ( class_exists( 'Tribe__Tickets__Tickets_View' ) ) {
	// Le plugin utilise $GLOBALS['post'] dans ses templates (ex: post_password_required)
	$event_post = get_post( $event_id );
	if ( $event_post instanceof WP_Post ) {
		$backup_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		$GLOBALS['post'] = $event_post;
		setup_postdata( $event_post );
	}

	$tickets_view = Tribe__Tickets__Tickets_View::instance();
	// get_tickets_block() retourne '' si l'événement n'a que des RSVP → il faut aussi get_rsvp_block()
	$rsvp_html   = $tickets_view->get_rsvp_block( $event_id, false );
	$tickets_html = $tickets_view->get_tickets_block( $event_id, false );
	$context['tickets_html'] = $rsvp_html . $tickets_html;

	if ( isset( $backup_post ) ) {
		$GLOBALS['post'] = $backup_post;
		if ( $backup_post ) {
			setup_postdata( $backup_post );
		}
	}
}

Timber::render( 'pages/single-event.twig', $context );
