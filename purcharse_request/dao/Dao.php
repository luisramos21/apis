<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dao
 *
 * @author ADMIM
 */
require PATH . 'dao/db.class.php';

class Dao {

    public $sql = null;
    public $isq = true;
    public $is_req = true;
    public $is_selectString = false;
    public $is_multi = false;
    public $autoController = true;
    public $MetEach = null;
    public $bd = null;
    private $_requireLastId = false;

    public function __construct() {
//        parent::__construct();
        if (isset($this->_request["isq"]) && ($this->_request["isq"] == false || $this->_request["isq"] == 'false')) {
            $this->isq = FALSE;
        }
		
		if(isset($_SESSION["login_requisiciones"])){
			unset($_SESSION["login_requisiciones"]);
		}
        //temp sesion
        $_SESSION["login_requisiciones"] = json_encode(array("data" => array(array("id" => 80, "user_type_id" => 6, "User" => "User Session"))));

        if (!empty($this->func) && isset($this->_request["qr"]) && $this->session()) {
            $this->processRquest();
        } elseif (!empty($this->func) && isset($this->_request["qr"]) && !$this->session() && $this->_request["qr"] == 'logIn') {
            $this->processRquest();
        } else {
            $this->responsed = 500;
        }
    }

    function processRquest() {
        $this->sql = call_user_method($this->func, $this);

        if ($this->isq != false && (int) method_exists($this, $this->_request["qr"]) > 0) {
            $this->toData = call_user_method('connect', $this);		
			
            if (isset($this->_request["co"]) && $this->_request["co"] != "" && ((int) method_exists($this, $this->_request["co"]) > 0)) {
                call_user_method($this->_request["co"], $this);
            } else if ($this->autoController) {
                call_user_method('defaut', $this);
            } else {
                return $this->toData;
            }
        } elseif (isset($this->_request["co"]) && $this->isq == false && (int) method_exists($this, $this->_request["co"]) > 0) {
            call_user_method($this->_request["co"], $this);
        } else {
            $this->responsed = "ERROR " . $this->isq;
            print_r($this->_request);
        }
    }

    function connect($setmethods = false) {
        $this->bd = new Database();
        $this->bd->_requireLastId = $this->_requireLastId;
        if ($setmethods)
            return $this->bd;
        return $this->bd->connect($this->sql, true);
    }

    //GET TO DATA

    function logIn() {
        $return = array();
        if (isset($this->_request["username"]) && $this->_request["password"]) {
            $return = array(
                "SELECT" => "u.id, u.name , u.lastname, u.username,u.email, u.user_type_id, ut.description AS description",
                "FROM" => 'users u, user_types ut',
                "WHERE" => "u.username = '{$this->_request["username"]}' AND u.password = '{$this->_request["password"]}' AND  u.user_type_id = ut.id AND state=1"
            );
        }
        return $return;
    }

    function isSes() {
        $session = $this->session();
        if ($session == "" && $session == null)
            return "";
        return array(
            "SELECT" => "u.id, u.name , u.lastname, u.username,u.email, u.user_type_id, ut.description AS description",
            "FROM" => 'users u, user_types ut',
            "WHERE" => "u.id = '{$session->id}'  AND  u.user_type_id = ut.id"
        );
    }

    function menus() {
        $session = $this->session();
        return array(
            "SELECT" => "permissions",
            "FROM" => 'user_types',
            "WHERE" => "id = '{$session->user_type_id}'"
        );
    }

    function main() {
        if (isset($this->_request["qr"])) {
            $request = $this->_request;
            $this->_request["ta"] = $request["qr"];
            $this->_request["main_id"] = $request["id"];
        }
        return $this->_all();
    }

    function permises() {
        if (isset($this->_request["qr"])) {
            $request = $this->_request;
            $this->_request["ta"] = $request["qr"];
            $this->_request["permises_id"] = $request["id"];
        }
        return $this->_all();
    }

    function user() {
        $session = $this->session();
        $return = array(
            "SELECT" => "name as Nombre , lastname as Apellido , username as Nombre_usuario , email as Email , user_types.description as Tipo_usuario  , state as Estado , users.id as Id",
            "FROM" => 'users',
            "INNER JOIN" => array("user_types" => " users.user_type_id = user_types.id")
        );
        if (isset($this->_request["username"]) && isset($this->_request["OldUsername"])) {
            $return["SELECT"] = "username";
            $old = ($this->_request["OldUsername"] != "") ? " AND users.username != '{$this->_request["OldUsername"]}'" : "";

            $return["WHERE"] = "users.username = '{$this->_request["username"]}' " . $old;
        } else if (!isset($this->_request["id"]) && isset($session)) {
            $return["WHERE"] = "users.id !={$session->id} AND user_type_id !=6 AND user_type_id !={$session->user_type_id}";
        } elseif (isset($this->_request["id"])) {
            $return["SELECT"] = "name  , lastname  , username , email , user_types.description as user_types  , state  , users.id as id , user_types.id as user_type_id ";
            $return["WHERE"] = "users.id = {$this->_request["id"]}";
        }
        return $return;
    }

    function userty() {
        $return = array();
        $return = array(
            "SELECT" => "description  as Perfil , information as Descripcion , permissions as Permisos , id as Id ",
            "FROM" => 'user_types'
        );
        if (isset($this->_request["_id"])) {
            $return["SELECT"] = str_replace('Id', '_id', $return["SELECT"]);
            $return["WHERE"] = "user_types.id =  {$this->_request["_id"]}";
        }
        return $return;
    }

