<?php

/**
 *
 * @package   GS_Team
 * @author    GS Plugins <hello@gsplugins.com>
 * @license   GPL-2.0+
 * @link      https://www.gsplugins.com
 * @copyright 2016 GS Plugins
 *
 * @wordpress-plugin
 * Plugin Name:		GS Team Members
 * Plugin URI:		https://www.gsplugins.com/wordpress-plugins
 * Description:     Best Team Member Plugin for WordPress – easily showcase your team’s photos, names, designations, and social media profiles. Display team members anywhere on your site using a generated shortcode like [gsteam id=1] or via widgets. Perfect for adding a professional touch to your "About" or "Meet the Team" pages. Check <a href="https://team.gsplugins.com">GS Team PRO Demo</a> and <a href="https://docs.gsplugins.com/gs-team-members">Documentation</a>.
 * Version:         2.6.5
 * Author:       	GS Plugins
 * Author URI:      https://www.gsplugins.com
 * Text Domain:     gsteam
 * Domain Path:     /languages
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */
/**
 * Protect direct access
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Defining constants
 */
if ( !defined( 'GSTEAM_VERSION' ) ) {
    define( 'GSTEAM_VERSION', '2.6.5' );
}
if ( !defined( 'GSTEAM_MENU_POSITION' ) ) {
    define( 'GSTEAM_MENU_POSITION', 39 );
}
if ( !defined( 'GSTEAM_PLUGIN_DIR' ) ) {
    define( 'GSTEAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'GSTEAM_PLUGIN_URI' ) ) {
    define( 'GSTEAM_PLUGIN_URI', plugins_url( '', __FILE__ ) );
}
if ( !defined( 'GSTEAM_PLUGIN_FILE' ) ) {
    define( 'GSTEAM_PLUGIN_FILE', __FILE__ );
}
if ( !function_exists( 'gtm_fs' ) ) {
    // Create a helper function for easy SDK access.
    function gtm_fs() {
        global $gtm_fs;
        if ( !isset( $gtm_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_1851_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_1851_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $gtm_fs = fs_dynamic_init( array(
                'id'              => '1851',
                'slug'            => 'gs-team-members',
                'type'            => 'plugin',
                'public_key'      => 'pk_e88759b9ba026403ad505a5877eac',
                'is_premium'      => false,
                'premium_slug'    => 'gs-team-members-premium',
                'premium_suffix'  => '- Pro',
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'trial'           => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'has_affiliation' => 'selected',
                'menu'            => array(
                    'slug'       => 'edit.php?post_type=gs_team',
                    'first-path' => 'edit.php?post_type=gs_team&page=gs-team-plugins-help',
                    'support'    => false,
                ),
                'is_live'         => true,
            ) );
        }
        return $gtm_fs;
    }

    // Init Freemius.
    gtm_fs();
    // Signal that SDK was initiated.
    do_action( 'gtm_fs_loaded' );
}
if ( !gtm_fs()->is_paying_or_trial() ) {
    function gs_team_free_vs_pro_page() {
        add_submenu_page(
            'edit.php?post_type=gs_team',
            'Free Pro Trial',
            'Free Pro Trial',
            'delete_posts',
            gtm_fs()->get_trial_url()
        );
    }

    add_action( 'admin_menu', 'gs_team_free_vs_pro_page', 20 );
}
require_once GSTEAM_PLUGIN_DIR . 'includes/autoloader.php';
require_once GSTEAM_PLUGIN_DIR . 'includes/functions.php';
require_once GSTEAM_PLUGIN_DIR . 'includes/plugin.php';