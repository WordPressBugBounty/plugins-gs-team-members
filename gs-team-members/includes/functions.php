<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

function is_divi_active() {
    if (!defined('ET_BUILDER_PLUGIN_ACTIVE') || !ET_BUILDER_PLUGIN_ACTIVE) return false;
    return et_core_is_builder_used_on_current_request();
}

function is_divi_editor() {
    if (!empty($_POST['action']) && $_POST['action'] == 'et_pb_process_computed_property' && !empty($_POST['module_type']) && $_POST['module_type'] == 'gs_team_members') return true;
}

function is_pro_active() {
    return gtm_fs()->can_use_premium_code();
}

function gs_wp_kses($content) {

    $allowed_tags = wp_kses_allowed_html('post');

    $input_common_atts = ['class' => true, 'id' => true, 'style' => true, 'novalidate' => true, 'name' => true, 'width' => true, 'height' => true, 'data' => true, 'title' => true, 'placeholder' => true, 'value' => true];

    $allowed_tags = array_merge_recursive($allowed_tags, [
        'select' => $input_common_atts,
        'input' => array_merge($input_common_atts, ['type' => true, 'checked' => true]),
        'option' => ['class' => true, 'id' => true, 'selected' => true, 'data' => true, 'value' => true]
    ]);

    return wp_kses(stripslashes_deep($content), $allowed_tags);
}

function get_shortcode_params($settings) {

    $params = [];

    foreach ($settings as $key => $val) {
        $params[] = sprintf('%s="%s"', $key, $val);
    }

    return implode(' ', $params);
}

function echo_return($content, $echo = false) {
    if ($echo) {
        echo gs_wp_kses($content);
    } else {
        return $content;
    }
}

function get_query($atts) {

    $args = shortcode_atts([
        'order'                => 'DESC',
        'orderby'              => 'date',
        'posts_per_page'       => -1,
        'paged'                => 1,
        'tax_query'            => [],
    ], $atts);

    $args['post_type'] = 'gs_team';

    return new \WP_Query(apply_filters('gs_team_wp_query_args', $args));
}

function get_translation($translation_name) {
    return plugin()->builder->get_translation($translation_name);
}

function member_description($shortcode_id, $max_length = 100, $echo = false, $is_excerpt = true, $has_link = true, $link_type = 'single_page') {

    $member_id = get_the_ID();
    
    $description = $is_excerpt ? get_the_excerpt() : get_the_content();

    $description = sanitize_text_field($description);

    $gs_team_more = get_translation('gs_team_more');

    $gs_more_link = '';

    if ( $link_type == 'custom' ) {
        $custom_page_link = get_post_meta( $member_id, '_gs_custom_page', true );
        if ( empty($custom_page_link) ) {
            $default_link_type = getoption('single_link_type', 'single_page');
            if ( $default_link_type == 'none' ) {
                $has_link = false;
            } else {
                $link_type = $default_link_type;
            }
        }
    }

    if ($has_link) {

        if ($link_type == 'single_page') {

            $gs_more_link = sprintf('...<a href="%s">%s</a>', get_the_permalink(), $gs_team_more);
        } else if ($link_type == 'popup') {

            global $popup_style;

            $popup_style = empty($popup_style) ? 'default' : $popup_style;

            $gs_more_link = sprintf('...<a class="gs_team_pop open-popup-link" data-mfp-src="#gs_team_popup_%s_%s" href="javascript:void(0)" data-theme="%s">%s</a>', $member_id, $shortcode_id, 'gs-team-popup--' . esc_attr($popup_style), esc_html($gs_team_more));
        } else if ($link_type == 'panel') {

            $gs_more_link = sprintf('...<a class="gs_team_pop gs_team_panelslide_link" id="gsteamlink_%1$s_%2$s" href="#gsteam_%1$s_%2$s">%3$s</a>', $member_id, $shortcode_id, esc_html($gs_team_more));
        } else if ($link_type == 'drawer') {

            $gs_more_link = sprintf('...<a href="%s">%s</a>', get_the_permalink(), esc_html($gs_team_more));
        } else if ($link_type == 'custom') {

            $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
            $gs_more_link = sprintf('...<a href="%s" %s>%s</a>', esc_url($custom_page_link), $target, esc_html($gs_team_more));
        }
    }

    // Reduce the description length
    if ($max_length > 0 && mb_strlen($description) > $max_length) {
        $description = mb_substr($description, 0, $max_length) . $gs_more_link;
    }

    return echo_return($description, $echo);
}

