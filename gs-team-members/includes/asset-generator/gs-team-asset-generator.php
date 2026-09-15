<?php
namespace GSTEAM;
use GSPLUGINS\GS_Asset_Generator_Base;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class GS_Team_Asset_Generator extends GS_Asset_Generator_Base {

	private static $instance = null;

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_assets_key() {
		return 'gs-team-members';
	}

	public function generateStyle( $selector, $selector_divi, $targets, $prop, $value ) {
		
		$selectors = [];

		if ( $targets === null ) return;

		if ( gettype($targets) !== 'array' ) $targets = [$targets];

		if ( !empty($selector_divi) && ( is_divi_active() || is_divi_editor() ) ) {
			foreach ( $targets as $target ) $selectors[] = $selector_divi . $target;
		}

		foreach ( $targets as $target ) $selectors[] = $selector . $target;

		echo wp_strip_all_tags( sprintf( '%s{%s:%s}', join(',', $selectors), $prop, $value ) );

	}

	public function generateCustomCss( $settings, $shortcodeID ) {

		ob_start();

		$selector = '#gs_team_area_' . $shortcodeID;
		$selector_divi = '#et-boc .et-l div ' . $selector;

		// Legacy name/role font settings (kept for backward compatibility)
		if ( !empty($settings['gs_tm_m_fz']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-name', ' .gs-member-name a', ' .gstm-panel-title'], 'font-size', $settings['gs_tm_m_fz'] . 'px' );
		}

		if ( !empty($settings['gs_tm_m_fntw']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-name', ' .gs-member-name a', ' .gstm-panel-title'], 'font-weight', $settings['gs_tm_m_fntw'] );
		}

		if ( !empty($settings['gs_tm_m_fnstyl']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-name', ' .gs-member-name a', ' .gstm-panel-title'], 'font-style', $settings['gs_tm_m_fnstyl'] );
		}

		if ( !empty($settings['gs_tm_mname_color']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-name', ' .gs-member-name a', ' .gstm-panel-title'], 'color', $settings['gs_tm_mname_color'] );
		}

		if ( !empty($settings['tm_bg_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member-div .single-member', 'background', $settings['tm_bg_color'] );
		}
		
		if ( !empty($settings['tm_bg_color_hover']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member-div .single-member:hover', 'background', $settings['tm_bg_color_hover'] );
		}

		if ( !empty($settings['gs_tm_ribon_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .gs_team_ribbon', 'background', $settings['gs_tm_ribon_color'] );
		}
		
		if ( !empty($settings['gs_tm_info_background']) ) {
			$targets = [' .info-card', ' .single-member-div .gs_member_info', ' .gridder .overlay-area .overlay', '.gs_tm_theme22 .tittle_container', '.gs_tm_theme1 .single-mem-desc-social', '.gs_tm_theme2 .single-mem-desc-social', '.gs_tm_theme7 .single-mem-desc-social', '.gs_tm_theme8 .gs_team_overlay', '.gs_tm_theme9 .gs_team_overlay', '.gs_tm_theme11 .gs_team_overlay', '.gs_tm_theme12 .gs_team_overlay', '.gs_tm_theme19 .gs_team_overlay', '.gs-team-circle-four .single-member-div .gs_team_image__overlay'];
			$this->generateStyle( $selector, $selector_divi, $targets, 'background', $settings['gs_tm_info_background'] );

			$targets = [' .single-member-div .gs_member_info > svg path', ' .single-member-div .single-mem-desc-social svg path'];
			$this->generateStyle( $selector, '', $targets, 'fill', $settings['gs_tm_info_background'] );
		}

		if ( !empty($settings['gs_tm_tooltip_background']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .staff-meta', 'background', $settings['gs_tm_tooltip_background'] );
			$this->generateStyle( $selector, $selector_divi, ' .staff-meta:after', 'border-top-color', $settings['gs_tm_tooltip_background'] );
		}
		
		if ( !empty($settings['gs_tm_mname_background']) ) {
			$targets = ['.gs_tm_theme8 .gs-member-name', '.gs_tm_theme9 .gs-member-name', '.gs_tm_theme11 .gs-member-name', '.gs_tm_theme12 .gs-member-name', '.gs_tm_theme19 .gs-member-name', ' .gstm-panel-title'];
			$this->generateStyle( $selector, $selector_divi, $targets, 'background-color', $settings['gs_tm_mname_background'] );
		}

		if ( !empty($settings['description_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member-div .gs_member_info .gs-member-desc', 'color', $settings['description_color'] );
		}

		if ( !empty($settings['description_link_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member-div .gs_member_info .gs-member-desc a', 'color', $settings['description_link_color'] );
		}

		if ( !empty($settings['info_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member-div .gs_member_info .gs-member-contact', 'color', $settings['info_color'] );
		}

		if ( !empty($settings['info_icon_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member-div .gs_member_info .gs-member-contact i', 'color', $settings['info_icon_color'] );
		}

		if ( !empty($settings['gs_tm_hover_icon_background']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .single-member .gs_team_overlay i', 'background-color', $settings['gs_tm_hover_icon_background'] );
		}

		// Legacy role font settings (kept for backward compatibility)
		if ( !empty($settings['gs_tm_role_fz']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-desig', ' .gstm-panel-desig'], 'font-size', $settings['gs_tm_role_fz'] . 'px' );
		}

		if ( !empty($settings['gs_tm_role_fntw']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-desig', ' .gstm-panel-desig'], 'font-weight', $settings['gs_tm_role_fntw'] );
		}

		if ( !empty($settings['gs_tm_role_fnstyl']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-desig', ' .gstm-panel-desig'], 'font-style', $settings['gs_tm_role_fnstyl'] );
		}

		if ( !empty($settings['gs_tm_role_color']) ) {
			$this->generateStyle( $selector, $selector_divi, [' .gs-member-desig', ' .gstm-panel-desig'], 'color', $settings['gs_tm_role_color'] );
		}

		// Apply typography settings (CSS variables)
		$this->apply_all_typography( $settings, $selector, $selector_divi );
		
		if ( !empty($settings['gs_slider_nav_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .owl-carousel .owl-nav [class*=owl-]', 'color', $settings['gs_slider_nav_color'] );
			$this->generateStyle( $selector, $selector_divi, ' .owl-carousel .owl-nav [class*=owl-] svg path', 'fill', $settings['gs_slider_nav_color'] );
		}
		
		if ( !empty($settings['gs_slider_nav_bg_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .owl-carousel .owl-nav [class*=owl-]', 'background', $settings['gs_slider_nav_bg_color'] );
		}
		
		if ( !empty($settings['gs_slider_nav_hover_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .owl-carousel .owl-nav [class*=owl-]:hover', 'color', $settings['gs_slider_nav_hover_color'] );
			$this->generateStyle( $selector, $selector_divi, ' .owl-carousel .owl-nav [class*=owl-]:hover svg path', 'fill', $settings['gs_slider_nav_hover_color'] );
		}
		
		if ( !empty($settings['gs_slider_nav_hover_bg_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .owl-carousel .owl-nav [class*=owl-]:hover', 'background', $settings['gs_slider_nav_hover_bg_color'] );
		}
		
		if ( !empty($settings['gs_slider_dot_color']) ) {
			
			$this->generateStyle( $selector, $selector_divi, ' .carousel-dots--style-one .owl-dots .owl-dot span', 'background', $settings['gs_slider_dot_color'] );

			$this->generateStyle( $selector, $selector_divi, ' .carousel-dots--style-two .owl-dots .owl-dot span', 'border-color', $settings['gs_slider_dot_color'] );
			$this->generateStyle( $selector, $selector_divi, ' .carousel-dots--style-two .owl-dots .owl-dot.active span', 'background', $settings['gs_slider_dot_color'] );

			$this->generateStyle( $selector, $selector_divi, ' .carousel-dots--style-three .owl-dots .owl-dot span', 'border-color', $settings['gs_slider_dot_color'] );
			$this->generateStyle( $selector, $selector_divi, ' .carousel-dots--style-three .owl-dots .owl-dot.active span', 'background', $settings['gs_slider_dot_color'] );

		}
		
		if ( !empty($settings['gs_slider_dot_hover_color']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .carousel-dots--style-one .owl-dots .owl-dot.active span', 'background', $settings['gs_slider_dot_hover_color'] );
		}


		if ( !empty($settings['gs_tm_filter_cat_pos']) ) {
			$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats', 'text-align', $settings['gs_tm_filter_cat_pos'] );
		}

		if ( !empty($settings['gs_tm_arrow_color']) ) {
			$targets = ['.mfp-gsteam .mfp-container .mfp-arrow', '.mfp-gsteam .mfp-container .mfp-arrow:hover'];
			$this->generateStyle( '', '', $targets, 'background-color', $settings['gs_tm_arrow_color'] . '!important' );
		}

		if ( !empty($settings['gs_desc_scroll_contrl']) && $settings['gs_desc_scroll_contrl'] && !empty($settings['gs_max_scroll_height']) ) {
			$gs_max_scroll_height = (int) $settings['gs_max_scroll_height'];
			if ( $gs_max_scroll_height ) {
				$targets = [
					sprintf( '.gs_team_popup_shortcode_%s .gs-team--scrollbar', $shortcodeID ),
					sprintf( '#gs_team_area_%s .gridder .gridder-show .gs-team--scrollbar', $shortcodeID )
				];
				$this->generateStyle( '', '', $targets, 'max-height', $gs_max_scroll_height . 'px !important' );
			}
		}

		if ( !empty($filter_style = $settings['filter_style']) ) {

			if ( !empty($settings['filter_text_color']) ) {
				$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats li:not(.active) a', 'color', $settings['filter_text_color'] . '!important' );
			}
	
			if ( !empty($settings['filter_active_text_color']) ) {
				$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats li.active a', 'color', $settings['filter_active_text_color'] . '!important' );
			}
	
			if ( !empty($settings['filter_bg_color']) && in_array($filter_style, ['style-four', 'style-five']) ) {
				$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats li:not(.active) a', 'background-color', $settings['filter_bg_color'] . '!important' );
			}
	
			if ( !empty($settings['filter_active_bg_color']) && $filter_style !== 'style-one' ) {
				$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats li.active a', 'background-color', $settings['filter_active_bg_color'] . '!important' );
			}
	
			if ( !empty($settings['filter_border_color']) && in_array($filter_style, ['default', 'style-three']) ) {
				$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats li:not(.active) a', 'border-color', $settings['filter_border_color'] . '!important' );
			}
	
			if ( !empty($settings['filter_active_border_color']) && in_array($filter_style, ['default', 'style-three']) ) {
				$this->generateStyle( $selector, $selector_divi, ' .gs-team-filter-cats li.active a', 'border-color', $settings['filter_active_border_color'] . '!important' );
			}

		}

		return ob_get_clean();
	}

	/**
	 * Normalize a value with unit only when appropriate.
	 */
	private function unitize( $value, $unit ) {
		if ( ! $unit ) return $value;

		if ( preg_match( '/[a-z%]+$/i', (string) $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return $value . $unit;
		}

		return $value;
	}

	/**
	 * Generic writer for typography settings (CSS variables + element properties).
	 */
	private function apply_typography_map( array $settings, $selector, $selector_divi, array $map ) {
		foreach ( $map as $block ) {
			$setting_key = $block['setting'];
			$values = isset( $settings[ $setting_key ] ) ? (array) $settings[ $setting_key ] : [];
			if ( empty( $values ) ) continue;

			$prefix          = $block['prefix'];
			$var_targets     = (array) ( $block['targets'] ?? [ '' ] );
			$element_targets = (array) ( $block['elements'] ?? [] );
			$color_elements  = (array) ( $block['color_elements'] ?? [] );
			$props           = (array) ( $block['props'] ?? [] );

			foreach ( $props as $sourceKey => $def ) {
				if ( ! array_key_exists( $sourceKey, $values ) || $values[ $sourceKey ] === '' || $values[ $sourceKey ] === null ) {
					continue;
				}
				[ $suffix, $unit, $css_prop ] = array_pad( $def, 3, null );
				$val = $this->unitize( $values[ $sourceKey ], $unit );

				// Write CSS variable on the shortcode container
				foreach ( $var_targets as $tgt ) {
					$this->generateStyle(
						$selector,
						$selector_divi,
						$tgt,
						$prefix . $suffix,
						$val
					);
				}

				// Apply property directly to elements so theme hardcoded styles are overridden
				if ( ! empty( $element_targets ) && $css_prop ) {
					$this->generateStyle(
						$selector,
						$selector_divi,
						$element_targets,
						$css_prop,
						$val
					);
				}

				// Color-only targets (e.g. Font Awesome icons with theme-hardcoded colors)
				if ( ! empty( $color_elements ) && 'color' === $css_prop ) {
					$this->generateStyle(
						$selector,
						$selector_divi,
						$color_elements,
						'color',
						$val
					);
				}
			}
		}
	}

	/**
	 * Apply all typography settings as CSS variables + element styles.
	 * Scoped to main theme cards (.single-member-div) — excludes popups, panels, drawers.
	 */
	private function apply_all_typography( array $settings, $selector, $selector_divi ) {
		$common_props = [
			'color'          => [ '-color', '', 'color' ],
			'hover_color'    => [ '-hover-color', '', null ], // hover handled separately below
			'style'          => [ '-style', '', 'font-style' ],
			'decoration'     => [ '-text-decoration', '', 'text-decoration' ],
			'line_height'    => [ '-line-height', '', 'line-height' ],
			'letter_spacing' => [ '-letter-spacing', 'px', 'letter-spacing' ],
			'font_family'    => [ '-font-family', '', 'font-family' ],
			'weight'         => [ '-font-weight', '', 'font-weight' ],
			'transform'      => [ '-text-transform', '', 'text-transform' ],
			'size'           => [ '-font-size', 'px', 'font-size' ],
		];

		$map = [
			[
				'setting'  => 'gs_tm_name_typography',
				'prefix'   => '--gstm-name',
				'targets'  => [ '' ],
				'elements' => [ ' .single-member-div .gs-member-name', ' .single-member-div .gs-member-name a' ],
				'props'    => $common_props,
			],
		];

		if ( gtm_fs()->is_paying_or_trial() ) {
			$map = array_merge( $map, [
				[
					'setting'  => 'gs_tm_role_typography',
					'prefix'   => '--gstm-desig',
					'targets'  => [ '' ],
					'elements' => [ ' .single-member-div .gs-member-desig' ],
					'props'    => $common_props,
				],
				[
					'setting'  => 'gs_tm_details_typography',
					'prefix'   => '--gstm-desc',
					'targets'  => [ '' ],
					'elements' => [ ' .single-member-div .gs-member-desc' ],
					'props'    => $common_props,
				],
				[
					'setting'         => 'gs_tm_info_typography',
					'prefix'          => '--gstm-info',
					'targets'         => [ '' ],
					'elements'        => [
						' .single-member-div .gs-member-contact',
						' .single-member-div .gs-member-contact a',
						' .single-member-div .gs-member-address',
						' .single-member-div .gs-member-address a',
					],
					'color_elements'  => [
						' .single-member-div .gs-member-contact i',
						' .single-member-div .gs-member-address i',
					],
					'props'           => $common_props,
				],
				[
					'setting'  => 'gs_tm_ribbon_typography',
					'prefix'   => '--gstm-ribbon',
					'targets'  => [ '' ],
					'elements' => [ ' .single-member-div .gs_team_ribbon' ],
					'props'    => $common_props,
				],
				[
					'setting'  => 'gs_tm_readmore_typography',
					'prefix'   => '--gstm-readmore',
					'targets'  => [ '' ],
					'elements' => [ ' .single-member-div .gs-member-desc .gs-member-read-more' ],
					'props'    => $common_props,
				],
			] );
		}

		$this->apply_typography_map( $settings, $selector, $selector_divi, $map );

		// Hover colors
		$this->apply_typography_hover_colors( $settings, $selector, $selector_divi );
	}

	private function apply_typography_hover_colors( array $settings, $selector, $selector_divi ) {
		$hover_map = [
			'gs_tm_name_typography' => [
				'elements' => [
					' .single-member-div .gs-member-name:hover',
					' .single-member-div .gs-member-name a:hover',
					' .single-member-div .single-member:hover .gs-member-name',
					' .single-member-div .single-member:hover .gs-member-name a',
				],
			],
		];

		if ( gtm_fs()->is_paying_or_trial() ) {
			$hover_map['gs_tm_role_typography'] = [
				'elements' => [
					' .single-member-div .gs-member-desig:hover',
					' .single-member-div .single-member:hover .gs-member-desig',
				],
			];
			$hover_map['gs_tm_details_typography'] = [
				'elements' => [
					' .single-member-div .gs-member-desc:hover',
					' .single-member-div .single-member:hover .gs-member-desc',
				],
			];
			$hover_map['gs_tm_info_typography'] = [
				'elements' => [
					' .single-member-div .gs-member-contact:hover',
					' .single-member-div .gs-member-contact:hover a',
					' .single-member-div .gs-member-contact:hover i',
					' .single-member-div .gs-member-address:hover',
					' .single-member-div .gs-member-address:hover a',
					' .single-member-div .gs-member-address:hover i',
					' .single-member-div .single-member:hover .gs-member-contact',
					' .single-member-div .single-member:hover .gs-member-contact a',
					' .single-member-div .single-member:hover .gs-member-contact i',
					' .single-member-div .single-member:hover .gs-member-address',
					' .single-member-div .single-member:hover .gs-member-address a',
					' .single-member-div .single-member:hover .gs-member-address i',
				],
			];
			$hover_map['gs_tm_ribbon_typography'] = [
				'elements' => [
					' .single-member-div .gs_team_ribbon:hover',
					' .single-member-div .single-member:hover .gs_team_ribbon',
				],
			];
			$hover_map['gs_tm_readmore_typography'] = [
				'elements' => [
					' .single-member-div .gs-member-desc .gs-member-read-more:hover',
					' .single-member-div .single-member:hover .gs-member-desc .gs-member-read-more',
				],
			];
		}

		foreach ( $hover_map as $setting_key => $block ) {
			$values = isset( $settings[ $setting_key ] ) ? (array) $settings[ $setting_key ] : [];
			if ( empty( $values['hover_color'] ) ) continue;

			$this->generateStyle(
				$selector,
				$selector_divi,
				$block['elements'],
				'color',
				$values['hover_color']
			);
		}
	}

	public function get_fonts_from_settings( $settings ) {

		$typography_keys = [
			'gs_tm_name_typography',
			'gs_tm_role_typography',
			'gs_tm_details_typography',
			'gs_tm_info_typography',
			'gs_tm_ribbon_typography',
			'gs_tm_readmore_typography',
		];

		$fonts = [];

		foreach ( $typography_keys as $key ) {
			if ( empty( $settings[ $key ] ) ) continue;
			$setting = (array) $settings[ $key ];
			if ( ! empty( $setting['font_family'] ) ) {
				$fonts[] = $setting['font_family'];
			}
		}

		return array_unique( $fonts );
	}

	private function build_google_fonts_url( array $fonts ): string {
		$families = array_unique(
			array_filter(
				array_map(
					static function ( $f ) {
						$f = trim( (string) $f );
						return $f === '' ? '' : preg_replace( '/\s+/', '+', $f );
					},
					$fonts
				)
			)
		);

		$system_fonts = [ 'Arial', 'Georgia', 'Helvetica', 'Tahoma', 'Times+New+Roman', 'Trebuchet+MS', 'Verdana' ];
		$families = array_diff( $families, $system_fonts );

		if ( empty( $families ) ) {
			return '';
		}

		$query = implode( '&', array_map( static function( $f ) { return "family={$f}"; }, $families ) );
		$url   = "https://fonts.googleapis.com/css2?{$query}&display=swap";

		return set_url_scheme( $url, 'https' );
	}

	public function load_typography_fonts( $fonts ) : void {
		$url = $this->build_google_fonts_url( (array) $fonts );
		if ( $url ) {
			wp_enqueue_style( 'gs-team-typography-fonts-' . md5( $url ), $url, [], null );
		}
	}

	public function generate_assets_data( Array $settings ) {

		if ( empty($settings) || !empty($settings['is_preview']) ) return;

		$theme 						= $settings['gs_team_theme'];
		$link_type 					= $settings['gs_member_link_type'];
		$member_name_linked 		= $settings['gs_member_name_is_linked'];
		$enable_scroll_animation 	= $settings['enable_scroll_animation'];

		$carousel_enabled 	= $settings['carousel_enabled'];
		$filter_enabled 	= $settings['filter_enabled'];

		$_carousel_enabled 	= $carousel_enabled == 'on';
		$_filter_enabled 	= $filter_enabled == 'on';

		$_filter_enabled 	= ! $_carousel_enabled && $_filter_enabled;
		$_drawer_enabled 	= false;
		$_panel_enabled 	= false;

		if ( in_array( $theme, ['gs_tm_theme7'] ) ) {
			$_filter_enabled = false;
			$_carousel_enabled = true;
		}

		if ( in_array( $theme, ['gs_tm_theme9', 'gs_tm_theme12', 'gs_tm_theme21_dense', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ) {
			$_carousel_enabled = false;
			$_filter_enabled = true;
		}

		if ( $member_name_linked == 'on' ) {
			$_drawer_enabled = ( ! $_carousel_enabled && ! $_filter_enabled && $link_type == 'drawer' );
			$_panel_enabled = $link_type == 'panel';
		}
		
		if ( in_array( $theme, ['gs_tm_theme13', 'gs_tm_drawer2'] ) ) {
			$_drawer_enabled = true;
		}
		
		if ( in_array( $theme, ['gs_tm_theme19'] ) ) {
			$_panel_enabled = true;
			$_filter_enabled = true;
		}

		if ( ! gtm_fs()->is_paying_or_trial() && in_array($link_type, ['panel', 'drawer']) ) {
			$link_type = 'default';
		}

		if ( $link_type == 'default' ) $link_type = 'single_page';
		if ( $member_name_linked != 'on' ) $link_type = '';

		$_popup_enabled = $link_type == 'popup';

		$scroll_animation = false;
		$load_font_awesome = false;

		$this->add_item_in_asset_list( 'styles', 'gs-team-public' );
		$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['jquery'] );
		
		$themes = ['gs-grid-style-', 'gs-team-circle-', 'gs-team-horizontal-', 'gs-team-flip-', 'gs-team-table-', 'gs-team-list-style-'];

		if ( str_contains( json_encode($themes), substr( $theme, 0, strrpos( $theme, '-' ) + 1 ) ) ) {
			$load_font_awesome = true;
		}

		if ( in_array( $theme, ['gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme12'] ) ) {
			$_popup_enabled = true;
		}
		
		if ( $_popup_enabled ) {
			$load_font_awesome = true;
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-magnific-popup'] );
			$this->add_item_in_asset_list( 'styles', 'gs-team-public', ['gs-magnific-popup'] );
		}
		
		if ( $scroll_animation && $enable_scroll_animation == 'on' ) {
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-cpb-scroller'] );
		}
		
		if ( $load_font_awesome ) {
			$this->add_item_in_asset_list( 'styles', 'gs-team-public', ['gs-font-awesome-5'] );
		}
		
		if ( $_carousel_enabled ) {
			$this->add_item_in_asset_list( 'styles', 'gs-team-public', ['gs-owl-carousel'] );
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-owl-carousel'] );
		}
		
		if ( $_drawer_enabled ) {
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-gridder'] );
		}
		
		if ( $_filter_enabled ) {
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-isotope'] );
		}
		
		if ( $_panel_enabled ) {
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-jquery-panelslider'] );
		}
		
		if ( $theme == 'gs_tm_theme23' ) {
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-jquery-flip'] );
		}
		
		if ( $theme == 'gs_tm_theme21' || $theme == 'gs_tm_theme21_dense' ) {
			$this->add_item_in_asset_list( 'styles', 'gs-team-public', ['gs-bootstrap-table'] );
			$this->add_item_in_asset_list( 'scripts', 'gs-team-public', ['gs-bootstrap-table'] );
		}

		// Hooked for Pro if availabel
		// do_action( 'gs_team_assets_data_generated', $settings );

		if ( is_divi_active() ) {
			$this->add_item_in_asset_list( 'styles', 'gs-team-divi-public', ['gs-team-public'] );
		}

		$fonts = $this->get_fonts_from_settings( $settings );

		if ( ! empty( $fonts ) ) {
			$this->add_item_in_asset_list( 'fonts', 'google-fonts', $fonts );
		}

		$css = $this->get_shortcode_custom_css( $settings );

		if ( !empty($css) ) {
			$this->add_item_in_asset_list( 'styles', 'inline', minimize_css_simple($css) );
		}

	}

	public function is_builder_preview() {
		return plugin()->integrations->is_builder_preview();
	}

	public function enqueue_builder_preview_assets() {
		plugin()->scripts->wp_enqueue_style_all( 'public', ['gs-team-divi-public'] );
		plugin()->scripts->wp_enqueue_script_all( 'public' );
		$this->enqueue_prefs_custom_css();
	}
	
	public function enqueue_localize_script(){
		$ajax_url = admin_url('admin-ajax.php');
		$nonce = wp_create_nonce('gsteam_user_action');
		wp_localize_script( 'gs-team-public', 'GSTeamData', array( 'ajaxUrl' => $ajax_url, 'nonce' => $nonce ) );
	}

	public function maybe_force_enqueue_assets( Array $settings ) {

		$exclude = ['gs-team-divi-public'];
		if ( is_divi_active() ) $exclude = [];
		
		plugin()->scripts->wp_enqueue_style_all( 'public', $exclude );
		plugin()->scripts->wp_enqueue_script_all( 'public' );

		$this->enqueue_localize_script();

		add_fs_script( 'gs-team-public' );
		
		$this->print_google_fonts();

		// Typography fonts from shortcode settings
		$fonts = $this->get_fonts_from_settings( $settings );
		if ( ! empty( $fonts ) ) {
			$this->load_typography_fonts( $fonts );
		}

		// Shortcode Generated CSS
		$css = $this->get_shortcode_custom_css( $settings );
		$this->wp_add_inline_style( $css );
		
		// Prefs Custom CSS
		$this->enqueue_prefs_custom_css();

	}

	public function get_shortcode_custom_css( $settings ) {
		return $this->generateCustomCss( $settings, $settings['id'] );
	}

	public function get_prefs_custom_css() {
		$prefs = plugin()->builder->_get_shortcode_pref( false );
		if ( empty($prefs['gs_team_custom_css']) ) return '';
		return $prefs['gs_team_custom_css'];
	}

	public function enqueue_prefs_custom_css() {
		$this->wp_add_inline_style( $this->get_prefs_custom_css() );
	}

	public function wp_add_inline_style( $css ) {
		if ( !empty($css) ) $css = minimize_css_simple($css);
		if ( !empty($css) ) wp_add_inline_style( 'gs-team-public', wp_strip_all_tags($css) );
	}

	public function enqueue_plugin_assets( $main_post_id, $assets = [] ) {

		if ( empty($assets) || empty($assets['styles']) || empty($assets['scripts']) ) return;

		foreach ( $assets['styles'] as $asset => $data ) {
			if ( $asset == 'inline' ) {
				$this->wp_add_inline_style( $data );
			} else {
				Scripts::add_dependency_styles( $asset, $data );
			}
		}

		foreach ( $assets['scripts'] as $asset => $data ) {
			if ( $asset == 'inline' ) {
				if ( !empty($data) ) wp_add_inline_script( 'gs-team-public', $data );
			} else {
				Scripts::add_dependency_scripts( $asset, $data );
			}
		}

		wp_enqueue_style( 'gs-team-public' );
		wp_enqueue_script( 'gs-team-public' );

		$this->enqueue_localize_script();
		
		add_fs_script( 'gs-team-public' );

		$this->print_google_fonts();

		if ( ! empty( $assets['fonts'] ) ) {
			foreach ( (array) $assets['fonts'] as $fonts ) {
				$this->load_typography_fonts( (array) $fonts );
			}
		}

		if ( is_divi_active() ) {
			wp_enqueue_style( 'gs-team-divi-public' );
		}

		$this->enqueue_prefs_custom_css();

	}

	public function print_google_fonts() {

		$disable_google_fonts = getoption( 'disable_google_fonts', 'off' );

		if ( $disable_google_fonts === 'on' ) return;

		wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Spartan:wght@100;200;300;400;500;600;700;800;900&family=Work+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Comforter&display=swap', [], null );

	}

}

if ( ! function_exists( 'gsTeamAssetGenerator' ) ) {
	function gsTeamAssetGenerator() {
		return GS_Team_Asset_Generator::getInstance(); 
	}
}

// Must inilialized for the hooks
gsTeamAssetGenerator();