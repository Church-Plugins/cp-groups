<?php
namespace CP_Groups\Setup\PostTypes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use ChurchPlugins\Setup\PostTypes\PostType;

/**
 * Setup for custom post type: Group
 *
 * @since 1.0
 */
class Shortcode extends PostType {

	/**
	 * Child class constructor
	 */
	protected function __construct() {
		$this->post_type = "cp-groups_shortcode";

		$this->single_label = apply_filters( "cploc_single_{$this->post_type}_label", 'Shortcode' );
		$this->plural_label = apply_filters( "cploc_plural_{$this->post_type}_label", 'Shortcodes' );

		parent::__construct();

		// the model for this class is not compatible with CP core
		$this->model = false;
	}

	/**
	 * Initializes actions and filters
	 */
	public function add_actions() {
		add_action( 'add_meta_boxes', [ $this, 'init_post_menu_item' ] );
		add_shortcode( 'cp_group_list', [ $this, 'group_list' ] );
		add_filter( 'allowed_block_types_all', [ $this, 'allowed_block_types' ], 10, 2 );
		add_filter( 'default_content', [ $this, 'populate_content' ], 10, 2 );
		add_filter( "{$this->post_type}_show_in_menu", [ $this, 'show_in_submenu' ] );
		add_filter("{$this->post_type}_slug", [ $this, 'custom_slug' ] );
		parent::add_actions();
	}

	/**
	 * Create CMB2 metaboxes
	 */
	public function register_metaboxes() {}

	/**
	 * Adds a meta box titled "Shortcode" when editing
	 */
	public function init_post_menu_item() {
		add_meta_box( 'shortcode', 'Shortcode', [ $this, 'shortcode_meta_box' ], $this->post_type, 'side', 'high' );
	}

	/**
	 * Adds a disabled text field for copying the shortcode
	 * 
	 * @param \WP_Post $post
	 */
	public function shortcode_meta_box( $post ) {
		$shortcode = "[cp_group_list id={$post->ID}]";
		?>
		<input type='text' disabled value="<?php echo esc_attr( $shortcode ) ?>">
		<button class="button" onclick="navigator.clipboard.writeText('<?php echo esc_attr( $shortcode ) ?>')"><?php echo esc_html_e( 'Copy Shortcode', 'cp-groups' ) ?></button>
		<?php
	}

	/**
	 * Displays the shortcode based on its content
	 * 
	 * @param array $atts the shortcode attributes
	 */
	public function group_list( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0
		), $atts, 'cp_group_list' );

		$atts['id'] = absint( $atts['id'] );

		if( ! $atts['id'] ) {
			return esc_html__( 'Invalid template id', 'cp-groups' );
		}

		$post = get_post( $atts['id'] );

		if( ! $post ) {
			return esc_html__( 'Template not found', 'cp-groups' );
		}

		return apply_filters( 'the_content', $post->post_content );
	}

	/**
	 * The allowed Gutenberg blocks when building a Shortcode post
	 * 
	 * @param array $allowed the allowed blocks
	 * @param \WP_Block_Editor_Context $context the block context
	 */
	public function allowed_block_types( $allowed, $context ) {
		if( $context->post->post_type === $this->post_type ) {
			return apply_filters( 'cp_groups_shortcodes_block_types', array( 
				'core/group',
				'core/spacer',
				'core/columns',
				'core/seperator',
				'cp-groups/group-badge',
				'cp-groups/group-content',
				'cp-groups/group-excerpt',
				'cp-groups/group-featured-image',
				'cp-groups/group-features',
				'cp-groups/group-location',
				'cp-groups/group-tags',
				'cp-groups/group-template',
				'cp-groups/group-time-desc',
				'cp-groups/group-title',
				'cp-groups/query',
				'cp-groups/group-title',
			) );
		}

		return $allowed;
	}

	/**
	 * Checks if a post is a Shortcode and populates it with an HTML template
	 * 
	 * @param string $content the default content for a Shortcode
	 * @param \WP_Post $post the post to check
	 */
	public function populate_content( $content, $post ) {
		if( $post->post_type !== $this->post_type ) {
			return $content;
		}
		$html_file = trailingslashit( CP_GROUPS_PLUGIN_DIR )  . 'templates/default_content/group-content.html';
		return file_get_contents( $html_file );
	}

	/**
	 * Display this menu item in the CP Groups menu item
	 */
	public function show_in_submenu() {
		return 'edit.php?post_type=cp_group';
	}

	/**
	 * Needs a custom slug so it won't be overridden by shortcodes for other plugins
	 */
	public function custom_slug( $slug ) {
		return 'cp_groups_shortcodes';
	}
}
