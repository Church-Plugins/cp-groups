<?php
/**
 * A background process class for importing group geolocation data.
 *
 * @since 1.2.0
 */

namespace CP_Groups\Util;

use CP_Groups\Admin\Settings;

/**
 * Class ImportGeolocation
 */
class ImportGeolocation extends \WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = 'cp_groups';

	/**
	 * Action
	 */
	protected $action = 'import_geolocation';

	/**
	 * Handle the task
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	protected function task( $data ) {
		$group_id = absint( $data );

		if ( ! $group_id ) {
			return false;
		}

		$group = get_post( $group_id );

		if ( $group->post_type !== cp_groups()->setup->post_types->groups->post_type ) {
			return false;
		}

		// geolocation already set
		if ( get_post_meta( $group_id, 'geolocation', true ) ) {
			return false;
		}

		$location = get_post_meta( $group_id, 'location', true );
		
		if ( ! $location ) {
			return false;
		}

		$api = new \CP_Groups\API\Mapbox( Settings::get_advanced( 'mapbox_api_key', '' ) );

		$geolocation = $api->geocode( $location );

		if ( ! $geolocation ) {
			cp_groups()->logging->log( sprintf(  'Failed to geocode location for group %d. Attempted to geocode "%s".', $group_id, $location ) );
			return false;
		}

		update_post_meta( $group_id, 'geolocation', implode( ',', $geolocation ) );
		wp_cache_flush_group( 'cp_groups_sort_by_distance' );

		return false;
	}
}
