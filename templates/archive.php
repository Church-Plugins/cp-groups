<?php
use CP_Groups\Templates;
?>
<div class="cp-groups-archive cp-archive">
	
	<?php do_action( 'cp_groups_before_archive' ); ?>

	<h1 class="page-title"><?php echo apply_filters( 'cp-groups-archive-title', post_type_archive_title() ); ?></h1>

	<div class="cp-groups-archive--search"></div>
	
	<div class="cp-groups-archive--container">
		<div class="cp-groups-archive--container--filters">
			<?php Templates::get_template_part( "parts/filter" ); ?>
		</div>
		
		<div class="cp-groups-archive--container--list">
			<?php Templates::get_template_part( "parts/filter-selected" ); ?>
			
			<div class="cp-groups-archive--list">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<div class="cp-groups-archive--list--item">
							<?php Templates::get_template_part( "parts/group-list" ); ?>
						</div>
					<?php endwhile; ?>
				<?php else : ?>
					<p><?php _e( "No items found.", 'cp-library' ); ?></p>
				<?php endif; ?>				
			</div>
		</div>
	</div>
	
	<?php do_action( 'cp_groups_after_archive' ); ?>
	
</div>