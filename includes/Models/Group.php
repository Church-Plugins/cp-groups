<?php

namespace CP_Groups\Models;

use ChurchPlugins\Exception;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Group {
	
	public $type;
	public $post_type;

	public function init() {
		$this->type = 'group';
		$this->post_type = 'cp_group';
	}	
	
	public function __construct( $object = false ) {
		$this->init();

		if ( ! $object ) {
			return;
		}

		foreach ( get_object_vars( $object ) as $key => $value ) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Setup instance using an origin id
	 * @param $origin_id
	 *
	 * @return bool | static self
	 * @throws Exception
	 * @since  1.0.0
	 *
	 * @author Tanner Moushey
	 */
	public static function get_instance_from_origin( $origin_id ) {
		$origin_id = apply_filters( 'cp_origin_id', absint( $origin_id ) );

		if ( ! $origin_id ) {
			return false;
		}

		if ( ! get_post( $origin_id ) ) {
			throw new Exception( 'That post does not exist.' );
		}

		if ( static::get_prop('post_type' ) !== get_post_type( $origin_id ) ) {
			throw new Exception( 'The post type for the provided ID is not correct.' );
		}

		$post_status = get_post_status( $origin_id );
		if ( 'auto-draft' == $post_status ) {
			throw new Exception( 'No instance retrieved for auto-draft' );
		}
		
		$class = get_called_class();
		return new $class();
	}
	

	public static function get_prop( $var ) {
		$class = get_called_class();
		$instance = new $class();

		if ( property_exists( $instance, $var ) ) {
			return $instance->$var;
		}

		return '';
	}	
}