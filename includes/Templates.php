<?php
/**
 * Templating functionality for Church Plugins groups
 */

namespace CP_Groups;

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


/**
 * Handle views and template files.
 */
class Templates extends \ChurchPlugins\Templates {


	/**
	 * Initialize the Template Yumminess!
	 */
	protected function __construct() {
		parent::__construct();

		add_action( 'cp_groups_after_archive', 'the_posts_pagination' );
	}

	public function pagination() {
		$this->get_template_part( 'parts/pagination' );
	}

	/**
	 * Build the link for the facet buttons
	 * 
	 * @param $slug
	 * @param $facet
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public static function get_facet_link( $slug, $facet ) {
		if ( is_singular( 'cp_group' ) ) {
			$uri = get_post_type_archive_link( 'cp_group' );
		} else {
			$uri = explode( '?', $_SERVER['REQUEST_URI'] )[0];
		}

		$uri = apply_filters( 'cp_groups_facet_link_uri', $uri, $slug, $facet );

		if ( empty( $uri ) ) {
			return '#';
		}

		$get = $_GET;

		if ( empty( $get ) ) {
			$get = [];
		}

		unset( $get['groups-paged'] );

		$get[ $facet ] = array( $slug );

		return esc_url( add_query_arg( $get, $uri ) ) . '#cp-group-filters';
	}



	/**
	 * Return the post types for this plugin
	 *
	 * @return mixed
	 * @since  1.0.11
	 *
	 * @author Tanner Moushey, 5/11/23
	 */
	public function get_post_types() {
		return cp_groups()->setup->post_types->get_post_types();
	}

	/**
	 * Return the taxonomies for this plugin
	 *
	 * @return mixed
	 * @since  1.0.11
	 *
	 * @author Tanner Moushey, 5/11/23
	 */
	public function get_taxonomies() {
		return cp_groups()->setup->taxonomies->get_taxonomies();
	}

	/**
	 * Return the plugin path for the current plugin
	 *
	 * @return mixed
	 * @since  1.5.0
	 *
	 */
	public function get_plugin_path() {
		return cp_groups()->get_plugin_path();
	}

	/**
	 * Get the slug / id for the current plugin
	 *
	 * @return mixed
	 * @since  1.0.11
	 *
	 * @author Tanner Moushey, 5/11/23
	 */
	public function get_plugin_id() {
		return cp_groups()->get_id();
	}
	
}
