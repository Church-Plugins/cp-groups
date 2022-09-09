<?php
use ChurchPlugins\Helpers;
use CP_Groups\Templates;
try {
	$item = new \CP_Groups\Controllers\Group( get_the_ID() );
	$item = $item->get_api_data();
} catch ( \ChurchPlugins\Exception $e ) {
	error_log( $e );

	return;
}
?>

<div class="cp-group-single">
	
	<div class="cp-group-single--thumb">
		<?php if ( $item['thumb'] ) : ?>
			<img alt="<?php esc_attr( $item['title'] ); ?>" src="<?php echo esc_url( $item['thumb'] ); ?>">
		<?php endif; ?>
	</div>

	<div class="cp-group-single--details">
		
		<?php if ( ! empty( $item['locations'] ) ) : ?>
			<div class="cp-group-single--locations">
				<?php foreach( $item['locations'] as $id => $location ) : ?>
					<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( 'location_' . $id, 'cp_location' ); ?>"><?php echo $location['title']; ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>		

		<?php if ( ! empty( $item['types'] ) || ! empty( $item['lifeStages'] ) ) : // for mobile ?>
			<div class="cp-group-single--categories">
				<?php if ( ! empty( $item['types'] ) ) : ?>
					<div class="cp-group-single--type">
						<?php foreach( $item['types'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_type' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				
				<?php if ( ! empty( $item['lifeStages'] ) ) : ?>
					<div class="cp-group-single--life-stage">
						<?php foreach( $item['lifeStages'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="<?php echo Templates::get_facet_link( $slug, 'cp_group_life_stage' ); ?>"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="cp-group-single--title"><?php echo $item['title']; ?></h3>
		
		<?php if ( $item['leader'] ) : ?>
			<h6 class="cp-group-single--leader"><?php echo $item['leader']; ?></h6>
		<?php endif; ?>
		
		<div class="cp-group-single--meta">
			<?php if ( ! empty( $item['startTime'] ) ) : ?>
				<div class="cp-group--item--meta--start-time"><?php echo Helpers::get_icon( 'date' ); ?> <?php echo esc_html( $item['startTime'] ); ?></div>
			<?php endif; ?>
			
			<?php if ( ! empty( $item['location'] ) ) : ?>
				<div class="cp-group--item--meta--location"><?php echo Helpers::get_icon( 'location' ); ?> <?php echo esc_html( $item['location'] ); ?></div>
			<?php endif; ?>
		</div>
		
		<div class="cp-group-single--content"><?php echo wp_kses_post( $item['desc'] ); ?></div>
		
		<?php do_action( 'cp_group_single_after_content', $item ); ?>
	</div>

</div>
