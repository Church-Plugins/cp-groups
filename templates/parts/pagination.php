<?php
/**
 * Pagination Template
 *
 * Override this template in your own theme by creating a file at [your-theme]/cp-groups/parts/pagination.php
 * 
 * @package cp-groups
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;
$paged      = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
$pagination = paginate_links(
	[
		'base'    => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
		'format'  => '?paged=%#%',
		'current' => max( 1, $paged ),
		'total'   => $wp_query->max_num_pages,
	]
);

if ( ! empty( $pagination ) ) {
	?>
	<div class="cp-pg-pagination cp-pagination">
		<?php echo $pagination; ?>
	</div>
	<?php
}