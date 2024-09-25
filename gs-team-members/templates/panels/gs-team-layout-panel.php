<?php
namespace GSTEAM;
if ( ! $_panel_enabled ) return;

if ( $gs_team_loop->have_posts() ) : ?>

    <!-- Panel -->
    <div class="gs-team-panel-container <?php echo 'gs-team-panel--' . esc_attr($panel_style); ?>">

        <?php
        
        plugin()->hooks->load_acf_fields( $show_acf_fields, $acf_fields_position );

        while ( $gs_team_loop->have_posts() ): $gs_team_loop->the_post();

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                include Template_Loader::locate_template( 'panels/gs-team-panel-default.php' );
                return;
            }

            switch ( $panel_style ) {
                case 'style-one': {
                    include Template_Loader::locate_template( 'pro/panels/gs-team-panel-style-one.php' );
                    break;
                }
                case 'style-two': {
                    include Template_Loader::locate_template( 'pro/panels/gs-team-panel-style-two.php' );
                    break;
                }
                case 'style-three': {
                    include Template_Loader::locate_template( 'pro/panels/gs-team-panel-style-three.php' );
                    break;
                }
                case 'style-four': {
                    include Template_Loader::locate_template( 'pro/panels/gs-team-panel-style-four.php' );
                    break;
                }
                case 'style-five': {
                    include Template_Loader::locate_template( 'pro/panels/gs-team-panel-style-five.php' );
                    break;
                }
                default: {
                    include Template_Loader::locate_template( 'panels/gs-team-panel-default.php' );
                }
            }
        
        endwhile; ?>

        <div id="gstm-overlay"></div>

    </div>
    
<?php endif; ?>