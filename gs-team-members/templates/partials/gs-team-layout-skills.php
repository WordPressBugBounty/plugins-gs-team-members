<?php

namespace GSTEAM;
/**
 * GS Team - Layout Skills
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-skills.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */

$member_id = get_the_id();

$skills = get_skills( $member_id );

$is_skills_title = empty($is_skills_title) ? false : wp_validate_boolean($is_skills_title);
$is_skills_title = apply_filters( 'gs_team_member_is_skills_title', $is_skills_title, $skills, $member_id );
$skills_text = get_translation( 'gs_team_skills' );

if ( !empty($skills) ) : ?>

    <div class="member-skill-wraaper">
        <?php if ( $is_skills_title && !empty($skills_text) ) : ?>
            <h3><?php echo esc_html($skills_text); ?></h3>
        <?php endif; ?>

        <div class="member-skill">
            <?php foreach( $skills as $skill ) : ?>
                
                <?php if ( !empty($skill['percent']) ) : ?>

                    <span class="progressText">
                        <b><?php echo esc_html($skill['skill']); ?></b>
                    </span>

                    <div class="progress" style="--gstm-progress-width: <?php echo esc_attr($skill['percent']); ?>%">
                        <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                        <span class="progress-completed"><?php echo esc_html($skill['percent']); ?>%</span>
                    </div>
                    
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>

    <?php do_action( 'gs_team_after_member_skills' ); ?>

<?php endif; ?>