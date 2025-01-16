<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

class Meta_Fields {

	public function __construct() {

		add_action('add_meta_boxes', [ $this, 'add_gs_team_metaboxes' ] );
		add_action('save_post', [ $this, 'save_gs_team_metadata' ] );
	}

	function add_gs_team_metaboxes() {
		add_meta_box('gsTeamSection', 'Member\'s Additioinal Info', [ $this, 'cmb_cb' ], 'gs_team', 'normal', 'high');
		add_meta_box('gsTeamSectionSocial', 'Member\'s Social Links', [ $this, 'cmb_social_cb' ], 'gs_team', 'normal', 'high');
		add_meta_box('gsTeamSectionSkill', 'Member\'s Skills', [ $this, 'cmb_skill_cb' ], 'gs_team', 'normal', 'high');
	}

	function gs_image_uploader_field($name, $value = '') {

		$image = ' button">Upload Image';
		$image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
		$display = 'none'; // display state ot the "Remove image" button
	
		if ($image_attributes = wp_get_attachment_image_src($value, $image_size)) {
	
			// $image_attributes[0] - image URL
			// $image_attributes[1] - image width
			// $image_attributes[2] - image height
	
			$image = '"><img src="' . esc_attr($image_attributes[0]) . '" />';
			$display = 'inline-block';
		}
	
		return '<div class="form-group"><label for="second_featured_img">Flip Image:</label><div class="gs-image-uploader-area"><a href="#" class="gs_upload_image_button' . $image . '</a><input type="hidden" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" /><a href="#" class="gs_remove_image_button" style="display:inline-block;display:' . esc_attr($display) . '">Remove image</a></div></div>';
	}

	
	function cmb_cb($post) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field('gs_team_nonce_name', 'gs_team_cmb_token');

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$gs_des         = get_post_meta($post->ID, '_gs_des', true);
		$gs_com         = get_post_meta($post->ID, '_gs_com', true);
		$gs_com_website = get_post_meta($post->ID, '_gs_com_website', true);
		$gs_land        = get_post_meta($post->ID, '_gs_land', true);
		$gs_cell        = get_post_meta($post->ID, '_gs_cell', true);
		$gs_email       = get_post_meta($post->ID, '_gs_email', true);
		$gs_cc       	= get_post_meta($post->ID, '_gs_cc', true);
		$gs_bcc       	= get_post_meta($post->ID, '_gs_bcc', true);
		$gs_address     = get_post_meta($post->ID, '_gs_address', true);
		$gs_ribon       = get_post_meta($post->ID, '_gs_ribon', true);
		$gs_zip_code    = get_post_meta($post->ID, '_gs_zip_code', true);
		$gs_vcard       = get_post_meta($post->ID, '_gs_vcard', true);
		$gs_custom_page = get_post_meta($post->ID, '_gs_custom_page', true);

		?>

		<div class="gs_team-metafields">

			<div style="height: 20px;"></div>

			<div class="form-group">
				<label for="gsDes"><?php _e('Designation', 'gsteam'); ?></label>
				<input type="text" id="gsDes" class="form-control" name="gs_des" value="<?php echo isset($gs_des) ? esc_attr($gs_des) : ''; ?>">
			</div>

			<div class="gs-team-pro-field">

