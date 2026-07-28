<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visibility settings helpers for shortcode builder.
 */
trait Visibility_Settings {

	public function get_theme_visibility_fields() {

		return [

			'gs-grid-style-one' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
			],
			'gs-grid-style-two' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
				'member_cell_phone',
			],
			'gs-grid-style-three' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs-grid-style-four' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs-grid-style-five' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs-grid-style-six' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],

			'gs-team-circle-one' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-circle-two' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_cell_phone',
			],
			'gs-team-circle-three' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
			],
			'gs-team-circle-four' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_cell_phone',
			],
			'gs-team-circle-five' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],

			'gs-team-horizontal-one' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-horizontal-two' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_cell_phone',
			],
			'gs-team-horizontal-three' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_ribbon',
				'member_featured_badge',
				'member_address',
			],
			'gs-team-horizontal-four' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-horizontal-five' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_email',
			],

			'gs-team-flip-one' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
				'member_cell_phone',
			],
			'gs-team-flip-two' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_email',
			],
			'gs-team-flip-three' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
				'member_address',
			],
			'gs-team-flip-four' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
				'member_cell_phone',
				'member_email',
			],
			'gs-team-flip-five' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],

			'gs-team-table-one' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-table-two' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-table-three' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-table-four' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-table-five' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],

			'gs-team-list-style-one' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs-team-list-style-two' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_cell_phone',
			],
			'gs-team-list-style-three' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_email',
			],
			'gs-team-list-style-four' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_email',
				'member_address',
			],
			'gs-team-list-style-five' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],

			'gs_tm_theme1' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme2' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_grid2' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
			],
			'gs_tm_theme20' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme10' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme_custom_10' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
			],
			'gs_tm_theme_custom_11' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme8' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme11' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme22' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
				'member_cell_phone',
				'member_email',
			],
			'gs_tm_theme9' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme7' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme12' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme24' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_cell_phone',
				'member_email',
				'member_vcard',
			],
			'gs_tm_theme19' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme13' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_drawer2' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme3' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme5' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme4' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme6' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],

			'gs_tm_theme23' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_ribbon',
				'member_featured_badge',
				'member_email',
				'member_land_phone',
				'member_cell_phone',
			],
			'gs_tm_theme14' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs_tm_theme15' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs_tm_theme16' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
			],
			'gs_tm_theme21' => [
				'member_name',
				'member_designation',
				'member_cell_phone',
				'member_email',
			],
			'gs_tm_theme21_dense' => [
				'member_name',
				'member_cell_phone',
				'member_email',
			],
			'gs_tm_theme17' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme18' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_ribbon',
				'member_featured_badge',
			],
			'gs_tm_theme25' => [
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_cell_phone',
				'member_email',
			],
		];
	}

	public function get_overlay_visibility_fields() {

		return [
			'member_thumbnail',
			'member_name',
			'member_designation',
			'member_details',
			'member_social',
			'member_skills',
			'member_company',
			'member_address',
			'member_land_phone',
			'member_cell_phone',
			'member_email',
			'member_location',
			'member_language',
			'member_specialty',
			'member_gender',
			'member_zip',
			'member_extra_one',
			'member_extra_two',
			'member_extra_three',
			'member_extra_four',
			'member_extra_five',
			'member_acf_fields',
		];
	}

	/**
	 * Fields each popup style template actually renders.
	 * Used by the Visibility tab so unused rows (e.g. Skills on style-one) are hidden.
	 */
	public function get_popup_style_visibility_fields() {

		$core = [
			'member_thumbnail',
			'member_name',
			'member_designation',
			'member_details',
			'member_social',
		];

		$ribbon = [
			'member_ribbon',
			'member_featured_badge',
		];

		$meta = [
			'member_company',
			'member_address',
			'member_land_phone',
			'member_cell_phone',
			'member_email',
			'member_location',
			'member_language',
			'member_specialty',
			'member_gender',
			'member_zip',
			'member_extra_one',
			'member_extra_two',
			'member_extra_three',
			'member_extra_four',
			'member_extra_five',
			'member_acf_fields',
		];

		$with_meta         = array_merge( $core, $ribbon, $meta );
		$with_meta_skills  = array_merge( $core, $ribbon, [ 'member_skills' ], $meta );
		// Styles without the ribon partial.
		$with_meta_no_ribbon        = array_merge( $core, $meta );
		$with_meta_skills_no_ribbon = array_merge( $core, [ 'member_skills' ], $meta );

		return [
			'default'     => $with_meta_skills_no_ribbon,
			'style-one'   => $with_meta,
			'style-two'   => $with_meta_skills,
			'style-three' => $with_meta_skills,
			'style-four'  => $with_meta,
			'style-five'  => $with_meta_skills_no_ribbon,
			// style-six one-column includes meta + skills; two-column is core only (filtered in Vue).
			'style-six'   => $with_meta_skills_no_ribbon,
		];
	}

	/**
	 * Fields each panel style template actually renders.
	 */
	public function get_panel_style_visibility_fields() {

		$core = [
			'member_thumbnail',
			'member_name',
			'member_designation',
			'member_details',
			'member_social',
		];

		$meta = [
			'member_company',
			'member_address',
			'member_land_phone',
			'member_cell_phone',
			'member_email',
			'member_location',
			'member_language',
			'member_specialty',
			'member_gender',
			'member_zip',
			'member_extra_one',
			'member_extra_two',
			'member_extra_three',
			'member_extra_four',
			'member_extra_five',
			'member_acf_fields',
		];

		$with_meta        = array_merge( $core, $meta );
		$with_skills      = array_merge( $core, [ 'member_skills' ] );
		$with_meta_skills = array_merge( $core, [ 'member_skills' ], $meta );

		return [
			'default'     => $with_meta_skills,
			'style-one'   => $with_meta,
			'style-two'   => $with_skills,
			'style-three' => $with_meta_skills,
			'style-four'  => $with_meta_skills,
			'style-five'  => $with_skills,
		];
	}

	/**
	 * Fields each drawer style template actually renders.
	 */
	public function get_drawer_style_visibility_fields() {

		$core = [
			'member_thumbnail',
			'member_name',
			'member_designation',
			'member_details',
			'member_social',
		];

		$meta = [
			'member_company',
			'member_address',
			'member_land_phone',
			'member_cell_phone',
			'member_email',
			'member_location',
			'member_language',
			'member_specialty',
			'member_gender',
			'member_zip',
			'member_extra_one',
			'member_extra_two',
			'member_extra_three',
			'member_extra_four',
			'member_extra_five',
			'member_acf_fields',
		];

		$with_meta        = array_merge( $core, $meta );
		$with_meta_skills = array_merge( $core, [ 'member_skills' ], $meta );

		// Default drawer has no thumbnail and no meta.
		$default = [
			'member_name',
			'member_designation',
			'member_details',
			'member_social',
			'member_skills',
		];

		// Style three has no thumbnail.
		$style_three = array_merge(
			[
				'member_name',
				'member_designation',
				'member_details',
				'member_social',
				'member_skills',
			],
			$meta
		);

		// Style four has meta but no socials/skills.
		$style_four = array_merge(
			[
				'member_thumbnail',
				'member_name',
				'member_designation',
				'member_details',
			],
			$meta
		);

		return [
			'default'     => $default,
			'style-one'   => $with_meta,
			'style-two'   => $with_meta_skills,
			'style-three' => $style_three,
			'style-four'  => $style_four,
			'style-five'  => $with_meta_skills,
		];
	}

	public function get_visibility_field_translation_keys() {

		return [
			'member_thumbnail'      => 'visibility-member-thumbnail',
			'member_name'           => 'visibility-member-name',
			'member_designation'    => 'visibility-member-designation',
			'member_details'        => 'visibility-member-details',
			'member_social'         => 'visibility-member-social',
			'member_ribbon'         => 'visibility-member-ribbon',
			'member_featured_badge' => 'visibility-member-featured-badge',
			'member_skills'         => 'visibility-member-skills',
			'member_company'        => 'visibility-member-company',
			'member_address'        => 'visibility-member-address',
			'member_land_phone'     => 'visibility-member-land-phone',
			'member_cell_phone'     => 'visibility-member-cell-phone',
			'member_email'          => 'visibility-member-email',
			'member_location'       => 'visibility-member-location',
			'member_language'       => 'visibility-member-language',
			'member_specialty'      => 'visibility-member-specialty',
			'member_gender'         => 'visibility-member-gender',
			'member_zip'            => 'visibility-member-zip',
			'member_extra_one'      => 'visibility-member-extra-one',
			'member_extra_two'      => 'visibility-member-extra-two',
			'member_extra_three'    => 'visibility-member-extra-three',
			'member_extra_four'     => 'visibility-member-extra-four',
			'member_extra_five'     => 'visibility-member-extra-five',
			'member_acf_fields'     => 'visibility-member-acf-fields',
			'member_vcard'          => 'visibility-member-vcard',
		];
	}

	public function get_visibility_legacy_key_map() {

		return [
			'member_name'        => 'gs_member_name',
			'member_designation' => 'gs_member_role',
			'member_details'     => 'gs_member_details',
			'member_social'      => 'gs_member_connect',
			'member_ribbon'      => 'display_ribbon',
		];
	}

	public function get_visibility_device_defaults( $visible = true ) {

		$visible = wp_validate_boolean( $visible );

		return [
			'desktop'          => $visible,
			'tablet'           => $visible,
			'mobile_landscape' => $visible,
			'mobile'           => $visible,
		];
	}

	public function get_visibility_field_defaults( $field_key, $legacy_settings = [] ) {

		$translation_keys = $this->get_visibility_field_translation_keys();
		$legacy_map       = $this->get_visibility_legacy_key_map();
		$visible          = true;

		if ( isset( $legacy_map[ $field_key ] ) ) {
			$legacy_key = $legacy_map[ $field_key ];
			if ( isset( $legacy_settings[ $legacy_key ] ) ) {
				$visible = ( $legacy_settings[ $legacy_key ] === 'on' || $legacy_settings[ $legacy_key ] === true || $legacy_settings[ $legacy_key ] === 1 || $legacy_settings[ $legacy_key ] === '1' );
			}
		}

		$defaults                    = $this->get_visibility_device_defaults( $visible );
		$defaults['translation_key'] = isset( $translation_keys[ $field_key ] ) ? $translation_keys[ $field_key ] : $field_key;

		return $defaults;
	}

	public function build_visibility_group( $field_keys, $legacy_settings = [] ) {

		$group = [];

		foreach ( (array) $field_keys as $field_key ) {
			$group[ $field_key ] = $this->get_visibility_field_defaults( $field_key, $legacy_settings );
		}

		return $group;
	}

	public function get_visibility_defaults( $theme = '', $legacy_settings = [] ) {

		$theme_fields = $this->get_theme_visibility_fields();

		if ( empty( $theme ) || ! isset( $theme_fields[ $theme ] ) ) {
			$theme = 'gs-grid-style-five';
		}

		$overlay_fields = $this->get_overlay_visibility_fields();

		return [
			'initial' => $this->build_visibility_group( $theme_fields[ $theme ], $legacy_settings ),
			'popup'   => $this->build_visibility_group( $overlay_fields, [] ),
			'panel'   => $this->build_visibility_group( $overlay_fields, [] ),
			'drawer'  => $this->build_visibility_group( $overlay_fields, [] ),
		];
	}

	public function validate_visibility_group( $settings, $field_defaults ) {

		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$validated = [];

		$existing_visible = false;
		foreach ( $settings as $existing_field ) {
			if ( ! is_array( $existing_field ) ) {
				continue;
			}
			if ( ! empty( $existing_field['desktop'] ) || ! empty( $existing_field['tablet'] )
				|| ! empty( $existing_field['mobile_landscape'] ) || ! empty( $existing_field['mobile'] ) ) {
				$existing_visible = true;
				break;
			}
		}
		$seed_new_fields_visible = empty( $settings ) || $existing_visible;

		foreach ( $field_defaults as $field_key => $defaults ) {
			$field = isset( $settings[ $field_key ] ) && is_array( $settings[ $field_key ] ) ? $settings[ $field_key ] : [];
			$is_new_field = ! isset( $settings[ $field_key ] ) || ! is_array( $settings[ $field_key ] );

			if ( isset( $field['translation_key'] ) ) {
				unset( $field['translation_key'] );
			}

			if ( $is_new_field && ! $seed_new_fields_visible ) {
				$defaults = array_merge( $defaults, $this->get_visibility_device_defaults( false ) );
			}

			$field = shortcode_atts( $defaults, $field );

			$field['desktop']          = wp_validate_boolean( $field['desktop'] );
			$field['tablet']           = wp_validate_boolean( $field['tablet'] );
			$field['mobile_landscape'] = wp_validate_boolean( $field['mobile_landscape'] );
			$field['mobile']           = wp_validate_boolean( $field['mobile'] );
			$field['translation_key']  = $defaults['translation_key'];

			$validated[ $field_key ] = $field;
		}

		// Keep stored keys not in current defaults (e.g. after theme switch).
		foreach ( $settings as $field_key => $field ) {
			if ( isset( $validated[ $field_key ] ) || ! is_array( $field ) ) {
				continue;
			}

			$defaults = $this->get_visibility_field_defaults( $field_key, [] );

			if ( isset( $field['translation_key'] ) ) {
				unset( $field['translation_key'] );
			}

			$field = shortcode_atts( $defaults, $field );

			$field['desktop']          = wp_validate_boolean( $field['desktop'] );
			$field['tablet']           = wp_validate_boolean( $field['tablet'] );
			$field['mobile_landscape'] = wp_validate_boolean( $field['mobile_landscape'] );
			$field['mobile']           = wp_validate_boolean( $field['mobile'] );
			$field['translation_key']  = $defaults['translation_key'];

			$validated[ $field_key ] = $field;
		}

		return $validated;
	}

	public function validate_visibility_settings( $settings, $theme = '', $legacy_settings = [] ) {

		$defaults = $this->get_visibility_defaults( $theme, $legacy_settings );

		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		return [
			'initial' => $this->validate_visibility_group( isset( $settings['initial'] ) ? $settings['initial'] : [], $defaults['initial'] ),
			'popup'   => $this->validate_visibility_group( isset( $settings['popup'] ) ? $settings['popup'] : [], $defaults['popup'] ),
			'panel'   => $this->validate_visibility_group( isset( $settings['panel'] ) ? $settings['panel'] : [], $defaults['panel'] ),
			'drawer'  => $this->validate_visibility_group( isset( $settings['drawer'] ) ? $settings['drawer'] : [], $defaults['drawer'] ),
		];
	}

	public function is_visibility_field_on( $field ) {

		if ( ! is_array( $field ) ) {
			return false;
		}

		return ! empty( $field['desktop'] ) || ! empty( $field['tablet'] ) || ! empty( $field['mobile_landscape'] ) || ! empty( $field['mobile'] );
	}

	public function sync_legacy_visibility_keys( $shortcode_settings ) {

		$legacy_map = $this->get_visibility_legacy_key_map();
		$initial    = isset( $shortcode_settings['visibility_settings']['initial'] ) ? $shortcode_settings['visibility_settings']['initial'] : [];

		foreach ( $legacy_map as $field_key => $legacy_key ) {
			if ( ! isset( $initial[ $field_key ] ) ) {
				continue;
			}

			$shortcode_settings[ $legacy_key ] = $this->is_visibility_field_on( $initial[ $field_key ] ) ? 'on' : 'off';
		}

		return $shortcode_settings;
	}

	public function force_initial_visibility_on( $shortcode_settings, $field_keys = [] ) {

		if ( empty( $field_keys ) ) {
			$field_keys = [ 'member_name', 'member_designation', 'member_details', 'member_social' ];
		}

		if ( ! isset( $shortcode_settings['visibility_settings']['initial'] ) || ! is_array( $shortcode_settings['visibility_settings']['initial'] ) ) {
			$shortcode_settings['visibility_settings']['initial'] = [];
		}

		foreach ( $field_keys as $field_key ) {
			$shortcode_settings['visibility_settings']['initial'][ $field_key ] = $this->get_visibility_field_defaults( $field_key, [ $this->get_visibility_legacy_key_map()[ $field_key ] ?? '' => 'on' ] );
			if ( isset( $this->get_visibility_legacy_key_map()[ $field_key ] ) ) {
				$shortcode_settings[ $this->get_visibility_legacy_key_map()[ $field_key ] ] = 'on';
			}
		}

		return $shortcode_settings;
	}
}
