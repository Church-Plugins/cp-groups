<?php
use CP_Groups\Templates;

global $wp_query;

$params = $_GET;
$params['post_type'] = 'cp_group';

if ( isset( $params['groups-paged' ] ) ) {
	$params['paged'] = $params['groups-paged'];
}

if ( isset( $params['group-search' ] ) ) {
	$params['s'] = $params['group-search'];
}

$wp_query = new WP_Query( $params );

?>
<div id="cp-groups-list" class="cp-groups-archive--container--list">
	<?php Templates::get_template_part( "parts/filter-selected" ); ?>

	<div class="cp-groups-archive--list">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="cp-groups-archive--list--item">
					<?php Templates::get_template_part( "parts/group-list" ); ?>
				</div>
			<?php endwhile; ?>
		<?php else : ?>
			<p><?php _e( "No groups found.", 'cp-groups' ); ?></p>
		<?php endif; wp_reset_postdata(); ?>
	</div>
	
	<?php the_posts_pagination( [ 'format' => '?groups-paged=%#%#cp-groups-list' ] ); ?>

</div>

<?php wp_reset_query(); ?>
