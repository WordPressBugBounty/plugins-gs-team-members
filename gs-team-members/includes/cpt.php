<?php 

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Cpt {

	// Constructor
	public function __construct() {

		add_action( 'init', [ $this, 'register' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'after_setup_theme', [ $this, 'theme_support' ] );

		if ( ! gtm_fs()->is_paying_or_trial() ) {
			$this->dummy_tax();
		}
		
	}

	// Register Custom Post Type
	function register() {
		$labels = array(
			'name'               => is_admin() ? get_post_type_title() : gs_get_post_type_archive_title(),
			'singular_name'      => __( 'Team', 'gsteam' ),
			'menu_name'          => _x( 'GS Team', 'admin menu', 'gsteam' ),
			'name_admin_bar'     => _x( 'GS Team', 'add new on admin bar', 'gsteam' ),
			'add_new'            => __( 'Add New Member', 'gsteam' ),
			'add_new_item'       => __( 'Add New Member', 'gsteam' ),
			'new_item'           => __( 'New Team', 'gsteam' ),
			'edit_item'          => __( 'Edit Team', 'gsteam' ),
			'view_item'          => __( 'View Team', 'gsteam' ),
			'all_items'          => __( 'All Members', 'gsteam' ),
			'search_items'       => __( 'Search Members', 'gsteam' ),
			'parent_item_colon'  => __( 'Parent Teams:', 'gsteam' ),
			'not_found'          => __( 'No Teams found.', 'gsteam' ),
			'not_found_in_trash' => __( 'No Teams found in Trash.', 'gsteam' ),
		);

		$gs_teammembers_slug = getoption( 'gs_teammembers_slug', 'team-members' );
		$replace_custom_slug = getoption( 'replace_custom_slug', 'off' ) === 'off';

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $gs_teammembers_slug, 'with_front' => $replace_custom_slug ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => GSTEAM_MENU_POSITION,
			'menu_icon'          => GSTEAM_PLUGIN_URI . '/assets/img/icon.svg',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'wpml_cf_fields'     => true,
			'show_in_wpml_language_switcher' => true
		);

		register_post_type( 'gs_team', $args );
	}

	// Register Taxonomies
	public function register_taxonomies() {
		
		$this->group();
		$this->tag();

		if ( gtm_fs()->is_paying_or_trial() ) {
			$this->language();
			$this->location();
			$this->gender();
			$this->specialty();
			$this->extra_one();
			$this->extra_two();
			$this->extra_three();
			$this->extra_four();
			$this->extra_five();
		}
	}
	
	// Register Group Taxonomy For Team
	function group() {

		if ( plugin()->builder->get_tax_option('enable_group_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('group_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('group_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('group_tax_archive_slug', 'gs-team-group'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_group', array( 'gs_team' ), $args );

	}
	
	// Register Tag Taxonomy For Team
	function tag() {

		if ( plugin()->builder->get_tax_option('enable_tag_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('tag_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('tag_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('tag_tax_archive_slug', 'gs-team-tag'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_tag', array( 'gs_team' ), $args );

	}

	// Register Language Taxonomy For Team
	function language() {

		if ( plugin()->builder->get_tax_option('enable_language_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('language_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('language_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('language_tax_archive_slug', 'gs-team-language'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_language', array( 'gs_team' ), $args );

	}

	// Register Location Taxonomy For Team
	function location() {

		if ( plugin()->builder->get_tax_option('enable_location_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('location_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('location_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('location_tax_archive_slug', 'gs-team-location'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_location', array( 'gs_team' ), $args );

	}

	// Register Gender Taxonomy For Team
	function gender() {

		if ( plugin()->builder->get_tax_option('enable_gender_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('gender_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('gender_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('gender_tax_archive_slug', 'gs-team-gender'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_gender', array( 'gs_team' ), $args );

	}

	// Register Specialty Taxonomy For Team
	function specialty() {

		if ( plugin()->builder->get_tax_option('enable_specialty_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('specialty_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('specialty_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('specialty_tax_archive_slug', 'gs-team-specialty'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_specialty', array( 'gs_team' ), $args );

	}

	// Register Extra One Taxonomy For Team
	function extra_one() {

		if ( plugin()->builder->get_tax_option('enable_extra_one_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_one_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_one_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_one_tax_archive_slug', 'gs-team-extra-one'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_extra_one', array( 'gs_team' ), $args );

	}

	// Register Extra Two Taxonomy For Team
	function extra_two() {

		if ( plugin()->builder->get_tax_option('enable_extra_two_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_two_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_two_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_two_tax_archive_slug', 'gs-team-extra-two'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_extra_two', array( 'gs_team' ), $args );

	}

	// Register Extra Three Taxonomy For Team
	function extra_three() {

		if ( plugin()->builder->get_tax_option('enable_extra_three_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_three_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_three_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_three_tax_archive_slug', 'gs-team-extra-three'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_extra_three', array( 'gs_team' ), $args );

	}

	// Register Extra Four Taxonomy For Team
	function extra_four() {

		if ( plugin()->builder->get_tax_option('enable_extra_four_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_four_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_four_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_four_tax_archive_slug', 'gs-team-extra-four'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_extra_four', array( 'gs_team' ), $args );

	}

	// Register Extra Five Taxonomy For Team
	function extra_five() {

		if ( plugin()->builder->get_tax_option('enable_extra_five_tax') !== 'on' ) return;

		$plural = plugin()->builder->get_tax_option('extra_five_tax_plural_label');
		$singular = plugin()->builder->get_tax_option('extra_five_tax_label');

		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'all_items'                  => sprintf( __('All %s'), $plural ),
			'parent_item'                => sprintf( __('Parent %s'), $singular ),
			'parent_item_colon'          => sprintf( __('Parent %s'), $singular ),
			'new_item_name'              => sprintf( __('New %s'), $singular ),
			'add_new_item'               => sprintf( __('Add New %s'), $singular ),
			'edit_item'                  => sprintf( __('Edit %s'), $singular ),
			'update_item'                => sprintf( __('Update %s'), $singular ),
			'separate_items_with_commas' => sprintf( __('Separate %s with commas'), $plural ),
			'search_items'               => sprintf( __('Search %s'), $plural ),
			'add_or_remove_items'        => sprintf( __('Add or remove %s'), $plural ),
			'choose_from_most_used'      => sprintf( __('Choose from the most used %s'), $plural ),
			'not_found'                  => __( 'Not Found', 'gsteam' ),
		);
		$rewrite = array(
			'slug'                       => plugin()->builder->get_tax_option('extra_five_tax_archive_slug', 'gs-team-extra-five'),
			'with_front'                 => true,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'rewrite'                    => $rewrite,
		);
		register_taxonomy( 'gs_team_extra_five', array( 'gs_team' ), $args );

	}

	// Add theme support for Featured Images
	function theme_support() {
		// Add Shortcode support in text widget
		add_filter( 'widget_text', 'do_shortcode' );
	}

	// Add Pro Guard
    function add_pro_guard() {
		echo '<div class="gs-team-disable--term-pages"><div class="gs-team-pro-field--inner"><div class="gs-team-pro-field--content"><a href="https://www.gsplugins.com/product/gs-team-members/#pricing">Upgrade to PRO</a></div></div></div>';
    }

	// Remove Actions
    function term_remove_actions() {
        return [];
    }

	// Dummy Tax
	function dummy_tax() {

		add_action( 'gs_team_language_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_location_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_gender_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_specialty_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_one_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_two_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_three_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_four_pre_add_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_five_pre_add_form', [ $this, 'add_pro_guard' ] );
	
		add_action( 'gs_team_language_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_location_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_gender_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_specialty_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_one_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_two_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_three_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_four_pre_edit_form', [ $this, 'add_pro_guard' ] );
		add_action( 'gs_team_extra_five_pre_edit_form', [ $this, 'add_pro_guard' ] );
	
		add_filter( "gs_team_language_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_location_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_gender_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_specialty_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_extra_one_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_extra_two_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_extra_three_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_extra_four_row_actions", [ $this, 'term_remove_actions' ] );
		add_filter( "gs_team_extra_five_row_actions", [ $this, 'term_remove_actions' ] );
		
	}

}