    function Inquiries() {
        $return = array();
        $status_check = array(0, "1,6");
        $session = $this->session();
        if (isset($this->_request["status"])) {
            if (strpos($this->_request["status"], ',') != FALSE) {
                $filter = "";
                if (isset($session->user_type_id) && $session->user_type_id == 2) {
                    $filter = " AND purchase_requests.user_id = {$session->id}";
                }
                $when = "products.status IN( {$this->_request["status"]}) {$filter}  AND purchase_requests.is_delete=0";
            } else {

                if (isset($session->user_type_id) && $session->user_type_id == 2) {
                    $filter = " AND purchase_requests.user_id = {$session->id}";
                }
                $when = array("products.status" => $this->_request["status"] . $filter . "  AND purchase_requests.is_delete=0 AND status_delete =0");
            }

            $getChecks = "";
            if (in_array($this->_request["status"], $status_check) && ( isset($this->_request["is_process"]) && ($this->_request["is_process"] == "false") &&
                    isset($this->_request["is_pendi_input"]) && $this->_request["is_pendi_input"] == "false" ) && ($session->user_type_id == 1 || $session->user_type_id == 6)) {
                $getChecks = "products.id as id_prod , ";
            }


            $return = array("SELECT" => "$getChecks" . ((isset($this->_request["is_process"]) &&
                $this->_request["is_process"] && $this->_request["status"] != "2,6") ? " products.id as id_process , " : "") .
                "products.purchase_request_id as Ireq ,products.purchase_request_id as Nreq  " . (( strpos($this->_request["status"], ',') == true && isset($this->_request["is_pendi_input"]) && $this->_request["is_pendi_input"] == "true" && isset($this->_request["is_process"]) && $this->_request["is_process"] == "true") ? ($this->_request["status"] == "2,6") ? ", qty as Cant_sol , (qty-cnt) as Cant_pend ," :
                ",(qty-cnt) as Cant_pend, " : ", products.qty as Cant,") .
                " products.description as Descripcion , fomplus_code as Codigo_wo,  products.cost_centre as Centro_Costo , products.provider as Proveedor , products.pn_provider as PN_Proveedor,products.manufacturer as Fabricante,part_number as PN_Fabricante  ,"
                . "priorities.description as Prioridad ,"
                . "purchase_requests.req_date as F_requi , products.approve_date as F_aprob ,"
                . "products.complete_date as F_complete, max_day, limit_date,priority_id, users.username as Usuario ,products.comments as  Observaciones, products.id as Id " . (( strpos($this->_request["status"], ',') != FALSE) ? ", products.status as estado  " : "") . ""
                , "FROM" => 'products'
                , "ORDER BY" => "F_aprob , F_requi DESC "
                , "INNER JOIN" => array("priorities" => "products.priority_id = priorities.id"
                    , "purchase_requests" => "purchase_requests.id = products.purchase_request_id "
                    , "users" => "purchase_requests.user_id  = users.id ")
                , "WHERE" => $when
            );


            /* "products.purchase_request_id as Ireq ,products.purchase_request_id as Nreq  " . (( strpos($this->_request["status"], ',') == true && isset($this->_request["is_pendi_input"]) && $this->_request["is_pendi_input"] == "true" && isset($this->_request["is_process"]) && $this->_request["is_process"] == "true") ? ($this->_request["status"] == "2,6") ? ", qty as Cant_sol , (qty-cnt) as Cant_pend ," :
              ",(qty-cnt) as Cant_pend, " : ", products.qty as Cant,") .
              " products.description as Descripcion , fomplus_code as Codigo_wo,  products.cost_centre as Centro_Costo ,part_number as PN  ,"
              . "products.provider as Proveedor , priorities.description as Prioridad ,"
              . "purchase_requests.req_date as F_requi , products.approve_date as F_aprob ,"
              . "products.complete_date as F_complete, max_day, limit_date,priority_id, users.username as Usuario , products.id as Id " . (( strpos($this->_request["status"], ',') != FALSE) ? ", products.status as estado  " : "") . "" */
            if (isset($this->_request["id"])) {

                $this->is_req = false;
                $return["SELECT"] = "products.id , products.purchase_request_id, products.description as Descripcion ,products.fomplus_code,products.qty ,products.cnt,products.part_number ,  products.manufacturer ,  products.cost_centre ,  products.provider  ,priorities.description as Prioridad ,purchase_requests.req_date , products.approve_date  , products.comments,products.pn_provider";
                $return["ORDER BY"] = "products.id asc ";
                $return["WHERE"] = (strpos($this->_request["status"], ',') != FALSE) ? " products.status IN( {$this->_request["status"]}) AND products.id= {$this->_request["id"]}" : array("products.status" => $this->_request["status"] . " and products.id=" . $this->_request["id"]);
            } elseif (isset($this->_request["fo_user"])) {
                $return["SELECT"] = "products.id as id_prod , " . str_replace('products.approve_date as F_aprob , max_day,', '', $return["SELECT"]);

                $return["ORDER BY"] = "products.id asc ";

                if ($this->_request["fo_user"] === "true") {
                    $where = "products.status = " . $this->_request["status"] . " and purchase_requests.approver_id=" . $session->id;
                }
                /* else {
                  $where = $this->_request["status"];
                  } */

                if (isset($this->_request["_id"]) || isset($this->_request["_id_in"])) {
                    if (isset($this->_request["_id_in"]) && $this->_request["fo_user"]) {
                        $where .= ((isset($where) || $where != '') ? " AND " : " " );
                        $where .= " products.id in ({$this->_request["_id_in"]} )";
                    } elseif (isset($this->_request["_id_in"]) && !$this->_request["fo_user"]) {
                        $where .= ((isset($where) || $where != '') ? " AND " : " " );
                        $where .= " products.id in ({$this->_request["_id_in"]} )";
                    } else {
                        $where = "products.id={$this->_request["_id"]}";
                    }
                    $return["SELECT"] .= " , users.email as Email ";
                }
                $return["WHERE"] = $where . " AND purchase_requests.is_delete=0  AND status_delete =0";
            }
        } else if (isset($this->_request["id"]) && isset($this->_request["is_ng"])) {

            $this->is_req = false;
            $return["SELECT"] = "products.id , products.purchase_request_id, products.description as Descripcion ,products.fomplus_code,products.qty ,products.cnt,products.part_number , products.limit_date ,  products.manufacturer ,  products.cost_centre ,  products.provider ,pn_provider  ,priorities.id as Prioridad ,purchase_requests.req_date , products.approve_date  , products.comments";
            $return["FROM"] = " products ";
            $return["INNER JOIN"] = array("priorities" => "products.priority_id = priorities.id"
                , "purchase_requests" => "purchase_requests.id = products.purchase_request_id "
                , "users" => "purchase_requests.user_id  = users.id ");

            $return["WHERE"] = "products.id={$this->_request["id"]} AND purchase_requests.is_delete=0 AND status_delete =0";
            $return["ORDER BY"] = "products.id asc ";
        }
        return $return;
    }

