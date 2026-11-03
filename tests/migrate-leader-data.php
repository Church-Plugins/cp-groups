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
 * It also checks the leader_name fallback, email checks, leader display, and that legacy meta is kept.
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

namespace ChurchPlugins\Controllers {

	if ( ! class_exists( 'ChurchPlugins\Controllers\Controller', false ) ) {
		class Controller {
			/**
			 * @var object
			 */
			public $post;

			/**
			 * @param int  $id         Post ID.
			 * @param bool $use_origin Unused. Matches the core constructor signature.
			 */
			public function __construct( $id, $use_origin = true ) {
				unset( $use_origin );

				$this->post = (object) array(
					'ID' => $id,
				);
			}

			/**
			 * @param mixed  $value    Value to return.
			 * @param string $function Caller name.
			 * @return mixed
			 */
			protected function filter( $value, $function ) {
				unset( $function );

				return $value;
			}
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
		if ( 'email' === $field ) {
			foreach ( $GLOBALS['cp_groups_users'] as $email => $user ) {
				if ( 0 === strcasecmp( (string) $email, (string) $value ) ) {
					return $user;
				}
			}

			return false;
		}

		if ( 'ID' === $field ) {
			foreach ( $GLOBALS['cp_groups_users'] as $user ) {
				if ( (int) $user->ID === (int) $value ) {
					return $user;
				}
			}
		}

		return false;
	}

	/**
	 * Subset of WordPress is_email(). Returns the email string when it is valid.
	 *
	 * @param string $email Email address.
	 * @return string|false
	 */
	function is_email( $email ) {
		if ( ! is_string( $email ) || strlen( $email ) < 6 || false === strpos( $email, '@', 1 ) ) {
			return false;
		}

		list( $local, $domain ) = explode( '@', $email, 2 );

		if ( ! preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) ) {
			return false;
		}

		if ( preg_match( '/\.{2,}/', $domain ) || trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
			return false;
		}

		$subs = explode( '.', $domain );

		if ( count( $subs ) < 2 ) {
			return false;
		}

		foreach ( $subs as $sub ) {
			if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub || ! preg_match( '/^[a-z0-9-]+$/i', $sub ) ) {
				return false;
			}
		}

		return $email;
	}

	/**
	 * @param mixed $value Value to cast.
	 * @return int
	 */
	function absint( $value ) {
		return abs( (int) $value );
	}

	/**
	 * @param string $email Email address.
	 * @return string
	 */
	function sanitize_email( $email ) {
		$email = trim( (string) $email );

		if ( '' === $email || ! is_email( $email ) ) {
			return '';
		}

		return $email;
	}

	/**
	 * @param string $value Text to clean.
	 * @return string
	 */
	function sanitize_text_field( $value ) {
		$value = strip_tags( (string) $value );
		$value = preg_replace( '/[\r\n\t ]+/', ' ', $value );

		return trim( $value );
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
	require dirname( __DIR__ ) . '/includes/Controllers/Group.php';

	$ada_id   = 42;
	$blank_id = 80;
	$case_id  = 77;

	$GLOBALS['cp_groups_users']['ada@example.com'] = (object) array(
		'ID'         => $ada_id,
		'first_name' => 'Ada',
		'last_name'  => 'Lovelace',
		'user_email' => 'ada@example.com',
	);
	$GLOBALS['cp_groups_users']['blank.name@example.com'] = (object) array(
		'ID'         => $blank_id,
		'first_name' => '',
		'last_name'  => '',
		'user_email' => 'blank.name@example.com',
	);
	$GLOBALS['cp_groups_users']['case.user@example.com'] = (object) array(
		'ID'         => $case_id,
		'first_name' => 'Profile',
		'last_name'  => 'Name',
		'user_email' => 'case.user@example.com',
	);
	$GLOBALS['cp_groups_users']['not-an-email'] = (object) array(
		'ID'         => 99,
		'first_name' => 'Should',
		'last_name'  => 'Not Match',
		'user_email' => 'not-an-email',
	);

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
	cp_groups_add_post( 10, 'cp_group', array(
		'leader'       => 'Stored Name',
		'leader_email' => 'blank.name@example.com',
	) );
	cp_groups_add_post( 11, 'cp_group', array(
		'leader' => '  <b>Grace</b> Hopper  ',
	) );
	cp_groups_add_post( 12, 'cp_group', array(
		'leader'       => 'Invalid Email',
		'leader_email' => 'not-an-email',
	) );
	cp_groups_add_post( 13, 'cp_group', array(
		'leader_email' => 'still-not-valid',
	) );
	cp_groups_add_post( 14, 'cp_group', array(
		'leader'       => 'Case Match',
		'leader_email' => 'Case.User@Example.com',
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

	cp_groups_assert_same(
		'case-mismatched email matches a user',
		array(
			'id'    => $case_id,
			'name'  => 'Case Match',
			'email' => 'Case.User@Example.com',
		),
		cp_groups_leader_entry( 14 )
	);

	cp_groups_assert_same(
		'invalid email is omitted when a name is present',
		array(
			'id'    => '',
			'name'  => 'Invalid Email',
			'email' => '',
		),
		cp_groups_leader_entry( 12 )
	);
	cp_groups_assert_same( 'invalid email meta is kept', 'not-an-email', get_post_meta( 12, 'leader_email', true ) );
	cp_groups_assert_same( 'invalid email and no name skips the leaders row', '', get_post_meta( 13, 'leaders', true ) );
	cp_groups_assert_same( 'invalid email and no name keeps email meta', 'still-not-valid', get_post_meta( 13, 'leader_email', true ) );

	cp_groups_assert_same( 'HTML in name is stored trimmed', '<b>Grace</b> Hopper', cp_groups_leader_entry( 11 )['name'] );

	cp_groups_assert_same( 'legacy meta is not deleted', array(), $GLOBALS['cp_groups_deletes'] );
	cp_groups_assert_same(
		'first run writes only groups that need a list',
		array( 1, 2, 3, 6, 7, 9, 10, 11, 12, 14 ),
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

	$blank_group = new \CP_Groups\Controllers\Group( 10 );
	cp_groups_assert_same(
		'blank user first and last name falls back to the stored name',
		array(
			array(
				'id'    => $blank_id,
				'name'  => 'Stored Name',
				'email' => 'blank.name@example.com',
			),
		),
		$blank_group->get_leaders()
	);

	$html_group = new \CP_Groups\Controllers\Group( 11 );
	cp_groups_assert_same(
		'HTML in name is plain text when displayed',
		array(
			array(
				'id'    => 0,
				'name'  => 'Grace Hopper',
				'email' => '',
			),
		),
		$html_group->get_leaders()
	);

	$case_group = new \CP_Groups\Controllers\Group( 14 );
	cp_groups_assert_same(
		'a user name still replaces the stored name',
		array(
			array(
				'id'    => $case_id,
				'name'  => 'Profile Name',
				'email' => 'case.user@example.com',
			),
		),
		$case_group->get_leaders()
	);

	if ( $GLOBALS['cp_groups_failures'] > 0 ) {
		echo "\n{$GLOBALS['cp_groups_failures']} check(s) failed.\n";
		exit( 1 );
	}

	echo "\nAll leader migration checks passed.\n";
	exit( 0 );
}
