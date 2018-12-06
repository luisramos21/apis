<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of usersController
 *
 * @author Luis Ramos
 */
require_once PATH . "controller/ReportsController.php";

class usersController extends ReportsController {

    public function __construct() {
        parent::__construct();
    }

    public function session($_request = null) {
        error_reporting(E_ALL);
        if (!isset($_SESSION["huellero"]) && isset($_request["user"]) && isset($_request["password"]) && !empty($_request["user"]) && !empty($_request["password"])) {

            $session = $this->huellero->session($_request["user"], $_request["password"]);
            if (!empty($session)) {
                $_SESSION["huellero"] = json_encode($session);
            }

            return $this->_return($session);
        } else if (isset($_SESSION["huellero"]) && !empty($_SESSION["huellero"]) && !is_null($_SESSION["huellero"]) && !isset($_request["logout"])) {
            return $this->_return(json_decode($_SESSION["huellero"], true));
        } else if (isset($_request["logout"])) {
            unset($_SESSION['huellero']);
            session_destroy();
            return json_encode(array('LOGOUT' => true));
        } else {
            return json_encode(array("NO_SESSION"));
        }
    }

    public function cambiarpass($_request) {
        $return = $this->huellero->cambiarpass($_request['Userid'], $_request['password']);
        return $this->_return(array($return));
    }

    public function getusers($_request = null) {
        //$log = $this->addCol();
        //file_put_contents("log.txt", $log);
        $id = (isset($_request["user"])) ? (is_array($_request["user"])) ? implode(",", $_request["user"]) : $_request["user"] : "";
        $table = (isset($_request["table"])) ? $_request["table"] : false;
        $call = (isset($_request["call"])) ? $_request["call"] : "";
        $deps = (isset($_request["deps"])) ? $_request["deps"] : "";

        $return = $this->huellero->getusers($id, $table, $deps);
        if ($call != "" && (int) method_exists($this, $call) > 0) {
            $return = $this->$call($return, $_request);
        }
        return $this->_return($return);
    }

    public function addUser($_request = null) {
        unset($_request["action"]);
        $return = $this->huellero->SAVE('user', $_request);
        return $this->_return($return);
    }

    private function user($result, $_request) {

        if (isset($_request["call"])) {
            foreach ($result as $key => $value) {
                if (isset($value["ID"]))
                    $result[$key]["_id"] = $value["ID"];
            }
        }
        return $this->Keys($result);
    }

    public function getdeps($_request = null) {

        $return = $this->huellero->Deps();
        if (isset($_request["call"]) && !empty($_request["call"]) && (int) method_exists($this, $_request["call"]) > 0) {
            $return = $this->{$_request["call"]}($return);
        }
        return $this->_return($return);
    }

    private function Jobs($result) {
        return $this->Keys($result);
    }

    private function depsKeys($result) {
        return $this->Keys($result);
    }

