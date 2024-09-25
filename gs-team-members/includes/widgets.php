<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds Widgets widget.
 */
class Widgets extends \WP_Widget {
	
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'widgets', // Base ID
			__( 'GS Team Members', 'gsteam' ), // Name
			array( 'description' => __( 'Display Team Members at widget area.', 'gsteam' ), ) // Args
		);
	}

	public function get_default_instance() {

		return [
			'title' => __( 'Meet Our Team', 'gsteam' ),
			'total_mem' => 3,
			'group_mem' => ''
		];

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		extract( wp_parse_args( $instance, $this->get_default_instance() ) );

        $gsteam_taxonomy = 'gs_team_group';
		$gsteam_term =  get_terms($gsteam_taxonomy); 
		
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input 
				class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" 
				name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" 
				type="text" value="<?php if ( isset($title) ) echo esc_attr( $title ); ?>"
			/>
		</p>
		<p> 
			<label for="<?php echo esc_attr( $this->get_field_id('total_mem') );?>"><?php _e( 'Total Members to display', 'gsteam' ); ?> :</label>
			<input
				class="widefat" type="number" min="1"
				id="<?php echo esc_attr( $this->get_field_id('total_mem') );?>"
				name="<?php echo esc_attr( $this->get_field_name('total_mem') ); ?>"
				value="<?php if(isset($total_mem)) echo esc_attr($total_mem);?>"
			/>
		</p>
		<p> 
			<label for="<?php echo esc_attr( $this->get_field_id('group_mem') ); ?>"><?php echo _e('Team Group : ') ?></label>
			<select value="<?php echo esc_attr( $group_mem ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name('group_mem') ); ?>" id="<?php echo esc_attr( $this->get_field_id('group_mem') ); ?>">
				<option <?php echo ($group_mem == 'all') ? 'selected' : ''; ?> value=""><?php _e( 'All Groups', 'gsteam' ); ?></option>
				<?php foreach( $gsteam_term as $term) : ?>
					<option <?php echo ( $group_mem == $term->slug ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $term->slug ); ?>"><?php echo ucfirst($term->name); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<?php 
	}


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		extract( wp_parse_args( $instance, $this->get_default_instance() ) );

		echo wp_kses_post( $before_widget );
			if ( !empty($title) ) echo wp_kses_post( $before_title . $title . $after_title );
			echo do_shortcode( sprintf( '[gs_team_sidebar total_mem="%d" group_mem="%s"]', esc_attr($total_mem), esc_attr($group_mem) ) );
		echo wp_kses_post( $after_widget );

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = [];

		$instance['title'] 		= ( !empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
		$instance['total_mem'] 	= ( !empty($new_instance['total_mem']) ) ? strip_tags($new_instance['total_mem']) : $old_instance['total_mem'];
		$instance['group_mem'] 	= ( !empty($new_instance['group_mem']) ) ? strip_tags($new_instance['group_mem']) : '';

		return $instance;

	}

} // class Widgets

// register Widgets widget
function register_GSTEAM_widget() {
    register_widget( 'GSTEAM\Widgets' );
}
add_action( 'widgets_init', 'GSTEAM\register_GSTEAM_widget' );