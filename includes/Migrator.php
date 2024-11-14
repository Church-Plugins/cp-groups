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

require_once __DIR__ . '/ChurchPlugins/Setup/Migrator.php';

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
			'1.2.0' => [
				'up'   => [ $this, 'migrate_1_2_0' ],
				'down' => [ $this, 'rollback_1_2_0' ],
			]
		);
	}

	/**
	 * Migrate to 1.2.0
	 */
	public function migrate_1_2_0() {
		// group leader is stored as postmeta, with leader_name and leader_email values
		// we need to convert this to an array of leader objects and save it as the leaders postmeta
		$group_args = [
			'post_type'      => 'cp_group',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		];

		$group_ids = get_posts( $group_args );

		foreach ( $group_ids as $group_id ) {
			$leader_name  = get_post_meta( $group_id, 'leader_name', true );
			$leader_email = get_post_meta( $group_id, 'leader_email', true );

			if ( ! $leader_name && ! $leader_email ) {
				continue;
			}

			$leaders = [
				[
					'id'    => '',
					'name'  => $leader_name,
					'email' => $leader_email,
				],
			];

			update_post_meta( $group_id, 'leaders', $leaders );
			delete_post_meta( $group_id, 'leader_name' );
			delete_post_meta( $group_id, 'leader_email' );
		}
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
