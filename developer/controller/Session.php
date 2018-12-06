<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Session
 *
 * @author admin
 */
class Session {

    public function __construct() {
        
    }

    public function session() {
        return true;
    }
    
    public function isSession($_request=null) {
        /*$status= isset($_SESSION['session]) && !empty($_SESSION['session])*/
        $status = true;
        return $this->_return(array("status"=>$status));
    }

    public function getSession() {
        $session_data = array("user" => 12);
        $session_data = json_encode($session_data);

        return $session_data;
    }

    public function setSession($data) {
        $_SESSION['session'] = json_encode($data);
    }

    public function removeSession() {
        $_SESSION['session'] = '';
    }
    

}
