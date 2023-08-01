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

    // $this->register_files();

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
   * Registers a Gutenberg block
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

  /**
   * Handles rendering a dynamic block
   */
  protected function register_dynamic_block() {
    // $asset_file = include( $this->block_dir . '/index.asset.php');
    // $script_name = 'cp-groups-block-script-' . $this->name;
    // $style_name  = 'cp-groups-block-style-'  . $this->name;
    // $editor_style_name = 'cp-groups-block-editor-style-'  . $this->name;

    // wp_register_script(
    //   $script_name,
    //   $this->block_url . '/index.js',
    //   $asset_file['dependencies'],
    //   $asset_file['version']
    // );

    // wp_register_style(
    //   $style_name,
    //   $this->block_url . '/style-' . $this->name . '.css'
    // );

    // wp_register_style(
    //   $editor_style_name,
    //   $this->block_url . '/' . $this->name . '.css'
    // );

    // register_block_type( $this->block_name, array(
    //   'api_version' => 3,
    //   'editor_script_handles' => array( $script_name ),
    //   'editor_style_handles' => array( $editor_style_name ),
    //   'style_handles' => array( $style_name ),
    //   'render_callback' => [ $this, 'render' ]
    // ) );

    // register_block_type_from_metadata( $this->block_dir, array(
    //   'render_callback' => [ $this, 'render' ] 
    // ) );
  }

  protected function register_files() {
    // $editor_style = "/{$this->name}.css";
    // if( file_exists( $this->block_dir . $editor_style ) ) {
    //   wp_register_style( 'cp-groups-block-editor-style-' . $this->name, $this->block_url . $editor_style );
    // }

    // $style = "/style-{$this->name}.css";
    // if( file_exists( $this->block_dir . $style ) ) {
    //   wp_register_style( 'cp-groups-block-style-' . $this->name, $this->block_url . $style );
    // }

    // $editor_script = "/index.js";
    // if( file_exists( $this->block_dir . $editor_script ) ) {
    //   wp_register_script( 'cp-groups-block-script-' . $this->name,  $this->block_url . $editor_script );
    // }
  }
}