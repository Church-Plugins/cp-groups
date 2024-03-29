<?php
namespace CP_Groups\Integrations;

class _Init {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * @var CP_Locations
	 */
	public $cp_locations = false;

	/**
	 * Store the active service instances
	 *
	 * @array
	 */
	public $active;

	/**
	 * Only make one instance of _Init
	 *
	 * @return _Init
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof _Init ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor: Add Hooks and Actions
	 *
	 */
	protected function __construct() {
		$this->actions();
	}

	protected function actions() {
		$this->load_integrations();
	}

	/** Actions Methods **************************************/

	public function load_integrations() {
		if ( function_exists( 'cp_locations' ) ) {
			$this->cp_locations = CP_Locations::get_instance();
		}

		do_action( 'cp_groups_load_integrations' );
	}

}