function member_thumbnail($size, $echo = false) {

    $disable_lazy_load = getoption('disable_lazy_load', 'off');
    $lazy_load_class   = getoption('lazy_load_class', 'skip-lazy');

    $member_id = get_the_ID();

    if (has_post_thumbnail()) {

        $size = apply_filters('gs_team_member_thumbnail_size', $size, $member_id);
        if (empty($size)) $size = 'large';

        $classes = ['gs_team_member--image'];

        if ($disable_lazy_load == 'on' && !empty($lazy_load_class)) {
            $classes[] = $lazy_load_class;
        }

        $classes = (array) apply_filters('gs_team_thumbnail_classes', $classes);

        $thumbnail = get_the_post_thumbnail($member_id, $size, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);
    } else {

        $thumbnail = sprintf('<img src="%s" alt="%s" itemprop="image"/>', GSTEAM_PLUGIN_URI . '/assets/img/no_img.jpg', get_the_title());
    }

    $thumbnail = apply_filters('gs_team_member_thumbnail_html', $thumbnail, $member_id);

    return echo_return($thumbnail, $echo);
}

function member_thumbnail_custom($size, $shortcode_id, $has_link = true, $link_type = 'single_page', $echo = false) {

    $disable_lazy_load = getoption('disable_lazy_load', 'off');
    $lazy_load_class   = getoption('lazy_load_class', 'skip-lazy');

    $member_id = get_the_ID();

    if (has_post_thumbnail()) {

        $size = apply_filters('gs_team_member_thumbnail_size', $size, $member_id);
        if (empty($size)) $size = 'large';

        $classes = ['gs_team_member--image'];

        if ($disable_lazy_load == 'on' && !empty($lazy_load_class)) {
            $classes[] = $lazy_load_class;
        }

        $classes = (array) apply_filters('gs_team_thumbnail_classes', $classes);

        $thumbnail = get_the_post_thumbnail($member_id, $size, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);

        if ( $link_type == 'custom' ) {
            $custom_page_link = get_post_meta( $member_id, '_gs_custom_page', true );
            if ( empty($custom_page_link) ) {
                $default_link_type = getoption('single_link_type', 'single_page');
                if ( $default_link_type == 'none' ) {
                    $has_link = false;
                } else {
                    $link_type = $default_link_type;
                }
            }
        }

        if ($has_link) {

            if ($link_type == 'single_page') {

                $linked_thumb = sprintf('<a href="%s">%s <div class="gs_team_image__overlay"></div></a>', get_the_permalink(), $thumbnail);
            } else if ($link_type == 'popup') {

                global $popup_style;

                $popup_style = empty($popup_style) ? 'default' : $popup_style;

                $linked_thumb = sprintf('<a class="gs_team_pop open-popup-link" data-mfp-src="#gs_team_popup_%s_%s" data-theme="%s" href="javascript:void(0)">%s <div class="gs_team_image__overlay"></div></a>', get_the_ID(), $shortcode_id, 'gs-team-popup--' . esc_attr($popup_style), $thumbnail);
            } else if ($link_type == 'panel') {

                $linked_thumb = sprintf('<a class="gs_team_pop gs_team_panelslide_link" id="gsteamlinkp_%1$s_%2$s" href="#gsteam_%1$s_%2$s">%3$s <div class="gs_team_image__overlay"></div></a>', get_the_ID(), $shortcode_id, $thumbnail);
            } else if ($link_type == 'drawer') {

                $linked_thumb = sprintf('<a href="%s">%s <div class="gs_team_image__overlay"></div></a>', get_the_permalink(), $thumbnail);
            } else if ($link_type == 'custom') {

                $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
                $linked_thumb = sprintf('<a href="%s" %s>%s <div class="gs_team_image__overlay"></div></a>', esc_url($custom_page_link), $target, $thumbnail);
            }

            return echo_return($linked_thumb, $echo);
        }
    } else {
        $thumbnail = sprintf('<img src="%s" alt="%s" itemprop="image"/>', GSTEAM_PLUGIN_URI . '/assets/img/no_img.jpg', get_the_title());
    }

    $thumbnail = apply_filters('gs_team_member_thumbnail_html', $thumbnail, $member_id);

    return echo_return($thumbnail, $echo);
}

