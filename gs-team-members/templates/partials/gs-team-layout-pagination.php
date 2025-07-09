<?php

namespace GSTEAM;
/**
 * GS Team - Layout Pagination
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-pagination.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */

if( ! is_display_pagination( $carousel_enabled, $filter_enabled, $gs_team_filter_type ) ) return;

do_action( 'gs_team_before_pagination' );

if( 'on' === $filter_enabled && 'normal-pagination' === $pagination_type ) {
    $pagination_type = 'ajax-pagination';
}

?>


<div id="gs-team-pagination-wrapper-<?php echo esc_attr( $id ); ?>">

    <?php if ( 'normal-pagination' === $pagination_type ) : ?>

        <?php echo get_pagination( $id, $team_per_page ); ?>

    <?php elseif ( 'ajax-pagination' === $pagination_type ) : ?>

        <div id="gs-team-ajax-pagination-<?php echo esc_attr( $id ); ?>" data-posts-per-page="<?php echo esc_attr( $team_per_page ); ?>">
            <?php echo get_ajax_pagination( $id, $team_per_page, 1 ); ?>
        </div>
        
    <?php elseif ( 'load-more-button' === $pagination_type ) : ?>

        <div id="gs-team-load-more-button-<?php echo esc_attr( $id ); ?>" class="gs-team-load-more-wrapper">
            <button id="gs-team-load-more-member-btn" class="gs-team-load-more-btn"><?php echo esc_html( $load_button_text ); ?></button>
        </div>

    <?php elseif ( 'load-more-scroll' === $pagination_type ) : ?>

        <div id="gs-team-load-more-scroll-<?php echo esc_attr( $id ); ?>">
            <div class="gs-team-loader-spinner" style="display: none;"><img src="<?php echo GSTEAM_PLUGIN_URI . '/assets/img/loader.svg'; ?>" alt="Loader Image"></div>
        </div>

    <?php endif; ?>
    
</div>

<?php

do_action( 'gs_team_after_pagination' );