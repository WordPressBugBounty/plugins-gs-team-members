<?php
namespace GSTEAM;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/gs-asset-generator-base.php';
require_once __DIR__ . '/gs-team-asset-generator.php';

// Needed for pro compatibility
do_action( 'gs_team_assets_generator_loaded' );