function member_thumbnail_with_link($shortcode_id, $size, $has_link = false, $link_type = 'single_page', $overlay = false, $extra_link_class = '') {

    $member_id = get_the_ID();
    $image_html = member_thumbnail($size, false);

    $img_overlay = '';
    if ($overlay) {
        $img_overlay = '<div class="gs_team_image__overlay"></div>';
    }

    $before = $after = '';

    if ( $link_type == 'custom' ) {
        $custom_page_link = get_post_meta( $member_id, '_gs_custom_page', true );
        if ( empty($custom_page_link) ) {
            $default_link_type = getoption('single_link_type', 'single_page');
            if ( $default_link_type == 'none' ) {
                $has_link = false;
            } else {
                $link_type = $default_link_type;
            }
        }
    }

    if ($has_link) {

        if ($link_type == 'single_page') {

            $before = sprintf('<a class="%s" href="%s">', esc_attr($extra_link_class), get_the_permalink());
        } else if ($link_type == 'popup') {

            global $popup_style;

            $popup_style = empty($popup_style) ? 'default' : $popup_style;

            $before = sprintf('<a class="gs_team_pop open-popup-link %s" data-mfp-src="#gs_team_popup_%s_%s" data-theme="%s" href="javascript:void(0);">', esc_attr($extra_link_class), $member_id, $shortcode_id, 'gs-team-popup--' . esc_attr($popup_style));
        } else if ($link_type == 'panel') {

            $before = sprintf('<a class="gs_team_pop gs_team_panelslide_link %1$s" id="gsteamlink_%2$s_%3$s" href="#gsteam_%2$s_%3$s">', esc_attr($extra_link_class), $member_id, $shortcode_id);
        } else if ($link_type == 'drawer') {

            $before = sprintf('<a class="%s" href="%s">', esc_attr($extra_link_class), get_the_permalink());
        } else if ($link_type == 'custom') {

            $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
            $before = sprintf('<a class="%s" %s href="%s">', esc_attr($extra_link_class), $target, esc_url($custom_page_link));
        }

        $after = '</a>';
    }

    return $before . $image_html . $img_overlay . $after;
}

