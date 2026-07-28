<?php

namespace GSTEAM;
/**
 * GS Team - Layout Social Links
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-social-links.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */

$social_links = get_social_links( get_the_id() );

$visibility_group = empty( $visibility_group ) ? 'initial' : $visibility_group;
$visibility_field = get_member_visibility_field( $visibility_group, 'member_social' );

$can_show_social = is_visible( $visibility_field ) && ! empty( $social_links );

if ( $visibility_group === 'initial' ) {
	$can_show_social = $can_show_social && ( 'on' == $gs_member_connect );
}

if ( $can_show_social ) : ?>

    <ul class="<?php print_visible_classes( $visibility_field, 'gs-team-social' ); ?>">

    <?php foreach ( $social_links as $social ) :

        $linkclass = str_replace( ['fa-', 'fab', 'fas', 'far'], '', $social['icon'] );
        $linkclass = trim($linkclass);
        
        if ( str_contains( $social['icon'], 'envelope' ) ) {
            $link = !empty($social['link']) ? 'mailto:' . $social['link'] : '#';
        } else {
            $link = !empty($social['link']) ? $social['link'] : '#';
        } ?>

        <li>
            <?php printf( '<a class="%s" href="%s" target="_blank" itemprop="sameAs"><i class="%s"></i></a>', esc_attr($linkclass), esc_url($link), esc_attr($social['icon']) ); ?>
        </li>

    <?php endforeach; ?>
        
    </ul>

    <?php do_action( 'gs_team_after_member_social_links' ); ?>

<?php endif; ?>
