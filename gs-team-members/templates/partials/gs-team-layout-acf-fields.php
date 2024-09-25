<?php

namespace GSTEAM;
/**
 * GS Team - Layout ACF Fields
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-acf-fields.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.2
 */

if ( ! function_exists('acf_get_field_groups') ) return;

$field_groups = acf_get_field_groups([
    'post_id'	=> get_the_ID(),
    'post_type'	=> get_post_type()
]);

if ( empty($field_groups) ) return;

foreach( $field_groups as $field_group ) {

    $title = $field_group['title'];

    $fields = acf_get_fields($field_group);

    if ( empty($fields) ) continue;

    // If all fields are empty vail early
    $empty = true;
    foreach ( $fields as $field ) if ( !empty( get_field( $field['name'] ) ) ) $empty = false;
    if ( $empty ) continue;

    ?>

    <div class="gs-team--acf_group">

        <?php if ( !empty($title) ) printf( '<h3 class="gs-team--acf_group-title">%s</h3>', esc_html($title) ); ?>
        
        <div class="gs-team--acf_group-fields gstm-details">
            
            <?php foreach ($fields as $field) :
                
                // If the current field is empty vail early
                if ( empty( get_field( $field['name'] ) ) ) continue; ?>
                
                <div class="gs-member-<?php echo esc_attr($field['name']); ?>">
                    <span class="levels"><?php echo esc_html($field['label']); ?></span>
                    <span class="level-info-company"><?php the_field($field['name']); ?></span>
                </div>

            <?php endforeach; ?>

        </div>

    </div>

    <?php
    
}