function member_name($shortcode_id, $echo = false, $has_link = true, $link_type = 'single_page', $tag = 'div', $extra_classes = '', $no_default_class = false, $custom_title = '') {

    $member_id = get_the_ID();

    if (empty($tag) || !in_array($tag, ['div', 'p', 'span', 'td', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) $tag = 'div';

    $the_title = $custom_title ?: get_the_title();

    if ( $link_type == 'custom' ) {
        $custom_page_link = get_post_meta( $member_id, '_gs_custom_page', true );
        if ( empty($custom_page_link) ) {
            $default_link_type = getoption('single_link_type', 'single_page');
            if ( $default_link_type == 'none' ) {
                $has_link = false;
            } else {
                $link_type = $default_link_type;
            }
        }
    }

    if ($has_link) {

        if ($link_type == 'single_page') {

            $the_title = sprintf('<a href="%s">%s</a>', get_the_permalink(), $the_title);
        } else if ($link_type == 'popup') {

            global $popup_style;

            $popup_style = empty($popup_style) ? 'default' : $popup_style;

            $the_title = sprintf('<a class="gs_team_pop open-popup-link" data-mfp-src="#gs_team_popup_%s_%s" data-theme="%s" href="javascript:void(0)">%s</a>', get_the_ID(), $shortcode_id, 'gs-team-popup--' . esc_attr($popup_style), $the_title);
        } else if ($link_type == 'panel') {

            $the_title = sprintf('<a class="gs_team_pop gs_team_panelslide_link" id="gsteamlinkp_%1$s_%2$s" href="#gsteam_%1$s_%2$s">%3$s</a>', get_the_ID(), $shortcode_id, $the_title);
        } else if ($link_type == 'drawer') {

            $the_title = sprintf('<a href="%s">%s</a>', get_the_permalink(), $the_title);
        } else if ($link_type == 'custom') {

            $target = is_internal_url($custom_page_link) ? '' : 'target="_blank"';
            $the_title = sprintf('<a href="%s" %s>%s</a>', esc_url($custom_page_link), $target, $the_title);
        }
    }

    $classes = $no_default_class ? '' : 'gs-member-name ';

    $classes .= $extra_classes;

    $name = sprintf('<%1$s class="%2$s" itemprop="name">%3$s</%1$s>', $tag, $classes, $the_title);

    $name = apply_filters('gs_team_member_name_html', $name, $member_id);

    return echo_return($name, $echo);
}

function getoption($option, $default = '') {
    $prefs = plugin()->builder->_get_shortcode_pref( false );
    return isset($prefs[$option]) ? $prefs[$option] : $default;
}

function member_secondary_thumbnail($size, $echo = false) {

    $member_id = get_the_ID();

    $thumbnail_id = get_post_meta($member_id, 'second_featured_img', true);

    $size = apply_filters('gs_team_member_secondary_thumbnail_size', $size, $member_id);
    if (empty($size)) $size = 'large';

    $thumbnail = '';

    if ($thumbnail_id) {

        $classes = (array) apply_filters('gs_team_secondary_thumbnail_classes', ['gs_team_member--image']);

        $thumbnail = wp_get_attachment_image($thumbnail_id, $size, false, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);
    }

    $thumbnail = apply_filters('gs_team_member_secondary_thumbnail_html', $thumbnail, $member_id);

    return echo_return($thumbnail, $echo);
}

function member_email_mailto($icon = '', $echo = false) {

    $member_id = get_the_ID();

    $email = get_post_meta($member_id, '_gs_email', true);
    $email_cc = get_post_meta($member_id, '_gs_cc', true);
    $email_bcc = get_post_meta($member_id, '_gs_bcc', true);

    // Remove spaces from comma separated emails_cc and emails_bcc & validate each email
    $email_cc = implode(',', array_filter(array_map('trim', explode(',', $email_cc)), 'is_email'));
    $email_bcc = implode(',', array_filter(array_map('trim', explode(',', $email_bcc)), 'is_email'));

    $email_link = sprintf('<a href="mailto:%1$s%2$s%3$s">%4$s%1$s</a>', $email, !empty($email_cc) ? '?cc=' . $email_cc : '', !empty($email_bcc) ? '&bcc=' . $email_bcc : '', $icon);

    $email_link = apply_filters('gs_team_member_email_link', $email_link, $member_id);

    return echo_return($email_link, $echo);
}

function member_custom() {

    $member_id = get_the_ID();

    $thumbnail_id = get_post_meta($member_id, 'second_featured_img', true);

    // $size = apply_filters( 'gs_team_member_secondary_thumbnail_size', $size, $member_id );
    // if ( empty($size) ) $size = 'large';

    $thumbnail = '';

    if ($thumbnail_id) {

        $classes = (array) apply_filters('gs_team_secondary_thumbnail_classes', ['gs_team_member--image']);

        $thumbnail = wp_get_attachment_image($thumbnail_id, array('50', '50'), false, [
            'class' => implode(' ', $classes),
            'alt' => get_the_title(),
            'itemprop' => 'image'
        ]);
    }

    $thumbnail = apply_filters('gs_team_member_secondary_thumbnail_html', $thumbnail, $member_id);

    return echo_return($thumbnail, true);
}

function format_phone($num) {

    $num = preg_replace('/[^0-9]/', '', $num);
    $len = strlen($num);

    if ($len == 7) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})/', '($1) $2$3-', $num);
    elseif ($len == 8) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})/', '($1) $2$3-', $num);
    elseif ($len == 9) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})([0-9]{2})/', '($1) $2$3-$4', $num);
    elseif ($len == 10) $num = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{1})([0-9]{3})/', '($1) $2$3-$4', $num);

    return $num;
}

