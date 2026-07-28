<?php
namespace GSTEAM;

$vg = empty( $visibility_group ) ? 'popup' : $visibility_group;

$thumb_field = get_member_visibility_field( $vg, 'member_thumbnail' );
$name_field  = get_member_visibility_field( $vg, 'member_name' );
$role_field  = get_member_visibility_field( $vg, 'member_designation' );
$desc_field  = get_member_visibility_field( $vg, 'member_details' );

if ( $gs_teammembers_pop_clm == 'one' ) : ?>

    <div class="gs_team_popup_details gs-tm-sicons popup-one-column">
        
        <?php if ( is_visible( $thumb_field ) ) : ?>
            <!-- Team Image -->
            <div class="<?php print_visible_classes( $thumb_field, 'clearfix' ); ?>">
                <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
            </div>
            <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>
        <?php endif; ?>

        <?php if ( is_visible( $name_field ) ) : ?>
            <div class="<?php print_visible_classes( $name_field ); ?>">
                <?php member_name( $id, true, false, 'single_page', 'h2' ); ?>
                <?php do_action( 'gs_team_after_member_name' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( !empty( $designation ) && is_visible( $role_field ) ): ?>
            <div class="<?php print_visible_classes( $role_field, 'gs-member-desig' ); ?>" itemprop="jobTitle"><?php echo wp_kses_post($designation); ?></div>
            <?php do_action( 'gs_team_after_member_designation' ); ?>
        <?php endif; ?>

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

        <?php if ( is_visible( $desc_field ) ) : ?>
            <div class="<?php print_visible_classes( $desc_field, 'gs-member-desc' . ( $gs_desc_scroll_contrl == 'on' ? ' gs-team--scrollbar' : '' ) ); ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
            <?php do_action( 'gs_team_after_member_details' ); ?>
        <?php endif; ?>
        
        <!-- Meta Details -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details.php' ); ?>

        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>

    </div>

<?php else: ?>

    <div class="gs_team_popup_left__wrapper">
    
        <?php if ( is_visible( $thumb_field ) ) : ?>
            <div class="<?php print_visible_classes( $thumb_field, 'gs_team_popup_img' ); ?>">
                <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
                <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>
            </div>
        <?php endif; ?>

        <!-- Meta Details -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details.php' ); ?>

    </div>

    <div class="gs_team_popup_details gs-tm-sicons">
        
        <?php if ( is_visible( $name_field ) ) : ?>
            <div class="<?php print_visible_classes( $name_field ); ?>">
                <?php member_name( $id, true, false, 'single_page', 'h2' ); ?>
                <?php do_action( 'gs_team_after_member_name' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( !empty( $designation ) && is_visible( $role_field ) ): ?>
            <div class="<?php print_visible_classes( $role_field, 'gs-member-desig' ); ?>" itemprop="jobTitle"><?php echo wp_kses_post($designation); ?></div>
            <?php do_action( 'gs_team_after_member_designation' ); ?>
        <?php endif; ?>

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

        <?php if ( is_visible( $desc_field ) ) : ?>
            <div class="<?php print_visible_classes( $desc_field, 'gs-member-desc' . ( $gs_desc_scroll_contrl == 'on' ? ' gs-team--scrollbar' : '' ) ); ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
            <?php do_action( 'gs_team_after_member_details' ); ?>
        <?php endif; ?>

        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>

    </div>

<?php endif; ?>
