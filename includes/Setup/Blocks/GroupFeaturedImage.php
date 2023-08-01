<?php

namespace CP_Groups\Setup\Blocks;

use CP_Groups\Admin\Settings;

class GroupFeaturedImage extends Block {
  public $name = 'group-featured-image';
  public $is_dynamic = true;

  public function __construct() {
    parent::__construct();
  }

  public function render( $attributes, $content, $block ) {
    if ( ! isset( $block->context['postId'] ) ) {
      return '';
    }
    $post_ID = $block->context['postId'];
  
    // Check is needed for backward compatibility with third-party plugins
    // that might rely on the `in_the_loop` check; calling `the_post` sets it to true.
    if ( ! in_the_loop() && have_posts() ) {
      the_post();
    }
  
    $is_link        = isset( $attributes['isLink'] ) && $attributes['isLink'];
    $size_slug      = isset( $attributes['sizeSlug'] ) ? $attributes['sizeSlug'] : 'post-thumbnail';
    $attr           = get_block_core_post_featured_image_border_attributes( $attributes );
    $overlay_markup = get_block_core_post_featured_image_overlay_element_markup( $attributes );
  
    if ( $is_link ) {
      if ( get_the_title( $post_ID ) ) {
        $attr['alt'] = trim( strip_tags( get_the_title( $post_ID ) ) );
      } else {
        $attr['alt'] = sprintf(
          // translators: %d is the post ID.
          __( 'Untitled post %d' ),
          $post_ID
        );
      }
    }
  
    $extra_styles = '';
  
    // Aspect ratio with a height set needs to override the default width/height.
    if ( ! empty( $attributes['aspectRatio'] ) ) {
      $extra_styles .= 'width:100%;height:100%;';
    } elseif ( ! empty( $attributes['height'] ) ) {
      $extra_styles .= "height:{$attributes['height']};";
    }
  
    if ( ! empty( $attributes['scale'] ) ) {
      $extra_styles .= "object-fit:{$attributes['scale']};";
    }
  
    if ( ! empty( $extra_styles ) ) {
      $attr['style'] = empty( $attr['style'] ) ? $extra_styles : $attr['style'] . $extra_styles;
    }
  
    $featured_image = get_the_post_thumbnail( $post_ID, $size_slug, $attr );

    

    if ( ! $featured_image ) {
      $featured_image = Settings::get( 'default_thumbnail', '' );

      if( ! $featured_image ) {
        return '';
      }

      $featured_image = esc_url( $featured_image );

      $featured_image = "<img src='$featured_image' loading='lazy' />";
    }
    if ( $is_link ) {
      $link_target    = $attributes['linkTarget'];
      $rel            = ! empty( $attributes['rel'] ) ? 'rel="' . esc_attr( $attributes['rel'] ) . '"' : '';
      $height         = ! empty( $attributes['height'] ) ? 'style="' . esc_attr( safecss_filter_attr( 'height:' . $attributes['height'] ) ) . '"' : '';
      $featured_image = sprintf(
        '<a href="%1$s" target="%2$s" %3$s %4$s>%5$s%6$s</a>',
        get_the_permalink( $post_ID ),
        esc_attr( $link_target ),
        $rel,
        $height,
        $featured_image,
        $overlay_markup
      );
    } else {
      $featured_image = $featured_image . $overlay_markup;
    }
  
    $aspect_ratio = ! empty( $attributes['aspectRatio'] )
      ? esc_attr( safecss_filter_attr( 'aspect-ratio:' . $attributes['aspectRatio'] ) ) . ';'
      : '';
    $width        = ! empty( $attributes['width'] )
      ? esc_attr( safecss_filter_attr( 'width:' . $attributes['width'] ) ) . ';'
      : '';
    $height       = ! empty( $attributes['height'] )
      ? esc_attr( safecss_filter_attr( 'height:' . $attributes['height'] ) ) . ';'
      : '';
    if ( ! $height && ! $width && ! $aspect_ratio ) {
      $wrapper_attributes = get_block_wrapper_attributes();
    } else {
      $wrapper_attributes = get_block_wrapper_attributes( array( 'style' => $aspect_ratio . $width . $height ) );
    }
    return "<figure {$wrapper_attributes}>{$featured_image}</figure>";
  }

