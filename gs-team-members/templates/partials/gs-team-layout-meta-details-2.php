<?php

namespace GSTEAM;
/**
 * GS Team - Layout Popup Details
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/gs-team-layout-popup-details.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.0
 */


$gs_teamcom_meta 		= get_translation( 'gs_teamcom_meta' );
$gs_teamadd_meta 		= get_translation( 'gs_teamadd_meta' );
$gs_teamlandphone_meta 	= get_translation( 'gs_teamlandphone_meta' );
$gs_teamcellPhone_meta 	= get_translation( 'gs_teamcellPhone_meta' );
$gs_teamemail_meta 		= get_translation( 'gs_teamemail_meta' );
$gs_team_zipcode_meta 	= get_translation( 'gs_team_zipcode_meta' );

$gs_team_location_label     = plugin()->builder->get_tax_option( 'location_tax_label' );
$gs_team_language_label     = plugin()->builder->get_tax_option( 'language_tax_label' );
$gs_team_specialty_label    = plugin()->builder->get_tax_option( 'specialty_tax_label' );
$gs_team_gender_label       = plugin()->builder->get_tax_option( 'gender_tax_label' );

$gs_team_extra_one_label   = plugin()->builder->get_tax_option( 'extra_one_tax_label' );
$gs_team_extra_two_label   = plugin()->builder->get_tax_option( 'extra_two_tax_label' );
$gs_team_extra_three_label = plugin()->builder->get_tax_option( 'extra_three_tax_label' );
$gs_team_extra_four_label  = plugin()->builder->get_tax_option( 'extra_four_tax_label' );
$gs_team_extra_five_label  = plugin()->builder->get_tax_option( 'extra_five_tax_label' );

$address            = get_post_meta( get_the_id(), '_gs_address', true );
$email              = get_post_meta( get_the_id(), '_gs_email', true );
$land               = get_post_meta( get_the_id(), '_gs_land', true );
$cell               = get_post_meta( get_the_id(), '_gs_cell', true );
$company            = get_post_meta( get_the_id(), '_gs_com', true );
$company_website    = get_post_meta( get_the_id(), '_gs_com_website', true );
$gs_zip_code        = gtm_fs()->is_paying_or_trial() ? get_post_meta( get_the_id(), '_gs_zip_code', true ) : '';
$location           = gtm_fs()->is_paying_or_trial() ? gs_team_member_location() : '';
$language           = gtm_fs()->is_paying_or_trial() ? gs_team_member_language() : '';
$specialty          = gtm_fs()->is_paying_or_trial() ? gs_team_member_specialty() : '';
$gender             = gtm_fs()->is_paying_or_trial() ? gs_team_member_gender() : '';
$extra_one          = gtm_fs()->is_paying_or_trial() ? gs_team_member_extra_one() : '';
$extra_two          = gtm_fs()->is_paying_or_trial() ? gs_team_member_extra_two() : '';
$extra_three        = gtm_fs()->is_paying_or_trial() ? gs_team_member_extra_three() : '';
$extra_four         = gtm_fs()->is_paying_or_trial() ? gs_team_member_extra_four() : '';
$extra_five         = gtm_fs()->is_paying_or_trial() ? gs_team_member_extra_five() : '';

?>

