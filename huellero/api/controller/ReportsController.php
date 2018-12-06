<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportsController
 *
 * @author Luis Ramos
 */
require_once PATH . "controller/Shellcontroller.php";

class ReportsController extends Shellcontroller {

    public $Excel = null;

    public function __construct() {
        parent::__construct();
    }

    function _getReports($_request = null) {
        //$this->huellero->deleteTable('reports');
        //$this->CreateTableReport();
        $session = json_decode($this->session(), true);
        if (!isset($session['Userid'])) {
            $session['Userid'] = 0;
        }

        $reports = $this->_get('Reportes', "status as Estado, "
                . "name as Nombre , type as Periodo,mails as Emails , hour as Hora, id as _ID", " user_id='{$session['Userid']}'");

        $tempReports = json_decode($reports, true);

        if ($tempReports[0] == "ERROR" && $session['Userid'] == 0) {
            $reports = json_encode(array("status" => 0));
        }
        return $reports;
    }

    function _getReport($_request = null) {
        $reports = $this->_get('Reportes', "*", "id={$_request['id']}", false);
        $reports = $this->_return($reports[0]);
        return $reports;
    }

    private function CheckTaskName($data, $id = false) {
        if (isset($data)) {
            $conditions = " `name`='$data' ";
            if ($id) {
                $conditions = "`id`=$data";
            }
            $datas = $this->huellero->GET('Reportes', "*", $conditions);
            return $datas;
        }
        return array();
    }

    function _DeleteTask($_request = null) {
        $status = array('status' => -1);
        if (isset($_request['id'])) {

            $_statusDelete = $this->huellero->DELETE('Reportes', $_request['id'], "", true);

            if (!empty($_statusDelete) && isset($_statusDelete[0]['name'])) {
                $statusDelete = $this->DeleteTask($_statusDelete[0]['name']);
                $status["status"] = empty($statusDelete) ? 0 : 1;
            }
        }
        return $this->_return($status);
    }

    function _newReport($_request = null) {
        $response = array("status" => -2);
        #CHECK IF EXISTE NOMBRE DE REPORTE
        $checkName = $this->CheckTaskName($_request["name"]);

        if (!empty($checkName) && !isset($_request["id"])) {
            $response["status"] = -1;
        } else {
            $continue = true;
            $info = array();
            $session = json_decode($this->session(), true);
            if (!isset($_request['id'])) {
                $_request["user_id"] = isset($session['Userid']) ? $session['Userid'] : -1;
            } else {

                $info = $this->CheckTaskName($_request["id"], true);
                $info = isset($info[0])?$info[0]:array();
                if (isset($info['name']) && $info['name'] != $_request['name']) {
                    $infoCompare1 = $this->huellero->GET('Reportes', '*', "`name` ='{$info["name"]}' "); //TRUE
                    $infoCompare2 = $this->huellero->GET('Reportes', '*', "`name` ='{$_request["name"]}' "); //TRUE
                    //file_put_contents("loggerCompare.txt", print_r($infoCompare1,true)."\n". print_r($infoCompare2,true));
                    if (!empty($infoCompare1) && !empty($infoCompare2)) {
                        $info = array();
                        $response["status"] = -1;
                        $continue = FALSE;
                    }
                }
            }
            if ($continue) {

                $_request = $this->cleanType($_request);
				
				if(isset( $_request['type']) && $_request['type'] =='biweekly'){
					 $_request['hour'] = "08:00";
				}
                //GUARDO EN LA BASE DE DATOS DE HUELLERO
                unset($_request["action"]);
                $_request["group"] = implode(",", $_request["group"]);
                $_request["user"] = implode(",", $_request["user"]);
                $status = $this->huellero->SAVE('Reportes', $_request);
                if (isset($status['status']) && $status['status'] == 1) {
                    if (!isset($_request['id'])) {
                        $_request['id'] = $this->huellero->last_id('Reportes');
                    }
					if(isset( $_request['type']) && $_request['type'] =='biweekly'){
					 unset($_request['hour']);
				    }
                    //create Tasks in programe tasks whith shell
                    $_request["action"] = "Task";
                    $_request["method"] = "Create";


                    if (isset($info['name']) && $info['name'] != $_request['name']) {
                        //delete old programa name
                        $statusDelete = $this->DeleteTask($info['name']);
                        //
                    }
                    //file_put_contents('infoSave.log', print_r($_request, true) . "\n", FILE_APPEND);
                    $shell = $this->string_shell($_request);

                    $resultado = $this->exec($shell);
                    file_put_contents('shell.log', print_r($shell, true) . "\n", FILE_APPEND);

                    $status = strpos($resultado, 'Correcto:') ? 1 : 2;
                    if ($status == 2) {
                        //delete for bd for fails 
                        $status2 = $this->huellero->DELETE('Reportes', 0, " name='{$_request['name']}' ");
                    }
                    $response["status"] = $status;
                } else {
                    $response["status"] = 0;
                }
            }
        }
        //RETORNO LA RESPUESTA AL CLIENTE PAGE WEB
        return $response;
    }

