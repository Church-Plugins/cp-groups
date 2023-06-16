<?php

namespace CP_Groups;

use RuntimeException;

class Ratelimit {
  
  protected $key;
  protected $data = array();
  protected $limit = 3;

  public function __construct($key) {
    $this->key = "cp_ratelimit_{$key}";
    $this->load_data(); 
  }

  protected function load_data() {
    $saved_data = get_option( $this->key );

    if( $saved_data ) {
      $this->data = $saved_data;
    }
    else {
      add_option( $this->key, $this->data );
    }
  }

  public function add_entry( $key, $limit = 3 ) {
    $today = date('m/d/Y');

    $data_today = $this->data[$today];

    if( ! $data_today ) {
      // clears data from yesterday
      $this->data = [];

      $data_today = array();
    }

    if( $data_today[ $key ] ) {
      $data_today[ $key ] += 1;
    }
    else {
      $data_today[ $key ] = 1;
    }

    $this->data[$today] = $data_today;

    update_option( $this->key, $this->data );

    if ($data_today[ $key ] > $limit ) {
      throw new RuntimeException( esc_html__( 'You have made too many requests', 'cp-groups' ), 429 );
      return false;
    }

    return true;
  }

  public function add_entries( $keys, $limit ) {
    foreach ( $keys as $key ) {
      $this->add_entry( $key, $limit );
    }
  }
}