<?php

namespace GSTEAM;
/**
 * GS Team - Layout Popup
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/gs-team-layout-popup.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.3
 */

global $gs_team_loop;

$gs_row_classes = ['gs-roow clearfix gs_team'];

if ( $_drawer_enabled ) $gs_row_classes[] = 'gstm-gridder gstm-gridder-' . $drawer_style;

?>

<!-- Container for Team members -->
<div class="gs-containeer cbp-so-scroller">
	
	<div class="<?php echo esc_attr( implode(' ', $gs_row_classes) ); ?>" id="gs_team<?php echo get_the_id(); ?>">

		<?php if ( $gs_team_loop->have_posts() ):

			if ( $_drawer_enabled ) echo '<div class="gridder">';

			do_action( 'gs_team_before_team_members' );

			while ( $gs_team_loop->have_posts() ): $gs_team_loop->the_post();

			$designation = get_post_meta( get_the_id(), '_gs_des', true );

			$classes = ['single-member-div', get_col_classes( $gs_team_cols, $gs_team_cols_tablet, $gs_team_cols_mobile_portrait, $gs_team_cols_mobile ) ];

			if ( $gs_member_link_type == 'popup' ) $classes[] = 'single-member-pop';
			if ( $_drawer_enabled ) $classes[] = 'gridder-list';
			if ( $enable_scroll_animation == 'on' ) $classes[] = 'cbp-so-section';

			$single_item_attr = '';
			if ( $_drawer_enabled ) $single_item_attr = sprintf( 'data-griddercontent="#gs-team-drawer-%s-%s"', get_the_ID(), $id );

			?>
			
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" <?php echo wp_kses_post( $single_item_attr ); ?>>
			    
				<!-- Sehema & Single member wrapper -->
				<div class="single-member--wrapper" itemscope itemtype="http://schema.org/Person">
					<div class="single-member">

						<?php

						if ( $gs_member_name_is_linked == 'on' ) {

							if ( $gs_member_link_type == 'custom' ) {
								$custom_page_link = get_post_meta( get_the_ID(), '_gs_custom_page', true );
								if ( empty($custom_page_link) ) {
									$gs_member_link_type = getoption('single_link_type', 'single_page');
								}
							}

							if ( $gs_member_link_type == 'single_page' ) {
								printf( '<a href="%s">', get_the_permalink() );
							} else if ( $gs_member_link_type == 'popup' ) {
								printf( '<a class="gs_team_pop open-popup-link" data-mfp-src="#gs_team_popup_%s_%s" href="#">', get_the_ID(), $id );
							}  else if ($gs_member_link_type == 'panel') {
								printf('<a class="gs_team_pop gs_team_panelslide_link" id="gsteamlink_%1$s_%2$s" href="#gsteam_%1$s_%2$s">', get_the_ID(), $id);
							} else if ($gs_member_link_type == 'drawer') {
								printf('<a class="gs_team_drawer" href="%s">', get_the_permalink());
							} else if ($gs_member_link_type == 'custom') {
								$target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
								printf('<a %s href="%s">', $target, esc_url($custom_page_link));
							}
						}

							do_action( 'gs_team_before_member_content', $gs_team_theme ); ?>
						
							<!-- Ribbon -->
							<?php include Template_Loader::locate_template( 'partials/gs-team-layout-ribon.php' ); ?>
							
							<!-- Team Image -->
							<div class="gs_team_image__wrapper">
								<?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
							</div>
							<?php do_action( 'gs_team_after_member_thumbnail' ); ?>
							
							<!-- Indicator -->
							<?php if ( $gs_member_name_is_linked == 'on' ) : ?>
								<div class="gs_team_overlay"><i class="fas fa-external-link-alt"></i></div>
							<?php endif; ?>

							<div class="single-member-name-desig cbp-so-side cbp-so-side-right">

								<!-- Single member name -->
								<?php if ( 'on' ==  $gs_member_name ): ?>
									<?php member_name( $id, true, false ); ?>
									<?php do_action( 'gs_team_after_member_name' ); ?>
								<?php endif; ?>
								
								<!-- Single member designation -->
								<?php if ( !empty( $designation ) && 'on' == $gs_member_role ): ?>
									<div class="gs-member-desig" itemprop="jobTitle"><?php echo wp_kses_post($designation); ?></div>
									<?php do_action( 'gs_team_after_member_designation' ); ?>
								<?php endif; ?>

							</div>

							<?php do_action( 'gs_team_after_member_content' ); ?>

						<?php if ( $gs_member_name_is_linked == 'on' ) echo '</a>'; ?>
						
						<!-- Popup -->
						<?php $_popup_enabled = true; ?>
						<?php include Template_Loader::locate_template( 'popups/gs-team-layout-popup.php' ); ?>
			        
			        </div>
				</div>

			</div>

		<?php endwhile; ?>

		<?php do_action( 'gs_team_after_team_members' );
		if ( $_drawer_enabled ) echo '</div>'; ?>

		<?php else: ?>

			<!-- Members not found - Load no-team-member template -->
			<?php include Template_Loader::locate_template( 'partials/gs-team-layout-no-team-member.php' ); ?>

		<?php endif; ?>


	<!-- Drawer Contents -->
	<?php include Template_Loader::locate_template( 'drawers/gs-team-layout-drawer.php' ); ?>

	</div>

	<!-- Pagination -->
	<?php if ( 'on' == $gs_member_pagination ) : ?>
		<?php include Template_Loader::locate_template( 'partials/gs-team-layout-pagination.php' ); ?>
	<?php endif; ?>

	<!-- Panel -->
	<?php include Template_Loader::locate_template( 'panels/gs-team-layout-panel.php' ); ?>

</div>