				<div class="form-group">
					<label for="gsCom"><?php _e('Company', 'gsteam'); ?></label>
					<input type="text" id="gsCom" class="form-control" name="gs_com" value="<?php echo isset($gs_com) ? esc_attr($gs_com) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsComWebsite"><?php _e('Company Website', 'gsteam'); ?></label>
					<input type="text" id="gsComWebsite" class="form-control" name="gs_com_website" value="<?php echo isset($gs_com_website) ? esc_attr($gs_com_website) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsLand"><?php _e('Land Phone', 'gsteam'); ?></label>
					<input type="text" id="gsLand" class="form-control" name="gs_land" value="<?php echo isset($gs_land) ? esc_attr($gs_land) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsCell"><?php _e('Cell Phone', 'gsteam'); ?></label>
					<input type="text" id="gsCell" class="form-control" name="gs_cell" value="<?php echo isset($gs_cell) ? esc_attr($gs_cell) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsEm"><?php _e('Email', 'gsteam'); ?></label>
					<input type="text" id="gsEm" class="form-control" name="gs_email" value="<?php echo isset($gs_email) ? esc_attr($gs_email) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsEmCC"><?php _e('CC', 'gsteam'); ?></label>
					<input type="text" id="gsEmCC" class="form-control" name="gs_cc" placeholder="<?php esc_attr_e( 'Enter CC emails, separated by semicolon. ex: email1@gmail.com; email2@gmail.com' ); ?>" value="<?php echo isset($gs_cc) ? esc_attr($gs_cc) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsEmBCC"><?php _e('BCC', 'gsteam'); ?></label>
					<input type="text" id="gsEmBCC" class="form-control" name="gs_bcc" placeholder="<?php esc_attr_e( 'Enter BCC emails, separated by semicolon. ex: email1@gmail.com; email2@gmail.com' ); ?>" value="<?php echo isset($gs_bcc) ? esc_attr($gs_bcc) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsAdd"><?php _e('Address', 'gsteam'); ?></label>
					<input type="text" id="gsAdd" class="form-control" name="gs_address" value="<?php echo isset($gs_address) ? esc_attr($gs_address) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsribon"><?php _e('Ribbon', 'gsteam'); ?></label>
					<input type="text" id="gsribon" class="form-control" name="gs_ribon" value="<?php echo isset($gs_ribon) ? esc_attr($gs_ribon) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gs_zip_code"><?php _e('Zip Code', 'gsteam'); ?></label>
					<input type="text" id="gs_zip_code" class="form-control" name="gs_zip_code" value="<?php echo isset($gs_zip_code) ? esc_attr($gs_zip_code) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gsvcard"><?php _e('vCard', 'gsteam'); ?></label>
					<input type="url" id="gsvcard" class="form-control" name="gs_vcard" placeholder="<?php _e('Add any external or internal link', 'gsteam'); ?>" value="<?php echo isset($gs_vcard) ? esc_url($gs_vcard) : ''; ?>">
				</div>

				<div class="form-group">
					<label for="gs_custom_page"><?php _e('Custom Page Link', 'gsteam'); ?></label>
					<input type="url" id="gs_custom_page" class="form-control" name="gs_custom_page" placeholder="<?php _e('Add any external or internal link', 'gsteam'); ?>" value="<?php echo isset($gs_custom_page) ? esc_url($gs_custom_page) : ''; ?>">
				</div>

				<?php
				$meta_key = 'second_featured_img';
				echo $this->gs_image_uploader_field($meta_key, get_post_meta($post->ID, $meta_key, true));
				?>

			</div>

		</div>

