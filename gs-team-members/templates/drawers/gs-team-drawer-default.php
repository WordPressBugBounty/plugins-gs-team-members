<?php
namespace GSTEAM;

$designation = get_post_meta( get_the_id(), '_gs_des', true );

plugin()->hooks->load_acf_fields( $show_acf_fields, $acf_fields_position );

$vg = empty( $visibility_group ) ? 'drawer' : $visibility_group;

$name_field = get_member_visibility_field( $vg, 'member_name' );
$role_field = get_member_visibility_field( $vg, 'member_designation' );
$desc_field = get_member_visibility_field( $vg, 'member_details' );

?>

<div class="gs-roow">

    <div class="gs-col-md-6 team-description">

        <?php if ( is_visible( $name_field ) ) : ?>
            <div class="<?php print_visible_classes( $name_field ); ?>">
                <?php member_name( $id, true, false, $gs_member_link_type, 'h2', 'title', true ); ?>
                <?php do_action( 'gs_team_after_member_name' ); ?>
            </div>
        <?php endif; ?>
        
        <?php if ( is_visible( $role_field ) ) : ?>
            <p class="<?php print_visible_classes( $role_field, 'gs-member-desig' ); ?>" itemprop="jobTitle"><?php echo wp_kses_post($designation); ?></p>
            <?php do_action( 'gs_team_after_member_designation' ); ?>
        <?php endif; ?>

        <?php if ( is_visible( $desc_field ) ) : ?>
            <div class="<?php print_visible_classes( $desc_field, 'gs-member-desc' . ( $gs_desc_scroll_contrl == 'on' ? ' gs-team--scrollbar' : '' ) ); ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
            <?php do_action( 'gs_team_after_member_details' ); ?>
        <?php endif; ?>

    </div>

    <div class="gs-col-md-6 gs-tm-sicons">

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>
        
        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>

    </div>

</div>
