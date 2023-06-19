<?php
namespace CP_Groups;

use CP_Groups\Admin\Settings;
use CP_Groups\Ratelimit;
use ChurchPlugins\Helpers;
use RuntimeException;

/**
 * Provides the global $cp_groups object
 *
 * @author costmo
 */
class Init {

	// TODO: Add missing DocBlock comments for methods of this class

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * @var Setup\Init
	 */
	public $setup;

	public $enqueue;

	protected $limiter;
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
	 * Class constructor: Add Hooks and Actions
	 *
	 */
	protected function __construct() {
		$this->enqueue = new \WPackio\Enqueue( 'cpGroups', 'dist', $this->get_version(), 'plugin', CP_GROUPS_PLUGIN_FILE );
		$this->limiter = new Ratelimit( "send_group_email" );
		add_action( 'plugins_loaded', [ $this, 'maybe_setup' ], - 9999 );
		add_action( 'init', [ $this, 'maybe_init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'app_enqueue' ] );
	}

	/**
	 * Plugin setup entry hub
	 *
	 * @return void
	 */
	public function maybe_setup() {
		if ( ! $this->check_required_plugins() ) {
			return;
		}

		$this->includes();
		$this->actions();
	}

	/**
	 * Actions that must run through the `init` hook
	 *
	 * @return void
	 * @author costmo
	 */
	public function maybe_init() {
		if ( ! $this->check_required_plugins() ) {
			return;
		}
	}

	/**
	 * `wp_enqueue_scripts` actions for the app's compiled sources
	 *
	 * @return void
	 * @author costmo
	 */
	public function app_enqueue() {
		$this->enqueue->enqueue( 'styles', 'main', [ 'css_dep' => [] ] );
		$this->enqueue->enqueue( 'scripts', 'main', [ 'js_dep' => [ 'jquery', 'jquery-ui-dialog', 'jquery-form' ] ] );

		// loads main.js script without needing to build
		// $path = plugins_url( 'cp-groups/assets/js/main.js', 'cp-groups' );
		// wp_enqueue_script( 'cp-groups-some-script', $path, array( 'jquery', 'jquery-ui-dialog', 'jquery-form' ) );

		if( Settings::get( 'enable_captcha', 'on', 'cp_groups_contact_options' ) == 'on' ) {
			$site_key = Settings::get( 'captcha_site_key', '', 'cp_groups_contact_options' );
			if( ! empty( $site_key ) ) {
				wp_localize_script( 'grecaptcha-site-key', 'recaptchaSiteKey', $site_key );
				wp_enqueue_script( 'grecaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $site_key );
			}
		}
	}

	/**
	 * Includes
	 *
	 * @return void
	 */
	protected function includes() {
		Templates::init();
		Admin\Init::get_instance();
		$this->setup = Setup\Init::get_instance();
	}

	protected function actions() {
		add_action( 'cp_send_email', [ $this, 'maybe_send_email' ] );
	}

	/**
	 * Required Plugins notice
	 *
	 * @return void
	 */
	public function required_plugins() {
		printf( '<div class="error"><p>%s</p></div>', __( 'Your system does not meet the requirements for Church Plugins - Groups', 'cp-groups' ) );
	}

	/**
	 * Render email modal
	 *
	 * @param string $name
	 * @param string $email
	 * @param string $title
	 * @return void
	 * @author Jonathan Roley
	 */
	public function build_email_modal( string $name, string $email, string $title ) {
		$is_hidden_att = Settings::get( 'show_leader_email', 'off', 'cp_groups_contact_options' ) == 'on' ? '' : 'hidden';
		?>
		<div class='cp-email-modal <?php echo esc_attr( $name ) ?>'>
			<button class='cp-back-btn cp-button is-small is-transparent'>Back</button>
			<?php $this->get_modal_meta_tag( $name, $email, $title ) ?>
			<form
					action="<?php echo esc_url( add_query_arg( 'cp_action', 'cp_send_email', admin_url( 'admin-ajax.php' ) ) ); ?>"
					method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'cp_send_email', 'cp_send_email_nonce' ); ?>

				<div>
					<h4><?php _e( 'Send a message to ', 'cp-groups' ); ?><span class="reciever-name"><?php echo $title ?></span></h4>
				</div>

				<div <?php echo $is_hidden_att ?>>
					<label>
						<?php _e( 'To:', 'cp-groups' ); ?>
						<input type="hidden" name="email-to" class="email-to" />
						<input type="text" disabled="disabled" class="email-to" />
						<div class="group-copy-email" title="Copy email address">
							<span class="material-icons-outlined">content_copy</span>
						</div>
					</label>
				</div>

				<div class="cp-email-form--name">
					<label>
						<?php _e( 'Your Full Name:', 'cp-groups' ); ?>
						<input type="text" name="from-name" />
					</label>
				</div>

				<div class="cp-email-form--email-from">
					<label>
						<?php _e( 'Your Email:', 'cp-groups' ); ?>
						<input type="text" name="email-from" class="cp-email-from"/>
					</label>
				</div>

				<div class='cp-email-form--email-verify'>
					<label>
						<?php _e( 'Email Verify', 'cp-groups' ) ?>
						<input type='text' name='email-verify'>
					</label>
				</div>

				<div class="cp-email-form--subject">
					<label>
						<?php _e( 'Email Subject:', 'cp-groups' ); ?>
						<input type="text" name="subject"/>
					</label>
				</div>

				<div class="cp-email-form--message">
					<label>
						<?php _e( 'Email Message:', 'cp-groups' ); ?>
						<textarea name="message" rows="3"></textarea>
					</label>
				</div>

				<input class="cp-button is-large" type="submit" value="Send"/>

			</form>
		</div>
		<?php
	}

	/**
	 * Send an email message via AJAX request after validating input
	 *
	 * Responds to browser request with 200 for success or 503 on error. Script execution is halted without function return in either case.
	 *
	 * @return void
	 * @author Jonathan Roley
	 */
	public function maybe_send_email() {
		$email_to = Helpers::get_post( 'email-to' );
		$reply_to = Helpers::get_post( 'email-from' );
		$honeypot = Helpers::get_post( 'email-verify' );
		$name     = Helpers::get_post( 'from-name' );
		$subject  = Helpers::get_post( 'subject' );
		$message  = Helpers::get_post( 'message' );
		$limit    = intval( Settings::get( 'throttle_amount', 3, 'cp_groups_contact_options' ) );


		if( ! wp_verify_nonce( $_REQUEST['cp_send_email_nonce'], 'cp_send_email' ) || ! is_email( $email_to ) ) {
			wp_send_json_error( array( 'error' => __( 'Something went wrong. Please reload the page and try again.', 'church-plugins' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'error' => __( 'Please enter a your full name.', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if ( ! is_email ( $reply_to ) ) {
			wp_send_json_error( array( 'error' => __( 'Please enter a valid email address.', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( $this->check_if_ratelimited( $reply_to, $limit ) ) {
			wp_send_json_error( array( 'error' => __( "Daily send limit of {$limit} submissions exceeded - Message blocked. Please try again later.", 'church-plugins' ) ) );
		}

		if( ! empty( $honeypot ) ) {
			wp_send_json_error( array( 'error' => __( 'Blocked for suspicious activity', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( empty( $subject ) ) {
			wp_send_json_error( array( 'error' => __( 'Please add an Email Subject.', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( empty( $message ) ) {
			wp_send_json_error( array( 'error' => __( 'Please add an Email Message.', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( $this->is_address_blocked( $reply_to ) ) {
			wp_send_json_error( array( 'error' => __( 'You are not allowed to send a message as a staff member', 'cp-groups' ), 'request' => $_REQUEST ) );
		}

		if( ! $this->is_verified_captcha() ) {
			wp_send_json_error( array( 'error' => __( 'Your captcha score is too low', 'cp-groups' ), 'request' => $_REQUEST ) );
		}

		$subject = apply_filters( 'cp_staff_email_subject', __( '[Web Inquiry]', 'cp-groups' ) . ' ' . $subject, $subject );

		$message_suffix = apply_filters( 'cp_staff_email_message_suffix', '<br /><br />-<br />' . sprintf( __( 'Submitted by %s via Staff Web Inquiry form. Simply click Reply to respond to them directly.', 'cp-groups' ), $name ) );
		$message        = apply_filters( 'cp_staff_email_message', $message . $message_suffix );

		$from_email = Settings::get( 'from_email', get_bloginfo( 'admin_email' ), 'cp_groups_contact_options' );
		$from_name  = Settings::get( 'from_name', get_bloginfo( 'name' ), 'cp_groups_contact_options' );

		wp_mail( $email_to, stripslashes( $subject ), stripslashes( wpautop( $message ) ), [
			'Content-Type: text/html; cahrset=UTF-8',
			"From: $from_name <$from_email>",
			sprintf( 'Reply-To: %s <%s>', $name, $reply_to )
		] );

		wp_send_json_success( array( 'success' => __( 'Email sent!', 'church-plugins' ), 'request' => $_REQUEST ) );
	}


	/** Helper Methods **************************************/

	public function get_default_thumb() {
		return CP_GROUPS_PLUGIN_URL . '/app/public/logo512.png';
	}

	/**
	 * Determine if the current user has exceeded the number of responses allowed per day
	 *
	 * @since  1.0.2
	 *
	 * @param $email
	 * @param $limit
	 *
	 * @return bool
	 * @author Jonathan Roley, 6/6/23
	 */
	public function check_if_ratelimited( $email, $limit ) {
		if( Settings::get( 'throttle_staff_emails', 'off', 'cp_groups_contact_options' ) == 'off' ) {
			return false;
		}

		try {
			$this->limiter->add_entries(
				array(
					$_SERVER['REMOTE_ADDR'], // user IP address
					$email // sender email address
				),
				$limit
			);
			return false;
		}
		catch(RuntimeException $err) {
			return true;
		}
	}

	/**
		 * Determine if the provided address is restricted
		 *
		 * @since 1.0.2
		 *
		 * @param $email
		 *
		 * @return bool
		 * @author Jonathan Roley, 6/6/23
		 */
	public function is_address_blocked( $email ) {
		if( Settings::get( 'block_staff_emails', 'on', 'cp_groups_contact_options' ) == 'off' ) {
			return false;
		}

		$site_domain = explode( '//', site_url() )[1];

		return str_contains( $email, $site_domain );
	}

	/**
	 * Determine if the captcha is verified
	 *
	 * @since  1.0.2
	 *
	 * @return bool
	 * @author Jonathan Roley, 6/6/23
	 */
	public function is_verified_captcha() {
		$token      = Helpers::get_post( 'token' );
		$action     = Helpers::get_post( 'action' );
		$secret_key = Settings::get( 'captcha_secret_key', '', 'cp_groups_contact_options' );

		if( empty( $secret_key ) ) {
			return true;
		}

		$post_body = array(
			'secret'   => $secret_key,
			'response' => $token
		);

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify' );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post_body ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$response = json_decode( curl_exec( $ch ), true );
		curl_close( $ch );

		return $response['success'] == '1' && $response['action'] == $action && $response['score'] > 0.5;
	}

	/**
	 * Get meta tag for populating form data
	 *
	 * @since 1.0.2
	 *
	 * @author Jonathan Roley, 6/14/23
	 */
	public function get_modal_meta_tag( string $name, string $email, string $title ) {
		$email = base64_encode( $email );
		?>
		<meta
			itemprop="groupDetails"
			data-name="<?php echo esc_attr( $name ) ?>"
			data-email="<?php echo $email ?>"
			data-title="<?php echo esc_attr( $title ) ?>"
		>
		<?php
	}

	/**
	 * Make sure required plugins are active
	 *
	 * @return bool
	 */
	protected function check_required_plugins() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// @todo check for requirements before loading
		if ( 1 ) {
			return true;
		}

		add_action( 'admin_notices', array( $this, 'required_plugins' ) );

		return false;
	}

	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_support_url() {
		return 'https://churchplugins.com/support';
	}

	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0.0
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'Church Plugins - Groups', 'cp-groups' );
	}

	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0.0
	 * @return string the plugin name
	 */
	public function get_plugin_path() {
		return CP_GROUPS_PLUGIN_DIR;
	}

	/**
	 * Provide a unique ID tag for the plugin
	 *
	 * @return string
	 */
	public function get_id() {
		return 'cp-groups';
	}

	/**
	 * Provide a unique ID tag for the plugin
	 *
	 * @return string
	 */
	public function get_version() {
		return '0.0.1';
	}

	/**
	 * Get the API namespace to use
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_api_namespace() {
		return $this->get_id() . '/v1';
	}

	public function enabled() {
		return true;
	}

}
