<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of controller
 *
 * @author ADMIM
 */
require PATH . 'model/model.php';
ini_set("upload_max_filesize", "1000000M");
ini_set("post_max_size", "1000000M");

class controller extends model {

    private $excel = null;
    private $targeFile = null;

    public function __construct() {
        parent::__construct();
        if (isset($this->responsed))
            return json_encode($this->responsed);
        else {
            return 404;
        }
    }

    function utf8ize($mixed) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } else if (is_string($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }

    function Trim($param, $val_int = false) {
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $val) {
                        unset($param[$key][$k]);
                        $param[$key][trim($k)] = ($val_int) ? (int) $val : $val;
                    }
                }
            }
        }
        return $param;
    }

    function _replace(&$toData) {
        $andle1 = array('<', ">");
        $andle2 = array('"', "'");
        if (is_array($toData) && isset($toData["data"]) && is_array($toData["data"])) {
            foreach ($toData["data"] as $key => $value) {
                foreach ($value as $key2 => $val) {
                    $toData["data"][$key][$key2] = str_replace($andle1, $andle2, $toData["data"][$key][$key2]);
                }
            }
        }
    }

    function _DATA($toData) {
        $this->_replace($toData);
        $this->responsed = json_encode($toData);
        $this->verifiqueErrorjson();
    }

    function defaut() {

        if (!empty($this->toData)) {
            $this->_DATA($this->toData);
        } else {
            $this->_DATA(array("data" => -1));
        }
    }

    function verifiqueErrorjson() {
        if (json_last_error() > 0)
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    echo ' - Sin errores';
                    break;
                case JSON_ERROR_DEPTH:
                    echo ' - Excedido tamaño máximo de la pila';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo ' - Desbordamiento de buffer o los modos no coinciden';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo ' - Encontrado carácter de control no esperado';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo ' - Error de sintaxis, JSON mal formado';
                    break;
                case JSON_ERROR_UTF8:
                    if (isset($this->toData) && !empty($this->toData)) {
//                        echo ' - Caracteres UTF-8 malformados, posiblemente están mal codificados';
                        $this->toData = $this->utf8ize($this->toData);
                        $this->responsed = json_encode($this->toData);
                    }
                    break;
                default:
                    echo ' - Error desconocido';
                    break;
            }
    }

    function session($ar = false) {
        if (isset($_SESSION["login_requisiciones"]) && !empty($_SESSION["login_requisiciones"])) {
            $data = json_decode($_SESSION["login_requisiciones"], $ar);
            return $data->data[0];
        } else {
            $this->responsed = "";
            return false;
        }
    }

    function coIn() {

        if (!empty($this->toData) && gettype($this->toData) == "array") {
            $_SESSION["login_requisiciones"] = json_encode($this->toData);
            $this->_DATA($this->toData);
        } else {
            $this->_DATA(array("0" => "ERROR"));
        }
    }

    function isSession() {
        if (!empty($this->toData) && $this->toData != "ERROR SQL EMPTY") {
            $this->_DATA($this->toData);
        } else {
             $this->_DATA(array("status" => 500));
        }
    }

    function logout() {
        $session = $this->session();
        if ($session != "" && $session != null) {
            $_SESSION["login_requisiciones"] = "";
            session_destroy();
            $this->responsed = "OK";
        } else {
            $this->responsed = "ERROR LOGOUT";
        }
    }

    function inquirie() {
        if (!empty($this->toData)) {

            if (is_array($this->toData) && $this->_request["status"] != 0 && $this->_request["status"] != 5 && $this->_request["status"] != 6 && $this->is_req == true) {

                foreach ($this->toData["data"] as $k => $value) {
                    $now = new DateTime();
                    $expiration_date = new DateTime($value['F_aprob']);
                    $expiration_date2 = new DateTime($value['F_aprob']);
                    $expiration_date->add(new DateInterval('P' . $value['max_day'] . 'D'));
                    $expiration_date2->add(new DateInterval('P' . ($value['max_day'] - 2 >= 0 ? $value['max_day'] - 2 : '0') . 'D'));
                    $ult = count($this->toData["data"][$k]);
                    $tmp = $this->toData["data"][$k]["Id"];

                    $this->toData["data"][$k]["F_venci"] = (($value["priority_id"] == 5 && $value["limit_date"] != '0000-00-00') ? $value["limit_date"] : $expiration_date2->format('Y-m-d'));
                    $expiration_date2 = ($value["limit_date"] != "0000-00-00" && $value['priority_id'] == 5) ? new DateTime($value["limit_date"]) : $expiration_date2;

                    $this->toData["data"][$k]["is_venci"] = ($now >= $expiration_date2) ? true : false;

                    if (isset($value['F_aprob'])) {
                        $expiration_date = new DateTime($value['F_aprob']);
                        $this->toData["data"][$k]['F_aprob'] = $expiration_date->format('Y-m-d');
                    }
                    if (isset($value['F_requi'])) {
                        $date = new DateTime($value['F_requi']);
                        $this->toData["data"][$k]['F_requi'] = $date->format('Y-m-d');
                    }
                    if (isset($value['F_complete']) && $value['F_complete'] != "" && $value['F_complete'] != null) {
                        $date = new DateTime($value['F_complete']);
                        if ($value['F_complete'] != "0000-00-00 00:00:00") {
                            $this->toData["data"][$k]['F_complete'] = $date->format('Y-m-d');
                        } else {
                            $this->toData["data"][$k]['F_complete'] = "";
                        }
                    }
                }

                foreach ($this->toData["KEY"] as $key => $value) {
                    switch ($value) {
                        case "max_day":
                        case "priority_id":
                        case "limit_date":
                            unset($this->toData["KEY"][$key]);
                            break;
                        case "F_complete":
                            if ($this->_request["status"] == 1 || $this->_request["status"] == 2 || $this->_request["status"] == 3)
                                unset($this->toData["KEY"][$key]);
                            break;
                        case "id_process":
                            if ($this->_request["status"] == 4 || $this->_request["status"] == 3 || (($this->_request["status"] == 1 || $this->_request["status"] == 2) && isset($this->_request["is_process"])) && ($this->_request["is_process"] == "false" || $this->_request["is_process"] == false))
                                unset($this->toData["KEY"][$key]);
                            break;
                    }
                }

                $this->toData["KEY"] = array_values($this->toData["KEY"]);

                if ($this->_request["status"] != 4 && $this->_request["status"] != 3 && $this->_request["status"] != 1) {                    

                    foreach ($this->toData["KEY"] as $index => $column) {
                        if ($column == "F_aprob") {
                            $keys = $this->toData["KEY"];
                            $this->toData["KEY"][$index + 1] = "F_venci";
                            $this->toData["KEY"][$index + 2] = $keys[$index + 1]; //Usuario
                            $this->toData["KEY"][$index + 3] = $keys[$index + 2]; //Observaciones
                            $this->toData["KEY"][$index + 4] = $keys[$index + 3]; //Acciones
                            $this->toData["KEY"][$index + 5] = $keys[$index + 4]; //estado
                            break;
                        }
                    }
                    array_push($this->toData["KEY"], "is_venci");
                }
            } else if (is_array($this->toData) && $this->toData["data"] != 0) {
                foreach ($this->toData["KEY"] as $key => $value) {
                    switch ($value) {
                        case "max_day":
                        case "id_process":
                        case "limit_date":
                        case "priority_id":
                        case "F_venci":
                            unset($this->toData["KEY"][$key]);
                            break;
                        case "F_complete":
                            if ($this->_request["status"] == 0 || $this->_request["status"] == 5)
                                unset($this->toData["KEY"][$key]);
                            break;
                        case "F_aprob":
                            unset($this->toData["KEY"][$key]);
                            break;
                    }
                }

                foreach ($this->toData["data"] as $key => $value) {
                    if (isset($value['F_requi'])) {
                        $date = new DateTime($value['F_requi']);
                        $this->toData["data"][$key]["F_requi"] = $date->format('Y-m-d');
                    }
                }
            }
            $this->_DATA($this->toData);
        } else {
            $this->_DATA(array("data" => 0));
        }
    }

    function nextPurc() {
        if (!empty($this->toData)) {
            $session = $this->session();

            if ($this->toData == 0) {
                if ($session->id < 10)
                    $session->id = "0" . $session->id;

                $this->responsed = json_encode(array("data" => $session->id . str_pad("1", 5, "0", STR_PAD_LEFT)));
            } elseif (isset($this->toData["data"][0]["next"]) && $this->toData["data"][0]["next"] == null || $this->toData["data"][0]["next"] == "") {
                $numbers = 5;

                if (strlen($session->id) >= 3) {
                    $numbers -= 1;
                }
                if ($session->id < 10)
                    $session->id = "0" . $session->id;

                $this->responsed = json_encode(array("data" => $session->id . str_pad("1", $numbers, "0", STR_PAD_LEFT)));
            } else {
                $numbers = 7;
                if (strlen($session->id) >= 3) {
                    $numbers -= 1;
                }

                $this->toData["data"][0]["next"] = str_pad(($this->toData["data"][0]["next"]) + 1, $numbers, "0", STR_PAD_LEFT);
                $this->_DATA($this->toData);
            }
        } else {
            $this->_DATA($this->toData);
        }
    }

    function cols() {
        if (!empty($this->toData)) {
            $this->_DATA($this->toData);
        } else {
            $this->responsed = "ERROR DATA EMPTY";
        }
    }

    function normalizeUserId($userId) {
        return str_pad($userId, 2, "0", STR_PAD_LEFT);
    }

    function penProcess() {
        if (!empty($this->toData) && gettype($this->toData) == "array") {

            foreach ($this->toData["data"] as $key => $value) {
                $this->toData["data"][$key]["Cant_Pendiente"] = "";
            }
            array_push($this->toData["KEY"], 'Cant_Pendiente');
            $this->_DATA($this->toData);
        } else {
            $this->_DATA(array("0" => "ERROR"));
        }
    }

    function ConfirmProcess() {
        if (!empty($this->toData) && is_array($this->toData)) {
            $this->toData = $this->toData["data"][0]["cnt"];
            $this->_DATA($this->toData);
        } else {
            $this->_DATA(array("0" => "ERROR"));
        }
    }

    function Indicato() {
        if (!empty($this->toData) && isset($this->toData["data"]) && is_array($this->toData["data"])) {

            $data = array();
            //group by year a year to mes to mes
            foreach ($this->toData["data"] as $key => $value) {
                $completeDate = new DateTime($value['complete_date']);
                $year = $completeDate->format("Y");
                $mes = $completeDate->format("m");

                $Nextmes = 0;

                if (isset($this->toData["data"][$key + 1]['complete_date'])) {
                    $NextcompleteDate = new DateTime($this->toData["data"][$key + 1]['complete_date']);
                    $Nextmes = $completeDate->format("m");
                }

                $data[(int) $year][(int) $mes][] = $value;
                //if existe diciembre
                if (!isset($data[(int) $year][11]) && $Nextmes == 12) {
                    $data[(int) $year][11] = array();
                }
            }
            //calculate Cumplimiento
            $response = array();

            foreach ($data as $year => $yearsValues) {
                foreach ($yearsValues as $month => $valu) {
                    $onTime = 0;
                    $count = 0;
                    $ontimeArray = array();
                    $ontimeArray2 = array();
                    foreach ($valu as $key => $value) {
                        if (isset($value["Dias"]) && isset($value["max_day"]) && $value["Dias"] <= $value["max_day"]) {
                            $onTime++;
                            $ontimeArray[] = $value;
                        } else {
                            $ontimeArray2[] = $value;
                        }
                        $count++;
                    }
                    $percentage = 0;
                    if ($count > 0) {
                        $percentage = round($onTime / $count * 100, 1);
                    }
                    $response[$year][$month] = array('ontime' => $onTime, "data" => $ontimeArray, "all" => $ontimeArray2, "total" => $count, 'porcent' => $percentage);
                }
                unset($data[$year]);
            }
            $this->_request["ta"] = "products";
            $this->_request["count"] = true;

            $this->sql = $this->_all();
            $_data = $this->connect();
            //llenar si el mes no es ta cumplido
            $mes = (int) date("m");
            for ($i = 1; $i <= $mes; $i++) {
                foreach ($response as $_year => $_month) {
                    if (!isset($response[$year][$i])) {
                        $response[$year][$i] = array('ontime' => 0, "data" => array(), "all" => array(), "total" => 0, 'porcent' => 0);
                    }
                }
            }

            $this->_DATA(array("complete" => $response, "pendient" => $_data));
        } else {
            $this->_DATA(array("data" => 0));
        }
    }

    function sql() {
        $this->responsed = json_encode($this->toData);
    }

    function changeProvider() {
//        file_put_contents("dataProvider.txt", print_r($this->toData,true));
        if (isset($this->toData["data"]) && !empty($this->toData["data"]) && is_array($this->toData["data"]) && $this->toData["data"] != 0) {
            $data = $this->toData["data"][0];
            $id = $this->_request["id"];
            $aprove = $this->_request["aprove"];
            unset($this->_request);

            if (isset($aprove) && !empty($aprove)) {

                $this->_request["ta"] = "products";
                $this->_request["isaproved"] = true;
                $this->_request["purchase_request_id"] = $id;
                $this->sql = $this->_all();
                $products = $this->connect();
                unset($this->_request);
                $is_correct = true;
                foreach ($products["data"] as $key => $value) {
                    if ($value["status"] != 0) {
                        $is_correct = false;
                        break;
                    }
                }
                if ($is_correct) {
                    $this->_request["ta"] = "users";
                    $this->_request["id"] = $data["approver_id"];
                    $this->sql = $this->_all();
                    $mailoldAproved = $this->connect();

                    $this->_request["id"] = $aprove;
                    $this->sql = $this->_all();
                    $mailnewAproved = $this->connect();

                    $this->_request["id"] = $data["user_id"];
                    $this->sql = $this->_all();
                    $mailUser = $this->connect();
                    unset($this->_request);

                    $this->_request["ta"] = "purchase_requests";
                    $this->_request["approver_id"] = $aprove;
                    $this->_request["_id"] = $id;
                    $this->sql = $this->update();
                    $_data = $this->connect();

                    $data = array($mailUser["data"][0], $mailnewAproved["data"][0], $mailoldAproved["data"][0], $products["data"]);
                    $this->_DATA(array("data" => $data));
                } else {
                    $this->_DATA(array("error" => "ProductAproveds"));
                }
            } else {
                $this->_DATA(array("data" => "REQUIRE APROBER"));
            }
        } elseif (empty($this->toData["data"])) {
            $this->_DATA(array("data" => "Empty"));
        } elseif (!is_array($this->toData["data"])) {
            $this->_DATA(array("data" => "No_array"));
        } elseif (!isset($this->toData["data"])) {
            $this->_DATA(array("data" => "No_Existe"));
        } elseif ($this->toData["data"] == 0) {
            $this->_DATA(array("data" => "NO_REQ"));
        }
    }

    function Confirm_Process() {

        if (isset($this->_request["data"]) && $this->_request["data"] != "" && is_array($this->_request["data"])) {
            $_response = array();

            $_dataResponse = $this->_request["data"];

            if (isset($_dataResponse) && $_dataResponse != "" & is_array($_dataResponse)) {
                unset($this->_request);
                foreach ($_dataResponse as $key => $value) {
                    if ($value != "") {
                        $this->_request["ta"] = "products";
                        $this->_request["process"] = true;
                        $this->_request["product_id"] = $value['_id'];
                        $this->sql = $this->_all();
                        $_data = $this->connect();
                        unset($this->_request);
                        foreach ($value as $k => $val) {
                            if ($k == "cnt") {
                                $this->_request[$k] = $_data["data"][0]["cnt"] + $val;
                            } else {
                                $this->_request[$k] = $val;
                            }
                        }
                        $this->sql = $this->update();
                        $_response[] = $this->connect();
                    }
                }
                foreach ($_response as $key => $value) {
                    if ($value == 0) {
                        $_response = 0;
                    } else {
                        $_response = $_response;
                    }
                }
                $this->_DATA(array("data" => $_response));
            } else {
                $this->_DATA(array("data" => 0));
            }
        } else {
            $this->_DATA(array("data" => 0));
        }
    }

    function ExportInputs() {

        if (isset($this->_request["RES"]) && $this->_request["RES"] != "" && is_array($this->_request["RES"])) {
            $data = $this->_request["RES"];
            unset($this->_request);

            $Response = array();
            foreach ($data as $key => $value) {
                $this->_request["id"] = $value;
                $this->_request["v1"] = true;
                $this->sql = $this->Inputts();
                $tmp = $this->connect();
                $Response[] = $tmp["data"];
            }
            $data = array();
            foreach ($Response as $key => $value) {
                foreach ($value as $k => $val) {
                    $data[] = $val;
                }
            }
            require_once '../upload/PHPEXCEL.php';
            $excel = new EXCEL(null, null, array("Filename" => "../upload/imports/Entradas", 'sheet' => 'Mis Datos', 'Nocenter' => array('C', 'G')));
            $datos = $excel->CellValues($data, array("A" => 'id_prod', 'B' => 'No_Requisicion', 'C' => 'usuario',
                'D' => 'Cantidad_Requerida', 'E' => 'Cantidad_Recibida',
                'F' => 'Codigo_Fomplus', 'G' => 'Descripcion'));
            $excel->FormatNumberCell = array('D', 'E');

            $sheet1 = $excel->setSheet(0, "", $datos);
//            $sheet1->setAutoFilter('A1:G1');
//            $filter = $sheet1->getAutoFilter();
//            $columnFilter = $filter->getColumn('A');
////           
//            $columnFilter->createRule()
//                    ->setRule(
//                            PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_EQUAL, 11182
//            );
//            $filter->showHideRows();
//            foreach ($sheet1->getRowIterator() as $row) {
//                if ($sheet1->getRowDimension($row->getRowIndex())->getVisible()) {
//
//                    $sheet1->getCell(
//                            'C' . $row->getRowIndex()
//                    )->getValue();
//                    $sheet1->getCell(
//                            'D' . $row->getRowIndex()
//                    )->getFormattedValue();
//                }
//            }

            $i = 1;
            foreach ($data as $key => $value) {
                if (key($value) == "id_prod") {
                    $i++;
                }
            }

            $excel->lock($sheet1, 'A2:A' . $i);
            $excel->lock($sheet1, 'D2:D' . $i, array("B2:B$i", "C2:C$i", "E2:E$i", "F2:F$i", "G2:G$i"));
            $sheet1->getStyle('A1:G1')
                    ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('A1:G1')->applyFromArray(array(
                        'font' => array(
                            'color' => array('rgb' => '000000'),
                            'size' => 16,
                )))
                    ->getFont()->setBold(true);
            $excel->execute();
            $this->_DATA(array("url" => str_replace('GET', '', $_SERVER['REDIRECT_URL'] . $excel->Filename)
                , "file" => $excel->File));
        } else {
            $this->_DATA(array("data" => 'No Data'));
        }
    }

    function ImportInputs() {
        set_time_limit(60);
        $data = $this->Excel_inputs();

        if (isset($data["Data"]["result"]) && is_array($data["Data"]["result"]) && isset($data["Data"]["query"])) {
            $sql = $data["Data"]["query"];
            $isv1 = $this->_request["isv1"];
            $DATOS = $data["Data"]["result"];

            $total = count($data["Data"]["result"]);
            $responses = 'NO DATA';
            $this->sql = "SELECT max(id) as maximo FROM inputs";
            $response1 = $this->connect();
            $first_last_id = $response1["data"][0]["maximo"];
            $DATOS_nreq = array();

            $bd = $this->connect(true);
            $bd->setConfig(array("_requireLastId" => true, "_insert" => true, "is_multi" => true));
            if ($this->bd != null) {
                $last_id = $first_last_id + $total;
                $x = 0;
                $respons = array();
                $ids = "";
                $DATOS_qty = array();
                $querys = array();
                for ($i = $first_last_id; $i <= $last_id; $i++) {
                    if ($x < count($DATOS)) {
                        unset($this->_request);
                        if (isset($DATOS[$x]["id"])) {
                            $this->_request["ta"] = "products_inputs";
                            $this->_request["input_id"] = $i;
                            $this->_request["product_id"] = $DATOS[$x]["id"];
                            $ids .= "{$DATOS[$x]["id"]},";
                            $DATOS_nreq["{$DATOS[$x]["No_Requisicion"]}"] = $DATOS[$x]["No_Requisicion"];
                            $DATOS_qty[$DATOS[$x]["id"]] = $DATOS[$x]["qtyrec"];
                            $querys[] = $this->save();
                            unset($this->_request);
                        }
                    }
                    $x++;
                }

                $ids = substr($ids, 0, -1);
                unset($this->_request);
                $sql2 = "";
                foreach ($querys as $key => $value)
                    $sql2 .= $this->bd->each($value) . ";";

                $this->bd->connect($sql . $sql2); //save all products_inputs and inputs
                if ($sql != "")
                    sleep(12);

                //get data for ids
                $this->_request["ta"] = "products";
                $this->_request["product_id"] = "";
                $this->_request["in_id"] = $ids;
                $this->_request["is_input"] = true;
                $this->sql = $this->_all();
                $all_products = $this->connect();


                $array_id = explode(",", $ids);
                $all_inputs = array();
                foreach ($array_id as $key => $value) {
                    $this->_request["id"] = $value;
                    $this->sql = $this->Input();
                    $all_inputs[$value] = $this->connect(); //get all product inputs
                }
                unset($this->_request);
                $rxponse = $DATOS_qty;
                $UPDATES = array();

                $DATOS_nreq2 = array();
                $all_products = (isset($all_products["data"])) ? $all_products["data"] : array();
                $true_input_id = array();
                $info = array();
                $product_completes = array();
                foreach ($all_products as $key => $producto) {

                    if (array_key_exists($producto["product_id"], $all_inputs) && array_key_exists($producto["product_id"], $DATOS_qty)) {
                        $tmpo = $all_inputs[$producto["product_id"]];
                        $tmpo = $tmpo["data"][0];
                        if ($tmpo["Cantidad_Recibida"] == null || $tmpo["Cantidad_Recibida"] == "") {
                            $tmpo["Cantidad_Recibida"] = 0;
                        }
                        unset($this->_request);
                        if (($tmpo["Cantidad_Recibida"] == $DATOS_qty[$producto["product_id"]] && ($producto["cnt"] == $producto["cantidad"] && $isv1 == FALSE || $isv1 && (int) $producto["cantidad"] == (int) $tmpo["Cantidad_Recibida"] || $tmpo["Cantidad_Recibida"] > (int) $producto["cantidad"] ) ) && !array_key_exists($producto["product_id"], $true_input_id)) {

                            $this->_request["complete_date"] = date("Y-m-d H:i:s");
                            $this->_request["status"] = 4;
                            $this->_request["ta"] = "products";
                            $this->_request["_id"] = $producto["product_id"];
                            $UPDATES[] = $this->update();
                            unset($this->_request);
                            $producto["Cantidad_Recibida"] = $tmpo["Cantidad_Recibida"];
                            $producto["Email"] = $tmpo["Email"];
                            if (key_exists("{$producto["purchase_request_id"]}", $product_completes)) {
                                $product_completes["{$producto["purchase_request_id"]}"][] = $producto;
                            } else {
                                $product_completes["{$producto["purchase_request_id"]}"][] = $producto;
                            }
                            $info["SI"][] = array($producto, $tmpo, $DATOS_qty[$producto["product_id"]]);
                            $true_input_id[$producto["product_id"]] = $producto["product_id"];
                        } else {
                            $info["NO"][] = array($isv1, $producto, $tmpo);
                        }
                    } else {
                        $rxponse[] = "NO Existe {$producto["product_id"]}";
                    }
                }
                $all_products_req = array();
                if (!empty($UPDATES)) {
                    $upd = array();
                    //qr: "_all", ta: "products", purchase_request_id: req, purchase_request_status: '2,6'
                    $this->_request["updates"] = $UPDATES;
                    $upd[] = $this->updateProductsImport();
                    unset($this->_request);
                    sleep(5);

                    $keys = array();
                    if ($DATOS_nreq != "") {
                        foreach ($DATOS_nreq as $key => $value) {
                            $this->_request["ta"] = 'products';
                            $this->_request["purchase_request_id"] = $value;
                            $this->_request["purchase_request_status"] = '2,6';
                            $this->sql = $this->_all();
                            unset($this->_request);
                            $tmp = $this->connect();
                            $tmporal = $tmp["data"];

                            if (($tmporal == "" || $tmporal == null ) && !in_array("$value", $keys)) {
                                $this->_request["complete_date"] = date("Y-m-d H:i:s");
                                $this->_request["status"] = 1;
                                $this->_request["ta"] = "purchase_requests";
                                $this->_request["_id"] = $value;
//                                $UPDATES[] = $this->update();
                                unset($this->_request);
                                $keys["{$value}"] = $value;
                            }
                        }
                    }

                    if ($UPDATES != "") {
                        $this->_request["updates"] = $UPDATES;
                        $upd[] = $UPDATES;
                        $upd[] = $this->updateProductsImport();
                        unset($this->_request);
                    }
                }
            }
            $this->_DATA(array("all_products" => $product_completes, "url" => $data["url"], "File" => $data["file"], "Errors" => $data["Errors"]));
        } else {
            $this->_DATA(array("data" => $data));
        }
    }

    function UploadAbout() {
        set_time_limit(60);

        #error_reporting(E_ALL);
        $base = "../abouts/";
        $filePath = $base . basename($_FILES["file"]["name"]);
        $Path = "./abouts/" . basename($_FILES["file"]["name"]);
        $title = $this->_request['title'];
        unset($this->_request);
        $this->_request['title'] = $title;
        $this->_request['column'] = 'path';
        $this->_request['columnVal'] = $Path;
        $this->_request['ta'] = 'about';
        $this->sql = $this->_all();
        $response = $this->connect();
        if (empty($response)) {
            unset($this->_request['column']);
            unset($this->_request['columnVal']);
            $this->_request['path'] = $Path;
            $this->sql = $this->save();
            $response = $this->connect();
        }
        if (!is_dir($base)) {
            mkdir($base);
        }
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)) {

            $this->_DATA(array("dir" => $filePath, "response" => $response, "config" => array(ini_get("upload_max_filesize"), ini_get("post_max_size"))));
        } else {
            $this->_DATA(array("dir" => $filePath, "response" => "ERROR", "Ero" => $_FILES["file"]['error'], $this->sql));
        }
    }

    protected function Excel($deletefile = true) {
        $session = $this->session();
        $dir = "./../upload/imports/{$session->id}/";

        if (!is_dir($dir)) {
            mkdir($dir, 7777, TRUE);
        }
        include_once '../upload/PHPEXCEL.php';

        $this->excel = new EXCEL($_FILES, 'file', array('targeFile' => $dir), $deletefile);

        return $this->excel->sheetData;
    }

    private function Excel_inputs() {

        if (isset($_FILES["file"]) && $_FILES["file"] != "") {
            $Data = $this->Excel();

            $errors = array();
            $errores = array();
            $result = array();

            $error1 = 'la Cantidad Recibida No puede ser 0';
            $error2 = 'la Cantidad Recibida No puede ser Mayor a la Requerida';
            $error3 = 'La Cantidad Recibida tiene un valor Invàlido';
            $listcoments = array();
            $i = 0;
            $x = 0;
            $sql = array();
            $idsx = "";
            foreach ($Data as $key => $value) {

                if ($key != 1 && $value != "") {
                    if (isset($value["A"]) && is_integer((int) $value["A"]) && is_integer((int) $value["A"]) != 0) {
                        $id = (int) $value["A"];
                        $qty = (int) $value["D"];
                        $qtyrec = (int) $value["E"];
                        $fomplus = ($value["F"] == "" || $value["F"] == null ? "" : $value["F"]);
                        $tmp = array("id_prod" => $id, 'No_Requisicion' => $value["B"], 'usuario' => $value["C"],
                            "Cantidad_Requerida" => $qty, 'Cantidad_Recibida' => $qtyrec,
                            'Codigo_Fomplus' => $fomplus, "Descripcion" => $value["G"]);
                        if ($qtyrec == 0) {
                            $tmp["codeError"] = $error1;
                            $errores[$i] = $tmp;
                            $errors[] = "<br>Error $id </b>: $error1 del <b>Producto</b> :  {$value["G"]}";
                        } elseif ($qtyrec > $qty) {
                            $tmp["codeError"] = $error2;
                            $errores[$i] = $tmp;
                            $errors[] = "<br>Error  $id  </b>: $error2  del <b>Producto</b> :  {$value["G"]}";
                        } elseif ($qtyrec == "" || $qtyrec == null) {
                            $tmp["codeError"] = $error3;
                            $errores[$i] = $tmp;
                            $errors[] = "<br>Error </b>: $id  $error3  del <b>Producto</b> :  {$value["G"]}";
                        } else {
                            $date = date("Y-m-d H:i:s");
                            $sql[$id] = "INSERT INTO   inputs    (  qty ,  active ,  in_date )  VALUES (  $qtyrec , 1 , '{$date}'  );";
                            $result[] = array(
                                "id" => $id,
                                "qty" => $qty,
                                "qtyrec" => $qtyrec,
                                "fomplus_code" => $fomplus,
                                'No_Requisicion' => $value["B"]
                            );
                            $idsx .= "{$id},";
                        }
                        $i++;
                        $x++;
                    }
                }
                unset($Data[$key]);
            }

            $this->_request["ta"] = "products";
            $this->_request["product_id"] = "";
            $this->_request["in_id"] = substr($idsx, 0, -1);
            $this->_request["is_input"] = true;
            $this->sql = $this->_all();
            $response = $this->connect();
            $response = (isset($response["data"]) ? $response["data"] : array());

            if ($response != "") {
                foreach ($result as $key => $value) {
                    foreach ($response as $k => $val) {
                        if ($val["status"] != 2 && $value["id"] == $val["product_id"]) {
                            unset($result[$key]);
                            unset($sql[$value["id"]]);
                        }
                    }
                }
            }
            $sql2 = "";
            foreach ($sql as $key => $value) {
                $sql2 .= $value;
            }
            $sql = $sql2;

            if ($errors != "") {
                $excel = new EXCEL(null, null, array("Filename" => PATH . "upload/imports/Entradas_pendientes", 'sheet' => 'Mis Datos', 'Nocenter' => array('C', 'G')));
                $datos = $excel->CellValues($errores, array("A" => 'id_prod', 'B' => 'No_Requisicion', 'C' => 'usuario',
                    'D' => 'Cantidad_Requerida', 'E' => 'Cantidad_Recibida',
                    'F' => 'Codigo_Fomplus', 'G' => 'Descripcion'));
                $excel->FormatNumberCell = array('D', 'E');
                $sheet1 = $excel->setSheet(0, "", $datos);
                $i = 1;
                $x = 2;
                foreach ($errores as $key => $value) {
                    if (key($value) == "id_prod") {
                        $i++;
                    }
                    if (array_key_exists("codeError", $value)) {
                        $listcoments["E$x"] = $value["codeError"];
                        $x++;
                    }
                }
                $excel->comments($sheet1, $listcoments);
                $excel->lock($sheet1, 'A2:A' . $i);
                $excel->lock($sheet1, 'D2:D' . $i, array("B2:B$i", "C2:C$i", "E2:E$i", "F2:F$i", "G2:G$i"));
                $sheet1->getStyle('A1:G1')
                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle('A1:G1')->applyFromArray(array(
                            'font' => array(
                                'color' => array('rgb' => '000000'),
                                'size' => 16,
                    )))
                        ->getFont()->setBold(true);
                $excel->execute();

                $host = str_replace('index.html', '', $_SERVER['HTTP_REFERER']);
                $data = array("Data" => array("result" => $result, "query" => $sql), "Errors" => $errors
                    , "dir" => $excel->Filename,
                    "url" => str_replace('C:/xampp/htdocs', $_SERVER["HTTP_ORIGIN"], $excel->Filename)
                    , "file" => $excel->File, "Response" => $response);
                return $data;
            } else {
                return array($errores);
            }
        }
    }

    function updateProductsImport() {
        if (isset($this->_request["updates"]) && $this->_request["updates"] != "" && is_array($this->_request["updates"])) {
            $bd = $this->connect(true);
            $bd->setConfig(array("_update" => true, "is_multi" => true));
            $sql = "";
            foreach ($this->_request["updates"] as $key => $value) {
                $sql .= $this->bd->each($value) . ";";
                unset($this->_request["updates"][$key]);
            }
            $response = $this->bd->connect($sql);
            if ($response != "") {
                $respons = array($response);
            } else {
                $respons = array("OK");
            }
            return $respons;
        } else {
            return array("data" => "NO_DATA");
        }
    }

    function _permises() {

        if (isset($this->_request["name"]) && $this->_request["name"] != "") {
            extract($this->_request);
            unset($this->_request);
            if (isset($users_id) && $users_id != "" && is_array($users_id)) {
                
            }
            //
            if (isset($permises) && $permises != "" && is_array($permises)) {
//                $permises= json_decode($permises,true);
                $permises = $this->Trim($permises);
                $permises = json_encode($permises);
            }
            $this->_request["name"] = $name;
            $this->_request["ta"] = 'permises';
            $this->_request["permise"] = $permises;
            $this->_request["users_id"] = json_encode($users_id);

            if (isset($action) && $action == "save") {
                $this->sql = $this->save();
            } else if (isset($id)) {
                $this->_request["id"] = $id;
                $this->sql = $this->update();
            }
            $response = $this->connect();

            return $this->_DATA(array("data" => $response));
        } else {

            return $this->_DATA(array("data" => "NO_DATA"));
        }
    }

    function permises_controller() {
        if (!empty($this->toData) && is_array($this->toData)) {
            if (isset($this->toData['data'][0]['permise'])) {
                $this->_replace($this->toData);
                $json_permise = json_decode($this->toData['data'][0]['permise'], true);
                $this->toData['data'][0]['permise'] = $this->Trim($json_permise, true);
            }
            $this->_DATA($this->toData);
        } else {
            $this->_DATA(array("data" => 0));
        }
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

    function search_product() {
        if (isset($this->_request['for_user'])) {
            $this->_request['all_products_for_user'] = true;
            $this->sql = $this->orders();
            $data = $this->connect();
            if (isset($data["data"]))
                $this->_DATA($data);
            else
                $this->_DATA(array("data" => 0));
        } else {
            $this->_DATA(array("data" => 0));
        }
    }

    function confirmarDelete() {
        if (isset($this->toData['data']) && !empty($this->toData['data'])) {

            unset($this->_request);
            $this->_request['ta'] = "purchase_requests";
            $this->_request['_id'] = $this->toData['data'][0]['No_requisicion'];
            $this->_request['is_delete'] = "1";

            $this->sql = $this->update();
            $response = $this->connect();
            $this->_DATA(array("data" => ($response == 0) ? "OK" : "ERROR"));
        } else {
            $this->_DATA(array("data" => "1150"));
        }
    }

    function CheckPurchasesDeletes() {
        error_reporting(E_ALL);
        if (isset($this->_request['_id'])) {
            $id = $this->_request['_id'];
            $table = $this->_request['ta'];
            unset($this->_request);

            $this->_request['ta'] = $table;
            $this->_request['product_id'] = $id;

            $this->sql = $this->_all();
            $data = $this->connect();
            unset($this->_request);

            if (isset($data) && isset($data["data"][0]['purchase_request_id'])) {
                $purcharse_id = $data["data"][0]['purchase_request_id'];

                $this->_request['ta'] = $table;
                $this->_request['status_delete'] = 0;
                $this->_request['purcharses_id'] = $purcharse_id;

                $this->sql = $this->_all();
                $new_data = $this->connect();
                unset($this->_request);

                //logica test
                if (empty($new_data)) {

                    //recupero todos los que esten activos
                    $this->_request['ta'] = $table;
                    $this->_request['status_delete'] = 1;
                    $this->_request['purcharses_id'] = $purcharse_id;

                    $this->sql = $this->_all();
                    $json = $this->connect();

                    $ids = " IN (";
                    foreach ($json['data'] as $key => $value) {
                        $ids .= "{$value['Id_delete']},";
                    }
                    $ids = trim($ids, ",") . ")";
                    unset($this->_request);

                    $this->_request['ta'] = 'purchase_requests';
                    $this->_request['_id'] = $purcharse_id;
                    $this->_request['is_delete'] = 1;

                    $this->sql = $this->update();
                    $this->connect();
                    unset($this->_request);

                    $this->_request['ta'] = 'products';
                    $this->_request['_id'] = $ids;
                    $this->_request['status_delete'] = "0";

                    $this->sql = $this->update();
                    $this->connect();
                }
            }
            $this->_DATA(array("data" => 0));
        } else {
            $this->_DATA(array("data" => 0));
        }
    }

    function addProductSearch() {
        if (isset($this->_request['id']) && isset($this->_request['req'])) {
            $req = $this->_request['req'];
            $this->_request['ta'] = 'products';
            $this->_request['search_id'] = $this->_request['id'];
            unset($this->_request['id']);
            $this->sql = $this->_all();
            $data = $this->connect();
            unset($this->_request);
            $data = $data['data'][0];

            foreach ($data as $key => $value) {

                switch ($key) {
                    case 'approve_date':
                    case 'process_date':
                    case 'complete_date':
                        $value = '';
                        break;
                }
                if ($key != "cnt" && $key != 'id') {
                    $this->_request["$key"] = $value;
                }
            }
            $this->_request['ta'] = 'temp_products';
            $this->_request['purchase_request_id'] = $req;
            $this->sql = $this->save();

            $data = $this->connect();
            if ($data == 0) {
                $data = $this->_request['description'];
            }
            $this->_DATA(array($data));
        } else {
            $this->_DATA(array("status" => false));
        }
    }

    function ImportProducts() {
        $sheetData = $this->Excel(false);
        $priority = array("NORMAL" => "NORMAL", "NORMAL" => "NORMAL", "MEDIA" => "MEDIA", "IMPORTACION" => "IMPORTACION", "FECHA LIMITE" => "FECHA LIMITE");
        $prioritys = array("NORMAL" => 1, "NORMAL" => 2, "MEDIA" => 3, "IMPORTACION" => 4, "FECHA LIMITE" => 5);
        $keys = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K");
        $replace = array('"A":' => '"fomplus_code":', '"B":' => '"cost_centre":', '"C":' => '"provider":'
            , '"D":' => '"pn_provider":', '"E":' => '"manufacturer":', '"F":' => '"part_number":', '"G":' => '"description":',
            '"H":' => '"qty":', '"I":' => '"priority_id":', '"J":' => '"limit_date":', '"K":' => '"comments":');
        $datos = array();
        $errors = array();
        $x = 1;
        $i = 0;
        foreach ($sheetData as $key => $values) {

            if (in_array(key($values), $keys)) {
//               

                if ($values["I"] != "Prioridad") {
                    if ($values["I"] == "") {
                        $errors[1] = "Prioridad  Vacia , " . $values["I"] . " por favor Digite una Válida  En I{$key}";
                    } elseif ($values["I"] != "Prioridad" && $values["I"] == $priority[$values["I"]]) {
                        $values["I"] = $prioritys[$values["I"]];
                        $resultado = (strtotime($values["J"]) >= strtotime(date('Y-m-d'))) ? 1 : 0;
                        //fecha limite TIENE QUE SER MAYOR mayor o igual ha Hoy
                        if ($values["I"] == 5 && !empty($values["J"]) && $resultado == 0) {

                            if ($resultado == 0 && !empty($values["J"])) {
                                $errors[1] = "Fecha Limite ( " . $values["J"] . " ) no puede ser menor ha hoy En J{$key}";
                            }
                        } else if ($values["I"] == 5 && empty($values["J"])) {
                            $errors[1] = "Fecha Limite no válida en J{$key}.";
                        }
                    } else {
                        $errors[1] = "Prioridad ( " . $values["I"] . ") No Válida En I{$key}";
                    }
                }

                if ($values["G"] != "Descripción") {
                    if ($values["G"] == "") {
                        $errors[1] = "Descripción vacia , por favor digite una válida En G{$key}";
                    }
                }

                $values['purchase_request_id'] = $this->_request["purchase_request_id"];
                $values['ta'] = "temp_products";

                if ($values["H"] != "Cantidad") {
                    $v = intval($values["H"]);
                    if ($v == "" || $v == "null" || $v == null) {
                        $errors[2] = "Cantidad Vacia , por favor digite un Número en H{$key}";
                    }
                    if ($v == "0" || $v == 0) {
                        $errors[2] = "Cantidad No Válida , por favor digite un Número H{$key}";
                    }
                }
                $datos[] = $values;
            } else {
                if (count($keys) < count($values))
                    $c = count($keys) - count($values);
                if (count($keys) < count($values))
                    $c = count($values) - count($keys);
                $errors[0] = "Número de Columnas No Coinciden , hay " . $c . " Columna mas.";
            }
            next($values);
        }
        $data = "";
        if (!empty($errors)) {
            echo json_encode(array("error" => $errors));
        } else {
            unset($datos[0]); //delete  first row
            $datos = json_encode($datos);
            $data = strtr($datos, $replace);
        }
        $date = date("Y-m-d H:i:s");
        file_put_contents("TempProducts.txt", "\n[" .$date." ] ".$data."\n",FILE_APPEND);
        if (isset($data) && !empty($data) && empty($errors)) {
            //Save all temp products to imports
            $temps_products = json_decode($data, true);
            $response = "ERROR";
            foreach ($temps_products as $key => $product) {
                unset($this->_request);
                $this->_request = $product;
                $this->sql = $this->save();
                $response = $this->connect() == 0 ? "OK" : "ERROR";
            }
            $this->_DATA(array("data" => $response));
        }
    }

    function permis() {
        $this->_request['ta'] = 'permises';
        $this->sql = $this->_all();
        $permises = $this->connect();
        $this->_replace($permises);
        $ok = false;
        $permiss = array();
        $session = $this->session();
        foreach ($permises['data'] as $key => $permise) {
            $users_id = json_decode($permise['users_id'], true);
            if (in_array($session->id, $users_id)) {
                $permiss = json_decode($permise['permise'], true);
                $ok = TRUE;
                break;
            }
        }
        if ($ok) {
            $this->_DATA($permiss);
        } else if ($permises == 0) {
            $this->_DATA(array($permises));
        } else {
            $this->_DATA(array("ERROR"));
        }
    }

    function getcostCenter() {
        $this->_request['ta'] = 'cost_center';
        $this->sql = $this->_all();
        $cost_centers = $this->connect();

        $this->_request['ta'] = 'sub_cost_center';
        $this->sql = $this->_all();
        $sub_cost_centers = $this->connect();

        $this->_DATA(array("sub_cost_centers" => $sub_cost_centers['data'], "cost_centers" => $cost_centers['data']));
    }

}

?>