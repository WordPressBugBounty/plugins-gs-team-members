<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Builder' ) ) {

    final class Builder {

        private $option_name = 'gs_team_shortcode_prefs';
        private $taxonomy_option_name = 'gs_team_taxonomy_settings';

        public function __construct() {
            
            add_action( 'admin_menu', array( $this, 'register_sub_menu') );
            add_action( 'admin_enqueue_scripts', array( $this, 'scripts') );
            add_action( 'wp_enqueue_scripts', array( $this, 'preview_scripts') );

            add_action( 'wp_ajax_gsteam_create_shortcode', array($this, 'create_shortcode') );
            add_action( 'wp_ajax_gsteam_clone_shortcode', array($this, 'clone_shortcode') );
            add_action( 'wp_ajax_gsteam_get_shortcode', array($this, 'get_shortcode') );
            add_action( 'wp_ajax_gsteam_update_shortcode', array($this, 'update_shortcode') );
            add_action( 'wp_ajax_gsteam_delete_shortcodes', array($this, 'delete_shortcodes') );
            add_action( 'wp_ajax_gsteam_temp_save_shortcode_settings', array($this, 'temp_save_shortcode_settings') );
            add_action( 'wp_ajax_gsteam_get_shortcodes', array($this, 'get_shortcodes') );

            add_action( 'wp_ajax_gsteam_get_shortcode_pref', array($this, 'get_shortcode_pref') );
            add_action( 'wp_ajax_gsteam_save_shortcode_pref', array($this, 'save_shortcode_pref') );

            add_action( 'wp_ajax_gsteam_get_taxonomy_settings', array($this, 'get_taxonomy_settings') );
            add_action( 'wp_ajax_gsteam_save_taxonomy_settings', array($this, 'save_taxonomy_settings') );

            add_action( 'template_include', array($this, 'populate_shortcode_preview') );
            add_action( 'show_admin_bar', array($this, 'hide_admin_bar_from_preview') );

            return $this;

        }

        public function is_preview() {

            return isset( $_REQUEST['gsteam_shortcode_preview'] ) && !empty($_REQUEST['gsteam_shortcode_preview']);

        }

        public function hide_admin_bar_from_preview( $visibility ) {

            if ( $this->is_preview() ) return false;

            return $visibility;

        }

        public function populate_shortcode_preview( $template ) {

            global $wp, $wp_query;
            
            if ( $this->is_preview() ) {

                // Create our fake post
                $post_id = rand( 1, 99999 ) - 9999999;
                $post = new \stdClass();
                $post->ID = $post_id;
                $post->post_author = 1;
                $post->post_date = current_time( 'mysql' );
                $post->post_date_gmt = current_time( 'mysql', 1 );
                $post->post_title = __('Shortcode Preview', 'gsteam');
                $post->post_content = '[gsteam preview="yes" id="' . esc_attr( sanitize_key( $_REQUEST['gsteam_shortcode_preview'] ) ) . '"]';
                $post->post_status = 'publish';
                $post->comment_status = 'closed';
                $post->ping_status = 'closed';
                $post->post_name = 'fake-page-' . rand( 1, 99999 ); // append random number to avoid clash
                $post->post_type = 'page';
                $post->filter = 'raw'; // important!


                // Convert to WP_Post object
                $wp_post = new \WP_Post( $post );


                // Add the fake post to the cache
                wp_cache_add( $post_id, $wp_post, 'posts' );


                // Update the main query
                $wp_query->post = $wp_post;
                $wp_query->posts = array( $wp_post );
                $wp_query->queried_object = $wp_post;
                $wp_query->queried_object_id = $post_id;
                $wp_query->found_posts = 1;
                $wp_query->post_count = 1;
                $wp_query->max_num_pages = 1; 
                $wp_query->is_page = true;
                $wp_query->is_singular = true; 
                $wp_query->is_single = false; 
                $wp_query->is_attachment = false;
                $wp_query->is_archive = false; 
                $wp_query->is_category = false;
                $wp_query->is_tag = false; 
                $wp_query->is_tax = false;
                $wp_query->is_author = false;
                $wp_query->is_date = false;
                $wp_query->is_year = false;
                $wp_query->is_month = false;
                $wp_query->is_day = false;
                $wp_query->is_time = false;
                $wp_query->is_search = false;
                $wp_query->is_feed = false;
                $wp_query->is_comment_feed = false;
                $wp_query->is_trackback = false;
                $wp_query->is_home = false;
                $wp_query->is_embed = false;
                $wp_query->is_404 = false; 
                $wp_query->is_paged = false;
                $wp_query->is_admin = false; 
                $wp_query->is_preview = false; 
                $wp_query->is_robots = false; 
                $wp_query->is_posts_page = false;
                $wp_query->is_post_type_archive = false;


                // Update globals
                $GLOBALS['wp_query'] = $wp_query;
                $wp->register_globals();


                include GSTEAM_PLUGIN_DIR . 'includes/shortcode-builder/preview.php';

                return;

            }

            return $template;

        }

        public function register_sub_menu() {

            add_submenu_page( 
                'edit.php?post_type=gs_team', 'Team Shortcode', 'Team Shortcode', 'publish_pages', 'gs-team-shortcode', array( $this, 'view' )
            );

            add_submenu_page( 
                'edit.php?post_type=gs_team', 'Preference', 'Preference', 'publish_pages', 'gs-team-shortcode#/preferences', array( $this, 'view' )
            );

            do_action( 'gs_after_shortcode_submenu' );

        }

        public function view() {

            include GSTEAM_PLUGIN_DIR . 'includes/shortcode-builder/page.php';

        }

        public static function get_team_terms( $term_name, $idsOnly = false ) {

            $taxonomies = get_taxonomies([ 'type' => 'restricted', 'enabled' => true ]);

            if ( ! in_array( $term_name, $taxonomies ) ) return [];

            $_terms = get_terms( $term_name, [
                'hide_empty' => false,
            ]);

            if ( empty($_terms) ) return [];
            
            if ( $idsOnly ) return wp_list_pluck( $_terms, 'term_id' );

            $terms = [];

            foreach ( $_terms as $term ) {
                $terms[] = [
                    'label' => $term->name,
                    'value' => $term->term_id
                ];
            }

            return $terms;

        }

        public function scripts( $hook ) {

            if ( 'gs_team_page_gs-team-shortcode' != $hook ) {
                return;
            }

            wp_register_style( 'gs-zmdi-fonts', GSTEAM_PLUGIN_URI . '/assets/libs/material-design-iconic-font/css/material-design-iconic-font.min.css', '', GSTEAM_VERSION, 'all' );

            wp_enqueue_style( 'gs-team-shortcode', GSTEAM_PLUGIN_URI . '/assets/admin/css/shortcode.min.css', array('gs-zmdi-fonts'), GSTEAM_VERSION, 'all' );

            $data = array(
                "nonce"    => wp_create_nonce( "_gsteam_admin_nonce_gs_" ),
                "ajaxurl"  => admin_url( "admin-ajax.php" ),
                "adminurl" => admin_url(),
                "siteurl"  => home_url()
            );

            $data['shortcode_settings'] = $this->get_shortcode_default_settings();
            $data['shortcode_options']  = $this->get_shortcode_default_options();
            $data['translations']       = $this->get_translation_srtings();
            $data['preference']         = $this->get_shortcode_default_prefs();
            $data['preference_options'] = $this->get_shortcode_prefs_options();
            $data['taxonomy_settings']  = $this->get_taxonomy_default_settings();
            $data['enabled_plugins']    = $this->get_enabled_plugins();
            $data['is_multilingual']    = $this->is_multilingual_enabled();

            $data['demo_data'] = [
                'team_data'      => wp_validate_boolean( get_option('gsteam_dummy_team_data_created') ),
                'shortcode_data' => wp_validate_boolean( get_option('gsteam_dummy_shortcode_data_created') )
            ];

            wp_enqueue_script( 'gs-team-shortcode', GSTEAM_PLUGIN_URI . '/assets/admin/js/shortcode.min.js', array('jquery'), GSTEAM_VERSION, true );

            wp_localize_script( 'gs-team-shortcode', '_gsteam_data', $data );

            add_fs_script( 'gs-team-shortcode' );
            
        }

        public function get_enabled_plugins() {
            
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            $plugins = [];

            if ( is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
                
                $team_groups = \acf_get_field_groups([
                    'post_type'	=> 'gs_team'
                ]);
                
                if ( !empty($team_groups) ) {
                    $plugins[] = 'advanced-custom-fields';
                }
            }

            return $plugins;
        }

        public function preview_scripts( $hook ) {
            
            if ( ! $this->is_preview() ) return;

            wp_enqueue_style( 'gs-team-shortcode-preview', GSTEAM_PLUGIN_URI . '/assets/css/preview.min.css', '', GSTEAM_VERSION );
            
        }

        public function get_wpdb() {

            global $wpdb;
            
            if ( wp_doing_ajax() ) $wpdb->show_errors = false;

            return $wpdb;

        }

        public function has_db_error() {

            $wpdb = $this->get_wpdb();

            if ( $wpdb->last_error === '') return false;

            return true;

        }

        public function validate_shortcode_settings( $shortcode_settings ) {
            $shortcode_settings = shortcode_atts( $this->get_shortcode_default_settings(), $shortcode_settings );
            return array_map( 'sanitize_text_field', $shortcode_settings );
        }

        protected function get_db_columns() {

            return array(
                'shortcode_name'     => '%s',
                'shortcode_settings' => '%s',
                'created_at'         => '%s',
                'updated_at'         => '%s',
            );

        }

        public function _get_shortcode( $shortcode_id, $is_ajax = false ) {

            if ( empty($shortcode_id) ) {
                if ( $is_ajax ) wp_send_json_error( __('Shortcode ID missing', 'gsteam'), 400 );
                return false;
            }

            $shortcode = wp_cache_get( 'gs_team_shortcode' . $shortcode_id, 'gs_team_memebrs' );

            // Return the cache if found
            if ( $shortcode !== false ) {
                if ( $is_ajax ) wp_send_json_success( $shortcode );
                return $shortcode;
            }

            $wpdb = $this->get_wpdb();

            $shortcode = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}gs_team WHERE id = %d LIMIT 1", absint($shortcode_id) ), ARRAY_A );

            if ( $shortcode ) {

                $shortcode["shortcode_settings"] = json_decode( $shortcode["shortcode_settings"], true );
                $shortcode["shortcode_settings"] = $this->validate_shortcode_settings( $shortcode["shortcode_settings"] );

                wp_cache_add( 'gs_team_shortcode' . $shortcode_id, $shortcode, 'gs_team_memebrs' );

                if ( $is_ajax ) wp_send_json_success( $shortcode );

                return $shortcode;

            }

            if ( $is_ajax ) wp_send_json_error( __('No shortcode found', 'gsteam'), 404 );

            return false;

        }

        public function _update_shortcode( $shortcode_id, $nonce, $fields, $is_ajax ) {

            if ( ! wp_verify_nonce( $nonce, '_gsteam_admin_nonce_gs_') || ! current_user_can( 'publish_pages' ) ) {
                if ( $is_ajax ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );
                return false;
            }

            if ( empty($shortcode_id) ) {
                if ( $is_ajax ) wp_send_json_error( __('Shortcode ID missing', 'gsteam'), 400 );
                return false;
            }
        
            $_shortcode = $this->_get_shortcode( $shortcode_id, false );
        
            if ( empty($_shortcode) ) {
                if ( $is_ajax ) wp_send_json_error( __('No shortcode found to update', 'gsteam'), 404 );
                return false;
            }
        
            $shortcode_name = !empty( $fields['shortcode_name'] ) ? $fields['shortcode_name'] : $_shortcode['shortcode_name'];
            $shortcode_settings = !empty( $fields['shortcode_settings']) ? $fields['shortcode_settings'] : $_shortcode['shortcode_settings'];

            // Remove dummy indicator on update
            if ( isset($shortcode_settings['gsteam-demo_data']) ) unset($shortcode_settings['gsteam-demo_data']);
        
            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
        
            $wpdb = $this->get_wpdb();
        
            $data = array(
                "shortcode_name" 	    => $shortcode_name,
                "shortcode_settings" 	=> json_encode($shortcode_settings),
                "updated_at" 		    => current_time( 'mysql')
            );
        
            $update_id = $wpdb->update( "{$wpdb->prefix}gs_team" , $data, array( 'id' => absint( $shortcode_id ) ),  $this->get_db_columns() );
        
            if ( $this->has_db_error() ) {
                if ( $is_ajax ) wp_send_json_error( sprintf( __( 'Database Error: %1$s', 'gsteam'), $wpdb->last_error), 500 );
                return false;
            }

            // Delete the shortcode cache
            wp_cache_delete( 'gs_team_shortcodes', 'gs_team_memebrs' );
            wp_cache_delete( 'gs_team_shortcode' . $shortcode_id, 'gs_team_memebrs' );

            do_action( 'gs_team_shortcode_updated', $update_id );
            do_action( 'gsp_shortcode_updated', $update_id );
        
            if ($is_ajax) wp_send_json_success( array(
                'message' => __('Shortcode updated', 'gsteam'),
                'shortcode_id' => $update_id
            ));
        
            return $update_id;

        }
        
        public function fetch_shortcodes( $shortcode_ids = [], $is_ajax = false, $minimal = false ) {

            $wpdb = $this->get_wpdb();
            $fields = $minimal ? 'id, shortcode_name' : '*';

            if ( empty( $shortcode_ids ) ) {
                
                $shortcodes = wp_cache_get( 'gs_team_shortcodes', 'gs_team_memebrs' );

                if ( $shortcodes === false ) {
                    $shortcodes = $wpdb->get_results( "SELECT {$fields} FROM {$wpdb->prefix}gs_team ORDER BY id DESC", ARRAY_A );
                    wp_cache_add( 'gs_team_shortcodes', $shortcodes, 'gs_team_memebrs' );
                }

            } else {

                $how_many = count($shortcode_ids);
                $placeholders = array_fill(0, $how_many, '%d');
                $format = implode(', ', $placeholders);
                $query = "SELECT {$fields} FROM {$wpdb->prefix}gs_team WHERE id IN($format)";
                $shortcodes = $wpdb->get_results( $wpdb->prepare($query, $shortcode_ids), ARRAY_A );

            }

            // check for database error
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error) );

            if ( $is_ajax ) {
                wp_send_json_success( $shortcodes );
            }

            return $shortcodes;

        }

        public function create_shortcode() {

            // validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            $shortcode_settings  = !empty( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : '';
            $shortcode_name  = !empty( $_POST['shortcode_name'] ) ? $_POST['shortcode_name'] : __( 'Undefined', 'gsteam' );

            if ( empty($shortcode_settings) || !is_array($shortcode_settings) ) {
                wp_send_json_error( __('Please configure the settings properly', 'gsteam'), 206 );
            }

            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );

            $wpdb = $this->get_wpdb();

            $data = array(
                "shortcode_name" => $shortcode_name,
                "shortcode_settings" => json_encode($shortcode_settings),
                "created_at" => current_time( 'mysql'),
                "updated_at" => current_time( 'mysql'),
            );

            $wpdb->insert( "{$wpdb->prefix}gs_team", $data, $this->get_db_columns() );

            // check for database error
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error), 500 );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_team_shortcodes', 'gs_team_memebrs' );

            do_action( 'gs_team_shortcode_created', $wpdb->insert_id );
            do_action( 'gsp_shortcode_created', $wpdb->insert_id );

            // send success response with inserted id
            wp_send_json_success( array(
                'message' => __('Shortcode created successfully', 'gsteam'),
                'shortcode_id' => $wpdb->insert_id
            ));
        }

        public function clone_shortcode() {

            // validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            $clone_id  = !empty( $_POST['clone_id']) ? $_POST['clone_id'] : '';

            if ( empty($clone_id) ) wp_send_json_error( __('Clone Id not provided', 'gsteam'), 400 );

            $clone_shortcode = $this->_get_shortcode( $clone_id, false );

            if ( empty($clone_shortcode) ) wp_send_json_error( __('Clone shortcode not found', 'gsteam'), 404 );


            $shortcode_settings  = $clone_shortcode['shortcode_settings'];
            $shortcode_name  = $clone_shortcode['shortcode_name'] .' '. __('- Cloned', 'gsteam');

            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );

            $wpdb = $this->get_wpdb();

            $data = array(
                "shortcode_name" => $shortcode_name,
                "shortcode_settings" => json_encode($shortcode_settings),
                "created_at" => current_time( 'mysql'),
                "updated_at" => current_time( 'mysql'),
            );

            $wpdb->insert( "{$wpdb->prefix}gs_team", $data, $this->get_db_columns() );

            // check for database error
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error), 500 );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_team_shortcodes', 'gs_team_memebrs' );

            // Get the cloned shortcode
            $shotcode = $this->_get_shortcode( $wpdb->insert_id, false );

            // send success response with inserted id
            wp_send_json_success( array(
                'message' => __('Shortcode cloned successfully', 'gsteam'),
                'shortcode' => $shotcode,
            ));
        }

        public function get_shortcode() {

            $shortcode_id = !empty( $_GET['id']) ? absint( $_GET['id'] ) : null;

            $this->_get_shortcode( $shortcode_id, wp_doing_ajax() );

        }

        public function update_shortcode( $shortcode_id = null, $nonce = null ) {

            if ( ! $shortcode_id ) {
                $shortcode_id = !empty( $_POST['id']) ? $_POST['id'] : null;
            }
            
            if ( ! $nonce ) {
                $nonce = $_POST['_wpnonce'] ?: null;
            }
    
            $this->_update_shortcode( $shortcode_id, $nonce, $_POST, true );

        }

        public function delete_shortcodes() {

            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') )
                wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );
    
            $ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : null;
    
            if ( empty( $ids ) ) {
                wp_send_json_error( __('No shortcode ids provided', 'gsteam'), 400 );
            }
    
            $wpdb = $this->get_wpdb();
    
            $count = count( $ids );
    
            $ids = implode( ',', array_map('absint', $ids) );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}gs_team WHERE ID IN($ids)" );
    
            if ( $this->has_db_error() ) wp_send_json_error( sprintf(__('Database Error: %s'), $wpdb->last_error), 500 );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_team_shortcodes', 'gs_team_memebrs' );

            do_action( 'gs_team_shortcode_deleted' );
            do_action( 'gsp_shortcode_deleted' );
    
            $m = _n( "Shortcode has been deleted", "Shortcodes have been deleted", $count, 'gsteam' ) ;
    
            wp_send_json_success( ['message' => $m] );

        }

        public function get_shortcodes() {

            $this->fetch_shortcodes( null, wp_doing_ajax() );

        }

        public function temp_save_shortcode_settings() {

            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') )
                wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );
            
            $temp_key = isset( $_POST['temp_key'] ) ? $_POST['temp_key'] : null;
            $shortcode_settings = isset( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : [];

            if ( empty($temp_key) ) wp_send_json_error( __('No temp key provided', 'gsteam'), 400 );
            if ( empty($shortcode_settings) ) wp_send_json_error( __('No temp settings provided', 'gsteam'), 400 );

            delete_transient( $temp_key );

            $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
            set_transient( $temp_key, $shortcode_settings, DAY_IN_SECONDS ); // save the transient for 1 day

            wp_send_json_success([
                'message' => __('Temp data saved', 'gsteam'),
            ]);

        }

        public function get_translation_srtings() {
            return [

                'image_filter'       => __( 'Image Filter', 'gsteam' ),
                'hover_image_filter' => __( 'Image Filter on Hover', 'gsteam' ),

                'location'  => __('Location', 'gsteam'),
                'location--details'  => __('Select specific team location to show that specific location members', 'gsteam'),

                'specialty'  => __('Specialty', 'gsteam'),
                'specialty--details'  => __('Select specific team specialty to show that specific specialty members', 'gsteam'),

                'language'  => __('Language', 'gsteam'),
                'language--details'  => __('Select specific team language to show that specific language members', 'gsteam'),

                'gender'  => __('Gender', 'gsteam'),
                'gender--details'  => __('Select specific team gender to show that specific gender members', 'gsteam'),

                'include_extra_one'  => __('Extra One', 'gsteam'),
                'include_extra_one--details'  => __('Select specific team extra one to show that specific extra one members', 'gsteam'),

                'include_extra_two'  => __('Extra Two', 'gsteam'),
                'include_extra_two--details'  => __('Select specific team extra two to show that specific extra two members', 'gsteam'),

                'include_extra_three'  => __('Extra Three', 'gsteam'),
                'include_extra_three--details'  => __('Select specific team extra three to show that specific extra three members', 'gsteam'),

                'include_extra_four'  => __('Extra Four', 'gsteam'),
                'include_extra_four--details'  => __('Select specific team extra four to show that specific extra four members', 'gsteam'),

                'include_extra_five'  => __('Extra Five', 'gsteam'),
                'include_extra_five--details'  => __('Select specific team extra five to show that specific extra five members', 'gsteam'),

                'install-demo-data' => __('Install Demo Data', 'gsteam'),
                'install-demo-data-description' => __('Quick start with GS Plugins by installing the demo data', 'gsteam'),

                'export-data' => __('Export Data', 'gsteam'),
                'export-data--description' => __('Export GS Team Plugins data', 'gsteam'),

                'import-data' => __('Import Data', 'gsteam'),
                'import-data--description' => __('Import GS Team Plugins data', 'gsteam'),

                'bulk-import' => __('Bulk Import', 'gsteam'),
                'bulk-import-description' => __('Add team members faster by GS Bulk Import feature', 'gsteam'),

                'preference' => __('Preference', 'gsteam'),
                'save-preference' => __('Save Preference', 'gsteam'),
                'save-settings' => __('Save Settings', 'gsteam'),
                'team-members-slug' => __('Team Members Slug', 'gsteam'),
                'team-members-slug-details' => __('Customize Team Members Post Type Slug, by default it is set to team-members', 'gsteam'),
                'replace-custom-slug' => __('Ignore Base Permalink Prefix', 'gsteam'),
                'replace-custom-slug-details' => __('Enable this option to use a custom structure without the base prefix.', 'gsteam'),

                'archive-page-slug' => __('Archive Page Slug', 'gsteam'),
                'archive-page-slug-details' => __('Set Custom Archive Page Slug, now it is set to', 'gsteam') . ' ' . get_post_type_archive_link( 'gs_team' ),

                'archive-page-title' => __('Archive Page Title', 'gsteam'),
                'archive-page-title-details' => __('Set Custom Archive Page Title, now it is set to', 'gsteam') . ' ' . gs_get_post_type_archive_title(),

                'taxonomies-page' => __('Taxonomies', 'gsteam'),
                'taxonomies-page--des' => __('Global settings for Taxonomies', 'gsteam'),

                'taxonomy_group' => $this->get_tax_option( 'group_tax_plural_label' ),
                'taxonomy_tag' => $this->get_tax_option( 'tag_tax_plural_label' ),
                'taxonomy_language' => $this->get_tax_option( 'language_tax_plural_label' ),
                'taxonomy_location' => $this->get_tax_option( 'location_tax_plural_label' ),
                'taxonomy_gender' => $this->get_tax_option( 'gender_tax_plural_label' ),
                'taxonomy_specialty' => $this->get_tax_option( 'specialty_tax_plural_label' ),
                'taxonomy_extra_one' => $this->get_tax_option( 'extra_one_tax_plural_label' ),
                'taxonomy_extra_two' => $this->get_tax_option( 'extra_two_tax_plural_label' ),
                'taxonomy_extra_three' => $this->get_tax_option( 'extra_three_tax_plural_label' ),
                'taxonomy_extra_four' => $this->get_tax_option( 'extra_four_tax_plural_label' ),
                'taxonomy_extra_five' => $this->get_tax_option( 'extra_five_tax_plural_label' ),

                // Extra One Taxonomy Settings
                'enable_extra_tax' => __('Enable Taxonomy', 'gsteam'),
                'enable_extra_tax--details' => __('Enable Taxonomy for team members', 'gsteam'),
                'extra_tax_label' => __('Taxonomy Label', 'gsteam'),
                'extra_tax_label--details' => __('Set Taxonomy Label', 'gsteam'),
                'extra_tax_plural_label' => __('Taxonomy Plural Label', 'gsteam'),
                'extra_tax_plural_label--details' => __('Set Taxonomy Plural Label', 'gsteam'),
                'enable_extra_tax_archive' => __('Enable Taxonomy Archive', 'gsteam'),
                'enable_extra_tax_archive--details' => __('Enable Taxonomy Archive', 'gsteam'),
                'extra_tax_archive_slug' => __('Taxonomy Archive Slug', 'gsteam'),
                'extra_tax_archive_slug--details' => __('Set Taxonomy Archive Slug', 'gsteam'),

                'disable-google-fonts' => __('Disable Google Fonts', 'gsteam'),
                'disable-google-fonts-details' => __('Disable Google Fonts Loading', 'gsteam'),
                
                'show-acf-fields' => __('Display ACF Fields', 'gsteam'),
                'show-acf-fields-details' => __('Display ACF fields in the single pages', 'gsteam'),
                
                'disable_lazy_load' => __('Disable Lazy Load', 'gsteam'),
                'disable_lazy_load-details' => __('Disable Lazy Load for team member images', 'gsteam'),
                
                'lazy_load_class' => __('Lazy Load Class', 'gsteam'),
                'lazy_load_class-details' => __('Add class to disable lazy loading, multiple classes should be separated by space', 'gsteam'),

                'acf-fields-position' => __('ACF Fields Position', 'gsteam'),
                'acf-fields-position-details' => __('Position to display ACF fields', 'gsteam'),
                
                'enable-multilingual' => __('Enable Multilingual', 'gsteam'),
                'enable-multilingual--details' => __('Enable Multilingual mode to translate below strings using any Multilingual plugin like wpml or loco translate.', 'gsteam'),
                
                'pref-filter-designation-text' => __('Filter Designation Text', 'gsteam'),
                'pref-serach-text' => __('Search Text', 'gsteam'),
                'gs_teamfliter_company-text' => __('Company Search Text', 'gsteam'),
                'gs_teamfliter_zip-text' => __('Zip Search Text', 'gsteam'),
                'gs_teamfliter_tag-text' => __('Tag Search Text', 'gsteam'),
                'pref-zip_code-text' => __('Zip Code', 'gsteam'),
                'pref-follow_me_on-text' => __('Follow Me On', 'gsteam'),
                'pref-skills-text' => __('Skills', 'gsteam'),
                'pref-search-all-fields' => __('Include fields when search', 'gsteam'),
                'pref-company' => __('Company', 'gsteam'),
                'pref-address' => __('Address', 'gsteam'),
                'pref-land-phone' => __('Land Phone', 'gsteam'),
                'pref-cell-phone' => __('Cell Phone', 'gsteam'),
                'pref-email' => __('Email', 'gsteam'),
                'pref-location' => __('Location', 'gsteam'),
                'pref-language' => __('Language', 'gsteam'),
                'pref-specialty' => __('Specialty', 'gsteam'),
                'pref-gender' => __('Gender', 'gsteam'),
                'pref-read-on' => __('Read On', 'gsteam'),
                'pref-more' => __('More', 'gsteam'),
                'custom-css' => __('Custom CSS', 'gsteam'),
                
                'pref-filter-designation-text-details' => __('Replace with preferred text for Designation', 'gsteam'),
                'pref-serach-text-details' => __('Replace with preferred text for Search', 'gsteam'),
                'gs_teamfliter_company-text-details' => __('Replace with preferred text for Company Search', 'gsteam'),
                'gs_teamfliter_zip-text-details' => __('Replace with preferred text for Zip Search', 'gsteam'),
                'gs_teamfliter_tag-text-details' => __('Replace with preferred text for Tag Search', 'gsteam'),
                'pref-company-details' => __('Replace with preferred text for Company', 'gsteam'),
                'pref-address-details' => __('Replace with preferred text for Address', 'gsteam'),
                'pref-land-phone-details' => __('Replace with preferred text for Land Phone', 'gsteam'),
                'pref-cell-phone-details' => __('Replace with preferred text for Cell Phone', 'gsteam'),
                'pref-email-details' => __('Replace with preferred text for Email', 'gsteam'),
                'pref-location-details' => __('Replace with preferred text for Location', 'gsteam'),
                'pref-language-details' => __('Replace with preferred text for Language', 'gsteam'),
                'pref-specialty-details' => __('Replace with preferred text for Specialty', 'gsteam'),
                'pref-gender-details' => __('Replace with preferred text for Gender', 'gsteam'),
                'pref-read-on-details' => __('Replace with preferred text for Read On', 'gsteam'),
                'pref-zip_code-text-details' => __('Replace with preferred text for Zip Code', 'gsteam'),
                'pref-follow_me_on-text-details' => __('Replace with preferred text for Follow Me On', 'gsteam'),
                'pref-skills-text-details' => __('Replace with preferred text for Skills', 'gsteam'),
                'pref-more-details' => __('Replace with preferred text for More', 'gsteam'),
                'pref-search-all-fields-details' => __('Enable searching through all fields', 'gsteam'),

                'vcard-txt' => __('vCard Text', 'gsteam'),
                'vcard-txt-details' => __('Replace with preferred text for vCard Text', 'gsteam'),

                'land-phone-link' => __('Link Land Phone', 'gsteam'),
                'land-phone-link--details' => __('Enable link for land phone number', 'gsteam'),

                'cell-phone-link' => __('Link Cell Phone', 'gsteam'),
                'cell-phone-link--details' => __('Enable link for cell phone number', 'gsteam'),

                'email-link' => __('Link Email', 'gsteam'),
                'email-link--details' => __('Enable link for Email', 'gsteam'),

                'reset-filters' => __('Reset Filters Text', 'gsteam'),
                'reset-filters-details' => __('Replace with preferred text for Reset Filters button text', 'gsteam'),

                'prev' => __('Prev Text', 'gsteam'),
                'prev-details' => __('Replace with preferred text for carousel Prev text', 'gsteam'),

                'next' => __('Next Text', 'gsteam'),
                'next-details' => __('Replace with preferred text for carousel Next text', 'gsteam'),

                'carousel_enabled' => __('Enable Carousel', 'gsteam'),
                'carousel_enabled__details' => __('Enable carousel for this theme, it may not available for certain theme', 'gsteam'),

                'carousel_navs_enabled' => __('Enable Carousel Navs', 'gsteam'),
                'carousel_navs_enabled__details' => __('Enable carousel navs for this theme, it may not available for certain theme', 'gsteam'),

                'gs_slider_nav_bg_color' => __('Nav BG Color', 'gsteam'),
                'gs_slider_nav_color' => __('Nav Color', 'gsteam'),
                'gs_slider_nav_hover_bg_color' => __('Nav Hover BG Color', 'gsteam'),
                'gs_slider_nav_hover_color' => __('Nav Hover Color', 'gsteam'),
                'gs_slider_dot_color' => __('Dots Color', 'gsteam'),
                'gs_slider_dot_hover_color' => __('Dots Active Color', 'gsteam'),

                'carousel_dots_enabled' => __('Enable Carousel Dots', 'gsteam'),
                'carousel_dots_enabled__details' => __('Enable carousel dots for this theme, it may not available for certain theme', 'gsteam'),

                'carousel_navs_style' => __('Carousel Navs Style', 'gsteam'),
                'carousel_navs_style__details' => __('Select carousel navs style, this is available for certain theme', 'gsteam'),

                'carousel_dots_style' => __('Carousel Dots Style', 'gsteam'),
                'carousel_dots_style__details' => __('Select carousel dots style, this is available for certain theme', 'gsteam'),

                'carousel_navs' => __('Carousel Navs Style', 'gsteam'),
                'carousel_navs__details' => __('Select carousel navs style, this is available for certain theme', 'gsteam'),

                'filter_enabled' => __('Enable Filter', 'gsteam'),
                'filter_enabled__details' => __('Enable filter for this theme, it may not available for certain theme', 'gsteam'),

                'drawer_style' => __('Drawer Style', 'gsteam'),
                'drawer_style__details' => __('Select drawer style, this is available for certain theme', 'gsteam'),

                'panel_style' => __('Panel Style', 'gsteam'),
                'panel_style__details' => __('Select panel style, this is available for certain theme', 'gsteam'),

                'popup_style' => __('Popup Style', 'gsteam'),
                'popup_style__details' => __('Select popup style, this is available for certain theme', 'gsteam'),

                'filter_style' => __('Filter Style', 'gsteam'),
                'filter_text_color' => __('Filter Color', 'gsteam'),
                'filter_bg_color' => __('Filter BG Color', 'gsteam'),
                'filter_border_color' => __('Filter Border Color', 'gsteam'),
                'filter_active_text_color' => __('Filter Active Color', 'gsteam'),
                'filter_active_bg_color' => __('Filter Active BG Color', 'gsteam'),
                'filter_active_border_color' => __('Filter Active Border Color', 'gsteam'),

                'shortcodes' => __('Shortcodes', 'gsteam'),
                'shortcode' => __('Shortcode', 'gsteam'),
                'global-settings-for-gs-team-members' => __('Global Settings for GS Team Members', 'gsteam'),
                'all-shortcodes-for-gs-team-member' => __('All shortcodes for GS Team Member', 'gsteam'),
                'create-shortcode' => __('Create Shortcode', 'gsteam'),
                'create-new-shortcode' => __('Create New Shortcode', 'gsteam'),
                'name' => __('Name', 'gsteam'),
                'action' => __('Action', 'gsteam'),
                'actions' => __('Actions', 'gsteam'),
                'edit' => __('Edit', 'gsteam'),
                'clone' => __('Clone', 'gsteam'),
                'delete' => __('Delete', 'gsteam'),
                'delete-all' => __('Delete All', 'gsteam'),
                'create-a-new-shortcode-and' => __('Create a new shortcode & save it to use globally in anywhere', 'gsteam'),
                'edit-shortcode' => __('Edit Shortcode', 'gsteam'),
                'general-settings' => __('General Settings', 'gsteam'),
                'style-settings' => __('Style Settings', 'gsteam'),
                'query-settings' => __('Query Settings', 'gsteam'),
                'general-settings-short' => __('General', 'gsteam'),
                'style-settings-short' => __('Style', 'gsteam'),
                'query-settings-short' => __('Query', 'gsteam'),
                'link_preview_image'   => __( 'Link Image', 'gsteam' ),
                'preview_enabled__details'   => __( 'Link Image', 'gsteam' ),
                'columns' => __('Columns', 'gsteam'),
                'columns_desktop' => __('Desktop Slides', 'gsteam'),
                'columns_desktop_details' => __('Enter the slides number for desktop', 'gsteam'),
                'columns_tablet' => __('Tablet Slides', 'gsteam'),
                'columns_tablet_details' => __('Enter the slides number for tablet', 'gsteam'),
                'columns_mobile_portrait' => __('Portrait Mobile Slides', 'gsteam'),
                'columns_mobile_portrait_details' => __('Enter the slides number for portrait or large display mobile', 'gsteam'),
                'columns_mobile' => __('Mobile Slides', 'gsteam'),
                'columns_mobile_details' => __('Enter the slides number for mobile', 'gsteam'),
                'style-theming' => __('Style & Theming', 'gsteam'),
                'member-name' => __('Member Name', 'gsteam'),
                'gs_member_name_is_linked' => __('Link Team Members', 'gsteam'),
                'gs_member_name_is_linked__details' => __('Add links to the Member\'s name, description & image to display popup or to single member page', 'gsteam'),
                'gs_member_thumbnail_sizes' => __('Thumbnail Sizes', 'gsteam'),
                'gs_member_thumbnail_sizes_details' => __('Select a thumbnail size', 'gsteam'),
                'gs_member_link_type' => __('Link Type', 'gsteam'),
                'gs_member_link_type__details' => __('Choose the link type of team members', 'gsteam'),
                
                'member-designation' => __('Member Designation', 'gsteam'),
                'member-details' => __('Member Details', 'gsteam'),
                'social-connection' => __('Social Connection', 'gsteam'),
                'display-ribbon' => __('Display Ribbon', 'gsteam'),
                'pagination' => __('Pagination', 'gsteam'),
                'single_page_style' => __('Single Page Style', 'gsteam'),
                'single_link_type' => __('Single Link Type', 'gsteam'),
                'single_page_style__details' => __('Style for all single page', 'gsteam'),
                'single_link_type__details' => __('Set the default link type for link behaviour', 'gsteam'),
                'next-prev-member' => __('Next / Prev Member', 'gsteam'),
                'instant-search-by-name' => __('Search by Name', 'gsteam'),
                'gs-member-srch-by-zip' => __('Search by Zip', 'gsteam'),
                'gs-member-srch-by-tag' => __('Search by Tag', 'gsteam'),
                'gs-member-srch-by-company' => __('Search by Company', 'gsteam'),
                'filter-by-designation' => __('Filter by Designation', 'gsteam'),
                'filter-by-location' => __('Filter by Location', 'gsteam'),
                'filter-by-language' => __('Filter by Language', 'gsteam'),
                'filter-by-gender' => __('Filter by Gender', 'gsteam'),
                'filter-by-speciality' => __('Filter by Specialty', 'gsteam'),
                'filter-by-extra-one' => __('Filter by Extra One', 'gsteam'),
                'filter-by-extra-two' => __('Filter by Extra Two', 'gsteam'),
                'filter-by-extra-three' => __('Filter by Extra Three', 'gsteam'),
                'filter-by-extra-four' => __('Filter by Extra Four', 'gsteam'),
                'filter-by-extra-five' => __('Filter by Extra Five', 'gsteam'),
                'gs_team_filter_columns' => __('Filter Columns', 'gsteam'),
                'social-link-target' => __('Social Link Target', 'gsteam'),
                'gs-desc-allow-html' => __('Allow HTML for Details', 'gsteam'),
                'gs-desc-allow-html--help' => __('Enable/Disable HTML content for the single team member descript, this will load whole content from the post type.', 'gsteam'),
                'details-control' => __('Details Control', 'gsteam'),
                'gs-desc-scroll-contrl' => __('Description Scroll Control', 'gsteam'),
                'gs-desc-scroll-contrl--help' => __('Enable/Disable scrollbar for description on popup, drawer & panel, useful when description has large content.', 'gsteam'),
                'gs-max-scroll-height' => __('Scroll Height', 'gsteam'),
                'gs-max-scroll-height--help' => __('Set the maximum height of the description, if content exceds the height, scrollbar will appear.', 'gsteam'),
                'popup-column' => __('Popup Column', 'gsteam'),
                'filter-category-position' => __('Filter Category Position', 'gsteam'),
                'panel' => __('Panel', 'gsteam'),
                'name-font-size' => __('Name Font Size', 'gsteam'),
                'name-font-weight' => __('Name Font Weight', 'gsteam'),
                'name-font-style' => __('Name Font Style', 'gsteam'),
                'name-color' => __('Name Color', 'gsteam'),
                'name-bg-color' => __('Name BG Color', 'gsteam'),
                'tm-bg-color' => __('Item BG Color', 'gsteam'),
                'tm-bg-color-hover' => __('Item Hover BG Color', 'gsteam'),
                'description-color' => __('Description Color', 'gsteam'),
                'description-link-color' => __('Description Link Color', 'gsteam'),
                'info-color' => __('Info Color', 'gsteam'),
                'info-icon-color' => __('Info Icon Color', 'gsteam'),
                'tooltip-bg-color' => __('Tooltip BG Color', 'gsteam'),
                'info-bg-color' => __('Info BG Color', 'gsteam'),
                'hover-icon-bg-color' => __('Hover Icon BG Color', 'gsteam'),
                'ribon-background-color' => __('Ribbon BG Color', 'gsteam'),
                'role-font-size' => __('Role Font Size', 'gsteam'),
                'role-font-weight' => __('Role Font Weight', 'gsteam'),
                'role-font-style' => __('Role Font Style', 'gsteam'),
                'role-color' => __('Role Color', 'gsteam'),
                'popup-arrow-color' => __('Popup Arrow Color', 'gsteam'),
                'team-members' => __('Team Members', 'gsteam'),
                'order' => __('Order', 'gsteam'),
                'order-by' => __('Order By', 'gsteam'),
                'group-order' => __('Group Order', 'gsteam'),
                'group-order-by' => __('Group Order By', 'gsteam'),
                'group_hide_empty' => __('Hide Empty Filters', 'gsteam'),
                'group_hide_empty__details' => __('Enable to hide the empty filters', 'gsteam'),
                'group' => __('Group', 'gsteam'),
                'exclude_group' => __('Exclude Group', 'gsteam'),
                'exclude_group__help' => __('Select a specific team group to hide that specific group members', 'gsteam'),

                'theme' => __('Theme', 'gsteam'),
                'font-size' => __('Font Size', 'gsteam'),
                'font-weight' => __('Font Weight', 'gsteam'),
                'font-style' => __('Font Style', 'gsteam'),
                'shortcode-name' => __('Shortcode Name', 'gsteam'),

                'select-number-of-team-columns' => __('Select the number of Team columns', 'gsteam'),
                'select-preffered-style-theme' => __('Select the preferred Style & Theme', 'gsteam'),
                'show-or-hide-team-member-name' => __('Show or Hide Team Member Name', 'gsteam'),
                'show-or-hide-team-member-designation' => __('Show or Hide Team Member Designation', 'gsteam'),
                'show-or-hide-team-member-details' => __('Show or Hide Team Member Details', 'gsteam'),
                'show-or-hide-team-member-social-connections' => __('Show or Hide Team Member Social Connections', 'gsteam'),
                'show-or-hide-team-member-paginations' => __('Show or Hide Team Member Paginations', 'gsteam'),
                'show-or-hide-next-prev-member-link-at-single-team-template' => __('Show or Hide Next / Prev Member link at Single Team Template', 'gsteam'),
                'show-or-hide-instant-search-applicable-for-theme-9' => __('Show or Hide Instant Search', 'gsteam'),
                'gs-member-srch-by-zip--details' => __('Show or Hide by Instant Zip Search', 'gsteam'),
                'gs-member-srch-by-tag--details' => __('Show or Hide by Instant Tag Search', 'gsteam'),
                'gs-member-srch-by-company--details' => __('Show or Hide Instant Company Search', 'gsteam'),
                'filter-by-designation--des' => __('Show or Hide Filter by Designation', 'gsteam'),
                'filter-by-location--des' => __('Show or Hide Filter by Location', 'gsteam'),
                'filter-by-language--des' => __('Show or Hide Filter by Language', 'gsteam'),
                'filter-by-gender--des' => __('Show or Hide Filter by Gender', 'gsteam'),
                'filter-by-speciality--des' => __('Show or Hide Filter by Specialty', 'gsteam'),
                'filter-by-extra-one--des' => __('Show or Hide Filter by Extra One', 'gsteam'),
                'filter-by-extra-two--des' => __('Show or Hide Filter by Extra Two', 'gsteam'),
                'filter-by-extra-three--des' => __('Show or Hide Filter by Extra Three', 'gsteam'),
                'filter-by-extra-four--des' => __('Show or Hide Filter by Extra Four', 'gsteam'),
                'filter-by-extra-five--des' => __('Show or Hide Filter by Extra Five', 'gsteam'),
                'specify-target-to-load-the-links' => __('Specify the target to load the Links, Default New Tab', 'gsteam'),
                'specify-target-to-load-the-links' => __('Specify the target to load the Links, Default New Tab', 'gsteam'),
                'define-maximum-number-of-characters' => __('Define the maximum number of characters in Member details. Default 100', 'gsteam'),
                'set-column-for-popup' => __('Set column for popup', 'gsteam'),
                'set-max-team-numbers-you-want-to-show' => __('Set max team numbers you want to show, set -1 for all members', 'gsteam'),
                'select-specific-team-group-to' => __('Select a specific team group to show that specific group members', 'gsteam'),

                'export-team-members-data' => __('Export Team Members', 'gsteam'),
                'export-shortcodes-data' => __('Export Shortcodes', 'gsteam'),
                'export-settings-data' => __('Export Settings', 'gsteam'),

                'enable-multi-select' => __('Enable Multi Select', 'gsteam'),
                'enable-multi-select--help' => __('Enable multi-selection on the filters, Default is Off', 'gsteam'),
                'multi-select-ellipsis' => __('Multi Select Ellipsis', 'gsteam'),
                'multi-select-ellipsis--help' => __('Show multi-selected values in ellipsis mode, Default is Off', 'gsteam'),

                'filter-all-enabled' => __('Enable All Filter', 'gsteam'),
                'filter-all-enabled--help' => __('Enable All filter in the filter templates, Default is On', 'gsteam'),

                'enable-child-cats' => __('Enable Child Filters', 'gsteam'),
                'enable-child-cats--help' => __('Enable child group filters, Default is Off', 'gsteam'),

                'enable-scroll-animation' => __('Enable Scroll Animation', 'gsteam'),
                'enable-scroll-animation--help' => __('Enable scroll animation, Default is On', 'gsteam'),

                'fitler-all-text' => __('All filter text', 'gsteam'),
                'fitler-all-text--help' => __('All filter text for filter templates, Default is All', 'gsteam'),

                'enable-clear-filters' => __('Reset Filters Button', 'gsteam'),
                'enable-clear-filters--help' => __('Enable Reset all filters button in filter themes, Default is Off ', 'gsteam'),

                'shortcode-name' => __('Shortcode Name', 'gsteam'),
                'save-shortcode' => __('Save Shortcode', 'gsteam'),
                'preview-shortcode' => __('Preview Shortcode', 'gsteam')
            ];
        }

        public static function _themes() {
            return [
                [
                    'label' => __( 'Grid 1', 'gsteam' ),
                    'value' => 'gs-grid-style-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 2', 'gsteam' ),
                    'value' => 'gs-grid-style-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 3', 'gsteam' ),
                    'value' => 'gs-grid-style-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 4', 'gsteam' ),
                    'value' => 'gs-grid-style-four',
                    'type' => 'free',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 5', 'gsteam' ),
                    'value' => 'gs-grid-style-five',
                    'type' => 'free',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 6', 'gsteam' ),
                    'value' => 'gs-grid-style-six',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 1', 'gsteam' ),
                    'value' => 'gs-team-circle-one',
                    'type' => 'free',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 2', 'gsteam' ),
                    'value' => 'gs-team-circle-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 3', 'gsteam' ),
                    'value' => 'gs-team-circle-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 4', 'gsteam' ),
                    'value' => 'gs-team-circle-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Circle 5', 'gsteam' ),
                    'value' => 'gs-team-circle-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 1', 'gsteam' ),
                    'value' => 'gs-team-horizontal-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 2', 'gsteam' ),
                    'value' => 'gs-team-horizontal-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 3', 'gsteam' ),
                    'value' => 'gs-team-horizontal-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 4', 'gsteam' ),
                    'value' => 'gs-team-horizontal-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Horizontal 5', 'gsteam' ),
                    'value' => 'gs-team-horizontal-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 1', 'gsteam' ),
                    'value' => 'gs-team-flip-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 2', 'gsteam' ),
                    'value' => 'gs-team-flip-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 3', 'gsteam' ),
                    'value' => 'gs-team-flip-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 4', 'gsteam' ),
                    'value' => 'gs-team-flip-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Flip 5', 'gsteam' ),
                    'value' => 'gs-team-flip-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 1', 'gsteam' ),
                    'value' => 'gs-team-table-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 2', 'gsteam' ),
                    'value' => 'gs-team-table-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 3', 'gsteam' ),
                    'value' => 'gs-team-table-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 4', 'gsteam' ),
                    'value' => 'gs-team-table-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Table 5', 'gsteam' ),
                    'value' => 'gs-team-table-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 1', 'gsteam' ),
                    'value' => 'gs-team-list-style-one',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 2', 'gsteam' ),
                    'value' => 'gs-team-list-style-two',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 3', 'gsteam' ),
                    'value' => 'gs-team-list-style-three',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 4', 'gsteam' ),
                    'value' => 'gs-team-list-style-four',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'List 5', 'gsteam' ),
                    'value' => 'gs-team-list-style-five',
                    'type' => 'pro',
                    'version' => 2
                ],
                [
                    'label' => __( 'Grid 6', 'gsteam' ),
                    'value' => 'gs_tm_theme1',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 7', 'gsteam' ),
                    'value' => 'gs_tm_grid2',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 8', 'gsteam' ),
                    'value' => 'gs_tm_theme20',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 9', 'gsteam' ),
                    'value' => 'gs_tm_theme10',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid 10', 'gsteam' ),
                    'value' => 'gs_tm_theme_custom_10',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Popup', 'gsteam' ),
                    'value' => 'gs_tm_theme8',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Single', 'gsteam' ),
                    'value' => 'gs_tm_theme11',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter Single', 'gsteam' ),
                    'value' => 'gs_tm_theme22',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter Popup', 'gsteam' ),
                    'value' => 'gs_tm_theme9',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Slider', 'gsteam' ),
                    'value' => 'gs_tm_theme7',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter - Selected Cats', 'gsteam' ),
                    'value' => 'gs_tm_theme12',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Filter with vcard', 'gsteam' ),
                    'value' => 'gs_tm_theme24',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Panel Slide', 'gsteam' ),
                    'value' => 'gs_tm_theme19',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Drawer 1', 'gsteam' ),
                    'value' => 'gs_tm_theme13',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Grid Drawer 2', 'gsteam' ),
                    'value' => 'gs_tm_drawer2',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Circle 6', 'gsteam' ),
                    'value' => 'gs_tm_theme2',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Horizontal 6', 'gsteam' ), // Horizontal 1 (Square Right Info)
                    'value' => 'gs_tm_theme3',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Horizontal 7', 'gsteam' ), // Horizontal 2 (Square Left Info)
                    'value' => 'gs_tm_theme4',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Horizontal 8', 'gsteam' ), // Horizontal 3 (Circle Right Info)
                    'value' => 'gs_tm_theme5',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Horizontal 9', 'gsteam' ), // Horizontal 4 (Circle Left Info)
                    'value' => 'gs_tm_theme6',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Flip', 'gsteam' ),
                    'value' => 'gs_tm_theme23',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 6 - Underline', 'gsteam' ),
                    'value' => 'gs_tm_theme14',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 7 - Box Border', 'gsteam' ),
                    'value' => 'gs_tm_theme15',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 8 - Odd Even', 'gsteam' ),
                    'value' => 'gs_tm_theme16',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 9 Filter', 'gsteam' ),
                    'value' => 'gs_tm_theme21',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'Table 10 Filter Dense', 'gsteam' ),
                    'value' => 'gs_tm_theme21_dense',
                    'type' => 'pro',
                    'version' => 1
                ],
                [
                    'label' => __( 'List 6', 'gsteam' ),
                    'value' => 'gs_tm_theme17',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'List 7', 'gsteam' ),
                    'value' => 'gs_tm_theme18',
                    'type' => 'free',
                    'version' => 1
                ],
                [
                    'label' => __( 'Group Filter 1', 'gsteam' ),
                    'value' => 'gs_tm_theme25',
                    'type' => 'pro',
                    'version' => 1
                ],
            ];
        }

        public static function get_free_themes_v_1() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 1, 'type' => 'free' ] );
        }

        public static function get_free_themes_v_2() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 2, 'type' => 'free' ] );
        }

        public static function get_pro_themes_v_1() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 1, 'type' => 'pro' ] );
        }

        public static function get_pro_themes_v_2() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'version' => 2, 'type' => 'pro' ] );
        }

        public static function get_free_themes() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'type' => 'free' ] );
        }

        public static function get_pro_themes() {
            $themes = self::_themes();
            return wp_list_filter( $themes, [ 'type' => 'pro' ] );
        }

        public static function get_formated_themes( $themes ) {

            if ( ! gtm_fs()->is_paying_or_trial() ) {

                $_themes = array_map( function( $theme ) {
                    $theme['label'] = $theme['label'] . __(' (Pro)', 'gsteam');
                    return $theme;
                }, wp_list_filter( $themes, [ 'version' => 1, 'type' => 'pro' ] ) );
                $themes = shortcode_atts( $themes, $_themes );
    
                $_themes = array_map( function( $theme ) {
                    $theme['label'] = $theme['label'] . __(' (New - Pro)', 'gsteam');
                    return $theme;
                }, wp_list_filter( $themes, [ 'version' => 2, 'type' => 'pro' ] ) );
                $themes = shortcode_atts( $themes, $_themes );
                
                $_themes = wp_list_filter( $themes, [ 'type' => 'pro' ] );
                $_themes = self::add_pro_to_options( $_themes );

                $_themes = shortcode_atts( $themes, $_themes );
                $themes = wp_list_sort( $_themes, 'type', 'ASC' );

            } else {

                $_themes = array_map( function( $theme ) {
                    $theme['label'] = $theme['label'] . __(' (New)', 'gsteam');
                    return $theme;
                }, wp_list_filter( $themes, [ 'version' => 2 ] ) );
                
                $themes = shortcode_atts( $themes, $_themes );

            }

            $themes = array_map( function( $theme ) {
                unset( $theme['type'] );
                unset( $theme['version'] );
                return $theme;
            }, $themes );

            return $themes;
        }

        public function get_shortcode_options_themes() {
            return self::get_formated_themes( self::_themes() );
        }

        public function get_shortcode_options_link_types() {

            $free_options = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Single Page', 'gsteam' ),
                    'value' => 'single_page'
                ],
                [
                    'label' => __( 'Popup', 'gsteam' ),
                    'value' => 'popup'
                ]
            ];

            $pro_options = [
                [
                    'label' => __( 'Panel', 'gsteam' ),
                    'value' => 'panel'
                ],
                [
                    'label' => __( 'Drawer', 'gsteam' ),
                    'value' => 'drawer'
                ],
                [
                    'label' => __( 'Custom URL', 'gsteam' ),
                    'value' => 'custom'
                ]
            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $pro_options = self::add_pro_to_options( $pro_options );
            }

            return array_merge( $free_options, $pro_options );

        }

        public function get_carousel_navs_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ]

            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_carousel_dots_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ]

            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_drawer_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gsteam' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gsteam' ),
                    'value' => 'style-five'
                ]

            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $styles = self::add_pro_to_options( $styles );
            }

            return $styles;

        }

        public static function add_pro_to_options( $options ) {
            return array_map( function( $item ) {
                $item['pro'] = true;
                return $item;
            }, $options );
        }

        public function get_panel_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gsteam' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gsteam' ),
                    'value' => 'style-five'
                ]

            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $styles = self::add_pro_to_options($styles);
            }

            return $styles;

        }

        public function get_popup_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gsteam' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gsteam' ),
                    'value' => 'style-five'
                ],
                [
                    'label' => __( 'Style Six', 'gsteam' ),
                    'value' => 'style-six'
                ]

            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $default = array_shift( $styles );
                $styles = array_merge( [$default], self::add_pro_to_options($styles) );
            }

            return $styles;

        }

        public function get_filter_styles() {

            $styles = [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gsteam' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gsteam' ),
                    'value' => 'style-five'
                ]

            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $styles = self::add_pro_to_options($styles);
            }

            return $styles;

        }

        public function get_shortcode_default_options() {
            return [
                'location' => self::get_team_terms('gs_team_location'),
                'specialty' => self::get_team_terms('gs_team_specialty'),
                'language' => self::get_team_terms('gs_team_language'),
                'gender' => self::get_team_terms('gs_team_gender'),
                'group' => self::get_team_terms('gs_team_group'),
                'exclude_group' => self::get_team_terms('gs_team_group'),
                'extra_one' => self::get_team_terms('gs_team_extra_one'),
                'extra_two' => self::get_team_terms('gs_team_extra_two'),
                'extra_three' => self::get_team_terms('gs_team_extra_three'),
                'extra_four' => self::get_team_terms('gs_team_extra_four'),
                'extra_five' => self::get_team_terms('gs_team_extra_five'),
                'gs_team_cols' => $this->get_columns(),
                'drawer_style' => $this->get_drawer_styles(),
                'carousel_navs_style' => $this->get_carousel_navs_styles(),
                'carousel_dots_style' => $this->get_carousel_dots_styles(),
                'panel_style' => $this->get_panel_styles(),
                'popup_style' => $this->get_popup_styles(),
                'filter_style' => $this->get_filter_styles(),
                'gs_member_thumbnail_sizes' => $this->getPossibleThumbnailSizes(),
                'gs_team_cols_tablet' => $this->get_columns(),
                'gs_team_cols_mobile_portrait' => $this->get_columns(),
                'gs_team_cols_mobile' => $this->get_columns(),
                'gs_team_theme' => $this->get_shortcode_options_themes(),
                'gs_member_link_type' => $this->get_shortcode_options_link_types(),
                'acf_fields_position' => $this->get_acf_fields_position(),
                'gs_teammembers_pop_clm' => [
                    [
                        'label' => __( 'One', 'gsteam' ),
                        'value' => 'one'
                    ],
                    [
                        'label' => __( 'Two', 'gsteam' ),
                        'value' => 'two'
                    ],
                ],
                'gs_team_filter_columns' => [
                    [
                        'label' => __( 'Two', 'gsteam' ),
                        'value' => 'two'
                    ],
                    [
                        'label' => __( 'Three', 'gsteam' ),
                        'value' => 'three'
                    ],
                ],
                'gs_tm_filter_cat_pos' => [
                    [
                        'label' => __( 'Left', 'gsteam' ),
                        'value' => 'left'
                    ],
                    [
                        'label' => __( 'Center', 'gsteam' ),
                        'value' => 'center'
                    ],
                    [
                        'label' => __( 'Right', 'gsteam' ),
                        'value' => 'right'
                    ]
                ],
                'panel' => [
                    [
                        'label' => __( 'Left', 'gsteam' ),
                        'value' => 'left'
                    ],
                    [
                        'label' => __( 'Center', 'gsteam' ),
                        'value' => 'center'
                    ],
                    [
                        'label' => __( 'Right', 'gsteam' ),
                        'value' => 'right'
                    ]
                ],
                'orderby' => [
                    [
                        'label' => __( 'Custom Order', 'gsteam' ),
                        'value' => 'menu_order'
                    ],
                    [
                        'label' => __( 'Team ID', 'gsteam' ),
                        'value' => 'ID'
                    ],
                    [
                        'label' => __( 'Team Name', 'gsteam' ),
                        'value' => 'title'
                    ],
                    [
                        'label' => __( 'Date', 'gsteam' ),
                        'value' => 'date'
                    ],
                    [
                        'label' => __( 'Random', 'gsteam' ),
                        'value' => 'rand'
                    ],
                ],
                'group_orderby' => [
                    [
                        'label' => __( 'Custom Order', 'gsteam' ),
                        'value' => 'term_order'
                    ],
                    [
                        'label' => __( 'Group ID', 'gsteam' ),
                        'value' => 'term_id'
                    ],
                    [
                        'label' => __( 'Group Name', 'gsteam' ),
                        'value' => 'name'
                    ],
                ],
                'order' => [
                    [
                        'label' => __( 'DESC', 'gsteam' ),
                        'value' => 'DESC'
                    ],
                    [
                        'label' => __( 'ASC', 'gsteam' ),
                        'value' => 'ASC'
                    ],
                ],

                'image_filter' => $this->get_image_filter_effects(),

                'hover_image_filter' => $this->get_image_filter_effects(),

                // Style Options
                'gs_tm_m_fntw' => [
                    [
                        'label' => __( '100 - Thin', 'gsteam' ),
                        'value' => 100
                    ],
                    [
                        'label' => __( '200 - Extra Light', 'gsteam' ),
                        'value' => 200
                    ],
                    [
                        'label' => __( '300 - Light', 'gsteam' ),
                        'value' => 300
                    ],
                    [
                        'label' => __( '400 - Regular', 'gsteam' ),
                        'value' => 400
                    ],
                    [
                        'label' => __( '500 - Medium', 'gsteam' ),
                        'value' => 500
                    ],
                    [
                        'label' => __( '600 - Semi-Bold', 'gsteam' ),
                        'value' => 600
                    ],
                    [
                        'label' => __( '700 - Bold', 'gsteam' ),
                        'value' => 700
                    ],
                    [
                        'label' => __( '800 - Extra Bold', 'gsteam' ),
                        'value' => 800
                    ],
                    [
                        'label' => __( '900 - Black', 'gsteam' ),
                        'value' => 900
                    ],
                ],
                'gs_tm_m_fnstyl' => [
                    [
                        'label' => __( 'Normal', 'gsteam' ),
                        'value' => 'normal'
                    ],
                    [
                        'label' => __( 'Italic', 'gsteam' ),
                        'value' => 'italic'
                    ],
                ],
                'gs_tm_role_fntw' => [
                    [
                        'label' => __( '100 - Thin', 'gsteam' ),
                        'value' => 100
                    ],
                    [
                        'label' => __( '200 - Extra Light', 'gsteam' ),
                        'value' => 200
                    ],
                    [
                        'label' => __( '300 - Light', 'gsteam' ),
                        'value' => 300
                    ],
                    [
                        'label' => __( '400 - Regular', 'gsteam' ),
                        'value' => 400
                    ],
                    [
                        'label' => __( '500 - Medium', 'gsteam' ),
                        'value' => 500
                    ],
                    [
                        'label' => __( '600 - Semi-Bold', 'gsteam' ),
                        'value' => 600
                    ],
                    [
                        'label' => __( '700 - Bold', 'gsteam' ),
                        'value' => 700
                    ],
                    [
                        'label' => __( '800 - Extra Bold', 'gsteam' ),
                        'value' => 800
                    ],
                    [
                        'label' => __( '900 - Black', 'gsteam' ),
                        'value' => 900
                    ],
                ],
                'gs_tm_role_fnstyl' => [
                    [
                        'label' => __( 'Normal', 'gsteam' ),
                        'value' => 'normal'
                    ],
                    [
                        'label' => __( 'Italic', 'gsteam' ),
                        'value' => 'italic'
                    ],
                ],
            ];
        }

        public function get_shortcode_default_settings() {
            return [
                'num'                             => -1,
                'order'                           => 'DESC',
                'orderby'                         => 'date',
                'group_orderby'                   => 'term_order',
                'group_order'                     => 'ASC',
                'group_hide_empty'                => 'off',
                'gs_team_theme'                   => 'gs-grid-style-five',
                'gs_team_cols'                    => '3',
                'gs_team_cols_tablet'             => '4',
                'gs_team_cols_mobile_portrait'    => '6',
                'gs_team_cols_mobile'             => '12',
                'group'                           => '',
                'exclude_group'                   => '',
                'panel'                           => 'right',
                'gs_teammembers_pop_clm'          => 'two',
                'gs_member_connect'               => 'on',
                'display_ribbon'                  => 'on',        
                'gs_slider_nav_color'             => '',
                'gs_slider_nav_bg_color'          => '',
                'gs_slider_nav_hover_color'       => '',
                'gs_slider_nav_hover_bg_color'    => '',
                'gs_slider_dot_color'             => '',
                'gs_slider_dot_hover_color'       => '',
                'filter_text_color'               => '',
                'filter_active_text_color'        => '',
                'filter_bg_color'                 => '',
                'filter_active_bg_color'          => '',
                'filter_border_color'             => '',
                'filter_active_border_color'      => '',
                'gs_tm_mname_color'               => '',
                'description_color'               => '',
                'info_color'                      => '',
                'info_icon_color'                 => '',
                'description_link_color'          => '',
                'tm_bg_color'                     => '',
                'tm_bg_color_hover'               => '',
                'gs_tm_info_background'           => '',
                'gs_tm_mname_background'          => '',
                'gs_tm_tooltip_background'        => '',
                'gs_tm_hover_icon_background'     => '',
                'gs_tm_ribon_color'               => '',
                'gs_tm_role_color'                => '',
                'gs_tm_arrow_color'               => '',
                'gs_member_name'                  => 'on',
                'gs_member_name_is_linked'        => 'on',
                'gs_member_link_type'             => 'default',
                'gs_member_role'                  => 'on',
                'gs_member_pagination'            => 'off',
                'gs_member_details'               => 'on',
                'gs_desc_scroll_contrl'           => 'on',
                'gs_max_scroll_height'            => '',
                'gs_details_area_height'          => 'off',
                'carousel_enabled'                => 'off',
                'link_preview_image'              => 'off',
                'carousel_navs_enabled'           => 'on',
                'carousel_dots_enabled'           => 'on',
                'carousel_navs_style'             => 'default',
                'carousel_dots_style'             => 'default',
                'filter_enabled'                  => 'off',
                'drawer_style'                    => 'default',
                'panel_style'                     => 'default',
                'popup_style'                     => 'default',
                'filter_style'                    => 'default',
                'gs_desc_allow_html'              => 'off',
                'gs_tm_details_contl'             => 100,
                'gs_member_srch_by_name'          => 'on',
                'gs_member_srch_by_zip'           => 'on',
                'gs_member_srch_by_tag'           => 'off',
                'gs_member_srch_by_company'       => 'off',
                'gs_member_filter_by_desig'       => 'on',
                'gs_member_filter_by_location'    => 'on',
                'gs_member_filter_by_language'    => 'on',
                'gs_member_filter_by_gender'      => 'on',
                'gs_member_filter_by_speciality'  => 'on',
                'gs_member_filter_by_extra_one'  => 'off',
                'gs_member_filter_by_extra_two'  => 'off',
                'gs_member_filter_by_extra_three'  => 'off',
                'gs_member_filter_by_extra_four'  => 'off',
                'gs_member_filter_by_extra_five'  => 'off',
                'gs_member_enable_clear_filters'  => 'off',
                'gs_member_enable_multi_select'   => 'off',
                'gs_member_multi_select_ellipsis' => 'off',
                'gs_filter_all_enabled'           => 'on',
                'enable_child_cats'               => 'off',
                'enable_scroll_animation'         => 'on',
                'fitler_all_text'                 => 'All',
                'gs_team_filter_columns'          => 'two',
                'gs_tm_m_fz'                      => '',
                'gs_tm_m_fntw'                    => '',
                'image_filter'                    => 'none',
                'hover_image_filter'              => 'none',
                'gs_tm_m_fnstyl'                  => '',
                'gs_tm_role_fz'                   => '',
                'gs_tm_role_fntw'                 => '',
                'gs_tm_role_fnstyl'               => '',
                'gs_tm_filter_cat_pos'            => 'center',
                'gs_member_thumbnail_sizes'       => 'large',
                'show_acf_fields'                 => 'off',
                'acf_fields_position'             => 'after_skills',
                'location'                        => '',
                'specialty'                       => '',
                'language'                        => '',
                'gender'                          => '',
                'include_extra_one'               => '',
                'include_extra_two'               => '',
                'include_extra_three'             => '',
                'include_extra_four'              => '',
                'include_extra_five'              => '',
            ];
        }

        public function get_translation($translation_name) {

            $translations = $this->get_shortcode_default_translations();
        
            if ( ! array_key_exists( $translation_name, $translations ) ) return '';

            $prefs = $this->_get_shortcode_pref( false );

            if ( $prefs['gs_member_enable_multilingual'] === 'on' ) return $translations[$translation_name];
        
            return $prefs[ $translation_name ];
        }

        public function get_shortcode_default_translations() {
            $translations = [
                'gs_teamfliter_designation' => __('Show All Designation', 'gsteam'),
                'gs_teamfliter_name' => __('Search By Name', 'gsteam'),
                'gs_teamfliter_company' => __('Search By Company', 'gsteam'),
                'gs_teamfliter_zip' => __('Search By Zip', 'gsteam'),
                'gs_teamfliter_tag' => __('Search By Tag', 'gsteam'),
                'gs_teamcom_meta' => __('Company', 'gsteam'),
                'gs_teamadd_meta' => __('Address', 'gsteam'),
                'gs_teamlandphone_meta' => __('Land Phone', 'gsteam'),
                'gs_teamcellPhone_meta' => __('Cell Phone', 'gsteam'),
                'gs_teamemail_meta' => __('Email', 'gsteam'),
                'gs_team_zipcode_meta' => __('Zip Code', 'gsteam'),
                'gs_team_follow_me_on' => __('Follow Me On', 'gsteam'),
                'gs_team_skills' => __('Skills', 'gsteam'),
                'gs_team_read_on' => __('Read On', 'gsteam'),
                'gs_team_more' => __('More', 'gsteam'),
                'gs_team_vcard_txt' => __('Download vCard', 'gsteam'),
                'gs_team_reset_filters_txt' => __('Reset Filters', 'gsteam'),
                'gs_team_prev_txt' => __('Prev', 'gsteam'),
                'gs_team_next_txt' => __('Next', 'gsteam')
            ];

            return $translations;
        }

        public function get_shortcode_default_prefs() {
            $prefs = [
                'gs_member_nxt_prev'            => 'off',
                'single_page_style'             => 'default',
                'single_link_type'              => 'single_page',
                'gs_member_search_all_fields'   => 'off',
                'gs_member_enable_multilingual' => 'off',
                'gs_teammembers_slug'           => 'team-members',
                'replace_custom_slug'           => 'off',
                'archive_page_slug'             => '',
                'archive_page_title'            => '',
                'disable_google_fonts'          => 'off',
                'show_acf_fields'               => 'off',
                'disable_lazy_load'             => 'off',

                'land_phone_link'               => 'off',
                'cell_phone_link'               => 'off',
                'email_link'                    => 'off',

                'lazy_load_class'               => 'skip-lazy',
                'acf_fields_position'           => 'after_skills',
                'gs_team_custom_css'            => ''
            ];

            $translations = $this->get_shortcode_default_translations();

            $prefs = array_merge( $prefs, $translations );

            return $prefs;
        }

        public function get_taxonomy_default_settings() {

            return [

                // Group Taxonomy
                'enable_group_tax' => 'on',
                'group_tax_label' => __('Group', 'gsteam'),
                'group_tax_plural_label' => __('Groups', 'gsteam'),
                'enable_group_tax_archive' => 'on',
                'group_tax_archive_slug' => 'gs-team-group',

                // Tag Taxonomy
                'enable_tag_tax' => 'on',
                'tag_tax_label' => __('Tag', 'gsteam'),
                'tag_tax_plural_label' => __('Tags', 'gsteam'),
                'enable_tag_tax_archive' => 'on',
                'tag_tax_archive_slug' => 'gs-team-tag',

                // Language Taxonomy
                'enable_language_tax' => 'on',
                'language_tax_label' => __('Language', 'gsteam'),
                'language_tax_plural_label' => __('Languages', 'gsteam'),
                'enable_language_tax_archive' => 'on',
                'language_tax_archive_slug' => 'gs-team-language',

                // Location Taxonomy
                'enable_location_tax' => 'on',
                'location_tax_label' => __('Location', 'gsteam'),
                'location_tax_plural_label' => __('Locations', 'gsteam'),
                'enable_location_tax_archive' => 'on',
                'location_tax_archive_slug' => 'gs-team-location',

                // Gender Taxonomy
                'enable_gender_tax' => 'on',
                'gender_tax_label' => __('Gender', 'gsteam'),
                'gender_tax_plural_label' => __('Genders', 'gsteam'),
                'enable_gender_tax_archive' => 'on',
                'gender_tax_archive_slug' => 'gs-team-gender',

                // Specialty Taxonomy
                'enable_specialty_tax' => 'on',
                'specialty_tax_label' => __('Specialty', 'gsteam'),
                'specialty_tax_plural_label' => __('Specialties', 'gsteam'),
                'enable_specialty_tax_archive' => 'on',
                'specialty_tax_archive_slug' => 'gs-team-specialty',

                // Extra One Taxonomy
                'enable_extra_one_tax' => 'off',
                'extra_one_tax_label' => __('Extra 1', 'gsteam'),
                'extra_one_tax_plural_label' => __('Extra 1', 'gsteam'),
                'enable_extra_one_tax_archive' => 'on',
                'extra_one_tax_archive_slug' => 'gs-team-extra-one',

                // Extra Two Taxonomy
                'enable_extra_two_tax' => 'off',
                'extra_two_tax_label' => __('Extra 2', 'gsteam'),
                'extra_two_tax_plural_label' => __('Extra 2', 'gsteam'),
                'enable_extra_two_tax_archive' => 'off',
                'extra_two_tax_archive_slug' => 'gs-team-extra-two',

                // Extra Three Taxonomy
                'enable_extra_three_tax' => 'off',
                'extra_three_tax_label' => __('Extra 3', 'gsteam'),
                'extra_three_tax_plural_label' => __('Extra 3', 'gsteam'),
                'enable_extra_three_tax_archive' => 'off',
                'extra_three_tax_archive_slug' => 'gs-team-extra-three',

                // Extra Four Taxonomy
                'enable_extra_four_tax' => 'off',
                'extra_four_tax_label' => __('Extra 4', 'gsteam'),
                'extra_four_tax_plural_label' => __('Extra 4', 'gsteam'),
                'enable_extra_four_tax_archive' => 'off',
                'extra_four_tax_archive_slug' => 'gs-team-extra-four',

                // Extra Five Taxonomy
                'enable_extra_five_tax' => 'off',
                'extra_five_tax_label' => __('Extra 5', 'gsteam'),
                'extra_five_tax_plural_label' => __('Extra 5', 'gsteam'),
                'enable_extra_five_tax_archive' => 'off',
                'extra_five_tax_archive_slug' => 'gs-team-extra-five',

            ];

        }

        public function get_tax_option( $option, $default = '' ) {
            $options = (array) get_option( $this->taxonomy_option_name, [] );
            $defaults = $this->get_taxonomy_default_settings();
            $options = array_merge($defaults, $options);

            if ( str_contains($option, '_label') && ( getoption('gs_member_enable_multilingual', 'off') == 'on' ) ) {
                return $defaults[$option];
            }

            if ( str_contains($option, '_label') && empty($options[$option]) ) {
                return $defaults[$option];
            }

            if ( isset($options[$option]) ) return $options[$option];
            return $default;
        }

        public function get_columns() {

            return [
                [
                    'label' => __( '1 Column', 'gsteam' ),
                    'value' => '12'
                ],
                [
                    'label' => __( '2 Columns', 'gsteam' ),
                    'value' => '6'
                ],
                [
                    'label' => __( '3 Columns', 'gsteam' ),
                    'value' => '4'
                ],
                [
                    'label' => __( '4 Columns', 'gsteam' ),
                    'value' => '3'
                ],
                [
                    'label' => __( '5 Columns', 'gsteam' ),
                    'value' => '2_4'
                ],
                [
                    'label' => __( '6 Columns', 'gsteam' ),
                    'value' => '2'
                ],
            ];

        }

        public function get_acf_fields_position() {

            return [
                [
                    'label' => __( 'After Skills', 'gsteam' ),
                    'value' => 'after_skills'
                ],
                [
                    'label' => __( 'After Description', 'gsteam' ),
                    'value' => 'after_description'
                ],
                [
                    'label' => __( 'After Meta Details', 'gsteam' ),
                    'value' => 'after_meta_details'
                ],
            ];

        }

        public function get_image_filter_effects() {

            $effects = [
                [
                    'label' => __( 'None', 'gsteam' ),
                    'value' => 'none'
                ],
                [
                    'label' => __( 'Blur', 'gsteam' ),
                    'value' => 'blur'
                ],
                [
                    'label' => __( 'Brightness', 'gsteam' ),
                    'value' => 'brightness'
                ],
                [
                    'label' => __( 'Contrast', 'gsteam' ),
                    'value' => 'contrast'
                ],
                [
                    'label' => __( 'Grayscale', 'gsteam' ),
                    'value' => 'grayscale'
                ],
                [
                    'label' => __( 'Hue Rotate', 'gsteam' ),
                    'value' => 'hue_rotate'
                ],
                [
                    'label' => __( 'Invert', 'gsteam' ),
                    'value' => 'invert'
                ],
                [
                    'label' => __( 'Opacity', 'gsteam' ),
                    'value' => 'opacity'
                ],
                [
                    'label' => __( 'Saturate', 'gsteam' ),
                    'value' => 'saturate'
                ],
                [
                    'label' => __( 'Sepia', 'gsteam' ),
                    'value' => 'sepia'
                ]
            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $effects = self::add_pro_to_options($effects);
            }

            return $effects;

        }

        public function get_single_page_style() {

            return [
                [
                    'label' => __( 'Default', 'gsteam' ),
                    'value' => 'default'
                ],
                [
                    'label' => __( 'Style One', 'gsteam' ),
                    'value' => 'style-one'
                ],
                [
                    'label' => __( 'Style Two', 'gsteam' ),
                    'value' => 'style-two'
                ],
                [
                    'label' => __( 'Style Three', 'gsteam' ),
                    'value' => 'style-three'
                ],
                [
                    'label' => __( 'Style Four', 'gsteam' ),
                    'value' => 'style-four'
                ],
                [
                    'label' => __( 'Style Five', 'gsteam' ),
                    'value' => 'style-five'
                ]
            ];

        }

        /**
         * Retrives WP registered possible thumbnail sizes.
         * 
         * @since  1.10.14
         * @return array   image sizes.
         */
        public function getPossibleThumbnailSizes() {
            
            $sizes = get_intermediate_image_sizes();

            if ( empty($sizes) ) return [];

            $result = [];

            foreach ( $sizes as $size ) {
                $result[] = [
                    'label' => ucwords( preg_replace('/_|-/', ' ', $size) ),
                    'value' => $size
                ];
            }
            
            return $result;
        }

        public function get_shortcode_prefs_options() {

            $acf_fields_position = $this->get_acf_fields_position();
            $single_page_style = $this->get_single_page_style();
            $single_link_type = [
                [
                    'label' => __( 'None', 'gsteam' ),
                    'value' => 'none'
                ],
                $this->get_shortcode_options_link_types()[1]
            ];

            if ( ! gtm_fs()->is_paying_or_trial() ) {
                $acf_fields_position = self::add_pro_to_options( $acf_fields_position );
                $default = array_shift( $single_page_style );
                $single_page_style = array_merge( [$default], self::add_pro_to_options($single_page_style) );
            }

            return [
                'acf_fields_position' => $acf_fields_position,
                'single_page_style' => $single_page_style,
                'single_link_type' => $single_link_type
            ];
        }

        public function is_multilingual_enabled() {
            return $this->_get_shortcode_pref( false )['gs_member_enable_multilingual'] == 'on';
        }

        public function validate_shortcode_prefs( Array $settings ) {
            foreach ( $settings as $setting_key => $setting_val ) {
                if ( $setting_key == 'gs_team_custom_css' ) {
                    $settings[ $setting_key ] = wp_strip_all_tags( $setting_val );
                } else {
                    $settings[ $setting_key ] = sanitize_text_field( $setting_val );
                }
            }
            return $settings;
        }

        public function _save_shortcode_pref( $settings, $is_ajax ) {

            if ( empty($settings) ) $settings = [];

            $settings = $this->validate_shortcode_prefs( $settings );
            update_option( $this->option_name, $settings, 'yes' );
            
            // Clean permalink flush
            delete_option( 'GS_Team_plugin_permalinks_flushed' );

            do_action( 'gs_team_preference_update' );
            do_action( 'gsp_preference_update' );
        
            if ( $is_ajax ) wp_send_json_success( __('Preference saved', 'gsteam') );

        }

        public function save_shortcode_pref() {

            check_ajax_referer( '_gsteam_admin_nonce_gs_' );
            
            if ( empty($_POST['prefs']) ) {
                wp_send_json_error( __('No preference provided', 'gsteam'), 400 );
            }
    
            $this->_save_shortcode_pref( $_POST['prefs'], true );

        }

        public function _get_shortcode_pref( $is_ajax ) {

            $pref = (array) get_option( $this->option_name, [] );
            $pref = shortcode_atts( $this->get_shortcode_default_prefs(), $pref );

            if ( $is_ajax ) {
                wp_send_json_success( $pref );
            }

            return $pref;
        }

        public function get_shortcode_pref() {
            return $this->_get_shortcode_pref( wp_doing_ajax() );
        }

        public function _get_taxonomy_settings( $is_ajax ) {

            $settings = (array) get_option( $this->taxonomy_option_name, [] );
            $settings = $this->validate_taxonomy_settings( $settings );

            if ( $is_ajax ) {
                wp_send_json_success( $settings );
            }

            return $settings;

        }

        public function validate_taxonomy_settings( $settings ) {

            $defaults = $this->get_taxonomy_default_settings();

            if ( empty($settings) ) {
                $settings = $defaults;
            } else {
                foreach ( $settings as $setting_key => $setting_val ) {
                    if ( str_contains($setting_key, '_label') && empty($setting_val) ) {
                        $settings[$setting_key] = $defaults[$setting_key];
                    }
                }
            }
            
            return array_map( 'sanitize_text_field', $settings );
        }

        public function get_taxonomy_settings() {
            return $this->_get_taxonomy_settings( wp_doing_ajax() );
        }

        public function _save_taxonomy_settings( $settings, $is_ajax ) {

            if ( empty($settings) ) $settings = [];

            $settings = $this->validate_taxonomy_settings( $settings );
            update_option( $this->taxonomy_option_name, $settings, 'yes' );
            
            // Clean permalink flush
            delete_option( 'GS_Team_plugin_permalinks_flushed' );

            do_action( 'gs_team_tax_settings_update' );
            do_action( 'gsp_tax_settings_update' );
        
            if ( $is_ajax ) wp_send_json_success( __('Taxonomy settings saved', 'gsteam') );
        }

        public function save_taxonomy_settings() {

            check_ajax_referer( '_gsteam_admin_nonce_gs_' );
            
            if ( empty($_POST['tax_settings']) ) {
                wp_send_json_error( __('No settings provided', 'gsteam'), 400 );
            }
    
            $this->_save_taxonomy_settings( $_POST['tax_settings'], true );
        }

        static function maybe_create_shortcodes_table() {

            global $wpdb;

            $gs_team_db_version = '1.0';

            if ( get_option("{$wpdb->prefix}gs_team_db_version") == $gs_team_db_version ) return; // vail early
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gs_team (
            	id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
            	shortcode_name TEXT NOT NULL,
            	shortcode_settings LONGTEXT NOT NULL,
            	created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            	updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            	PRIMARY KEY (id)
            )".$wpdb->get_charset_collate().";";
                
            if ( get_option("{$wpdb->prefix}gs_team_db_version") < $gs_team_db_version ) {
                dbDelta( $sql );
            }

            update_option( "{$wpdb->prefix}gs_team_db_version", $gs_team_db_version );
        }

        public function create_dummy_shortcodes() {

            $Dummy_Data = new Dummy_Data();

            $request = wp_remote_get( GSTEAM_PLUGIN_URI . '/includes/demo-data/shortcodes.json', array('sslverify' => false) );

            if ( is_wp_error($request) ) return false;

            $shortcodes = wp_remote_retrieve_body( $request );

            $shortcodes = json_decode( $shortcodes, true );

            $wpdb = $this->get_wpdb();

            if ( ! $shortcodes || ! count($shortcodes) ) return;

            foreach ( $shortcodes as $shortcode ) {

                $shortcode['shortcode_settings'] = json_decode( $shortcode['shortcode_settings'], true );
                $shortcode['shortcode_settings']['gsteam-demo_data'] = true;

                if ( !empty( $group = $shortcode['shortcode_settings']['group']) ) {
                    $shortcode['shortcode_settings']['group'] = (array) $Dummy_Data->get_taxonomy_ids_by_slugs( 'gs_team_group', explode(',', $group) );
                }

                if ( !empty( $exclude_group = $shortcode['shortcode_settings']['exclude_group']) ) {
                    $shortcode['shortcode_settings']['exclude_group'] = (array) $Dummy_Data->get_taxonomy_ids_by_slugs( 'gs_team_group', explode(',', $exclude_group) );
                }
    
                $data = array(
                    "shortcode_name" => $shortcode['shortcode_name'],
                    "shortcode_settings" => json_encode($shortcode['shortcode_settings']),
                    "created_at" => current_time( 'mysql'),
                    "updated_at" => current_time( 'mysql'),
                );
    
                $wpdb->insert( "{$wpdb->prefix}gs_team", $data, $this->get_db_columns() );

            }

        }

        public function delete_dummy_shortcodes() {

            $wpdb = $this->get_wpdb();

            $needle = 'gsteam-demo_data';

            $wpdb->query( "DELETE FROM {$wpdb->prefix}gs_team WHERE shortcode_settings like '%$needle%'" );

            // Delete the shortcode cache
            wp_cache_delete( 'gs_team_shortcodes', 'gs_team_memebrs' );

        }

        public function maybe_upgrade_data( $old_version ) {
            if ( version_compare( $old_version, '1.10.8' ) < 0 ) $this->upgrade_to_1_10_8();
            if ( version_compare( $old_version, '1.10.16' ) < 0 ) $this->upgrade_to_1_10_16();
            if ( version_compare( $old_version, '2.3.6' ) < 0 ) $this->upgrade_to_2_3_6();
            if ( version_compare( $old_version, '2.3.9' ) < 0 ) $this->upgrade_to_2_3_9();
            if ( version_compare( $old_version, '2.5.0' ) < 0 ) $this->upgrade_to_2_5_0();
        }

        public function upgrade_to_1_10_8() {

            $shortcodes = $this->fetch_shortcodes();
            
            foreach ( $shortcodes as $shortcode ) {

                $shortcode_id = $shortcode['id'];
                $shortcode_settings = json_decode( $shortcode["shortcode_settings"], true );

                if ( !in_array( $shortcode_settings['gs_team_theme'], ['gs_tm_theme3', 'gs_tm_theme4', 'gs_tm_theme5', 'gs_tm_theme6'] ) ) {

                    $shortcode_settings['gs_team_cols']                 = 3;
                    $shortcode_settings['gs_team_cols_tablet']          = 4;
                    $shortcode_settings['gs_team_cols_mobile_portrait'] = 6;
                    $shortcode_settings['gs_team_cols_mobile']          = 12;

                } else {

                    $shortcode_settings['gs_team_cols']                 = 4;
                    $shortcode_settings['gs_team_cols_tablet']          = 6;
                    $shortcode_settings['gs_team_cols_mobile_portrait'] = 6;
                    $shortcode_settings['gs_team_cols_mobile']          = 12;

                }

                if ( empty($shortcode_settings['gs_member_link_type']) ) $shortcode_settings['gs_member_link_type'] = 'default';

                unset( $shortcode_settings['gs_team_cols_desktop'] );

                $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
        
                $wpdb = $this->get_wpdb();
            
                $data = array(
                    "shortcode_settings" 	=> json_encode($shortcode_settings),
                    "updated_at" 		    => current_time( 'mysql')
                );
            
                $wpdb->update( "{$wpdb->prefix}gs_team" , $data, array( 'id' => absint( $shortcode_id ) ), [
                    'shortcode_settings' => '%s',
                    'updated_at' => '%s',
                ]);

            }

        }

        public function upgrade_to_1_10_16() {

            $shortcodes = $this->fetch_shortcodes();
            
            foreach ( $shortcodes as $shortcode ) {

                $update             = false;
                $shortcode_id       = $shortcode['id'];
                $shortcode_settings = json_decode( $shortcode["shortcode_settings"], true );
                $group              = $shortcode_settings['group'];
                $exclude_group      = $shortcode_settings['exclude_group'];

                if ( !empty($group) && is_string($group) ) {
                    
                    $update = true;
                    $group = explode( ',', $group );
                    
                    $terms = array_map( function( $group_slug ) {
                        return get_term_by( 'slug', $group_slug, 'gs_team_group' );
                    }, $group );

                    $shortcode_settings['group'] = wp_list_pluck( $terms, 'term_id' );

                }

                if ( !empty($exclude_group) && is_string($exclude_group) ) {
                    
                    $update = true;
                    $exclude_group  = explode( ',', $exclude_group );

                    $terms = array_map( function( $group_slug ) {
                        return get_term_by( 'slug', $group_slug, 'gs_team_group' );
                    }, $exclude_group );

                    $shortcode_settings['exclude_group'] = wp_list_pluck( $terms, 'term_id' );

                }

                if ( ! $update ) continue;

                $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
        
                $wpdb = $this->get_wpdb();
            
                $data = array(
                    "shortcode_settings" 	=> json_encode($shortcode_settings),
                    "updated_at" 		    => current_time( 'mysql')
                );
            
                $wpdb->update( "{$wpdb->prefix}gs_team" , $data, array( 'id' => absint( $shortcode_id ) ), [
                    'shortcode_settings' => '%s',
                    'updated_at' => '%s',
                ]);

            }

        }

        public function upgrade_to_2_3_6() {

            $social_icons_map = [
                "envelope"                => "fas fa-envelope",
                "link"                    => "fas fa-link",
                "google-plus"             => "fab fa-google-plus-g",
                "facebook"                => "fab fa-facebook-f",
                "instagram"               => "fab fa-instagram",
                "whatsapp"                => "fab fa-whatsapp",
                "twitter"                 => "fab fa-x-twitter",
                "youtube"                 => "fab fa-youtube",
                "vimeo-square"            => "fab fa-vimeo-square",
                "flickr"                  => "fab fa-flickr",
                "dribbble"                => "fab fa-dribbble",
                "behance"                 => "fab fa-behance",
                "dropbox"                 => "fab fa-dropbox",
                "wordpress"               => "fab fa-wordpress",
                "tumblr"                  => "fab fa-tumblr",
                "skype"                   => "fab fa-skype",
                "linkedin"                => "fab fa-linkedin-in",
                "stack-overflow"          => "fab fa-stack-overflow",
                "pinterest"               => "fab fa-pinterest",
                "foursquare"              => "fab fa-foursquare",
                "github"                  => "fab fa-github",
                "xing"                    => "fab fa-xing",
                "stumbleupon"             => "fab fa-stumbleupon",
                "delicious"               => "fab fa-delicious",
                "lastfm"                  => "fab fa-lastfm",
                "hacker-news"             => "fab fa-hacker-news",
                "reddit"                  => "fab fa-reddit",
                "soundcloud"              => "fab fa-soundcloud",
                "yahoo"                   => "fab fa-yahoo",
                "trello"                  => "fab fa-trello",
                "steam"                   => "fab fa-steam-symbol",
                "deviantart"              => "fab fa-deviantart",
                "twitch"                  => "fab fa-twitch",
                "feed"                    => "fas fa-rss",
                "renren"                  => "fab fa-renren",
                "vk"                      => "fab fa-vk",
                "vine"                    => "fab fa-vine",
                "spotify"                 => "fab fa-spotify",
                "digg"                    => "fab fa-digg",
                "slideshare"              => "fab fa-slideshare",
                "bandcamp"                => "fab fa-bandcamp",
                "map-pin"                 => "fas fa-map-pin",
                "map-marker"              => "fas fa-map-marker-alt"
            ];

            $team_members = get_posts([
                'numberposts' => -1,
                'post_type' => 'gs_team',
                'fields' => 'ids'
            ]);

            foreach ( $team_members as $team_member_id ) {

                $social_data = get_post_meta( $team_member_id, 'gs_social', true );

                foreach ( $social_data as $key => $social_link ) {
                    if ( ! empty($social_link['icon']) && array_key_exists( $social_link['icon'], $social_icons_map ) ) {
                        $social_data[$key]['icon'] = $social_icons_map[ $social_link['icon'] ];
                    }
                }

                update_post_meta( $team_member_id, 'gs_social', $social_data );

            }

        }

        public function upgrade_to_2_3_9() {
            
            // Team Group
            $this->upgrade_to_2_3_9__taxonomy( 'team_group', 'gs_team_group' );

            // Team Tag
            $this->upgrade_to_2_3_9__taxonomy( 'team_tag', 'gs_team_tag' );

            // Team Gender
            $this->upgrade_to_2_3_9__taxonomy( 'team_gender', 'gs_team_gender' );

            // Team Location
            $this->upgrade_to_2_3_9__taxonomy( 'team_location', 'gs_team_location' );

            // Team Language
            $this->upgrade_to_2_3_9__taxonomy( 'team_language', 'gs_team_language' );

            // Team Specialty
            $this->upgrade_to_2_3_9__taxonomy( 'team_specialty', 'gs_team_specialty' );
    
        }

        public function upgrade_to_2_3_9__taxonomy( $from_taxonomy, $to_taxonomy ) {

            $wpdb = self::get_wpdb();

            $term_taxonomy_ids = $wpdb->get_results( $wpdb->prepare( "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy='%s'", $from_taxonomy ), ARRAY_A );
    
            if ( $this->has_db_error() ) {
                die( sprintf( __( 'GS Team Upgrade failed. Database Error: %s' ), $wpdb->last_error ) );
            }
    
            if ( empty($term_taxonomy_ids) ) return;
    
            $term_taxonomy_ids = wp_list_pluck( $term_taxonomy_ids, 'term_taxonomy_id' );
    
            foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
                $wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => esc_html( $to_taxonomy ) ), array( 'term_taxonomy_id' => $term_taxonomy_id ) );
            }

        }

        public function upgrade_to_2_5_0() {

            // Get the preference settings
            $prefs = $this->get_shortcode_pref();

            // Get the taxonomy settings
            $taxonomy_settings = $this->_get_taxonomy_settings( false );

            // Set the Language Taxonomy Labels
            $taxonomy_settings['language_tax_label'] = $prefs['gs_teamlanguage_meta'];
            $taxonomy_settings['language_tax_plural_label'] = $prefs['gs_teamlanguage_meta'];

            // Set the Location Taxonomy Labels
            $taxonomy_settings['location_tax_label'] = $prefs['gs_teamlocation_meta'];
            $taxonomy_settings['location_tax_plural_label'] = $prefs['gs_teamlocation_meta'];

            // Set the Specialty Taxonomy Labels
            $taxonomy_settings['specialty_tax_label'] = $prefs['gs_teamspecialty_meta'];
            $taxonomy_settings['specialty_tax_plural_label'] = $prefs['gs_teamspecialty_meta'];;

            // Set the Gender Taxonomy Labels
            $taxonomy_settings['gender_tax_label'] = $prefs['gs_teamgender_meta'];
            $taxonomy_settings['gender_tax_plural_label'] = $prefs['gs_teamgender_meta'];

            // Update the taxonomy settings
            $this->_save_taxonomy_settings( $taxonomy_settings, false );

            // Remove old meta settings
            unset( $prefs['gs_teamlanguage_meta'] );
            unset( $prefs['gs_teamlocation_meta'] );
            unset( $prefs['gs_teamspecialty_meta'] );
            unset( $prefs['gs_teamgender_meta'] );

            // Update the shortcode settings
            $this->_save_shortcode_pref( $prefs, false );

        }

    }

}