	<?php
	}

	function cmb_social_cb($post) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field('gs_team_nonce_name', 'gs_team_cmb_token');

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$gs_social = get_post_meta( $post->ID, 'gs_social', true );
		
		$social_icons = require_once GSTEAM_PLUGIN_DIR . 'includes/fs-icons.php';

	?>

		<div class="gs_team-metafields">

			<div style="height: 20px;"></div>

			<div class="gs-team-social--section">

				<div class="member-details-section">

					<table id="repeatable-fieldset-two" width="100%" class="gstm-sorable-table">
						<thead>
							<tr>
								<td width="3%"></td>
								<td width="45%"><?php _e('Icon', 'gsteam'); ?></td>
								<td width="42%"><?php _e('Link', 'gsteam'); ?></td>
								<td width="10%"></td>
							</tr>
						</thead>
						<tbody>

							<?php if ($gs_social) : foreach ($gs_social as $field) : ?>

									<tr>
										<td><i class="fas fa-arrows-alt" aria-hidden="true"></i></td>
										<td>
											<?php select_builder('gstm-team-icon[]', $social_icons, $field['icon'], __('Select icon', 'gsteam'), 'widefat gstm-icon-select'); ?>
										</td>
										<td><input type="text" placeholder="<?php _e('ex: https://twitter.com/gsplugins', 'gsteam'); ?>" class="widefat" name="gstm-team-link[]" value="<?php if (isset($field['link'])) echo esc_attr($field['link']); ?>" /></td>
										<td><a class="button remove-row" href="#"><?php _e('Remove', 'gsteam'); ?></a></td>
									</tr>

								<?php endforeach;
							else : ?>

								<tr>
									<td><i class="fas fa-arrows-alt" aria-hidden="true"></i></td>
									<td>
										<?php select_builder('gstm-team-icon[]', $social_icons, '', __('Select icon', 'gsteam'), 'widefat gstm-icon-select'); ?>
									</td>
									<td><input type="text" placeholder="<?php _e('ex: https://twitter.com/gsplugins', 'gsteam'); ?>" class="widefat" name="gstm-team-link[]" value="" /></td>
									<td><a class="button remove-row" href="#"><?php _e('Remove', 'gsteam'); ?></a></td>
								</tr>

							<?php endif; ?>

							<tr class="empty-row screen-reader-text">
								<td><i class="fas fa-arrows-alt" aria-hidden="true"></i></td>
								<td>
									<?php select_builder('gstm-team-icon[]', $social_icons, '', __('Select icon', 'gsteam'), 'widefat'); ?>
								</td>
								<td><input type="text" placeholder="<?php _e('ex: https://twitter.com/gsplugins', 'gsteam'); ?>" class="widefat" name="gstm-team-link[]" value="" /></td>
								<td><a class="button remove-row" href="#"><?php _e('Remove', 'gsteam'); ?></a></td>
							</tr>

						</tbody>
					</table>

					<p><a class="button gstm-add-row" href="#" data-table="repeatable-fieldset-two"><?php _e('Add Row', 'gsteam'); ?></a></p>

				</div>

			</div>

		</div>

	<?php
	}


	function cmb_skill_cb($post) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field('gs_team_nonce_name', 'gs_team_cmb_token');

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$gs_skill = get_post_meta($post->ID, 'gs_skill', true);

	?>

		<div class="gs_team-metafields">

			<div style="height: 20px;"></div>

			<div class="gs-team-skills--section gs-team-pro-field">

				<div class="member-details-section">
					<table id="repeatable-fieldset-skill" width="100%" class="gstm-sorable-table">
						<thead>
							<tr>
								<td width="3%"></td>
								<td width="45%"><?php _e('Title', 'gsteam'); ?></td>
								<td width="42%"><?php _e('Percent', 'gsteam'); ?></td>
								<td width="10%"></td>
							</tr>
						</thead>
						<tbody>

							<?php if ($gs_skill) : foreach ($gs_skill as $field) : ?>

									<tr>
										<td><i class="fas fa-arrows-alt" aria-hidden="true"></i></td>
										<td>
											<input type="text" placeholder="html" class="widefat" name="gstm-skill-name[]" value="<?php if (isset($field['skill'])) echo esc_attr($field['skill']); ?>" />
										</td>
										<td><input type="text" placeholder="85" class="widefat" name="gstm-skill-percent[]" value="<?php if (isset($field['percent'])) echo esc_attr($field['percent']); ?>" /></td>
										<td><a class="button remove-row" href="#"><?php _e('Remove', 'gsteam'); ?></a></td>
									</tr>

								<?php endforeach;
							else : ?>

								<tr>
									<td><i class="fas fa-arrows-alt" aria-hidden="true"></i></td>
									<td>
										<input type="text" placeholder="html" class="widefat" name="gstm-skill-name[]" value="<?php if (isset($field['skill'])) echo esc_attr($field['skill']); ?>" />
									</td>
									<td><input type="text" placeholder="85" class="widefat" name="gstm-skill-percent[]" value="<?php if (isset($field['percent'])) echo esc_attr($field['percent']); ?>" /></td>
									<td><a class="button remove-row" href="#"><?php _e('Remove', 'gsteam'); ?></a></td>
								</tr>

							<?php endif; ?>

							<tr class="empty-skill screen-reader-text">
								<td><i class="fas fa-arrows-alt" aria-hidden="true"></i></td>
								<td>
									<input type="text" placeholder="<?php _e('ex: Wordpress', 'gsteam'); ?>" class="widefat" name="gstm-skill-name[]" value="<?php if (isset($field['link'])) echo esc_attr($field['link']); ?>" />
								</td>
								<td><input type="text" placeholder="<?php _e('ex: 90', 'gsteam'); ?>" class="widefat" name="gstm-skill-percent[]" value="" /></td>
								<td><a class="button remove-row" href="#"><?php _e('Remove', 'gsteam'); ?></a></td>
							</tr>

						</tbody>
					</table>

					<p><a class="button gstm-add-skill" href="#" data-table="repeatable-fieldset-skill"><?php _e('Add Row', 'gsteam'); ?></a></p>

				</div>

			</div>


		</div>

