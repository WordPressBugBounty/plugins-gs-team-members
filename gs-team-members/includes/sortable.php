<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
defined('ABSPATH') || exit;

class Sortable {

	public function __construct() {
		
		// Add sortable menu in admin
		add_action('admin_menu', [$this, 'gs_team_sortable']);
		
		// Set object_type if not set & Redirect to the correct page
		add_action('admin_init', [$this, 'maybe_redirect']);
		
		// Add term_order column to terms table
		add_filter('plugins_loaded', array($this, 'alter_terms_table'), 0);
		
		// Set custom order for terms
		add_filter('get_terms_orderby', array($this, 'get_terms_orderby'), 1, 2);
		add_filter('terms_clauses', array($this, 'terms_clauses'), 10, 3);
		
		// Update team members order via AJAX
		add_action('wp_ajax_update_team_members_order', array($this, 'update_team_members_order'));
		
		// Update taxonomy order via AJAX
		add_action('wp_ajax_update_taxonomy_order', array($this, 'update_taxonomy_order'));
		
		// Update team filters order via AJAX
		add_action('wp_ajax_update_team_filters_order', array($this, 'update_team_filters_order'));
		
		// Enqueue admin scripts for sorting
		add_action('admin_enqueue_scripts', array($this, 'sort_scripts'));
		
		// Set custom order for posts
		add_filter('posts_orderby', array($this, 'order_posts'));
	}

	/**
	 * Update taxonomy order
	 */
	public function update_taxonomy_order() {

		if (!$this->is_pro()) wp_send_json_error();

		if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gsteam_update_order_gs_')) {
			wp_send_json_error(__('Unauthorised Request', 'gsteam'), 401);
		}

		global $wpdb;

		$order = explode(',', sanitize_text_field($_POST['order']));
		$counter = 0;

		foreach ($order as $term_id) {
			$wpdb->update($wpdb->terms, array('term_order' => $counter), array('term_id' => (int) $term_id));
			$counter++;
		}

