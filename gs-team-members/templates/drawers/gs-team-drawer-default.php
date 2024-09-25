<?php
namespace GSTEAM;

$designation = get_post_meta( get_the_id(), '_gs_des', true );

plugin()->hooks->load_acf_fields( $show_acf_fields, $acf_fields_position );

?>

<div class="gs-roow">

    <div class="gs-col-md-6 team-description">

        <!-- Single member name -->
        <?php member_name( $id, true, false, $gs_member_link_type, 'h2', 'title', true ); ?>
        <?php do_action( 'gs_team_after_member_name' ); ?>
        
        <!-- Single member designation -->
        <p class="gs-member-desig" itemprop="jobtitle"><?php echo wp_kses_post($designation); ?></p>
        <?php do_action( 'gs_team_after_member_designation' ); ?>

        <!-- Description -->
        <div class="gs-member-desc <?php echo $gs_desc_scroll_contrl == 'on' ? 'gs-team--scrollbar' : ''; ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
        <?php do_action( 'gs_team_after_member_details' ); ?>

    </div>

    <div class="gs-col-md-6 gs-tm-sicons">

        <!-- Social Links -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>
        
        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>

    </div>

</div>