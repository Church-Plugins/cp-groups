<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;
use WP_Block;

class GroupTimeDesc extends Block {
    public $name = 'group-time-desc';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    public function render( $attributes, $content, WP_Block $block ) {
      $time_desc = get_post_meta( $block->context['postId'], 'time_desc', true );

      $wrapper_attributes = get_block_wrapper_attributes();

      if( ! $time_desc ) {
        return '';
      }

      return sprintf(
        '<div %1$s><span class="material-icons">calendar_today</span><div>%2$s</div></div>',
        $wrapper_attributes,
        $time_desc
      );
    }
}