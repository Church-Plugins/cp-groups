<?php
use ChurchPlugins\Helpers;
use CP_Groups\Admin\Settings;
use CP_Groups\Templates;

try {
	$item = new \CP_Groups\Controllers\Group( get_the_ID() );
	$item = $item->get_api_data();
} catch ( \ChurchPlugins\Exception $e ) {
	error_log( $e );

	return;
}

$disable_modal_class = '';
if ( Settings::get_groups( 'disable_modal', false ) ) {
	$disable_modal_class = 'cp-group-item--disable-modal';
}
$is_location_page = get_query_var( 'cp_location_id' );
?>

<div class="cp-group-item <?php echo $disable_modal_class; ?>">

	<div class="cp-group-item--thumb">
		<a class="cp-group-item--thumb--canvas" href="<?php the_permalink(); ?>" style="background: url(<?php echo esc_url( $item['thumb'] ); ?>) 0% 0% / cover;">
			<?php if ( $item['thumb'] ) : ?>
				<img alt="<?php esc_attr( $item['title'] ); ?>" src="<?php echo esc_url( $item['thumb'] ); ?>">
			<?php endif; ?>
		</a>
	</div>

	<div class="cp-group-item--details">

		<?php if ( ! empty( $item['types'] ) || ! empty( $item['categories'] ) || ! empty( $item['lifeStages'] ) ) : ?>
			<div class="cp-group-item--categories">
				<?php if ( ! empty( $item['categories'] ) ) : ?>
					<div class="cp-group-item--category">
						<?php foreach( $item['categories'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_category' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['types'] ) ) : ?>
					<div class="cp-group-item--type">
						<?php foreach( $item['types'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_type' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $item['lifeStages'] ) ) : ?>
					<div class="cp-group-item--life-stage">
						<?php foreach( $item['lifeStages'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_life_stage' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if( $cp_connect_tags = get_option( 'cp_group_custom_meta_mapping', false ) ) : ?>
					<?php foreach( $cp_connect_tags as $tag ) : ?>
						<?php $item_tag = get_post_meta( $item['originID'], $tag['slug'], true ) ?>
						<?php if( empty( $item_tag ) ) continue; ?>
						<?php if( isset( $tag['options'][$item_tag] ) ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $item_tag, $tag['slug'] ) ?>"><?php echo esc_html( $tag['options'][$item_tag] ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="cp-group-item--title"><a href="<?php the_permalink(); ?>"><?php echo $item['title']; ?></a></h3>

		<div class="cp-group-item--meta">
			<?php if ( ! empty( $item['startTime'] ) ) : ?>
				<div class="cp-group--item--meta--start-time"><?php echo Helpers::get_icon( 'date' ); ?> <?php echo esc_html( $item['startTime'] ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $item['location'] ) ) : ?>
				<div class="cp-group--item--meta--location"><?php echo Helpers::get_icon( 'location' ); ?> <?php echo esc_html( $item['location'] ); ?></div>
			<?php endif; ?>
		</div>

		<div class="cp-group-item--content"><?php echo wp_kses_post( $item['excerpt'] ); ?></div>

		<?php if ( ! empty( $item['locations'] ) ) : ?>
			<div class="cp-group-item--locations">
				<?php foreach( $item['locations'] as $id => $location ) : ?>
					<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( 'location_' . $id, 'cp_location' ); ?>"><?php echo $location['title']; ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="cp-group-item--attributes">
			<?php if ( $item['handicap'] ) : ?>
				<span class="cp-group-item--attributes--accessible"><?php echo Helpers::get_icon( 'accessible' ); ?>
					<?php echo esc_html( Settings::get( 'accessible_badge_label', __( 'Wheelchair Accessible', 'cp-groups' ), 'cp_groups_labels_options' ) ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $item['kidFriendly'] ) : ?>
				<span class="cp-group-item--attributes--kid-friendly">
					<?php echo Helpers::get_icon( 'child' ); ?> <?php echo esc_html( Settings::get( 'kid_friendly_badge_label', __( 'Kid Friendly', 'cp-groups' ), 'cp_groups_labels_options' ) ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $item['meetsOnline'] ) : ?>
				<span class="cp-group-item--attributes--meets-online">
					<?php echo Helpers::get_icon( 'virtual' ); ?> <?php echo esc_html( Settings::get( 'meets_online_badge_label', __( 'Meets Online', 'cp-groups' ), 'cp_groups_labels_options' ) ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $item['isFull'] ) : ?>
				<span class="cp-group-item--attributes--is-full"><?php echo Helpers::get_icon( 'report' ); ?> <?php _e( 'Full', 'cp-groups' ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<div style="display:none;">
		<?php
			cp_groups()->templates->get_template_part( "parts/group-modal" );
		?>
	</div>
</div>
