<div class="cp-group-modal">
	<?php do_action( 'cp_before_group_modal' ); ?>

	<?php \CP_Groups\Templates::get_template_part( "parts/group-single" ); ?>

	<?php do_action( 'cp_after_group_modal' ); ?>	
</div>
