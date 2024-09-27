<?php

namespace CP_Groups\Setup\Blocks;

use Exception;
use WP_Block_Type;

abstract class Block {
  /**
   * The directory for the block's bundled resources
   * 
   * @var string
   */
  protected $block_dir;

  /**
   * The URL of the block's bundled resources
   * 
   * @var string
   */
  protected $block_url;

  /**
   * The block namespace.
   * 
   * @var string
   */
  protected $_namespace = 'cp-groups/';

  /**
   * The block name
   * 
   * @var string
   */
  public $name;

  /**
   * The full namespaced block name, e.g. cp-groups/spacer
   * 
   * @var string
   */
  public $block_name;

  /**
   * Specifies whether a block is dynamic or not
   * 
   * @var boolean
   */
  public $is_dynamic;

  /**
   * Single instance
   * 
   * @var Block
   */
  protected static $_instance;

  /**
   * Class constructor
   */
  protected function __construct() {
    if( !$this->name ) {
      throw new Exception( "Block must have a name" );
    }

    $this->block_name = $this->_namespace . $this->name;
    $this->block_dir = CP_GROUPS_PLUGIN_DIR . 'dist/blocks/' . $this->name;
    $this->block_url = CP_GROUPS_PLUGIN_URL . 'dist/blocks/' . $this->name;

    if( ! file_exists( $this->block_dir ) ) {
      throw new Exception( "Invalid block configuration. No build directory found for " . $this->name );
    }

    add_action( 'init', [ $this, 'register_block' ] );
  }

  /**
   * Initialize block
   */
  public static function init() {
    $class = get_called_class();
    if( ! self::$_instance instanceof Block ) {
      self::$_instance = new $class();
    }
    return self::$_instance;
  }

  /**
   * Registers block on the server
   */
  public function register_block() {
    if( $this->is_dynamic ) {
      $block_args = apply_filters( "wp-block-cp-groups-{$this->name}_block_args", array( 'render_callback' => [ $this, 'render' ] ) ); 
      register_block_type_from_metadata( $this->block_dir, $block_args );
    }
    else {
      register_block_type( $this->block_dir );
    }
  }
}