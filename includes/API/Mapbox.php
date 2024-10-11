<?php
/**
 * A utility class for interacting with the Mapbox API.
 *
 * @since 1.2.0
 */

namespace CP_Groups\API;

/**
 * Class Mapbox
 */
class Mapbox {
	/**
	 * The Mapbox API key.
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Class constructor.
	 *
	 * @param string $access_token The Mapbox API key.
	 */
	public function __construct( $access_token ) {
		$this->access_token = $access_token;
	}

	/**
	 * Geocode an address.
	 *
	 * @param string $address The address to geocode.
	 * @return array|false The latitude and longitude of the address, or false if the address could not be geocoded.
	 */
	public function geocode( $address ) {
		$cache_key = 'geocode_' . md5( $address );

		$cached =  wp_cache_get( $cache_key, 'cp-groups' );
		if ( $cached ) {
			return $cached;
		}

		$query_args = [
			'q'            => $address,
			'access_token' => $this->access_token,
			'limit'        => 1,
		];

		$response = wp_remote_get( add_query_arg( $query_args, 'https://api.mapbox.com/search/geocode/v6/forward' ) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $data['features'][0] ) ) {
			return false;
		}

		$feature = $data['features'][0];

		wp_cache_set( $cache_key, $feature['geometry']['coordinates'], 'cp-groups' );

		return $feature['geometry']['coordinates'];
	}

	/**
	 * Convert zip code to coordinates.
	 *
	 * @param string $zipcode The zip code to convert.
	 * @return array|false The latitude and longitude of the zip code, or false if the zip code could not be converted.
	 */
	public function zip_to_coords( $zipcode ) {

	}
}
