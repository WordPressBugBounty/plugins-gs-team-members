<?php
namespace GSTEAM;

$vg = empty( $visibility_group ) ? 'popup' : $visibility_group;

$role_field = get_member_visibility_field( $vg, 'member_designation' );
$desc_field = get_member_visibility_field( $vg, 'member_details' );

if ( $gs_teammembers_pop_clm == 'one' ) : ?>

    <div class="gs_team_popup_details gs-tm-sicons popup-one-column">
        
        <!-- Team Image -->
        <div class="clearfix">
            <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
        </div>
        <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>

        <!-- Member Name -->
        <?php member_name( $id, true, $gs_member_name_is_linked == 'on' ); ?>
        <?php do_action( 'gs_team_after_member_name' ); ?>

        <!-- Member Designation -->
        <?php if ( ! empty( $designation ) && is_visible( $role_field ) ): ?>
            <div class="<?php print_visible_classes( $role_field, 'gs-member-desig' ); ?>" itemprop="jobTitle"><?php echo wp_kses_post($designation); ?></div>
            <?php do_action( 'gs_team_after_member_designation' ); ?>
        <?php endif; ?>

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

        <!-- Description -->
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
    
        <!-- Team Image -->
        <div class="gs_team_popup_img">
            <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
            <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>
        </div>

    </div>

    <div class="gs_team_popup_details gs-tm-sicons">
        
        <div class="name-designation-icon">
            <div class="name-designation">
                <!-- Single member name -->
                <?php member_name( $id, true, $gs_member_name_is_linked == 'on' ); ?>
                <?php do_action( 'gs_team_after_member_name' ); ?>

                <!-- Single member designation -->
                <?php if ( ! empty( $designation ) && is_visible( $role_field ) ): ?>
                    <div class="<?php print_visible_classes( $role_field, 'gs-member-desig' ); ?>" itemprop="jobTitle"><?php echo wp_kses_post($designation); ?></div>
                    <?php do_action( 'gs_team_after_member_designation' ); ?>
                <?php endif; ?>
            </div>

            <div class="item-icon">
                 <!-- Team Flip Image -->
                <div class="gs_team_img__wrapper">
                    <?php member_custom(); ?>
                </div>
                <?php do_action( 'gs_team_after_member_secondary_thumbnail' ); ?>
            </div>
            
        </div>
        
        <!-- Description -->
        <?php if ( is_visible( $desc_field ) ) : ?>
            <div class="<?php print_visible_classes( $desc_field, 'gs-member-desc' . ( $gs_desc_scroll_contrl == 'on' ? ' gs-team--scrollbar' : '' ) ); ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
            <?php do_action( 'gs_team_after_member_details' ); ?>
        <?php endif; ?>
        
        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

    </div>

<?php endif; ?>
