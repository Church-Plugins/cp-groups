<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;

class GroupLocation extends Block {
    public $name = 'group-location';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    /**
     * Renders the `cp-groups/group-location` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param \WP_Block $block      Block instance.
     * @return string Returns the HTML for the group location block.
     */
    public function render( $attributes, $content, $block ) {
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