    function Inputts() {

        $return = array();
        $return = array(
            "SELECT" => "purchase_requests.id as id , purchase_requests.id as No_requisicion , CONCAT ( users.name ,'  ' ,users.lastname )  as Usuario , purchase_requests.req_date as Fecha_Aprobacion , purchase_requests.status as Estado",
            "FROM" => 'purchase_requests ',
            "ORDER BY" => "purchase_requests.req_date DESC ",
            "INNER JOIN" => array("users" => "purchase_requests.user_id  = users.id ",
                "products" => "purchase_requests.id  = products.purchase_request_id "),
            "WHERE" => array("purchase_requests.status" => " 0  AND products.status in(2,6) AND purchase_requests.is_delete=0  AND status_delete =0 AND purchase_requests.version2=" . ((isset($this->_request["v1"]) && $this->_request["v1"] == 'true') ? 0 : 1) . "  GROUP BY products.purchase_request_id")
        );
        if (isset($this->_request["id"]) || isset($this->_request["_id"])) {

            $return["SELECT"] = "products.id as id_prod ,purchase_requests.id as No_Requisicion, CONCAT ( users.name ,'  ' ,users.lastname )  as usuario , purchase_requests.req_date as Fecha_Requisicion , purchase_requests.status as Estado ,"
                    . "SUM(inputs.qty)  as Cantidad_Recibida , " . ((isset($this->_request["v1"]) && $this->_request["v1"]) ? "products.qty" : "cnt") . " as Cantidad_Requerida , products.description as Descripcion , products.provider as Proveedor , priorities.description as Prioridad , products.approve_date  as Fecha_Aprobacion , products.id as Id ";

//            $return["SELECT"] = "products.id as id_prod ,purchase_requests.id as No_Requisicion, CONCAT ( users.name ,'  ' ,users.lastname )  as usuario , purchase_requests.req_date as Fecha_Requisicion , purchase_requests.status as Estado ,"
//                    . "SUM(inputs.qty)  as Cantidad_Recibida ,  products.qty  as Cantidad_Requerida , products.description as Descripcion , products.provider as Proveedor , priorities.description as Prioridad , products.approve_date  as Fecha_Aprobacion , products.id as Id ";


            $return["INNER JOIN"] = array("users" => "purchase_requests.user_id  = users.id ",
                "products" => "purchase_requests.id = products.purchase_request_id",
                "priorities" => "priorities.id = products.priority_id");

            $return["LEFT JOIN"] = array("products_inputs" => "products_inputs.product_id = products.id",
                "inputs" => "inputs.id = products_inputs.input_id");
            $return["ORDER BY"] = "products.id  ASC";
            $col = "purchase_request_id";
            if (isset($this->_request["_id"])) {
                $col = "id";
                $return["SELECT"] = "products.* ";
                $this->_request["id"] = $this->_request["_id"];
            }
            $return["WHERE"] = array("purchase_requests.status " => " 0  AND products.$col={$this->_request["id"]}  AND products.status in(2,6) AND status_delete =0  AND purchase_requests.is_delete=0  GROUP BY products.id ");
        }

        return $return;
    }

