<?php

namespace CP_Groups\Setup\Blocks;
use CP_Groups\Setup\Blocks\Block;

class Query extends Block {
    public $name = 'query';
    public $is_dynamic = false;

    public function __construct() {
      parent::__construct();
    }
}