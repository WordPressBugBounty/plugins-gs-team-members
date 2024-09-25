<?php

namespace GSTEAM;
/**
 * GS Team - Layout Filters
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-filters.php
 * 
 * @package GS_Team/Templates
 * @version 1.1.0
 */

do_action( 'gs_team_before_filters' );

$filter_col_class = $gs_team_filter_columns == 'three' ? 'gs-col-md-4 gs-col-sm-6' : 'gs-col-md-6 gs-col-sm-6';
$filter_col_class .= ' gs-col-xs-12';

$filters_order = array_keys( Sortable::get_team_filters() );

ob_start();

foreach ( $filters_order as $filter_order ) : ?>

    <?php if ( $filter_order == 'search_by_name' && 'on' ==  $gs_member_srch_by_name ) : ?>
        <?php do_action( 'gs_team_before_search_filter' ); ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <input type="text" class="search-by-name" placeholder="<?php echo esc_attr( $gs_teamfliter_name ); ?>" />
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'search_by_company' && 'on' ==  $gs_member_srch_by_company ) : ?>
        <?php do_action( 'gs_team_before_company_search_filter' ); ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <input type="text" class="search-by-company" placeholder="<?php echo esc_attr( $gs_teamfliter_company ); ?>" />
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'search_by_zip' && 'on' ==  $gs_member_srch_by_zip ) : ?>
        <?php do_action( 'gs_team_before_zip_search_filter' ); ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <input type="text" class="search-by-zip" placeholder="<?php echo esc_attr( $gs_teamfliter_zip ); ?>" />
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_tag' && plugin()->builder->get_tax_option('enable_tag_tax') == 'on' && $gs_member_srch_by_tag == 'on' ) : ?>
        <?php do_action( 'gs_team_before_tag_search_filter' ); ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <input type="text" class="search-by-tag" placeholder="<?php echo esc_attr( $gs_teamfliter_tag ); ?>" />
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'filter_by_designation' && 'on' == $gs_member_filter_by_desig ) : ?>
        <?php do_action( 'gs_team_before_designation_filter' ); ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-designation">
                <option value="*"><?php echo esc_html($gs_teamfliter_designation); ?></option>
                <?php get_meta_values_options( '_gs_des', [ 'post_ids' => wp_list_pluck( $gs_team_loop->posts, 'ID' ) ] ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_language' && plugin()->builder->get_tax_option('enable_language_tax') == 'on' && $gs_member_filter_by_language == 'on' ) : ?>
        <?php
            $gs_team_language_meta = plugin()->builder->get_tax_option( 'language_tax_label' );
            $language_terms = get_terms_for_filter( 'gs_team_language', $hide_empty, $language );
            do_action( 'gs_team_before_language_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-language">
                <option value="*"><?php echo esc_html($gs_team_language_meta); ?></option>
                <?php get_terms_options( $language_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_location' && plugin()->builder->get_tax_option('enable_location_tax') == 'on' && $gs_member_filter_by_location == 'on' ) : ?>
        <?php
            $gs_team_location_meta = plugin()->builder->get_tax_option( 'location_tax_label' );
            $location_terms = get_terms_for_filter( 'gs_team_location', $hide_empty, $location );
            do_action( 'gs_team_before_location_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-location">
                <option value="*"><?php echo esc_html($gs_team_location_meta); ?></option>
                <?php get_terms_options( $location_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_gender' && plugin()->builder->get_tax_option('enable_gender_tax') == 'on' && $gs_member_filter_by_gender == 'on' ) : ?>
        <?php
            $gs_team_gender_meta = plugin()->builder->get_tax_option( 'gender_tax_label' );
            $gender_terms = get_terms_for_filter( 'gs_team_gender', $hide_empty, $gender, 'DESC' );
            do_action( 'gs_team_before_gender_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-gender">
                <option value="*"><?php echo esc_html($gs_team_gender_meta); ?></option>
                <?php get_terms_options( $gender_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_specialty' && plugin()->builder->get_tax_option('enable_specialty_tax') == 'on' && $gs_member_filter_by_speciality == 'on' ) : ?>
        <?php
            $gs_team_specialty_meta = plugin()->builder->get_tax_option( 'specialty_tax_label' );
            $specialty_terms = get_terms_for_filter( 'gs_team_specialty', $hide_empty, $specialty, 'ASC' );
            do_action( 'gs_team_before_speciality_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-specialty">
                <option value="*"><?php echo esc_html($gs_team_specialty_meta); ?></option>
                <?php get_terms_options( $specialty_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_extra_one' && plugin()->builder->get_tax_option('enable_extra_one_tax') == 'on' && $gs_member_filter_by_extra_one == 'on' ) : ?>
        <?php
            $gs_team_extra_one_meta = plugin()->builder->get_tax_option( 'extra_one_tax_label' );
            $extra_one_terms = get_terms_for_filter( 'gs_team_extra_one', $hide_empty, $include_extra_one, 'ASC' );
            do_action( 'gs_team_before_extra_one_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-extra_one">
                <option value="*"><?php echo esc_html($gs_team_extra_one_meta); ?></option>
                <?php get_terms_options( $extra_one_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_extra_two' && plugin()->builder->get_tax_option('enable_extra_two_tax') == 'on' && $gs_member_filter_by_extra_two == 'on' ) : ?>
        <?php
            $gs_team_extra_two_meta = plugin()->builder->get_tax_option( 'extra_two_tax_label' );
            $extra_two_terms = get_terms_for_filter( 'gs_team_extra_two', $hide_empty, $include_extra_two, 'ASC' );
            do_action( 'gs_team_before_extra_two_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-extra_two">
                <option value="*"><?php echo esc_html($gs_team_extra_two_meta); ?></option>
                <?php get_terms_options( $extra_two_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_extra_three' && plugin()->builder->get_tax_option('enable_extra_three_tax') == 'on' && $gs_member_filter_by_extra_three == 'on' ) : ?>
        <?php
            $gs_team_extra_three_meta = plugin()->builder->get_tax_option( 'extra_three_tax_label' );
            $extra_three_terms = get_terms_for_filter( 'gs_team_extra_three', $hide_empty, $include_extra_three, 'ASC' );
            do_action( 'gs_team_before_extra_three_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-extra_three">
                <option value="*"><?php echo esc_html($gs_team_extra_three_meta); ?></option>
                <?php get_terms_options( $extra_three_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_extra_four' && plugin()->builder->get_tax_option('enable_extra_four_tax') == 'on' && $gs_member_filter_by_extra_four == 'on' ) : ?>
        <?php
            $gs_team_extra_four_meta = plugin()->builder->get_tax_option( 'extra_four_tax_label' );
            $extra_four_terms = get_terms_for_filter( 'gs_team_extra_four', $hide_empty, $include_extra_four, 'ASC' );
            do_action( 'gs_team_before_extra_four_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-extra_four">
                <option value="*"><?php echo esc_html($gs_team_extra_four_meta); ?></option>
                <?php get_terms_options( $extra_four_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

    <?php if ( $filter_order == 'gs_team_extra_five' && plugin()->builder->get_tax_option('enable_extra_five_tax') == 'on' && $gs_member_filter_by_extra_five == 'on' ) : ?>
        <?php
            $gs_team_extra_five_meta = plugin()->builder->get_tax_option( 'extra_five_tax_label' );
            $extra_five_terms = get_terms_for_filter( 'gs_team_extra_five', $hide_empty, $include_extra_five, 'ASC' );
            do_action( 'gs_team_before_extra_five_filter' );
        ?>
        <div class="<?php echo esc_attr($filter_col_class); ?> search-fil-nbox">
            <select class="filters-select-extra_five">
                <option value="*"><?php echo esc_html($gs_team_extra_five_meta); ?></option>
                <?php get_terms_options( $extra_five_terms ); ?>
            </select>
        </div>
    <?php continue; endif; ?>

<?php endforeach; ?>

<?php $filters_html = ob_get_clean();

if ( !empty(trim($filters_html)) ) : ?>
    <div class="search-filter"><div class="gs-roow"><?php echo gs_wp_kses( $filters_html ); ?></div></div>
<?php endif; ?>