    function Input($in_id = null) {
        $return = array();
        if (isset($this->_request["id"]) || $in_id != NULL) {

            if ($in_id != null) {
                $where = "products.id in($in_id) ";
            } else {
                $where = "products.id ={$this->_request["id"]}";
            }

            $return["SELECT"] = "SUM(inputs.qty)  as Cantidad_Recibida, products.description as Descripcion , products.purchase_request_id as Nreq , users.email as Email";
            $return["FROM"] = 'purchase_requests ';
            $return["INNER JOIN"] = array("users" => "purchase_requests.user_id  = users.id ",
                "products" => "purchase_requests.id = products.purchase_request_id",
                "priorities" => "priorities.id = products.priority_id");

            $return["LEFT JOIN"] = array("products_inputs" => "products_inputs.product_id = products.id",
                "inputs" => "inputs.id = products_inputs.input_id");
            $return["ORDER BY"] = "products.id asc ";
            $return["WHERE"] = $where . " AND purchase_requests.is_delete=0 AND status_delete =0";
        }


        return $return;
    }

    function processed() {
        $return = array();
        $session = $this->session();
        $return = array(
            "SELECT" => "products.purchase_request_id as Nreq , products.qty as Cantidad , products.description as Descripcion ,   users.username as Usuario , products.provider as Proveedor , priorities.description as Prioridad ,purchase_requests.req_date as Freq  , products.id as Id"
            , "FROM" => 'products'
            , "ORDER BY" => "purchase_requests.req_date DESC "
            , "INNER JOIN" => array("priorities" => "products.priority_id = priorities.id"
                , "purchase_requests" => "purchase_requests.id = products.purchase_request_id "
                , "users" => "purchase_requests.user_id  = users.id ")
            , "WHERE" => "purchase_requests.user_id= {$session->id}" . " AND purchase_requests.is_delete=0 AND status_delete =0"
        );
        if (isset($this->_request["purcharse_id"]) || isset($this->_request["id"]) || isset($this->_request["purcha_id"])) {

            $return["SELECT"] = str_replace(" users.username as Usuario", " products.fomplus_code as Codigo_wo , products.manufacturer As Fabricante ", $return["SELECT"]);
            if (isset($this->_request["purcharse_id"])) {
                $return["WHERE"] = array("purchase_requests.user_id" => $session->id . " AND products.purchase_request_id = {$this->_request["purcharse_id"]}" . " AND purchase_requests.is_delete=0  AND status_delete =0");
            }
            if (isset($this->_request["id"])) {
                $return["SELECT"] = "purchase_requests.req_date as Freq,  fomplus_code, products.description ,manufacturer , provider , qty , "
                        . "cost_centre , priorities.id as PriorityId,priorities.description as Priority  ,products.id as _id , part_number , comments, purchase_request_id , products.status";
                $return["WHERE"] = array("purchase_requests.user_id" => $session->id . " AND products.id = {$this->_request["id"]}" . " AND purchase_requests.is_delete=0  AND status_delete =0");
            }
            if (isset($this->_request["id"])) {
                $return["SELECT"] = "purchase_requests.req_date as Freq,  fomplus_code, products.description ,manufacturer , provider , qty , "
                        . "cost_centre , priorities.id as PriorityId,priorities.description as Priority  ,products.id as _id , part_number , comments, purchase_request_id , products.status";
                $return["WHERE"] = array("purchase_requests.user_id" => $session->id . " AND products.id = {$this->_request["id"]}" . " AND purchase_requests.is_delete=0 AND status_delete =0");
            }
            if (isset($this->_request["purcha_id"])) {
                $filterPurcharse = " AND purchase_requests.is_delete=0 ";
                if (isset($this->_request["check_deletes"]) && $this->_request["check_deletes"] == 1) {
                    $filterPurcharse = " ";
                }

                $return["SELECT"] = "products.purchase_request_id as Nreq , products.qty as Cantidad , products.description as Descripcion ,"
                        . "cost_centre as Centro_Costo,fomplus_code as Codigo_wo, provider as Proveedor , pn_provider as PN_Proveedor , manufacturer as Fabricante  ,part_number as PN_Fabricante , "
                        . "purchase_requests.status as Estado_pur, "
                        . " products.status as Estado_producto , "
                        . "priorities.description as Prioridad ,comments as Comentarios , products.id as Id";
                $return["WHERE"] = array("purchase_requests.user_id" => $session->id . " AND products.purchase_request_id IN({$this->_request["purcha_id"]})" . " $filterPurcharse AND status_delete =0");
            }
        }
        return $return;
    }

    function Approve() {
//        $session = $this->session();
        return array(
            "SELECT" => "users.id as Id , username",
            "FROM" => 'users',
            "INNER JOIN" => array('user_types' => 'user_types.id = users.user_type_id'),
            "WHERE" => " user_types.id = 1 or user_types.id = 4"
        );
    }

//*return mixed array */
    function Indicator() {
        $return = array();
        if (isset($this->_request['year']) && !empty($this->_request['year'])) {

            if (is_array($this->_request["year"])) {
                $this->_request["year"] = implode(',', $this->_request["year"]);
            }
            if (is_string($this->_request["year"])) {
                $this->_request["year"] = $this->_request["year"];
            }

            $return = array(
                "SELECT" => "*,purchase_request_id as Nreq ,  products.description as Description , qty as cant , fomplus_code as Codigo_wo , priorities.description as Prioridad ,"
                . "approve_date as Faprob ,process_date as Fprocess, products.complete_date as FComplete , ( DATEDIFF( products.complete_date, products.approve_date )) AS Dias",
                "FROM" => 'products',
                "INNER JOIN" => array("priorities" => "products.priority_id = priorities.id"),
                "WHERE" => " products.status = 4  AND YEAR(products.complete_date) in( {$this->_request['year']}) AND products.complete_date !='0000-00-00 00:00:00' AND status_delete =0"
            );
        }
        if (isset($this->_request['mes']) && !empty($this->_request['mes']) && isset($this->_request['year']) && !empty($this->_request['year'])) {
            $return = array(
                "SELECT" => "*,purchase_request_id as Nreq ,  products.description as Description , qty as cant , fomplus_code as Codigo_wo , priorities.description as Prioridad ,"
                . "approve_date as Faprob ,process_date as Fprocess, products.complete_date as FComplete , ( DATEDIFF( products.complete_date, products.approve_date )) AS Dias",
                "FROM" => 'products',
                "INNER JOIN" => array("priorities" => "products.priority_id = priorities.id"
                ),
                "WHERE" => "  products.status = 4 AND status_delete =0 AND MONTH(products.complete_date) = {$this->_request['mes']} AND YEAR(products.complete_date) in( {$this->_request['year']} ) AND products.complete_date !='0000-00-00 00:00:00'"
            );
        }
        return $return;
    }

