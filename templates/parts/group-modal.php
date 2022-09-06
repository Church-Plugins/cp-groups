<div class="cp-group-modal">
	<?php do_action( 'cp_before_group_modal' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php \CP_Groups\Templates::get_template_part( "parts/group-single" ); ?>
	<?php endwhile; ?>

	<?php do_action( 'cp_after_group_modal' ); ?>	
</div>
