<?php
/**
 * Server-side rendering of the `core/post-featured-image` block.
 *
 * @package WordPress
 */

/**
 * Renders the `core/post-featured-image` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Returns the featured image for the current post.
 */
function render_block_core_post_featured_image( $attributes, $content, $block ) {
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
		return '';
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

