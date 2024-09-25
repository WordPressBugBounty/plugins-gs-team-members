<?php
namespace GSTEAM;
if ( $gs_teammembers_pop_clm == 'one' ) : ?>

    <div class="gs_team_popup_details gs-tm-sicons popup-one-column">
        
        <!-- Team Image -->
        <div class="clearfix">
            <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
        </div>
        <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>

        <!-- Member Name -->
        <?php member_name( $id, true, false, 'single_page', 'h2' ); ?>
        <?php do_action( 'gs_team_after_member_name' ); ?>

        <!-- Member Designation -->
        <?php if ( !empty( $designation ) ): ?>
            <div class="gs-member-desig" itemprop="jobtitle"><?php echo wp_kses_post($designation); ?></div>
            <?php do_action( 'gs_team_after_member_designation' ); ?>
        <?php endif; ?>

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

        <!-- Description -->
        <div class="gs-member-desc <?php echo $gs_desc_scroll_contrl == 'on' ? 'gs-team--scrollbar' : ''; ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
        <?php do_action( 'gs_team_after_member_details' ); ?>
        
        <!-- Meta Details -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details.php' ); ?>

        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>

    </div>

<?php else: ?>

    <div class="gs_team_popup_left__wrapper">
    
        <!-- Team Image -->
        <div class="gs_team_popup_img">
            <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
            <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>
        </div>

        <!-- Meta Details -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details.php' ); ?>

    </div>

    <div class="gs_team_popup_details gs-tm-sicons">
        
        <!-- Single member name -->
        <?php member_name( $id, true, false, 'single_page', 'h2' ); ?>
        <?php do_action( 'gs_team_after_member_name' ); ?>

        <!-- Single member designation -->
        <?php if ( !empty( $designation ) ): ?>
            <div class="gs-member-desig" itemprop="jobtitle"><?php echo wp_kses_post($designation); ?></div>
            <?php do_action( 'gs_team_after_member_designation' ); ?>
        <?php endif; ?>

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

        <!-- Description -->
        <div class="gs-member-desc <?php echo $gs_desc_scroll_contrl == 'on' ? 'gs-team--scrollbar' : ''; ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
        <?php do_action( 'gs_team_after_member_details' ); ?>

        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>

    </div>

<?php endif; ?>