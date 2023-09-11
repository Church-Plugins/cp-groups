<?php
use ChurchPlugins\Helpers;
use CP_Groups\Admin\Settings;

$taxonomies = apply_filters( 'cp_groups_filter_facets', cp_groups()->setup->taxonomies->get_objects() );

$uri = explode( '?', $_SERVER['REQUEST_URI'] )[0];
$uri = explode( 'page', $uri )[0];
$get = $_GET;

$search_param = is_post_type_archive( 'cp_group' ) ? 's' : 'group-search';

$kid_friendly_param = 'child-friendly';
$is_full_param      = 'is-full';
$accessible_param   = 'accessible';
$virtual_param      = 'virtual';

$isFull = Settings::get_advanced( 'is_full_enabled', 'hide' ) === 'hide' ? __( 'Show', 'cp-groups' ) : __( 'Hide', 'cp-groups' );

$cp_connect_custom_meta = get_option( 'cp_group_custom_meta_mapping', [] );

if ( empty( $get ) ) {
	return;
}

unset( $get['groups-paged'] );
?>
<div class="cp-groups-filter--filters">
	<?php if ( ! empty( $_GET[ $search_param ] ) ) : unset( $get[ $search_param ] ); ?>
		<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>" class="cpl-filter--filters--filter"><?php echo __( 'Search:' ) . ' ' . Helpers::get_request( $search_param ); ?></a>
	<?php endif; ?>

	<?php if ( ! empty( $_GET[ $kid_friendly_param ] ) ) : $get = $_GET; unset( $get[ $kid_friendly_param ] ); ?>
		<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>" class="cpl-filter--filters--filter"><?php _e( 'Kid Friendly', 'cp-groups' ); ?></a>
	<?php endif; ?>

	<?php if ( ! empty( $_GET[ $is_full_param ] ) ) : $get = $_GET; unset( $get[ $is_full_param ] ); ?>
		<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>" class="cpl-filter--filters--filter"><?php echo ucwords( $isFull ); ?> <?php _e( 'Full Groups', 'cp-groups' ); ?></a>
	<?php endif; ?>

	<?php if ( ! empty( $_GET[ $accessible_param ] ) ) : $get = $_GET; unset( $get[ $accessible_param ] ); ?>
		<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>" class="cpl-filter--filters--filter"><?php _e( 'Wheelchair Accessible', 'cp-groups' ); ?></a>
	<?php endif; ?>

	<?php if ( ! empty( $_GET[ $virtual_param ] ) ) : $get = $_GET; unset( $get[ $virtual_param ] ); ?>
		<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>" class="cpl-filter--filters--filter"><?php _e( 'Virtual', 'cp-groups' ); ?></a>
	<?php endif; ?>

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

	<?php foreach( $cp_connect_custom_meta as $custom_meta ): ?>
		<?php $meta_key = $custom_meta['slug'] ?>
		<?php if ( empty( $_GET[ $meta_key ] ) ) continue; ?>
		<?php foreach( $_GET[ $meta_key ] as $slug ) : ?>
			<?php
			$options = $custom_meta['options'];
			$term_name = isset( $options[ $slug ] ) ? $options[ $slug ] : $slug;
			$get = $_GET;
			unset( $get[ $meta_key ][ array_search( $slug, $get[ $meta_key ] ) ] );
			?>
			<a href="<?php echo esc_url( add_query_arg( $get, $uri ) ); ?>#cp-group-filters" class="cp-groups-filter--filters--filter"><?php echo esc_html( $term_name ) ?></a>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>
