<?php

namespace GSTEAM;

// if direct access than exit the file.
defined('ABSPATH') || exit;

$classmaps = [

	'Plugin'             => 'includes/plugin.php',
	'Column' 		     => 'includes/column.php',
	'Cpt'       	     => 'includes/cpt.php',
	'Hooks'              => 'includes/hooks.php',
	'Meta_Fields'        => 'includes/meta-fields.php',
	'Template_Loader'    => 'includes/template-loader.php',
	'Scripts'            => 'includes/scripts.php',
	'Widgets'            => 'includes/widgets.php',
	'Sortable'           => 'includes/sortable.php',
	'Bulk_Importer'      => 'includes/bulk-importer/bulk-importer.php',
	'Dummy_Data'         => 'includes/demo-data/dummy-data.php',
	'Builder'            => 'includes/shortcode-builder/builder.php',
	'Integrations'       => 'includes/integrations/integrations.php',
	'Shortcode'          => 'includes/shortcode.php',
	'Import_Export' 	 => 'includes/import-export.php'
];

return $classmaps;
