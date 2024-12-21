<?php
/**
 * Template for displaying the group list
 *
 * Variables available:
 * @var array $args array of shortcode arguments
 */

use CP_Groups\Templates;
use ChurchPlugins\Helpers;

global $wp_query;

$taxonomies = get_object_taxonomies( 'cp_group' );

$default_args = [];
foreach( $taxonomies as $tax ) {
	$default_args[ $tax ] = '';
}

$args = shortcode_atts(
	$default_args,
	$args,
	'cp-groups'
);


$filter_terms = [];
foreach ( $taxonomies as $tax ) {
	if ( ! empty( $args[ $tax ] ) ) {
		$filter_terms[ $tax ] = explode( ',', trim( $args[ $tax ] ) );
	}
}

$params = $_GET;
$params['post_type'] = 'cp_group';

if ( isset( $params['groups-paged' ] ) ) {
	$params['paged'] = $params['groups-paged'];
}

if ( isset( $params['group-search' ] ) ) {
	$params['s'] = $params['group-search'];
}

$tax_query = [];

foreach ( $filter_terms as $taxonomy => $terms ) {
	$tax_query[] = [
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $terms,
	];
}

if ( ! empty( $tax_query ) ) {
	$params['tax_query'] = $tax_query;
}

$params = apply_filters( 'cp_groups_shortcode_query', $params );
$wp_query = new WP_Query( $params );

?>
<div id="cp-groups-list" class="cp-groups-archive--container--list">
	<?php cp_groups()->templates->get_template_part( "parts/filter-selected" ); ?>

	<div class="cp-groups-archive--list">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="cp-groups-archive--list--item">
					<?php cp_groups()->templates->get_template_part( "parts/group-list" ); ?>
				</div>
			<?php endwhile; ?>
		<?php else : ?>
			<p><?php _e( "No groups found.", 'cp-groups' ); ?></p>
		<?php endif; wp_reset_postdata(); ?>
	</div>

	<?php Helpers::safe_posts_pagination( [ 'format' => '?groups-paged=%#%#cp-groups-list' ] ); ?>

</div>

<?php wp_reset_query(); ?>
