<?php

namespace CP_Groups\Setup;

/**
 * Setup plugin initialization
 */
class Init {

	/**
	 * @var Init
	 */
	protected static $_instance;

	/**
	 * @var PostTypes\Init;
	 */
	public $post_types;
	
	/**
	 * @var Taxonomies\Init;
	 */
	public $taxonomies;
	
	/**
	 * Only make one instance of Init
	 *
	 * @return Init
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Init ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 */
	protected function __construct() {
		$this->includes();
		$this->actions();
	}

	/**
	 * Admin init includes
	 *
	 * @return void
	 */
	protected function includes() {
		ShortCodes::get_instance();
		$this->post_types = PostTypes\Init::get_instance();
		$this->taxonomies = Taxonomies\Init::get_instance();
	}

	protected function actions() {
//		add_action( 'init', [ $this, 'create_group_leader_role' ] );
//		add_action( 'show_user_profile', [ $this, 'add_group_leader_field'] );
//		add_action( 'edit_user_profile', [ $this, 'add_group_leader_field'] );
//		add_action( 'personal_options_update', [ $this, 'save_group_leader_role' ] );
//		add_action( 'edit_user_profile_update', [ $this, 'save_group_leader_role' ] );
//		add_action( 'profile_update', [ $this, 'save_group_leader_role' ] );
	}

	/** Actions ***************************************************/

	/**
	 * Creates the `cp_group_leader` role
	 *
	 * @return void
	 */
	public function create_group_leader_role() {
		if ( ! get_role( 'cp_group_leader') ) {
			add_role(
				'cp_group_leader',
				__( 'Group Leader', 'cp-groups' ),
				[ 'read' => true ]
			);
		}
	}

	/**
	 * Add group leader field to user profile
	 *
	 * @param WP_User $user
	 */
	public function add_group_leader_field( $user ) {
		$is_group_leader = in_array( 'cp_group_leader', $user->roles );

		?>
    <h3><?php _e( 'CP Groups', 'cp-groups' ); ?></h3>
    <table class="form-table">
			<tr>
				<th><label for="cp_group_leader"><?php _e( 'Group Leader', 'cp-groups' ); ?></label></th>
				<td>
					<input type="checkbox" name="cp_group_leader" id="cp_group_leader" value="1" <?php checked( $is_group_leader ); ?> />
					<span class="description"><?php _e( 'This user can lead groups.', 'cp-groups' ); ?></span>
				</td>
			</tr>
    </table>
    <?php
	}

	/**
	 * Save group leader role
	 *
	 * @param int $user_id
	 */
	public function save_group_leader_role( $user_id ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( isset( $_POST['cp_group_leader'] ) ) {
			$user->add_role( 'cp_group_leader' );
		} else {
			$user->remove_role( 'cp_group_leader' );
		}
	}
}
