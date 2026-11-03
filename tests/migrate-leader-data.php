<?php
/**
 * Check the 1.2.0 leader migration.
 *
 * Run from the plugin root:
 *
 *     php tests/migrate-leader-data.php
 *
 * The script stubs the post meta API and runs migrate_leader_data() against:
 *
 * - a group with a leader name and email
 * - a group with a leader name only
 * - a group with an email only
 * - a group with neither
 * - a group that already has a leaders list
 * - the same records a second time
 *
 * It also checks the leader_name fallback and that legacy meta is kept.
 *
 * @package CP_Groups
 */

namespace ChurchPlugins\Setup {

	if ( ! class_exists( 'ChurchPlugins\Setup\Migrator', false ) ) {
		abstract class Migrator {
			/**
			 * @return array
			 */
			abstract public function get_migrations(): array;
		}
	}
}

namespace {

	define( 'ABSPATH', __DIR__ );

	$GLOBALS['cp_groups_posts']    = array();
	$GLOBALS['cp_groups_meta']     = array();
	$GLOBALS['cp_groups_users']    = array();
	$GLOBALS['cp_groups_updates']  = array();
	$GLOBALS['cp_groups_deletes']  = array();
	$GLOBALS['cp_groups_failures'] = 0;

	/**
	 * @param array $args Query arguments.
	 * @return int[]
	 */
	function get_posts( $args ) {
		$type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
		$ids  = array();

		foreach ( $GLOBALS['cp_groups_posts'] as $id => $post_type ) {
			if ( $post_type === $type ) {
				$ids[] = (int) $id;
			}
		}

		return $ids;
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Return a single value.
	 * @return mixed
	 */
	function get_post_meta( $post_id, $key, $single = false ) {
		if ( ! $single ) {
			return array();
		}

		if ( ! isset( $GLOBALS['cp_groups_meta'][ $post_id ][ $key ] ) ) {
			return '';
		}

		return $GLOBALS['cp_groups_meta'][ $post_id ][ $key ];
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 * @return bool
	 */
	function update_post_meta( $post_id, $key, $value ) {
		$GLOBALS['cp_groups_updates'][]                 = array( (int) $post_id, $key );
		$GLOBALS['cp_groups_meta'][ $post_id ][ $key ] = $value;
		return true;
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return bool
	 */
	function delete_post_meta( $post_id, $key ) {
		$GLOBALS['cp_groups_deletes'][] = array( (int) $post_id, $key );
		unset( $GLOBALS['cp_groups_meta'][ $post_id ][ $key ] );
		return true;
	}

	/**
	 * @param string $field Lookup field.
	 * @param string $value Lookup value.
	 * @return object|false
	 */
	function get_user_by( $field, $value ) {
		if ( 'email' !== $field || ! isset( $GLOBALS['cp_groups_users'][ $value ] ) ) {
			return false;
		}

		return $GLOBALS['cp_groups_users'][ $value ];
	}

	/**
	 * @param int    $id        Post ID.
	 * @param string $post_type Post type.
	 * @param array  $meta      Meta values.
	 */
	function cp_groups_add_post( $id, $post_type, $meta ) {
		$GLOBALS['cp_groups_posts'][ $id ] = $post_type;
		$GLOBALS['cp_groups_meta'][ $id ]  = $meta;
	}

	/**
	 * @param string $label    Check label.
	 * @param mixed  $expected Expected value.
	 * @param mixed  $actual   Actual value.
	 */
	function cp_groups_assert_same( $label, $expected, $actual ) {
		if ( $expected === $actual ) {
			echo "PASS  {$label}\n";
			return;
		}

		$GLOBALS['cp_groups_failures']++;
		echo "FAIL  {$label}\n";
		echo '      expected: ' . var_export( $expected, true ) . "\n";
		echo '      actual:   ' . var_export( $actual, true ) . "\n";
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	function cp_groups_leader_entry( $post_id ) {
		$leaders = get_post_meta( $post_id, 'leaders', true );

		if ( ! is_array( $leaders ) || ! isset( $leaders[0] ) ) {
			return array();
		}

		return $leaders[0];
	}

	require dirname( __DIR__ ) . '/includes/Migrator.php';

	$ada_id = 42;
	$GLOBALS['cp_groups_users']['ada@example.com'] = (object) array( 'ID' => $ada_id );

	$already = array(
		array(
			'id'    => 7,
			'name'  => 'Existing Leader',
			'email' => 'existing@example.com',
		),
		array(
			'id'    => '',
			'name'  => 'Second Leader',
			'email' => 'second@example.com',
		),
	);

	cp_groups_add_post( 1, 'cp_group', array(
		'leader'       => '  Ada Lovelace  ',
		'leader_email' => ' ada@example.com ',
	) );
	cp_groups_add_post( 2, 'cp_group', array(
		'leader' => 'Grace Hopper',
	) );
	cp_groups_add_post( 3, 'cp_group', array(
		'leader_email' => 'only@example.com',
	) );
	cp_groups_add_post( 4, 'cp_group', array(
		'leader'       => '   ',
		'leader_email' => '  ',
	) );
	cp_groups_add_post( 5, 'cp_group', array(
		'leaders'      => $already,
		'leader'       => 'Do Not Copy',
		'leader_email' => 'old@example.com',
	) );
	cp_groups_add_post( 6, 'cp_group', array(
		'leader_name'  => 'Katherine Johnson',
		'leader_email' => 'kj@example.com',
	) );
	cp_groups_add_post( 7, 'cp_group', array(
		'leader'       => 'Primary Name',
		'leader_name'  => 'Fallback Name',
		'leader_email' => 'primary@example.com',
	) );
	cp_groups_add_post( 8, 'page', array(
		'leader'       => 'Not A Group',
		'leader_email' => 'page@example.com',
	) );
	cp_groups_add_post( 9, 'cp_group', array(
		'leaders'      => array(),
		'leader'       => 'Filled Later',
		'leader_email' => 'filled@example.com',
	) );

	$migrator = new \CP_Groups\Migrator();
	$migrator->migrate_leader_data();

	cp_groups_assert_same(
		'leader and email',
		array(
			'id'    => $ada_id,
			'name'  => 'Ada Lovelace',
			'email' => 'ada@example.com',
		),
		cp_groups_leader_entry( 1 )
	);
	cp_groups_assert_same( 'leader and email keeps leader meta', '  Ada Lovelace  ', get_post_meta( 1, 'leader', true ) );
	cp_groups_assert_same( 'leader and email keeps email meta', ' ada@example.com ', get_post_meta( 1, 'leader_email', true ) );
	cp_groups_assert_same( 'leader and email stores one entry', 1, count( get_post_meta( 1, 'leaders', true ) ) );

	cp_groups_assert_same(
		'leader only',
		array(
			'id'    => '',
			'name'  => 'Grace Hopper',
			'email' => '',
		),
		cp_groups_leader_entry( 2 )
	);

	cp_groups_assert_same(
		'email only',
		array(
			'id'    => '',
			'name'  => '',
			'email' => 'only@example.com',
		),
		cp_groups_leader_entry( 3 )
	);

	cp_groups_assert_same( 'neither leaves leaders unset', '', get_post_meta( 4, 'leaders', true ) );
	cp_groups_assert_same( 'neither keeps blank leader meta', '   ', get_post_meta( 4, 'leader', true ) );

	cp_groups_assert_same( 'already migrated list is unchanged', $already, get_post_meta( 5, 'leaders', true ) );
	cp_groups_assert_same( 'already migrated keeps legacy name', 'Do Not Copy', get_post_meta( 5, 'leader', true ) );

	cp_groups_assert_same(
		'leader_name fallback',
		array(
			'id'    => '',
			'name'  => 'Katherine Johnson',
			'email' => 'kj@example.com',
		),
		cp_groups_leader_entry( 6 )
	);
	cp_groups_assert_same( 'leader_name meta is kept', 'Katherine Johnson', get_post_meta( 6, 'leader_name', true ) );

	cp_groups_assert_same( 'leader wins over leader_name', 'Primary Name', cp_groups_leader_entry( 7 )['name'] );
	cp_groups_assert_same( 'other post types are skipped', '', get_post_meta( 8, 'leaders', true ) );
	cp_groups_assert_same( 'other post types keep leader meta', 'Not A Group', get_post_meta( 8, 'leader', true ) );

	cp_groups_assert_same(
		'empty leaders list is filled from legacy meta',
		array(
			'id'    => '',
			'name'  => 'Filled Later',
			'email' => 'filled@example.com',
		),
		cp_groups_leader_entry( 9 )
	);

	cp_groups_assert_same( 'legacy meta is not deleted', array(), $GLOBALS['cp_groups_deletes'] );
	cp_groups_assert_same(
		'first run writes only groups that need a list',
		array( 1, 2, 3, 6, 7, 9 ),
		array_map(
			function ( $update ) {
				return $update[0];
			},
			$GLOBALS['cp_groups_updates']
		)
	);

	$snapshot     = unserialize( serialize( $GLOBALS['cp_groups_meta'] ) );
	$update_count = count( $GLOBALS['cp_groups_updates'] );

	$migrator->migrate_leader_data();

	cp_groups_assert_same( 'second run does not change meta', $snapshot, $GLOBALS['cp_groups_meta'] );
	cp_groups_assert_same( 'second run does not write again', $update_count, count( $GLOBALS['cp_groups_updates'] ) );
	cp_groups_assert_same( 'second run still has one leader for the named group', 1, count( get_post_meta( 1, 'leaders', true ) ) );
	cp_groups_assert_same( 'second run keeps the existing list at two leaders', 2, count( get_post_meta( 5, 'leaders', true ) ) );
	cp_groups_assert_same( 'second run still does not delete meta', array(), $GLOBALS['cp_groups_deletes'] );

	if ( $GLOBALS['cp_groups_failures'] > 0 ) {
		echo "\n{$GLOBALS['cp_groups_failures']} check(s) failed.\n";
		exit( 1 );
	}

	echo "\nAll leader migration checks passed.\n";
	exit( 0 );
}
