<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;
use WP_Error;
use WP_Term;

class GroupTags extends Block {
    public $name = 'group-tags';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    public function render( $attributes, $content, $block ) {
      $post = get_post( $block->context['postId'] );
      $taxonomies = get_taxonomies( array( 'object_type' => ['cp_group'] ) );
      $primary_tags = array();
      $additional_tags = array();
      foreach( $taxonomies as $slug => $_taxonomy ) {
        $terms = get_the_terms( $post, $slug );

        $taxonomies[$slug] = array();

        if( is_wp_error( $terms) || ! $terms ) continue;

        if( $attributes['primaryTagType'] === $slug ) {
          $primary_tags = $terms;
        }
        else if( in_array( $slug, $attributes['additionalTagTypes'] ) ) {
          $additional_tags = array_merge( $additional_tags, $terms );
        }
      }


      $primary_tag_style    = $attributes['highlightStyle'] === 'solid' ? '' : 'is-transparent';
      $additional_tag_style = $attributes['highlightStyle'] === 'solid' ? 'is-transparent' : '';

      $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'cp-group-item--categories' ) );

      ob_start();
      ?>

      <div <?php echo $wrapper_attributes ?>>
        <?php foreach( $primary_tags as $tag ): ?>
          <div className='cp-group-item--type'>
            <a href="#" class="cp-button is-xsmall <?php echo $primary_tag_style ?>"><?php esc_html_e( $tag->name ) ?></a>
          </div>
        <?php endforeach; ?>

        <?php foreach( $additional_tags as $tag ): ?>
          <div className='cp-group-item--type'>
            <a href="#" class="cp-button is-xsmall <?php echo $additional_tag_style ?>"><?php esc_html_e( $tag->name ) ?></a>
          </div>
        <?php endforeach; ?>
      </div>
        
      <?php
      return ob_get_clean();
    }
}