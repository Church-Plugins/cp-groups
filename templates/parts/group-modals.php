<?php 
use ChurchPlugins\Helpers;
use CP_Groups\Admin\Settings;
use CP_Groups\Templates;
$item = $args['item'];
?>


<div style="display:none;">
		<?php
			Templates::get_template_part( "parts/group-modal" );

			if( Settings::get_advanced( 'contact_action', 'action' ) == 'form' ) {
				$group_leader = get_post_meta( $item['id'], 'leader', true );
				if ( ! $email = get_post_meta( $item['id'], 'leader_email', true ) ) {
					$email = get_post_meta( $item['id'], 'action_contact', true );
				}

				if( is_email( $email ) ) {
					cp_groups()->build_email_modal( 'action_contact', $email, $group_leader, $item['id'] );
				}
			}

			if( Settings::get_advanced( 'hide_registration', 'off' ) == 'off' ) {
				$register_url = get_post_meta( $item['id'], 'registration_url', true );

				if( is_email( $register_url ) ) {
					cp_groups()->build_email_modal( 'action_register', $register_url, $item['title'], $item['id'] );
				}
			}
		?>
	</div>