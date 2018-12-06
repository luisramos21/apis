<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of huellero
 *
 * @author Luis Ramos
 */
class huellero {

    public $connection = null;

    public function __construct() {
        $this->connect();
    }

    protected function connect() {
        $this->connection = odbc_connect("huellero", "", "");
    }

    public function last_id($table) {
        $ids = $this->GET($table, " max(id) ");
        $id = 0;
        if (isset($ids[0]['Expr1000'])) {
            $id = $ids[0]['Expr1000'];
        }
        return $id;
    }

    public function GET($table = "", $cols = "*", $conditions = "") {
        $return = array("NO_data");
        if ($this->connection != null && $table != "") {
            if ($conditions != "") {
                $conditions = " WHERE {$conditions}";
            }

            $sql = "SELECT {$cols} FROM `$table` {$conditions}";
            $response = odbc_exec($this->connection, $sql);
            $return = $this->fetch_array($response);

            file_put_contents('sql.txt', "$sql\n", FILE_APPEND);
            if (odbc_error()) {
                $error = odbc_errormsg($this->connection);
                file_put_contents("error.txt", $error . "\n", FILE_APPEND);
            } else {
                $nums = odbc_num_rows($response);
                $output = array();
                while ($e = odbc_fetch_array($response)) {
                    $output[] = $e;
                }
            }
        }
        return $return;
    }

    public function DELETE($table = "", $id = 0, $conditions = "", $returnLastValue = false) {
        $return = array();
        if ($this->connection != null && $table != "") {
            if ($conditions != "" && $id == 0) {
                $conditions = " {$conditions}";
            } else {
                $conditions = " id={$id}";
            }
            $status = $this->GET($table, "*", $conditions);
            if (!empty($status)) {
                $sql = "DELETE FROM `$table` WHERE {$conditions}";
                $return = odbc_exec($this->connection, $sql);
                if($returnLastValue){
                    $return = $status;
                }
            }
            if (odbc_error()) {
                $error = odbc_errormsg($this->connection);
                file_put_contents("error.txt", $error . "\n", FILE_APPEND);
            }
        }
        return $return;
    }

    public function deleteTable($table = "") {
        $return = array("NO_data");
        if ($this->connection != null && $table != "") {
            $sql = "DROP TABLE {$table};";
            $return = odbc_exec($this->connection, $sql);

            if (odbc_error()) {
                $error = odbc_errormsg($this->connection);
                file_put_contents("error.txt", $error . "\n", FILE_APPEND);
            }
        }
        return $return;
    }

    public function SAVE($table = "", $data) {
        $return = array("NO_data");
        if ($this->connection != null && $table != "") {
            $update = false;
            $col_id = 'id';
            if ((isset($data['id']) || isset($data["ID"])) && (!empty($data["ID"]) || !empty($data["id"]))) {
                $update = true;
            }
            if (isset($data['ID'])) {
                $col_id = "ID";
            }

//            $colums = implode(", ", array_keys($data));
//            $output = array_map(function($val) {
//                return "?";
//            }, $data);
//            $values = implode(",", $output);

            $colums = '`' . implode("`,`", array_keys($data)) . '`';
            $values = "'" . implode("','", $data) . "'";
            $sql = "INSERT INTO `{$table}` ({$colums}) VALUES({$values})";
            if ($update) {
                $id = $data["$col_id"];
                unset($data["$col_id"]);
                $set = "";
                foreach ($data as $key => $value) {
                    $set .= "`$key`='$value' ,";
                }
                $set = substr($set, 0, -1);

                $sql = "UPDATE `{$table}` SET {$set} WHERE id=$id";
            }

            $stm = odbc_exec($this->connection, $sql);

            file_put_contents("sql.txt", $sql . "\n", FILE_APPEND);
            if (odbc_error()) {
                $error = odbc_errormsg($this->connection);
                file_put_contents("error.txt", ($stm) ? 1 : 0 . "\n$error\n", FILE_APPEND);
            }

            $return = array("status" => ($stm) ? 1 : 0);
            odbc_close($this->connection);
        }
        return $return;
    }

    public function createTable($Table = array()) {
        $return = array("NO");
        if (isset($Table) && is_array($Table) && !empty($Table)) {
            /* example result
              CREATE TABLE `mytest` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `job` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`id`));
             *              */
            $tables = "";
            foreach ($Table as $key => $value) {
                $tables .= " CREATE TABLE `$key` (";
                foreach ($value as $row => $type) {
                    if ($row != "PRIMARY KEY") {
                        $tables .= "`$row` $type ,";
                    } else {
                        $tables .= "$row $type ,";
                    }
                }
                $tables = substr($tables, 0, -1);
                $tables .= ");";
            }
            if ($tables != "" && isset($this->connection) && $this->connection != null) {
                $return = odbc_exec($this->connection, $tables);

                if (odbc_error()) {
                    $error = odbc_errormsg($this->connection);
                    file_put_contents("error.txt", $error . "\n", FILE_APPEND);
                }
            }
        }
        return $return;
    }

