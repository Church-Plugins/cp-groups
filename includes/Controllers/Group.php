<?php

namespace CP_Groups\Controllers;

use CP_Groups\Admin\Settings;
use ChurchPlugins\Controllers\Controller;
use ChurchPlugins\Helpers;
use CP_Groups\Exception;

class Group extends Controller {

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

		if ( ! is_string( $url ) ) {
			return '';
		}

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

		if ( ! is_string( $url ) ) {
			return '';
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

	public function get_location_label() {
		$label = $this->location_label ?: $this->location;

		return $this->filter( trim( $label ), __FUNCTION__ );
	}

	public function get_location() {
		return $this->filter( trim( $this->location ), __FUNCTION__ );
	}

	public function get_leader( $field = 'name' ) {
		$leaders = $this->get_leaders();

		$leader = [
			'id'    => 0,
			'name'  => '',
			'email' => '',
		];

		if ( ! empty( $leaders ) ) {
			$leader = array_shift( $leaders );
		}

		if ( $field && isset( $leader[ $field ] ) ) {
			$leader = $leader[ $field ];
		}

		return $this->filter( $leader, __FUNCTION__ );
	}

	public function get_leaders() {
		$leaders = get_post_meta( $this->post->ID, 'leaders', true );

		if ( empty( $leaders ) && $this->leader ) {
			$leaders = [
				[
					'id'    => 0,
					'name'  => $this->leader,
					'email' => $this->leader_email,
				]
			];
		}

		if ( ! is_array( $leaders ) ) {
			return [];
		}

		foreach( $leaders as &$leader ) {
			if ( empty( $leader['id'] ) ) {
				continue;
			}

			$user = get_user_by( 'ID', $leader['id'] );
			if ( $user ) {
				$leader['name'] = $user->first_name . ' ' . $user->last_name;
				$leader['email'] = $user->user_email;
			}
		}

		$leaders = array_map( [ $this, 'sanitize_leader' ], $leaders );

		return $this->filter( $leaders, __FUNCTION__ );
	}
	
	/**
	 * Update all leaders for this group
	 *
	 * @param array $leaders Array of leader data
	 * @return bool True on success, false on failure
	 */
	public function update_leaders( $leaders ) {
		if ( ! is_array( $leaders ) ) {
			return false;
		}
		
		// Sanitize and normalize leaders
		$sanitized_leaders = array_map( [ $this, 'sanitize_leader' ], $leaders );
		
		// Update the meta (this will trigger the sync_leader_meta hook in PostTypes/Group.php)
		return update_post_meta( $this->post->ID, 'leaders', $sanitized_leaders );
	}
	
	/**
	 * Add a leader to this group
	 *
	 * @param mixed $id_or_email User ID or email address
	 * @param string $name Name (required when using email)
	 * @return bool True on success, false if leader already exists or invalid data
	 */
	public function add_leader( $id_or_email, $name = '' ) {
		$leader = $this->prepare_leader_data( $id_or_email, $name );
		if ( ! $leader ) {
			return false;
		}
		
		$leaders = $this->get_leaders();
		
		// Check if already exists
		foreach ( $leaders as $existing ) {
			if ( ( $leader['id'] && $leader['id'] === $existing['id'] ) || 
				( ! $leader['id'] && $leader['email'] === $existing['email'] ) ) {
				return false; // Already exists
			}
		}
		
		$leaders[] = $leader;
		return $this->update_leaders( $leaders );
	}
	
	/**
	 * Remove a leader from this group
	 *
	 * @param mixed $id_or_email User ID or email address
	 * @return bool True on success, false if not found
	 */
	public function remove_leader( $id_or_email ) {
		$leaders = $this->get_leaders();
		$found = false;
		
		if ( is_numeric( $id_or_email ) ) {
			$id = absint( $id_or_email );
			$leaders = array_filter( $leaders, function( $leader ) use ( $id, &$found ) {
				if ( absint( $leader['id'] ) === $id ) {
					$found = true;
					return false;
				}
				return true;
			} );
		} else if ( is_email( $id_or_email ) ) {
			$email = sanitize_email( $id_or_email );
			$leaders = array_filter( $leaders, function( $leader ) use ( $email, &$found ) {
				if ( $leader['email'] === $email ) {
					$found = true;
					return false;
				}
				return true;
			} );
		}
		
		if ( ! $found ) {
			return false;
		}
		
		return $this->update_leaders( array_values( $leaders ) );
	}
	
	/**
	 * Helper method to prepare leader data
	 *
	 * @param mixed $id_or_email User ID or email
	 * @param string $name Optional name (required for email)
	 * @return array|false Leader data or false on failure
	 */
	protected function prepare_leader_data( $id_or_email, $name = '' ) {
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'ID', absint( $id_or_email ) );
			if ( ! $user ) {
				return false;
			}
			
			return [
				'id' => $user->ID,
				'name' => $user->first_name . ' ' . $user->last_name,
				'email' => $user->user_email
			];
		} else if ( is_email( $id_or_email ) ) {
			$data = [
				'id' => '',
				'name' => sanitize_text_field( $name ),
				'email' => sanitize_email( $id_or_email )
			];

			$user = get_user_by( 'email', $data['email'] );
			if ( $user ) {
				$data['id'] = $user->ID;
				$data['name'] = $user->first_name . ' ' . $user->last_name;
			}

			if ( empty( $data['name'] ) ) {
				return false; // Name is required for email
			}

			return $data;
		}
		
