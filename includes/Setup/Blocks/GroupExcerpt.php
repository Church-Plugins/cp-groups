<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;

class GroupExcerpt extends Block {
    public $name = 'group-excerpt';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();

      if ( is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        add_filter(
          'excerpt_length',
          function() {
            return 100;
          },
          PHP_INT_MAX
        );
      }
    }

    /**
     * Renders the `cp-groups/group-excerpt` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param \WP_Block $block      Block instance.
     * @return string Returns excerpt of the current group
     */
    public function render( $attributes, $content, $block ) {
      if ( ! isset( $block->context['postId'] ) ) {
        return '';
      }
    
      /*
      * The purpose of the excerpt length setting is to limit the length of both
      * automatically generated and user-created excerpts.
      * Because the excerpt_length filter only applies to auto generated excerpts,
      * wp_trim_words is used instead.
      */
      $excerpt_length = $attributes['excerptLength'];
      $excerpt        = get_the_excerpt( $block->context['postId'] );
      if ( isset( $excerpt_length ) ) {
        $excerpt = wp_trim_words( $excerpt, $excerpt_length );
      }
    
      $more_text           = ! empty( $attributes['moreText'] ) ? '<a class="wp-block-post-excerpt__more-link" href="' . esc_url( get_the_permalink( $block->context['postId'] ) ) . '">' . wp_kses_post( $attributes['moreText'] ) . '</a>' : '';
      $filter_excerpt_more = function( $more ) use ( $more_text ) {
        return empty( $more_text ) ? $more : '';
      };
      /**
       * Some themes might use `excerpt_more` filter to handle the
       * `more` link displayed after a trimmed excerpt. Since the
       * block has a `more text` attribute we have to check and
       * override if needed the return value from this filter.
       * So if the block's attribute is not empty override the
       * `excerpt_more` filter and return nothing. This will
       * result in showing only one `read more` link at a time.
       */
      add_filter( 'excerpt_more', $filter_excerpt_more );
      $classes = array();
      if ( isset( $attributes['textAlign'] ) ) {
        $classes[] = 'has-text-align-' . $attributes['textAlign'];
      }
      if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
        $classes[] = 'has-link-color';
      }
      $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );
    
      $content               = '<p class="wp-block-post-excerpt__excerpt">' . $excerpt;
      $show_more_on_new_line = ! isset( $attributes['showMoreOnNewLine'] ) || $attributes['showMoreOnNewLine'];
      if ( $show_more_on_new_line && ! empty( $more_text ) ) {
        $content .= '</p><p class="wp-block-post-excerpt__more-text">' . $more_text . '</p>';
      } else {
        $content .= " $more_text</p>";
      }
      remove_filter( 'excerpt_more', $filter_excerpt_more );
      return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, $content );
    }
}