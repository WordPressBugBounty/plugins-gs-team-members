<?php
namespace GSTEAM;
/**
 * GS Team - Layout Category Filter
 * @author GS Plugins <hello@gsplugins.com>
 * 
 * This template can be overridden by copying it to yourtheme/gs-team/partials/gs-team-layout-cat-filters.php
 * 
 * @package GS_Team/Templates
 * @version 1.0.3
 */

if ( plugin()->builder->get_tax_option('enable_group_tax') !== 'on' ) return;

do_action( 'gs_team_before_cats_filters' );

$_group = (array) string_to_array($group);
$_exclude_group = [];

$terms = get_terms([
    'taxonomy'  => 'gs_team_group',
    'orderby'   => $group_orderby,
    'order'     => $group_order,
    'hide_empty' => $hide_empty,
    'ignore_term_order' => true,
]);

if ( !empty($_group) ) {
    $term_ids = wp_list_pluck( $terms, 'term_id' );
    $_exclude_group = array_diff( $term_ids, $_group );
} else {
    $_exclude_group = (array) string_to_array($exclude_group);
}

$_terms = [];
terms_hierarchically( $terms, $_terms, 0, $_exclude_group ); // it will override $_terms variable.

$classes = 'gs-team-filter-cats gs-team-filter-theme--' . $filter_style;

$with_child_cats = $enable_child_cats == 'on';

if ( $with_child_cats ) $classes .= ' gs-filter--with-child';

if ( empty($_terms) || count($_terms) < 2 ) return;

?>

<ul class="<?php echo esc_attr($classes); ?>" style="text-align: <?php echo esc_attr($gs_tm_filter_cat_pos); ?>">
    
    <?php if ( $gs_filter_all_enabled == 'on' ) : ?>
        <li class="filter"><a href="javascript:void(0)" data-filter="*"><?php echo esc_html($fitler_all_text); ?></a></li>
    <?php endif; ?>

    <?php foreach ( $_terms as $term ) :
        
        $has_child = !empty( $term->children );

        ?>

        <li class="filter <?php echo $has_child ? 'has-child' : ''; ?>">
            <a href="javascript:void(0)" data-filter=".<?php echo esc_attr($term->slug); ?>">
                <span><?php echo esc_html($term->name); ?></span>
                <?php if ( $has_child && 'on' === $enable_child_cats ) : ?>
                    <span class="sub-arrow fa fa-angle-down"></span>
                <?php endif; ?>
            </a>
            <?php if ( $with_child_cats ) term_walker( $term ); ?>
        </li>

    <?php endforeach; ?>
</ul>