    function table() {
        return array(
            "SHOW" => " TABLES"
        );
    }

    function _all() {
        $session = $this->session();
        $return = array();
        if (isset($this->_request["ta"]) && !empty($this->_request["ta"])) {
            $extra = "";
            if ($this->_request["ta"] == "temp_products") {
                $extra = " {$this->_request["ta"]}.id as ID_TEMP , ";
            }
            if ($this->_request["ta"] == "permises" || $this->_request["ta"] == "main") {
                $extra = " {$this->_request["ta"]}.id as id_check , ";
            }

            $return = array(
                "SELECT" => " $extra " . $this->_request["ta"] . ".* ," . $this->_request["ta"] . ".id as Id",
                "FROM" => $this->_request["ta"]
            );

            if ($this->_request["ta"] == "products" && isset($this->_request["year"]) && !empty($this->_request["year"]) && !isset($this->_request["count"])) {
                $return["WHERE"] = " YEAR(approve_date) = " . $this->_request["year"];
                $return["ORDER BY"] = " approve_date DESC";
            } elseif ((isset($this->_request["purchase_request_id"]) || isset($this->_request["id"]) ) && !isset($this->_request["main_id"]) && !isset($this->_request["permises_id"])) {
                $table = $this->_request["ta"];
                $where = "";
                $select = "";
                if (isset($this->_request["purchase_request_id"])) {
                    $where = " purchase_request_id = {$this->_request["purchase_request_id"]}";
                    $select = " {$extra}  $table.purchase_request_id as Nreq , $table.qty as Cantidad , $table.description as Descripcion , $table.fomplus_code as Codigo_wo , $table.cost_centre as Centro_Costo,  $table.provider as Proveedor , $table.pn_provider as PN_Proveedor, $table.manufacturer as Fabricante , $table.part_number as PN_Fabricante, "
                            . " priorities.description as Prioridad  ,$table.limit_date , $table.comments as Comentarios, $table.id  " . (isset($this->_request["isaproved"]) == "products" ? " , status" : "");
                    $return["INNER JOIN"] = array("priorities" => "$table.priority_id = priorities.id");
                } elseif (isset($this->_request["id"])) {
                    $where = "$table.id = {$this->_request["id"]}";
                    $select = "*";
                }
                if (isset($this->_request["purchase_request_status"])) {
                    $select = "$table.status , $table.description , $table.id";
                    $where .= " AND $table.status in({$this->_request["purchase_request_status"]})";
                }
                $return["SELECT"] = $select;

                $return["WHERE"] = $where;
                $return["ORDER BY"] = " id DESC";
                if (isset($this->_request["id"]) && $this->_request["ta"] != "users" && !isset($this->_request["noComents"])) {
                    $return["SELECT"] = str_replace("priorities.description", "$table.priority_id", $return["SELECT"]) . " , $table.comments as Comentarios";
                    unset($return["INNER JOIN"]);
                }
            } elseif (isset($this->_request["product_id"])) {
                $return["SELECT"] = " products.id as product_id , products.status, products.description ,products.purchase_request_id , products.qty as cantidad" . (((isset($this->_request["process"]) && $this->_request["process"] == true) || isset($this->_request["is_input"]) && $this->_request["is_input"] == true) ? ",products.cnt" : "");

                $return["WHERE"] = (isset($this->_request["in_id"]) && !empty($this->_request["in_id"]) ) ? "products.id IN({$this->_request["in_id"]})" : (" products.id " . ((strpos($this->_request["product_id"], ",")) ? "" : "=") . " {$this->_request["product_id"]}");
            } elseif (isset($this->_request["purcharse_id"])) {
                $table = $this->_request["ta"];
                $return["SELECT"] = " $table.id as No_Requisicion, $table.req_date as Freq ,status as Estado, CONCAT ( users.name ,'  ' ,users.lastname )  as usuario";
                $return["INNER JOIN"] = array("users" => "{$table}.user_id = users.id");
                $return["WHERE"] = "$table.id = " . $this->_request["purcharse_id"];
            } elseif (isset($this->_request["User_id"])) {
                $table = $this->_request["ta"];
                $return["SELECT"] = "*";
                unset($return["INNER JOIN"]);
                unset($return["ORDER BY"]);
                $return["WHERE"] = "$table.id = " . $this->_request["User_id"];
            } elseif (isset($this->_request["count"])) {
                $month = (int) date("m");
                $return["SELECT"] = "products.*";
                $return["INNER JOIN"] = array("purchase_requests" => "purchase_requests.id = products.purchase_request_id ");
                $return["WHERE"] = " AND purchase_requests.is_delete=0  AND status_delete =0 " . "products.status in(2) AND MONTH(products.approve_date) = " . $month;

                if (isset($this->_request["year"])) {
                    if (is_array($this->_request["year"])) {
                        $this->_request["year"] = implode(',', $this->_request["year"]);
                    }
                    if (is_string($this->_request["year"])) {
                        $this->_request["year"] = $this->_request["year"];
                    }

                    $return["WHERE"] = "products.status in(2) AND MONTH(products.approve_date) =  $month AND YEAR(products.approve_date) in( {$this->_request["year"]} )" . " AND purchase_requests.is_delete=0 AND status_delete =0";
                }
            } elseif (isset($this->_request["ta"]) && $this->_request["ta"] == "Sync_products" && !isset($this->_request["item"]) && !isset($this->_request["fomplus_code"])) {
                $return["SELECT"] = "Sync_products.* , code_wo as Id";
            } elseif (isset($this->_request["codeWo"]) && $this->_request["ta"] == "Sync_products") {
                $return["SELECT"] = "code_wo as Id";
                $return["WHERE"] = "code_wo = '{$this->_request["codeWo"]}'";
            } elseif (isset($this->_request["item"]) && $this->_request["ta"] == "Sync_products") {
                $return["SELECT"] = "*";
                $return["WHERE"] = "code_wo LIKE '%{$this->_request["item"]}%' LIMIT 20";
                if (isset($this->_request["item_desc"])) {
                    $return["WHERE"] = "descripction LIKE '%{$this->_request["item"]}%' LIMIT 20";
                }
            } else if (isset($this->_request["for_user"])) {
                $return["SELECT"] = "id as No_requisicion , req_date as Fecha_requisicion , status as Estado, id as Id";
                $return["WHERE"] = "user_id = '{$session->id}'";
            } else if (isset($this->_request["ind"]) && isset($this->_request["col"]) && !empty($this->_request["ind"]) && !empty($this->_request["col"])) {
                $return["SELECT"] .= ", products.id as id_pendi ";
                if (isset($this->_request["select"]) && $this->_request["select"] != "") {
                    if (!isset($this->_request["select_replace"])) {
                        $return["SELECT"] .= ",{$this->_request["select"]}";
                    } else {
                        $return["SELECT"] = "{$this->_request["select"]}";
                    }
                }
                if (isset($this->_request["inner"]) && !empty($this->_request["inner"]) && is_array($this->_request["inner"])) {
                    $return["INNER JOIN"] = $this->_request["inner"];
                }
                $return["WHERE"] = "{$this->_request["col"]} in({$this->_request["ind"]})";
                if (isset($this->_request["AND"]) && empty($this->_request["AND"])) {
                    $return["WHERE"] .= " AND {$this->_request["AND"]}";
                }
            } else if (isset($this->_request["column"]) && isset($this->_request["columnVal"])) {
                $return["SELECT"] = "*";
                $return["WHERE"] = "{$this->_request["column"]} in('{$this->_request["columnVal"]}')";
            } else if (isset($this->_request["fomplus_code"]) && $this->_request["ta"] == "Sync_products") {
                $return["SELECT"] = "*";
                $return["WHERE"] = "code_wo= '{$this->_request["fomplus_code"]}'";
            } elseif (isset($this->_request["main_id"])) {
                $return["SELECT"] = "*";
                $return["WHERE"] = "id= '{$this->_request["id"]}'";
            } elseif (isset($this->_request["permises_id"])) {
                $return["SELECT"] = "*";
                $return["WHERE"] = "id= '{$this->_request["id"]}'";
            } elseif (isset($this->_request["for_user"])) {
                $return["SELECT"] = "*";
//               $return["INNER JOIN"] = array('');
            } elseif (isset($this->_request["search_id"])) {
                $return["SELECT"] = "*";
                $return["WHERE"] = "id= '{$this->_request["search_id"]}'";
            } elseif (isset($this->_request["status_delete"]) && $this->_request["ta"] == "products") {

                $filter = "";

                $return["SELECT"] = " products.id as id_check,purchase_request_id as Nreq,qty as Cantidad, products.description as Descripcion , fomplus_code as Codigo_wo , cost_centre as  Centro_costo ,part_number as PN  ,"
                        . "products.provider as Proveedor , priorities.description as Prioridad , limit_date, users.username as Usuario , products.id as Id_delete ";

                $return["INNER JOIN"] = array("priorities" => "products.priority_id = priorities.id"
                    , "purchase_requests" => "purchase_requests.id = products.purchase_request_id "
                    , "users" => "purchase_requests.user_id  = users.id ");

                if (isset($this->_request["purcharses_id"])) {
                    $filter = " AND  products.purchase_request_id IN({$this->_request["purcharses_id"]}) ";
                }

                $return["WHERE"] = "products.status_delete IN('{$this->_request["status_delete"]}')  $filter";
            }
        }

        return $return;
    }

