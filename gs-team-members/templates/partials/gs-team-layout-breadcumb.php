<?php

namespace GSTEAM;
/**
 * GS Team - Breadcumb Template
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/gs-team-layout-breadcumb.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */

if( 'on' === $is_breadcumb_enabled ):

    // get home text and url
    $home_text = 'Home';
    $home_url = home_url();
    $seperator = ' > ';

    // get post type slug and archive link
    $post_type = get_post_type();
    $post_type_obj = get_post_type_object( $post_type );
    $post_type_slug = ucfirst($post_type_obj->rewrite['slug']);
    $post_type_archive_link = get_post_type_archive_link( $post_type );

    // get current page title
    $current_page = get_the_title();

?>


<div class="gs-breadcumb-container">
    <div class="gs-roow">
        <div class="col-md-12">
            <ul class="gs-team-breadcumb-list">
                <li><a href="<?php echo esc_url( $home_url ); ?>"><?php echo esc_html($home_text); ?></a><?php echo $seperator; ?></li>
                <li><a href="<?php echo esc_url( $post_type_archive_link ); ?>"><?php echo esc_html( $post_type_slug ); ?></a><?php echo esc_html( $seperator ); ?></li>
                <li><?php echo esc_html( $current_page ); ?></li>
            </ul>
        </div>
    </div>
</div>

<?php endif; ?>