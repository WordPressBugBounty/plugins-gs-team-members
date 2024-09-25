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

<div class="gs-containeer-f" itemscope="" itemtype="http://schema.org/Person">

    <div class="gs-roow">

        <div class="gs-col-md-4">

            <div class="gs_member_img">
                
                <div class="gs_ribon_wrapper">
                    
                    <!-- Team Image -->
                    <?php member_thumbnail( 'full', true ); ?>
                    <?php do_action( 'gs_team_after_member_thumbnail' ); ?>
        
                    <!-- Ribbon -->
                    <?php include Template_Loader::locate_template( 'partials/gs-team-layout-ribon.php' ); ?>
                    
                </div>
        
            </div>
        
        </div>

        <div class="gs-col-md-8">

            <div class="gs_member_details">
        
                <!-- Member Name -->
                <h2 class="gs-sin-mem-name" itemprop="name"><?php the_title(); ?></h2>
                <?php do_action( 'gs_team_after_member_name' ); ?>
        
                <!-- Member Designation -->
                <div class="gs-sin-mem-desig" itemprop="jobtitle"><?php echo esc_html( $designation ); ?></div>
                <?php do_action( 'gs_team_after_member_designation' ); ?>
        
                <!-- Description -->
                <div class="gs-member-desc <?php echo $gs_desc_scroll_contrl == 'on' ? 'gs-team--scrollbar' : ''; ?>" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
                <?php do_action( 'gs_team_after_member_details' ); ?>
        
                <!-- Social Links -->
                <?php if ( ! empty( get_social_links( get_the_id() ) ) ) : ?>
                    <div class="gs-tm-sicons">
                        <div class="gs-tm-sicons-lable"><?php echo esc_html($gs_team_follow_me_on); ?></div>
                        <?php $gs_member_connect = 'on'; ?>
                        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Meta Details -->
                <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details-2.php' ); ?>
                
            </div>

        </div>

    </div> <!-- End of row -->

</div>