<?php

namespace CP_Groups\Setup\Taxonomies;

/**
 * Setup plugin initialization for Taxonomies
 */
class Init {

	/**
	 * @var Init
	 */
	protected static $_instance;

	/**
	 * Setup Type taxonomy
	 *
	 * @var Type
	 */
	public $type;

	/**
	 * Setup Group Category taxonomy
	 *
	 * @var Type
	 */
	public $category;

	/**
	 * Only make one instance of Init
	 *
	 * @return Init
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof Init ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 * Run includes and actions on instantiation
	 *
	 */
	protected function __construct() {
		$this->includes();
		$this->actions();
	}

	/**
	 * Plugin init includes
	 *
	 * @return void
	 */
	protected function includes() {}

	/**
	 * Plugin init actions
	 *
	 * @return void
	 * @author costmo
	 */
	protected function actions() {
		add_action( 'init', [ $this, 'register_taxonomies' ], 5 );
	}

	/**
	 * Return array of taxonomy objects
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_objects() {
		return [ $this->type ];
	}

	/**
	 * Return array of taxonomies
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public function get_taxonomies() {
		$tax = [];

		foreach( $this->get_objects() as $object ) {
			$tax[] = $object->taxonomy;
		}

		return $tax;
	}

	public function register_taxonomies() {

		$this->type = Type::get_instance();
		$this->category = Category::get_instance();

		$this->type->add_actions();
		$this->category->add_actions();
		do_action( 'cp_register_taxonomies' );

	}

}
