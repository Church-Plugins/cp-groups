<?php
/**
 * Plugin constants
 */

/**
 * Setup/config constants
 */
if( !defined( 'CP_GROUPS_PLUGIN_FILE' ) ) {
	 define ( 'CP_GROUPS_PLUGIN_FILE',
	 	dirname( dirname( __FILE__ ) ) . "/cp-groups.php"
	);
}
if( !defined( 'CP_GROUPS_PLUGIN_DIR' ) ) {
	 define ( 'CP_GROUPS_PLUGIN_DIR',
	 	plugin_dir_path( CP_GROUPS_PLUGIN_FILE )
	);
}
if( !defined( 'CP_GROUPS_PLUGIN_URL' ) ) {
	 define ( 'CP_GROUPS_PLUGIN_URL',
	 	plugin_dir_url( CP_GROUPS_PLUGIN_FILE )
	);
}
if( !defined( 'CP_GROUPS_INCLUDES' ) ) {
	 define ( 'CP_GROUPS_INCLUDES',
	 	plugin_dir_path( dirname( __FILE__ ) ) . 'includes'
	);
}
if( !defined( 'CP_GROUPS_PREFIX' ) ) {
	define ( 'CP_GROUPS_PREFIX',
		'cpg'
   );
}
if( !defined( 'CP_GROUPS_TEXT_DOMAIN' ) ) {
	 define ( 'CP_GROUPS_TEXT_DOMAIN',
		'cp_groups'
   );
}
if( !defined( 'CP_GROUPS_DIST' ) ) {
	 define ( 'CP_GROUPS_DIST',
		CP_GROUPS_PLUGIN_URL . "/dist/"
   );
}

/**
 * Licensing constants
 */
if( !defined( 'CP_GROUPS_STORE_URL' ) ) {
	 define ( 'CP_GROUPS_STORE_URL',
	 	'https://churchplugins.com'
	);
}
if( !defined( 'CP_GROUPS_ITEM_NAME' ) ) {
	 define ( 'CP_GROUPS_ITEM_NAME',
	 	'Church Plugins - Groups'
	);
}

/**
 * App constants
 */
if( !defined( 'CP_GROUPS_APP_PATH' ) ) {
	 define ( 'CP_GROUPS_APP_PATH',
	 	plugin_dir_path( dirname( __FILE__ ) ) . 'app'
	);
}
if( !defined( 'CP_GROUPS_ASSET_MANIFEST' ) ) {
	 define ( 'CP_GROUPS_ASSET_MANIFEST',
	 	plugin_dir_path( dirname( __FILE__ ) ) . 'app/build/asset-manifest.json'
	);
}
