<?php

namespace GSTEAM;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
	
final class Term_Order {

	/**
	 * @var string Database version
	 */
	public $db_version = 202507201841;

	/**
	 * @var string Database version
	 */
	public $db_version_key = 'gs_team_term_taxonomy_version';

	/**
	 * @var string File for plugin
	 */
	public $file = '';

	/**
	 * @var string URL to plugin
	 */
	public $url = '';

	/**
	 * @var string Path to plugin
	 */
	public $path = '';

	/**
	 * @var string Basename for plugin
	 */
	public $basename = '';

	/**
	 * @var array Which taxonomies are being targeted?
	 */
	public $taxonomies = array();

	/**
	 * @var bool Whether to use fancy ordering
	 */
	public $fancy = true;

	/**
	 * Empty constructor
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		add_action( 'init', [$this, 'init'], 99 );
	}

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Setup plugin
		$this->file     = GSTEAM_PLUGIN_FILE;
		$this->url      = plugin_dir_url( $this->file );
		$this->path     = plugin_dir_path( $this->file );
		$this->basename = plugin_basename( $this->file );

		/**
		 * Allow overriding the UI approach
		 *
		 * @since 1.0.0
		 * @param bool True to use jQuery sortable. False for numbers only.
		 */
		$this->fancy = apply_filters( 'wp_fancy_term_order', true );

		// Queries
		add_filter( 'get_terms_orderby', array( $this, 'get_terms_orderby' ), 20, 2 );
		add_action( 'create_term',       array( $this, 'add_term_order'    ), 20, 3 );
		add_action( 'edit_term',         array( $this, 'add_term_order'    ), 20, 3 );

		// Get visible taxonomies
		$this->taxonomies = $this->get_taxonomies();

		// Always hook these in, for ajax actions
		foreach ( $this->taxonomies as $value ) {

			// Unfancy gets the column
			add_filter( "manage_edit-{$value}_columns",          array( $this, 'add_column_header' ) );
			add_filter( "manage_{$value}_custom_column",         array( $this, 'add_column_value' ), 10, 3 );
			add_filter( "manage_edit-{$value}_sortable_columns", array( $this, 'sortable_columns' ) );

			add_action( "{$value}_add_form_fields",  array( $this, 'term_order_add_form_field'  ) );
			add_action( "{$value}_edit_form_fields", array( $this, 'term_order_edit_form_field' ) );
		}

