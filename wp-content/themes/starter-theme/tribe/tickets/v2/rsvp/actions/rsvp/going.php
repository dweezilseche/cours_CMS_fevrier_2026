<?php
/**
 * Block: RSVP
 * Actions - RSVP - Going (Surchargé)
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets/v2/rsvp/actions/rsvp/going.php
 *
 * @link https://evnt.is/1amp Help article for RSVP & Ticket template files.
 *
 * @var bool $must_login Whether the user has to login to RSVP or not.
 * @var Tribe__Tickets__Ticket_Object $rsvp The rsvp ticket object.
 *
 * @since 4.12.3
 * @version 4.12.3
 */

?>

<div class="tribe-tickets__rsvp-actions-rsvp-going">
	<button
		class="tribe-common-c-btn tribe-tickets__rsvp-actions-button-going tribe-common-b1 tribe-common-b2--min-medium"
		type="submit"
		<?php tribe_disabled( $must_login ); ?>
	>
		<?php echo esc_html__( 'Je m\'inscris', 'starter-theme' ); ?>
	</button>
</div>