function get_meta_values($meta_key, $args) {

    extract(shortcode_atts([
        'post_type' => 'gs_team',
        'status' => 'publish',
        'order_by' => true,
        'order' => 'ASC',
        'post_ids' => []
    ], $args));

    global $wpdb;

    if (empty($meta_key)) return [];

    if ($order_by) {
        $order == 'ASC' ? $order : 'DESC';
        $order_by = sprintf(' ORDER BY pm.meta_value %s', $order);
    } else {
        $order_by = '';
    }

    $query = $wpdb->prepare("
            SELECT pm.meta_value FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s 
            AND p.post_status = %s 
            AND p.post_type = %s 
        ", $meta_key, $status, $post_type);

    if (!empty($post_ids)) {
        $post_ids = implode("','", $post_ids);
        $query .= "AND p.ID IN ('" . $post_ids . "')";
    }

    $query .= $order_by;

    $result = $wpdb->get_col($query);

    $result = array_values(array_unique($result));

    return $result;
}

function get_meta_values_options($meta_key = '', $post_type = 'gs_team', $status = 'publish', $echo = true) {

    $meta_values = get_meta_values($meta_key, $post_type, $status);

    $html = '';

    foreach ($meta_values as $meta_value) {
        $html .= sprintf('<option value=".%s">%s</option>', sanitize_title($meta_value), esc_html($meta_value));
    }

    return echo_return($html, $echo);
}

function gs_cols_to_number($cols) {

    return (12 / (int) str_replace('_', '.', $cols));
}

function get_carousel_data($cols_desktop, $cols_tablet, $cols_mobile_portrait, $cols_mobile, $echo = true) {

    $carousel_data = [
        'data-carousel-desktop'         => gs_cols_to_number($cols_desktop),
        'data-carousel-tablet'             => gs_cols_to_number($cols_tablet),
        'data-carousel-mobile-portrait' => gs_cols_to_number($cols_mobile_portrait),
        'data-carousel-mobile'             => gs_cols_to_number($cols_mobile)
    ];

    $carousel_data = array_map(function ($key, $val) {
        return $key . '=' . esc_attr($val);
    }, array_keys($carousel_data), array_values($carousel_data));

    $carousel_data = implode(' ', $carousel_data);

    return echo_return($carousel_data, $echo);
}

function get_col_classes($desktop = '3', $tablet = '4', $mobile_portrait = '6', $mobile = '12') {
    return sprintf('gs-col-lg-%s gs-col-md-%s gs-col-sm-%s gs-col-xs-%s', $desktop, $tablet, $mobile_portrait, $mobile);
}


function gs_team_get_terms($term_name, $order = 'ASC', $orderby = 'name', $exclude = [], $hide_empty = false) {

    $term_name = 'gs_' . str_replace('gs_', '', $term_name);

    $taxonomies = get_taxonomies([ 'type' => 'restricted', 'enabled' => true ]);

    if ( ! in_array( $term_name, $taxonomies ) ) return [];

    $args = [
        'taxonomy' => $term_name,
        'orderby'  => $orderby,
        'order'    => $order,
        'exclude' => (array) $exclude,
        'hide_empty' => $hide_empty
    ];

    $args = apply_filters('gs_team_get_terms', $args);

    $terms = get_terms($args);

    return wp_list_pluck($terms, 'name', 'slug');
}

function string_to_array($terms = '') {
    if (empty($terms)) return [];
    return (array) array_filter(explode(',', $terms));
}

function get_taxonomies( $args = [] ) {

    $args = wp_parse_args( $args, [
        'enabled' => true,
        'restricted' => true
    ]);

    $taxonomies = [
        'group' => 'gs_team_group',
        'tag' => 'gs_team_tag',
        'language' => 'gs_team_language',
        'location' => 'gs_team_location',
        'gender' => 'gs_team_gender',
        'specialty' => 'gs_team_specialty',
        'extra_one' => 'gs_team_extra_one',
        'extra_two' => 'gs_team_extra_two',
        'extra_three' => 'gs_team_extra_three',
        'extra_four' => 'gs_team_extra_four',
        'extra_five' => 'gs_team_extra_five'
    ];

    if ( $args['restricted'] && ! gtm_fs()->is_paying_or_trial() ) {
        $taxonomies = array_intersect_key($taxonomies, array_flip(['group', 'tag']));
    }

    if ( $args['enabled'] ) {
        $taxonomies = array_filter($taxonomies, function($taxonomy) {
            return plugin()->builder->get_tax_option( 'enable_' . $taxonomy . '_tax' ) == 'on';
        }, ARRAY_FILTER_USE_KEY);
    }

    return array_values( $taxonomies );
}

function get_terms_for_filter($term_name, $hide_empty = false, $include = '', $order = 'ASC', $orderby = 'name') {

    $term_name = 'gs_' . str_replace('gs_', '', $term_name);

    $taxonomies = get_taxonomies([ 'type' => 'restricted', 'enabled' => true ]);

    if ( ! in_array( $term_name, $taxonomies ) ) return [];

    $args = [
        'taxonomy'  => $term_name,
        'orderby'   => $orderby,
        'order'     => $order,
        'hide_empty' => $hide_empty,
        'ignore_term_order' => true,
        'include' => string_to_array($include)
    ];

    $args = apply_filters('gs_team_get_terms_for_filter', $args);

    return get_terms( $args );
}

function get_terms_options($terms, $echo = true) {

    $html = '';

    foreach ($terms as $term) {
        $html .= sprintf('<option value=".%s">%s</option>', esc_attr($term->slug), esc_html($term->name));
    }

    return echo_return($html, $echo);
}

function setup_group_to_posts($query) {

    if (empty($query->posts)) return;

    foreach ($query->posts as $post_key => $post) {

        $terms = get_the_terms($post->ID, 'gs_team_group');
        $terms = empty($terms) ? [] : wp_list_pluck($terms, 'slug');
        $query->posts[$post_key]->gs_team_group = (array) $terms;
    }
}

function filter_posts_by_term($group_slug, $query_posts) {

    $posts = array_filter($query_posts, function ($post) use ($group_slug) {
        return in_array($group_slug, $post->gs_team_group);
    });

    return array_values($posts);
}

function get_member_terms_slugs($term_name, $separator = ' ') {

    global $post;

    $term_name = 'gs_' . str_replace('gs_', '', $term_name);

    $terms = get_the_terms($post->ID, $term_name);

    if (!empty($terms) && !is_wp_error($terms)) {
        $terms = implode($separator, wp_list_pluck($terms, 'slug'));
        return $terms;
    }

    return '';
}

function pagination($echo = true) {

    $gs_tm_paged = get_query_var('paged') ? get_query_var('paged') : get_query_var('page');
    $gsbig = 999999999; // need an unlikely integer

    $paginate_params = [
        'base' => str_replace($gsbig, '%#%', esc_url(get_pagenum_link($gsbig))),
        'format' => '?paged=%#%',
        'current' => max(1, $gs_tm_paged),
        'total' => $GLOBALS['gs_team_loop']->max_num_pages
    ];
    $paginate_params = (array) apply_filters('gs_team_paginate_params', $paginate_params);

    $paginate_links = paginate_links($paginate_params);
    $paginate_links = apply_filters('gs_team_paginate_links', $paginate_links);

    $html = '';

    if (!empty($paginate_links)) {
        $html = sprintf('<div class="gs-roow"><div class="gs-col-md-12 gs-pagination">%s</div></div>', wp_kses_post($paginate_links));
    }

    return echo_return($html, $echo);
}

function get_shortcodes() {

    return plugin()->builder->fetch_shortcodes(null, false, true);
}

function select_builder($name, $options, $selected = "", $selecttext = "", $class = "", $optionvalue = 'value') {

    if (is_array($options)) {

        $select_html = sprintf('<select name="%1$s" id="%1$s" class="%2$s">', esc_attr($name), esc_attr($class));

        if ($selecttext) $select_html .= sprintf('<option value="">%s</option>', esc_html($selecttext));

        foreach ($options as $key => $option) {
            $value = $optionvalue == 'value' ? $option : $key;
            $is_selected = $value == $selected ? 'selected="selected"' : '';
            $select_html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $is_selected, esc_html($option));
        }

        $select_html .= '</select>';
        echo gs_wp_kses($select_html);
    }
}

