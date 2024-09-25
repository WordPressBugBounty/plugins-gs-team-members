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

do_action( 'gs_team_before_pagination' );

pagination();

do_action( 'gs_team_after_pagination' );