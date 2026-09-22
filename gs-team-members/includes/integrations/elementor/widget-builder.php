<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Elementor_Widget_Builder extends Widget_Base {

	const SETTINGS_ID_PREFIX = 'gste_';

	protected $builder_defaults = null;
	protected $builder_options  = null;
	protected $builder_strings  = null;

	public function get_name() {
		return 'gs-team-members-builder';
	}

	public function get_title() {
		return __( 'GS Team Builder', 'gsteam' );
	}

	public function get_icon() {
		return 'gs-team-members';
	}

	public function get_categories() {
		return [ 'gs-plugins', 'general' ];
	}

	public function get_keywords() {
		return [ 'team', 'members', 'gs', 'builder', 'staff', 'showcase' ];
	}

	protected function register_controls() {

		$this->register_general_controls();
		$this->register_query_controls();
		$this->register_visibility_controls();
		$this->register_style_controls();

	}

	protected function register_general_controls() {

		$options = $this->get_builder_options();

		$this->start_controls_section(
			'general_section',
			[
				'label' => $this->t( 'general-settings' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_select_control(
			'gs_team_theme',
			'style-theming',
			$options['gs_team_theme'],
			[
				'description' => $this->t( 'select-preffered-style-theme' ),
				'type'        => Controls_Manager::SELECT2,
			]
		);

		$this->add_on_off_control(
			'carousel_enabled',
			'carousel_enabled',
			[
				'condition' => [
					'gs_team_theme' => $this->get_themes_v2_carousel(),
				],
			]
		);

		$carousel_active = $this->get_carousel_active_conditions();

		$this->add_on_off_control( 'carousel_autoplay', 'carousel_autoplay', [ 'conditions' => $carousel_active ] );

		$this->add_on_off_control(
			'carousel_autoplay_hover_pause',
			'carousel_autoplay_hover_pause',
			[
				'conditions' => $this->merge_conditions_and( $carousel_active, [
					$this->term_is( 'carousel_autoplay', 'on' ),
				] ),
			]
		);

		$this->add_on_off_control( 'carousel_loop', 'carousel_loop', [ 'conditions' => $carousel_active ] );

		$this->add_number_control(
			'carousel_autoplay_speed',
			'carousel_autoplay_speed',
			[
				'min'        => 0,
				'step'       => 100,
				'conditions' => $this->merge_conditions_and( $carousel_active, [
					$this->term_is( 'carousel_autoplay', 'on' ),
				] ),
			]
		);

		$this->add_number_control(
			'carousel_autoplay_timeout',
			'carousel_autoplay_timeout',
			[
				'min'        => 0,
				'step'       => 100,
				'conditions' => $this->merge_conditions_and( $carousel_active, [
					$this->term_is( 'carousel_autoplay', 'on' ),
				] ),
			]
		);

		$this->add_number_control(
			'carousel_items_to_scroll',
			'carousel_items_to_scroll',
			[
				'min'        => 1,
				'step'       => 1,
				'conditions' => $carousel_active,
			]
		);

		$this->add_on_off_control(
			'filter_enabled',
			'filter_enabled',
			[
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is_not( 'carousel_enabled', 'on' ),
						$this->term_is_not( 'gs_member_link_type', 'drawer' ),
						$this->term_in( 'gs_team_theme', $this->get_themes_v2_filter() ),
					],
				],
			]
		);

		$this->add_select_control(
			'gs_team_filter_type',
			'filter_type',
			$options['gs_team_filter_type'],
			[
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'filter_enabled', 'on' ),
						$this->term_is_not( 'carousel_enabled', 'on' ),
						$this->term_in( 'gs_team_theme', $this->get_themes_v2_filter() ),
					],
				],
			]
		);

		$pagination_conditions = $this->get_pagination_conditions();

		$this->add_on_off_control( 'gs_member_pagination', 'gs_member_pagination', [ 'conditions' => $pagination_conditions ] );

		$this->add_select_control(
			'pagination_type',
			'pagination_type',
			$options['pagination_type'],
			[
				'conditions' => $this->merge_conditions_and( $pagination_conditions, [
					$this->term_is( 'gs_member_pagination', 'on' ),
				] ),
			]
		);

		$this->add_number_control(
			'initial_items',
			'initial_items',
			[
				'min'        => 1,
				'conditions' => $this->merge_conditions_and( $pagination_conditions, [
					$this->term_is( 'gs_member_pagination', 'on' ),
					$this->term_in( 'pagination_type', [ 'load-more-button', 'load-more-scroll' ] ),
				] ),
			]
		);

		$this->add_number_control(
			'team_per_page',
			'team_per_page',
			[
				'min'        => 1,
				'conditions' => $this->merge_conditions_and( $pagination_conditions, [
					$this->term_is( 'gs_member_pagination', 'on' ),
					$this->term_in( 'pagination_type', [ 'normal-pagination', 'ajax-pagination' ] ),
				] ),
			]
		);

		$this->add_number_control(
			'load_per_click',
			'load_per_click',
			[
				'min'        => 1,
				'conditions' => $this->merge_conditions_and( $pagination_conditions, [
					$this->term_is( 'gs_member_pagination', 'on' ),
					$this->term_is( 'pagination_type', 'load-more-button' ),
				] ),
			]
		);

		$this->add_number_control(
			'per_load',
			'per_load',
			[
				'min'        => 1,
				'conditions' => $this->merge_conditions_and( $pagination_conditions, [
					$this->term_is( 'gs_member_pagination', 'on' ),
					$this->term_is( 'pagination_type', 'load-more-scroll' ),
				] ),
			]
		);

		$this->add_text_control(
			'load_button_text',
			'load_button_text',
			[
				'conditions' => $this->merge_conditions_and( $pagination_conditions, [
					$this->term_is( 'gs_member_pagination', 'on' ),
					$this->term_is( 'pagination_type', 'load-more-button' ),
				] ),
			]
		);

		$this->add_on_off_control(
			'link_preview_image',
			'link_preview_image',
			[
				'description' => $this->t( 'preview_enabled__details' ),
				'condition'   => [
					'gs_team_theme' => [ 'gs-grid-style-one', 'gs-grid-style-two', 'gs-grid-style-three', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-grid-style-six' ],
				],
			]
		);

		$this->add_on_off_control( 'enable_featuring', 'enable_featuring' );

		$this->add_on_off_control(
			'featured_badge',
			'featured_badge',
			[
				'condition' => [
					'enable_featuring' => 'on',
				],
			]
		);

		$link_hidden_themes = [ 'gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme25' ];

		$this->add_on_off_control(
			'gs_member_name_is_linked',
			'gs_member_name_is_linked',
			[
				'condition' => [
					'gs_team_theme!' => $link_hidden_themes,
				],
			]
		);

		$this->add_select_control(
			'gs_member_link_type',
			'gs_member_link_type',
			$options['gs_member_link_type'],
			[
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'gs_member_name_is_linked', 'on' ),
						$this->term_not_in( 'gs_team_theme', $link_hidden_themes ),
					],
				],
			]
		);

		$this->add_select_control(
			'popup_style',
			'popup_style',
			$options['popup_style'],
			[
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'gs_member_name_is_linked', 'on' ),
						$this->term_is( 'gs_member_link_type', 'popup' ),
						$this->term_not_in( 'gs_team_theme', $link_hidden_themes ),
					],
				],
			]
		);

		$this->add_select_control(
			'panel_style',
			'panel_style',
			$options['panel_style'],
			[
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'gs_member_name_is_linked', 'on' ),
						$this->term_is( 'gs_member_link_type', 'panel' ),
						$this->term_not_in( 'gs_team_theme', $link_hidden_themes ),
					],
				],
			]
		);

		$this->add_select_control(
			'drawer_style',
			'drawer_style',
			$options['drawer_style'],
			[
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'gs_member_name_is_linked', 'on' ),
						$this->term_is( 'gs_member_link_type', 'drawer' ),
						$this->term_is_not( 'carousel_enabled', 'on' ),
						$this->term_not_in( 'gs_team_theme', $link_hidden_themes ),
					],
				],
			]
		);

		$this->add_select_control(
			'gs_member_thumbnail_sizes',
			'gs_member_thumbnail_sizes',
			$options['gs_member_thumbnail_sizes']
		);

		$search_conditions = $this->get_filter_or_v2_conditions( [ 'gs_tm_theme9', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25' ] );
		$filter_by_conditions = $this->get_filter_or_v2_conditions( [ 'gs_tm_theme9', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25' ] );

		$this->add_on_off_control( 'gs_member_srch_by_name', 'instant-search-by-name', [ 'conditions' => $search_conditions ] );
		$this->add_on_off_control( 'gs_member_srch_by_company', 'gs-member-srch-by-company', [ 'conditions' => $search_conditions ] );
		$this->add_on_off_control( 'gs_member_srch_by_zip', 'gs-member-srch-by-zip', [ 'conditions' => $search_conditions ] );
		$this->add_on_off_control( 'gs_member_srch_by_tag', 'gs-member-srch-by-tag', [ 'conditions' => $search_conditions ] );

		$this->add_on_off_control( 'gs_member_filter_by_desig', 'filter-by-designation', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-designation--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_location', 'filter-by-location', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-location--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_language', 'filter-by-language', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-language--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_gender', 'filter-by-gender', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-gender--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_speciality', 'filter-by-speciality', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-speciality--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_extra_one', 'filter-by-extra-one', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-extra-one--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_extra_two', 'filter-by-extra-two', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-extra-two--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_extra_three', 'filter-by-extra-three', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-extra-three--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_extra_four', 'filter-by-extra-four', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-extra-four--des' ) ] );
		$this->add_on_off_control( 'gs_member_filter_by_extra_five', 'filter-by-extra-five', [ 'conditions' => $filter_by_conditions, 'description' => $this->t( 'filter-by-extra-five--des' ) ] );

		$this->add_on_off_control( 'gs_member_enable_clear_filters', 'enable-clear-filters', [ 'conditions' => $filter_by_conditions ] );
		$this->add_on_off_control( 'gs_member_enable_multi_select', 'enable-multi-select', [ 'conditions' => $filter_by_conditions ] );

		$this->add_on_off_control(
			'gs_member_multi_select_ellipsis',
			'multi-select-ellipsis',
			[
				'conditions' => $this->merge_conditions_and( $filter_by_conditions, [
					$this->term_is( 'gs_member_enable_multi_select', 'on' ),
				] ),
			]
		);

		$this->add_on_off_control( 'gs_filter_all_enabled', 'filter-all-enabled', [ 'conditions' => $filter_by_conditions ] );

		$this->add_on_off_control(
			'enable_child_cats',
			'enable-child-cats',
			[
				'conditions' => $this->get_filter_or_v2_conditions( [ 'gs_tm_theme9', 'gs_tm_theme12', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25' ] ),
			]
		);

		$this->add_on_off_control(
			'enable_scroll_animation',
			'enable-scroll-animation',
			[
				'condition' => [
					'gs_team_theme!' => [ 'gs_tm_theme7', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21' ],
				],
			]
		);

		$this->add_text_control( 'fitler_all_text', 'fitler-all-text', [ 'conditions' => $filter_by_conditions ] );

		$this->add_select_control(
			'gs_team_filter_columns',
			'gs_team_filter_columns',
			$options['gs_team_filter_columns'],
			[ 'conditions' => $filter_by_conditions ]
		);

		$details_hidden_themes = [ 'gs_tm_grid2', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme21', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme25' ];

		$this->add_on_off_control(
			'gs_desc_allow_html',
			'gs-desc-allow-html',
			[
				'condition' => [
					'gs_team_theme!' => $details_hidden_themes,
				],
			]
		);

		$this->add_number_control(
			'gs_tm_details_contl',
			'details-control',
			[
				'min'         => 0,
				'description' => $this->t( 'define-maximum-number-of-characters' ),
				'conditions'  => [
					'relation' => 'and',
					'terms'    => [
						$this->term_is_not( 'gs_desc_allow_html', 'on' ),
						$this->term_not_in( 'gs_team_theme', array_merge( $details_hidden_themes, [ 'gs_tm_theme_custom_10' ] ) ),
					],
				],
			]
		);

		$this->add_on_off_control( 'gs_desc_scroll_contrl', 'gs-desc-scroll-contrl' );

		$this->add_number_control(
			'gs_max_scroll_height',
			'gs-max-scroll-height',
			[
				'min'       => 0,
				'condition' => [
					'gs_desc_scroll_contrl' => 'on',
				],
			]
		);

		$this->add_select_control(
			'gs_teammembers_pop_clm',
			'popup-column',
			$options['gs_teammembers_pop_clm'],
			[
				'description' => $this->t( 'set-column-for-popup' ),
				'conditions'  => $this->merge_conditions_and( $this->get_popup_enabled_conditions(), [
					$this->term_in( 'popup_style', [ 'default', 'style-six' ] ),
				] ),
			]
		);

		$this->add_control(
			'gs_tm_filter_cat_pos',
			[
				'label'      => $this->t( 'filter-category-position' ),
				'type'       => Controls_Manager::CHOOSE,
				'options'    => [
					'left'   => [
						'title' => __( 'Left', 'gsteam' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'gsteam' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'gsteam' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'    => 'center',
				'toggle'     => false,
				'conditions' => $this->get_filter_or_v2_conditions( [ 'gs_tm_theme9', 'gs_tm_theme12' ] ),
			]
		);

		$this->add_control(
			'panel',
			[
				'label'     => $this->t( 'panel' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'gsteam' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'gsteam' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'gsteam' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'   => 'right',
				'toggle'    => false,
				'condition' => [
					'gs_team_theme' => 'gs_tm_theme19',
				],
			]
		);

		if ( $this->is_acf_available() ) {
			$this->add_on_off_control( 'show_acf_fields', 'show-acf-fields', [
				'description' => $this->t( 'show-acf-fields-details' ),
			] );

			$this->add_select_control(
				'acf_fields_position',
				'acf_fields_position',
				$options['acf_fields_position'],
				[
					'label'     => __( 'ACF Fields Position', 'gsteam' ),
					'condition' => [
						'show_acf_fields' => 'on',
					],
				]
			);
		}

		$this->end_controls_section();

	}

	protected function register_query_controls() {

		$options = $this->get_builder_options();

		$this->start_controls_section(
			'query_section',
			[
				'label' => $this->t( 'query-settings' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_number_control(
			'num',
			'team-members',
			[
				'min'         => -1,
				'description' => $this->t( 'set-max-team-numbers-you-want-to-show' ),
				'condition'   => [
					'gs_member_pagination!' => 'on',
				],
			]
		);

		$this->add_select_control( 'orderby', 'order-by', $options['orderby'] );
		$this->add_select_control( 'order', 'order', $options['order'] );
		$this->add_select_control( 'taxonomy_orderby', 'taxonomy-order-by', $options['taxonomy_orderby'] );
		$this->add_select_control( 'taxonomy_order', 'taxonomy-order', $options['order'] );
		$this->add_on_off_control( 'taxonomy_hide_empty', 'taxonomy_hide_empty' );

		$this->add_select2_multiple_control( 'group', 'group', $options['group'], [
			'description' => $this->t( 'select-specific-team-group-to' ),
		] );
		$this->add_select2_multiple_control( 'exclude_group', 'exclude_group', $options['exclude_group'] );
		$this->add_select2_multiple_control( 'language', 'language', $options['language'] );
		$this->add_select2_multiple_control( 'location', 'location', $options['location'] );
		$this->add_select2_multiple_control( 'specialty', 'specialty', $options['specialty'] );
		$this->add_select2_multiple_control( 'gender', 'gender', $options['gender'] );
		$this->add_select2_multiple_control( 'include_extra_one', 'include_extra_one', $options['extra_one'] );
		$this->add_select2_multiple_control( 'include_extra_two', 'include_extra_two', $options['extra_two'] );
		$this->add_select2_multiple_control( 'include_extra_three', 'include_extra_three', $options['extra_three'] );
		$this->add_select2_multiple_control( 'include_extra_four', 'include_extra_four', $options['extra_four'] );
		$this->add_select2_multiple_control( 'include_extra_five', 'include_extra_five', $options['extra_five'] );

		$this->end_controls_section();

	}

	protected function register_visibility_controls() {

		$device_options = $this->get_visibility_device_options();
		$device_default = $this->get_visibility_device_default();

		$this->start_controls_section(
			'visibility_initial_section',
			[
				'label' => $this->t( 'visibility-initial-view' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		foreach ( $this->get_unique_theme_visibility_fields() as $field_key ) {
			$themes = $this->get_themes_for_visibility_field( $field_key );

			if ( empty( $themes ) ) {
				continue;
			}

			$this->add_control(
				'vis_initial_' . $field_key,
				[
					'label'       => $this->get_visibility_field_label( $field_key ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'options'     => $device_options,
					'default'     => $device_default,
					'condition'   => [
						'gs_team_theme' => $themes,
					],
				]
			);
		}

		$this->end_controls_section();

		$this->register_overlay_visibility_section(
			'visibility_popup_section',
			$this->t( 'visibility-popup' ),
			'vis_popup_',
			plugin()->builder->get_popup_style_visibility_fields(),
			'popup_style',
			$this->get_popup_visibility_section_conditions()
		);

		$this->register_overlay_visibility_section(
			'visibility_panel_section',
			$this->t( 'visibility-panel' ),
			'vis_panel_',
			plugin()->builder->get_panel_style_visibility_fields(),
			'panel_style',
			$this->get_panel_visibility_section_conditions()
		);

		$this->register_overlay_visibility_section(
			'visibility_drawer_section',
			$this->t( 'visibility-drawer' ),
			'vis_drawer_',
			plugin()->builder->get_drawer_style_visibility_fields(),
			'drawer_style',
			$this->get_drawer_visibility_section_conditions()
		);

	}

	protected function register_overlay_visibility_section( $section_id, $section_label, $prefix, $style_map, $style_control, $section_conditions ) {

		$device_options = $this->get_visibility_device_options();
		$device_default = $this->get_visibility_device_default();
		$core_fields    = [ 'member_thumbnail', 'member_name', 'member_designation', 'member_details', 'member_social' ];

		$this->start_controls_section(
			$section_id,
			[
				'label'      => $section_label,
				'tab'        => Controls_Manager::TAB_CONTENT,
				'conditions' => $section_conditions,
			]
		);

		$all_fields = [];
		foreach ( $style_map as $fields ) {
			$all_fields = array_merge( $all_fields, $fields );
		}
		$all_fields = array_values( array_unique( $all_fields ) );

		foreach ( $all_fields as $field_key ) {
			if ( 'member_acf_fields' === $field_key && ! $this->is_acf_available() ) {
				continue;
			}

			$styles = $this->get_styles_for_visibility_field( $style_map, $field_key );

			if ( empty( $styles ) ) {
				continue;
			}

			$field_conditions = [
				'relation' => 'and',
				'terms'    => [
					$this->term_in( $style_control, $styles ),
				],
			];

			if ( 'vis_popup_' === $prefix && ! in_array( $field_key, $core_fields, true ) ) {
				$field_conditions['terms'][] = [
					'relation' => 'or',
					'terms'    => [
						$this->term_is_not( 'popup_style', 'style-six' ),
						$this->term_is( 'gs_teammembers_pop_clm', 'one' ),
					],
				];
			}

			$this->add_control(
				$prefix . $field_key,
				[
					'label'       => $this->get_visibility_field_label( $field_key ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'options'     => $device_options,
					'default'     => $device_default,
					'conditions'  => $field_conditions,
				]
			);
		}

		$this->end_controls_section();

	}

	protected function register_style_controls() {

		$options = $this->get_builder_options();

		$this->start_controls_section(
			'style_layout_section',
			[
				'label' => $this->t( 'columns' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_select_control(
			'gs_team_cols',
			'visibility-desktop',
			$options['gs_team_cols'],
			[
				'condition' => [
					'gs_team_theme!' => [ 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme17', 'gs_tm_theme18' ],
				],
			]
		);

		$this->add_select_control(
			'gs_team_cols_tablet',
			'visibility-tablet',
			$options['gs_team_cols_tablet'],
			[
				'condition' => [
					'gs_team_theme!' => [ 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme17', 'gs_tm_theme18' ],
				],
			]
		);

		$this->add_select_control(
			'gs_team_cols_mobile_portrait',
			'visibility-large-mobile',
			$options['gs_team_cols_mobile_portrait'],
			[
				'condition' => [
					'gs_team_theme!' => [ 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme17', 'gs_tm_theme18' ],
				],
			]
		);

		$this->add_select_control(
			'gs_team_cols_mobile',
			'visibility-mobile',
			$options['gs_team_cols_mobile'],
			[
				'condition' => [
					'gs_team_theme!' => [ 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme17', 'gs_tm_theme18' ],
				],
			]
		);

		$this->end_controls_section();

		$this->register_typography_style_section( 'gs_tm_name_typography', 'gs-tm-name-typography' );

		if ( gtm_fs()->is_paying_or_trial() ) {
			$this->register_typography_style_section( 'gs_tm_role_typography', 'gs-tm-role-typography' );
			$this->register_typography_style_section( 'gs_tm_details_typography', 'gs-tm-details-typography' );
			$this->register_typography_style_section( 'gs_tm_info_typography', 'gs-tm-info-typography' );
			$this->register_typography_style_section( 'gs_tm_ribbon_typography', 'gs-tm-ribbon-typography' );
		}

		$carousel_active = $this->get_carousel_active_conditions();

		$this->start_controls_section(
			'style_carousel_section',
			[
				'label'      => $this->t( 'carousel_enabled' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => $carousel_active,
			]
		);

		$this->add_on_off_control( 'carousel_navs_enabled', 'carousel_navs_enabled' );
		$this->add_on_off_control( 'carousel_dots_enabled', 'carousel_dots_enabled' );

		$this->add_select_control(
			'carousel_navs_style',
			'carousel_navs_style',
			$options['carousel_navs_style'],
			[
				'condition' => [
					'carousel_navs_enabled' => 'on',
				],
			]
		);

		$this->add_select_control(
			'carousel_dots_style',
			'carousel_dots_style',
			$options['carousel_dots_style'],
			[
				'condition' => [
					'carousel_dots_enabled' => 'on',
				],
			]
		);

		$this->add_color_control( 'gs_slider_nav_color', 'gs_slider_nav_color', [
			'condition' => [ 'carousel_navs_enabled' => 'on' ],
		] );
		$this->add_color_control( 'gs_slider_nav_bg_color', 'gs_slider_nav_bg_color', [
			'condition' => [ 'carousel_navs_enabled' => 'on' ],
		] );
		$this->add_color_control( 'gs_slider_nav_hover_color', 'gs_slider_nav_hover_color', [
			'condition' => [ 'carousel_navs_enabled' => 'on' ],
		] );
		$this->add_color_control( 'gs_slider_nav_hover_bg_color', 'gs_slider_nav_hover_bg_color', [
			'condition' => [ 'carousel_navs_enabled' => 'on' ],
		] );
		$this->add_color_control( 'gs_slider_dot_color', 'gs_slider_dot_color', [
			'condition' => [ 'carousel_dots_enabled' => 'on' ],
		] );
		$this->add_color_control( 'gs_slider_dot_hover_color', 'gs_slider_dot_hover_color', [
			'condition' => [
				'carousel_dots_enabled' => 'on',
				'carousel_dots_style'   => 'style-one',
			],
		] );

		$this->end_controls_section();

		$filter_style_conditions = $this->get_filter_or_v2_conditions( [ 'gs_tm_theme9', 'gs_tm_theme12' ] );

		$this->start_controls_section(
			'style_filter_section',
			[
				'label'      => $this->t( 'filter_style' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => $filter_style_conditions,
			]
		);

		$this->add_select_control( 'filter_style', 'filter_style', $options['filter_style'] );
		$this->add_color_control( 'filter_text_color', 'filter_text_color' );
		$this->add_color_control( 'filter_active_text_color', 'filter_active_text_color' );

		$this->add_color_control( 'filter_bg_color', 'filter_bg_color', [
			'condition' => [
				'filter_style' => [ 'style-four', 'style-five' ],
			],
		] );

		$this->add_color_control( 'filter_active_bg_color', 'filter_active_bg_color', [
			'condition' => [
				'filter_style!' => 'style-one',
			],
		] );

		$this->add_color_control( 'filter_border_color', 'filter_border_color', [
			'condition' => [
				'filter_style' => [ 'default', 'style-three' ],
			],
		] );

		$this->add_color_control( 'filter_active_border_color', 'filter_active_border_color', [
			'condition' => [
				'filter_style' => [ 'default', 'style-three' ],
			],
		] );

		$this->end_controls_section();

		$this->start_controls_section(
			'style_colors_section',
			[
				'label' => $this->t( 'style-settings' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_select_control( 'image_filter', 'image_filter', $options['image_filter'] );
		$this->add_select_control( 'hover_image_filter', 'hover_image_filter', $options['hover_image_filter'] );
		$this->add_color_control( 'description_link_color', 'description-link-color' );
		$this->add_color_control( 'info_icon_color', 'info-icon-color' );

		$this->add_color_control( 'tm_bg_color', 'tm-bg-color', [
			'condition' => [
				'gs_team_theme!' => $this->get_no_bg_color_themes(),
			],
		] );

		$this->add_color_control( 'tm_bg_color_hover', 'tm-bg-color-hover', [
			'condition' => [
				'gs_team_theme!' => $this->get_no_bg_color_themes(),
			],
		] );

		$this->add_color_control( 'gs_tm_info_background', 'info-bg-color', [
			'condition' => [
				'gs_team_theme' => $this->get_info_bg_color_themes(),
			],
		] );

		$this->add_color_control( 'gs_tm_mname_background', 'name-bg-color', [
			'condition' => [
				'gs_team_theme' => [ 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19' ],
			],
		] );

		$this->add_color_control( 'gs_tm_tooltip_background', 'tooltip-bg-color', [
			'condition' => [
				'gs_team_theme' => 'gs_tm_grid2',
			],
		] );

		$this->add_color_control( 'gs_tm_hover_icon_background', 'hover-icon-bg-color', [
			'condition' => [
				'gs_team_theme' => [ 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19' ],
			],
		] );

		$this->add_color_control( 'gs_tm_ribon_color', 'ribon-background-color', [
			'conditions' => [
				'relation' => 'and',
				'terms'    => [
					$this->term_not_in( 'gs_team_theme', [ 'gs_tm_theme3' ] ),
				],
			],
		] );

		$this->add_color_control( 'gs_tm_arrow_color', 'popup-arrow-color', [
			'conditions' => $this->get_popup_enabled_conditions(),
		] );

		$this->end_controls_section();

	}

	protected function register_typography_style_section( $group_name, $label_key ) {

		$selector = $this->get_typography_selector( $group_name );

		$this->start_controls_section(
			$group_name . '_section',
			[
				'label' => $this->t( $label_key ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'        => $group_name,
				'label'       => $this->t( $label_key ),
				'description' => $this->t( $label_key . '--help' ),
				'selector'    => $selector,
				'global'      => [
					'active' => false,
				],
				'fields_options' => [
					'__all' => [
						'render_type' => 'ui',
					],
				],
			]
		);

		$this->add_control(
			$group_name . '_color',
			[
				'label'       => __( 'Color', 'gsteam' ),
				'type'        => Controls_Manager::COLOR,
				'render_type' => 'ui',
				'global'      => [
					'active' => false,
				],
				'selectors'   => [
					$selector => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$group_name . '_hover_color',
			[
				'label'       => __( 'Hover Color', 'gsteam' ),
				'type'        => Controls_Manager::COLOR,
				'render_type' => 'ui',
				'global'      => [
					'active' => false,
				],
				'selectors'   => [
					$this->get_typography_hover_selector( $group_name ) => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function get_typography_area_selector() {
		return '{{WRAPPER}} #gs_team_area_' . self::SETTINGS_ID_PREFIX . '{{ID}}';
	}

	protected function get_typography_selector( $group_name ) {

		$area = $this->get_typography_area_selector();

		$map = [
			'gs_tm_name_typography'    => $area . ' .single-member-div .gs-member-name, ' . $area . ' .single-member-div .gs-member-name a',
			'gs_tm_role_typography'    => $area . ' .single-member-div .gs-member-desig',
			'gs_tm_details_typography' => $area . ' .single-member-div .gs-member-desc',
			'gs_tm_info_typography'    => $area . ' .single-member-div .gs-member-contact, ' . $area . ' .single-member-div .gs-member-address',
			'gs_tm_ribbon_typography'  => $area . ' .single-member-div .gs_team_ribbon',
		];

		return isset( $map[ $group_name ] ) ? $map[ $group_name ] : $area;

	}

	protected function get_typography_hover_selector( $group_name ) {

		$area = $this->get_typography_area_selector();

		$map = [
			'gs_tm_name_typography'    => $area . ' .single-member-div .gs-member-name:hover, ' . $area . ' .single-member-div .gs-member-name a:hover, ' . $area . ' .single-member-div .single-member:hover .gs-member-name, ' . $area . ' .single-member-div .single-member:hover .gs-member-name a',
			'gs_tm_role_typography'    => $area . ' .single-member-div .gs-member-desig:hover, ' . $area . ' .single-member-div .single-member:hover .gs-member-desig',
			'gs_tm_details_typography' => $area . ' .single-member-div .gs-member-desc:hover, ' . $area . ' .single-member-div .single-member:hover .gs-member-desc',
			'gs_tm_info_typography'    => $area . ' .single-member-div .gs-member-contact:hover, ' . $area . ' .single-member-div .single-member:hover .gs-member-contact',
			'gs_tm_ribbon_typography'  => $area . ' .single-member-div .gs_team_ribbon:hover, ' . $area . ' .single-member-div .single-member:hover .gs_team_ribbon',
		];

		return isset( $map[ $group_name ] ) ? $map[ $group_name ] : $area;

	}

	protected function render() {

		$shortcode_settings = $this->convert_to_shortcode_settings( $this->get_settings_for_display() );
		$settings_id        = self::SETTINGS_ID_PREFIX . $this->get_id();

		Integration_Elementor::store_builder_widget_settings( $settings_id, $shortcode_settings );

		echo plugin()->shortcode->shortcode( [
			'id'      => $settings_id,
			'preview' => 'yes',
		] );

	}

	protected function convert_to_shortcode_settings( $elementor_settings ) {

		$defaults  = $this->get_builder_defaults();
		$shortcode = $defaults;
		$skip      = [
			'visibility_settings',
			'gs_tm_name_typography',
			'gs_tm_role_typography',
			'gs_tm_details_typography',
			'gs_tm_info_typography',
			'gs_tm_ribbon_typography',
		];
		$multi     = [
			'group',
			'exclude_group',
			'location',
			'specialty',
			'language',
			'gender',
			'include_extra_one',
			'include_extra_two',
			'include_extra_three',
			'include_extra_four',
			'include_extra_five',
		];

		foreach ( $defaults as $key => $default ) {
			if ( in_array( $key, $skip, true ) ) {
				continue;
			}

			if ( ! array_key_exists( $key, $elementor_settings ) ) {
				continue;
			}

			$value = $elementor_settings[ $key ];

			if ( in_array( $key, $multi, true ) ) {
				$shortcode[ $key ] = is_array( $value ) ? implode( ',', array_filter( array_map( 'strval', $value ) ) ) : $value;
				continue;
			}

			if ( 'on' === $default || 'off' === $default ) {
				$shortcode[ $key ] = ( 'on' === $value ) ? 'on' : 'off';
				continue;
			}

			$shortcode[ $key ] = $value;
		}

		$shortcode['gs_tm_name_typography'] = $this->convert_typography( $elementor_settings, 'gs_tm_name_typography' );

		if ( gtm_fs()->is_paying_or_trial() ) {
			$shortcode['gs_tm_role_typography']    = $this->convert_typography( $elementor_settings, 'gs_tm_role_typography' );
			$shortcode['gs_tm_details_typography'] = $this->convert_typography( $elementor_settings, 'gs_tm_details_typography' );
			$shortcode['gs_tm_info_typography']    = $this->convert_typography( $elementor_settings, 'gs_tm_info_typography' );
			$shortcode['gs_tm_ribbon_typography']  = $this->convert_typography( $elementor_settings, 'gs_tm_ribbon_typography' );
		}

		$shortcode['visibility_settings'] = $this->convert_visibility_settings( $elementor_settings, $shortcode );

		return $shortcode;

	}

	protected function convert_typography( $elementor_settings, $group_name ) {

		$typo = [];

		$family = isset( $elementor_settings[ $group_name . '_font_family' ] ) ? $elementor_settings[ $group_name . '_font_family' ] : '';
		if ( '' !== $family ) {
			$typo['font_family'] = $family;
		}

		$size = $this->extract_slider_value( isset( $elementor_settings[ $group_name . '_font_size' ] ) ? $elementor_settings[ $group_name . '_font_size' ] : '' );
		if ( '' !== $size && null !== $size ) {
			$typo['size'] = $size;
		}

		$weight = isset( $elementor_settings[ $group_name . '_font_weight' ] ) ? $elementor_settings[ $group_name . '_font_weight' ] : '';
		if ( '' !== $weight ) {
			$typo['weight'] = $weight;
		}

		$transform = isset( $elementor_settings[ $group_name . '_text_transform' ] ) ? $elementor_settings[ $group_name . '_text_transform' ] : '';
		if ( '' !== $transform ) {
			$typo['transform'] = $transform;
		}

		$style = isset( $elementor_settings[ $group_name . '_font_style' ] ) ? $elementor_settings[ $group_name . '_font_style' ] : '';
		if ( '' !== $style ) {
			$typo['style'] = $style;
		}

		$decoration = isset( $elementor_settings[ $group_name . '_text_decoration' ] ) ? $elementor_settings[ $group_name . '_text_decoration' ] : '';
		if ( '' !== $decoration ) {
			$typo['decoration'] = $decoration;
		}

		$line_height = $this->extract_slider_value( isset( $elementor_settings[ $group_name . '_line_height' ] ) ? $elementor_settings[ $group_name . '_line_height' ] : '' );
		if ( '' !== $line_height && null !== $line_height ) {
			$typo['line_height'] = $line_height;
		}

		$letter_spacing = $this->extract_slider_value( isset( $elementor_settings[ $group_name . '_letter_spacing' ] ) ? $elementor_settings[ $group_name . '_letter_spacing' ] : '' );
		if ( '' !== $letter_spacing && null !== $letter_spacing ) {
			$typo['letter_spacing'] = $letter_spacing;
		}

		$color = isset( $elementor_settings[ $group_name . '_color' ] ) ? $elementor_settings[ $group_name . '_color' ] : '';
		if ( '' !== $color ) {
			$typo['color'] = $color;
		}

		$hover_color = isset( $elementor_settings[ $group_name . '_hover_color' ] ) ? $elementor_settings[ $group_name . '_hover_color' ] : '';
		if ( '' !== $hover_color ) {
			$typo['hover_color'] = $hover_color;
		}

		return (object) $typo;

	}

	protected function extract_slider_value( $value ) {

		if ( ! is_array( $value ) ) {
			return $value;
		}

		if ( ! isset( $value['size'] ) || '' === $value['size'] || null === $value['size'] ) {
			return '';
		}

		$size = $value['size'];
		$unit = isset( $value['unit'] ) ? $value['unit'] : 'px';

		if ( $unit && 'px' !== $unit ) {
			return $size . $unit;
		}

		return $size;

	}

	protected function convert_visibility_settings( $elementor_settings, $shortcode_settings ) {

		$visibility = plugin()->builder->get_visibility_defaults(
			isset( $shortcode_settings['gs_team_theme'] ) ? $shortcode_settings['gs_team_theme'] : '',
			$shortcode_settings
		);

		$visibility['initial'] = $this->overlay_visibility_group(
			isset( $visibility['initial'] ) ? $visibility['initial'] : [],
			$elementor_settings,
			'vis_initial_'
		);

		$visibility['popup'] = $this->overlay_visibility_group(
			isset( $visibility['popup'] ) ? $visibility['popup'] : [],
			$elementor_settings,
			'vis_popup_'
		);

		$visibility['panel'] = $this->overlay_visibility_group(
			isset( $visibility['panel'] ) ? $visibility['panel'] : [],
			$elementor_settings,
			'vis_panel_'
		);

		$visibility['drawer'] = $this->overlay_visibility_group(
			isset( $visibility['drawer'] ) ? $visibility['drawer'] : [],
			$elementor_settings,
			'vis_drawer_'
		);

		return $visibility;

	}

	protected function overlay_visibility_group( $group, $elementor_settings, $prefix ) {

		foreach ( $group as $field_key => $field ) {
			$control_id = $prefix . $field_key;

			if ( ! array_key_exists( $control_id, $elementor_settings ) ) {
				continue;
			}

			$devices = (array) $elementor_settings[ $control_id ];

			$group[ $field_key ]['desktop']          = in_array( 'desktop', $devices, true );
			$group[ $field_key ]['tablet']           = in_array( 'tablet', $devices, true );
			$group[ $field_key ]['mobile_landscape'] = in_array( 'mobile_landscape', $devices, true );
			$group[ $field_key ]['mobile']           = in_array( 'mobile', $devices, true );
		}

		return $group;

	}

	protected function add_on_off_control( $id, $label_key, $args = [] ) {

		$defaults = $this->get_builder_defaults();
		$config   = [
			'label'        => $this->t( $label_key ),
			'description'  => $this->help( $label_key ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'On', 'gsteam' ),
			'label_off'    => __( 'Off', 'gsteam' ),
			'return_value' => 'on',
			'default'      => isset( $defaults[ $id ] ) ? $defaults[ $id ] : '',
		];

		if ( empty( $config['description'] ) ) {
			unset( $config['description'] );
		}

		$this->add_control( $id, array_merge( $config, $args ) );

	}

	protected function add_select_control( $id, $label_key, $options, $args = [] ) {

		$defaults = $this->get_builder_defaults();
		$config   = [
			'label'       => $this->t( $label_key ),
			'description' => $this->help( $label_key ),
			'type'        => Controls_Manager::SELECT,
			'options'     => $this->to_select_options( $options ),
			'default'     => isset( $defaults[ $id ] ) ? (string) $defaults[ $id ] : '',
			'label_block' => true,
		];

		if ( empty( $config['label'] ) && isset( $args['label'] ) ) {
			$config['label'] = $args['label'];
		}

		if ( empty( $config['description'] ) ) {
			unset( $config['description'] );
		}

		$this->add_control( $id, array_merge( $config, $args ) );

	}

	protected function add_select2_multiple_control( $id, $label_key, $options, $args = [] ) {

		$config = [
			'label'       => $this->t( $label_key ),
			'description' => $this->help( $label_key ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'label_block' => true,
			'options'     => $this->to_select_options( $options ),
			'default'     => [],
		];

		if ( empty( $config['description'] ) ) {
			unset( $config['description'] );
		}

		$this->add_control( $id, array_merge( $config, $args ) );

	}

	protected function add_number_control( $id, $label_key, $args = [] ) {

		$defaults = $this->get_builder_defaults();
		$config   = [
			'label'       => $this->t( $label_key ),
			'description' => $this->help( $label_key ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => isset( $defaults[ $id ] ) ? $defaults[ $id ] : '',
		];

		if ( empty( $config['description'] ) ) {
			unset( $config['description'] );
		}

		$this->add_control( $id, array_merge( $config, $args ) );

	}

	protected function add_text_control( $id, $label_key, $args = [] ) {

		$defaults = $this->get_builder_defaults();
		$config   = [
			'label'       => $this->t( $label_key ),
			'description' => $this->help( $label_key ),
			'type'        => Controls_Manager::TEXT,
			'default'     => isset( $defaults[ $id ] ) ? (string) $defaults[ $id ] : '',
			'label_block' => true,
		];

		if ( empty( $config['description'] ) ) {
			unset( $config['description'] );
		}

		$this->add_control( $id, array_merge( $config, $args ) );

	}

	protected function add_color_control( $id, $label_key, $args = [] ) {

		$config = [
			'label'  => $this->t( $label_key ),
			'type'   => Controls_Manager::COLOR,
			'global' => [
				'active' => false,
			],
		];

		$this->add_control( $id, array_merge( $config, $args ) );

	}

	protected function to_select_options( $items ) {

		$options = [];

		foreach ( (array) $items as $item ) {
			if ( ! is_array( $item ) || ! isset( $item['value'] ) ) {
				continue;
			}

			$options[ $item['value'] ] = isset( $item['label'] ) ? $item['label'] : $item['value'];
		}

		return $options;

	}

	protected function t( $key ) {

		$strings = $this->get_builder_strings();

		return isset( $strings[ $key ] ) ? $strings[ $key ] : '';

	}

	protected function help( $key ) {

		foreach ( [ $key . '__details', $key . '--details', $key . '--help', $key . '--des', $key . '_details', $key . '-details' ] as $try ) {
			$text = $this->t( $try );
			if ( $text ) {
				return $text;
			}
		}

		return '';

	}

	protected function get_builder_defaults() {

		if ( null === $this->builder_defaults ) {
			$this->builder_defaults = plugin()->builder->get_shortcode_default_settings();
		}

		return $this->builder_defaults;

	}

	protected function get_builder_options() {

		if ( null === $this->builder_options ) {
			$this->builder_options = plugin()->builder->get_shortcode_default_options();
		}

		return $this->builder_options;

	}

	protected function get_builder_strings() {

		if ( null === $this->builder_strings ) {
			$this->builder_strings = plugin()->builder->get_translation_srtings();
		}

		return $this->builder_strings;

	}

	protected function get_themes_v2() {
		return [ 'gs-grid-style-one', 'gs-grid-style-two', 'gs-grid-style-three', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-grid-style-six', 'gs-team-circle-one', 'gs-team-circle-two', 'gs-team-circle-three', 'gs-team-circle-four', 'gs-team-circle-five', 'gs-team-horizontal-one', 'gs-team-horizontal-two', 'gs-team-horizontal-three', 'gs-team-horizontal-four', 'gs-team-horizontal-five', 'gs-team-flip-one', 'gs-team-flip-two', 'gs-team-flip-three', 'gs-team-flip-four', 'gs-team-flip-five', 'gs-team-table-one', 'gs-team-table-two', 'gs-team-table-three', 'gs-team-table-four', 'gs-team-table-five', 'gs-team-list-style-one', 'gs-team-list-style-two', 'gs-team-list-style-three', 'gs-team-list-style-four', 'gs-team-list-style-five' ];
	}

	protected function get_themes_v2_carousel() {
		return array_values( array_filter( $this->get_themes_v2(), function( $theme ) {
			return false === strpos( $theme, 'table' ) && false === strpos( $theme, 'list' );
		} ) );
	}

	protected function get_themes_v2_filter() {
		return $this->get_themes_v2_carousel();
	}

	protected function get_old_carousel_themes() {
		return [ 'gs_tm_theme7' ];
	}

	protected function get_no_bg_color_themes() {
		return [ 'gs-grid-style-one', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-team-circle-one', 'gs-team-circle-two', 'gs-team-circle-three', 'gs-team-circle-four', 'gs-team-circle-five', 'gs-team-horizontal-one', 'gs-team-horizontal-three', 'gs-team-flip-one', 'gs-team-flip-two', 'gs-team-flip-three', 'gs-team-flip-four', 'gs-team-flip-five', 'gs-team-table-one', 'gs-team-table-two', 'gs-team-table-three', 'gs-team-table-four', 'gs-team-table-five', 'gs-team-list-style-four', 'gs-team-list-style-five', 'gs_tm_theme1', 'gs_tm_theme2', 'gs_tm_theme3', 'gs_tm_theme4', 'gs_tm_theme5', 'gs_tm_theme6', 'gs_tm_theme7', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme10', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme21_dense', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme24', 'gs_tm_theme25', 'gs_tm_grid2', 'gs_tm_drawer2' ];
	}

	protected function get_info_bg_color_themes() {
		return [ 'gs-grid-style-one', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-team-circle-three', 'gs-team-circle-four', 'gs-team-horizontal-one', 'gs-team-horizontal-three', 'gs-team-flip-one', 'gs-team-flip-two', 'gs-team-flip-three', 'gs-team-flip-four', 'gs-team-flip-five', 'gs_tm_drawer2', 'gs_tm_theme22', 'gs_tm_theme1', 'gs_tm_theme2', 'gs_tm_theme7', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme25' ];
	}

	protected function get_pagination_unsupported_themes() {
		return [ 'gs_tm_theme7', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme19', 'gs_tm_theme21', 'gs_tm_theme21_dense', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme24', 'gs_tm_theme25', 'gs_tm_drawer2' ];
	}

	protected function term_is( $name, $value ) {
		return [
			'name'     => $name,
			'operator' => '===',
			'value'    => $value,
		];
	}

	protected function term_is_not( $name, $value ) {
		return [
			'name'     => $name,
			'operator' => '!==',
			'value'    => $value,
		];
	}

	protected function term_in( $name, $values ) {
		return [
			'name'     => $name,
			'operator' => 'in',
			'value'    => array_values( $values ),
		];
	}

	protected function term_not_in( $name, $values ) {
		return [
			'name'     => $name,
			'operator' => '!in',
			'value'    => array_values( $values ),
		];
	}

	protected function get_filter_or_v2_conditions( $legacy_themes ) {
		return [
			'relation' => 'or',
			'terms'    => [
				$this->term_in( 'gs_team_theme', $legacy_themes ),
				[
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'filter_enabled', 'on' ),
						$this->term_in( 'gs_team_theme', $this->get_themes_v2_filter() ),
					],
				],
			],
		];
	}

	protected function get_carousel_active_conditions() {
		return [
			'relation' => 'or',
			'terms'    => [
				$this->term_in( 'gs_team_theme', $this->get_old_carousel_themes() ),
				[
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'carousel_enabled', 'on' ),
						$this->term_in( 'gs_team_theme', $this->get_themes_v2_carousel() ),
					],
				],
			],
		];
	}

	protected function get_pagination_conditions() {
		return [
			'relation' => 'and',
			'terms'    => [
				$this->term_is_not( 'carousel_enabled', 'on' ),
				$this->term_not_in( 'gs_team_theme', $this->get_pagination_unsupported_themes() ),
				[
					'relation' => 'or',
					'terms'    => [
						$this->term_is_not( 'filter_enabled', 'on' ),
						$this->term_is_not( 'gs_team_filter_type', 'normal-filter' ),
					],
				],
			],
		];
	}

	protected function get_popup_enabled_conditions() {
		return [
			'relation' => 'and',
			'terms'    => [
				$this->term_is( 'gs_member_name_is_linked', 'on' ),
				[
					'relation' => 'or',
					'terms'    => [
						$this->term_is( 'gs_member_link_type', 'popup' ),
						[
							'relation' => 'and',
							'terms'    => [
								$this->term_is_not( 'gs_member_link_type', 'single_page' ),
								$this->term_in( 'gs_team_theme', [ 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme12' ] ),
							],
						],
					],
				],
			],
		];
	}

	protected function get_popup_visibility_section_conditions() {
		return [
			'relation' => 'and',
			'terms'    => [
				$this->term_is( 'gs_member_name_is_linked', 'on' ),
				$this->term_is( 'gs_member_link_type', 'popup' ),
			],
		];
	}

	protected function get_panel_visibility_section_conditions() {
		return [
			'relation' => 'or',
			'terms'    => [
				$this->term_in( 'gs_team_theme', [ 'gs_tm_theme19' ] ),
				[
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'gs_member_name_is_linked', 'on' ),
						$this->term_is( 'gs_member_link_type', 'panel' ),
					],
				],
			],
		];
	}

	protected function get_drawer_visibility_section_conditions() {
		return [
			'relation' => 'or',
			'terms'    => [
				$this->term_in( 'gs_team_theme', [ 'gs_tm_theme13', 'gs_tm_drawer2' ] ),
				[
					'relation' => 'and',
					'terms'    => [
						$this->term_is( 'gs_member_name_is_linked', 'on' ),
						$this->term_is( 'gs_member_link_type', 'drawer' ),
					],
				],
			],
		];
	}

	protected function merge_conditions_and( $base, $extra_terms ) {

		if ( isset( $base['relation'] ) && 'and' === $base['relation'] ) {
			$base['terms'] = array_merge( $base['terms'], $extra_terms );
			return $base;
		}

		return [
			'relation' => 'and',
			'terms'    => array_merge( [ $base ], $extra_terms ),
		];

	}

	protected function get_visibility_device_options() {
		return [
			'desktop'          => $this->t( 'visibility-desktop' ),
			'tablet'           => $this->t( 'visibility-tablet' ),
			'mobile_landscape' => $this->t( 'visibility-large-mobile' ),
			'mobile'           => $this->t( 'visibility-mobile' ),
		];
	}

	protected function get_visibility_device_default() {
		return [ 'desktop', 'tablet', 'mobile_landscape', 'mobile' ];
	}

	protected function get_visibility_field_label( $field_key ) {

		$keys = plugin()->builder->get_visibility_field_translation_keys();

		if ( isset( $keys[ $field_key ] ) ) {
			$label = $this->t( $keys[ $field_key ] );
			if ( $label ) {
				return $label;
			}
		}

		return ucwords( str_replace( '_', ' ', $field_key ) );

	}

	protected function get_unique_theme_visibility_fields() {

		$fields = [];

		foreach ( plugin()->builder->get_theme_visibility_fields() as $theme_fields ) {
			$fields = array_merge( $fields, $theme_fields );
		}

		return array_values( array_unique( $fields ) );

	}

	protected function get_themes_for_visibility_field( $field_key ) {

		$themes = [];

		foreach ( plugin()->builder->get_theme_visibility_fields() as $theme => $fields ) {
			if ( in_array( $field_key, $fields, true ) ) {
				$themes[] = $theme;
			}
		}

		return $themes;

	}

	protected function get_styles_for_visibility_field( $style_map, $field_key ) {

		$styles = [];

		foreach ( $style_map as $style => $fields ) {
			if ( in_array( $field_key, $fields, true ) ) {
				$styles[] = $style;
			}
		}

		return $styles;

	}

	protected function is_acf_available() {

		$plugins = plugin()->builder->get_enabled_plugins();

		return in_array( 'advanced-custom-fields', $plugins, true );

	}

}
