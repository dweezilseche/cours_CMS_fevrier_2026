<?php
/**
 * Block: RSVP - Version surchargée personnalisée
 *
 * Affiche uniquement :
 * - Nombre de participants
 * - Nombre de places restantes
 * - Nombre de jours restants avant la fin de réservation
 * - Bouton d'inscription
 *
 * @since 1.0.0
 *
 * @var Tribe__Tickets__Editor__Template $this
 * @var WP_Post|int                      $post_id       The post object or ID.
 * @var boolean                          $has_rsvps     True if there are RSVPs.
 * @var array                            $active_rsvps  An array containing the active RSVPs.
 * @var string                           $block_html_id The unique HTML id for the block.
 */

// Ne rien afficher s'il n'y a pas de RSVP
if ( ! $has_rsvps || empty( $active_rsvps ) ) {
	return;
}

use Tribe__Date_Utils as Dates;

// Variables globales nécessaires aux sous-templates
$step = isset( $step ) ? $step : null;
$must_login = isset( $must_login ) ? $must_login : false;
?>

<div
	id="<?php echo esc_attr( $block_html_id ); ?>"
	class="tribe-common event-tickets"
>
	<?php foreach ( $active_rsvps as $rsvp ) : 
		// Calculer les jours restants pour la réservation
		$days_to_rsvp = Dates::date_diff( current_time( 'mysql' ), $rsvp->end_date );
		$days_to_rsvp = floor( $days_to_rsvp );
		
		// Vérifier si le RSVP est disponible
		$is_in_stock = $rsvp->is_in_stock();
		$remaining_tickets = $rsvp->remaining();
	?>
		<div
			class="tribe-tickets__rsvp-wrapper"
			data-rsvp-id="<?php echo esc_attr( $rsvp->ID ); ?>"
		>
			<?php $this->template( 'v2/components/loader/loader' ); ?>
			
			<div class="tribe-tickets__rsvp-custom">
				
				<!-- Nombre de participants -->
				<div class="tribe-tickets__rsvp-stat">
					<span class="tribe-tickets__rsvp-stat-label">Participants :</span>
					<strong><?php echo esc_html( $rsvp->qty_sold ); ?></strong>
				</div>
				
				<!-- Nombre de places restantes -->
				<div class="tribe-tickets__rsvp-stat">
					<span class="tribe-tickets__rsvp-stat-label">Places restantes :</span>
					<strong>
						<?php 
						if ( $remaining_tickets === -1 ) {
							echo 'Illimité';
						} else {
							echo esc_html( $remaining_tickets );
						}
						?>
					</strong>
				</div>
				
				<!-- Jours restants avant la fin de réservation -->
				<?php if ( $days_to_rsvp >= 0 ) : ?>
					<div class="tribe-tickets__rsvp-stat">
						<span class="tribe-tickets__rsvp-stat-label">Jours restants :</span>
						<strong>
							<?php 
							if ( 0 === $days_to_rsvp ) {
								echo 'Dernier jour';
							} else {
								echo esc_html( $days_to_rsvp ) . ( $days_to_rsvp > 1 ? ' jours' : ' jour' );
							}
							?>
						</strong>
					</div>
				<?php endif; ?>
				
				<!-- Formulaire RSVP avec uniquement le bouton visible -->
				<div class="tribe-tickets__rsvp-action">
					<?php 
					// Rendre le contenu complet mais on va masquer certaines parties avec CSS
					$this->template( 'v2/rsvp/content', [ 
						'rsvp' => $rsvp,
						'step' => $step,
						'must_login' => $must_login,
					] ); 
					?>
				</div>
				
			</div>
		</div>
	<?php endforeach; ?>
</div>

