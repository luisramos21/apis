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
date_default_timezone_set('America/Bogota');
class _apiShell {
    public function __construct() {
        global $argv;
        if (isset($argv)) {
            $base = "";
            foreach ($argv as $arg) {
                $e = explode("=", $arg);
                if (count($e) == 2){
                    $_GET[$e[0]] = $e[1];
                }else{
                    $base = $e[0];
                }
            }
            $port = (gethostname()=='DST3240-1')?"":":8080";
            $url = "http://localhost{$port}/api/huellero/api/ExcelReport";
			//echo $url;
			//sleep(5);
            $status = $this->Curl($url, $_GET);
            file_put_contents(str_replace('api.php', "status.txt", $base),"$status\n", FILE_APPEND);
        }
    }
    function Curl($url, $fields) {
        
        //open connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $result = curl_exec($ch);
        $error = curl_error($ch);
		if(!empty($error)){
			file_put_contents('error_shell.log',$error);
		}
        curl_close($ch);
        return $result!=''?$result:$error;        
        
    }

}

new _apiShell();