		// Hide the "order" column by default
		if ( false !== $this->fancy ) {
			add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ), 10, 2 );
		}

		// Ajax actions
		add_action( 'wp_ajax_gs_team_reordering_terms', array( $this, 'ajax_reordering_terms' ) );

		// Only blog admin screens
		if ( is_blog_admin() || doing_action( 'wp_ajax_inline_save_tax' ) || defined( 'WP_CLI' ) ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			// Proceed only if taxonomy supported
			if ( ! empty( $_REQUEST['taxonomy'] ) && $this->taxonomy_supported( $_REQUEST['taxonomy'] ) && ! defined( 'WP_CLI' ) ) {
				add_action( 'load-edit-tags.php', array( $this, 'edit_tags' ) );
			}
		}
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function admin_init() {

		// Check for DB update
		$this->maybe_upgrade_database();
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function edit_tags() {
		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_head-edit-tags.php',          array( $this, 'admin_head'      ) );
		add_action( 'admin_head-edit-tags.php',          array( $this, 'help_tabs'       ) );
		add_action( 'quick_edit_custom_box',             array( $this, 'quick_edit_term_order' ), 10, 3 );
	}

	/** Assets ****************************************************************/

	/**
	 * Check if a taxonomy supports ordering its terms.
	 *
	 * @since 1.0.0
	 * @param array $taxonomy
	 * @return bool Default true
	 */
	public function taxonomy_supported( $taxonomy = array() ) {

		// Defaut return value
		$retval = true;

		if ( is_string( $taxonomy ) ) {
			$taxonomy = (array) $taxonomy;
		}

		if ( is_array( $taxonomy ) ) {
			$taxonomy = array_map( 'sanitize_key', $taxonomy );

			foreach ( $taxonomy as $tax ) {
				if ( ! in_array( $tax, $this->taxonomies, true ) ) {
					$retval = false;
					break;
				}
			}
		}

		// Filter & return
		return (bool) apply_filters( 'gs_team_taxonomy_supported', $retval, $taxonomy );
	}

	/**
	 * Check if a taxonomy supports overriding the orderby of a Term_Query.
	 *
	 * Allows filtering of overriding the orderby specifically.
	 *
	 * @since 2.0.0
	 * @param array $taxonomy
	 * @return bool Default true
	 */
	public function taxonomy_override_orderby_supported( $taxonomy = array() ) {

		// Defaut return value
		$retval = $this->taxonomy_supported( $taxonomy );

		// Filter & return
		return (bool) apply_filters( 'gs_team_taxonomy_override_orderby_supported', $retval, $taxonomy );
	}

	/**
	 * Enqueue quick-edit JS
	 *
	 * @since 0.1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'gs-team-term-order-quick-edit', $this->url . 'includes/term-order/assets/js/quick-edit.js', array( 'jquery' ), $this->db_version, true );

		// Enqueue fancy ordering
		if ( true === $this->fancy ) {
			wp_enqueue_script( 'gs-team-term-order-reorder', $this->url . 'includes/term-order/assets/js/reorder.js', array( 'jquery-ui-sortable' ), $this->db_version, true );
		}
	}

	/**
	 * Contextual help tabs
	 *
	 * @since 0.1.5
	 */
	public function help_tabs() {

		// Drag & Drop
		if ( true === $this->fancy ) {
			get_current_screen()->add_help_tab( array(
				'id'      => 'gsteam_term_order_help_tab',
				'title'   => esc_html__( 'Term Order', 'gsteam' ),
				'content' => '<p>' . esc_html__( 'To reposition an item, drag and drop the row by "clicking and holding" it anywhere and moving it to its new position.', 'gsteam' ) . '</p>',
			) );

		// Numbers only
		} else {
			get_current_screen()->add_help_tab( array(
				'id'      => 'gsteam_term_order_help_tab',
				'title'   => esc_html__( 'Term Order', 'gsteam' ),
				'content' => '<p>' . esc_html__( 'To position an item, Quick Edit the row and change the order value to a more suitable number.', 'gsteam' ) . '</p>',
			) );
		}
	}

	/**
	 * Align custom `order` column, and fancy sortable styling.
	 *
	 * @since 0.1.0
	 */
	public function admin_head() {
		?>

		<style type="text/css">
			.column-order {
				text-align: center;
				width: 74px;
			}

			<?php if ( true === $this->fancy ) : ?>

			.wp-list-table .ui-sortable tr:not(.no-items) {
				cursor: move;
			}

			.striped.dragging > tbody > .ui-sortable-helper ~ tr:nth-child(even) {
				background: #f9f9f9;
			}

			.striped.dragging > tbody > .ui-sortable-helper ~ tr:nth-child(odd) {
				background: #fff;
			}

			.wp-list-table .to-updating tr,
			.wp-list-table .ui-sortable tr.inline-editor {
				cursor: default;
			}

			.wp-list-table .ui-sortable-placeholder {
				outline: 1px dashed #bbb;
				background: #f1f1f1 !important;
				visibility: visible !important;
			}

			.wp-list-table .ui-sortable-helper {
				background-color: #fff !important;
				outline: 1px solid #bbb;
				box-shadow: 0 3px 6px rgba(0, 0, 0, 0.175);
			}

			.wp-list-table.dragging .row-actions,
			.wp-list-table .ui-sortable-helper .row-actions,
			.wp-list-table .ui-sortable-disabled .row-actions,
			.wp-list-table .ui-sortable-disabled tr:hover .row-actions {
				position: relative !important;
				visibility: hidden !important;
			}
			.to-row-updating .check-column {
				background: url('<?php echo admin_url( '/images/spinner.gif' );?>') 10px 9px no-repeat;
			}
			@media print,
			(-o-min-device-pixel-ratio: 5/4),
			(-webkit-min-device-pixel-ratio: 1.25),
			(min-resolution: 120dpi) {
				.to-row-updating .check-column {
					background-image: url('<?php echo admin_url( '/images/spinner-2x.gif' );?>');
					background-size: 20px 20px;
				}
			}
			.to-row-updating .check-column input {
				visibility: hidden;
			}

			<?php endif; ?>

		</style>

		<?php
	}

	/**
	 * Return the taxonomies used by this plugin
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 * @return array
	 */
	private function get_taxonomies( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'show_ui' => true
		) );

		// Get & return the taxonomies
		$taxonomies = get_taxonomies( $r );

		// Filter taxonomies & return
		return (array) apply_filters( 'gs_team_get_taxonomies', $taxonomies, $r, $args );
	}

	/** Columns ***************************************************************/

	/**
	 * Add the "Order" column to taxonomy terms list-tables
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_column_header( $columns = array() ) {
		$columns['order'] = esc_html__( 'Order', 'gsteam' );

		return $columns;
	}

	/**
	 * Output the value for the custom column, in our case: `order`
	 *
	 * @since 0.1.0
	 *
	 * @param string $empty
	 * @param string $custom_column
	 * @param int    $term_id
	 * @return mixed
	 */
	public function add_column_value( $empty = '', $custom_column = '', $term_id = 0 ) {

		// Bail if no taxonomy passed or not on the `order` column
		if ( empty( $_REQUEST['taxonomy'] ) || ( 'order' !== $custom_column ) || ! empty( $empty ) ) {
			return;
		}

		return $this->get_term_order( $term_id );
	}

	/**
	 * Allow sorting by `order` order
	 *
	 * @since 0.1.0
	 * @param array $columns
	 * @return array
	 */
	public function sortable_columns( $columns = array() ) {
		$columns['order'] = 'order';

		return $columns;
	}

	/**
	 * Add `order` to hidden columns
	 *
	 * @since 2.0.0
	 * @param array     $columns
	 * @param WP_Screen $screen
	 * @return array
	 */
	public function hidden_columns( $columns = array(), $screen = '' ) {

		// Bail if not on the `edit-tags` screen for a visible taxonomy
		if ( ( 'edit-tags' !== $screen->base ) || ! $this->taxonomy_supported( $screen->taxonomy ) ) {
			return $columns;
		}

		$columns[] = 'order';

		return $columns;
	}

	/**
	 * Add `order` to term when updating
	 *
	 * @since 0.1.0
	 * @param  int     $term_id   The ID of the term
	 * @param  int     $tt_id     Not used
	 * @param  string  $taxonomy  Taxonomy of the term
	 */
	public function add_term_order( $term_id = 0, $tt_id = 0, $taxonomy = '' ) {

		/*
		 * Bail if order info hasn't been POSTed, like when the "Quick Edit"
		 * form is used to update a term.
		 */
		if ( ! isset( $_POST['order'] ) ) {
			return;
		}

		// Sanitize the value.
		$order = ! empty( $_POST['order'] )
			? (int) $_POST['order']
			: 0;

		// No cache clean required
		$this->set_term_order( $term_id, $taxonomy, $order, false );
	}

	/**
	 * Set order of a specific term
	 *
	 * @since 0.1.0
	 * @global object  $wpdb
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 * @param  int     $order
	 * @param  bool    $clean_cache
	 */
	public function set_term_order( $term_id = 0, $taxonomy = '', $order = 0, $clean_cache = false ) {
		global $wpdb;

		// Avoid malformed order values
		if ( ! is_numeric( $order ) ) {
			$order = 0;
		}

		// Cast to int
		$order = (int) $order;

		// Get existing term order
		$existing_order = $this->get_term_order( $term_id );

		// Bail if no change
		if ( $order === $existing_order ) {
			return;
		}

		// Database query
		$success = $wpdb->update(
			$wpdb->term_taxonomy,
			array(
				'order' => $order
			),
			array(
				'term_id'  => $term_id,
				'taxonomy' => $taxonomy
			),
			array(
				'%d'
			),
			array(
				'%d',
				'%s'
			)
		);

		// Only execute action and clean cache when update succeeds
		if ( ! empty( $success ) ) {

			// Maybe clean the term cache
			if ( true === $clean_cache ) {
				clean_term_cache( $term_id, $taxonomy );
			}
		}

		/**
		 * A term order was successfully set/changed.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gsteam_term_order_set_term_order', $term_id, $taxonomy, $order );
	}

	/**
	 * Return the order of a term
	 *
	 * @since 0.1.0
	 * @param int $term_id
	 */
	public function get_term_order( $term_id = 0 ) {

		// Use false
		$retval = 0;

		// Use taxonomy if available
		$tax = ! empty( $_REQUEST['taxonomy'] )
			? sanitize_key( $_REQUEST['taxonomy'] )
			: '';

		// Get the term, probably from cache at this point
		$term = get_term( $term_id, $tax );

		if ( isset( $term->order ) ) {
			$retval = $term->order;
		}

		// Cast & return
		return (int) $retval;
	}

	/** Markup ****************************************************************/

	/**
	 * Output the "order" form field when adding a new term
	 *
	 * @since 0.1.0
	 */
	public function term_order_add_form_field() {

		// Default classes
		$classes = array(
			'form-field',
			'form-required',
			'gsteam-term-order-form-field',
		);

		/**
		 * Allows filtering HTML classes on the wrapper of the "order" form
		 * field shown when adding a new term.
		 *
		 * @param array $classes
		 */
		$classes = (array) apply_filters( 'gsteam_term_order_add_form_field_classes', $classes, $this );

		?>

		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<label for="order">
				<?php esc_html_e( 'Order', 'gsteam' ); ?>
			</label>
			<input type="number" pattern="[0-9.]+" name="order" id="order" value="0" size="11">
			<p class="description">
				<?php esc_html_e( 'Set a specific order by entering a number (1 for first, etc.) in this field.', 'gsteam' ); ?>
			</p>
		</div>

		<?php
	}

	/**
	 * Output the "order" form field when editing an existing term
	 *
	 * @since 0.1.0
	 * @param object $term
	 */
	public function term_order_edit_form_field( $term = false ) {

		// Default classes
		$classes = array(
			'form-field',
			'gsteam-term-order-form-field',
		);

		/**
		 * Allows filtering HTML classes on the wrapper of the "order" form
		 * field shown when editing an existing term.
		 *
		 * @param array $classes
		 */
		$classes = (array) apply_filters( 'gsteam_term_order_edit_form_field_classes', $classes, $this );

		?>

		<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<th scope="row" valign="top">
				<label for="order">
					<?php esc_html_e( 'Order', 'gsteam' ); ?>
				</label>
			</th>
			<td>
				<input name="order" id="order" type="text" value="<?php echo $this->get_term_order( $term ); ?>" size="11" />
				<p class="description">
					<?php esc_html_e( 'Terms are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.', 'gsteam' ); ?>
				</p>
			</td>
		</tr>

		<?php
	}

	/**
	 * Output the "order" quick-edit field
	 *
	 * @since 0.1.0
	 * @param string $column_name
	 * @param string $screen
	 * @param string $name
	 */
	public function quick_edit_term_order( $column_name = '', $screen = '', $name = '' ) {

		// Bail if not the `order` column on the `edit-tags` screen for a visible taxonomy
		if ( ( 'order' !== $column_name ) || ( 'edit-tags' !== $screen ) || ! $this->taxonomy_supported( $name ) ) {
			return false;
		}

		// Default classes
		$classes = array(
			'inline-edit-col',
			'gsteam-term-order-edit-col',
		);

		/**
		 * Allows filtering HTML classes on the wrapper of the "order"
		 * quick-edit field.
		 *
		 * @param array $classes
		 */
		$classes = (array) apply_filters( 'gsteam_term_order_quick_edit_field_classes', $classes, $this );

		?>

		<fieldset>
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<label>
					<span class="title"><?php esc_html_e( 'Order', 'gsteam' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" pattern="[0-9.]+" class="ptitle" name="order" value="" size="11">
					</span>
				</label>
			</div>
		</fieldset>

		<?php
	}

	/** Query Filters *********************************************************/

	/**
	 * Force `orderby` to `tt.order` if not explicitly set to something else
	 *
	 * @since 0.1.0
	 * @param  string $orderby
	 * @param  array  $args
	 * @return string
	 */
	public function get_terms_orderby( $orderby = 't.name', $args = array() ) {

		// Bail if taxonomy not supported
		if ( ! $this->taxonomy_supported( $args['taxonomy'] ) ) {
			return $orderby;
		}

		// Bail if taxonomy orderby override not supported
		if ( ! $this->taxonomy_override_orderby_supported( $args['taxonomy'] ) ) {
			return $orderby;
		}
		
		global $current_screen;

		$current_screen_base = $current_screen->base ?? '';

		if ( wp_doing_ajax() && isset( $args['orderby'] ) && $args['orderby'] === 'order' ) {
			return 'tt.order, t.name'; // Or 'tt.order'
		}

		if( $current_screen_base !== 'edit-tags' ) {
			$safe_orderbys = ['id', 'name', 'slug', 'count', 'none']; // Native values you allow

			if ( isset($args['orderby']) && in_array($args['orderby'], $safe_orderbys, true) ) {
				return $orderby; // 👈 respect what's passed from settings
			}

			// ✅ Now handle custom 'term_order' ordering
			if ( isset($args['orderby']) && $args['orderby'] === 'order' ) {
				return 'tt.order'; // Or 'tt.order'
			}
		}

		// Default to not overriding
		$override = false;

		// Ordering on admin screens
		if ( is_admin() ) {

			// Look for custom orderby
			$get_orderby = ! empty( $_GET['orderby'] )
				? sanitize_key( $_GET['orderby'] )
				: $orderby;

			// Override if explicitly sorting the UI by the "order" column
			if ( 'order' === $get_orderby ) {
				$override = true;
			}
		}

		// Explicitly asking for "order" column
		if ( 'order' === $args['orderby'] ) {
			$orderby = 'tt.order';

		// Falling back to "t.name" so we'll guess at an override
		} elseif ( 't.name' === $orderby ) {
			$orderby = 'tt.order, t.name';

		// Fallback or override
		} elseif ( empty( $orderby ) || ( true === $override ) ) {
			$orderby = 'tt.order';
		}

		return $orderby;
	}

	/** Database Alters *******************************************************/

	/**
	 * Should a database update occur.
	 *
	 * Runs on `admin_init` hook.
	 *
	 * @since 0.1.0
	 */
	private function maybe_upgrade_database() {

		// Check DB for version
		$db_version = get_option( $this->db_version_key, 0 );

		// Needs
		if ( $db_version < $this->db_version ) {
			$this->upgrade_database( $db_version );
		}
	}

	/**
	 * Modify the `term_taxonomy` table and add an `order` column to it
	 *
	 * @since 0.1.0
	 * @param  int    $old_version
	 * @global object $wpdb
	 */
	private function upgrade_database( $old_version = 0 ) {
		global $wpdb;

		$old_version = (int) $old_version;

		// The main column alter
		if ( $old_version < 202507201841 ) {
			// Check if the 'order' column exists in the term_taxonomy table
			$column_exists = $wpdb->get_results("SHOW COLUMNS FROM `{$wpdb->term_taxonomy}` LIKE 'order';");
			if ( empty( $column_exists ) ) {
				$wpdb->query("ALTER TABLE `{$wpdb->term_taxonomy}` ADD `order` INT(11) NOT NULL DEFAULT 0;");
			}
		}

		// Update the DB version
		update_option( $this->db_version_key, $this->db_version );
	}

	/** Admin Ajax ************************************************************/

	/**
	 * Handle AJAX term reordering
	 *
	 * @since 0.1.0
	 */
	public function ajax_reordering_terms() {

		// Bail if required term data is missing
		if ( empty( $_POST['id'] ) || empty( $_POST['tax'] ) || ( ! isset( $_POST['previd'] ) && ! isset( $_POST['nextid'] ) ) ) {
			die( -1 );
		}

		// Sanitize
		$term_id  = absint( $_POST['id'] );
		$taxonomy = sanitize_key( $_POST['tax'] );

		// Attempt to get the taxonomy
		$tax = get_taxonomy( $taxonomy );

		// Bail if taxonomy does not exist
		if ( empty( $tax ) || ! $this->taxonomy_supported( $tax ) ) {
			die( -1 );
		}

		// Bail if current user cannot assign terms
		if ( ! current_user_can( $tax->cap->edit_terms ) ) {
			die( -1 );
		}

		// Bail if term cannot be found
		$term = get_term( $term_id, $taxonomy );
		if ( empty( $term ) ) {
			die( -1 );
		}

		// Sanitize positions
		$previd   = empty( $_POST['previd']   ) ? false : (int) $_POST['previd'];
		$nextid   = empty( $_POST['nextid']   ) ? false : (int) $_POST['nextid'];
		$start    = empty( $_POST['start']    ) ? 1     : (int) $_POST['start'];
		$excluded = empty( $_POST['excluded'] )
			? array( $term->term_id )
			: array_filter( (array) $_POST['excluded'], 'intval' );

		// Default return values
		$retval  = new \stdClass;
		$new_pos = array();

		// attempt to get the intended parent...
		$parent_id        = $term->parent;
		$next_term_parent = $nextid
			? wp_get_term_taxonomy_parent_id( $nextid, $taxonomy )
			: false;

		// If the preceding term is the parent of the next term, move it inside
		if ( $previd === $next_term_parent ) {
			$parent_id = $next_term_parent;

		// If the next term's parent isn't the same as our parent, we need more info
		} elseif ( $next_term_parent !== $parent_id ) {
			$prev_term_parent = $previd
				? wp_get_term_taxonomy_parent_id( $nextid, $taxonomy )
				: false;

			// If the previous term is not our parent now, set it
			if ( $prev_term_parent !== $parent_id ) {
				$parent_id = ( $prev_term_parent !== false )
					? $prev_term_parent
					: $next_term_parent;
			}
		}

		// If the next term's parent isn't our parent, set to false
		if ( $next_term_parent !== $parent_id ) {
			$nextid = false;
		}

		// Get term siblings for relative ordering
		$siblings = get_terms( $taxonomy, array(
			'depth'      => 1,
			'number'     => 100,
			'parent'     => $parent_id,
			'orderby'    => 'order',
			'order'      => 'ASC',
			'hide_empty' => false,
			'exclude'    => array_unique( $excluded )
		) );

		// Loop through siblings and update terms
		foreach ( $siblings as $sibling ) {

			// Skip the actual term if it's in the array
			if ( (int) $sibling->term_id === (int) $term->term_id ) {
				continue;
			}

			// If this is the term that comes after our repositioned term, set
			// our repositioned term position and increment order
			if ( $nextid === (int) $sibling->term_id ) {
				$this->set_term_order( $term->term_id, $taxonomy, $start, true );

				$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

				$new_pos[ $term->term_id ] = array(
					'order'  => $start,
					'parent' => $parent_id,
					'depth'  => count( $ancestors ),
				);

				$start++;
			} else {
				$ancestors = array();
			}

			// Get the term order from object
			$order = $this->get_term_order( $sibling->term_id );

			// If repositioned term has been set and new items are already in
			// the right order, we can stop looping
			if ( isset( $new_pos[ $term->term_id ] ) && ( $order >= $start ) ) {
				$retval->next = false;
				break;
			}

			// Set order of current sibling and increment the order
			if ( $start !== $order ) {
				$this->set_term_order( $sibling->term_id, $taxonomy, $start, true );
			}

			$new_pos[ $sibling->term_id ] = array(
				'order'  => $start,
				'parent' => $parent_id,
				'depth'  => count( $ancestors )
			);

			$start++;

			if ( empty( $nextid ) && ( $previd === (int) $sibling->term_id ) ) {
				$this->set_term_order( $term->term_id, $taxonomy, $start, true );

				$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

				$new_pos[ $term->term_id ] = array(
					'order'  => $start,
					'parent' => $parent_id,
					'depth'  => count( $ancestors ),
				);

				$start++;
			} else {
				$ancestors = array();
			}
		}

		// max per request
		if ( ! isset( $retval->next ) && count( $siblings ) > 1 ) {
			$retval->next = array(
				'id'       => $term->term_id,
				'previd'   => $previd,
				'nextid'   => $nextid,
				'start'    => $start,
				'excluded' => array_unique( array_merge( array_keys( $new_pos ), $excluded ) ),
				'taxonomy' => $taxonomy
			);
		} else {
			$retval->next = false;
		}

		if ( empty( $retval->next ) ) {

			// If the moved term has children, refresh the page for UI reasons
			$children = get_terms( $taxonomy, array(
				'number'     => 1,
				'depth'      => 1,
				'orderby'    => 'order',
				'order'      => 'ASC',
				'parent'     => $term->term_id,
				'fields'     => 'ids',
				'hide_empty' => false
			) );

			if ( ! empty( $children ) ) {
				die( 'children' );
			}
		}

		// Add to return value
		$retval->new_pos = $new_pos;

		die( json_encode( $retval ) );
	}
}