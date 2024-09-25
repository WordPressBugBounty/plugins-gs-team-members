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

    <div class="gs-containeer-f">

        <div class="gs-roow">
        
            <div class="gs-col-md-4 gstm-popup-left">
        
                <!-- Team Image -->
                <div class="gs_team_image__wrapper">
                    <?php member_thumbnail( $gs_member_thumbnail_sizes, true ); ?>
                    <?php do_action( 'gs_team_after_member_thumbnail_popup' ); ?>
                </div>
        
            </div>
        
            <div class="gs-col-md-8 gstm-popup-right">
        
                <div class="gstm-popup-right-top <?php echo $gs_desc_scroll_contrl == 'on' ? 'gs-team--scrollbar' : ''; ?>">

                    <div class="gs-roow">

                        <div class="gs-col-md-8">

                            <!-- Single member name -->
                            <h2 class="gs-sin-mem-name" itemprop="name"><?php the_title(); ?></h2>
                            <?php do_action( 'gs_team_after_member_name' ); ?>
                            
                            <!-- Single member designation -->
                            <p class="gs-member-desig" itemprop="jobtitle"><?php echo wp_kses_post($designation); ?></p>
                            <?php do_action( 'gs_team_after_member_designation' ); ?>

                        </div>

                        <div class="gs-col-md-4">

                            <div class="popup-navigation">
                            
                                <a href="javascript:void(0)" class="popup-nav prev">
                                    <svg xmlns="http://www.w3.org/2000/svg"xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="20px"><path fill-rule="evenodd" fill="#c1c1c7" d="M11.414,18.485 L9.999,19.899 L0.100,9.999 L1.514,8.585 L1.514,8.585 L9.999,0.100 L11.414,1.514 L2.928,9.999 L11.414,18.485 Z"/></svg>
                                </a>

                                <a href="javascript:void(0)" class="popup-nav next">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="20px"><path fill-rule="evenodd" fill="#c1c1c7" d="M11.899,9.999 L1.999,19.899 L0.585,18.485 L9.70,9.999 L0.585,1.514 L1.999,0.100 L10.485,8.585 L10.485,8.585 L11.899,9.999 Z"/><path fill="url(#PSgrad_0)" d="M11.899,9.999 L1.999,19.899 L0.585,18.485 L9.70,9.999 L0.585,1.514 L1.999,0.100 L10.485,8.585 L10.485,8.585 L11.899,9.999 Z"/></svg>
                                </a>

                            </div>

                        </div>

                    </div>
    
            
                    <!-- Description -->
                    <div class="gs-member-desc" itemprop="description"><?php echo wpautop( do_shortcode( get_the_content() ) ); ?></div>
                    <?php do_action( 'gs_team_after_member_details' ); ?>
        
                </div>
        
                <div class="gs-roow">

                    <div class="gs-col-md-6 gs-col-xs-12 gstm-dsf-bottom-left">
        
                        <!-- Meta Details -->
                        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-meta-details-2.php' ); ?>
        
                        <!-- Social Links -->
                        <?php if ( ! empty( get_social_links( get_the_id() ) ) ) : ?>
                            <div class="gs-tm-sicons">
                                <div class="gs-tm-sicons-lable"><?php echo esc_html($gs_team_follow_me_on); ?></div>
                                <?php $gs_member_connect = 'on'; ?>
                                <?php include Template_Loader::locate_template( 'partials/gs-team-layout-social-links.php' ); ?>
                            </div>
                        <?php endif; ?>
        
                    </div>
    
                    <div class="gs-col-md-6 gs-col-xs-12 gstm-dsf-bottom-right">
        
                        <!-- Skills -->
                        <?php $is_skills_title = true; ?>
                        <?php include Template_Loader::locate_template( 'partials/gs-team-layout-skills.php' ); ?>
        
                    </div>
                    
                </div>
        
            </div>
        
        </div>

    </div>
