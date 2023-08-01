<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;
use WP_Block;

class GroupLocation extends Block {
    public $name = 'group-location';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    public function render( $attributes, $content, WP_Block $block ) {
      $location = get_post_meta( $block->context['postId'], 'location', true );

      $wrapper_attributes = get_block_wrapper_attributes();

      if( ! $location ) {
        return '';
      }

      return sprintf(
        '<div %1$s><span class="material-icons">location_on</span><div>%2$s</div></div>',
        $wrapper_attributes,
        $location
      );
    }
}