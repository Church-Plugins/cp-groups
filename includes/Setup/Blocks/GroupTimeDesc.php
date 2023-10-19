<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;

class GroupTimeDesc extends Block {
    public $name = 'group-time-desc';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    /**
     * Renders the `cp-groups/group-time-desc` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param \WP_Block $block      Block instance.
     * @return string Returns the description of when this group meets.
     */
    public function render( $attributes, $content, $block ) {
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