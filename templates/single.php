<?php if ( have_posts() ) : ?>
	<div class="cp-group">
		<?php do_action( 'cp_before_group_single' ); ?>

		<?php while( have_posts() ) : the_post(); ?>
			<?php cp_groups()->templates->get_template_part( "parts/group-single" ); ?>
		<?php endwhile; ?>

		<?php do_action( 'cp_after_group_single' ); ?>
	</div>
<?php endif; ?>
