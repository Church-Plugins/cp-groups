<?php
/**
 * Handles integration with Groups Engine
 *
 * @package CP_Groups
 * @since 1.2.0
 */

namespace CP_Groups\Integrations;

use CP_Groups\Admin\Settings;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * class GroupsEngine
 */
class GroupsEngine {

	/**
	 * Singleton instance
	 */
	protected static $_instance;

	/**
	 * Only make one instance of GroupsEngine
	 *
	 * @return GroupsEngine
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof GroupsEngine ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 */
	protected function __construct() {
		add_action( 'cp_groups_import_from_groups_engine', [ $this, 'import_from_groups_engine' ] );
	}

	/**
	 * Import groups from Groups Engine
	 *
	 * @return void
	 */
	public function import_from_groups_engine() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ge_groups" );

		foreach ( $groups as $ge_group ) {
			$group_id = wp_insert_post( [
				'post_title'   => $ge_group->group_title,
				'post_content' => $ge_group->group_description,
				'post_status'  => 'publish',
				'post_type'    => 'cp_group',
			] );

			// group is closed
			if ( $ge_group->group_status === '0' || $ge_group->group_status === '2' ) {
				update_post_meta( $group_id, 'is_group_full', 1 );
			}

			// group time description
			$day_names = [ '1' => 'Sunday', '2' => 'Monday', '3' => 'Tuesday', '4' => 'Wednesday', '5' => 'Thursday', '6' => 'Friday', '7' => 'Saturday', '8' => 'Day' ];
			$time_desc = $ge_group->group_frequency . ' ' . $day_names[ $ge_group->group_day ];
			if ( $ge_group->group_day === '8' && $ge_group->group_frequency === 'Every' ) {
				$time_desc = 'Every day';
			}
			update_post_meta( $group_id, 'time_desc', $time_desc );

			// group location
			$this->import_group_location( $ge_group->group_id, $group_id );

			// group leaders
			$this->import_group_leaders( $ge_group->group_id, $group_id );

			// group types
			$this->import_group_types( $ge_group->group_id, $group_id );

			// import group topics as categories
			$this->import_group_categories( $ge_group->group_id, $group_id );
		}
	}
	
	/**
	 * Get group location for a GE group and attach to the new CP group
	 *
	 * @param int $ge_group_id
	 * @param int $group_id
	 */
	protected function import_group_location( $ge_group_id, $group_id ) {
		global $wpdb;
		$location = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ge_locations l
				LEFT JOIN {$wpdb->prefix}ge_group_location_matches m ON l.location_id = m.location_id
				WHERE m.group_id = %d",
				$ge_group_id
			)
		);

		$full_address = false;
		if ( $location->location_address1 && $location->location_city && $location->location_state && $location->location_zip ) {
			$full_address = sprintf(
				'%s, %s%s, %s %s',
				$location->location_address1,
				$location->location_address2 ? $location->location_address2 . ', ' : '',
				$location->location_city,
				$location->location_state,
				$location->location_zip
			);
			update_post_meta( $group_id, 'location', $full_address );
		}

		// geo coordinates
		if ( $location->location_lat && $location->location_long ) {
			update_post_meta( $group_id, 'geolocation', "$location->location_long,$location->location_lat" );

			if ( ! $full_address && $api_key = Settings::get_advanced( 'mapbox_api_key', '' ) ) {
				$api = new \CP_Groups\API\Mapbox( $api_key );
				$full_address = $api->reverse_geocode( $location->location_lat, $location->location_long );
				update_post_meta( $group_id, 'location', $full_address );
			}
		}
	}

	/**
	 * Get group leaders for a GE group and attach to the new CP group
	 *
	 * @param int $ge_group_id
	 * @param int $group_id
	 */
	protected function import_group_leaders( $ge_group_id, $group_id ) {
		global $wpdb;

		// get leaders of groups
		// leaders are in the ge_leaders table, and there is a join table ge_group_leader_matches
		// that links leaders to groups
		$leaders = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ge_leaders l
				LEFT JOIN {$wpdb->prefix}ge_group_leader_matches m ON l.leader_id = m.leader_id
				WHERE m.group_id = %d",
				$ge_group_id
			)
		);

		$cp_leaders = [];

		foreach ( $leaders as $leader ) {
			$cp_leaders[] = [
				'id'    => '',
				'name'  => $leader->leader_name,
				'email' => $leader->leader_email,
			];
		}

		update_post_meta( $group_id, 'leaders', $cp_leaders );
	}

	/**
	 * Get group types for a ge group and attach to the new CP Group
	 *
	 * @param int $ge_group_id
	 * @param int $group_id
	 */
	protected function import_group_types( $ge_group_id, $group_id ) {
		global $wpdb;
		$term_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT group_type_title AS term_name FROM {$wpdb->prefix}ge_group_types t
				LEFT JOIN {$wpdb->prefix}ge_group_group_type_matches m ON t.group_type_id = m.group_type_id
				WHERE m.group_id = %d",
				$ge_group_id
			)
		);
 		$this->insert_terms( $term_names, 'cp_group_type', $group_id );
	}

	/**
	 * Get group topics for a ge group and attach to the new CP Group
	 *
	 * @param int $ge_group_id
	 * @param int $group_id
	 */
	protected function import_group_categories( $ge_group_id, $group_id ) {
		global $wpdb;
		$term_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT topic_name AS term_name FROM {$wpdb->prefix}ge_topics t
				LEFT JOIN {$wpdb->prefix}ge_group_topic_matches m ON t.topic_id = m.topic_id
				WHERE m.group_id = %d",
				$ge_group_id
			)
		);
		$this->insert_terms( $term_names, 'cp_group_category', $group_id );
	}

	/**
	 * Insert terms and attach to post
	 * 
	 * @param string[] $term_names
	 * @param string $taxonomy
	 * @param int $post_id
	 */
	protected function insert_terms( $term_names, $taxonomy, $post_id ) {
		$term_ids = [];
		foreach ( $term_names as $term_name ) {
			if ( $term = get_term_by( 'name', $term_name, $taxonomy ) ) {
				$term_ids[] = $term->term_id;
			} else {
				$term = wp_insert_term( $term_name, $taxonomy );
				$term_ids[] = $term['term_id'];
			}
		}
		wp_set_object_terms( $post_id, $term_ids, $taxonomy );
	}	
}
