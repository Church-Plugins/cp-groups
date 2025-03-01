<?php
namespace CP_Groups;

use CP_Groups\Admin\Settings;
use CP_Groups\Ratelimit;
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

	/**
	 * @var Integrations\_Init
	 */
	public $integrations;

	/**
	 * @var Templates
	 */
	public $templates;

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
		add_action( 'cp_core_loaded', [ $this, 'maybe_setup' ], - 9999 );
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

		wp_enqueue_style( 'material-icons' );

		if( Settings::get_advanced( 'enable_captcha', 'on' ) == 'on' ) {
			$site_key = Settings::get_advanced( 'captcha_site_key', '' );
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
		$this->templates = Templates::get_instance();
		Admin\Init::get_instance();
		$this->integrations = Integrations\_Init::get_instance();
		$this->setup = Setup\Init::get_instance();
	}

	protected function actions() {
		add_action( 'cp_send_email', [ $this, 'maybe_send_email' ] );
		add_filter( 'cp_resources_output_resources_check_object', [ $this, 'allow_resources_for_group_modals' ], 50, 1 );
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
	public function build_email_modal( string $name, string $email, string $title, int $id = 0 ) {
		$is_hidden_att = Settings::get_advanced( 'show_leader_email', 'off' ) == 'on' ? '' : 'hidden';
		?>
		<div class='cp-email-modal <?php echo esc_attr( $name ) ?>'>
			<button class='cp-back-btn cp-button is-small is-text'>&larr; <?php _e( 'Back', 'cp-groups' ); ?></button>
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
						<input type="email" name="email-from" class="cp-email-from" />
					</label>
				</div>

				<div class='cp-email-form--email-verify'>
					<label>
						<?php _e( 'Email Verify', 'cp-groups' ) ?>
						<input type='text' name='email-verify' autocomplete="do-not-autofill" tabindex="-1" id="verify-<?php echo time(); ?>">
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

				<input type="hidden" name="group-id" value="<?php echo absint( $id ); ?>" />
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
		$group_id = \ChurchPlugins\Helpers::get_post( 'group-id' );
		$email_to = \ChurchPlugins\Helpers::get_post( 'email-to' );
		$reply_to = \ChurchPlugins\Helpers::get_post( 'email-from' );
		$honeypot = \ChurchPlugins\Helpers::get_post( 'email-verify' );
		$name     = \ChurchPlugins\Helpers::get_post( 'from-name' );
		$subject  = \ChurchPlugins\Helpers::get_post( 'subject' );
		$message  = \ChurchPlugins\Helpers::get_post( 'message' );
		$limit    = intval( Settings::get_advanced( 'throttle_amount', 3 ) );


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

		if( ! empty( $honeypot ) && Settings::get_advanced( 'enable_honeypot', 'off' ) === 'on' ) {
			wp_send_json_error( array( 'error' => __( 'Blocked for suspicious activity', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( empty( $subject ) ) {
			wp_send_json_error( array( 'error' => __( 'Please add an Email Subject.', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( empty( $message ) ) {
			wp_send_json_error( array( 'error' => __( 'Please add an Email Message.', 'church-plugins' ), 'request' => $_REQUEST ) );
		}

		if( $this->is_address_blocked( $reply_to ) ) {
			wp_send_json_error( array( 'error' => __( 'You are not allowed to send a message as a group leader.', 'cp-groups' ), 'request' => $_REQUEST ) );
		}

		if( ! $this->is_verified_captcha() ) {
			wp_send_json_error( array( 'error' => __( 'Your captcha score is too low', 'cp-groups' ), 'request' => $_REQUEST ) );
		}

		$subject = apply_filters( 'cp_groups_email_subject', __( '[Web Inquiry]', 'cp-groups' ) . ' ' . $subject, $subject );

		$message_suffix = apply_filters( 'cp_groups_email_message_suffix', '<br /><br />-<br />' . sprintf( __( 'Submitted by %s via Group Contact Form from "%s". Simply click Reply to respond to them directly.', 'cp-groups' ), $name, get_the_title( $group_id ) ), $group_id );
		$message        = apply_filters( 'cp_groups_email_message', $message . $message_suffix, $group_id );

		$from_email = Settings::get_advanced( 'from_email', get_bloginfo( 'admin_email' ) );
		$from_name  = Settings::get_advanced( 'from_name', get_bloginfo( 'name' ) );

		$cc   = [ Settings::get_advanced( 'ccd' ) ];
		$cc[] = get_post_meta( $group_id, 'cc', true );
		$cc   = implode( ',', array_filter( $cc ) );

		$cc  = apply_filters( 'cp_groups_email_cc', $cc, $group_id );
		$bcc = apply_filters( 'cp_groups_email_bcc', Settings::get_advanced( 'bcc' ), $group_id );

		$headers = [
			'Content-Type: text/html; cahrset=UTF-8',
			"From: $from_name <$from_email>",
			sprintf( 'Reply-To: %s <%s>', $name, $reply_to )
		];

		if ( ! empty( $cc ) ) {
			$headers[] = 'Cc: ' . $cc;
		}

		if ( ! empty( $bcc ) ) {
			$headers[] = 'Bcc: ' . $bcc;
		}

		$headers = apply_filters( 'cp_groups_email_headers', $headers, $group_id );

		wp_mail( $email_to, stripslashes( $subject ), stripslashes( wpautop( $message ) ), $headers );

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
		if( Settings::get_advanced( 'throttle_emails', 'off' ) == 'off' ) {
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
		if( Settings::get_advanced( 'block_emails', 'on' ) == 'off' ) {
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
		$token      = \ChurchPlugins\Helpers::get_post( 'token' );
		$action     = \ChurchPlugins\Helpers::get_post( 'action' );
		$secret_key = Settings::get_advanced( 'captcha_secret_key', '' );

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
		return '1.1.3';
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

	/**
	 * CP Resources only appends resources onto single objects. This makes sure the resources are added to groups content in an archive context.
	 */
	public function allow_resources_for_group_modals( $check ) {
		if( get_post_type() === cp_groups()->setup->post_types->groups->post_type ) {
			return false;
		}
		return $check;
	}
}
