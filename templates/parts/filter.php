<?php
use ChurchPlugins\Helpers;
use CP_Groups\Admin\Settings;

$taxonomies = apply_filters( 'cp_groups_filter_facets', cp_groups()->setup->taxonomies->get_objects() );
$uri = explode( '?', $_SERVER['REQUEST_URI'] )[0];
$uri = explode( 'page', $uri )[0];
$get = $_GET;

$search_param = is_post_type_archive( 'cp_group' ) ? 's' : 'group-search';
$cp_connect_custom_meta = get_option( 'cp_group_custom_meta_mapping', [] );

?>
<div id="cp-group-filters" class="cp-groups-filter">

	<form method="get" action="<?php echo $uri; ?>#cp-group-filters" class="cp-groups-filter--form">

		<legend class="text-xlarge"><?php _e( 'Filter by', 'cp-groups' ); ?></legend>

		<div class="cp-groups-filter--toggle">
			<a href="#" class="cp-groups-filter--toggle--button cp-button"><span><?php _e( 'Filter', 'cp-library' ); ?></span> <?php echo Helpers::get_icon( 'filter' ); ?></a>
		</div>

		<div class="cp-groups-filter--facet cp-groups-filter--search">
			<div class="cp-groups-filter--search--box cp-button is-light">
				<button type="submit"><span class="material-icons-outlined">search</span></button>
				<input type="text" name="<?php echo $search_param; ?>" value="<?php echo Helpers::get_param( $_GET, $search_param ); ?>"
					   placeholder="<?php _e( 'Search', 'cp-groups' ); ?>"/>
			</div>
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

		<?php foreach( $cp_connect_custom_meta as $meta_mapping ) : ?>
			<?php if( Settings::get_advanced( $meta_mapping['slug'], false ) ) continue; ?>
			<div class="cp-groups-filter--facet cp-groups-filter--has-dropdown">
				<a href="#" class="cp-groups-filter--dropdown-button cp-button is-light"><?php echo $meta_mapping['display_name'] ?></a>
				<div class="cp-groups-filter--dropdown">
					<?php foreach ( $meta_mapping['options'] as $option_slug => $option ) : ?>
						<label>
							<?php 
							$existing_options = Helpers::get_param( $_GET, $meta_mapping['slug'], [] );
							if( ! is_array( $existing_options ) ) {
								$existing_options = [];
							}
							?>
							<input type="checkbox" <?php checked( in_array( $option_slug, $existing_options ) ); ?> name="<?php echo esc_attr( $meta_mapping['slug'] ); ?>[]" value="<?php echo esc_attr( $option_slug ); ?>"/> <?php echo esc_html( $option ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="cp-groups-filter--attributes">

			<?php if ( Settings::get_advanced( 'kid_friendly_enabled', true ) ) : ?>
				<div class="cp-groups-filter--facet">
					<label><input type="checkbox" name="child-friendly" value="1" <?php checked( Helpers::get_param( $_GET, 'child-friendly' ) ); ?> /> <?php _e( 'Kid Friendly', 'cp-groups' ); ?></label>
				</div>
			<?php endif; ?>

			<?php if ( $isFull = Settings::get_advanced( 'is_full_enabled', 'hide' ) ) : $isFull = $isFull === 'hide' ? __( 'Show', 'cp-groups' ) : __( 'Hide', 'cp-groups' ); ?>
				<div class="cp-groups-filter--facet">
					<label><input type="checkbox" name="is-full" value="1" <?php checked( Helpers::get_param( $_GET, 'is-full' ) ); ?> /> <?php echo ucwords( $isFull ); ?> <?php _e( 'Groups that are full', 'cp-groups' ); ?></label>
				</div>
			<?php endif; ?>

			<?php if ( Settings::get_advanced( 'accessible_enabled', true ) ) : ?>
				<div class="cp-groups-filter--facet">
					<label><input type="checkbox" name="accessible" value="1" <?php checked( Helpers::get_param( $_GET, 'accessible' ) ); ?> /> <?php _e( 'Wheelchair Accessible', 'cp-groups' ); ?></label>
				</div>
			<?php endif; ?>

			<?php if ( Settings::get_advanced( 'virtual_enabled', false ) ) : ?>
				<div class="cp-groups-filter--facet">
					<label><input type="checkbox" name="virtual" value="1" <?php checked( Helpers::get_param( $_GET, 'virtual' ) ); ?> /> <?php _e( 'Virtual', 'cp-groups' ); ?></label>
				</div>
			<?php endif; ?>

		</div>

	</form>

</div>