function add_fs_script($handler) {

    $data = [
        'is_paying_or_trial' => wp_validate_boolean(gtm_fs()->is_paying_or_trial())
    ];

    wp_localize_script($handler, 'gs_team_fs', $data);
}

function terms_hierarchically(array &$cats, array &$into, $parentId = 0, $exclude_group = []) {

    foreach ($cats as $i => $cat) {
        if (in_array($cat->term_id, $exclude_group)) continue;
        if ($cat->parent == $parentId) {
            $into[$cat->term_id] = $cat;
            unset($cats[$i]);
        }
    }

    foreach ($into as $topCat) {
        $topCat->children = array();
        terms_hierarchically($cats, $topCat->children, $topCat->term_id, $exclude_group);
    }
}

function term_walker($term) {

    if (!empty($term->children)) : ?>
        <ul class="filter-cats--sub">
            <?php foreach ($term->children as $_term) :

                $has_child = !empty($_term->children);

            ?>

                <li class="filter <?php echo $has_child ? 'has-child' : ''; ?>">
                    <a href="javascript:void(0)" data-filter=".<?php echo esc_attr($_term->slug); ?>">
                        <span><?php echo esc_html($_term->name); ?></span>
                        <?php if ($has_child) : ?>
                            <span class="sub-arrow fa fa-angle-right"></span>
                        <?php endif; ?>
                    </a>
                    <?php term_walker($_term); ?>
                </li>

            <?php endforeach; ?>
        </ul>
<?php endif;
}

