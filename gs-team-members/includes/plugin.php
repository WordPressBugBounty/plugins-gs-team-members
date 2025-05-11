<?php

namespace GSTEAM;

defined('ABSPATH') || exit;

class Plugin {

    public static $instance = null;

    public $cpt;
    public $shortcode;
    public $template_loader;
    public $scripts;
    public $hooks;
    public $sortable;
    public $builder;
    public $integrations;

    public static function get_instance() {
        
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initiate Autoloader for Class Load
     *
     * @since 1.0.0
     */
    public function __construct() {

        $this->cpt               = new Cpt();
        $this->shortcode         = new Shortcode();
        $this->template_loader   = new Template_Loader();
        $this->scripts           = new Scripts();
        $this->hooks             = new Hooks();
        $this->sortable          = new Sortable();
        $this->builder           = new Builder();
        $this->integrations      = new Integrations();

        new Bulk_Importer();
        new Column();
        new Meta_Fields();
        new Dummy_Data();
        new Import_Export();

        if (gtm_fs()->is_paying_or_trial()) {
            include_once GSTEAM_PLUGIN_DIR . 'includes/widgets.php';
        }

        require_once GSTEAM_PLUGIN_DIR . 'includes/asset-generator/gs-load-asset-generator.php';

        // Load Free/Pro Plugins List
        require_once GSTEAM_PLUGIN_DIR . 'includes/gs-common-pages/gs-team-common-pages.php';
    }

}

function plugin() {
    return Plugin::get_instance();
}
plugin();
