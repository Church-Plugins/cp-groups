<?php

namespace CP_Groups\Setup\PostTypes;

/**
 * Setup plugin initialization for CPTs
 */
class Init {

	/**
	 * @var Init
	 */
	protected static $_instance;

	/**
	 * Setup Groups CPT
	 *
	 * @var Group
	 */
	public $groups;

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
	 * Run includes and actions on instantiation
	 *
	 */
	protected function __construct() {
		$this->includes();
		$this->actions();
	}

	/**
	 * Plugin init includes
	 *
	 * @return void
	 */
	protected function includes() {}

	public function in_post_types( $type ) {
		return in_array( $type, $this->get_post_types() );
	}

	public function get_post_types() {
		return [ $this->groups->post_type ];
	}

	/**
	 * Plugin init actions
	 *
	 * @return void
	 * @author costmo
	 */
	protected function actions() {
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
		add_action( 'init', [ $this, 'register_post_types' ], 4 );
		add_action( 'cmb2_render_cp_group_leader', [ $this, 'render_group_leader_field' ], 10, 5 );
		add_action( 'cmb2_sanitize_cp_group_leader', [ $this, 'cp_group_leader_sanitize' ], 10, 2 );
		add_action( 'cmb2_types_esc_cp_group_leader', [ $this, 'cp_group_leader_escaped_value' ], 10, 2 );
	}

	public function register_post_types() {

		$this->groups = Group::get_instance();

		if ( cp_groups()->enabled() ) {
			$this->groups->add_actions();
			do_action( 'cp_register_post_types' );
		}
	}

	public function disable_gutenberg( $status, $post_type ) {
		if ( $this->in_post_types( $post_type ) ) {
			return false;
		}

		return $status;
	}

	/**
	 * Handle escaping for cp_group_leader field
	 *
	 * @param string $check Whether to perform default escaping.
	 * @param mixed  $meta_value The value to be escaped.
	 * @since 1.2.0
	 */
	public function cp_group_leader_escaped_value( $check, $meta_value ) {
		if ( ! is_array( $meta_value ) ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ][ 'id' ]    = absint( $val[ 'id' ] );
			$meta_value[ $key ][ 'name' ]  = esc_html( $val[ 'name' ] );
			$meta_value[ $key ][ 'email' ] = sanitize_email( $val[ 'email' ] );
		}

		return $meta_value;
	}

	/**
	 * Sanitize the value of the cp_group_leader field
	 *
	 * @param mixed $check Whether or not to proceed with default sanitization.
	 * @param mixed $meta_value The value to be sanitized.
	 * @since 1.2.0
	 */
	public function cp_group_leader_sanitize( $check, $meta_value ) {
		if ( ! is_array( $meta_value ) ) {
			return $check;
		}

		$sanitized = array();

		foreach ( $meta_value as $leader ) {
			$sanitized[] = [
				'id'    => absint( $leader['id'] ),
				'name'  => sanitize_text_field( $leader['name'] ),
				'email' => sanitize_email( $leader['email'] ),
			];
		}

		return $sanitized;
	}


	/**
	 * Create a group leader cmb2 field
	 *
	 * @param \CMB2_Field $field The field object.
	 * @param mixed       $escaped_value The value of this field escaped.
	 * @param int         $object_id The id of the current object.
	 * @param string      $object_type The type of object you are working with.
	 * @param object      $field_type_object The field type object.
	 */
	public function render_group_leader_field( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$leaders = get_post_meta( $object_id, 'leaders', true );

		if ( ! is_array( $leaders ) ) {
			$leaders = [];
		}

		$leader_count = count( $leaders );

		$user_arr = [];
		$users    = get_users( [ 'role__in' => [ 'cp_group_leader' ] ] );

		foreach ( $users as $user ) {
			$user_arr[ $user->ID ] = $user->display_name;
		}

		?>
		<div class="cp-groups--group-leader-field">
			<template class="cp-groups--group-leader-template">
				<?php $this->render_single_group_leader_field( [ 'id' => '', 'name' => '', 'email' => '' ], $user_arr, 0 ); ?>
			</template>
			<div class="cp-groups--group-leader-list">
				<?php for ( $i = 0; $i < $leader_count; $i++ ): ?>
					<?php $leader_id = $leaders[ $i ]['id'] ?? ''; ?>
					<?php if ( isset( $user_arr[ $leader_id ] ) || empty( $leader_id ) ) : ?>
						<?php $this->render_single_group_leader_field( $leaders[ $i ], $user_arr, $i ); ?>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
			<button type="button" class="button cp-groups--add-leader"><?php esc_html_e( 'Add', 'cp-groups' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Render a single group leader field
	 *
	 * @param array $leader The leader data.
	 * @param array $user_arr The user array.
	 * @param int   $index The index of this leader.
	 */
	public function render_single_group_leader_field( $leader, $user_arr, $index ) {
		$hide_if_leader = empty( $leader['id'] ) ? '' : 'style="display: none;"';

		?>
		<div class="cp-groups--group-leader">
			<select name="leaders[<?php echo absint( $index ); ?>][id]" class="cp-groups--leader-select">
				<option value=""><?php esc_html_e( 'Custom', 'cp-groups' ); ?></option>
				<?php foreach ( $user_arr as $id => $name ) : ?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php echo selected( $leader['id'], $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="text" name="leaders[<?php echo absint( $index ); ?>][name]" class="cp-groups--leader-name" placeholder="Name" <?php echo $hide_if_leader; ?> value="<?php echo esc_attr( $leader['name'] ); ?>">
			<input type="email" name="leaders[<?php echo absint( $index ); ?>][email]" class="cp-groups--leader-email" placeholder="Email" <?php echo $hide_if_leader; ?> value="<?php echo esc_attr( $leader['email'] ); ?>">
			<button type="button" class="button cp-groups--remove-leader"><?php esc_html_e( 'Remove', 'cp-groups' ); ?></button>
		</div>
		<?php
	}	
}