/*
 * @param $version          all | 1 | 2
 * @param $type             both | free | pro
 * @param $data_type        full | label | value
 */
function get_themes_list($version = 'all', $type = 'both', $data_type = 'full') {

    $themes = [];
    $versions = $version != 'all' ? [$version] : [1, 2];
    $methods = [];

    $versions = array_reverse($versions);

    foreach ($versions as $version) {
        if ($type == 'free' || $type == 'both') {
            $methods[] = 'get_' . 'free' . '_themes_v_' . $version;
        }
        if ($type == 'pro' || $type == 'both') {
            $methods[] = 'get_' . 'pro' . '_themes_v_' . $version;
        }
    }

    foreach ($methods as $method) {
        if (is_callable(['GSTEAM\Builder', $method], true, $callable_name)) {
            $themes = array_merge($themes, $callable_name());
        }
    }

    if ($data_type == 'full') return $themes;

    return wp_list_pluck($themes, $data_type);
}

if (gtm_fs()->is_paying_or_trial()) {

    function gs_team_get_terms_names($term_name, $separator = ', ') {

        global $post;

        $terms = get_the_terms($post->ID, $term_name);

        if (!empty($terms) && !is_wp_error($terms)) {
            $terms = implode($separator, wp_list_pluck($terms, 'name'));
            return $terms;
        }
    }

    function gs_team_member_location($separator = ', ') {
        return gs_team_get_terms_names('gs_team_location', $separator);
    }

    function gs_team_member_language($separator = ', ') {
        return gs_team_get_terms_names('gs_team_language', $separator);
    }

    function gs_team_member_specialty($separator = ', ') {
        return gs_team_get_terms_names('gs_team_specialty', $separator);
    }

    function gs_team_member_gender($separator = ', ') {
        return gs_team_get_terms_names('gs_team_gender', $separator);
    }

    function gs_team_member_extra_one($separator = ', ') {
        return gs_team_get_terms_names('gs_team_extra_one', $separator);
    }

    function gs_team_member_extra_two($separator = ', ') {
        return gs_team_get_terms_names('gs_team_extra_two', $separator);
    }

    function gs_team_member_extra_three($separator = ', ') {
        return gs_team_get_terms_names('gs_team_extra_three', $separator);
    }

    function gs_team_member_extra_four($separator = ', ') {
        return gs_team_get_terms_names('gs_team_extra_four', $separator);
    }

    function gs_team_member_extra_five($separator = ', ') {
        return gs_team_get_terms_names('gs_team_extra_five', $separator);
    }
}

function minimize_css_simple($css) {
    // https://datayze.com/howto/minify-css-with-php
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function get_social_links($post_id = null) {
    if (empty($post_id)) $post_id = get_the_ID();
    if (empty($post_id)) return [];
    $social_links = (array) get_post_meta($post_id, 'gs_social', true);
    $social_links = array_filter($social_links);
    return (array) apply_filters('gs_team_member_social_links', $social_links, $post_id);
}

function get_skills($post_id = null) {
    if (empty($post_id)) $post_id = get_the_ID();
    if (empty($post_id)) return [];
    $skills = (array) get_post_meta($post_id, 'gs_skill', true);
    $skills = array_filter($skills);
    return (array) apply_filters('gs_team_member_skills', $skills, $post_id);
}

function isPreview() {
    return isset($_REQUEST['gsteam_shortcode_preview']) && !empty($_REQUEST['gsteam_shortcode_preview']);
}

function is_internal_url($url) {
    $home_url = home_url();
    return str_contains($url, $home_url);
}

function get_post_type_title() {
    return __( 'Teams', 'gsteam' );
}

function gs_get_post_type_archive_title() {
    $archive_title = getoption('archive_page_title', get_post_type_title());
    if ( empty($archive_title) ) return get_post_type_title();
    return $archive_title;
}