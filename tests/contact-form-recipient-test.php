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

	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}

	function esc_url( $url ) {
		return (string) $url;
	}

	function _e( $text, $domain = 'default' ) {
		echo $text;
	}

	function admin_url( $path = '', $scheme = 'admin' ) {
		return 'https://groups.test/wp-admin/' . ltrim( (string) $path, '/' );
	}

	function add_query_arg( $key, $value = '', $url = '' ) {
		if ( ! is_string( $key ) ) {
			return (string) $url;
		}

		$separator = str_contains( (string) $url, '?' ) ? '&' : '?';

		return $url . $separator . rawurlencode( $key ) . '=' . rawurlencode( (string) $value );
	}

	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		$html = '<input type="hidden" name="' . esc_attr( $name ) . '" value="test-nonce" />';

		if ( $echo ) {
			echo $html;
		}

		return $html;
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

	function cp_groups_test_filled_honeypot_still_sends() {
		cp_groups_test_add_post(
			10,
			'cp_group',
			'publish',
			array(
				'leader_email' => 'leader@groups.test',
			)
		);
		$GLOBALS['cp_groups_test_settings']['enable_honeypot'] = 'off';

		$response = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'email-verify' => 'visitor@example.com',
				)
			)
		);

		cp_groups_test_assert( true === $response->ok, 'Expected a filled honeypot field to still send' );
		cp_groups_test_assert( 1 === count( $GLOBALS['cp_groups_test_mail'] ), 'Expected one message' );
		cp_groups_test_assert( 'leader@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Expected the stored leader address' );
	}

	function cp_groups_test_honeypot_setting_turned_on() {
		cp_groups_test_add_post(
			10,
			'cp_group',
			'publish',
			array(
				'leader_email' => 'leader@groups.test',
			)
		);
		$GLOBALS['cp_groups_test_settings']['enable_honeypot'] = 'on';

		$filled = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'email-verify' => 'visitor@example.com',
					'email-to'     => 'other@elsewhere.test',
				)
			)
		);

		cp_groups_test_assert( false === $filled->ok, 'Expected a filled honeypot field to be rejected when the setting is on' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Expected no message when the honeypot setting is on' );

		$empty = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'email-verify' => '',
					'email-to'     => 'other@elsewhere.test',
				)
			)
		);

		cp_groups_test_assert( true === $empty->ok, 'Expected an empty honeypot field to still send when the setting is on' );
		cp_groups_test_assert( 'leader@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Expected the stored leader address' );
		cp_groups_test_assert( 'other@elsewhere.test' !== $GLOBALS['cp_groups_test_mail'][0]['to'], 'Posted address was used' );
	}

	function cp_groups_test_group_addresses() {
		return array(
			'leader_email'     => 'leader@groups.test',
			'action_contact'   => 'contact@groups.test',
			'registration_url' => 'register@groups.test',
		);
	}

	function cp_groups_test_empty_contact_with_matching_email_to() {
		cp_groups_test_add_post( 10, 'cp_group', 'publish', cp_groups_test_group_addresses() );

		foreach ( cp_groups_test_group_addresses() as $email ) {
			$response = cp_groups_test_send(
				cp_groups_test_fields(
					array(
						'contact'  => '',
						'email-to' => $email,
					)
				)
			);

			cp_groups_test_assert( true === $response->ok, "Expected a matching stored address {$email} to send" );
			cp_groups_test_assert( $email === $GLOBALS['cp_groups_test_mail'][0]['to'], "Expected the stored address {$email}" );
		}

		cp_groups_test_add_post( 11, 'cp_group', 'draft', cp_groups_test_group_addresses() );
		$draft = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'group-id' => 11,
					'contact'  => '',
					'email-to' => 'register@groups.test',
				)
			)
		);

		cp_groups_test_assert( false === $draft->ok, 'Expected an unpublished group to be rejected' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Expected no message for an unpublished group' );
	}

	function cp_groups_test_empty_contact_with_non_matching_email_to() {
		cp_groups_test_add_post( 10, 'cp_group', 'publish', cp_groups_test_group_addresses() );

		$posted   = 'other@elsewhere.test';
		$response = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'contact'  => '',
					'email-to' => $posted,
				)
			)
		);

		cp_groups_test_assert( true === $response->ok, 'Expected the leader address when the posted address does not match' );
		cp_groups_test_assert( 'leader@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Expected the stored leader address' );
		cp_groups_test_assert( $posted !== $GLOBALS['cp_groups_test_mail'][0]['to'], 'Posted address was used' );

		$GLOBALS['cp_groups_test_meta'][10]['leader_email'] = 'not-an-email';
		$fallback = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'contact'  => '',
					'email-to' => $posted,
				)
			)
		);

		cp_groups_test_assert( true === $fallback->ok, 'Expected the contact address when the leader address is not usable' );
		cp_groups_test_assert( 'contact@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Expected the stored contact address' );
		cp_groups_test_assert( $posted !== $GLOBALS['cp_groups_test_mail'][0]['to'], 'Posted address was used' );

		$unknown = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'contact'  => 'website',
					'email-to' => 'contact@groups.test',
				)
			)
		);

		cp_groups_test_assert( false === $unknown->ok, 'Expected an unknown contact to be rejected' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Expected no message for an unknown contact' );
	}

	function cp_groups_test_empty_contact_without_email_to() {
		cp_groups_test_add_post( 10, 'cp_group', 'publish', cp_groups_test_group_addresses() );

		$fields = cp_groups_test_fields( array( 'contact' => '' ) );
		unset( $fields['email-to'] );

		$response = cp_groups_test_send( $fields );

		cp_groups_test_assert( true === $response->ok, 'Expected the leader address when no address is posted' );
		cp_groups_test_assert( 'leader@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Expected the stored leader address' );

		$GLOBALS['cp_groups_test_meta'][10]['leader_email'] = '';
		$contact = cp_groups_test_send( $fields );

		cp_groups_test_assert( true === $contact->ok, 'Expected the contact address when the leader address is empty' );
		cp_groups_test_assert( 'contact@groups.test' === $GLOBALS['cp_groups_test_mail'][0]['to'], 'Expected the stored contact address' );

		$GLOBALS['cp_groups_test_meta'][10]['action_contact'] = '';
		$none = cp_groups_test_send( $fields );

		cp_groups_test_assert( false === $none->ok, 'Expected no send when the group has no stored address' );
		cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Expected no message without a stored address' );
	}

	function cp_groups_test_missing_group_id_asks_to_refresh() {
		$without_id = cp_groups_test_fields();
		unset( $without_id['group-id'] );

		$requests = array(
			$without_id,
			cp_groups_test_fields( array( 'group-id' => '' ) ),
			cp_groups_test_fields( array( 'group-id' => '0' ) ),
		);

		foreach ( $requests as $request ) {
			$response = cp_groups_test_send( $request );

			cp_groups_test_assert( false === $response->ok, 'Expected a request without a group id to be rejected' );
			cp_groups_test_assert(
				isset( $response->data['error'] ) && 'Please refresh the page and try again.' === $response->data['error'],
				'Expected the refresh message'
			);
			cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], 'Expected no message without a group id' );
		}
	}

	function cp_groups_test_input_value( $html, $name ) {
		$pattern = '/name="' . preg_quote( $name, '/' ) . '" value="([^"]*)"/';

		if ( ! preg_match( $pattern, $html, $matches ) ) {
			throw new RuntimeException( "Missing {$name} field" );
		}

		return $matches[1];
	}

	function cp_groups_test_render_modal( $name, $email, $title, $id ) {
		$init = ( new ReflectionClass( \CP_Groups\Init::class ) )->newInstanceWithoutConstructor();

		ob_start();
		$init->build_email_modal( $name, $email, $title, $id );

		return ob_get_clean();
	}

	function cp_groups_test_four_arg_modal_uses_stored_contact() {
		cp_groups_test_add_post( 10, 'cp_group', 'publish', cp_groups_test_group_addresses() );

		$cases = array(
			array( 'action_contact', 'leader@groups.test', 'leader' ),
			array( 'action_contact', 'contact@groups.test', 'contact' ),
			array( 'action_register', 'register@groups.test', 'register' ),
		);

		foreach ( $cases as $case ) {
			list( $name, $email, $contact ) = $case;
			$html = cp_groups_test_render_modal( $name, $email, 'Group', 10 );

			cp_groups_test_assert( $contact === cp_groups_test_input_value( $html, 'contact' ), "Expected the rendered contact to be {$contact}" );
			cp_groups_test_assert( '10' === cp_groups_test_input_value( $html, 'group-id' ), 'Expected the rendered group id' );
			cp_groups_test_assert( false === str_contains( $html, 'name="email-to"' ), 'Expected the modal not to post an address' );

			$fields = cp_groups_test_fields(
				array(
					'group-id' => cp_groups_test_input_value( $html, 'group-id' ),
					'contact'  => cp_groups_test_input_value( $html, 'contact' ),
				)
			);
			unset( $fields['email-to'] );

			$response = cp_groups_test_send( $fields );

			cp_groups_test_assert( true === $response->ok, "Expected {$contact} to send" );
			cp_groups_test_assert( $email === $GLOBALS['cp_groups_test_mail'][0]['to'], "Expected {$contact} to use {$email}" );
			cp_groups_test_assert( 'leader@groups.test' === $email || 'leader@groups.test' !== $GLOBALS['cp_groups_test_mail'][0]['to'], 'Register was sent to the leader address' );
		}
	}

	function cp_groups_test_error_message( $response ) {
		return isset( $response->data['error'] ) ? $response->data['error'] : '';
	}

	function cp_groups_test_changed_contact_message() {
		cp_groups_test_add_post(
			10,
			'cp_group',
			'publish',
			array(
				'leader_email'     => 'leader@groups.test',
				'action_contact'   => '',
				'registration_url' => '',
			)
		);

		$changed = "Sorry, this message couldn't be sent. This group's contact details have changed. Please contact the church directly.";
		$reload  = 'Something went wrong. Please reload the page and try again.';
		$refresh = 'Please refresh the page and try again.';

		foreach ( array( 'leader', 'contact', 'register' ) as $contact ) {
			if ( 'leader' === $contact ) {
				$GLOBALS['cp_groups_test_meta'][10]['leader_email'] = '';
			}

			$response = cp_groups_test_send(
				cp_groups_test_fields(
					array(
						'contact' => $contact,
					)
				)
			);

			cp_groups_test_assert( false === $response->ok, "Expected {$contact} without a stored address to be rejected" );
			cp_groups_test_assert( $changed === cp_groups_test_error_message( $response ), "Expected the changed-details message for {$contact}" );
			cp_groups_test_assert( array() === $GLOBALS['cp_groups_test_mail'], "Expected no message for {$contact}" );
		}

		$GLOBALS['cp_groups_test_meta'][10]['leader_email'] = 'leader@groups.test';

		$missing = cp_groups_test_fields( array( 'contact' => 'leader' ) );
		unset( $missing['group-id'] );
		$missing_response = cp_groups_test_send( $missing );
		cp_groups_test_assert( $refresh === cp_groups_test_error_message( $missing_response ), 'Expected the refresh message when the group id is missing' );

		$zero = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'group-id' => '0',
					'contact'  => 'leader',
				)
			)
		);
		cp_groups_test_assert( $refresh === cp_groups_test_error_message( $zero ), 'Expected the refresh message when the group id is zero' );

		$unknown = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'contact' => 'website',
				)
			)
		);
		cp_groups_test_assert( $reload === cp_groups_test_error_message( $unknown ), 'Expected the reload message for an unknown contact' );
		cp_groups_test_assert( $changed !== cp_groups_test_error_message( $unknown ), 'Unknown contact used the changed-details message' );

		cp_groups_test_add_post( 11, 'cp_group', 'draft', array( 'leader_email' => 'leader@groups.test' ) );
		$draft = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'group-id' => 11,
					'contact'  => 'leader',
				)
			)
		);
		cp_groups_test_assert( $reload === cp_groups_test_error_message( $draft ), 'Expected the reload message for an unpublished group' );

		cp_groups_test_add_post( 12, 'post', 'publish', array( 'leader_email' => 'leader@groups.test' ) );
		$other = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'group-id' => 12,
					'contact'  => 'leader',
				)
			)
		);
		cp_groups_test_assert( $reload === cp_groups_test_error_message( $other ), 'Expected the reload message for a non-group id' );

		$nonce = cp_groups_test_send(
			cp_groups_test_fields(
				array(
					'cp_send_email_nonce' => 'wrong-nonce',
					'contact'             => 'register',
				)
			)
		);
		cp_groups_test_assert( $reload === cp_groups_test_error_message( $nonce ), 'Expected the reload message when the nonce does not match' );
		cp_groups_test_assert( $changed !== cp_groups_test_error_message( $nonce ), 'Nonce failure used the changed-details message' );

		$empty_contact = cp_groups_test_fields( array( 'contact' => '' ) );
		unset( $empty_contact['email-to'] );
		$GLOBALS['cp_groups_test_meta'][10]['leader_email']   = '';
		$GLOBALS['cp_groups_test_meta'][10]['action_contact'] = '';
		$empty = cp_groups_test_send( $empty_contact );
		cp_groups_test_assert( $reload === cp_groups_test_error_message( $empty ), 'Expected the reload message when contact is empty and no address is stored' );
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
		'filled honeypot field still sends'    => 'cp_groups_test_filled_honeypot_still_sends',
		'honeypot setting turned on'         => 'cp_groups_test_honeypot_setting_turned_on',
		'empty contact with matching email-to' => 'cp_groups_test_empty_contact_with_matching_email_to',
		'empty contact with non-matching email-to' => 'cp_groups_test_empty_contact_with_non_matching_email_to',
		'empty contact with no email-to'     => 'cp_groups_test_empty_contact_without_email_to',
		'four argument modal uses stored contact' => 'cp_groups_test_four_arg_modal_uses_stored_contact',
		'changed contact details message'    => 'cp_groups_test_changed_contact_message',
		'request without a group id asks to refresh' => 'cp_groups_test_missing_group_id_asks_to_refresh',
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