    function _cols() {
        $return = array();
        if (isset($this->_request["ta"]) && !empty($this->_request["ta"])) {
            $return = array(
                "SHOW" => "COLUMNS",
                "FROM" => $this->_request["ta"]
            );
        }

        return $return;
    }

    function orders() {
        $session = $this->session();
		
        $return = array();

        if (isset($this->_request["user_id"]) || isset($this->_request["purcharId"])) {

            $filter = "user_id = '{$this->_request["user_id"]}' ";
            if (isset($this->_request["purcharId"])) {
                $filter .= " OR purchase_requests.id IN({$this->_request["purcharId"]}) ";
            }

            $return["SELECT"] = " purchase_requests.id as No_requisicion , users.username as Usuario, req_date as Fecha_requisicion ";
            $return["FROM"] = "purchase_requests";
            $return["ORDER BY"] = " purchase_requests.id DESC";
            $return["INNER JOIN"] = array("users" => "purchase_requests.user_id  = users.id ");
            $return["WHERE"] = " {$filter}";
        } else if (isset($this->_request["purcha_id"])) {
            $return = $this->processed();
        } elseif (isset($this->_request["id"])) {
            $return = array("SELECT" => "products.id, products.purchase_request_id as Nreq , products.description as Descripcion ,products.fomplus_code,products.qty ,products.part_number , "
                . " products.manufacturer ,  products.cost_centre ,  products.provider  ,priorities.description as Prioridad ,purchase_requests.req_date , products.approve_date  , products.comments"
                , "FROM" => 'products'
                , "ORDER BY" => "products.id"
                , "INNER JOIN" => array("priorities" => "products.priority_id = priorities.id"
                    , "purchase_requests" => "purchase_requests.id = products.purchase_request_id "
                    , "users" => "purchase_requests.user_id  = users.id ")
                , "WHERE" => array("products.id" => $this->_request["id"] . " AND purchase_requests.is_delete=0 ")
            );
        } elseif (isset($this->_request["all_products_for_user"])) {
            $return = array("SELECT" => "products.id as Product_id, products.purchase_request_id as Nreq , products.description as Descripcion ,products.fomplus_code as Cod_Wo,products.qty as cnt ,products.part_number as P_numero , "
                . " products.manufacturer as Fabricante ,  products.cost_centre  as C_costo,  products.provider  as  Prov_sug ,pn_provider as PN_Prove,priorities.description as Prioridad ,products.comments as Comens, products.limit_date as Limite_fecha"
                , "FROM" => 'products'
                , "ORDER BY" => "products.id"
                , "INNER JOIN" => array("priorities" => "products.priority_id = priorities.id"
                    , "purchase_requests" => "purchase_requests.id = products.purchase_request_id "
                    , "users" => "purchase_requests.user_id  = users.id ")
                , "WHERE" => "purchase_requests.user_id ={$session->id}" . " AND purchase_requests.is_delete=0 AND status_delete =0");
        } else {
            $status = " user_id = '{$session->id}' AND  purchase_requests.is_delete = 0 ";
            $show_status = "purchase_requests.status as Estado,purchase_requests.id as Id";
            $check = "";
            if (isset($this->_request["deletes"])) {
                $status = "  purchase_requests.is_delete =  1";
                $show_status = " purchase_requests.id as Id_delete";
                $check = " purchase_requests.id as id_check , ";
            }

            $return["SELECT"] = "{$check} purchase_requests.id as No_requisicion , users.username as Usuario, req_date as Fecha_requisicion , $show_status ";
            $return["FROM"] = "purchase_requests";
            $return["ORDER BY"] = " purchase_requests.id DESC";
            $return["INNER JOIN"] = array("users" => "purchase_requests.user_id  = users.id ");
            $return["WHERE"] = $status;
        }
		
        return $return;
    }

