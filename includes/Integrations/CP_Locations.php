<?php

namespace CP_Groups\Integrations;

class CP_Locations {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of CP_Locations
	 *
	 * @return CP_Locations
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof CP_Locations ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add Hooks and Actions
	 */
	public function __construct() {
		add_action( 'cploc_location_meta_details', [ $this, 'location_meta' ] );
		add_filter( 'cp_groups_email_headers', [ $this, 'add_bcc' ], 10, 2 );
	}

	/**
	 * Add meta for groups
	 *
	 * @since  1.1.1
	 *
	 * @author Tanner Moushey
	 */
	public function location_meta() {
		if ( ! function_exists( 'cp_locations' ) ) {
			return;
		}

		$cmb = new_cmb2_box( [
			'id'           => "cp_groups_meta",
			'title'        => __( 'Group Settings', 'cp-groups' ),
			'object_types' => [ cp_locations()->setup->post_types->locations->post_type ],
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		] );

		$cmb->add_field( array(
			'name' => __( 'BCC', 'cp-groups' ),
			'desc' => __( 'Enter the email address(es) to BCC whenever a contact form is submitted. Comma separate multiple email addresses.', 'cp-groups' ),
			'id'   => 'cp_group_email_bcc',
			'type' => 'text',
		) );

	}

	/**
	 * Add Bcc addresses to groups header
	 *
	 * @since  1.1.1
	 *
	 * @param $headers
	 * @param $group_id
	 *
	 * @return mixed
	 * @author Tanner Moushey, 7/6/23
	 */
	public function add_bcc( $headers, $group_id ) {
		if ( ! $group_id ) {
			return $headers;
		}

		$locations = get_the_terms( $group_id, cp_locations()->setup->taxonomies->location->taxonomy );

		if ( is_wp_error( $locations ) || ! $locations ) {
			return $headers;
		}

		foreach ( $locations as $location ) {
			if ( $location_id = cp_locations()->setup->taxonomies->location::get_id_from_term( $location->slug ) ) {
				if ( $bcc = get_post_meta( $location_id, 'cp_group_email_bcc', true ) ) {
					$headers[] = 'Bcc: ' . $bcc;
				}
			}
		}

		return $headers;
	}

}