    function addCol($table, $cols = array()) {
        $return = array("NO_create");
        if ($cols != "" && $table != "") {
            $sql = "ALTER TABLE $table "
                    . "ALTER COLUMN ";
            foreach ($cols as $key => $value) {
                $sql .= "$key $value ";
            }

            $sql = substr($sql, 0, -1);
            if ($sql != "" && isset($this->connection) && $this->connection != null) {
                $return = odbc_exec($this->connection, $sql);
                echo odbc_errormsg();
                exit;
            }
        }
        return $return;
    }

    function getusers($legal = "", $user = false, $deps = false) {
        if ($this->connection != null) {
            $table = 'Userinfo';
            $select = "*";
            $where = "";
            if ($deps != "" && $deps) {
                $where = " INNER JOIN Dept ON Userinfo.Deptid = Dept.Deptid";
                $select = "Userid as id , Name as Nombre , Sex as Sexo , DeptName as Departamento";
            }
            if ($legal != "" && $legal != 1) {
                $where = " WHERE Userinfo.Deptid IN($legal)";
            }
            if ($user) {
                $table = "user";
            }
            $sql = "SELECT $select FROM $table $where";

            $result = odbc_exec($this->connection, $sql);
            return $this->fetch_array($result);
        } else {
            return array("data" => "ERROR");
        }
    }

    private function fetch_array($result) {
        $tmp = array();

        if ($result != null) {
            while (($row = odbc_fetch_array($result)) != false) {
                $tmp[] = $row;
            }
        }
        return $tmp;
    }

    function Deps() {
        if ($this->connection != null) {
            $sql = "SELECT * FROM Dept;";
            $result = odbc_exec($this->connection, $sql);
            return $this->fetch_array($result);
        } else {
            return array("data" => "ERROR");
        }
    }

    function session($user, $password) {
        if ($this->connection != null) {
            $sql = "SELECT * FROM `Userinfo` INNER JOIN Dept ON Userinfo.Deptid = Dept.Deptid  WHERE `Userinfo`.`NativePlace` = '$user' and `Userinfo`.`IDCard` = '$password'";
            $result = odbc_exec($this->connection, $sql);
            if (odbc_error($this->connection) > 0) {
                file_put_contents('error.txt', odbc_errormsg($this->connection));
            }
            $response = $this->fetch_array($result);
            if (isset($response[0])) {
                $response = $response[0];
            }
            return $response;
        } else {
            return array("data" => "ERROR");
        }
    }

    function cambiarpass($userid, $password) {

        if ($this->connection != null) {
            $sql = "UPDATE `Userinfo` SET `IDCard`='$password'  WHERE `Userinfo`.`Userid` = '$userid' ";
            $result = odbc_exec($this->connection, $sql);
            $response = odbc_num_rows($result);
            return $response;
        }
    }

    function HoursJob($group = "", $legal = "", $desde = null, $hasta = null) {
        if ($this->connection != null && $desde != null && $hasta != null) {
            array_map('unlink', glob("*.txt"));
            $hasta .= " 23:59:59";
            $desde .= " 00:00:00";


            if ($legal == 0)
                $legal = "";
            if ($group == 1)
                $group = "";

            if (is_array($legal)) {
                $legal = "'" . implode("','", $legal) . "'";
                $legal = " IN($legal) ";
            } else if (strpos($legal, ",")) {
                $legal = "'" . implode("','", explode(',', trim($legal, ','))) . "'";
                $legal = " IN($legal) ";
            } else if ($legal != '') {
                $legal = " = '$legal' ";
            }

            if (is_array($group)) {
                $group = implode(',', $group);
                $group = " IN($group) ";
            } else if (strpos($group, ",")) {
                $group = implode(',', explode(',', $group));
                $group = " IN($group) ";
            } else if ($group != '') {
                $group = " = $group ";
            }

            $consul = ($legal != null && $legal != "") ?
                    "Userinfo.Userid $legal AND " : "";

            $consul .= ($group != "" && $group != null) ? "Userinfo.Deptid $group  AND " : "";

            $sql = "SELECT Userinfo.Userid , Checkinout.CheckTime, Checkinout.CheckType , "
                    . "Userinfo.Name, Dept.DeptName FROM ((Checkinout LEFT JOIN Userinfo ON Checkinout.Userid = Userinfo.Userid) "
                    . "LEFT JOIN Dept ON Userinfo.Deptid = Dept.Deptid)  "
                    . "WHERE $consul Checktime >= #$desde# and Checktime <=  #$hasta# ORDER BY Checkinout.Userid, CheckTime ,Dept.DeptName DESC";
            file_put_contents("sql.txt", "$sql\n", FILE_APPEND);
            $result = odbc_exec($this->connection, $sql);
            if (odbc_error()) {
                file_put_contents("error.txt", odbc_errormsg($this->connection) . "\n", FILE_APPEND);
            }
            return $this->fetch_array($result);
        } else {
            return array("data" => "ERROR");
        }
    }

}