    function nextPurcharse() {
        $session = $this->session();
        return array(
            "SELECT" => "MAX(id) as next",
            "FROM" => 'purchase_requests',
            "WHERE" => 'user_id =  ' . $this->normalizeUserId($session->id)
        );
    }

    function ReSession() {
        $session = $this->session();
        return array(
            "SELECT" => "u.id, u.name , u.lastname, u.username,u.email, u.user_type_id, ut.description AS description",
            "FROM" => 'users u, user_types ut',
            "WHERE" => " u.id={$session->id} AND state=1 "
        );
    }

    //SET  INSERT DATA

    function save() {
        $return = array();
        if (is_array($this->_request) && !empty($this->_request)) {
            file_put_contents("loggerSave.txt", print_r($this->_request, true) . "\n", FILE_APPEND);
            $keys = "";
            $values = "";
            if (isset($this->_request["ta"]) && $this->_request["ta"] == 'users') {
                //checking  ids disponibles
                /* $id = $this->checkDisponibleIdUser();
                  if ($id != 0) {
                  $keys .= "id,";
                  $values .= "$id,";
                  } */
            }

            foreach ($this->_request as $key => $value) {

                if ($key != "qr" && $key != "ta" && $key != "require_lastId" && $value != "") {
                    $keys .= " $key , ";
                    $sep = "'";
                    $andle1 = array('"', "'");
                    $andle2 = array('<', ">");
                    $values .= $sep . str_replace($andle1, $andle2, $value) . $sep . ",";
                }
            }
            if (isset($this->_request["ta"])) {
                if (isset($this->_request["require_lastId"]) && $this->_request["require_lastId"])
                    $this->_requireLastId = true;
                $return["INSERT INTO"] = " {$this->_request["ta"]} ";
                $return["()"] = substr($keys, 0, -3);
                $return["VALUES"] = substr($values, 0, -1);
            }
        }
        return $return;
    }