    function testReport($_request = null) {
        $status = array('status' => -1);
        if (isset($_request['id'])) {
            $data = $this->huellero->GET('Reportes', "*", " `id`={$_request['id']} ");
            if (!empty($data) && isset($data[0]['name'])) {
                $_request['name'] = $data[0]['name'];
            }
        }

        if (isset($_request['name'])) {
            $response = $this->START_TASK($_request['name']);
            $status['status'] = (strpos($response, 'tarea') ? 1 : 0);
        }
        return $this->_return($status);
    }

    function cleanType($_request) {
        switch ($_request["type"]) {
            case '':
                $_request["mes"] = "";
                $_request["Dmes"] = "";
                break;
            default:
                break;
        }
        return $_request;
    }

    function ExcelReport($_request = null) {

        $_request = $this->CheckTaskName($_request["id"], true);
        $_request = $_request[0];

        $status = array('status' => -1);
        $name = "Report_aut " . date("Y_m_d H_i_s");
        if (isset($_request["name"]) && !empty($_request["name"])) {
            $name = $_request["name"];
        }
		
        $this->_type_Report($_request);
		$_request['next_Task'] = true;
		$dia = date('d');//get dia
		$dia = 14;
		
		if($_request["type"] !="biweekly"){
			
		
		
			$data = json_decode($this->getHoursJob($_request), true);
			$_request['Periodo'] = $this->traslate_type_report($_request["type"]);
			
			if (isset($data["data"]) && !empty($data["data"])) {
				$typeOfShellData = $_request['opReport'];
				$options = array("Filename" => "../shell/files/{$name}", 'sheet' => 'Reporte Huellero');

				$this->Excel(null, null, $options);
				$styles = array(
					'font' => array(
						'color' => array('rgb' => 'FF0000'),
						'size' => 12
					)
				);

				$cols = array_combine(array('B', 'C', 'D', 'E', 'F'), $data["keys"]);
				//ninguna
				if ($typeOfShellData == 0) {
					$reporte = $data['data'];

					foreach ($reporte as $key => $column) {
						$reporte[$key]["Tarde"] = 0;
						//$reporte[$key]["Fecha"] = "";
						#Calcular si LLEGA TARDE LA PERSONA


						if ($column["Entrada"] != "NO TIENE") {
							$date = explode(" ", $column["Entrada"]);

							$entrada = $column["Entrada"];
							$entradasPermitidas = array("{$date[0]} 08:00:00", "{$date[0]} 13:00:00", "{$date[0]} 12:00:00", "{$date[0]} 18:00:00");

							$dateTime1 = new DateTime($entradasPermitidas[0]);
							$dateTime2 = new DateTime($entradasPermitidas[1]);
							$dateTime3 = new DateTime($entradasPermitidas[2]);
							$dateTime4 = new DateTime($entradasPermitidas[3]);
							$entrada = new DateTime($entrada);

							$entradaMaxima = $dateTime1->getTimestamp(); //08 AM
							$salidaMedioDiaMinimo = $dateTime3->getTimestamp(); //12 PM
							$salidaTardeMaximo = $dateTime2->getTimestamp(); //01 PM
							$salidaTardeMinimo = $dateTime4->getTimestamp(); //18 horas

							$entrada = $entrada->getTimestamp();

							if (($entrada > $entradaMaxima && $entrada < $salidaMedioDiaMinimo) || ($entrada > $salidaTardeMaximo && $entrada < $salidaTardeMinimo)) {
								#Tarde
								$reporte[$key]["Tarde"] = 1;
							}
						}

						$reporte[$key]["HORAS TRABAJADAS"] = $column["Horas Trab"];
					}

					$cols["F"] = "HORAS TRABAJADAS";
					$optionsStyles = array(
						"styles" => array(
							'font' => array(
								'color' => array('rgb' => 'FF0000'),
								'size' => 12,
							)),
						"data" => $reporte,
						"columns" => $cols,
						"conditions" => array("Tarde" => 1)
					);

					$optionsStyles = array_merge($optionsStyles, $_request);
					$sheet = $this->Excel->setSheet(0, "", null, $optionsStyles);
				} else if ($typeOfShellData == 1 || $typeOfShellData == 2) {
					#GUARDAR HOJA POR TRABAJADOR. OR  //GUARDAR HOJA POR AREAS
					$data = $data['data'];
					$columna = "Trabajador";
					if ($typeOfShellData == 2) {
						$columna = "Area";
					}
					$info_trabajador = array();
					#AGRUPAR DATOS POR USUARIO O POR AREAS
					foreach ($data as $id => $column) {
						if (!isset($info_trabajador[$column["{$columna}"]])) {
							$info_trabajador[$column["{$columna}"]] = array();
						}
						$column["Tarde"] = 0;
						#Calcular si LLEGA TARDE LA PERSONA
						if ($column["Entrada"] != "NO TIENE") {
							$date = explode(" ", $column["Entrada"]);
							$entrada = $column["Entrada"];

							$entradasPermitidas = array("$date[0] 08:00:00", "$date[0] 13:00:00", "$date[0] 12:00:00", "$date[0] 18:00:00");

							$dateTime1 = new DateTime($entradasPermitidas[0]);
							$dateTime2 = new DateTime($entradasPermitidas[1]);
							$dateTime3 = new DateTime($entradasPermitidas[2]);
							$dateTime4 = new DateTime($entradasPermitidas[3]);
							$entrada = new DateTime($entrada);

							$entradaMaxima = $dateTime1->getTimestamp(); //08 AM
							$salidaMedioDiaMinimo = $dateTime3->getTimestamp(); //12 PM
							$salidaTardeMaximo = $dateTime2->getTimestamp(); //01 PM
							$salidaTardeMinimo = $dateTime4->getTimestamp(); //18 horas

							$entrada = $entrada->getTimestamp();

							if (($entrada > $entradaMaxima && $entrada < $salidaMedioDiaMinimo) || ($entrada > $salidaTardeMaximo && $entrada < $salidaTardeMinimo)) {
								#Tarde
								$column["Tarde"] = 1;
							}
						}

						$column["HORAS TRABAJADAS"] = $column["Horas Trab"];
						$info_trabajador[$column["$columna"]][] = $column;
					}

					//file_put_contents("info.log", print_r($info_trabajador, true));
					#CREAR HOJA POR TRABAJADOR.. O POR   //GUARDAR HOJA POR AREAS
					$contador = 0;
					$Title = "";

					$cols["G"] = "HORAS TRABAJADAS";

					foreach ($info_trabajador as $Nombre_trabajador => $reporte) {

						$sheet = $this->Excel->setSheet($contador, $Nombre_trabajador, null, array_merge(array(
							"styles" => $styles,
							"data" => $reporte,
							"columns" => $cols,
							"conditions" => array("Tarde" => 1)
										), $_request));
						if ($contador == 0) {
							$Title = $Nombre_trabajador;
						}
						$contador++;
					}
					if (!empty($Title)) {
						$this->Excel->RenameSheet($Title);
					}
				}

				$this->Excel->Execute();

				//upload and send mail file reporte
				$upload = json_decode($this->UploadFile($this->Excel->Filename), true);
				$upload["name"] = $_request["name"];

				$upload["subject"] = "Reporte {$_request['Periodo']} Huellero";
				$upload["message"] = file_get_contents("controller/header.html") .
						"<br><h2>Reporte Huellero automatico</h2>"
						. "<h4>Informacion Realizada  </h4><br><b>Desde :</b> {$_request['desde']}  <br><b>Hasta : </b>{$_request['hasta']} ";
				$upload["mails"] = (isset($_request["mails"]) ? explode(',', $_request["mails"]) : array('soporte_dst@onlinedst.com'));

				$status['status'] = $this->Mail($upload);

				file_put_contents('logger.log', date('y-m-d H:i:s') . "-> status  {$status['status']}" . "\n", FILE_APPEND);
			}
		}else if($_request["type"] =="biweekly" && $dia ==14){
			unset($_request['next_Task']);
		}

        /*
         * volver a programa si solo es quincenal    
         */

        if (isset($_request['id']) && isset($_request['name'])) {
            #NEXT MES
            $_request["action"] = "Task";
            $_request["method"] = "Create";
            
            $shell = $this->string_shell($_request);
            $resultado = $this->exec($shell);
            file_put_contents('shell.log', print_r($_request, true) . print_r($shell, true) . "\n{$resultado}\n", FILE_APPEND);

            $status['update'] = strpos($resultado, 'Correcto:') ? 1 : 2;
        }

        return $this->_return($status);
    }

