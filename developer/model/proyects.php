<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of developer
 *
 * @author admin
 */

require_once PATH . "model/model.php";

class developer_proyects extends model{
    
    private $table = 'proyects';
    
    function save_proyect($_request){
        $result = array("status"=>false);
        $result["status"] = $this->save($$this->table, $_request);
        return $result;
    }
    
    function remove($id) {
        return $this->_delete($this->table,$id);
    }
   
}
