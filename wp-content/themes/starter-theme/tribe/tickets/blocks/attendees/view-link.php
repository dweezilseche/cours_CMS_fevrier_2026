<?php
/**
 * Block: Attendees List - View Link
 * Override : redirige vers la page "Mes événements" du thème au lieu de la page par défaut Tribe.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets/blocks/attendees/view-link.php
 *
 * @since 4.9
 * @version 5.8.0
 *
 * @var Tribe__Tickets__Editor__Template $this
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( isset( $hide_view_my_tickets_link ) && tribe_is_truthy( $hide_view_my_tickets_link ) ) {
	return;
}

$view     = Tribe__Tickets__Tickets_View::instance();
$event_id = $this->get( 'post_id' ) ?? get_the_ID();

$data = $view->get_my_tickets_link_data( $event_id, get_current_user_id() );

if ( empty( $data['total_count'] ) ) {
	return;
}

// Trouver la page "Mes événements" (template page-my-events.php)
$my_events_page = null;
$pages = get_pages([
	'meta_key'   => '_wp_page_template',
	'meta_value' => 'page-my-events.php',
	'number'     => 1,
]);

if ( ! empty( $pages ) ) {
	$my_events_page = $pages[0];
	$my_events_url  = get_permalink( $my_events_page->ID );
} else {
	// Fallback : utiliser le lien par défaut de Tribe si la page n'existe pas
	$my_events_url = $data['link'];
}

?>
<div class="tribe-link-view-attendee">
	<?php echo esc_html( $data['message'] ); ?>
	<a href="<?php echo esc_url( $my_events_url ); ?>">
		<?php echo esc_html( $data['link_label'] ); ?>
	</a>
</div>
