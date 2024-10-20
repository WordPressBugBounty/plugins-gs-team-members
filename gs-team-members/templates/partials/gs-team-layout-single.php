<?php

namespace GSTEAM;

$single_page_style = getoption( 'single_page_style', 'default' );

$single_page_style = apply_filters( 'gs_team_single_page_style', $single_page_style );

if ( ! gtm_fs()->is_paying_or_trial() ) {
	$single_page_style = 'default';
}

?>

<div class="gs-containeer gs-single-container <?php echo 'gs-single-' . esc_attr($single_page_style); ?>">
	
	<div class="gs_team" id="gs_team_single">

		<?php
		
		while ( have_posts() ) : the_post();

			if ( ! gtm_fs()->is_paying_or_trial() ) {
				include Template_Loader::locate_template( 'singles/gs-team-single-default.php' );
				return;
			}

			switch ( $single_page_style ) {

				case 'style-one': {
					include Template_Loader::locate_template( 'pro/singles/gs-team-single-style-one.php' );
					break;
				}

				case 'style-two': {
					include Template_Loader::locate_template( 'pro/singles/gs-team-single-style-two.php' );
					break;
				}

				case 'style-three': {
					include Template_Loader::locate_template( 'pro/singles/gs-team-single-style-three.php' );
					break;
				}

				case 'style-four': {
					include Template_Loader::locate_template( 'pro/singles/gs-team-single-style-four.php' );
					break;
				}

				case 'style-five': {
					include Template_Loader::locate_template( 'pro/singles/gs-team-single-style-five.php' );
					break;
				}

				default: {
					include Template_Loader::locate_template( 'singles/gs-team-single-default.php' );
				}

			}

		endwhile;

		include Template_Loader::locate_template( 'partials/gs-team-layout-navigation.php' ); ?>

	</div>
    
</div>