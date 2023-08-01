<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;

class GroupTitle extends Block {
    public $name = 'group-title';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    public function render( $attributes, $content, $block ) {
      if ( ! isset( $block->context['postId'] ) ) {
        return '';
      }
    
      $post  = get_post( $block->context['postId'] );
      $title = get_the_title( $post );
    
      if ( ! $title ) {
        return '';
      }
    
      $tag_name = 'h2';
      if ( isset( $attributes['level'] ) ) {
        $tag_name = 0 === $attributes['level'] ? 'p' : 'h' . $attributes['level'];
      }
    
      if ( isset( $attributes['isLink'] ) && $attributes['isLink'] ) {
        $rel   = ! empty( $attributes['rel'] ) ? 'rel="' . esc_attr( $attributes['rel'] ) . '"' : '';
        $title = sprintf( '<a href="%1$s" target="%2$s" %3$s>%4$s</a>', get_the_permalink( $post ), esc_attr( $attributes['linkTarget'] ), $rel, $title );
      }
    
      $classes = array();
      if ( isset( $attributes['textAlign'] ) ) {
        $classes[] = 'has-text-align-' . $attributes['textAlign'];
      }
      if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
        $classes[] = 'has-link-color';
      }
      $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );
    
      return sprintf(
        '<%1$s %2$s>%3$s</%1$s>',
        $tag_name,
        $wrapper_attributes,
        $title
      );
    }
}