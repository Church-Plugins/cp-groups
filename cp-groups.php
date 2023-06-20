<?php
/**
 * Plugin Name: Church Plugins - Groups
 * Plugin URL: https://churchplugins.com
 * Description: Church Groups plugin for managing groups
 * Version: 1.0.1
 * Author: Church Plugins
 * Author URI: https://churchplugins.com
 * Text Domain: cp-groups
 * Domain Path: languages
 */

if( !defined( 'CP_GROUPS_PLUGIN_VERSION' ) ) {
	 define ( 'CP_GROUPS_PLUGIN_VERSION',
	 	'1.1.0'
	);
}

require_once( dirname( __FILE__ ) . "/includes/Constants.php" );

require_once( CP_GROUPS_PLUGIN_DIR . "includes/ChurchPlugins/init.php" );
require_once( CP_GROUPS_PLUGIN_DIR . 'vendor/autoload.php' );


use CP_Groups\Init as Init;

/**
 * @var CP_Groups\Init
 */
global $cp_groups;
$cp_groups = cp_groups();

/**
 * @return CP_Groups\Init
 */
function cp_groups() {
	return Init::get_instance();
}

/**
 * Load plugin text domain for translations.
 *
 * @return void
 */
function cp_groups_load_textdomain() {

	// Traditional WordPress plugin locale filter
	$get_locale = get_user_locale();

	/**
	 * Defines the plugin language locale used in RCP.
	 *
	 * @var string $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
	 *                  otherwise uses `get_locale()`.
	 */
	$locale        = apply_filters( 'plugin_locale',  $get_locale, 'cp-groups' );
	$mofile        = sprintf( '%1$s-%2$s.mo', 'cp-groups', $locale );

	// Setup paths to current locale file
	$mofile_global = WP_LANG_DIR . '/cp-groups/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/cp-groups folder
		load_textdomain( 'cp-groups', $mofile_global );
	}

}
add_action( 'init', 'cp_groups_load_textdomain' );
