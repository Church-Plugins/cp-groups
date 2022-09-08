<?php
use ChurchPlugins\Helpers;

$taxonomies = apply_filters( 'cp_groups_filter_facets', cp_groups()->setup->taxonomies->get_objects() );
$uri = explode( '?', $_SERVER['REQUEST_URI'] )[0];
$get = $_GET;

if ( empty( $get ) ) {
	return;
}

unset( $get['groups-paged'] );
?>
<div class="cp-groups-filter--filters">
	<?php foreach ( $taxonomies as $tax ) : if ( empty( $_GET[ $tax->taxonomy ] ) ) continue; ?>
		<?php foreach( $_GET[ $tax->taxonomy ] as $slug ) :
			if ( ! $term = get_term_by( 'slug', $slug, $tax->taxonomy ) ) {
				continue;
			}

			$get = $_GET;
			unset( $get[ $tax->taxonomy ][ array_search( $slug, $get[ $tax->taxonomy ] ) ] );
			?>
			<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>#cp-group-filters" class="cp-groups-filter--filters--filter"><?php echo $term->name; ?></a>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>
