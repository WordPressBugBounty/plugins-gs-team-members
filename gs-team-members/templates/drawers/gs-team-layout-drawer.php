<?php
namespace GSTEAM;

if ( ! $_drawer_enabled ) return;

if ( $gs_team_loop->have_posts() ) : while ( $gs_team_loop->have_posts() ): $gs_team_loop->the_post();

    ?>
    
    <div id="gs-team-drawer-<?php echo get_the_id(); ?>-<?php echo $id; ?>" class="gridder-content">

    <?php

    if ( ! gtm_fs()->is_paying_or_trial() ) {
        include Template_Loader::locate_template( 'drawers/gs-team-drawer-default.php' );
        return;
    }
    
    switch ( $drawer_style ) {
        
        case 'style-one': {
            include Template_Loader::locate_template( 'pro/drawers/gs-team-drawer-style-one.php' );
            break;
        }
        case 'style-two': {
            include Template_Loader::locate_template( 'pro/drawers/gs-team-drawer-style-two.php' );
            break;
        }
        case 'style-three': {
            include Template_Loader::locate_template( 'pro/drawers/gs-team-drawer-style-three.php' );
            break;
        }
        case 'style-four': {
            include Template_Loader::locate_template( 'pro/drawers/gs-team-drawer-style-four.php' );
            break;
        }
        case 'style-five': {
            include Template_Loader::locate_template( 'pro/drawers/gs-team-drawer-style-five.php' );
            break;
        }
        default: {
            include Template_Loader::locate_template( 'drawers/gs-team-drawer-default.php' );
        }
    }

    ?>

    </div>

    <?php

endwhile; endif; ?>