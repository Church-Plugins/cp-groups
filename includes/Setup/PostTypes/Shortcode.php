<?php
namespace CP_Groups\Setup\PostTypes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use CP_Groups\Admin\Settings;
use ChurchPlugins\Helpers;
use ChurchPlugins\Setup\PostTypes\PostType;
use CP_Groups\Templates;
use WP_Block_Editor_Context;
use WP_Post;

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
		parent::add_actions();
	}

  public function register_metaboxes() {

    $cmb = new_cmb2_box( [
			'id' => 'shortcode_meta',
			'title' => $this->single_label . ' ' . __( 'Details', 'cp-groups' ),
			'object_types' => [ $this->post_type ],
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true,
      'show_in_rest' => true
		] );

  }

  public function init_post_menu_item() {
    add_meta_box( 'shortcode', 'Shortcode', [ $this, 'shortcode_meta_box' ], $this->post_type, 'side', 'high' );
  }

  public function shortcode_meta_box( $post ) {
    $shortcode = "[cp_group_list id={$post->ID}]";
    ?>
    <input type='text' disabled value="<?php echo esc_attr( $shortcode ) ?>">
    <button class="button" onclick="navigator.clipboard.writeText('<?php echo esc_attr( $shortcode ) ?>')"><?php echo esc_html_e( 'Copy Shortcode', 'cp-groups' ) ?></button>
    <?php
  }

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

  public function allowed_block_types( $allowed, WP_Block_Editor_Context $context ) {
    if( false && $context->post->post_type === $this->post_type ) {
      return apply_filters( 'cp_groups_shortcodes_block_types', array( 
        'core/group',
        'core/spacer',
        'core/columns',
        'core/seperator',
        'cp-groups/group-title',
        'cp-groups/group-excerpt',
        'cp-groups/group-template',
        'cp-groups/query'
      ) );
    }

    return $allowed;
  }

  public function populate_content( $content, WP_Post $post ) {
    if( $post->post_type !== $this->post_type ) {
      return $content;
    }
    $html_file = trailingslashit( CP_GROUPS_PLUGIN_DIR )  . 'templates/default_content/group-content.html';
    return file_get_contents( $html_file );
  }

  public function show_in_submenu() {
    return 'edit.php?post_type=cp_group';
  }
}
