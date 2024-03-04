<?php
/**
 * Default Groups Content Template
 *
 * Override this template in your own theme by creating a file at [your-theme]/cp-library/default-template.php
 *
 * @package cp-library
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Allows filtering the classes for the main element.
 *
 * @param array<string> $classes An (unindexed) array of classes to apply.
 * @return array<string> The modified array of classes.
 * @since 1.0.0
 */
$classes = apply_filters( 'cp_default_template_classes', [ 'cp-pg-template' ] );

get_header();

/**
 * Provides an action that allows for the injection of HTML at the top of the template after the header.
 */
do_action( 'cp_default_template_after_header' );
?>
<main id="cp-pg-template" class="<?php echo implode( ' ', $classes ); ?>">
	<?php echo apply_filters( 'cp_default_template_before_content', '' ); ?>
	<?php \CP_Groups\Templates::get_view(); ?>
	<?php echo apply_filters( 'cp_default_template_after_content', '' ); ?>
</main> <!-- #cp-pg-template -->
<?php

/**
 * Provides an action that allows for the injections of HTML at the bottom of the template before the footer.
 */
do_action( 'cp_default_template_before_footer' );

get_footer();
