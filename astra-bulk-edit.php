<?php
/**
 * Plugin Name: Astra Bulk Edit
 * Plugin URI: http://www.wpastra.com/pro/
 * Description: Easier way to edit Astra meta options in bulk.
 * Version: 1.1.0
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Domain Path: /languages
 * Text Domain: astra-bulk-edit
 * Version:         1.0.0
 *
 * @package         Astra_Bulk_Edit
 */

/**
 * Set constants.
 */
define( 'ASTRA_BLK_VER',  '1.1.0' );
define( 'ASTRA_BLK_FILE', __FILE__ );
define( 'ASTRA_BLK_BASE', plugin_basename( ASTRA_BLK_FILE ) );
define( 'ASTRA_BLK_DIR',  plugin_dir_path( ASTRA_BLK_FILE ) );
define( 'ASTRA_BLK_URI',  plugins_url( '/', ASTRA_BLK_FILE ) );

require_once ASTRA_BLK_DIR . 'classes/class-astra-blk-meta-boxes-bulk-edit.php';