    function traslate_type_report($type) {
        $array = array(
            'daily' => "Diario",
            'weekly' => 'Semanal',
            'biweekly' => 'Quincenal',
            'monthly' => 'Mensual',
            'biannual' => 'Semestral',
            'annual' => 'Anual');

        return $array[$type];
    }

    private function Excel($null = null, $null2 = null, $options = array()) {
        require_once PATH . "controller/Excel.php";
        $this->Excel = new EXCEL($null, $null2, $options);
    }

    private function _type_Report(&$_request) {
        if (isset($_request["type"])) {
            $Yesterday = date('Y-m-d', strtotime('-1 day')); // resta 1 día
            $last_wekly = date('Y-m-d', strtotime('-1 week')); // resta 1 semanas
            $last_biwekly = date('Y-m-d', strtotime('-2 week')); // resta 2 semana
            $lastMonth = date('Y-m-d', strtotime('-1 month')); // resta 1 mes
            $lastBiannual = date('Y-m-d', strtotime('-6 month')); // resta 6 mes
            $lastYear = date('Y-m-d', strtotime('-1 year')); // resta 1 año
            ##verifico que sean cada 15 dias para quincenal
            if ($_request['type'] == 'biweekly') {
                
            }

            $types = array(
                'daily' => array('desde' => $Yesterday, 'hasta' => date('Y-m-d')),
                'weekly' => array('desde' => $last_wekly, 'hasta' => date('Y-m-d')),
                'biweekly' => array('desde' => $last_biwekly, 'hasta' => date('Y-m-d')),
                'monthly' => array('desde' => $lastMonth, 'hasta' => date('Y-m-d')),
                'biannual' => array('desde' => $lastBiannual, 'hasta' => date('Y-m-d')),
                'annual' => array('desde' => $lastYear, 'hasta' => date('Y-m-d'))
            );
            $type = $types[$_request["type"]];
            $_request = array_merge($_request, $type);
        }
    }

}
