<?php

use CP_Groups\Templates;

$description   = get_the_archive_description();
$term          = get_queried_object();
$taxonomy_name = get_taxonomy_labels( get_taxonomy( $term->taxonomy ) )->singular_name;

$types = [];

// if the post_type is defined in the query, only include that type
$queried_post_type = get_query_var( 'post_type' );
if ( isset( $types[ $queried_post_type ] ) ) {
	$types = [ $types[ $queried_post_type ] ];
}
?>

<div class="cp-archive cp-archive--<?php echo esc_attr( $term->slug ); ?>">

	<?php do_action( 'cp_before_archive' ); ?>
	<?php do_action( 'cp_before_archive_'  . $term->slug ); ?>

	<div class="cp-archive-term-name">
		<!-- get the taxonomy name -->
		<?php echo esc_html( $taxonomy_name ); ?>
	</div>
	<h1 class="page-title"><?php echo single_term_title( '', false ); ?></h1>
	<?php if ( $description ) : ?>
		<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
	<?php endif; ?>

	<div class="cp-archive--container">
		<?php if ( have_posts() ) : ?>
			<div class="cp-archive--list">
				<?php while ( have_posts() ) : the_post(); ?>
					<div class="cp-archive--list--item">
						<?php $type = Templates::get_type( get_post_type() ); ?>
						<?php cp_groups()->templates->get_template_part( 'parts/' . $type . '-list' ); ?>
					</div>
				<?php endwhile; ?>
			</div>
		<?php else: ?>
			<p><?php printf( __( "No %s found.", 'cp-library' ), $type->plural_label ); ?></p>
		<?php endif; ?>

		<!-- pagination -->
		<?php cp_groups()->templates->get_template_part( 'parts/pagination' ); ?>
	</div>

	<?php do_action( 'cp_after_archive' ); ?>
	<?php do_action( 'cp_after_archive_'  . $term->slug ); ?>
</div>
