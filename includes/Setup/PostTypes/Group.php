<?php
namespace CP_Groups\Setup\PostTypes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use CP_Groups\Admin\Settings;
use ChurchPlugins\Helpers;
use ChurchPlugins\Setup\PostTypes\PostType;

/**
 * Setup for custom post type: Group
 *
 * @since 1.0
 */
class Group extends PostType {

	/**
	 * Importer for geolocation data
	 *
	 * @var \CP_Groups\Util\ImportGeolocation
	 */
	protected $geo_importer;

	/**
	 * Child class constructor. Punts to the parent.
	 *
	 * @author costmo
	 */
	protected function __construct() {
		$this->post_type = 'cp_group';

		$this->single_label = apply_filters( "cp_single_{$this->post_type}_label", Settings::get_groups( 'singular_label', 'Group' ) );
		$this->plural_label = apply_filters( "cp_plural_{$this->post_type}_label", Settings::get_groups( 'plural_label', 'Groups' ) );

		parent::__construct();

		// the model for this class is not compatible with CP core.
		$this->model = false;
		$this->geo_importer = new \CP_Groups\Util\ImportGeolocation();
	}

	/**
	 * Actions
	 */
	public function add_actions() {
		add_filter( 'enter_title_here', [ $this, 'add_title' ], 10, 2 );
		add_filter( 'cp_location_taxonomy_types', [ $this, 'location_tax' ] );
		add_action( 'pre_get_posts', [ $this, 'groups_query' ] );
		add_action( "cp_save_{$this->post_type}", [ $this, 'save_group' ] );
		add_action( "save_post_{$this->post_type}", [ $this, 'save_post' ] );
		add_action( 'updated_post_meta', [ $this, 'set_geolocation' ], 10, 4 );
		add_action( 'added_post_meta', [ $this, 'set_geolocation' ], 10, 4 );
		add_action( 'cmb2_save_field', [ $this, 'load_geolocations' ], 10, 4 );
		add_filter( 'query_vars', [ $this, 'add_coords_query_var' ] );

		parent::add_actions();
	}

