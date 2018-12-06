<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of controller
 *
 * @author Luis Ramos
 */
require_once PATH . "controller/DeveloperProyectsController.php";

class controller extends DeveloperProyectsController {
    /* DEFINED FUNTIONS METHODS AND  REQUEST METHODS */

    private $FuntionMethods = array(
        "getFiles" => "POST",
        "getContentsFile"=>"POST",
        "saveFile"=>"POST",
        "removeFile"=>"POST",
        "removeFolder"=>"POST",
        "renameFile"=>"POST",
        "IsSession" => "GET"
    );

    function __construct() {
        parent::__construct();
    }

    public function checkMethod($function) {
//        echo $_SERVER['REQUEST_METHOD'];
        if (empty($function)) {

            return false;
        }
//        print_r($this->FuntionMethods);

        if (isset($this->FuntionMethods[$function]) && !empty($this->FuntionMethods[$function])) {
            $Request_Method = $this->FuntionMethods["{$function}"];
//            echo $Request_Method;
            if ($_SERVER['REQUEST_METHOD'] == $Request_Method) {
                return true;
            }
        }
        return false;
    }

    public function _return($json) {
        if ($json == null || $json == "" || !is_array($json)) {
            return json_encode(array("status" => 500, "data" => array()));
        } elseif (is_array($json)) {
            $return = json_encode($json);
            if (json_last_error() == 0) {
                return $return;
            } else {
                return json_encode($this->array_utf8_encode($json));
            }
        }
    }

    static function array_utf8_encode($dat) {
        if (is_string($dat))
            return utf8_encode($dat);
        if (!is_array($dat))
            return $dat;
        $ret = array();
        foreach ($dat as $i => $d)
            $ret[$i] = self::array_utf8_encode($d);
        return $ret;
    }

    function httpPost($url, $data) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

//    public function OK($param) {
//        
//    }
}
