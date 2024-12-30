<?php
namespace GSTEAM;
/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Dummy_Data' ) ) {

    final class Dummy_Data {

        public function __construct() {

            add_action( 'wp_ajax_gsteam_import_team_data', array($this, 'import_team_data') );

            add_action( 'wp_ajax_gsteam_remove_team_data', array($this, 'remove_team_data') );

            add_action( 'wp_ajax_gsteam_import_shortcode_data', array($this, 'import_shortcode_data') );

            add_action( 'wp_ajax_gsteam_remove_shortcode_data', array($this, 'remove_shortcode_data') );

            add_action( 'wp_ajax_gsteam_import_all_data', array($this, 'import_all_data') );

            add_action( 'wp_ajax_gsteam_remove_all_data', array($this, 'remove_all_data') );

            add_action( 'gs_after_shortcode_submenu', array($this, 'register_sub_menu') );

            add_action( 'admin_init', array($this, 'maybe_auto_import_all_data') );

            // Remove dummy indicator
            add_action( 'edit_post_gs_team', array($this, 'remove_dummy_indicator'), 10 );

            // Import Process
            add_action( 'gsteam_dummy_attachments_process_start', function() {

                // Force delete option if have any
                delete_option( 'gsteam_dummy_team_data_created' );

                // Force update the process
                set_transient( 'gsteam_dummy_team_data_creating', 1, 3 * MINUTE_IN_SECONDS );

            });
            
            add_action( 'gsteam_dummy_attachments_process_finished', function() {

                $this->create_dummy_terms();

            });
            
            add_action( 'gsteam_dummy_terms_process_finished', function() {

                $this->create_dummy_members();

            });
            
            add_action( 'gsteam_dummy_members_process_finished', function() {

                // clean the record that we have started a process
                delete_transient( 'gsteam_dummy_team_data_creating' );

                // Add a track so we never duplicate the process
                update_option( 'gsteam_dummy_team_data_created', 1 );

            });
            
            // Shortcodes
            add_action( 'gsteam_dummy_shortcodes_process_start', function() {

                // Force delete option if have any
                delete_option( 'gsteam_dummy_shortcode_data_created' );

                // Force update the process
                set_transient( 'gsteam_dummy_shortcode_data_creating', 1, 3 * MINUTE_IN_SECONDS );

            });

            add_action( 'gsteam_dummy_shortcodes_process_finished', function() {

                // clean the record that we have started a process
                delete_transient( 'gsteam_dummy_shortcode_data_creating' );

                // Add a track so we never duplicate the process
                update_option( 'gsteam_dummy_shortcode_data_created', 1 );

            });
            
        }

        public function register_sub_menu() {

            $builder = plugin()->builder;

            add_submenu_page(
                'edit.php?post_type=gs_team', 'Install Demo', 'Install Demo', 'publish_pages', 'gs-team-shortcode#/demo-data', array( $builder, 'view' )
            );

        }

        public function get_taxonomy_list() {
            $taxonomies = ['gs_team_group', 'gs_team_tag', 'gs_team_language', 'gs_team_location', 'gs_team_gender', 'gs_team_specialty'];
            return array_filter( $taxonomies, 'taxonomy_exists' );
        }

        public function remove_dummy_indicator( $post_id ) {

            if ( empty( get_post_meta( $post_id, 'gsteam-demo_data', true ) ) ) return;
            
            $taxonomies = $this->get_taxonomy_list();

            // Remove dummy indicator from texonomies
            $dummy_terms = wp_get_post_terms( $post_id, $taxonomies, [
                'fields' => 'ids',
                'meta_key' => 'gsteam-demo_data',
                'meta_value' => 1,
            ]);

            if ( !empty($dummy_terms) ) {
                foreach( $dummy_terms as $term_id ) {
                    delete_term_meta( $term_id, 'gsteam-demo_data', 1 );
                }
            }

            // Remove dummy indicator from attachments
            $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
            $thumbnail_flip_id = get_post_meta( $post_id, 'second_featured_img', true );
            if ( !empty($thumbnail_id) ) delete_post_meta( $thumbnail_id, 'gsteam-demo_data', 1 );
            if ( !empty($thumbnail_flip_id) ) delete_post_meta( $thumbnail_flip_id, 'gsteam-demo_data', 1 );
            delete_transient( 'gsteam_dummy_attachments' );
            
            // Remove dummy indicator from post
            delete_post_meta( $post_id, 'gsteam-demo_data', 1 );
            delete_transient( 'gsteam_dummy_members' );

        }

        public function maybe_auto_import_all_data() {

            if ( get_option('gs_team_autoimport_done') == true ) return;

            $team_members = get_posts([
                'numberposts' => -1,
                'post_type' => 'gs_team',
                'fields' => 'ids'
            ]);

            $shortcodes = plugin()->builder->fetch_shortcodes();

            if ( empty($team_members) && empty($shortcodes) ) {
                $this->_import_team_data( false );
                $this->_import_shortcode_data( false );
            }

            update_option( 'gs_team_autoimport_done', true );
        }

        public function import_all_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            $response = [
                'team' => $this->_import_team_data( false ),
                'shortcode' => $this->_import_shortcode_data( false )
            ];

            if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

            return $response;

        }

        public function remove_all_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            $response = [
                'team' => $this->_remove_team_data( false ),
                'shortcode' => $this->_remove_shortcode_data( false )
            ];

            if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

            return $response;

        }

        public function import_team_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            // Start importing
            $this->_import_team_data();

        }

        public function remove_team_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            // Remove team data
            $this->_remove_team_data();

        }

        public function import_shortcode_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            // Start importing
            $this->_import_shortcode_data();

        }

        public function remove_shortcode_data() {

            // Validate nonce && check permission
            if ( !check_admin_referer('_gsteam_admin_nonce_gs_') || !current_user_can('publish_pages') ) wp_send_json_error( __('Unauthorised Request', 'gsteam'), 401 );

            // Remove team data
            $this->_remove_shortcode_data();

        }

        public function _import_team_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            // Data already imported
            if ( get_option('gsteam_dummy_team_data_created') !== false || get_transient('gsteam_dummy_team_data_creating') !== false ) {

                $message_202 = __( 'Dummy Team members already imported', 'gsteam' );

                if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
                
                return [
                    'status' => 202,
                    'message' => $message_202
                ];

            }
            
            // Importing demo data
            $this->create_dummy_attachments();

            $message = __( 'Dummy Team members imported', 'gsteam' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _remove_team_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            $this->delete_dummy_attachments();
            $this->delete_dummy_terms();
            $this->delete_dummy_members();

            delete_option( 'gsteam_dummy_team_data_created' );
            delete_transient( 'gsteam_dummy_team_data_creating' );

            $message = __( 'Dummy Team members deleted', 'gsteam' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _import_shortcode_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            // Data already imported
            if ( get_option('gsteam_dummy_shortcode_data_created') !== false || get_transient('gsteam_dummy_shortcode_data_creating') !== false ) {

                $message_202 = __( 'Dummy Shortcodes already imported', 'gsteam' );

                if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
                
                return [
                    'status' => 202,
                    'message' => $message_202
                ];

            }
            
            // Importing demo shortcodes
            $this->create_dummy_shortcodes();

            $message = __( 'Dummy Shortcodes imported', 'gsteam' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function _remove_shortcode_data( $is_ajax = null ) {

            if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

            $this->delete_dummy_shortcodes();

            delete_option( 'gsteam_dummy_shortcode_data_created' );
            delete_transient( 'gsteam_dummy_shortcode_data_creating' );

            $message = __( 'Dummy Shortcodes deleted', 'gsteam' );

            if ( $is_ajax ) wp_send_json_success( $message, 200 );

            return [
                'status' => 200,
                'message' => $message
            ];

        }

        public function get_taxonomy_ids_by_slugs( $taxonomy_group, $taxonomy_slugs = [] ) {

            $_terms = $this->get_dummy_terms();

            if ( empty($_terms) ) return [];
            
            $_terms = wp_filter_object_list( $_terms, [ 'taxonomy' => $taxonomy_group ] );
            $_terms = array_values( $_terms );      // reset the keys
            
            if ( empty($_terms) ) return [];
            
            $term_ids = [];
            
            foreach ( $taxonomy_slugs as $slug ) {
                $key = array_search( $slug, array_column($_terms, 'slug') );
                if ( $key !== false ) $term_ids[] = $_terms[$key]['term_id'];
            }

            return $term_ids;

        }

        public function get_attachment_id_by_filename( $filename ) {

            $attachments = $this->get_dummy_attachments();
            
            if ( empty($attachments) ) return '';
            
            $attachments = wp_filter_object_list( $attachments, [ 'post_name' => $filename ] );
            if ( empty($attachments) ) return '';
            
            $attachments = array_values( $attachments );
            
            return $attachments[0]->ID;

        }

        public function get_tax_inputs( $tax_inputs = [] ) {

            if ( empty($tax_inputs) ) return $tax_inputs;

            $_tax_inputs = [];

            foreach( $tax_inputs as $taxonomy => $tax_params ) {
                if ( taxonomy_exists( $taxonomy ) ) $_tax_inputs[$taxonomy] = $this->get_taxonomy_ids_by_slugs( $taxonomy, $tax_params );
            }

            return $_tax_inputs;
        }

        public function get_meta_inputs( $meta_inputs = [] ) {

            $meta_inputs['_thumbnail_id'] = $this->get_attachment_id_by_filename( $meta_inputs['_thumbnail_id'] );
            $meta_inputs['second_featured_img'] = $this->get_attachment_id_by_filename( $meta_inputs['second_featured_img'] );

            return $meta_inputs;

        }

        // Members
        public function create_dummy_members() {

            do_action( 'gsteam_dummy_members_process_start' );

            $post_status = 'publish';
            $post_type = 'gs_team';

            $members = [];

            $members[] = array(
                'post_title'    => "Morgan Freman",
                'post_content'  => "Experienced and innovative Web Developer with a passion for creating elegant and functional web solutions. Proficient in frontend and backend technologies, I excel at translating complex design and functionality requirements into clean, efficient, and user-friendly websites.\r\n\r\nWith a strong foundation in coding and problem-solving, I am dedicated to delivering high-quality projects on time and within scope.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-10 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_team_group" => ['apps-development', 'graphic-design'],
                    "gs_team_tag" => ['agency', 'freelancer'],
                    "gs_team_language" => ['english', 'german'],
                    "gs_team_location" => ['london', 'paris', 'sweden'],
                    "gs_team_gender" => ['male'],
                    "gs_team_specialty" => ['graphic-design', 'marketing-ninja']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gsteam-member-1',
                    '_gs_des' => "Web Developer",
                    '_gs_com' => "Herzog PLC",
                    '_gs_land' => '406-324-6585',
                    '_gs_cell' => '619-770-9056',
                    '_gs_email' => "morganfreman@herzogplc.com",
                    '_gs_address' => "406 Goyette Inlet Apt. 008 Kochmouth",
                    '_gs_ribon' => 'Rising Star',
                    'second_featured_img' => 'gsteam-member-flip-1',
                    'gs_social' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/WilliamMDean'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/WilliamMDean'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/WilliamMDean'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/WilliamMDean'],
                    ],
                    'gs_skill' => [
                        ['skill' => 'Communication', 'percent' => 100],
                        ['skill' => 'Growth Process', 'percent' => 90],
                        ['skill' => 'Analysis', 'percent' => 95],
                    ],
                ])
            );

            $members[] = array(
                'post_title'    => "Samuel Oliver",
                'post_content'  => "Dedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.\r\n\r\nDedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-11 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_team_group" => ['content-creation', 'marketing'],
                    "gs_team_tag" => ['fashion-design', 'interior-design'],
                    "gs_team_language" => ['french', 'german'],
                    "gs_team_location" => ['london', 'new-zealand', 'usa'],
                    "gs_team_gender" => ['male'],
                    "gs_team_specialty" => ['graphic-design']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gsteam-member-2',
                    '_gs_des' => "Corona Specialist",
                    '_gs_com' => "Herzog PLC",
                    '_gs_land' => '301-346-3447',
                    '_gs_cell' => '719-382-2900',
                    '_gs_email' => "samueljackson@herzogplc.com",
                    '_gs_address' => "99050 Meggie Harbor New Dawson",
                    '_gs_ribon' => 'Top Talent',
                    'second_featured_img' => 'gsteam-member-flip-2',
                    'gs_social' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/MichaelDDehaven'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/MichaelDDehaven'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/MichaelDDehaven'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/MichaelDDehaven'],
                    ],
                    'gs_skill' => [
                        ['skill' => 'Graphic Design', 'percent' => 95],
                        ['skill' => 'UI/UX Design', 'percent' => 100],
                        ['skill' => 'Design Tools', 'percent' => 95],
                    ],
                ])
            );

            $members[] = array(
                'post_title'    => "Orlando Bloom",
                'post_content'  => "Dedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.\r\n\r\nDedicated and knowledgeable Corona Specialist with a proven background in effectively managing and mitigating challenges posed by the COVID-19 pandemic.\r\n\r\nLeveraging a multidisciplinary skill set, I am adept at developing and implementing comprehensive strategies for disease prevention, public health education, crisis management, and community outreach.\r\n\r\nBy staying informed about the latest developments, guidelines, and best practices, I am committed to fostering safer environments and contributing to the well-being of individuals and communities.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-12 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_team_group" => ['apps-development', 'content-creation'],
                    "gs_team_tag" => ['creative', 'programmer'],
                    "gs_team_language" => ['english', 'german'],
                    "gs_team_location" => ['australia', 'london'],
                    "gs_team_gender" => ['male'],
                    "gs_team_specialty" => ['graphic-design', 'marketing-ninja']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gsteam-member-3',
                    '_gs_des' => "Corona Specialist",
                    '_gs_com' => "Stroman",
                    '_gs_land' => '949-250-0110',
                    '_gs_cell' => '646-281-3348',
                    '_gs_email' => "orlandobloom@stromaninc.com",
                    '_gs_address' => "8500 Lorem Street, Chicago",
                    '_gs_ribon' => 'Best Employee',
                    'second_featured_img' => 'gsteam-member-flip-3',
                    'gs_social' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/HermanEWillis'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/HermanEWillis'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/HermanEWillis'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/HermanEWillis'],
                    ],
                    'gs_skill' => [
                        ['skill' => 'Empathy', 'percent' => 100],
                        ['skill' => 'Social Skills', 'percent' => 80],
                        ['skill' => 'Active Listening', 'percent' => 85],
                    ]
                ])
            );

            $members[] = array(
                'post_title'    => "Juri Sepp",
                'post_content'  => "Creative and detail-oriented UI\/UX Designer with a passion for crafting exceptional user experiences. Proficient in translating user needs into visually appealing and intuitive designs. Strong collaborator who thrives in interdisciplinary teams to deliver innovative digital solutions that combine aesthetics with functionality.\r\n\r\nPassionate UI Designer with a keen eye for detail and a drive to create exceptional digital experiences. Leveraging a strong foundation in design principles and an understanding of user behavior, I specialize in crafting interfaces that seamlessly blend aesthetics with usability.\r\n\r\nBy collaborating closely with cross-functional teams, I consistently deliver innovative solutions that captivate users and elevate brands. With a dedication to staying at the forefront of design trends and technologies, I am committed to pushing the boundaries of visual and interactive design to create meaningful connections between users and products.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-13 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_team_group" => ['apps-development', 'content-creation'],
                    "gs_team_tag" => ['content-creation', 'iso-developer'],
                    "gs_team_language" => ['german', 'spanish'],
                    "gs_team_location" => ['australia', 'paris', 'rome'],
                    "gs_team_gender" => ['male'],
                    "gs_team_specialty" => ['4g-expert', 'health-and-aging']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gsteam-member-4',
                    '_gs_des' => "UI\/UX Designer",
                    '_gs_com' => "Modera",
                    '_gs_land' => '785-416-8903',
                    '_gs_cell' => '212-694-2286',
                    '_gs_email' => "cameronguy@modera.com",
                    '_gs_address' => "2589 Cheshire Road, Stamford, CT 06901",
                    '_gs_ribon' => 'Top Talent',
                    'second_featured_img' => 'gsteam-member-flip-4',
                    'gs_social' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/JosephPBarren'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/JosephPBarren'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/JosephPBarren'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/JosephPBarren'],
                    ],
                    'gs_skill' => [
                        ['skill' => 'FrontEnd Development', 'percent' => 100],
                        ['skill' => 'BackEnd Development', 'percent' => 95],
                        ['skill' => 'Server Management', 'percent' => 90],
                    ],
                ])
            );

            $members[] = array(
                'post_title'    => "Richard Gere",
                'post_content'  => "Results-driven SEO Manager with a proven track record of developing and executing successful search engine optimization strategies.\r\n\r\nAdept at analyzing data, identifying trends, and implementing actionable insights to improve organic search rankings and drive targeted traffic. Strong leadership skills and a passion for staying updated with industry trends, algorithms, and best practices.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-14 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_team_group" => ['web-development', 'graphic-design'],
                    "gs_team_tag" => ['back-end', 'full-stack'],
                    "gs_team_language" => ['french', 'spanish'],
                    "gs_team_location" => ['paris', 'rome'],
                    "gs_team_gender" => ['male'],
                    "gs_team_specialty" => ['4g-expert', 'graphic-design', 'transmission']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gsteam-member-5',
                    '_gs_des' => "SEO Manager",
                    '_gs_com' => "",
                    '_gs_land' => '952-855-3834',
                    '_gs_cell' => '865-635-1895',
                    '_gs_email' => "richardgere@oracleorg.com",
                    '_gs_address' => "4970 University Drive, Chicago, IL 60606",
                    '_gs_ribon' => 'Rising Star',
                    'second_featured_img' => 'gsteam-member-flip-5',
                    'gs_social' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/SidneyMBuckley'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/SidneyMBuckley'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/SidneyMBuckley'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/SidneyMBuckley'],
                    ],
                    'gs_skill' => [
                        ['skill' => 'Product Design', 'percent' => 95],
                        ['skill' => 'Competitor Analysis', 'percent' => 100],
                        ['skill' => 'Product Interaction', 'percent' => 95],
                    ],
                ])
            );

            $members[] = array(
                'post_title'    => "Hugh Jakman",
                'post_content'  => "Experienced and innovative Web Developer with a passion for creating elegant and functional web solutions. Proficient in frontend and backend technologies, I excel at translating complex design and functionality requirements into clean, efficient, and user-friendly websites.\r\n\r\nWith a strong foundation in coding and problem-solving, I am dedicated to delivering high-quality projects on time and within scope.",
                'post_status'   => $post_status,
                'post_type' => $post_type,
                'post_date' => '2020-08-15 07:01:44',
                'tax_input' => $this->get_tax_inputs([
                    "gs_team_group" => ['apps-development', 'marketing'],
                    "gs_team_tag" => ['graphic-design', 'ui-ux'],
                    "gs_team_language" => ['english', 'german'],
                    "gs_team_location" => ['germany', 'london', 'new-zealand'],
                    "gs_team_gender" => ['male'],
                    "gs_team_specialty" => ['executive-recruiter', 'networking', 'transmission', 'web-development']
                ]),
                'meta_input' => $this->get_meta_inputs([
                    '_thumbnail_id' => 'gsteam-member-6',
                    '_gs_des' => "Web Developer",
                    '_gs_com' => "Sanira Inc",
                    '_gs_land' => '419-255-5857',
                    '_gs_cell' => '507-513-6174',
                    '_gs_email' => "hughjakman@sanirainc.com",
                    '_gs_address' => "1158 Hartland Avenue, Fond Du Lac, WI 54935",
                    '_gs_ribon' => 'Top Rated',
                    'second_featured_img' => 'gsteam-member-flip-6',
                    'gs_social' => [
                        ['icon' => 'fab fa-x-twitter', 'link' => 'https://twitter.com/DanteKHicks'],
                        ['icon' => 'fab fa-google-plus-g', 'link' => 'https://google.com/DanteKHicks'],
                        ['icon' => 'fab fa-facebook-f', 'link' => 'https://facebook.com/DanteKHicks'],
                        ['icon' => 'fab fa-linkedin-in', 'link' => 'https://linkedin.com/DanteKHicks'],
                    ],
                    'gs_skill' => [
                        ['skill' => 'Cartoon Design', 'percent' => 85],
                        ['skill' => 'Product Mockup', 'percent' => 100],
                        ['skill' => 'Graphic Elements', 'percent' => 95],
                    ],
                ])
            );

            foreach ( $members as $member ) {
                // Insert the post into the database
                $post_id = wp_insert_post( $member );
                // Add meta value for demo
                if ( $post_id ) add_post_meta( $post_id, 'gsteam-demo_data', 1 );
            }

            do_action( 'gsteam_dummy_members_process_finished' );

        }

        public function delete_dummy_members() {
            
            $members = $this->get_dummy_members();

            if ( empty($members) ) return;

            foreach ($members as $member) {
                wp_delete_post( $member->ID, true );
            }

            delete_transient( 'gsteam_dummy_members' );

        }

        public function get_dummy_members() {

            $members = get_transient( 'gsteam_dummy_members' );

            if ( false !== $members ) return $members;

            $members = get_posts( array(
                'numberposts' => -1,
                'post_type'   => 'gs_team',
                'meta_key' => 'gsteam-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($members) || empty($members) ) {
                delete_transient( 'gsteam_dummy_members' );
                return [];
            }
            
            set_transient( 'gsteam_dummy_members', $members, 3 * MINUTE_IN_SECONDS );

            return $members;

        }

        public function http_request_args( $args ) {
            
            $args['sslverify'] = false;

            return $args;

        }

        // Attachments
        public function create_dummy_attachments() {

            do_action( 'gsteam_dummy_attachments_process_start' );

            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            $attachment_files = [
                'gsteam-member-1.jpg',
                'gsteam-member-2.jpg',
                'gsteam-member-3.jpg',
                'gsteam-member-4.jpg',
                'gsteam-member-5.jpg',
                'gsteam-member-6.jpg',
                'gsteam-member-flip-1.jpg',
                'gsteam-member-flip-2.jpg',
                'gsteam-member-flip-3.jpg',
                'gsteam-member-flip-4.jpg',
                'gsteam-member-flip-5.jpg',
                'gsteam-member-flip-6.jpg'
            ];

            add_filter( 'http_request_args', [ $this, 'http_request_args' ] );

            wp_raise_memory_limit( 'image' );

            foreach ( $attachment_files as $file ) {

                $file = GSTEAM_PLUGIN_URI . '/assets/img/dummy-data/' . $file;

                $filename = basename($file);

                $get = wp_remote_get( $file );
                $type = wp_remote_retrieve_header( $get, 'content-type' );
                $mirror = wp_upload_bits( $filename, null, wp_remote_retrieve_body( $get ) );
                
                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $mirror['url'],
                    'post_mime_type' => $type,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                
                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
                
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                add_post_meta( $attach_id, 'gsteam-demo_data', 1 );

            }

            remove_filter( 'http_request_args', [ $this, 'http_request_args' ] );

            do_action( 'gsteam_dummy_attachments_process_finished' );

        }

        public function delete_dummy_attachments() {
            
            $attachments = $this->get_dummy_attachments();

            if ( empty($attachments) ) return;

            foreach ($attachments as $attachment) {
                wp_delete_attachment( $attachment->ID, true );
            }

            delete_transient( 'gsteam_dummy_attachments' );

        }

        public function get_dummy_attachments() {

            $attachments = get_transient( 'gsteam_dummy_attachments' );

            if ( false !== $attachments ) return $attachments;

            $attachments = get_posts( array(
                'numberposts' => -1,
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'meta_key' => 'gsteam-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($attachments) || empty($attachments) ) {
                delete_transient( 'gsteam_dummy_attachments' );
                return [];
            }
            
            set_transient( 'gsteam_dummy_attachments', $attachments, 3 * MINUTE_IN_SECONDS );

            return $attachments;
        }
        
        // Terms
        public function create_dummy_terms() {

            do_action( 'gsteam_dummy_terms_process_start' );
            
            $terms = [
                [
                    "name" => "Marketing",
                    "slug" => "marketing",
                    "group" => "gs_team_group",
                ],
                [
                    "name" => "Australia",
                    "slug" => "australia",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Marketing Ninja",
                    "slug" => "marketing-ninja",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "Graphic Design",
                    "slug" => "graphic-design",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Agency",
                    "slug" => "agency",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "English",
                    "slug" => "english",
                    "group" => "gs_team_language",
                ],
                [
                    "name" => "Germany",
                    "slug" => "germany",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Graphic Design",
                    "slug" => "graphic-design",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "Interior Design",
                    "slug" => "interior-design",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Male",
                    "slug" => "male",
                    "group" => "gs_team_gender",
                ],
                [
                    "name" => "German",
                    "slug" => "german",
                    "group" => "gs_team_language",
                ],
                [
                    "name" => "Paris",
                    "slug" => "paris",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Web Development",
                    "slug" => "web-development",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "Fashion Design",
                    "slug" => "fashion-design",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Female",
                    "slug" => "female",
                    "group" => "gs_team_gender",
                ],
                [
                    "name" => "Spanish",
                    "slug" => "spanish",
                    "group" => "gs_team_language",
                ],
                [
                    "name" => "Rome",
                    "slug" => "rome",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Networking",
                    "slug" => "networking",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "Content Creation",
                    "slug" => "content-creation",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Web Development",
                    "slug" => "web-development",
                    "group" => "gs_team_group",
                ],
                [
                    "name" => "French",
                    "slug" => "french",
                    "group" => "gs_team_language",
                ],
                [
                    "name" => "London",
                    "slug" => "london",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Transmission",
                    "slug" => "transmission",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "ISO Developer",
                    "slug" => "iso-developer",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Apps Development",
                    "slug" => "apps-development",
                    "group" => "gs_team_group",
                ],
                [
                    "name" => "New Zealand",
                    "slug" => "new-zealand",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "4G Expert",
                    "slug" => "4g-expert",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "Back End",
                    "slug" => "back-end",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Programmer",
                    "slug" => "programmer",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Graphic Design",
                    "slug" => "graphic-design",
                    "group" => "gs_team_group",
                ],
                [
                    "name" => "Sweden",
                    "slug" => "sweden",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Executive Recruiter",
                    "slug" => "executive-recruiter",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "Full Stack",
                    "slug" => "full-stack",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Creative",
                    "slug" => "creative",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Content Creation",
                    "slug" => "content-creation",
                    "group" => "gs_team_group",
                ],
                [
                    "name" => "USA",
                    "slug" => "usa",
                    "group" => "gs_team_location",
                ],
                [
                    "name" => "Health and Aging",
                    "slug" => "health-and-aging",
                    "group" => "gs_team_specialty",
                ],
                [
                    "name" => "UI/UX",
                    "slug" => "ui-ux",
                    "group" => "gs_team_tag",
                ],
                [
                    "name" => "Freelancer",
                    "slug" => "freelancer",
                    "group" => "gs_team_tag",
                ]
            ];

            foreach( $terms as $term ) {

                $response = wp_insert_term( $term['name'], $term['group'], array('slug' => $term['slug']) );
    
                if ( ! is_wp_error($response) ) {
                    add_term_meta( $response['term_id'], 'gsteam-demo_data', 1 );
                }

            }

            do_action( 'gsteam_dummy_terms_process_finished' );

        }
        
        public function delete_dummy_terms() {
            
            $terms = $this->get_dummy_terms();

            if ( empty($terms) ) return;
    
            foreach ( $terms as $term ) {
                wp_delete_term( $term['term_id'], $term['taxonomy'] );
            }

        }

        public function get_dummy_terms() {

            $taxonomies = $this->get_taxonomy_list();

            $terms = get_terms( array(
                'taxonomy' => $taxonomies,
                'hide_empty' => false,
                'meta_key' => 'gsteam-demo_data',
                'meta_value' => 1,
            ));
            
            if ( is_wp_error($terms) || empty($terms) ) return [];

            return json_decode( json_encode( $terms ), true ); // Object to Array

        }

        // Shortcode
        public function create_dummy_shortcodes() {

            do_action( 'gsteam_dummy_shortcodes_process_start' );

            plugin()->builder->create_dummy_shortcodes();

            do_action( 'gsteam_dummy_shortcodes_process_finished' );

        }

        public function delete_dummy_shortcodes() {
            
            plugin()->builder->delete_dummy_shortcodes();

        }

    }

}

