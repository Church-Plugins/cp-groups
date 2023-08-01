<?php 

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;
use WP_Block;

class GroupBadge extends Block {
    public $name = 'group-badge';
    public $is_dynamic = true;

    public function __construct() {
      parent::__construct();
    }

    /**
     * Renders the `cp-groups/group-badge` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the filtered group content of the current group.
     */
    public function render( $attributes, $content, $block ) {
      $wrapper_attributes = get_block_wrapper_attributes();

      $badge_types = array(
        array( 
          'label' => esc_html__( 'Kid Friendly', 'cp-groups' ),
          'value' => 'kid_friendly',
          'icon'  => 'escalator_warning'
        ),
        array( 
          'label' => esc_html__( 'Accessible', 'cp-groups' ),
          'value' => 'handicap_accessible',
          'icon'  => 'accessible'
        )
      );

      $badge_type = current( array_filter( $badge_types, function( $type ) use ( $attributes ) {
        return $type['value'] === $attributes['badgeType'];
      } ) );

      $badge = $badge_type ? get_post_meta( $block->context['postId'], $badge_type['value'], true ) : false;

      if( ! $badge ) {
        return '';
      }

      return sprintf(
        '<div %1$s><span class="material-icons">%2$s</span><div>%3$s</div></div>',
        $wrapper_attributes,
        $badge_type['icon'],
        $badge_type['label']
      );
    }
}

