<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of model
 *
 * @author ADMIM
 */
require PATH . 'dao/Dao.php';

class model extends Dao {

    public function __construct() {
        parent::__construct();
    }

    public function GET() {
        if (isset($this->_request["qr"]) && !empty($this->_request["qr"]) && (int) method_exists($this, $this->_request["qr"]) > 0) {
            return call_user_method($this->_request["qr"], $this);
        }
    }

    public function SET() {
       if (isset($this->_request["qr"]) && !empty($this->_request["qr"]) && (int) method_exists($this, $this->_request["qr"]) > 0) {
            return call_user_method($this->_request["qr"], $this);
        }
    }
    
    public function UNSETT() {
       if (isset($this->_request["qr"]) && !empty($this->_request["qr"]) && (int) method_exists($this, $this->_request["qr"]) > 0) {
            return call_user_method($this->_request["qr"], $this);
        }
    }

}