		return wp_send_json_success();
	}

	/**
	 * Update Team Filters Order
	 */
	public function update_team_filters_order() {

		if (!$this->is_pro()) wp_send_json_error();

		if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gsteam_update_order_gs_')) {
			wp_send_json_error(__('Unauthorised Request', 'gsteam'), 401);
		}

		$order = explode(',', sanitize_text_field($_POST['order']));
		update_option('gs_team_filters_order', $order);

		wp_send_json_success();
	}

	/**
	 * Order Posts
	 */
	public function order_posts($orderby) {
		
		global $wpdb;
		global $wp_query;
		
		if ( ! isset($wp_query) || ! is_main_query() ) return $orderby;

		if ( is_post_type_archive('gs_team') ) {
			$orderby = "{$wpdb->posts}.menu_order, {$wpdb->posts}.post_date DESC";
		}

		return ($orderby);
	}

	/**
	 * Enqueue Sortable Scripts
	 */
	public function sort_scripts($hook) {

		if ( $hook != 'gs_team_page_sort_gs_team' ) return;

		plugin()->scripts->wp_enqueue_style('gs-team-sort');
		plugin()->scripts->wp_enqueue_script('gs-team-sort');

		if ( $this->is_pro() ) {

			if ( empty($_GET['object_type']) || $_GET['object_type'] == 'gs_team' ) {
				$action = 'update_team_members_order';
			} else if ( $_GET['object_type'] == 'gs_team_filters' ) {
				$action = 'update_team_filters_order';
			} else {
				$action = 'update_taxonomy_order';
			}

			$data = [
				'nonce' => wp_create_nonce('_gsteam_update_order_gs_'),
				'action' => $action
			];

			wp_localize_script('gs-team-sort', '_gsteam_sort_data', $data);
		}

		add_fs_script('gs-team-sort');
	}

	/**
	 * Update Team Members Order
	 */
	public function update_team_members_order() {

		if (empty($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_gsteam_update_order_gs_')) {
			wp_send_json_error(__('Unauthorised Request', 'gsteam'), 401);
		}

		global $wpdb;

		$order = explode(',', $_POST['order']);
		$counter = 0;

		foreach ($order as $post_id) {
			$wpdb->update($wpdb->posts, array('menu_order' => $counter), array('ID' => (int) $post_id));
			$counter++;
		}

		return wp_send_json_success();
	}

	/**
	 * Set custom order for terms
	 */
	public function terms_clauses($clauses, $taxonomies, $args) {

		if (empty($args['taxonomy'])) return $clauses;

		if (!$this->is_pro() || !in_array('gs_team_group', $args['taxonomy'])) return $clauses;

		$options = [
			'adminsort' => '1',
			'autosort' => '1',
		];

		// if admin make sure use the admin setting
		if (is_admin()) {
			// return if use orderby columns
			if (isset($_GET['orderby']) && $_GET['orderby'] != 'term_order') return $clauses;
			if ($options['adminsort'] == "1") $clauses['orderby'] = 'ORDER BY t.term_order';
			return $clauses;
		}

		// if autosort, then force the menu_order
		if ($options['autosort'] == 1 && (!isset($args['ignore_term_order']) || (isset($args['ignore_term_order']) && $args['ignore_term_order'] !== TRUE))) {
			$clauses['orderby'] = 'ORDER BY t.term_order';
		}

		return $clauses;
	}

	/**
	 * Modify and Return terms orderby
	 */
	public function get_terms_orderby($orderby, $args) {

		if (empty($args['taxonomy'])) return $orderby;

		if ($this->is_pro() && in_array('gs_team_group', $args['taxonomy'])) {
			if (isset($args['orderby']) && $args['orderby'] == "term_order" && $orderby != "term_order") return "t.term_order";
		}

		return $orderby;
	}

	/**
	 * Alter terms table and add term_order column
	 */
	public function alter_terms_table() {

		if (!$this->is_pro()) return;

		if (get_site_option('gsp_terms_table_altered', false) !== false) return;

		global $wpdb;

		//check if the menu_order column exists;
		$query = "SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'";
		$result = $wpdb->query($query);

		if ($result == 0) {
			$query = "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'";
			$result = $wpdb->query($query);

			update_site_option('gsp_terms_table_altered', true);
		}
	}

	/**
	 * Check if PRO version is active
	 */
	public function is_pro() {
		return gtm_fs()->is_paying_or_trial();
	}

	/**
	 * Redirect to the correct page
	 */
	public function maybe_redirect() {
		if ( isset($_GET['post_type']) && $_GET['post_type'] == 'gs_team' && isset($_GET['page']) && $_GET['page'] === 'sort_gs_team' && empty($_GET['object_type']) ) {
			wp_redirect( $this->get_url_with_object_type() );
			exit;
		}
	}

	/**
	 * Add Sortable Menu
	 */
	public function gs_team_sortable() {
		add_submenu_page(
			'edit.php?post_type=gs_team',
			'Sort Order',
			'Sort Order',
			'publish_pages',
			'sort_gs_team',
			[$this, 'gs_team_sortable_callback']
		);
	}

	/**
	 * Get the full URL
	 */
	public function get_full_url() {
		// Get the protocol
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

		// Get the host
		$host = $_SERVER['HTTP_HOST'];

		// Get the request URI
		$uri = $_SERVER['REQUEST_URI'];

		// Combine them to get the full URL
		$full_url = $protocol . $host . $uri;

		return $full_url;
	}

	/**
	 * Get the URL with object type
	 */
	public function get_url_with_object_type( $object = 'gs_team' ) {
		return add_query_arg( 'object_type', $object, $this->get_full_url() );
	}

	/**
	 * Sortable Callback
	 */
	public function gs_team_sortable_callback() {
		
		$object_type = isset($_GET['object_type']) ? $_GET['object_type'] : 'gs_team';

		?>

		<div class="gs-plugins--sort-page">

			<div class="gs-plugins--sort-links">
				<a class="<?php echo $object_type === 'gs_team' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type('gs_team') ); ?>">Team Members</a>
				<a class="<?php echo $object_type === 'gs_team_group' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type('gs_team_group') ); ?>">Groups</a>
				<a class="<?php echo $object_type === 'gs_team_filters' ? 'gs-sort-active' : ''; ?>" href="<?php echo esc_url( $this->get_url_with_object_type('gs_team_filters') ); ?>">Team Filters</a>
			</div>

			<div class="gs-plugins--sort-content">

				<!-- <h2>Sort Order <img src="<?php // bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h2> -->
				
				<?php if ($object_type === 'gs_team') : ?>

					<?php $this->sort_team_members(); ?>

				<?php elseif ($object_type === 'gs_team_filters') : ?>

					<?php $this->sort_team_filters(); ?>

				<?php else : ?>

					<?php $this->sort_team_taxonomies(); ?>

				<?php endif; ?>
			</div>

		</div>

		<?php

	}

	/**
	 * Sort Team Members
	 */
	public function sort_team_members() {


		$sortable = new \WP_Query('post_type=gs_team&posts_per_page=-1&orderby=menu_order&order=ASC');

		if (!$this->is_pro()) : ?>

			<div class="gs-team-disable--term-pages">
				<div class="gs-team-disable--term-inner">
					<div class="gs-team-disable--term-message"><a href="https://www.gsplugins.com/product/gs-team-members/#pricing">Upgrade to PRO</a></div>
				</div>
			</div>

		<?php endif; ?>

		<div class="gs-team--sort-wrap <?php echo $this->is_pro() ? 'sort--wrap-active' : ''; ?>">

			<div style="display: flex; width: 100%; max-width: 1280px; gap: 40px; flex-wrap: wrap;">

				<div class="gsteam-sort--left-area" style="flex: 1 0 auto; width: 670px;">

					<h3><?php esc_html_e('Step 1: Drag & Drop to rearrange Members', 'gsteam'); ?><img src="<?php // bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>

					<?php if ($sortable->have_posts()) : ?>

						<ul id="sortable-list">
							<?php while ($sortable->have_posts()) :

								$sortable->the_post();
								$term_obj_list = get_the_terms(get_the_ID(), 'gs_team_group');
								$terms_string = '';

								if (is_array($term_obj_list) || is_object($term_obj_list)) {
									$terms_string = join('</span><span>', wp_list_pluck($term_obj_list, 'name'));
								}

								if (!empty($terms_string)) $terms_string = '<span>' . $terms_string . '</span>';

							?>

								<li id="<?php the_id(); ?>">
									<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
									<div class="sortable-content sortable-thumbnail"><span><?php member_thumbnail('thumbnail', true); ?></span></div>
									<div class="sortable-content sortable-title"><?php the_title(); ?></div>
									<div class="sortable-content sortable-group"><?php echo wp_kses_post($terms_string); ?></div>
								</li>

							<?php endwhile; ?>
						</ul>

					<?php else : ?>

						<div class="notice notice-warning" style="margin-top: 30px;">
							<h3><?php _e('No Team Member Found!', 'gsteam'); ?></h3>
							<p style="font-size: 14px;"><?php _e('We didn\'t find any team member.</br>Please add some team members to sort them.', 'gsteam'); ?></p>
							<a href="<?php echo admin_url('post-new.php?post_type=gs_team'); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php _e('Add Member', 'gsteam'); ?></a>
						</div>

					<?php endif; ?>

				</div>

				<div class="gsteam-sort--right-area">
					
					<h3><?php esc_html_e('Step 2: Query Settings for Members', 'gsteam'); ?></h3>

					<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">
						
						<ol style="list-style: numeric; padding-left: 20px; margin: 0">
							<li>Create or Edit a Shortcode From <strong>GS Team > Team Shortcode</strong>.</li>
							<li>Then proceed to the 3rd tab labeled <strong>Query Settings</strong>.</li>
							<li>Set <strong>Order by</strong> to <strong>Custom Order</strong>.</li>
							<li>Set <strong>Order</strong> to <strong>ASC</strong>.</li>
						</ol>
	
						<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
							<li>Follow <a href="https://docs.gsplugins.com/gs-team-members/manage-gs-team-member/sort-order/" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>

				</div>

			</div>

		</div><!-- #wrap -->

		<?php

	}

	/**
	 * Get Team Filters Strings
	 */
	public static function get_team_filters_strings() {
		$translations = plugin()->builder->get_translation_srtings();
		$team_filters = [
			'search_by_name' => $translations['instant-search-by-name'],
			'search_by_company' => $translations['gs-member-srch-by-company'],
			'search_by_zip' => $translations['gs-member-srch-by-zip'],
			'gs_team_tag' => $translations['gs-member-srch-by-tag'],
			'filter_by_designation' => $translations['filter-by-designation'],
			'gs_team_language' => $translations['filter-by-language'],
			'gs_team_location' => $translations['filter-by-location'],
			'gs_team_gender' => $translations['filter-by-gender'],
			'gs_team_specialty' => $translations['filter-by-speciality'],
			'gs_team_extra_one' => $translations['filter-by-extra-one'],
			'gs_team_extra_two' => $translations['filter-by-extra-two'],
			'gs_team_extra_three' => $translations['filter-by-extra-three'],
			'gs_team_extra_four' => $translations['filter-by-extra-four'],
			'gs_team_extra_five' => $translations['filter-by-extra-five']
		];
		return $team_filters;
	}

	/**
	 * Get Team Filters
	 */
	public static function get_team_filters() {
		$defaults = [
			'search_by_name',
			'search_by_company',
			'search_by_zip',
			'gs_team_tag',
			'filter_by_designation',
			'gs_team_language',
			'gs_team_location',
			'gs_team_gender',
			'gs_team_specialty',
			'gs_team_extra_one',
			'gs_team_extra_two',
			'gs_team_extra_three',
			'gs_team_extra_four',
			'gs_team_extra_five',
		];
		$saved = get_option( 'gs_team_filters_order', $defaults );
		$filter_strings = self::get_team_filters_strings();
		return array_merge(array_flip($saved), $filter_strings);
	}

	/**
	 * Sort Team Filters
	 */
	public function sort_team_filters() {

		if (!$this->is_pro()) : ?>

			<div class="gs-team-disable--term-pages">
				<div class="gs-team-disable--term-inner">
					<div class="gs-team-disable--term-message"><a href="https://www.gsplugins.com/product/gs-team-members/#pricing">Upgrade to PRO</a></div>
				</div>
			</div>

		<?php endif;

		$filters = self::get_team_filters();

		?>

		<div class="gs-team--sort-wrap <?php echo $this->is_pro() ? 'sort--wrap-active' : ''; ?>">

			<div style="display: flex; width: 100%; max-width: 1280px; gap: 40px; flex-wrap: wrap;">

				<div class="gsteam-sort--left-area" style="flex: 1 0 auto; width: 570px;">

					<h3><?php esc_html_e('Filter Orders', 'gsteam'); ?><img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>

					<?php if (!empty($filters)) : ?>

						<ul id="sortable-list" style="max-width: 600px;">
							<?php foreach ($filters as $filter => $filter_title) : ?>

								<li id="<?php echo esc_attr($filter); ?>">
									<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
									<div class="sortable-content sortable-title"><?php echo esc_html($filter_title); ?></div>
								</li>

							<?php endforeach; ?>
						</ul>

					<?php endif; ?>

				</div>

			</div>

		</div><!-- #wrap -->

		<?php
	}

	/**
	 * Sort Team Taxonomies
	 */
	public function sort_team_taxonomies() {

		if (!$this->is_pro()) : ?>

			<div class="gs-team-disable--term-pages">
				<div class="gs-team-disable--term-inner">
					<div class="gs-team-disable--term-message"><a href="https://www.gsplugins.com/product/gs-team-members/#pricing">Upgrade to PRO</a></div>
				</div>
			</div>

		<?php endif; ?>

		<div class="gs-team--sort-wrap <?php echo $this->is_pro() ? 'sort--wrap-active' : ''; ?>">

			<div style="display: flex; width: 100%; max-width: 1280px; gap: 40px; flex-wrap: wrap;">

				<div class="gsteam-sort--left-area" style="flex: 1 0 auto; width: 570px;">

					<h3><?php esc_html_e('Step 1: Drag & Drop to rearrange Groups', 'gsteam'); ?><img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>

					<?php

					$terms = get_terms('gs_team_group');

					if (!empty($terms)) : ?>

						<ul id="sortable-list" style="max-width: 600px;">
							<?php foreach ($terms as $term) : ?>

								<li id="<?php echo esc_attr($term->term_id); ?>">
									<div class="sortable-content sortable-icon"><i class="fas fa-arrows-alt" aria-hidden="true"></i></div>
									<div class="sortable-content sortable-title"><?php echo esc_html($term->name); ?></div>
									<div class="sortable-content sortable-group"><span><?php echo absint($term->count) . ' ' . 'Members'; ?></span></div>
								</li>

							<?php endforeach; ?>
						</ul>

					<?php else : ?>

						<div class="notice notice-warning" style="margin-top: 30px;">
							<h3><?php _e('No Team Member Found!', 'gsteam'); ?></h3>
							<p style="font-size: 14px;"><?php _e('We didn\'t find any team member.</br>Please add some team members to sort them.', 'gsteam'); ?></p>
							<a href="<?php echo admin_url('post-new.php?post_type=gs_team'); ?>" style="margin-top: 10px; margin-bottom: 20px;" class="button button-primary button-large"><?php _e('Add Member', 'gsteam'); ?></a>
						</div>

					<?php endif; ?>

				</div>

				<div class="gsteam-sort--right-area">
					
					<h3><?php esc_html_e('Step 2: Query Settings for Groups', 'gsteam'); ?></h3>

					<div style="background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.12); font-size: 1.3em; line-height: 1.6; margin-top: 30px">
						
						<ol style="list-style: numeric; padding-left: 20px; margin: 0">
							<li>Create or Edit a Shortcode From <strong>GS Team > Team Shortcode</strong>.</li>
							<li>Then proceed to the 3rd tab labeled <strong>Query Settings</strong>.</li>
							<li>Set <strong>Group Order by</strong> to <strong>Custom Order</strong>.</li>
							<li>Set <strong>Group Order</strong> to <strong>ASC</strong>.</li>
						</ol>
	
						<ul style="list-style: circle; padding-left: 20px; margin-top: 20px">
							<li>Follow <a href="https://docs.gsplugins.com/gs-team-members/manage-gs-team-member/sort-order/#reordering-groups-categories" target="_blank">Documentation</a> to learn more.</li>
							<li><a href="https://www.gsplugins.com/contact/" target="_blank">Contact us</a> for support.</li>
						</ul>

					</div>

				</div>

			</div>

		</div><!-- #wrap -->

		<?php
	}

}