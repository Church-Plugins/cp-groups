<?php

namespace CP_Groups\Admin;

use CP_Groups\Controllers\Group;

/**
 * Admin Tools page for CP Groups
 *
 * @since 1.2.0
 */
class Tools {

	/**
	 * @var Tools
	 */
	protected static $_instance;

	/**
	 * Only make one instance of Tools
	 *
	 * @return Tools
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Tools ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 */
	protected function __construct() {
		$this->actions();
	}

	/**
	 * Admin init actions
	 *
	 * @return void
	 */
	protected function actions() {
		add_action( 'admin_menu', [ $this, 'add_tools_submenu' ] );
		add_action( 'admin_init', [ $this, 'handle_export' ] );
	}

	/**
	 * Add tools submenu
	 *
	 * @return void
	 */
	public function add_tools_submenu() {
		add_submenu_page(
			'edit.php?post_type=cp_group',
			__( 'Tools', 'cp-groups' ),
			__( 'Tools', 'cp-groups' ),
			'manage_options',
			'cp-groups-tools',
			[ $this, 'tools_page' ]
		);
	}

	/**
	 * Tools page content
	 *
	 * @return void
	 */
	public function tools_page() {
		// Get current tab
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'export';

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'CP Groups Tools', 'cp-groups' ); ?></h1>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'cp-groups-tools', 'tab' => 'export' ], admin_url( 'edit.php?post_type=cp_group' ) ) ); ?>"
				   class="nav-tab <?php echo $current_tab === 'export' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Export to CSV', 'cp-groups' ); ?>
				</a>
				<?php do_action( 'cp_groups_tools_tabs', $current_tab ); ?>
			</nav>

			<div class="tab-content">
				<?php
				switch ( $current_tab ) {
					case 'export':
						$this->export_tab();
						break;
					default:
						do_action( 'cp_groups_tools_tab_content', $current_tab );
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Export tab content
	 *
	 * @return void
	 */
	protected function export_tab() {
		?>
		<div class="export-tab" style="max-width: 800px;">
			<h2><?php esc_html_e( 'Export Groups to CSV', 'cp-groups' ); ?></h2>
			<p><?php esc_html_e( 'Export all groups to a CSV file.', 'cp-groups' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'edit.php?post_type=cp_group&page=cp-groups-tools&tab=export' ) ); ?>">
				<?php wp_nonce_field( 'cp_groups_export_csv', 'cp_groups_export_nonce' ); ?>
				<input type="hidden" name="cp_groups_action" value="export_csv" />

				<?php do_action( 'cp_groups_export_options' ); ?>

				<p class="submit">
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Export All Groups to CSV', 'cp-groups' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle CSV export
	 *
	 * @return void
	 */
	public function handle_export() {
		// Check if this is an export request
		if ( ! isset( $_POST['cp_groups_action'] ) || $_POST['cp_groups_action'] !== 'export_csv' ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['cp_groups_export_nonce'] ) || ! wp_verify_nonce( $_POST['cp_groups_export_nonce'], 'cp_groups_export_csv' ) ) {
			wp_die( __( 'Security check failed', 'cp-groups' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to export groups', 'cp-groups' ) );
		}

		// Build query args based on filters
		$query_args = $this->build_export_query_args();

		// Generate and download CSV
		$this->generate_csv( $query_args );
	}

	/**
	 * Build query args for export
	 *
	 * @return array
	 */
	protected function build_export_query_args() {
		$args = [
			'post_type'      => 'cp_group',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		return apply_filters( 'cp_groups_export_query_args', $args );
	}

	/**
	 * Generate CSV file
	 *
	 * @param array $query_args
	 * @return void
	 */
	protected function generate_csv( $query_args ) {
		// Temporarily remove the groups_query filter to prevent distance sorting issues
		remove_action( 'pre_get_posts', [ cp_groups()->setup->post_types->groups, 'groups_query' ] );

		$query = new \WP_Query( $query_args );

		add_action( 'pre_get_posts', [ cp_groups()->setup->post_types->groups, 'groups_query' ] );

		if ( ! $query->have_posts() ) {
			wp_die( __( 'No groups found matching your criteria', 'cp-groups' ) );
		}

		// Set headers for CSV download
		$filename = 'cp-groups-export-' . date( 'Y-m-d-His' ) . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Open output stream
		$output = fopen( 'php://output', 'w' );

		// Add UTF-8 BOM for Excel compatibility
		fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );

		// Get columns
		$columns = $this->get_csv_columns();

		// Write headers
		fputcsv( $output, $columns );

		// Write data rows
		while ( $query->have_posts() ) {
			$query->the_post();
			$group = new Group( get_the_ID() );
			$fields = $this->get_csv_fields( $group );
			fputcsv( $output, array_values( $fields ) );
		}

		wp_reset_postdata();

		fclose( $output );
		exit;
	}

	/**
	 * Get CSV column headers
	 *
	 * @return array
	 */
	protected function get_csv_columns() {
		// Get fields from a dummy group to extract headers
		$fields = $this->get_csv_fields( null );

		// Extract column names
		return array_keys( $fields );
	}

	/**
	 * Get CSV fields (columns and data) for a group
	 *
	 * @param Group|null $group Group object or null for headers only
	 * @return array Associative array of column => value pairs
	 */
	protected function get_csv_fields( $group ) {
		// Default fields structure
		$fields = [
			'ID'                       => '',
			'Title'                    => '',
			'Description'              => '',
			'Status'                   => '',
			'Publish Date'             => '',
			'Primary Leader Name'      => '',
			'Primary Leader Email'     => '',
			'Primary Leader User ID'   => '',
			'All Leaders'              => '',
			'Meeting Time'             => '',
			'Is Full'                  => '',
			'Kid Friendly'             => '',
			'Wheelchair Accessible'    => '',
			'Virtual'                  => '',
			'Location Label'           => '',
			'Location Address'         => '',
			'Geolocation'              => '',
			'Categories'               => '',
			'Types'                    => '',
			'Life Stages'              => '',
			'Public URL'               => '',
			'Registration URL'         => '',
			'Contact Action'           => '',
		];

		// Add custom meta fields from CP Connect
		$custom_meta = get_option( 'cp_group_custom_meta_mapping', [] );
		if ( ! empty( $custom_meta ) ) {
			foreach ( $custom_meta as $meta ) {
				$fields[ $meta['display_name'] ] = '';
			}
		}

		// If no group provided, apply filter and return structure with empty values (for headers)
		if ( ! $group ) {
			return apply_filters( 'cp_groups_csv_export_fields', $fields, $group );
		}

		// Populate with actual data
		$leaders = $group->get_leaders();
		$primary_leader = ! empty( $leaders ) ? $leaders[0] : [ 'id' => '', 'name' => '', 'email' => '' ];

		$fields['ID']                      = $group->post->ID;
		$fields['Title']                   = $group->get_title();
		$fields['Description']             = wp_strip_all_tags( $group->get_content( true ) );
		$fields['Status']                  = $group->post->post_status;
		$fields['Publish Date']            = get_the_date( 'Y-m-d H:i:s', $group->post->ID );
		$fields['Primary Leader Name']     = $primary_leader['name'];
		$fields['Primary Leader Email']    = $primary_leader['email'];
		$fields['Primary Leader User ID']  = $primary_leader['id'];
		$fields['All Leaders']             = $this->format_all_leaders( $leaders );
		$fields['Meeting Time']            = get_post_meta( $group->post->ID, 'time_desc', true );
		$fields['Is Full']                 = get_post_meta( $group->post->ID, 'is_group_full', true ) ? 'Yes' : 'No';
		$fields['Kid Friendly']            = get_post_meta( $group->post->ID, 'kid_friendly', true ) === 'on' ? 'Yes' : 'No';
		$fields['Wheelchair Accessible']   = get_post_meta( $group->post->ID, 'handicap_accessible', true ) === 'on' ? 'Yes' : 'No';
		$fields['Virtual']                 = get_post_meta( $group->post->ID, 'is_virtual', true ) === 'on' ? 'Yes' : 'No';
		$fields['Location Label']          = get_post_meta( $group->post->ID, 'location_label', true );
		$fields['Location Address']        = get_post_meta( $group->post->ID, 'location', true );
		$fields['Geolocation']             = get_post_meta( $group->post->ID, 'geolocation', true );
		$fields['Categories']              = implode( ', ', $group->get_categories() );
		$fields['Types']                   = implode( ', ', $group->get_types() );
		$fields['Life Stages']             = implode( ', ', $group->get_life_stages() );
		$fields['Public URL']              = get_post_meta( $group->post->ID, 'public_url', true );
		$fields['Registration URL']        = get_post_meta( $group->post->ID, 'registration_url', true );
		$fields['Contact Action']          = get_post_meta( $group->post->ID, 'action_contact', true );

		// Add custom meta field values
		if ( ! empty( $custom_meta ) ) {
			foreach ( $custom_meta as $meta ) {
				$fields[ $meta['display_name'] ] = get_post_meta( $group->post->ID, $meta['slug'], true );
			}
		}

		/**
		 * Filter the CSV export fields for a group
		 *
		 * Third-party extensions can use this filter to add or modify fields
		 * by adding/changing key-value pairs in the associative array.
		 *
		 * Example:
		 * add_filter( 'cp_groups_csv_export_fields', function( $fields, $group ) {
		 *     $fields['My Custom Field'] = get_post_meta( $group->post->ID, 'custom_field', true );
		 *     return $fields;
		 * }, 10, 2 );
		 *
		 * @param array $fields Associative array of column => value pairs
		 * @param Group $group  The group object being exported
		 * @return array Modified fields array
		 *
		 * @since 1.2.0
		 */
		return apply_filters( 'cp_groups_csv_export_fields', $fields, $group );
	}

	/**
	 * Format all leaders for CSV export
	 *
	 * @param array $leaders
	 * @return string
	 */
	protected function format_all_leaders( $leaders ) {
		if ( empty( $leaders ) ) {
			return '';
		}

		$formatted = [];
		foreach ( $leaders as $leader ) {
			$name = $leader['name'];
			$email = $leader['email'];
			$id = $leader['id'];

			if ( $id ) {
				$formatted[] = sprintf( '%s <%s> [ID:%d]', $name, $email, $id );
			} else {
				$formatted[] = sprintf( '%s <%s> [Custom]', $name, $email );
			}
		}

		return implode( '; ', $formatted );
	}
}
