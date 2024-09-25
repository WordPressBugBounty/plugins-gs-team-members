<?php

namespace GSTEAM;
/**
 * GS Team - Archive Template
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/gs-team-layout-archive.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.1
 */

remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

if ( empty($gs_member_thumbnail_sizes) ) {
	$gs_member_thumbnail_sizes = 'large';
}

$display_ribbon = 'on';

get_header(); ?>

<div class="gs-containeer gs-archive-container">
	
	<h1 class="arc-title"><?php the_archive_title(); ?></h1>

	<div class="gs-roow clearfix gs_team" id="gs_team_archive">

		<?php while ( have_posts() ) : the_post();
		
		$designation = get_post_meta( get_the_id(), '_gs_des', true );
		
		?>
		
		<div class="gs-col-lg-3 gs-col-md-4 gs-col-sm-6 gs-col-xs-12">
			<div itemscope="" itemtype="http://schema.org/Person"> <!-- Start sehema -->
			
				<!-- Team Image -->
				<div class="gs-arc-mem-img gs_ribon_wrapper">
					
					<a href="<?php the_permalink(); ?>">
						<?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
					</a>

					<!-- Ribbon -->
					<?php include Template_Loader::locate_template( 'partials/gs-team-layout-ribon.php' ); ?>

				</div>
				<?php do_action( 'gs_team_after_member_thumbnail' ); ?>


				<div class="gs_member_details gs-tm-sicons">

					<a href="<?php the_permalink(); ?>"><h3 class="gs-arc-mem-name" itemprop="name"><?php the_title(); ?></h3></a>
					<?php do_action( 'gs_team_after_member_name' ); ?>
					
					<div class="gs-arc-mem-desig" itemprop="jobtitle"><?php echo esc_html( $designation ); ?></div>
					<?php do_action( 'gs_team_after_member_designation' ); ?>

					<!-- Social Links -->
					<?php $gs_member_connect = 'on'; ?>
					<?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

				</div>
			
			</div> <!-- end sehema -->
		</div> <!-- end col -->
	
		<?php endwhile; ?>

	</div>
	
</div>

<?php get_footer(); ?>