<?php

namespace GSTEAM;
/**
 * GS Team - Layout No Member
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-no-team-member.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */

?>

<div class="gs-col-md-12 gs-team--no-team-found">
    <p><?php _e( 'No team member found', 'gsteam' ); ?></p>
</div>

<?php do_action( 'gs_team_after_no_team_found' ); ?>