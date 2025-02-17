<?php
namespace FreePBX\modules;
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

require_once(__DIR__.'/CustomComponent.class.php');

class Gestionrenvois implements \BMO {
    private $currentcomponent;
    private $FreePBX;
    
    public function __construct($freepbx = null) {
        if ($freepbx == null) {
            throw new \Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
        $this->currentcomponent = new CustomComponent();
    }
    
    public function install() {
        return true;
    }
    
    public function uninstall() {
        return true;
    }
    
    public function backup() {}
    
    public function restore($backup) {}
    
    public function doConfigPageInit($page) {}
    
    public function getCustomComponent() {
        return $this->currentcomponent;
    }
    
    public static function myConfigPageInits() {
        return array('gestionrenvois');
    }

    public function chownFreepbx() {
        $files = array(
            array('type' => 'file',
                'path' => __DIR__,
                'perms' => 0644)
        );
        return $files;
    }
}