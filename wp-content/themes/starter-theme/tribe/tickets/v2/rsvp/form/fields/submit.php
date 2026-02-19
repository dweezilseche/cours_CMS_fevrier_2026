<?php
/**
 * Block: RSVP - Form Submit Button
 * Override : utilise le composant button du thème.
 *
 * @since 4.12.3
 */

if ( ! class_exists( 'Timber' ) ) {
	echo '<button class="tribe-common-c-btn tribe-tickets__rsvp-form-button" type="submit">' . esc_html__( 'Finish', 'event-tickets' ) . '</button>';
	return;
}

$button_context = [
	'button' => [
		'type'  => 'submit',
		'title' => __( 'Finish', 'event-tickets' ),
	],
	'title'  => __( 'Finish', 'event-tickets' ),
	'class'  => 'tribe-tickets__rsvp-form-button is-normal submit-button is-white',
	'icon' => 'arrow-right',
];

\Timber\Timber::render( 'components/button.twig', $button_context );
