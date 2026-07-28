<?php
/**
 * Divi 5 Integration – GS Team Members module.
 *
 * @package GSTEAM
 */

namespace GSTEAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots the native Divi 5 Team Members module.
 */
class Integration_Divi {

	/**
	 * Singleton instance.
	 *
	 * @var Integration_Divi|null
	 */
	private static $_instance = null;

	/**
	 * Get singleton.
	 *
	 * @return Integration_Divi
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Divi 5 only — no legacy Divi 4 module registration.
		add_action( 'divi_module_library_modules_dependency_tree', [ $this, 'register_module' ] );
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', [ $this, 'enqueue_visual_builder_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets_in_builder' ] );
	}

	/**
	 * Whether Divi 5 builder APIs are available.
	 *
	 * @return bool
	 */
	public static function is_divi_5() {
		return function_exists( 'et_builder_d5_enabled' ) && et_builder_d5_enabled();
	}

	/**
	 * Register module into Divi 5 dependency tree.
	 *
	 * @param object $dependency_tree Divi dependency tree.
	 * @return void
	 */
	public function register_module( $dependency_tree ) {
		if ( ! interface_exists( '\ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface' ) ) {
			return;
		}

		require_once GSTEAM_PLUGIN_DIR . 'includes/integrations/divi/TeamMembersModule.php';

		$dependency_tree->add_dependency( new \GSTEAM\Divi\TeamMembersModule() );
	}

	/**
	 * Register REST routes for Visual Builder preview.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		require_once GSTEAM_PLUGIN_DIR . 'includes/integrations/divi/TeamMembersModule.php';
		require_once GSTEAM_PLUGIN_DIR . 'includes/integrations/divi/TeamMembersController.php';

		\GSTEAM\Divi\TeamMembersController::register_routes();
	}

	/**
	 * Enqueue public CSS/JS inside Visual Builder so team layouts preview correctly.
	 *
	 * @return void
	 */
	public function enqueue_public_assets_in_builder() {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}

		if ( ! self::is_divi_5() ) {
			return;
		}

		plugin()->scripts->wp_enqueue_style_all( 'public' );
		plugin()->scripts->wp_enqueue_script_all( 'public' );
	}

	/**
	 * Enqueue Divi 5 Visual Builder package.
	 *
	 * @return void
	 */
	public function enqueue_visual_builder_assets() {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
			return;
		}

		if ( ! self::is_divi_5() ) {
			return;
		}

		if ( ! class_exists( '\ET\Builder\VisualBuilder\Assets\PackageBuildManager' ) ) {
			return;
		}

		require_once GSTEAM_PLUGIN_DIR . 'includes/integrations/divi/TeamMembersModule.php';
		require_once GSTEAM_PLUGIN_DIR . 'includes/integrations/divi/TeamMembersController.php';

		$script_path = GSTEAM_PLUGIN_DIR . 'includes/integrations/divi/visual-builder/build/gs-team-divi.js';
		$script_uri  = GSTEAM_PLUGIN_URI . '/includes/integrations/divi/visual-builder/build/gs-team-divi.js';

		if ( ! file_exists( $script_path ) ) {
			return;
		}

		$shortcode_options = \GSTEAM\Divi\TeamMembersModule::get_shortcode_options();
		$default_id        = (string) \GSTEAM\Divi\TeamMembersModule::get_default_shortcode_id();

		\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
			[
				'name'    => 'gs-team-divi-vb',
				'version' => GSTEAM_VERSION,
				'script'  => [
					'src'                => $script_uri,
					'deps'               => [
						'react',
						'jquery',
						'divi-module',
						'divi-module-library',
						'divi-vendor-wp-hooks',
						'divi-rest',
					],
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
					'data_app_window'    => [
						'shortcodeOptions' => $shortcode_options,
						'defaultShortcode' => $default_id,
						'iconUrl'          => GSTEAM_PLUGIN_URI . '/assets/img/icon.svg',
						'restNamespace'    => \GSTEAM\Divi\TeamMembersController::NAMESPACE,
						'i18n'             => [
							'loading'     => __( 'Loading team members…', 'gsteam' ),
							'empty'       => __( 'Please select a GS Team shortcode.', 'gsteam' ),
							'noShortcode' => __( 'No shortcodes found. Create one in GS Team first.', 'gsteam' ),
						],
					],
				],
			]
		);
	}
}
