<?php

namespace CP_Groups\Admin;

/**
 * Plugin settings
 *
 */
class Settings {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of \CP_Groups\Settings
	 *
	 * @return Settings
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Settings ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get a value from the options table
	 *
	 * @param $key
	 * @param $default
	 * @param $group
	 *
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public static function get( $key, $default = '', $group = 'cp_groups_main_options' ) {
		$options = get_option( $group, [] );

		if ( isset( $options[ $key ] ) ) {
			$value = $options[ $key ];
		} else {
			$value = $default;
		}

		return apply_filters( 'cp_groups_settings_get', $value, $key, $group );
	}

	public static function get_groups( $key, $default = '' ) {
		return self::get( $key, $default, 'cp_groups_group_options' );
	}

	/**
	 * Class constructor. Add admin hooks and actions
	 *
	 */
	protected function __construct() {
		add_action( 'cmb2_admin_init', [ $this, 'register_main_options_metabox' ] );
		add_action( 'cmb2_save_options_page_fields', 'flush_rewrite_rules' );
	}

	public function register_main_options_metabox() {

		$post_type = cp_groups()->setup->post_types->groups->post_type;
		/**
		 * Registers main options page menu item and form.
		 */
		$args = array(
			'id'           => 'cp_groups_main_options_page',
			'title'        => 'Settings',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cp_groups_main_options',
			'tab_group'    => 'cp_groups_main_options',
			'tab_title'    => 'Main',
			'parent_slug'  => 'edit.php?post_type=' . $post_type,
			'display_cb'   => [ $this, 'options_display_with_tabs'],
		);

		$main_options = new_cmb2_box( $args );

		$main_options->add_field( array(
			'name'         => __( 'Default Thumbnail', 'cp-groups' ),
			'desc'         => sprintf( __( 'The default thumbnail image to use for %s.', 'cp-groups' ), cp_groups()->setup->post_types->groups->plural_label ),
			'id'           => 'default_thumbnail',
			'type'         => 'file',
			// query_args are passed to wp.media's library query.
			'query_args'   => array(
				// Or only allow gif, jpg, or png images
				 'type' => array(
				     'image/gif',
				     'image/jpeg',
				     'image/png',
				 ),
			),
			'preview_size' => 'medium', // Image size to use when previewing in the admin
		) );

		$this->group_options();
//		$this->advanced_options();

		/**
		 * Registers tertiary options page, and set main item as parent.
		 */
		$args = array(
			'id'           => 'cp_groups_license_options_page',
			'title'        => 'Settings',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cp_groups_license',
			'parent_slug'  => 'cp_groups_main_options',
			'tab_group'    => 'cp_groups_main_options',
			'tab_title'    => 'License',
			'display_cb'   => [ $this, 'options_display_with_tabs' ]
		);

		$tertiary_options = new_cmb2_box( $args );

		$tertiary_options->add_field( array(
			'name' => 'License Key',
			'id'   => 'license',
			'type' => 'text',
		) );
	}

	protected function group_options() {
		/**
		 * Registers secondary options page, and set main item as parent.
		 */
		$args = array(
			'id'           => 'cp_groups_group_options_page',
			'title'        => 'Settings',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cp_groups_group_options',
			'parent_slug'  => 'cp_groups_main_options',
			'tab_group'    => 'cp_groups_main_options',
			'tab_title'    => cp_groups()->setup->post_types->groups->plural_label,
			'display_cb'   => [ $this, 'options_display_with_tabs' ],
		);

		$options = new_cmb2_box( $args );

		$options->add_field( array(
			'name' => __( 'Labels' ),
			'id'   => 'labels',
			'type' => 'title',
		) );

		$options->add_field( array(
			'name'    => __( 'Singular Label', 'cp-groups' ),
			'id'      => 'singular_label',
			'type'    => 'text',
			'default' => cp_groups()->setup->post_types->groups->single_label,
		) );

		$options->add_field( array(
			'name'    => __( 'Plural Label', 'cp-groups' ),
			'id'      => 'plural_label',
			'desc'    => __( 'Caution: changing this value will also adjust the url structure and may affect your SEO.', 'cp-groups' ),
			'type'    => 'text',
			'default' => cp_groups()->setup->post_types->groups->plural_label,
		) );

	}

	protected function advanced_options() {
		/**
		 * Registers secondary options page, and set main item as parent.
		 */
		$args = array(
			'id'           => 'cp_groups_advanced_options_page',
			'title'        => 'Settings',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'cp_groups_advanced_options',
			'parent_slug'  => 'cp_groups_main_options',
			'tab_group'    => 'cp_groups_main_options',
			'tab_title'    => 'Advanced',
			'display_cb'   => [ $this, 'options_display_with_tabs' ],
		);

		$advanced_options = new_cmb2_box( $args );

		$advanced_options->add_field( array(
			'name' => __( 'Modules' ),
			'id'   => 'modules_enabled',
			'type' => 'title',
		) );

		$advanced_options->add_field( array(
			'name'    => __( 'Enable' ) . ' ' . cp_groups()->setup->post_types->groups_type->plural_label,
			'id'      => 'item_type_enabled',
			'type'    => 'radio_inline',
			'default' => 1,
			'options' => [
				1 => __( 'Enable', 'cp-groups' ),
				0 => __( 'Disable', 'cp-groups' ),
			]
		) );

		$advanced_options->add_field( array(
			'name'    => __( 'Enable' ) . ' ' . cp_groups()->setup->post_types->speaker->plural_label,
			'id'      => 'speaker_enabled',
			'type'    => 'radio_inline',
			'default' => 1,
			'options' => [
				1 => __( 'Enable', 'cp-groups' ),
				0 => __( 'Disable', 'cp-groups' ),
			]
		) );

	}

	/**
	 * A CMB2 options-page display callback override which adds tab navigation among
	 * CMB2 options pages which share this same display callback.
	 *
	 * @param \CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
	 */
	public function options_display_with_tabs( $cmb_options ) {
		$tabs = $this->options_page_tabs( $cmb_options );
		?>
		<div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
			<?php if ( get_admin_page_title() ) : ?>
				<h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
			<?php endif; ?>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
					<a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>"
					   href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
				<?php endforeach; ?>
			</h2>
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST"
				  id="<?php echo $cmb_options->cmb->cmb_id; ?>" enctype="multipart/form-data"
				  encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $cmb_options->option_key ); ?>">
				<?php $cmb_options->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $cmb_options->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Gets navigation tabs array for CMB2 options pages which share the given
	 * display_cb param.
	 *
	 * @param \CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
	 *
	 * @return array Array of tab information.
	 */
	public function options_page_tabs( $cmb_options ) {
		$tab_group = $cmb_options->cmb->prop( 'tab_group' );
		$tabs      = array();

		foreach ( \CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
			if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
				$tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
					? $cmb->prop( 'tab_title' )
					: $cmb->prop( 'title' );
			}
		}

		return $tabs;
	}


}
