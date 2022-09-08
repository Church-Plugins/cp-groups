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

<div class="cp-group-item">

	<div class="cp-group-item--thumb">
		<div class="cp-group-item--thumb--canvas" style="background: url(<?php echo esc_url( $item['thumb'] ); ?>) 0% 0% / cover;">
			<?php if ( $item['thumb'] ) : ?>
				<img alt="<?php esc_attr( $item['title'] ); ?>" src="<?php echo esc_url( $item['thumb'] ); ?>">
			<?php endif; ?>
		</div>
	</div>

	<div class="cp-group-item--details">

		<?php if ( ! empty( $item['types'] ) || ! empty( $item['lifeStages'] ) ) : // for mobile ?>
			<div class="cp-group-item--categories">
				<?php if ( ! empty( $item['types'] ) ) : ?>
					<div class="cp-group-item--type">
						<?php foreach( $item['types'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall" href="#"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				
				<?php if ( ! empty( $item['lifeStages'] ) ) : ?>
					<div class="cp-group-item--life-stage">
						<?php foreach( $item['lifeStages'] as $slug => $label ) : ?>
							<a class="cp-button is-xsmall is-transparent" href="#"><?php echo $label; ?></a>
						<?php endforeach; ?>
					</div>
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
					<a class="cp-button is-xsmall is-transparent" href="#"><?php echo $location['title']; ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	
	<div style="display:none;">
		<?php Templates::get_template_part( "parts/group-modal" ); ?>	
	</div>
	

</div>
