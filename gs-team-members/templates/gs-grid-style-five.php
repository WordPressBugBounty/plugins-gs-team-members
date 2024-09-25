<?php

namespace GSTEAM;
/**
 * GS Team - Layout One
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/gs-team-layout-default-1.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.2
 */

global $gs_team_loop;

$gs_row_classes = ['gs-roow clearfix gs_team'];
$carousel_params = '';

if ( $_carousel_enabled ) {
	$gs_row_classes[] = 'slider owl-carousel owl-theme';
	$carousel_params = get_carousel_data( $gs_team_cols, $gs_team_cols_tablet, $gs_team_cols_mobile_portrait, $gs_team_cols_mobile, false );
	if ( $carousel_navs_enabled ) {
		$gs_row_classes[] = 'carousel-has-navs';
		$gs_row_classes[] = 'carousel-navs--' . $carousel_navs_style;
	}
	if ( $carousel_dots_enabled ) {
		$gs_row_classes[] = 'carousel-has-dots';
		$gs_row_classes[] = 'carousel-dots--' . $carousel_dots_style;
	}
}

if ( $_drawer_enabled ) $gs_row_classes[] = 'gstm-gridder gstm-gridder-' . $drawer_style;
if ( $_filter_enabled ) $gs_row_classes[] = 'gs-all-items-filter-wrapper';

?>

<!-- Container for Team members -->
<div class="gs-containeer cbp-so-scroller">

	<?php if ( $_filter_enabled ) : ?>

		<!-- Cat Filters Template -->
		<?php include Template_Loader::locate_template( 'partials/gs-team-layout-cat-filters.php' ); ?>

		<!-- Filters Template -->
		<?php include Template_Loader::locate_template( 'partials/gs-team-layout-filters.php' ); ?>

	<?php endif; ?>
	
	<div class="<?php echo esc_attr( implode(' ', $gs_row_classes) ); ?>" <?php if ( !empty($carousel_params) ) echo wp_kses_post( $carousel_params ); ?>>
	
		<?php if ( $gs_team_loop->have_posts() ):

			if ( $_drawer_enabled ) echo '<div class="gridder">';

			do_action( 'gs_team_before_team_members' );

			while ( $gs_team_loop->have_posts() ): $gs_team_loop->the_post();

			$designation = get_post_meta( get_the_id(), '_gs_des', true );

			$classes = ['single-member-div', get_col_classes( $gs_team_cols, $gs_team_cols_tablet, $gs_team_cols_mobile_portrait, $gs_team_cols_mobile ) ];

			if ( $gs_member_link_type == 'popup' ) $classes[] = 'single-member-pop';
			if ( $enable_scroll_animation == 'on' ) $classes[] = 'cbp-so-section';
			if ( $_drawer_enabled ) $classes[] = 'gridder-list';
			if ( $_filter_enabled ) {
				$designation = get_post_meta( get_the_id(), '_gs_des', true );
				if ( empty($designation) ) $designation = '';
				
				$classes[] = 'gs-filter-single-item';
				$classes[] = sanitize_title( $designation );
				$classes[] = get_col_classes( $gs_team_cols, $gs_team_cols_tablet, $gs_team_cols_mobile_portrait, $gs_team_cols_mobile );
				$classes[] = get_member_terms_slugs( 'gs_team_group' );
				$classes[] = get_member_terms_slugs( 'gs_team_location' );
				$classes[] = get_member_terms_slugs( 'gs_team_language' );
				$classes[] = get_member_terms_slugs( 'gs_team_gender' );
				$classes[] = get_member_terms_slugs( 'gs_team_specialty' );
				$classes[] = get_member_terms_slugs( 'gs_team_extra_one' );
				$classes[] = get_member_terms_slugs( 'gs_team_extra_two' );
				$classes[] = get_member_terms_slugs( 'gs_team_extra_three' );
				$classes[] = get_member_terms_slugs( 'gs_team_extra_four' );
				$classes[] = get_member_terms_slugs( 'gs_team_extra_five' );
			}
			$single_item_attr = '';
			if ( $_drawer_enabled ) $single_item_attr = sprintf( 'data-griddercontent="#gs-team-drawer-%s-%s"', get_the_ID(), $id );
			
			?>

			<!-- Start single member -->
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" <?php echo wp_kses_post($single_item_attr); ?>>
				
				<!-- Sehema & Single member wrapper -->
				<div class="single-member" itemscope itemtype="http://schema.org/Organization">

					<?php do_action( 'gs_team_before_member_content', $gs_team_theme ); ?>
						
					<!-- Team Image -->
					<div class="gs_team_image__wrapper">

						<!-- Image -->
						<?php echo member_thumbnail_with_link( $id, $gs_member_thumbnail_sizes, $gs_member_name_is_linked == 'on', $gs_member_link_type, $link_preview_image == 'on' ); ?>

						<!-- Overlay -->
						<?php if( $link_preview_image == 'off' ) { ?>
							<div class="gs_team_image__overlay"></div>
						<?php } ?>

						<!-- Ribbon -->
						<?php include Template_Loader::locate_template( 'partials/gs-team-layout-ribon.php' ); ?>

					</div>

					<!-- Member Name -->
					<div class="gs_member_info">

						<!-- Member Name -->
						<?php member_name( $id, true, $gs_member_name_is_linked == 'on', $gs_member_link_type ); ?>
						<?php do_action( 'gs_team_after_member_name' ); ?>

						<!-- Member Designation -->
						<div class="gs-member-desig" itemprop="jobtitle"><?php echo wp_kses_post($designation); ?></div>
						<?php do_action( 'gs_team_after_member_designation' ); ?>

						<!-- Social Links -->
						<div class="single-mem-desc-social">
							<?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>
						</div>
						
					</div>

				</div>

				<!-- Popup -->
				<?php include Template_Loader::locate_template( 'popups/gs-team-layout-popup.php' ); ?>

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

</div>

<!-- Panel -->
<?php include Template_Loader::locate_template( 'panels/gs-team-layout-panel.php' ); ?>