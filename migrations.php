<?php
/**
 * Manage migrations between plugin versions
 *
 * @package CP_Groups
 */

return [
	'1.2.0' => [
		'up' => function() {
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
		},
		'down' => function() {
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
	]
];
