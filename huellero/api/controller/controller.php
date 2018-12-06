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
require_once PATH . "controller/huellero/huellero.php";
require_once PATH . "controller/usersController.php";

class controller extends usersController {

    public $huellero = null;

    function __construct() {
        $this->huellero = new huellero();
        parent::__construct();
    }

    function _return($json) {
        if ($json == null || $json == "" || !is_array($json)) {
            return json_encode(array("ERROR"));
        } elseif (is_array($json)) {
            $return = json_encode($json);
            if (json_last_error() == 0) {
                return $return;
            } else {
                return json_encode($this->array_utf8_encode($json));
            }
        }
    }
    
    function _get($_request, $cols = "*",$conditions = "",$encode = true) {
        $return = array();
        if (isset($_request) && !empty($_request) && is_string($_request)) {
            $return = $this->huellero->GET($_request, $cols,$conditions);
        }
        return ($encode)?$this->_return($return):$return;
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

    public function addCol($_request = null) {

        $return = $this->huellero->addCol('user', array("ID" => 'AutoNumber Primary Key'));
        return $this->_return($return);
    }

    function save($_request) {
        $return = array($_request["action"]);
        if (isset($_request["action"]) && method_exists($this, $_request["action"]) > 0)
            $return = $this->{$_request["action"]}($_request);
        return $this->_return($return);
    }

    function FileExist($_request) {
        $existe = false;
        if (isset($_request["file"]) && file_exists(str_replace("api/", "", PATH) . $_request["file"])) {
            $existe = true;
        }
        return $this->_return(array("file" => $existe));
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

    static function Keys($result) {
        $resulta = array();
        if (is_array($result)) {
            $keys = array_keys($result);
            foreach ($keys as $key => $value) {
                $keys = array_keys($result[$value]);
                break;
            }
            $resulta["data"] = $result;
            $resulta["key"] = $keys;
        }
        return $resulta;
    }

    function CreateTableReport() {
        $table = array('Reportes' => array(
                "id" => " COUNTER ",
                "user_id" => "text",
                "status" => "text",
                "name" => "text",
                "type" => "text",
                "daySemana" => "text",
                "mes" => "text",
                "Dmes" => "text",
                "hour" => "text",
                "mails" => "LONGTEXT",
                "group" => "LONGTEXT",
                "user" => "LONGTEXT",
                "opReport" => "text",
                "PRIMARY KEY" => " (`id`)"));
        $this->huellero->createTable($table);
    }

    function UploadFile($pathFile) {
        if (file_exists(PATH . $pathFile)) {
            $json = shell_exec('java -jar "' . PATH . 'controller/Lib/UploadFile/uploadFile.jar" "' . PATH . $pathFile . '"');
            return $json;
        } else {
            return array("NO");
        }
    }

    function Mail($options) {
        if ($options != "" && isset($options['dir']) && isset($options["mails"]) && isset($options["subject"]) && isset($options["message"])) {
            $Files = array('path' => $options['dir'], 'file_name' => $options['FileName']);
            $mail = array(
                'name' => $options["name"],
                'from' => 'Huellero@onlinedst.com',
                'fromName' => 'Huellero@onlinedst.com',
                'addresses' => $options["mails"],
                'subject' => $options["subject"],
                'message' => "<p>{$options["message"]}</p>",
                'attachment' => $Files
            );
            $return = array($this->httpPost("onlinedst.com/send_mail/SimpleSendInfo", $mail));
        } else {
            $return = array("ERROR OPTIONS MAIL");
        }
        return $return;
    }

}
