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
error_reporting(E_ALL);
define('PROYECT_NAME', $dir[1]."/".$dir[2]);
$last = $_SERVER["DOCUMENT_ROOT"][strlen($_SERVER["DOCUMENT_ROOT"])-1];

if($last =='s'){
    $last = "/";
}

define("PATH", $_SERVER["DOCUMENT_ROOT"] . $last.PROYECT_NAME."/api/");

ini_set("display_errors", 1);
ini_set("memory_limit", "-1");
require './Rest.inc.php';
session_start();
//unset($_SESSION['huellero']);

date_default_timezone_set('America/Bogota');
header("Access-Control-Allow-Origin: *");

class API extends REST {

    public $func = null;

    public function Process() {
        $this->func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
        require PATH."controller/controller.php";
        $controller = new controller();
        
        if ((int) method_exists($controller, $this->func) > 0) {
            $this->response($controller->{$this->func}((array)$this->_request), 200);
        } else {
            $this->response("", 500);
        }
    }
}

$api = new API();
$api->Process();
?>
