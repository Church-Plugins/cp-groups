<?php
namespace CP_Groups\Setup\PostTypes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use ChurchPlugins\Setup\Tables\SourceMeta;
use CP_Groups\Admin\Settings;

use ChurchPlugins\Setup\PostTypes\PostType;

/**
 * Setup for custom post type: Speaker
 *
 * @author costmo
 * @since 1.0
 */
class Groups extends PostType {
	
	/**
	 * Child class constructor. Punts to the parent.
	 *
	 * @author costmo
	 */
	protected function __construct() {
		$this->post_type = "cp_groups";

		$this->single_label = apply_filters( "cploc_single_{$this->post_type}_label", Settings::get_groups( 'singular_label', 'Groups' ) );
		$this->plural_label = apply_filters( "cploc_plural_{$this->post_type}_label", Settings::get_groups( 'plural_label', 'Groups' ) );

		parent::__construct();
	}

	public function add_actions() {
		add_filter( 'enter_title_here', [ $this, 'add_title' ], 10, 2 );
		add_filter( 'cp_location_taxonomy_types', [ $this, 'location_tax' ] );
		parent::add_actions();
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
		
		return $type->rewrite['slug'];
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
		$args['has_archive'] = false;
		$args['supports'][] = 'page-attributes';
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
			'name' => __( 'Group Leader', 'cp-groups' ),
			'desc' => __( 'The name of the group leader.', 'cp-groups' ),
			'id'   => 'leader',
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
			'name' => __( 'Kid Friendly', 'cp-groups' ),
			'desc' => __( 'This group is kid friendly or has child care.', 'cp-groups' ),
			'id'   => 'kid_friendly',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => __( 'Handicap Accessible', 'cp-groups' ),
			'desc' => __( 'This group is handicap accessible.', 'cp-groups' ),
			'id'   => 'handicap_accessible',
			'type' => 'checkbox',
		] );

		$cmb->add_field( [
			'name' => __( 'Registration Action', 'cp-groups' ),
			'desc' => __( 'The action for the group register button (email address, registration page, etc).', 'cp-groups' ),
			'id'   => 'action_register',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Contact Action', 'cp-groups' ),
			'desc' => __( 'The action for the group contact button (email address, contact page, etc).', 'cp-groups' ),
			'id'   => 'action_contact',
			'type' => 'text',
		] );

	}
	
}