    //SET  update DATA

    function update() {
        $return = array();
        if (is_array($this->_request) && !empty($this->_request)) {
            $set = "";

            foreach ($this->_request as $key => $value) {
                $key = trim($key);
                if (($key != "qr" && $key != "co" && $key != "purcharses_id" && $key != "ta" && $key != "_id" && $key != "key" && (($value == "" && $key == "fomplus_code" ) || $value != "" ))) {
                    if (is_string($value)) {

                        $sep = "'";
                        $andle1 = array('"', "'");
                        $andle2 = array('<', ">");
                        $val = $value;
                        if (strpos($value, "'") || strpos($value, '"'))
                            $val = str_replace($andle1, $andle2, $value) . ",";
                        $set .= $key . ' = "' . $val . '",';
                    } else {
                        $set .= "$key = $value , ";
                    }
                }
            }
            if (isset($this->_request["ta"]) && (isset($this->_request["_id"]) || isset($this->_request["purcharses_id"]))) {

                if (isset($this->_request["sub_main"])) {
                    $set = substr($set, 0, -1);
                } else {
                    $set = substr($set, 0, -1);
                    $set = trim($set, ',');
                }

                if ($this->_request["ta"] == "users" && !isset($this->_request["state"]) && empty($this->_request["state"])) {
                    $set .= "  , state = 0";
                }

                $return["UPDATE"] = " {$this->_request["ta"]} ";
                $return["SET"] = trim($set, ',');

                if (stripos($this->_request["_id"], '(') && stripos($this->_request["_id"], ')')) {
                    $id = "id " . $this->_request["_id"];
                } elseif (isset($this->_request["purcharses_id"])) {
                    $id = " purchase_request_id  IN(" . $this->_request["purcharses_id"] . ")";
                } else {
                    $id = "id=" . $this->_request["_id"];
                }

                $return["WHERE"] = $id;
            }
        }
        return $return;
    }

    //UNSETT -> delete Data

    function delete() {
        $return = array();

        if (isset($this->_request["ta"]) && $this->_request["ta"] != "") {

            $return["DELETE FROM"] = $this->_request["ta"];
            $where = ".";

            if (isset($this->_request["in"]) && !empty($this->_request["in"])) {
                $where = "id in ({$this->_request["in"]})";
            } elseif (isset($this->_request["id"]) && !empty($this->_request["id"]) && strpos($this->_request["id"], "(") && strpos($this->_request["id"], ")")) {
                $where = "id {$this->_request["id"]} ";
            } elseif (isset($this->_request["id"]) && !empty($this->_request["id"])) {
                $where = "id = {$this->_request["id"]} ";
            }
            $return["WHERE"] = $where;
        }
        return $return;
    }

    function Getproducts() {
        $session = $this->session();
        if (isset($this->_request["date_ini"]) && isset($this->_request["date_end"])) {
            $filter = "";
            if (isset($session->user_type_id) && $session->user_type_id == 2) {
                $filter = " AND purchase_requests.user_id = {$session->id}";
            }

            $return = array(
                "SELECT" => "users.username as Usuario , products.purchase_request_id as Nreq , products.description as Descripcion,products.status as Estado , fomplus_code as codeWo,products.cost_centre as Centro_Costo ,part_number as PN  ,products.provider as Proveedor   , qty as Cantidad_solicitada, cnt as Cantidad_Procesada ",
                "FROM" => 'products',
                "INNER JOIN" =>
                array(
                    'purchase_requests' => 'purchase_requests.id = products.purchase_request_id',
                    'users' => 'users.id = purchase_requests.user_id',
                    'priorities' => "priorities.id = products.priority_id"
                ),
                "WHERE" => "products.approve_date >= '{$this->_request["date_ini"]}' AND products.approve_date <= '{$this->_request["date_end"]}'  {$filter}" . " AND purchase_requests.is_delete=0 AND status_delete =0"
            );
        }

        return $return;
    }

    function SetqueryString() {
        if (isset($this->_request["data"]) && $this->_request["data"] != "") {
            $this->_insert = true;
            return $this->_request["data"];
        }
    }

    function queryString() {
        if (isset($this->_request["data"]) && $this->_request["data"] != "") {
            $this->is_selectString = true;
            return $this->_request["data"];
        }
    }

    function truncate() {
        if (isset($this->_request["ta"]) && $this->_request["ta"] != "") {
            $this->is_multi = true;
            return "TRUNCATE {$this->_request["ta"]} ";
        }
    }

    function processConfirm() {
        $return = array();
    }

    function checkDisponibleIdUser() {
        $disponible_id = 0;
        $this->sql = "SELECT id FROM users;";
        $data = $this->connect();
        if (isset($data['data'][0]['id'])) {
            $ids = $data['data'];
            $idsArray = array();
            foreach ($ids as $key => $value) {
                $idsArray[] = $value['id'];
            }

            $max = max($idsArray);

            for ($x = 1; $x < $max; $x++) {
                if (!in_array($x, $idsArray)) {
                    $this->sql = "SELECT * FROM users WHERE id=$x;";
                    $response = $this->connect();
                    if (empty($response)) {
                        $disponible_id = $x;
                    }
                    break;
                }
            }
        }
        return $disponible_id;
    }

}