		return false;
	}
	
	/**
	 * Sanitize a leader entry
	 *
	 * @param array $leader Leader data
	 * @return array Sanitized leader data
	 */
	protected function sanitize_leader( $leader ) {
		return [
			'id'    => isset( $leader['id'] ) ? absint( $leader['id'] ) : '',
			'name'  => isset( $leader['name'] ) ? sanitize_text_field( trim( $leader['name'] ) ) : '',
			'email' => isset( $leader['email'] ) ? sanitize_email( trim( $leader['email'] ) ) : '',
		];
	}


	/**
	 * Get all groups where a user is a leader
	 *
	 * @param mixed  $id         User ID
	 * @param string $email      Optional email address when first param is ID
	 * @param array  $query_args Optional additional WP_Query arguments
	 *
	 * @since 1.2.0
	 *
	 * @return array Array of group post IDs
	 */
	public static function get_groups_by_leader( $user_id, $user_email = '', $query_args = [] ) {

		$args = array_merge( [
			'post_type'              => 'cp_group',
			'post_status'            => 'publish',
			'posts_per_page'         => 999, // Limit to reasonable batch size
			'fields'                 => 'ids',        // Just get IDs for better performance
			'no_found_rows'          => true,  // Skip counting total rows for pagination
			'update_post_meta_cache' => false, // Don't prime post meta cache
			'update_post_term_cache' => false, // Don't prime taxonomy cache
			'meta_query'             => [
				'relation' => 'OR'
			]
		], $query_args );

		// Add user ID to query if provided
		if ( ! empty( $user_id ) ) {
			$args['meta_query'][] = [
				'key'     => "leader_",
				'value'   => absint( $user_id ),
				'compare' => '=',
				'compare_key' => 'LIKE'
			];
		}

		// Add email to query if provided
		if ( ! empty( $user_email ) && is_email( $user_email ) ) {
			$args['meta_query'][] = [
				'key'     => 'leader_',
				'value'   => sanitize_email( $user_email ),
				'compare' => '=',
				'compare_key' => 'LIKE'
			];
		}

		// Return empty array if no valid search criteria
		if ( count( $args['meta_query'] ) <= 1 ) {
			return [];
		}

		$args = apply_filters( 'cp_groups_get_groups_by_leader_args', $args, $user_id, $user_email );

		remove_action( 'pre_get_posts', [ cp_groups()->setup->post_types->groups, 'groups_query' ] );
		$query = new \WP_Query( $args );
		add_action( 'pre_get_posts', [ cp_groups()->setup->post_types->groups, 'groups_query' ] );

		if ( ! $query->have_posts() ) {
			return [];
		}

		return $query->posts;
	}

	public function get_leader_emails() {
		$emails = [];
		$leaders = $this->get_leaders();

		if ( empty( $leaders ) ) {
			return [];
		}

		foreach( $leaders as $leader ) {
			if ( ! empty( $leader['email'] ) ) {
				$emails[] = $leader['email'];
			}
		}

		return $this->filter( $emails, __FUNCTION__ );
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
				'leader'           => trim( $this->get_leader() ),
				'leaders'          => $this->get_leaders(),
				'location'         => $this->get_location_label(),
				'handicap'         => trim( $this->handicap_accessible ),
				'kidFriendly'      => trim( $this->kid_friendly ),
				'isFull'           => boolval( $this->is_group_full ),
				'isVirtual'        => boolval( $this->is_virtual ),
			];
		} catch ( \ChurchPlugins\Exception $e ) {
			error_log( $e );
		}

		return $this->filter( $data, __FUNCTION__ );
	}

}
