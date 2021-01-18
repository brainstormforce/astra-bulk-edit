<?php
/**
 * Plugin Name: Astra Bulk Edit
 * Plugin URI:  http://www.wpastra.com/pro/
 * Description: Easier way to edit Astra meta options in bulk.
 * Version: 1.2.6
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Domain Path: /languages
 * Text Domain: astra-bulk-edit
 *
 * @package Astra Bulk Edit
 */

if ( 'astra' !== get_template() ) {
	return;
}

/**
 * Set constants.
 */
define( 'ASTRA_BLK_VER', '1.2.6' );
define( 'ASTRA_BLK_FILE', __FILE__ );
define( 'ASTRA_BLK_BASE', plugin_basename( ASTRA_BLK_FILE ) );
define( 'ASTRA_BLK_DIR', plugin_dir_path( ASTRA_BLK_FILE ) );
define( 'ASTRA_BLK_URI', plugins_url( '/', ASTRA_BLK_FILE ) );

if ( is_admin() ) {
	require_once ASTRA_BLK_DIR . 'classes/class-astra-blk-meta-boxes-bulk-edit.php';
}
