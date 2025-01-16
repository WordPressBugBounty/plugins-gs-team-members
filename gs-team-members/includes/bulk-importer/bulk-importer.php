<?php

namespace GSTEAM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Bulk_Importer' ) ) {

    final class Bulk_Importer {

        public function __construct() {

            if ( gtm_fs()->is_paying_or_trial() ) {
                add_action( 'wp_ajax_gsteam_process_csv_file', array($this, 'process_csv_file') );
                add_action( 'wp_ajax_gsteam_bulk_import', array($this, 'bulk_import') );
            }

            add_action( 'gs_after_shortcode_submenu', array($this, 'register_sub_menu') );

        }

        public function register_sub_menu() {

            $builder = plugin()->builder;

            add_submenu_page(
                'edit.php?post_type=gs_team', 'Bulk Import', 'Bulk Import', 'publish_pages', 'gs-team-shortcode#/bulk-import', array( $builder, 'view' )
            );

        }

        public function bulk_import() {

            check_ajax_referer( '_gsteam_admin_nonce_gs_' );

            $index = isset( $_REQUEST['index'] ) ? (int) $_REQUEST['index'] : null;
    
            if ( ! is_numeric($index) ) wp_send_json_error();

            $rows = get_transient( 'gs_team_bulk_import_rows' );

            $row = $this->map_row_data( $rows[$index] );

            if ( empty($row['name']) ) wp_send_json_error();

            $member = [
                'post_title'    => $row['name'],
                'post_content'  => empty($row['description']) ? '' : $row['description'],
                'post_status'   => 'publish',
                'post_type'     => 'gs_team',
                'tax_input'     => $this->get_row_tax_input( $row ),
                'meta_input'    => $this->get_row_meta_input( $row )
            ];

            $post_id = wp_insert_post( $member );

            if ( $post_id ) wp_send_json_success();

            wp_send_json_error();

        }

        public function get_row_tax_input( $row ) {

            $tax_input = [];

            if ( !empty($row['group']) && taxonomy_exists('gs_team_group') ) {
                $tax_input['gs_team_group'] = $this->get_row_term_ids( $row['group'], 'gs_team_group' );
            }

            if ( !empty($row['tag']) && taxonomy_exists('gs_team_tag') ) {
                $tax_input['gs_team_tag'] = $this->get_row_term_ids( $row['tag'], 'gs_team_tag' );
            }

            if ( !empty($row['languages']) && taxonomy_exists('gs_team_language') ) {
                $tax_input['gs_team_language'] = $this->get_row_term_ids( $row['languages'], 'gs_team_language' );
            }

            if ( !empty($row['location']) && taxonomy_exists('gs_team_location') ) {
                $tax_input['gs_team_location'] = $this->get_row_term_ids( $row['location'], 'gs_team_location' );
            }

            if ( !empty($row['gender']) && taxonomy_exists('gs_team_gender') ) {
                $tax_input['gs_team_gender'] = $this->get_row_term_ids( $row['gender'], 'gs_team_gender' );
            }

            if ( !empty($row['specialty']) && taxonomy_exists('gs_team_specialty') ) {
                $tax_input['gs_team_specialty'] = $this->get_row_term_ids( $row['specialty'], 'gs_team_specialty' );
            }

            if ( !empty($row['extra_one']) && taxonomy_exists('gs_team_extra_one') ) {
                $tax_input['gs_team_extra_one'] = $this->get_row_term_ids( $row['extra_one'], 'gs_team_extra_one' );
            }

            if ( !empty($row['extra_two']) && taxonomy_exists('gs_team_extra_two') ) {
                $tax_input['gs_team_extra_two'] = $this->get_row_term_ids( $row['extra_two'], 'gs_team_extra_two' );
            }

            if ( !empty($row['extra_three']) && taxonomy_exists('gs_team_extra_three') ) {
                $tax_input['gs_team_extra_three'] = $this->get_row_term_ids( $row['extra_three'], 'gs_team_extra_three' );
            }

            if ( !empty($row['extra_four']) && taxonomy_exists('gs_team_extra_four') ) {
                $tax_input['gs_team_extra_four'] = $this->get_row_term_ids( $row['extra_four'], 'gs_team_extra_four' );
            }

            if ( !empty($row['extra_five']) && taxonomy_exists('gs_team_extra_five') ) {
                $tax_input['gs_team_extra_five'] = $this->get_row_term_ids( $row['extra_five'], 'gs_team_extra_five' );
            }

            return $tax_input;
        }

        public function get_row_meta_input( $row ) {

            $meta_input = [];

            if ( !empty($row['image']) )            $meta_input['_thumbnail_id']        = (int) $row['image'];
            if ( !empty($row['flip_image']) )       $meta_input['second_featured_img']  = (int) $row['flip_image'];
            if ( !empty($row['designation']) )      $meta_input['_gs_des']              = sanitize_text_field( $row['designation'] );
            if ( !empty($row['company']) )          $meta_input['_gs_com']              = sanitize_text_field( $row['company'] );
            if ( !empty($row['company_website']) )  $meta_input['_gs_com_website']      = sanitize_url( $row['company_website'] );
            if ( !empty($row['land_phone']) )       $meta_input['_gs_land']             = sanitize_text_field( $row['land_phone'] );
            if ( !empty($row['cell_phone']) )       $meta_input['_gs_cell']             = sanitize_text_field( $row['cell_phone'] );
            if ( !empty($row['email']) )            $meta_input['_gs_email']            = sanitize_email( $row['email'] );
            if ( !empty($row['cc']) )               $meta_input['_gs_cc']               = sanitize_text_field( $row['cc'] );
            if ( !empty($row['bcc']) )              $meta_input['_gs_bcc']              = sanitize_text_field( $row['bcc'] );
            if ( !empty($row['address']) )          $meta_input['_gs_address']          = sanitize_text_field( $row['address'] );
            if ( !empty($row['ribbon']) )           $meta_input['_gs_ribon']            = sanitize_text_field( $row['ribbon'] );
            if ( !empty($row['zip_code']) )         $meta_input['_gs_zip_code']         = sanitize_text_field( $row['zip_code'] );
            if ( !empty($row['vcard']) )            $meta_input['_gs_vcard']            = esc_url_raw( $row['vcard'] );
            if ( !empty($row['custom_page_link']) ) $meta_input['_gs_custom_page']      = esc_url_raw( $row['custom_page_link'] );
            if ( !empty($row['socials']) )          $meta_input['gs_social']            = (array) $row['socials'];
            if ( !empty($row['skills']) )           $meta_input['gs_skill']             = (array) $row['skills'];

            return $meta_input;

        }

        public function get_row_term_ids( $terms, $taxonomy ) {

            $term_ids = [];

            foreach ( $terms as $term ) {

                $_term = get_term_by( 'name', $term, $taxonomy );

                if ( $_term ) {
                    $term_ids[] = $_term->term_id;
                } else {
                    $response = wp_insert_term( $term, $taxonomy );
                    if ( ! is_wp_error($response) ) {
                        $term_ids[] = $response['term_id'];
                    }
                }

            }

            return array_values( array_unique($term_ids) );

        }

        public function process_csv_file() {

            check_ajax_referer( '_gsteam_admin_nonce_gs_' );

            $rows = $this->get_rows_from_file();
            
            if ( is_wp_error($rows) ) wp_send_json_error( ['message' => $rows->get_error_message() ] );
            
            set_transient( 'gs_team_bulk_import_rows', $rows, DAY_IN_SECONDS );

            $allowed = array( 'name', 'designation', 'email', 'company', 'image' );

            $rows = array_map(function( $row ) use ($allowed) {
                $row = array_intersect_key( $row, array_flip($allowed) );
                if ( !empty($row['image']) ) {
                    $row['image'] = wp_get_attachment_url( $row['image'] );
                }
                return $row;
            }, $rows );

            wp_send_json_success( $rows );

        }

        public function parse_to_array( $array ) {

            return array_map( 'trim', explode( ',', str_replace(', ', ',', $array) ) );

        }

        public function parse_to_array_deep( $array, $columns ) {

            if ( empty($array) ) return [];

            $array = $this->parse_to_array( $array );

            return array_map( function( $data ) use ( $columns ) {
                $data = array_map( 'trim', explode( '|', $data ) );
                return array_combine( $columns, $data );
            }, $array );

        }

        public function clean($string) {
            $string = str_replace( [' ', '-'], '_', $string); // Replaces all spaces with hyphens.
            $string = strtolower( _wp_json_convert_string( trim( $string ) ) );
            return preg_replace('/[^A-Za-z0-9\_]/', '', $string); // Removes special chars.
        }

        public function get_rows_from_file() {

            $items = [];

            // File extension
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            // If file extension is 'csv'
            if ( !empty($_FILES['file']['name']) && $extension == 'csv' ) {

                // Open file in read mode
                $csv_file = fopen($_FILES['file']['tmp_name'], 'r');

                // Header Row
                $header_row = fgetcsv( $csv_file );

                $header_row = array_map( [ $this, 'clean' ], $header_row );

                if ( count($header_row) != 30 ) return new \WP_Error('file_not_valid', __('File is not valid', 'gsteam') );
        
                // Read file
                while ( ( $csv_data = fgetcsv($csv_file) ) !== false ) {

                    if ( count($csv_data) != 30 ) return new \WP_Error('file_not_valid', __('File is not valid', 'gsteam') );

                    $csv_data = array_map( function( $column ) {
                        return _wp_json_convert_string( trim($column) );
                    }, $csv_data );
        
                    $items[] = array_combine( $header_row, $csv_data );
        
                }

            }

            return $items;

        }

        public function map_row_data( $data ) {

            $data['languages']    = $this->parse_to_array( $data['languages'] );
            $data['location']     = $this->parse_to_array( $data['location'] );
            $data['gender']       = $this->parse_to_array( $data['gender'] );
            $data['specialty']    = $this->parse_to_array( $data['specialty'] );
            $data['group']        = $this->parse_to_array( $data['group'] );
            $data['tag']          = $this->parse_to_array( $data['tag'] );
            $data['extra_one']    = $this->parse_to_array( $data['extra_one'] );
            $data['extra_two']    = $this->parse_to_array( $data['extra_two'] );
            $data['extra_three']  = $this->parse_to_array( $data['extra_three'] );
            $data['extra_four']   = $this->parse_to_array( $data['extra_four'] );
            $data['extra_five']   = $this->parse_to_array( $data['extra_five'] );
            $data['socials']      = $this->parse_to_array_deep( $data['socials'], ['icon', 'link'] );
            $data['skills']       = $this->parse_to_array_deep( $data['skills'], ['skill', 'percent'] );

            return $data;

        }

    }

}