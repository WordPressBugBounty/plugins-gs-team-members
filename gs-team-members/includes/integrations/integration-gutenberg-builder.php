<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gutenberg block that exposes the whole shortcode builder inline.
 *
 * Unlike Integration_Gutenberg (which only picks a saved shortcode by ID), this
 * block keeps every shortcode setting in its own block attributes, so nothing is
 * written to the shortcode table.
 */
class Integration_Gutenberg_Builder {

    const BLOCK_NAME = 'gsteam/team-members';

    const SCRIPT_HANDLE = 'gs-team-builder-block';

    /**
     * Prefix of the per instance key used for CSS scoping and AJAX lookups.
     */
    const INSTANCE_PREFIX = 'gstb_';

    private static $_instance = null;

    public static function get_instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
        // WP 7.1 always iframes the canvas; editor_assets stay in the parent
        // document, so public CSS/JS for the preview must use this hook.
        add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_assets' ] );
    }

    public function register_block() {

        wp_register_style(
            self::SCRIPT_HANDLE . '-editor',
            false,
            [],
            GSTEAM_VERSION
        );

        wp_add_inline_style( self::SCRIPT_HANDLE . '-editor', $this->get_inspector_css() );

        wp_register_script(
            self::SCRIPT_HANDLE,
            GSTEAM_PLUGIN_URI . '/includes/integrations/assets/gutenberg/gutenberg-builder-block.min.js',
            [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render' ],
            GSTEAM_VERSION,
            true
        );

        wp_localize_script( self::SCRIPT_HANDLE, 'gs_team_builder_block', $this->get_localized_data() );

        add_fs_script( self::SCRIPT_HANDLE );

        // block.json supplies the title, icon and description that WordPress.org
        // shows on the plugin page; attributes stay PHP-driven so they cannot
        // drift from the shortcode builder defaults.
        register_block_type( GSTEAM_PLUGIN_DIR . 'includes/integrations/assets/gutenberg/builder-block', [
            'editor_script'   => self::SCRIPT_HANDLE,
            'attributes'      => $this->get_block_attributes_schema(),
            'render_callback' => [ $this, 'render_block' ],
        ] );

    }

    /**
     * Inspector UI styles belong on the parent admin document (sidebar), not
     * only in the iframed canvas.
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_style( self::SCRIPT_HANDLE . '-editor' );
    }

    /**
     * Load the public team assets into the iframed editor canvas so the
     * ServerSideRender preview can be revealed and initialised.
     */
    public function enqueue_block_assets() {

        if ( ! is_admin() ) {
            return;
        }

        plugin()->scripts->wp_enqueue_style_all( 'public', [ 'gs-team-divi-public' ] );
        plugin()->scripts->wp_enqueue_script_all( 'public', [ 'gs-cpb-scroller' ] );

        add_fs_script( 'gs-team-public' );

    }

    /**
     * Block attribute schema derived from the shortcode builder defaults, so the
     * block and the builder can never drift apart.
     */
    public function get_block_attributes_schema() {

        $attributes = [
            'blockId' => [
                'type'    => 'string',
                'default' => ''
            ],
            'align' => [
                'type'    => 'string',
                'default' => 'wide'
            ]
        ];

        foreach ( $this->get_block_default_settings() as $setting_key => $default_value ) {
            $attributes[ $setting_key ] = $this->get_attribute_schema( $setting_key, $default_value );
        }

        return $attributes;
    }

    /**
     * Map a single builder default to a Gutenberg attribute definition.
     */
    protected function get_attribute_schema( $setting_key, $default_value ) {

        if ( 'visibility_settings' === $setting_key || false !== strpos( $setting_key, 'typography' ) ) {
            return [
                'type'    => 'object',
                'default' => is_object( $default_value ) ? (array) $default_value : $default_value
            ];
        }

        if ( is_array( $default_value ) ) {
            return [
                'type'    => 'array',
                'default' => $default_value
            ];
        }

        if ( is_int( $default_value ) || is_float( $default_value ) ) {
            return [
                'type'    => 'number',
                'default' => $default_value
            ];
        }

        if ( is_object( $default_value ) ) {
            return [
                'type'    => 'object',
                'default' => (array) $default_value
            ];
        }

        return [
            'type'    => 'string',
            'default' => (string) $default_value
        ];
    }

    /**
     * Defaults with typography objects cast to arrays for JSON / block attrs.
     */
    protected function get_block_default_settings() {

        $settings = plugin()->builder->get_shortcode_default_settings();

        $typography_keys = array_merge(
            plugin()->builder->get_typography_settings_config()['free'],
            plugin()->builder->get_typography_settings_config()['pro']
        );

        foreach ( $typography_keys as $key ) {
            if ( isset( $settings[ $key ] ) && is_object( $settings[ $key ] ) ) {
                $settings[ $key ] = (array) $settings[ $key ];
            }
        }

        return $settings;
    }

    /**
     * Render the block through the regular shortcode pipeline.
     */
    public function render_block( $block_attributes ) {

        // Divi shortcode modules saved before the block name was split still use
        // this block. Their selected id lives in Divi's attribute shape.
        if ( is_array( $block_attributes ) ) {
            $divi_shortcode_id = absint( $block_attributes['shortcode']['innerContent']['desktop']['value'] ?? 0 );

            if ( $divi_shortcode_id ) {
                return do_shortcode( sprintf( '[gsteam id="%u"]', $divi_shortcode_id ) );
            }
        }

        $settings = plugin()->builder->validate_shortcode_settings( (array) $block_attributes );

        $instance_key = $this->get_instance_key( $block_attributes );

        // The AJAX filter / load more / pagination handlers resolve a non numeric
        // shortcode id through a transient, so refresh it on every render.
        set_transient( $instance_key, $settings, DAY_IN_SECONDS );

        return plugin()->shortcode->shortcode( [
            'id'       => $instance_key,
            'settings' => $settings,
        ] );
    }

    /**
     * Stable, non numeric key for this block instance.
     */
    protected function get_instance_key( $block_attributes ) {

        $block_id = ! empty( $block_attributes['blockId'] ) ? sanitize_key( $block_attributes['blockId'] ) : '';

        if ( empty( $block_id ) ) {
            $block_id = self::INSTANCE_PREFIX . md5( maybe_serialize( $block_attributes ) );
        }

        // A numeric key would be treated as a saved shortcode id.
        if ( is_numeric( $block_id ) ) {
            $block_id = self::INSTANCE_PREFIX . $block_id;
        }

        return $block_id;
    }

    /**
     * Everything the editor UI needs, reusing the builder's own data providers.
     */
    public function get_localized_data() {

        $builder = plugin()->builder;

        return [
            'settings'                       => $this->get_block_default_settings(),
            'options'                        => $builder->get_shortcode_default_options(),
            'translations'                   => $builder->get_translation_srtings(),
            'taxonomy_settings'              => $builder->_get_taxonomy_settings( false ),
            'theme_visibility_fields'        => $builder->get_theme_visibility_fields(),
            'overlay_visibility_fields'      => $builder->get_overlay_visibility_fields(),
            'popup_style_visibility_fields'  => $builder->get_popup_style_visibility_fields(),
            'panel_style_visibility_fields'  => $builder->get_panel_style_visibility_fields(),
            'drawer_style_visibility_fields' => $builder->get_drawer_style_visibility_fields(),
            'visibility_legacy_key_map'      => $builder->get_visibility_legacy_key_map(),
            'visibility_translation_keys'    => $builder->get_visibility_field_translation_keys(),
            'fonts_data'                     => $builder->get_fonts_list(),
            'enabled_plugins'                => $builder->get_enabled_plugins(),
            'is_pro_active'                  => wp_validate_boolean( gtm_fs()->is_paying_or_trial() ),
            'instance_prefix'                => self::INSTANCE_PREFIX,
            'premium_url'                    => 'https://www.gsplugins.com/product/gs-team-members/#pricing',
            'labels'                         => [
                'block_title'          => __( 'GS Team Builder', 'gsteam' ),
                'block_description'    => __( 'Build a team members section with all layout and style options.', 'gsteam' ),
                'premium_notice'       => __( 'Available in the premium version.', 'gsteam' ),
                'premium_alert'        => __( 'This is a premium feature. Please upgrade the plan.', 'gsteam' ),
                'include_terms'        => __( 'Include', 'gsteam' ),
                'exclude_terms'        => __( 'Exclude', 'gsteam' ),
                'filter_panel'         => __( 'Filter & Pagination', 'gsteam' ),
                'slider_panel'         => __( 'Carousel & Autoplay', 'gsteam' ),
                'content_panel'        => __( 'Description & Content', 'gsteam' ),
                'layout_panel'         => __( 'Layout', 'gsteam' ),
                'linking_panel'        => __( 'Linking', 'gsteam' ),
                'thumbnail_panel'      => __( 'Thumbnail', 'gsteam' ),
                'featuring_panel'      => __( 'Featuring', 'gsteam' ),
                'acf_panel'            => __( 'ACF Fields', 'gsteam' ),
                'typography_panel'     => __( 'Typography', 'gsteam' ),
                'carousel_style_panel' => __( 'Carousel Navs & Dots', 'gsteam' ),
                'filter_style_panel'   => __( 'Filter Style', 'gsteam' ),
                'colors_panel'         => __( 'Colors', 'gsteam' ),
                'image_filter_panel'   => __( 'Image Filter', 'gsteam' ),
            ]
        ];
    }

    /**
     * Inspector sidebar styles (parent admin document).
     */
    public function get_inspector_css() {

        ob_start(); ?>

        /* Keep the four builder tabs inside the narrow inspector width. */
        .gsteam-builder-block--tabs {
            max-width: 100%;
            overflow-x: hidden;
        }

        .gsteam-builder-block--tabs .components-tab-panel__tabs {
            display: flex !important;
            flex-wrap: wrap;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
            border-bottom: 1px solid #e0e0e0;
        }

        .gsteam-builder-block--tabs .components-tab-panel__tabs-item,
        .gsteam-builder-block--tabs .components-tab-panel__tabs .components-button {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            max-width: 100%;
            justify-content: center;
            padding: 8px 2px !important;
            font-size: 11px !important;
            line-height: 1.25;
            white-space: normal !important;
            text-align: center;
            height: auto !important;
        }

        .gsteam-builder-block--tabs .components-tab-panel__tab-content {
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .gsteam-builder-block--tabs .components-panel__body,
        .gsteam-builder-block--tabs .components-base-control,
        .gsteam-builder-block--tabs .components-select-control {
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Custom selects must fill the inspector, not grow to option label length. */
        .gsteam-builder-block--tabs .components-select-control,
        .gsteam-builder-block--tabs .components-select-control .components-base-control__field,
        .gsteam-builder-block--tabs .gsteam-builder-block--select,
        .gsteam-builder-block--tabs .gsteam-builder-block--select .components-base-control__field {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .gsteam-builder-block--tabs .components-select-control select,
        .gsteam-builder-block--tabs select.components-select-control__input,
        .gsteam-builder-block--tabs .gsteam-builder-block--select select {
            display: block;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
        }

        .gsteam-builder-block--premium {
            margin: -8px 0 16px;
            font-size: 12px;
            color: #b26b00;
        }

        .gsteam-builder-block--tabs select option.gsteam-builder-block--premium-option {
            background-color: #e2e4e7;
            color: #757575;
        }

        .gsteam-builder-block--select-menu {
            position: relative;
        }

        .gsteam-builder-block--select-menu select.components-select-control__input {
            display: block;
            width: 100%;
        }

        .gsteam-builder-block--select-list {
            position: absolute;
            z-index: 30;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 220px;
            margin: 4px 0 0;
            padding: 4px 0;
            overflow: auto;
            list-style: none;
            background: #fff;
            border: 1px solid #8c8f94;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }

        .gsteam-builder-block--select-list li {
            margin: 0;
            padding: 0;
        }

        .gsteam-builder-block--select-list .gsteam-builder-block--select-row,
        .gsteam-builder-block--select-list button.gsteam-builder-block--select-row {
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: auto;
            min-height: 0;
            margin: 0;
            padding: 6px 12px;
            border: 0;
            border-radius: 0;
            background: #fff;
            box-shadow: none;
            color: #2c3338;
            font-family: inherit;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.4;
            text-align: left;
            white-space: nowrap;
            cursor: pointer;
        }

        button.gsteam-builder-block--select-row.is-selected,
        button.gsteam-builder-block--select-row:hover {
            background: #007cba;
            color: #fff;
        }

        button.gsteam-builder-block--select-row:disabled {
            background: #e2e4e7;
            color: #757575;
            cursor: default;
        }

        li.gsteam-builder-block--select-row.is-pro {
            background: #fff;
            color: #2c3338;
            cursor: pointer;
        }

        li.gsteam-builder-block--select-row.is-pro:hover {
            background: #007cba;
            color: #fff;
        }

        li.gsteam-builder-block--select-row.is-pro .gsteam-builder-block--pro-text {
            color: #2271b1;
            font-weight: 400;
            text-decoration: none;
            cursor: pointer;
        }

        li.gsteam-builder-block--select-row.is-pro:hover .gsteam-builder-block--pro-text {
            color: #fff;
        }

        .gsteam-builder-block--locked > *:first-child {
            pointer-events: none;
            opacity: 0.6;
        }

        .gsteam-builder-block--visibility {
            overflow: hidden;
            border: 1px solid #e6e8ee;
            border-radius: 8px;
            background: #fff;
        }

        .gsteam-builder-block--visibility-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) repeat(4, 32px);
            align-items: center;
            column-gap: 6px;
            min-height: 40px;
            margin: 0;
            padding: 6px 10px;
            border-top: 1px solid #eceef2;
        }

        .gsteam-builder-block--visibility-head {
            min-height: 44px;
            border-top: 0;
            background: #f4f5f7;
        }

        .gsteam-builder-block--visibility-label {
            margin: 0;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
            color: #1d2327;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.3;
            text-align: left;
            cursor: pointer;
        }

        button.gsteam-builder-block--visibility-label {
            height: auto;
            min-height: 0;
            font-weight: 400;
        }

        button.gsteam-builder-block--visibility-label:hover,
        button.gsteam-builder-block--visibility-label:focus {
            color: #1d2327;
            background: transparent;
            box-shadow: none;
        }

        .gsteam-builder-block--visibility-device {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 28px;
            border-radius: 6px;
            background: #e8eaef;
            color: #5c6370;
        }

        .gsteam-builder-block--visibility-device svg {
            display: block;
        }

        .gsteam-builder-block--visibility-check {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 28px;
            margin: 0;
            cursor: pointer;
        }

        .gsteam-builder-block--visibility-check input {
            position: absolute;
            width: 18px;
            height: 18px;
            margin: 0;
            opacity: 0;
            cursor: pointer;
        }

        .gsteam-builder-block--visibility-check span {
            display: block;
            box-sizing: border-box;
            width: 18px;
            height: 18px;
            border: 1.5px solid #c5c8d0;
            border-radius: 4px;
            background: #fff;
        }

        .gsteam-builder-block--visibility-check input:checked + span {
            border-color: #2563eb;
            background: #2563eb url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' d='M3.2 8.2l3 3.1 6.6-6.6'/%3E%3C/svg%3E") center / 12px 12px no-repeat;
        }

        .gsteam-builder-block--visibility-check input:focus-visible + span {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }

        .gsteam-builder-block--typography {
            margin-bottom: 16px;
            padding: 12px;
            background: #f6f7f7;
            border-radius: 2px;
        }

        .gsteam-builder-block--typography h3 {
            margin: 0 0 12px;
            font-size: 13px;
        }

        <?php return ob_get_clean();
    }

}
