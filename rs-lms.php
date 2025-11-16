<?php
/**
 * Plugin Name: RS LMS
 * Plugin URI: https://www.shanto.net/plugins/rs-lms
 * Description: RS LMS is a Learning Management System (LMS) plugin for WordPress that allows you to create and manage online courses, track student progress, and facilitate learning.
 * Version: 1.0.0
 * Author: Riadujjaman Shanto
 * Author URI: https://www.shanto.net
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * Text Domain: rs-lms
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


// Define plugin constants
define( 'RS_LMS_VERSION', '1.0.0' );
define( 'RS_LMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RS_LMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RS_LMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );


// Include required files
require_once RS_LMS_PLUGIN_DIR . 'includes/class-enqueue-assets.php';
require_once RS_LMS_PLUGIN_DIR . 'includes/class-cpt.php';
require_once RS_LMS_PLUGIN_DIR . 'includes/class-cmb2.php';
//require_once RS_LMS_PLUGIN_DIR . 'includes/carbon-fields.php';
require_once RS_LMS_PLUGIN_DIR . 'includes/class-rest-api.php';