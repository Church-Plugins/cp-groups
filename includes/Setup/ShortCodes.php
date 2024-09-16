<?php

namespace CP_Groups\Setup;

use CP_Groups\Templates;

/**
 * Setup plugin ShortCodesialization
 */
class ShortCodes {

	/**
	 * @var ShortCodes
	 */
	protected static $_instance;

	/**
	 * Only make one instance of ShortCodes
	 *
	 * @return ShortCodes
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof ShortCodes ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 */
	protected function __construct() {
		add_shortcode( 'cp-groups', [ $this, 'groups_cb' ] );
		add_shortcode( 'cp-groups-filter', [ $this, 'groups_filter_cb' ] );
	}

	protected function actions() {}

	/** Actions ***************************************************/

	/**
	 * Print groups
	 * 
	 * @param $atts
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function groups_cb( $atts ) {
		ob_start();
		Templates::get_template_part( "shortcodes/group-list", $atts );
		return ob_get_clean();
	}

	/**
	 * Print groups filters
	 * 
	 * @param $atts
	 *
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function groups_filter_cb( $atts ) {
		ob_start();
		Templates::get_template_part( "shortcodes/filter" );
		return ob_get_clean();
		
	}
	
}
