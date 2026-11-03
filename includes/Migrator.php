<?php
/**
 * Handles CP Groups migrations
 *
 * @package CP_Groups
 * @since 1.2.0
 */

namespace CP_Groups;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ChurchPlugins\Setup\Migrator', false ) ) {
	require_once __DIR__ . '/ChurchPlugins/Setup/Migrator.php';
}

/**
 * Class Migrator
 */
class Migrator extends \ChurchPlugins\Setup\Migrator {

	/**
	 * Get list of migrations
	 *
	 * @return array
	 */
	public function get_migrations(): array {
		return array(
			'1.2.0-beta1' => [
				'up'   => [ $this, 'migrate_1_2_0' ],
				'down' => [ $this, 'rollback_1_2_0' ],
			]
		);
	}

	public function migrate_1_2_0() {
		// run after CPTs are registered
		add_action( 'init', [ $this, 'migrate_leader_data' ] );
	}

	/**
	 * Copy 1.1 leader meta into the 1.2 leaders list.
	 *
	 * 1.1.x stores the name in `leader` and the email in `leader_email`.
	 * `leader_name` is only used when `leader` is empty. The email is copied
	 * only when is_email() accepts it. Groups that already have a leaders
	 * list are left unchanged, and the old meta is kept.
	 */
	public function migrate_leader_data() {
		$group_ids = get_posts( [
			'post_type'      => 'cp_group',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		] );

		foreach ( $group_ids as $group_id ) {
			$this->migrate_group_leader( $group_id );
		}
	}

	/**
	 * Migrate one group when it still needs a leaders list.
	 *
	 * @param int $group_id Group post ID.
	 */
	protected function migrate_group_leader( $group_id ) {
		$existing = get_post_meta( $group_id, 'leaders', true );

		if ( is_array( $existing ) && ! empty( $existing ) ) {
			return;
		}

		$name  = $this->legacy_leader_name( $group_id );
		$email = $this->validated_leader_email( $this->legacy_leader_email( $group_id ) );

		if ( '' === $name && '' === $email ) {
			return;
		}

		$id = '';

		if ( '' !== $email && $user = get_user_by( 'email', $email ) ) {
			$id = $user->ID;
		}

		update_post_meta( $group_id, 'leaders', [
			[
				'id'    => $id,
				'name'  => $name,
				'email' => $email,
			],
		] );
	}

	/**
	 * Leader name stored by 1.1.x, with a fallback for `leader_name`.
	 *
	 * @param int $group_id Group post ID.
	 * @return string
	 */
	protected function legacy_leader_name( $group_id ) {
		$name = $this->meta_string( get_post_meta( $group_id, 'leader', true ) );

		if ( '' !== $name ) {
			return $name;
		}

		return $this->meta_string( get_post_meta( $group_id, 'leader_name', true ) );
	}

	/**
	 * Leader email stored by 1.1.x.
	 *
	 * @param int $group_id Group post ID.
	 * @return string
	 */
	protected function legacy_leader_email( $group_id ) {
		return $this->meta_string( get_post_meta( $group_id, 'leader_email', true ) );
	}

	/**
	 * Keep a legacy email only when is_email() accepts it.
	 *
	 * @param string $email Trimmed legacy email.
	 * @return string
	 */
	protected function validated_leader_email( $email ) {
		if ( '' === $email ) {
			return '';
		}

		$valid = is_email( $email );

		if ( ! is_string( $valid ) || '' === $valid ) {
			return '';
		}

		return $valid;
	}

	/**
	 * Normalize a meta value to a trimmed string.
	 *
	 * @param mixed $value Raw meta value.
	 * @return string
	 */
	protected function meta_string( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return trim( $value );
	}

	/**
	 * Rollback from 1.2.0
	 */
	public function rollback_1_2_0() {
		// group leader is stored as an array of leader objects in the leaders postmeta
		// we need to convert this to leader_name and leader_email values and save them as postmeta
		$group_args = [
			'post_type'      => 'cp_group',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		];

		$group_ids = get_posts( $group_args );

		foreach ( $group_ids as $group_id ) {
			$leaders = get_post_meta( $group_id, 'leaders', true );

			if ( ! $leaders || ! is_array( $leaders ) ) {
				continue;
			}

			$leader_name  = $leaders[0]['name'];
			$leader_email = $leaders[0]['email'];

			update_post_meta( $group_id, 'leader_name', $leader_name );
			update_post_meta( $group_id, 'leader_email', $leader_email );
			delete_post_meta( $group_id, 'leaders' );
		}
	}
}
