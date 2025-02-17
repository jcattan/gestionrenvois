<?php
namespace FreePBX\modules;
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

require_once(__DIR__ . '/../../libraries/components.class.php');

class CustomComponent extends \Component {
    protected $active = '';
    
    public function __construct() {
        parent::__construct("gestionrenvois");
    }
    
    public function generateconfigpage($loadView=null, $variables = array()) {
        if (!isset($this->active)) {
            $this->active = '';
        }
        return parent::generateconfigpage($loadView, $variables);
    }
}