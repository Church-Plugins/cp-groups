<?php
use ChurchPlugins\Helpers;

$taxonomies = apply_filters( 'cp_groups_filter_facets', cp_groups()->setup->taxonomies->get_objects() );
$uri = explode( '?', $_SERVER['REQUEST_URI'] )[0];
$get = $_GET;
?>
<div id="cp-group-filters" class="cp-groups-filter">

	<form method="get" action="#cp-group-filters" class="cp-groups-filter--form">

		<legend class="text-xlarge"><?php _e( 'Filter by', 'cp-groups' ); ?></legend>

		<div class="cp-groups-filter--toggle">
			<a href="#" class="cp-groups-filter--toggle--button cp-button"><span><?php _e( 'Filter', 'cp-library' ); ?></span> <?php echo Helpers::get_icon( 'filter' ); ?></a>
		</div>
		
		<?php foreach( $taxonomies as $tax ) :
			$terms = apply_filters( 'cp_groups_filter_facet_terms', get_terms( [ 'taxonomy' => $tax->taxonomy ] ), $tax->taxonomy, $tax );

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			} ?>

			<div class="cp-groups-filter--facet cp-groups-filter--<?php echo esc_attr( $tax->taxonomy ); ?> cp-groups-filter--has-dropdown">
				<a href="#" class="cp-groups-filter--dropdown-button cp-button is-light"><?php echo $tax->single_label; ?></a>
				<div class="cp-groups-filter--dropdown">
					<?php foreach ( $terms as $term ) : ?>
						<label>
							<input type="checkbox" <?php checked( in_array( $term->slug, Helpers::get_param( $_GET, $tax->taxonomy, [] ) ) ); ?> name="<?php echo esc_attr( $tax->taxonomy ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>"/> <?php echo esc_html( $term->name ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

	</form>

</div>
