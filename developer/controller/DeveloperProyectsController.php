<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeveloperController
 *
 * @author admin
 */
require_once PATH . "model/proyects.php";
require_once PATH . "controller/FilesController.php";

class DeveloperProyectsController extends FilesController {

    public function __construct() {
        parent::__construct();
    }

    function saveProyect($_request) {
        $response = array("status" => false);
        if ($this->session() && !empty($_request)) {

            $model = new developer_proyects();
            $response = $model->save_proyect($_request);
        }

        return $this->_return($response);
    }

    function removeProyect($_request) {
        
        $response = array("status" => false);
        if ($this->session() && isset($_request['proyect'])) {

            $model = new developer_proyects();
            $response = $model->remove($_request['proyect']);
            if(isset($_request['removeFiles'])){
                //implement remove files 
            }
        }

        return $this->_return($response);
    }

}