	/**
	 * Customize WordPress query for groups
	 *
	 * @since  1.0.0
	 * @updated 1.0.2 | Updated query for attribute parameters
	 *
	 * @param \WP_Query $query The WordPress query.
	 *
	 * @author Tanner Moushey, 5/2/23
	 * @since 1.0.0
	 * @since 1.0.2 Updated query for attribute parameters
	 * @since 1.2.0 Added sorting by distance from a set of coordinates
	 */
	public function groups_query( $query ) {
		if ( $this->post_type !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		$coords = false;
		if ( $zipcode = $query->get( 'zipcode' ) ) {
			$api = new \CP_Groups\API\Mapbox( Settings::get_advanced( 'mapbox_api_key', '' ) );
			$coords = $api->geocode( $zipcode );
		} else if ( $query->get( 'coords' ) ) {
			$coords = array_map( 'floatval', explode( ',', $query->get( 'coords' ) ) );
		}

		if ( $coords ) {
			// if we have coords specified, sort by distance
			$group_ids = $this->sort_by_distance( $coords );
			$query->set( 'post__in', array_keys( $group_ids ) );
			$query->set( 'orderby', 'post__in' );
			$query->set( 'cp_groups_sorted_by_distance', $group_ids );
			add_filter( 'the_posts', [ $this, 'add_distance_to_groups' ], 10, 2 );
		} else {
			$query->set( 'orderby', 'post_title' );
			$query->set( 'order', 'ASC' );
		}

		$meta_query = $query->get( 'meta_query', [] );

		if ( Helpers::get_param( $_GET, 'child-friendly' ) ) {
			$meta_query[] = array(
				'key'   => 'kid_friendly',
				'value' => 'on',
			);
		}

		if ( Helpers::get_param( $_GET, 'accessible' ) ) {
			$meta_query[] = array(
				'key'   => 'handicap_accessible',
				'value' => 'on',
			);
		}

		if ( Helpers::get_param( $_GET, 'virtual' ) ) {
			$meta_query[] = array(
				'key'   => 'is_virtual',
				'value' => 'on',
			);
		}

		if ( $is_full_enabled = Settings::get_advanced( 'is_full_enabled', 'hide' ) ) {
			$is_full_param = Helpers::get_param( $_GET, 'is-full', false );
			$show_full = ( 'show' == $is_full_enabled );

			// if the is-full parameter is set, do the opposite of the default action.
			if ( $is_full_param ) {
				$show_full = ! $show_full;
			}

			if ( ! $show_full ) {
				$meta_query[] = array(
					'key'   => 'is_group_full',
					'value' => 0,
				);
			}
		}

		// if we have a custom meta mapping, add those to the query.
		$cp_connect_custom_meta = get_option( 'cp_group_custom_meta_mapping', array() );
		foreach ( $cp_connect_custom_meta as $meta_mapping ) {
			$meta_key   = $meta_mapping['slug'];
			$meta_value = Helpers::get_param( $_GET, $meta_key, false );

			if ( ! $meta_value ) {
				continue;
			}

			$meta_query[] = array(
				'key'   => $meta_key,
				'value' => $meta_value,
			);
		}

		$query->set( 'meta_query', $meta_query );

		$per_page = absint( Settings::get_advanced( 'groups_per_page', 40 ) );

		$query->set( 'posts_per_page', $per_page ? $per_page : 40 );
	}

	/**
	 * Add a query var for coordinates
	 *
	 * @param array $vars The query vars
	 * @return array
	 */
	public function add_coords_query_var( $vars ) {
		$vars[] = 'coords';
		$vars[] = 'zipcode';
		return $vars;
	}

	/**
	 * Add the distance to the groups
	 *
	 * @param array $posts The posts
	 * @param \WP_Query $query The query object
	 * @return array
	 */
	public function add_distance_to_groups( $posts, $query ) {
		if ( ! $distances = $query->get( 'cp_groups_sorted_by_distance' ) ) {
			return $posts;
		}
		foreach ( $posts as $post ) {
			$post->distance = $distances[ $post->ID ];
		}
		remove_filter( 'the_posts', [ $this, 'add_distance_to_groups' ], 10 );
		return $posts;
	}

	/**
	 * Update title placeholder in edit page
	 *
	 * @param $title
	 * @param $post
	 *
	 * @return string|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function add_title( $title, $post ) {
		if ( get_post_type( $post ) != $this->post_type ) {
			return $title;
		}

		return __( 'Group name', 'cp-groups' );
	}

	/**
	 * Add Groups to locations taxonomy if it exists
	 *
	 * @param $types
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function location_tax( $types ) {
		$types[] = $this->post_type;
		return $types;
	}

	/**
	 * Get the slug for this taxonomy
	 *
	 * @return false|mixed
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_slug() {
		if ( ! $type = get_post_type_object( $this->post_type ) ) {
			return false;
		}

		if ( isset( $type->rewrite['slug'] ) ) {
			return $type->rewrite['slug'];
		}

		return false;
	}

	/**
	 * Setup arguments for this CPT
	 *
	 * @return array
	 * @author costmo
	 */
	public function get_args() {
		$args               = parent::get_args();
		$args['menu_icon']  = apply_filters( "{$this->post_type}_icon", 'dashicons-groups' );
		$args['supports'][] = 'page-attributes';
		
		/**
		 * Disable the archive page for groups
		 *
		 * @param bool $is_archive_disabled Whether the archive page is disabled. Default is the setting from the admin.
		 * @since 1.1.0
		 */
		$is_archive_disabled = apply_filters( 'cp_groups_disable_archive', Settings::get_groups( 'disable_archive', false ) );

		if ( $is_archive_disabled ) {
			$args['has_archive'] = false;
		}

		return $args;
	}

	public function register_metaboxes() {
		$this->meta_details();
	}

	protected function meta_details() {
		$cmb = new_cmb2_box( [
			'id' => 'groups_meta',
			'title' => $this->single_label . ' ' . __( 'Details', 'cp-groups' ),
			'object_types' => [ $this->post_type ],
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true,
		] );

		$cmb->add_field( [
			'name' => 'Group Leaders',
			'desc' => 'Select the leaders for this group.',
			'id'   => 'leaders',
			'type' => 'cp_group_leader',
		] );

		// $cmb->add_field( [
		// 	'name' => __( 'Group Leader', 'cp-groups' ),
		// 	'desc' => __( 'The name of the group leader.', 'cp-groups' ),
		// 	'id'   => 'leader',
		// 	'type' => 'text',
		// ] );

		// $cmb->add_field( [
		// 	'name' => __( 'Group Leader Email', 'cp-groups' ),
		// 	'desc' => __( 'The email address of the group leader.', 'cp-groups' ),
		// 	'id'   => 'leader_email',
		// 	'type' => 'text',
		// ] );

		$cmb->add_field( [
			'name' => __( 'Group Email CC', 'cp-groups' ),
			'desc'         => __( 'Enter the email address(es) to CC whenever a contact form is submitted for this group. Comma separate multiple email addresses.', 'cp-groups' ),
			'id'   => 'cc',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Meeting Time Desc', 'cp-groups' ),
			'desc' => __( 'The Day / Time of the meeting. Ex "Thursdays at 6pm"', 'cp-groups' ),
			'id'   => 'time_desc',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Meeting Location', 'cp-groups' ),
			'desc' => __( 'The location for the meeting. Ex City Name, ST 12345', 'cp-groups' ),
			'id'   => 'location',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Group is Full', 'cp-groups' ),
			'desc' => __( 'This group is full and not accepting new registrations.', 'cp-groups' ),
			'id'   => 'is_group_full',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => Settings::get( 'kid_friendly_badge_label', __( 'Kid Friendly', 'cp-groups' ), 'cp_groups_labels_options' ),
			'desc' => __( 'This group is kid friendly or has child care.', 'cp-groups' ),
			'id'   => 'kid_friendly',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => Settings::get( 'accessible_badge_label', __( 'Wheelchair Accessible', 'cp-groups' ), 'cp_groups_labels_options' ),
			'desc' => __( 'This group is handicap accessible.', 'cp-groups' ),
			'id'   => 'handicap_accessible',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => __( 'Virtual', 'cp-groups' ),
			'desc' => __( 'This group meets online.', 'cp-groups' ),
			'id'   => 'is_virtual',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => __( 'Group Details', 'cp-groups' ),
			'desc' => __( 'The link for the View Details button.', 'cp-groups' ),
			'id'   => 'public_url',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Registration Action', 'cp-groups' ),
			'desc' => __( 'The action for the group register button (email address, registration page, etc).', 'cp-groups' ),
			'id'   => 'registration_url',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Contact Action', 'cp-groups' ),
			'desc' => __( 'The action for the group contact button (email address, contact page, etc).', 'cp-groups' ),
			'id'   => 'action_contact',
			'type' => 'text',
		] );

		$this->register_cp_connect_fields( $cmb );
	}

	/**
	 * Actions to run whenever a group is saved
	 *
	 * @since  1.0.2
	 *
	 * @param $group_id
	 *
	 * @return bool|\ChurchPlugins\Models\Item|\ChurchPlugins\Models\ItemType|\ChurchPlugins\Models\Source|void
	 * @author Tanner Moushey, 5/2/23
	 */
	public function save_post( $group_id ) {

		// make sure `is_group_full` is always set for better querying
		if ( ! get_post_meta( $group_id, 'is_group_full', true ) ) {
			update_post_meta( $group_id, 'is_group_full', 0 );
		}

		parent::save_post( $group_id );
	}

	/**
	 * Update geolocation from group address
	 *
	 * @param int $meta_id
	 * @param int $object_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 * @since  1.2.0
	 */
	public function set_geolocation( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( 'location' !== $meta_key || get_post_type( $object_id ) !== $this->post_type ) {
			return;
		}

		if (
			! Settings::get_advanced( 'enable_zipcode_filter', false ) ||
			! Settings::get_advanced( 'mapbox_api_key', '' )
		) {
			return;
		}
		
		$api    = new \CP_Groups\API\Mapbox( Settings::get_advanced( 'mapbox_api_key' ) );
		$coords = $api->geocode( $meta_value );
		
		if ( $coords ) {
			update_post_meta( $object_id, 'geolocation', implode( ',', $coords ) );
			// delete cached sorted groups
			wp_cache_flush_group( 'cp_groups_sort_by_distance' );
		}
	}

	/**
	 * Load geolocations for all groups that don't have it enabled.
	 *
	 * @param string $field_id The field ID.
	 * @param mixed $updated Whether the field was updated.
	 * @param string $action The action.
	 * @param \CMB2_Field $field The field object.
	 * @since 1.2.0
	 */
	public function load_geolocations( $field_id, $updated, $action, $field ) {
		if ( 'enable_zipcode_filter' !== $field_id ) {
			return;
		}

		if (
			isset( $_POST[ 'enable_zipcode_filter' ] ) &&
			'on' === $_POST[ 'enable_zipcode_filter' ] &&
			! Settings::get_advanced( 'enable_zipcode_filter', false ) &&
			Settings::get_advanced( 'mapbox_api_key', '' )
		) {
			// get all groups that don't have a `geolocation` meta key
			$group_ids = get_posts( [
				'post_type'      => $this->post_type,
				'posts_per_page' => 9999,
				'fields'         => 'ids',
				'meta_query' => [
					[
						'key' => 'geolocation',
						'compare' => 'NOT EXISTS',
					],
				],
			] );
			$this->geo_importer->delete_all();
			foreach ( $group_ids as $group_id ) {
				$this->geo_importer->push_to_queue( $group_id );
			}
			$this->geo_importer->save()->dispatch();
		}
	}

	/**
	 * Register custom meta fields based on the mapping from CP Connect
	 *
	 * @param \CMB2 $cmb the metabox to add the custom fields to
	 * @return void
	 * @since 1.1.3
	 * @author Jonathan Roley
	 */
	public function register_cp_connect_fields( $cmb ) {
		$option = get_option( 'cp_group_custom_meta_mapping' );

		if( ! $option ) {
			return;
		}

		foreach( $option as $key => $data ) {
			$cmb->add_field( [
				'name' => $data['display_name'],
				'desc' => __( 'The ' . $data['display_name'] . ' for the group.', 'cp-groups' ),
				'id'   => $data['slug'],
				'type' => 'select',
				'options' => $data['options'],
				'show_option_none' => true
			] );
		}
	}

	/**
	 * Sort groups by distance from a given set of coordinates, also filters out groups that are too far away
	 *
	 * @param array $coords The coordinates to sort by
	 * @return array The sorted groups
	 */
	public function sort_by_distance( $coords ) {
		global $wpdb;

		$cache_key   = 'sorted_by_distance_' . md5( serialize( $coords ) );
		$cache_group = 'cp_groups_sort_by_distance';

		$cached = wp_cache_get( $cache_key, $cache_group );
		if ( $cached ) {
			return $cached;
		}

		// get all groups ids that have a geolocation set
		$groups = $wpdb->get_results( "SELECT ID, meta.meta_value AS geolocation
			FROM $wpdb->posts AS posts
			LEFT JOIN $wpdb->postmeta AS meta ON ID = meta.post_id
			WHERE posts.post_type = '{$this->post_type}'
			AND posts.post_status = 'publish'
			AND meta.meta_key = 'geolocation'
			ORDER BY ID ASC
			LIMIT 9999
		" );

		$sorted = [];

		$max_distance = (float) Settings::get_advanced( 'geo_radius', 10 );

		foreach ( $groups as $group ) {
			$location = array_map( 'floatval', explode( ',', (string) $group->geolocation ) );
			$distance = $this->haversine( $coords, $location );
			if ( $distance <= $max_distance ) {
				$sorted[ $group->ID ] = $distance;
			}
		}

		asort( $sorted );

		wp_cache_set( $cache_key, $sorted, $cache_group );

		return $sorted;
	}

	/**
	 * Get the distance between two sets of coordinates using the Haversine formula
	 *
	 * @param array $coord1 The first set of coordinates
	 * @param array $coord2 The second set of coordinates
	 * @return float The distance between the two sets of coordinates in miles
	 */
	public function haversine( $coord1, $coord2 ) {
		$earth_radius    = 3963; // miles radius
		$lat1            = deg2rad( $coord1[1] );
		$lat2            = deg2rad( $coord2[1] );
		$delta_lat       = deg2rad( $coord2[1] - $coord1[1] );
		$delta_lon       = deg2rad( $coord2[0] - $coord1[0] );
		$haversine_value = sin( $delta_lat / 2 ) ** 2 + cos( $lat1 ) * cos( $lat2 ) * sin( $delta_lon / 2 ) ** 2;
		$angle_delta     = 2 * atan2( sqrt( $haversine_value ), sqrt( 1 - $haversine_value ) );

		return $earth_radius * $angle_delta;
	}
}