<div class="gstm-details">
    
    <?php if ( !empty($company) || !empty($company_website) ) : ?>
        <div class="gs-member-company">

            <i class="fas fa-users" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_teamcom_meta); ?></span>
            <span class="level-info-company">
                <?php if ( empty($company_website) ) :
                    echo esc_html($company);
                elseif ( empty($company) ) :
                    printf( '<a href="%s" target="_blank" rel="nofollow noopener">%s</a>', esc_url_raw( $company_website ), esc_html($company_website) );
                else :
                    printf( '<a href="%s" target="_blank" rel="nofollow noopener">%s</a>', esc_url_raw( $company_website ), esc_html($company) );
                endif; ?>
            </span>

        </div>
    <?php endif; ?>

    <?php if ( !empty($address) ) : ?>
        <div class="gs-member-address">
            <i class="fas fa-book" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_teamadd_meta); ?></span>
            <span class="level-info-address"><?php echo wp_kses_post( $address ); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty($land) ) : ?>
        <div class="gs-member-lphon">
            <i class="fas fa-phone-square" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_teamlandphone_meta); ?></span>
            <span class="level-info-lphon">
                <?php
                $land_phone_link = getoption( 'land_phone_link', 'off' );
                if ( $land_phone_link == 'on' ) {
                    printf( '<a href="callto:%1$s">%1$s</a>', esc_html($land) );
                } else {
                    echo esc_html($land);
                }
                ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if ( !empty($cell) ) : ?>
        <div class="gs-member-cphon">
            <i class="fas fa-phone"></i>
            <span class="levels"><?php echo esc_html($gs_teamcellPhone_meta); ?></span>
            <span class="level-info-cphon">
                <?php
                $cell_phone_link = getoption( 'cell_phone_link', 'off' );
                if ( $cell_phone_link == 'on' ) {
                    printf( '<a href="callto:%1$s">%1$s</a>', esc_html($cell) );
                } else {
                    echo esc_html($cell);
                }
                ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if ( !empty($email) ) : ?>
        <div class="gs-member-email">
            <i class="fas fa-envelope" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_teamemail_meta); ?></span>
            <span class="level-info-email">
                <?php
                $email_link = getoption( 'email_link', 'off' );
                if ( $email_link == 'on' ) {
                    member_email_mailto('', true);
                } else {
                    echo sanitize_email($email);
                }
                ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $location )) : ?>
        <div class="gs-member-loc">
            <i class="fas fa-map-marker" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_location_label); ?></span>
            <span class="level-info-loc"><?php echo esc_html($location); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $language ) ) : ?>
        <div class="gs-member-lang">
            <i class="fas fa-language" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_language_label); ?></span>
            <span class="level-info-lang"><?php echo esc_html($language); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $specialty ) ) : ?>
        <div class="gs-member-specialty">
            <i class="fas fa-plus-square" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_specialty_label); ?></span>
            <span class="level-info-specialty"><?php echo esc_html($specialty); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $gender ) ) : ?>
        <div class="gs-member-gender">
            <i class="fas fa-user" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_gender_label); ?></span>
            <span class="level-info-gender"><?php echo esc_html($gender); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $extra_one ) ) : ?>
        <div class="gs-member-extra_one">
            <i class="fas fa-tag" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_extra_one_label); ?></span>
            <span class="level-info-extra_one"><?php echo esc_html($extra_one); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $extra_two ) ) : ?>
        <div class="gs-member-extra_two">
            <i class="fas fa-tag" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_extra_two_label); ?></span>
            <span class="level-info-extra_two"><?php echo esc_html($extra_two); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $extra_three ) ) : ?>
        <div class="gs-member-extra_three">
            <i class="fas fa-tag" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_extra_three_label); ?></span>
            <span class="level-info-extra_three"><?php echo esc_html($extra_three); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $extra_four ) ) : ?>
        <div class="gs-member-extra_four">
            <i class="fas fa-tag" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_extra_four_label); ?></span>
            <span class="level-info-extra_four"><?php echo esc_html($extra_four); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $extra_five ) ) : ?>
        <div class="gs-member-extra_five">
            <i class="fas fa-tag" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_extra_five_label); ?></span>
            <span class="level-info-extra_five"><?php echo esc_html($extra_five); ?></span>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $gs_zip_code ) ) : ?>
        <div class="gs-member-zipcode">
            <i class="fas fa-map-marker" aria-hidden="true"></i>
            <span class="levels"><?php echo esc_html($gs_team_zipcode_meta); ?></span>
            <span class="level-info-zipcode"><?php echo esc_html($gs_zip_code); ?></span>
        </div>
    <?php endif; ?>
    
</div>

<?php do_action( 'gs_team_after_member_meta_details' ); ?>