<?php
	}


	function save_gs_team_metadata($post_id) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if (!isset($_POST['gs_team_cmb_token'])) {
			return;
		}

		// Verify that the nonce is valid.
		if (!wp_verify_nonce($_POST['gs_team_cmb_token'], 'gs_team_nonce_name')) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check the user's permissions.
		if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {

			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		if (!empty($social_icons = $_POST['gstm-team-icon']) && !empty($social_links = $_POST['gstm-team-link'])) {

			$social_icons = array_map('sanitize_text_field', $social_icons);
			$social_links = array_map('sanitize_text_field', $social_links);

			$newdata = array_map(function ($icon, $link) {
				if (!empty($icon) && !empty($link)) return ['icon' => $icon, 'link' => $link];
			}, $social_icons, $social_links);

			$meta_key = 'gs_social';

			$newdata = array_values(array_filter($newdata));
			$olddata = get_post_meta($post_id, $meta_key, true);

			if (!empty($newdata) && $newdata != $olddata) {
				update_post_meta($post_id, $meta_key, $newdata);
			} elseif (empty($newdata) && $olddata) {
				delete_post_meta($post_id, $meta_key, $olddata);
			}
		}


		if (gtm_fs()->is_paying_or_trial()) {

			if (!empty($member_skill = $_POST['gstm-skill-name']) && !empty($members_percent = $_POST['gstm-skill-percent'])) {

				$member_skill = array_map('sanitize_text_field', $member_skill);
				$members_percent = array_map('absint', $members_percent);

				$newdata = array_map(function ($skill, $percent) {
					if (!empty($skill) && !empty($percent)) return ['skill' => $skill, 'percent' => $percent];
				}, $member_skill, $members_percent);

				$meta_key = 'gs_skill';

				$newdata = array_values(array_filter($newdata));
				$olddata = get_post_meta($post_id, $meta_key, true);

				if (!empty($newdata) && $newdata != $olddata) {
					update_post_meta($post_id, $meta_key, $newdata);
				} elseif (empty($newdata) && $olddata) {
					delete_post_meta($post_id, $meta_key, $olddata);
				}
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Make sure that it is set.
		if (!isset($_POST['gs_des'])) {
			return;
		}

		// Sanitize user input.
		$gs_des_data = sanitize_text_field($_POST['gs_des']);
		update_post_meta($post_id, '_gs_des', $gs_des_data);

		if (gtm_fs()->is_paying_or_trial()) {

			update_post_meta($post_id, '_gs_com', sanitize_text_field($_POST['gs_com']));
			update_post_meta($post_id, '_gs_com_website', esc_url_raw($_POST['gs_com_website']));
			update_post_meta($post_id, '_gs_land', sanitize_text_field($_POST['gs_land']));
			update_post_meta($post_id, '_gs_cell', sanitize_text_field($_POST['gs_cell']));
			update_post_meta($post_id, '_gs_email', sanitize_text_field($_POST['gs_email']));
			update_post_meta($post_id, '_gs_cc', sanitize_text_field($_POST['gs_cc']));
			update_post_meta($post_id, '_gs_bcc', sanitize_text_field($_POST['gs_bcc']));
			update_post_meta($post_id, '_gs_address', sanitize_text_field($_POST['gs_address']));
			update_post_meta($post_id, '_gs_ribon', sanitize_text_field($_POST['gs_ribon']));
			update_post_meta($post_id, '_gs_zip_code', sanitize_text_field($_POST['gs_zip_code']));
			update_post_meta($post_id, '_gs_vcard', esc_url_raw($_POST['gs_vcard'], array('http', 'https', 'ftp', 'ftps')));
			update_post_meta($post_id, '_gs_custom_page', esc_url_raw($_POST['gs_custom_page'], array('http', 'https')));

			$meta_key = 'second_featured_img';
			update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$meta_key]));
		}
	}
}
