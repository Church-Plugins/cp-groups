<?php

namespace CP_Groups\Controllers;

use CP_Groups\Admin\Settings;
use ChurchPlugins\Controllers\Controller;
use ChurchPlugins\Helpers;
use CP_Groups\Exception;

class Group extends Controller{

	public function get_excerpt() {
		return $this->filter( get_the_excerpt( $this->post->ID ), __FUNCTION__ );
	}

	public function get_content( $raw = false ) {
		$content = get_the_content( null, false, $this->post );
		if ( ! $raw ) {
			$content = apply_filters( 'the_content', $content );
		}

		return $this->filter( $content, __FUNCTION__ );
	}

	public function get_title() {
		return $this->filter( get_the_title( $this->post->ID ), __FUNCTION__ );
	}

	public function get_permalink() {
		return $this->filter( get_permalink( $this->post->ID ), __FUNCTION__ );
	}

	public function get_locations() {
		if ( ! function_exists( 'cp_locations' ) ) {
			return $this->filter( [], __FUNCTION__ );
		}

		$tax = cp_locations()->setup->taxonomies->location->taxonomy;
		$locations = wp_get_post_terms( $this->post->ID, $tax );

		if ( is_wp_error( $locations ) || empty( $locations ) ) {
			return $this->filter( [], __FUNCTION__ );
		}

		$item_locations = [];
		foreach ( $locations as $location ) {
			$location_id = \CP_Locations\Setup\Taxonomies\Location::get_id_from_term( $location->slug );

			if ( 'global' === $location_id ) {
				continue;
			}

			$location    = new \CP_Locations\Controllers\Location( $location_id );
			$item_locations[ $location_id ] = [
				'title' => $location->get_title(),
				'url'   => $location->get_permalink(),
			];
		}

		return $this->filter( $item_locations, __FUNCTION__ );
	}

	/**
	 * Return the registration URL
	 *
	 * @since  1.1.0
	 *
	 * @return mixed|void
	 * @author Tanner Moushey, 6/20/23
	 */
	public function get_registration_url() {
		$url = $this->registration_url;

		if ( is_email( $url ) ) {
			$url = 'mailto:' . $url;
		}

		return $this->filter( $url, __FUNCTION__ );
	}

	/**
	 * Return the registration URL
	 *
	 * @since  1.1.0
	 *
	 * @return mixed|void
	 * @author Tanner Moushey, 6/20/23
	 */
	public function get_contact_url() {
		if ( Settings::get_advanced( 'contact_action' ) == 'form' ) {
			$url = $this->leader_email;
		} else {
			$url = $this->action_contact;
		}

		if ( is_email( $url ) ) {
			$url = 'mailto:' . $url;
		}

		return $this->filter( $url, __FUNCTION__ );
	}

	/**
	 * Get default thumbnail for items
	 *
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_default_thumb() {
		$img = Settings::get( 'default_thumbnail', '' );
		return $this->filter( $img, __FUNCTION__ );
	}

	/**
	 * Get thumbnail
	 *
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_thumbnail() {
		if ( $thumb = get_the_post_thumbnail_url( $this->post->ID ) ) {
			return $this->filter( $thumb, __FUNCTION__ );
		}

		if ( ! $thumb ) {
			$thumb = $this->get_default_thumb();
		}

		return $this->filter( $thumb, __FUNCTION__ );
	}

	public function get_publish_date() {
		$date = get_post_datetime( $this->post, 'date', 'gmt' );
		return $this->filter( $date->format('U' ), __FUNCTION__ );
	}

	public function get_categories() {
		$return = [];
		$terms = get_the_terms( $this->post->ID, 'cp_group_category' );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		if ( $terms ) {
			foreach( $terms as $term ) {
				$return[ $term->slug ] = $term->name;
			}
		}


		return $this->filter( $return, __FUNCTION__ );
	}

	/**
	 * Get the type taxonomy associated with this item
	 *
	 * @return array|mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_types() {
		$return = [];
		$terms = get_the_terms( $this->post->ID, 'cp_group_type' );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		if ( $terms ) {
			foreach( $terms as $term ) {
				$return[ $term->slug ] = $term->name;
			}
		}

		return $this->filter( $return, __FUNCTION__ );
	}

	/**
	 * Get the type taxonomy associated with this item
	 *
	 * @return array|mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_life_stages() {
		$return = [];
		$terms = get_the_terms( $this->post->ID, 'cp_group_life_stage' );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		if ( $terms ) {
			foreach( $terms as $term ) {
				$return[ $term->slug ] = $term->name;
			}
		}

		return $this->filter( $return, __FUNCTION__ );
	}

	public function get_api_data() {
		try {
			$data = [
				'id'               => $this->post->ID,
				'originID'         => $this->post->ID,
				'permalink'        => $this->get_permalink(),
				'slug'             => $this->post->post_name,
				'thumb'            => $this->get_thumbnail(),
				'title'            => htmlspecialchars_decode( $this->get_title(), ENT_QUOTES | ENT_HTML401 ),
				'desc'             => $this->get_content(),
				'excerpt'          => $this->get_excerpt(),
				'date'             => [
					'desc'      => Helpers::relative_time( $this->get_publish_date() ),
					'timestamp' => $this->get_publish_date()
				],
				'contact_url'      => $this->get_contact_url(),
				'registration_url' => $this->get_registration_url(),
				'categories'       => $this->get_categories(),
				'locations'        => $this->get_locations(),
				'types'            => $this->get_types(),
				'lifeStages'       => $this->get_life_stages(),
				'startTime'        => trim( $this->time_desc ),
				'leader'           => trim( $this->leader ),
				'location'         => trim( $this->location ),
				'handicap'         => trim( $this->handicap_accessible ),
				'kidFriendly'      => trim( $this->kid_friendly ),
				'isFull'           => boolval( $this->is_group_full ),
			];
		} catch ( \ChurchPlugins\Exception $e ) {
			error_log( $e );
		}

		return $this->filter( $data, __FUNCTION__ );
	}

}
