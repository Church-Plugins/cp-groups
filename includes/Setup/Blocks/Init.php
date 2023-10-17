<?php
/**
 * Initializes Gutenberg blocks
 *
 * @package CP_Groups
 */

namespace CP_Groups\Setup\Blocks;

use CP_Groups\Admin\Settings;

/**
 * Setup plugin initialization for CPTs
 */
class Init {

	/**
	 * Class instance
	 *
	 * @var Init
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Init
	 *
	 * @return Init
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Init ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 * Run includes and actions on instantiation
	 */
	protected function __construct() {
		$this->includes();
		$this->actions();
	}

	/**
	 * Plugin init includes
	 *
	 * @return void
	 */
	protected function includes() {
		new Query();
		new GroupTitle();
		new GroupTemplate();
		new GroupFeaturedImage();
		new GroupExcerpt();
		new GroupTags();
		new GroupLocation();
		new GroupTimeDesc();
		new GroupContent();
		new GroupBadge();
	}

	/**
	 * Plugin init actions
	 *
	 * @return void
	 */
	protected function actions() {
		add_action( 'init', array( $this, 'register_block_patterns' ) );
		add_filter( 'default_post_metadata', array( $this, 'default_thumbnail' ), 10, 5 );
	}

	/**
	 * Set default thumbnail for groups
	 *
	 * @param mixed  $value     The value get_metadata() should return - a single metadata value.
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key.
	 * @param bool   $single    Whether to return only the first value of the specified $meta_key.
	 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user).
	 */
	public function default_thumbnail( $value, $object_id, $meta_key, $single, $meta_type ) {
		if ( '_thumbnail_id' === $meta_key && 'post' === $meta_type ) {
			$post_type = get_post_type( $object_id );
			if ( cp_groups()->setup->post_types->groups->post_type === $post_type ) {
				$image = Settings::get( 'default_thumbnail' );
				if ( $image ) {
					$image = attachment_url_to_postid( $image );
				}
				return $image ? $image : $value;
			}
		}
		return $value;
	}

	/**
	 * Register block patterns
	 */
	public function register_block_patterns() {
		$patterns_dir = CP_GROUPS_PLUGIN_DIR . 'block-patterns/';

		$files = glob( $patterns_dir . '*.php' );

		foreach ( $files as $file ) {
			register_block_pattern(
				'cp-groups/' . basename( $file, '.php' ),
				require $file
			);
		}
	}
}
