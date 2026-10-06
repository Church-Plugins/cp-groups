<?php
/**
 * Contact form recipient is resolved from the group record.
 *
 * Run: php tests/contact-form-recipient-test.php
 */

namespace ChurchPlugins {

	class Helpers {
		public static function get_post( $key, $default = '' ) {
			return array_key_exists( $key, $_POST ) ? $_POST[ $key ] : $default;
		}
	}
}

namespace CP_Groups\Admin {

	class Settings {
		public static function get_advanced( $key, $default = '' ) {
			if ( isset( $GLOBALS['cp_groups_test_settings'] ) && array_key_exists( $key, $GLOBALS['cp_groups_test_settings'] ) ) {
				return $GLOBALS['cp_groups_test_settings'][ $key ];
			}

			return $default;
		}
	}
}

namespace {

	class CP_Groups_Test_Response extends \Exception {
		public $ok;
		public $data;

		public function __construct( $ok, $data ) {
			parent::__construct( $ok ? 'success' : 'error' );
			$this->ok   = $ok;
			$this->data = $data;
		}
	}

	function cp_groups_test_post_field( $post, $field ) {
		$id = is_object( $post ) ? (int) $post->ID : (int) $post;

		if ( ! isset( $GLOBALS['cp_groups_test_posts'][ $id ] ) ) {
			return false;
		}

		return $GLOBALS['cp_groups_test_posts'][ $id ]->$field;
	}

	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}

	function is_email( $email ) {
		if ( ! is_string( $email ) ) {
			return false;
		}

		$valid = filter_var( $email, FILTER_VALIDATE_EMAIL );

		return $valid ? $email : false;
	}

	function get_post_type( $post = null ) {
		return cp_groups_test_post_field( $post, 'post_type' );
	}

	function get_post_status( $post = null ) {
		return cp_groups_test_post_field( $post, 'post_status' );
	}

	function get_post_meta( $post_id, $key = '', $single = false ) {
		$id = (int) $post_id;

		if ( ! isset( $GLOBALS['cp_groups_test_meta'][ $id ][ $key ] ) ) {
			return $single ? '' : array();
		}

		return $GLOBALS['cp_groups_test_meta'][ $id ][ $key ];
	}

	function wp_verify_nonce( $nonce, $action = -1 ) {
		$GLOBALS['cp_groups_test_nonce_ok'] = ( 'test-nonce' === $nonce && 'cp_send_email' === $action );

		return $GLOBALS['cp_groups_test_nonce_ok'];
	}

	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		$GLOBALS['cp_groups_test_mail'][] = array(
			'to'      => $to,
			'subject' => $subject,
			'message' => $message,
			'headers' => $headers,
		);

		return true;
	}

	function wp_send_json_success( $data = null ) {
		throw new CP_Groups_Test_Response( true, $data );
	}

	function wp_send_json_error( $data = null ) {
		throw new CP_Groups_Test_Response( false, $data );
	}

	function __( $text, $domain = 'default' ) {
		return $text;
	}

	function apply_filters( $hook, $value, ...$args ) {
		return $value;
	}

	function get_the_title( $post = 0 ) {
		return 'Group ' . $post;
	}

	function get_bloginfo( $show = '', $filter = 'raw' ) {
		if ( 'admin_email' === $show ) {
			return 'admin@groups.test';
		}

		if ( 'name' === $show ) {
			return 'Groups';
		}

		return '';
	}

	function site_url( $path = '', $scheme = null ) {
		return 'https://groups.test';
	}

	function wpautop( $text, $br = true ) {
		return $text;
	}

	require dirname( __DIR__ ) . '/includes/Init.php';

	function cp_groups_test_reset() {
		$GLOBALS['cp_groups_test_posts']    = array();
		$GLOBALS['cp_groups_test_meta']     = array();
		$GLOBALS['cp_groups_test_mail']     = array();
		$GLOBALS['cp_groups_test_settings'] = array(
			'enable_honeypot' => 'off',
		);
		$GLOBALS['cp_groups_test_nonce_ok'] = false;
		$_POST                              = array();
		$_REQUEST                           = array();
	}

	function cp_groups_test_add_post( $id, $type, $status, $meta = array() ) {
		$GLOBALS['cp_groups_test_posts'][ $id ] = (object) array(
			'ID'          => $id,
			'post_type'   => $type,
			'post_status' => $status,
		);
		$GLOBALS['cp_groups_test_meta'][ $id ]  = $meta;
	}

	function cp_groups_test_fields( $extra = array() ) {
		return array_merge(
			array(
				'cp_send_email_nonce' => 'test-nonce',
				'from-name'           => 'Ada Lovelace',
				'email-from'          => 'ada@example.com',
				'subject'             => 'Hello',
				'message'             => 'Can I join?',
				'email-verify'        => '',
				'group-id'            => 10,
				'contact'             => 'leader',
			),
			$extra
		);
	}

	function cp_groups_test_send( $post ) {
		$_POST    = $post;
		$_REQUEST = $post;
		$GLOBALS['cp_groups_test_mail'] = array();

		$init = ( new ReflectionClass( \CP_Groups\Init::class ) )->newInstanceWithoutConstructor();

		try {
			$init->maybe_send_email();
		} catch ( CP_Groups_Test_Response $response ) {
			return $response;
		}

		throw new RuntimeException( 'Handler did not respond' );
	}

	function cp_groups_test_assert( $condition, $message ) {
		if ( ! $condition ) {
			throw new RuntimeException( $message );
		}
	}

	function cp_groups_test_request_recipient_is_ignored() {
		cp_groups_test_add_post(
			10,
			'cp_group',
			'publish',
			array(
				'leader_email'     => 'leader@groups.test',
				'action_contact'   => 'contact@groups.test',
				'registration_url' => 'register@groups.test',
			)
		);

		$requested = 'other@elsewhere.test';
		$response  = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'email-to' => $requested,
				)
			)
		);

		cp_groups_test_assert( true === $response->ok, 'Expected the stored leader address to be used' );
		cp_groups_test_assert( true === $GLOBALS['cp_groups_test_nonce_ok'], 'Expected the nonce check to pass' );
		cp_groups_test_assert( 1 === count( $GLOBALS['cp_groups_test_mail'] ), 'Expected one message' );
		cp_groups_test_assert( 'leader@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Message recipient was not the stored leader address' );
		cp_groups_test_assert( $requested !== $GLOBALS['cp_groups_test_mail'][0]['to'], 'Request recipient was used' );

		$rejected = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'email-to' => $requested,
					'contact'  => '',
				)
			)
		);

		cp_groups_test_assert( false === $rejected->ok, 'Expected a request without a stored contact to be rejected' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'A request recipient was sent without a stored contact' );

		$GLOBALS['cp_groups_test_meta'][10]['leader_email'] = 'not-an-email';
		$invalid = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'email-to' => $requested,
					'contact'  => 'leader',
				)
			)
		);

		cp_groups_test_assert( false === $invalid->ok, 'Expected an invalid stored address to be rejected' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Request recipient was used when the stored address was invalid' );
	}

	function cp_groups_test_published_group_sends_to_stored_email() {
		cp_groups_test_add_post(
			10,
			'cp_group',
			'publish',
			array(
				'leader_email'     => 'leader@groups.test',
				'action_contact'   => 'contact@groups.test',
				'registration_url' => 'register@groups.test',
			)
		);

		$expected = array(
			'leader'   => 'leader@groups.test',
			'contact'  => 'contact@groups.test',
			'register' => 'register@groups.test',
		);

		foreach ( $expected as $contact => $email ) {
			$response = cp_groups_test_send(
				cp_groups_test_fields(
					array(
						'contact'  => $contact,
						'email-to' => 'other@elsewhere.test',
					)
				)
			);

			cp_groups_test_assert( true === $response->ok, "Expected a published group to send for {$contact}" );
			cp_groups_test_assert( $email === $GLOBALS['cp_groups_test_mail'][0]['to'], "Expected the stored {$contact} address" );
		}
	}

	function cp_groups_test_unpublished_or_non_group_is_rejected() {
		$stored = array(
			'leader_email'     => 'leader@groups.test',
			'action_contact'   => 'contact@groups.test',
			'registration_url' => 'register@groups.test',
		);

		$cases = array(
			array( 11, 'cp_group', 'draft', 'unpublished group' ),
			array( 12, 'cp_group', 'pending', 'unpublished group' ),
			array( 13, 'cp_group', 'private', 'unpublished group' ),
			array( 14, 'post', 'publish', 'non-group id' ),
		);

		foreach ( $cases as $case ) {
			list( $id, $type, $status, $label ) = $case;
			cp_groups_test_add_post( $id, $type, $status, $stored );

			$response = cp_groups_test_send(
				cp_groups_test_fields(
					array(
						'group-id' => $id,
						'email-to' => 'other@elsewhere.test',
					)
				)
			);

			cp_groups_test_assert( false === $response->ok, "Expected {$label} {$id} to be rejected" );
			cp_groups_test_assert( true === $GLOBALS['cp_groups_test_nonce_ok'], "Expected the nonce check to pass for {$label} {$id}" );
			cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], "Expected no message for {$label} {$id}" );
		}

		$missing = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'group-id' => 999,
					'email-to' => 'other@elsewhere.test',
				)
			)
		);

		cp_groups_test_assert( false === $missing->ok, 'Expected a missing group id to be rejected' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Expected no message for a missing group id' );
	}

	set_error_handler(
		function ( $severity, $message, $file, $line ) {
			if ( ! ( error_reporting() & $severity ) ) {
				return false;
			}

			throw new ErrorException( $message, 0, $severity, $file, $line );
		}
	);

	$tests  = array(
		'request recipient is ignored'        => 'cp_groups_test_request_recipient_is_ignored',
		'published group sends to stored email' => 'cp_groups_test_published_group_sends_to_stored_email',
		'unpublished or non-group id is rejected' => 'cp_groups_test_unpublished_or_non_group_is_rejected',
	);
	$failed = 0;

	foreach ( $tests as $name => $fn ) {
		try {
			cp_groups_test_reset();
			$fn();
			echo "PASS {$name}\n";
		} catch ( Throwable $e ) {
			$failed++;
			echo "FAIL {$name}: {$e->getMessage()}\n";
		}
	}

	if ( $failed ) {
		echo "{$failed} failed\n";
		exit( 1 );
	}

	echo "All contact form recipient tests passed\n";
	exit( 0 );
}
