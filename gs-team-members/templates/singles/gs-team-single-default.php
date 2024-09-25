<?php
namespace GSTEAM;
/**
 * GS Team - Single Template 
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/gs-team-layout-single.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.2
 */

$designation = get_post_meta( get_the_id(), '_gs_des', true );

?>

<div class="gs-team-single-content" itemscope="" itemtype="http://schema.org/Person">

    <div class="gs_member_img">
        
        <div class="gs_ribon_wrapper">
            
            <!-- Team Image -->
            <?php member_thumbnail( 'full', true ); ?>
            <?php do_action( 'gs_team_after_member_thumbnail' ); ?>

            <!-- Ribbon -->
            <?php include Template_Loader::locate_template( 'partials/gs-team-layout-ribon.php' ); ?>
            
        </div>

        <!-- Meta Details -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details.php' ); ?>

    </div>

    <div class="gs_member_details gs-tm-sicons">

        <!-- Member Name -->
        <h1 class="gs-sin-mem-name" itemprop="name"><?php the_title(); ?></h1>
        <?php do_action( 'gs_team_after_member_name' ); ?>

        <!-- Member Designation -->
        <div class="gs-sin-mem-desig" itemprop="jobtitle"><?php echo esc_html( $designation ); ?></div>
        <?php do_action( 'gs_team_after_member_designation' ); ?>

        <!-- Social Links -->
        <?php $gs_member_connect = 'on'; ?>
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>

        <!-- Description -->
        <div class="gs-member-desc" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
        <?php do_action( 'gs_team_after_member_details' ); ?>
        
        <!-- Skills -->
        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>
        
    </div>

</div>