<?php 

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;
use WP_Block;

class GroupContent extends Block {
    public $name = 'group-content';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    /**
     * Renders the `cp-groups/group-content` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the filtered group content of the current group.
     */
    public function render( $attributes, $content, $block ) {
      static $seen_ids = array();

      if ( ! isset( $block->context['postId'] ) ) {
        return '';
      }

      $post_id = $block->context['postId'];

      if ( isset( $seen_ids[ $post_id ] ) ) {
        // WP_DEBUG_DISPLAY must only be honored when WP_DEBUG. This precedent
        // is set in `wp_debug_mode()`.
        $is_debug = WP_DEBUG && WP_DEBUG_DISPLAY;

        return $is_debug ?
          // translators: Visible only in the front end, this warning takes the place of a faulty block.
          __( '[block rendering halted]' ) :
          '';
      }

      $seen_ids[ $post_id ] = true;

      // Check is needed for backward compatibility with third-party plugins
      // that might rely on the `in_the_loop` check; calling `the_post` sets it to true.
      if ( ! in_the_loop() && have_posts() ) {
        the_post();
      }

      // When inside the main loop, we want to use queried object
      // so that `the_preview` for the current post can apply.
      // We force this behavior by omitting the third argument (post ID) from the `get_the_content`.
      $content = get_the_content();
      // Check for nextpage to display page links for paginated posts.
      if ( has_block( 'core/nextpage' ) ) {
        $content .= wp_link_pages( array( 'echo' => 0 ) );
      }

      /** This filter is documented in wp-includes/post-template.php */
      $content = apply_filters( 'the_content', str_replace( ']]>', ']]&gt;', $content ) );
      unset( $seen_ids[ $post_id ] );

      if ( empty( $content ) ) {
        return '';
      }

      $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'entry-content' ) );

      return (
        '<div ' . $wrapper_attributes . '>' .
          $content .
        '</div>'
      );
    }
}