  protected function get_border_attributes( $attributes ) {
    $border_styles = array();
    $sides         = array( 'top', 'right', 'bottom', 'left' );
  
    // Border radius.
    if ( isset( $attributes['style']['border']['radius'] ) ) {
      $border_styles['radius'] = $attributes['style']['border']['radius'];
    }
  
    // Border style.
    if ( isset( $attributes['style']['border']['style'] ) ) {
      $border_styles['style'] = $attributes['style']['border']['style'];
    }
  
    // Border width.
    if ( isset( $attributes['style']['border']['width'] ) ) {
      $border_styles['width'] = $attributes['style']['border']['width'];
    }
  
    // Border color.
    $preset_color           = array_key_exists( 'borderColor', $attributes ) ? "var:preset|color|{$attributes['borderColor']}" : null;
    $custom_color           = _wp_array_get( $attributes, array( 'style', 'border', 'color' ), null );
    $border_styles['color'] = $preset_color ? $preset_color : $custom_color;
  
    // Individual border styles e.g. top, left etc.
    foreach ( $sides as $side ) {
      $border                 = _wp_array_get( $attributes, array( 'style', 'border', $side ), null );
      $border_styles[ $side ] = array(
        'color' => isset( $border['color'] ) ? $border['color'] : null,
        'style' => isset( $border['style'] ) ? $border['style'] : null,
        'width' => isset( $border['width'] ) ? $border['width'] : null,
      );
    }
  
    $styles     = wp_style_engine_get_styles( array( 'border' => $border_styles ) );
    $attributes = array();
    if ( ! empty( $styles['classnames'] ) ) {
      $attributes['class'] = $styles['classnames'];
    }
    if ( ! empty( $styles['css'] ) ) {
      $attributes['style'] = $styles['css'];
    }
    return $attributes;
  }

  public function get_overlay_element_markup( $attributes ) {
    $has_dim_background  = isset( $attributes['dimRatio'] ) && $attributes['dimRatio'];
    $has_gradient        = isset( $attributes['gradient'] ) && $attributes['gradient'];
    $has_custom_gradient = isset( $attributes['customGradient'] ) && $attributes['customGradient'];
    $has_solid_overlay   = isset( $attributes['overlayColor'] ) && $attributes['overlayColor'];
    $has_custom_overlay  = isset( $attributes['customOverlayColor'] ) && $attributes['customOverlayColor'];
    $class_names         = array( 'wp-block-post-featured-image__overlay' );
    $styles              = array();

    if ( ! $has_dim_background ) {
      return '';
    }

    // Apply border classes and styles.
    $border_attributes = get_block_core_post_featured_image_border_attributes( $attributes );

    if ( ! empty( $border_attributes['class'] ) ) {
      $class_names[] = $border_attributes['class'];
    }

    if ( ! empty( $border_attributes['style'] ) ) {
      $styles[] = $border_attributes['style'];
    }

    // Apply overlay and gradient classes.
    if ( $has_dim_background ) {
      $class_names[] = 'has-background-dim';
      $class_names[] = "has-background-dim-{$attributes['dimRatio']}";
    }

    if ( $has_solid_overlay ) {
      $class_names[] = "has-{$attributes['overlayColor']}-background-color";
    }

    if ( $has_gradient || $has_custom_gradient ) {
      $class_names[] = 'has-background-gradient';
    }

    if ( $has_gradient ) {
      $class_names[] = "has-{$attributes['gradient']}-gradient-background";
    }

    // Apply background styles.
    if ( $has_custom_gradient ) {
      $styles[] = sprintf( 'background-image: %s;', $attributes['customGradient'] );
    }

    if ( $has_custom_overlay ) {
      $styles[] = sprintf( 'background-color: %s;', $attributes['customOverlayColor'] );
    }

    return sprintf(
      '<span class="%s" style="%s" aria-hidden="true"></span>',
      esc_attr( implode( ' ', $class_names ) ),
      esc_attr( safecss_filter_attr( implode( ' ', $styles ) ) )
    );
  }
}