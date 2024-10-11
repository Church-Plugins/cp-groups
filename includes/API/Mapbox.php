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
	 * Reverse geocode a latitude and longitude.
	 *
	 * @param float $latitude The latitude.
	 * @param float $longitude The longitude.
	 * @return string|false The address of the latitude and longitude, or false if the address could not be reverse geocoded.
	 */
	public function reverse_geocode( $latitude, $longitude ) {
		$cache_key = 'reverse_geocode_' . md5( $latitude . $longitude );

		$cached =  wp_cache_get( $cache_key, 'cp-groups' );
		if ( $cached ) {
			return $cached;
		}

		$query_args = [
			'latitude'     => $latitude,
			'longitude'    => $longitude,
			'access_token' => $this->access_token,
		];

		$response = wp_remote_get( add_query_arg( $query_args, 'https://api.mapbox.com/search/geocode/v6/reverse' ) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $data['features'][0] ) ) {
			return false;
		}

		$full_address = $data['features'][0]['properties']['full_address'] ?? '';

		if ( ! $full_address ) {
			return false;
		}

		wp_cache_set( $cache_key, $full_address, 'cp-groups' );

		return $full_address;
	}
}