    public function getHoursJob($_request = null) {

        $result = $this->huellero->HoursJob($_request["group"], $_request["user"], $_request["desde"], $_request["hasta"]);

        if ($result == "") {
            $result = array();
        } else if (is_array($result)) {

            $results = array();
			#EN EL CASO DE REGISTROS REPETIDOS SENA ENTRADAS O SALIDAS
			$minimo = 900;
            for ($j = 0; $j < count($result) - 1; $j++) {

                $timea = new DateTime($result[$j]["CheckTime"]);
                $timeb = new DateTime($result[$j + 1]["CheckTime"]);
				
				$diff = $timeb->getTimestamp() - $timea->getTimestamp();
				
                if ($diff >0 &&  $diff < $minimo) {
                    unset($result[$j]);
                    $result = array_values($result);
                }
            }


            for ($i = 0; $i < count($result) - 1; $i = $i + 2) {
                $in = $result[$i];
                $out = $result[$i + 1];

                if ($in["CheckType"] == "I" && $out["CheckType"] == "O") {
                    $results[$i]["IN"] = $in;
                    $results[$i]["OUT"] = $out;
                } else {

                    if ($in["CheckType"] == "I" && $out["CheckType"] == "I") {
                        $results[$i]["IN"] = $in;
                        $i = $i - 1;
                    }
                    if ($in["CheckType"] == "O" && $out["CheckType"] == "O") {
                        $results[$i]["OUT"] = $in;
                        $i = $i - 1;
                    }

                    if ($in["CheckType"] == "O" && $out["CheckType"] == "I") {
                        $results[$i]["OUT"] = $in;
                        $i = $i - 1;
                    }
                }
                /* if (isset($value["CheckTime"]) && isset($value["CheckType"])) {
                  $date_in = new DateTime($value["CheckTime"]);
                  $date = $date_in->format('Y-m-d');
                  $in_out = "IN";
                  if ($value["CheckType"] == "O") {
                  $in_out = "OUT";
                  }
                  $results[$date . $value["Userid"]][$in_out] = $result[$key];
                  } */
            }

            $hoursJobs = array();
            foreach ($results as $key => $value) {
                if (isset($value["IN"]) && isset($value["OUT"])) {
                    $in = $value["IN"]["CheckTime"];
                    $out = $value["OUT"]["CheckTime"];
                    $date_in = new DateTime($in);
                    $hour_in = $date_in->format('H');
                    $date_out = new DateTime($out);

                    $hour_outs = (int) $date_out->format('H');
                    if ($hour_outs > 13) {
                        // $date_out->modify('-1 hour');
                    }
                    ##PARA CALCULAR LA HORAS TRABAJADAS DESDE LAS 08:00 AM
                    if ($hour_in < 8) {
                        //$date_in->setTime(8, 0, 0);
                    }
                    $hour_out = $date_in->diff($date_out);
                    if (!isset($results[$key]["HOURS"]))
                        $results[$key]["HOURS"] = 0;
                    $results[$key]["HOURS"] += $this->Hras($hour_out->format("%H:%I:%S")); //
                }
            }
            $hours = 0;
            foreach ($results as $key => $value) {
                if (isset($value["IN"]) && isset($value["OUT"]) && isset($value["HOURS"])) {
                    $hours += $value["HOURS"];
                }
            }

            $response = array();
            foreach ($results as $key => $value) {
                $entrada = "NO TIENE";
                $salida = "NO TIENE";
                $nombre = "";
                $dept = "";
                $hora = 0;
                if (isset($value["IN"]["CheckTime"])) {
                    $entrada = $value["IN"]["CheckTime"];
                }
                if (isset($value["OUT"]["CheckTime"])) {
                    $salida = $value["OUT"]["CheckTime"];
                }
                if (isset($value["OUT"]["Name"])) {
                    $nombre = $value["OUT"]["Name"];
                }
                if (isset($value["IN"]["Name"])) {
                    $nombre = $value["IN"]["Name"];
                }
                if (isset($value["OUT"]["DeptName"])) {
                    $dept = $value["OUT"]["DeptName"];
                }
                if (isset($value["IN"]["DeptName"])) {
                    $dept = $value["IN"]["DeptName"];
                }
                if (isset($value["HOURS"])) {
                    $hora = $value["HOURS"];
                }
                $response[] = array(
                    "Trabajador" => $nombre,
                    "Area" => $dept,
                    "Entrada" => $entrada,
                    "Salida" => $salida,
                    "Horas Trab" => $hora);
            }
            $keys = $this->Keys($response);
            $dataForUser = array();
            foreach ($response as $key => $value) {
                if (isset($value["Trabajador"])) {
                    if (!isset($dataForUser[$value["Trabajador"]])) {
                        $dataForUser[$value["Trabajador"]] = "";
                    }
                    $dataForUser[$value["Trabajador"]] += $value["Horas Trab"];
                }
            }
            foreach ($dataForUser as $key => $value) {
                unset($dataForUser[$key]);
                $dataForUser[] = array("Trabajador" => $key, "Horas Trabajadas" => $value);
            }

            $result = array(
                "info" => $result, "data" => $response, "JobsHours" => array("data" => $dataForUser, "keys" => array("Trabajador", "Horas Trabajadas")), "keys" => $keys["key"], "TOTAL_HORAS" => $hours);
        }
        return $this->_return($result);
    }

    function Hras($date) {
        $date = explode(":", $date);
        $hours = $date[0];
        $minute = $date[1];
        $secunds = $date[2];

        $minute += round($secunds / 60, 2);
        $minute = round($minute / 60, 2);
        $hours += $minute;
        return $hours;
    }

}
