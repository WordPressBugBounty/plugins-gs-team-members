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

remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

get_header();

$display_ribbon = 'on';

$gs_team_follow_me_on = get_translation( 'gs_team_follow_me_on' );

include Template_Loader::locate_template( 'partials/gs-team-layout-single.php' );

get_footer();