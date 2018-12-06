<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of api
 *
 * @author Luis Ramos
 */
$dir = explode("/", $_SERVER['PHP_SELF']);
$api = $dir[1];
$projectName = $dir[2];
define("PATH", "{$_SERVER['DOCUMENT_ROOT']}/$api/$projectName/");
ini_set("display_errors", 1);
ini_set("memory_limit", "-1");
require './Rest.inc.php';
session_start();
date_default_timezone_set('America/Bogota');


class API extends REST {

    public $func = null;

    public function __construct() {
        $this->func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));   
        $date = date("Y-m-d H:i:s");
        parent::__construct();
        if ((int) method_exists($this, $this->func) > 0) {
            $this->{$this->func}();
            if (isset($this->responsed) && $this->responsed == 500) {
                $this->response("", 500);
            } elseif (isset($this->responsed) && $this->responsed == null) {
                if ($this->responsed == null && $this->session()) {
                    $this->response(null, 200);
                } else if ($this->responsed == null && !$this->session() && isset($this->_request["co"]) && $this->_request["co"] == "isSession") {
                    $this->response(null, 200);
                } else {
                    $this->response('', 500);
                }
            } else {
                if (is_array($this->responsed)) {
//                    file_put_contents("all_log.txt", print_r($this->responsed, true) . "\n", FILE_APPEND);
                } else {
//                    file_put_contents("all_log.txt", "[ ".$date." ] : ".$this->responsed . "\n", FILE_APPEND);
                }
                $this->response($this->responsed, 200);
            }
        } else {
            $this->response("ERROR NO METHOD EXIST", 500);
        }
    }

}

new API();
?>


