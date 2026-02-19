<?php
/**
 * Block: RSVP - Actions Going
 * Override : utilise le composant button du thème.
 *
 * @var bool $must_login Whether the user has to login to RSVP or not.
 * @var Tribe__Tickets__Ticket_Object $rsvp The rsvp ticket object.
 *
 * @since 4.12.3
 */

if ( ! class_exists( 'Timber' ) ) {
	?>
	<div class="tribe-tickets__rsvp-actions-rsvp-going">
		<button
			class="tribe-common-c-btn tribe-tickets__rsvp-actions-button-going tribe-common-b1 tribe-common-b2--min-medium"
			type="submit"
			<?php tribe_disabled( $must_login ); ?>
		>
			<?php echo esc_html_x( 'Going', 'Label for the RSVP going button', 'event-tickets' ); ?>
		</button>
	</div>
	<?php
	return;
}

$button_context = [
	'button' => [
		'type'    => 'submit',
		'title'   => _x( 'Going', 'Label for the RSVP going button', 'event-tickets' ),
		'disabled' => ! empty( $must_login ),
		'ignore' => true,
	],
	'title'  => _x( 'Je participe', 'Label for the RSVP going button', 'event-tickets' ),
	'class'  => 'tribe-tickets__rsvp-actions-button-going is-normal booking-button is-white',
	'icon' => 'arrow-right',
	'ignore' => true,
];

?>
<div class="tribe-tickets__rsvp-actions-rsvp-going">
	<?php \Timber\Timber::render( 'components/button.twig', $button_context ); ?>
</div>
