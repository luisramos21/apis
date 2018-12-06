<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Shellcontroller
 *
 * @author Luis Ramos
 */
class Shellcontroller {

    //primer dia de cada mes SCHTASKS /Create /tn "My App" /tr myapp.exe /sc mensual
    //cada tres meses SCHTASKS /Create /tn "My App" /tr c:\aps\miap.exe /sc monthly /mo 3
    //cada dos semanas el viernes SCHTASKS /Create /tn "My App" /tr c:\aps\miap.exe /sc weekly /mo 2 /d VIE
    //delete SCHTASKS /delete /tn "Start Mail"
    private $pass = 'd1s3tr0n.2016*';
    private $comands = array("P" => 'php',
        "Task" => array('Create' =>
            array(
                'SCHTASKS /create  ',
                'name' => ' /tn "%s" ',
                'task' => ' /tr %s ',
                'type' => array(
                    'daily' => ' /sc daily ',
                    'weekly' => ' /sc  weekly ',
                    'biweekly' => ' /sc once /sd %s /st 08:00',
                    'once' => '/sc once',
                    'monthly' => '/sc monthly ',
                    'biannual' => ' /sc monthly  /MO 6 ',
                    'annual' => ' /sc monthly  /MO %s '),
                'month' => '',
                'days' => '/mo %s',
                'start_date' => '/sd %s',
                'end_date' => '/ed %s',
                'hour' => '/st %s:%s',
                'replace' => ' /f ',
                'dayMes' => '  /d %s ',
                "daySemana" => array(1 => ' /d MON', 2 => ' /d TUE', 3 => ' /d WED', 4 => ' /d THU', 5 => ' /d FRI', 6 => ' /d SAT')
            ),
            "disabled" => 'SCHTASKS /Change /tn "%s" /disable',
            "enable" => 'SCHTASKS /Change /tn "%s" /enable',
            "Query" => "SCHTASKS /query /tn %s",
            "Delete" => 'SCHTASKS /delete /tn "%s" /f',
            "START" => 'SCHTASKS /Run  /tn "%s"'
        ),
        "Backup" => array("cmd" => "C:/xampp/mysql/bin/mysqldump ",
            "aut" => " --user=%s --password=%s ",
            "bd" => "",
            "save" => '')
    );

    public function __construct() {
        
    }

    function DeleteTask($name) {
        $execute = array(sprintf($this->comands['Task']['Delete'], $name));
        file_put_contents('shellDelete.log', print_r($execute, true) . "\n", FILE_APPEND);
        return $this->exec($execute);
    }

    function exec($comand) {
        $return = "NO_EXECUTE";
        $cnms = "";
        $rn = "";
        if ($comand != "" && is_string($comand)) {
            $rn = shell_exec($comand);
            if (!empty($rn)) {
                $return = '';
                $return .= $rn;
            }
            $cnms = $comand;
        } elseif (is_array($comand)) {
            foreach ($comand as $key => $value) {
                $cnms .= "\n" . $value;
                $rn .= "\n" . shell_exec($value);
                if (!empty($rn)) {
                    if ($key == 0) {
                        $return = '';
                    }
                    $return .= $rn;
                }
            }
        }
        return $return;
    }

    function string_shell($options = array()) {
        $strins_shell = "";
        if (isset($options["action"]) && !empty($options["action"]))
            switch ($options["action"]) {
                case 'Task':
                    unset($options["action"]);
                    switch ($options["method"]) {
                        case 'Create':
                            unset($options["method"]);
                            $strins_shell = $this->new_task($options);
                            break;
                    }
                    break;
            }
        return $strins_shell;
    }

    function new_task($options = array()) {
        //example SCHTASKS /Create /tn "My" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\api\huellero\shell\api.php id=2" /sc daily /st 11:59 /f
        //schtasks /create   /tn "new reporte"  /sc daily 
        $comand = $this->comands["Task"]["Create"];
        $task = $comand[0];
        if (!isset($options["name"]))
            die('fail Name Required for create Task');


        if (isset($options["name"])) {
            $task .= sprintf($comand["name"], $options["name"]);
        }

        if (isset($options["type"]) && key_exists($options["type"], $comand['type'])) {
            $task .= $comand["type"][$options["type"]];
        }
		
		file_put_contents('shell.log', date('y-m-d H:i:s') . "\n".print_r($options,true) . "\n", FILE_APPEND);
        ##Hora del Reporte
        if (isset($options["hour"]) && $options["type"] != "biweekly") {
            $hour = $options["hour"];
            $minute = date("i");

            if (!strpos($options['hour'], ":")) {
                if ($options["hour"] < 10) {
                    $hour = "0$hour";
                }
				
                if ($minute  < 10) {
                    $minute = "0{$minute}";
                }
            } else {
                $h = explode(":", $options["hour"]);
                $hour = $h[0];
                $minute = $h[1];
            }

            $task .= sprintf($comand["hour"], $hour, $minute);
        } else if ($options["type"] == 'biweekly') {
            # SOLO PARA PROGRAMACION DE TIPO Quincenal
            $dia = date('d');
            $fecha = date('d-m-Y');
            //TEST
            //$dia = 30;
            $lastDay = $this->lastDayMonth();
            /* si el dia  es  mayor a 15 se ejecuta el ultimo dia del mes  */
            if ($dia > 15 && $dia != $lastDay && !isset($options['next_Task'])) {
                $fecha = $this->lastDayMonth('d-m-Y');
            } else if ($dia >= 15 && isset($options['next_Task'])) {
                /*  SI EL DIA DE HOY ES 31 ENTONCES PROGRAMAR PARA EL 15 DEL OTRO MES
                 *  SI EL DIA DE HOY ES 15 ENTONCES PROGRAMAR PARA EL ULTIMO DIA */
                $fech = explode('-', $fecha);
                $fech[0] = ($dia==15)?$lastDay:15;				

                //TEST DICIEMBRE
                //$fech[1] = 12;

                #siguiente mes
                if ($fech[1] < 12 && $dia !=15) {
					$m = $fech[1];
					if($fech[1] < 10 && strlen($fech[1])==1){
						$m = "0{$m}";
					}else{
						$m +=1;
					}
					$fech[1] = $m;
                } else if ($fech[1] == 12) {
                    #15 Enero Siguiente año
                    $fech[1] = "01";
                    $fech[2] += 1;
                }
                $fecha = implode("-", $fech);
            } else if (!isset($options['next_Task'])) {

                $fech = explode('-', $fecha);
                $fech[0] = 15;
                
                if($lastDay == $dia){
                    $fech[1] += 1;
                }
				if($dia ==15){
                    $fech[0] = $lastDay;
                }
                
                if($fech[1]<10){
                   $fech[1] = "0".(int)$fech[1]; 
                }
                
                $fecha = implode("-", $fech);
            }

            $task = sprintf($task, $fecha);
        }
        $args = " id={$options['id']} ";
        $task .= ' /tr "C:\xampp\php/php.exe C:\xampp\htdocs\api\huellero\shell\api.php ' . $args . '" /f';


        if (isset($options["type"])) {
            switch ($options["type"]) {
                case 'weekly':
                    if (isset($options["daySemana"]) && !empty($options["daySemana"])) {
                        $task .= $this->Task_type($options["daySemana"], $options['type'], $comand);
                    }
                    break;
                case 'monthly':
                case 'biannual':
                    if (isset($options["Dmes"]) && !empty($options["Dmes"])) {
                        $task .= $this->Task_type($options["Dmes"], $options['type'], $comand);
                    };
                    break;
                case 'annual':
                    if (isset($options["mes"]) && !empty($options["mes"])) {
                        $task = sprintf($task, $options["mes"]);
                    }
                    break;
            }
        }
        if (isset($options["status"])) {
            $comand = $this->comands["Task"];
            $exec = $comand["enable"];
            if ((int) $options["status"] == 0) {
                $exec = $comand["disabled"];
            }
            $tasktemp = $task;
            $task = array();
            $extra = "";
            if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 8080) {
                $extra = "  /V1 /RU admin  /RP {$this->pass}";
            }

            $task[0] = $tasktemp . $extra;
            $task[1] = sprintf($exec, trim($options["name"]));
        }
        return $task;
    }

    private function Task_type($weekly, $type_task, $comand) {
        $return = "  ";
        switch ($type_task) {
            case 'weekly':
                $return .= $comand["daySemana"][(int) $weekly];
                break;
            case 'monthly':
            case 'biannual':
                $return = sprintf($comand["dayMes"], $weekly);
                break;
        }
        return $return;
    }

    function START_TASK($name_Tasks) {
        $comand = $this->comands["Task"]["START"];

        $comand = sprintf($comand, $name_Tasks);
        $status = $this->exec($comand);
        return $status;
    }

    function lastDayMonth($format = 'd') {

        $month = date('m');
        $year = date('Y');
        $day = date("d", mktime(0, 0, 0, $month + 1, 0, $year));

        return date($format, mktime(0, 0, 0, $month, $day, $year));
    }

}
