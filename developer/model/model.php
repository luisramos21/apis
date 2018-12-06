<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of model
 *
 * @author admin
 */
require_once PATH . "model/database.php";

class model extends Database {

    public function __construct() {
        $this->connect();
    }

    public function getAll($table = null, $extra = '') {
        $this->connect();
        $this->execute("SELECT * FROM `$table`");
        $json = array();
        if ($this->num_rows($devices) > 0) {
            while ($temp = $this->to_array($devices)) {
                $json[] = $temp;
            }
        }
        $this->disconnect();
        return $json;
    }

    public function getForColumn($table, $column = '', $value = '', $type = 'String') {

        if ($type == 'int') {
            $value = "$value";
        } else {
            $value = "'$value'";
        }

        return $this->getAll($table, " WHERE $column=$value");
    }

    function save($table, $data) {

        $return = false;
        if ($data != "" && $data != null && $table != "" && $table != null) {
            $sql = "";
            if (!$update) {
                $columnas = '`' . implode("`,`", array_keys($data)) . '`';
                $extra = '';
                if ($validate != '') {
                    foreach ($validate as $key => $value) {
                        if (isset($data[$key]) && $value == 'int' && $data[$key] != '') {
                            $extra .= "{$data[$key]},";
                            unset($data[$key]);
                        }
                    }
                }
                if (!empty($extra)) {
                    $extra = "," . substr($extra, 0, -1);
                }

                $values = "'" . implode("','", $data) . "'";
                $sql = "INSERT INTO `{$table}` ({$columnas}) VALUES({$values}{$extra});";
            } elseif ($update) {
                $id = $data["id"];
                unset($data["id"]);
                $set = "";
                foreach ($data as $key => $value) {
                    $set .= "$key='$value' ,";
                }
                $set = substr($set, 0, -1);
                $where = "WHERE id=$id";
                if (is_array($id)) {
                    foreach ($id as $key => $value) {
                        $where = "WHERE $key $value";
                    }
                }
                $sql = "UPDATE `{$table}` SET {$set} {$where}";
            }

            $this->connect();
            $result = $this->execute($sql);

            if ($this->affected_rows() || $result) {
                $return = true;
                if ($last_id) {
                    $return = $this->get_insert_id();
                }
            }
            $this->disconnect();
        } else {
            return array("array data empty and table name null");
        }
        return $return;
    }

    function _delete($table = null, $id = null, $col = null, $in = false) {
        $return = array("status" => false);
        if ($table != "" && $id != null && $id != 0) {
            $this->connect();
            $col = ($col == null) ? "`id` " : $col;
            $compare = ((!$in) ? " {$col}=$id " : ($in ? " {$col} IN ($id) " : ""));
            
            if ($compare != '') {
                $sql = "DELETE FROM `{$table}` WHERE $compare";
                $return["status"] = $this->execute($sql);
                $this->disconnect();
            }
        }
        
        return $return;
    }

    function _get($query = null, $call = null) {
        $return = array();
        if ($query != "") {
            $this->connect();
            $response = $this->execute($query);
            $this->disconnect();
            if ($this->num_rows($response) > 0) {
                while ($row = $this->to_array($response)) {

                    if ($call != null) {
                        $call($row, $return);
                    } else {
                        $return[] = $row;
                    }
                }
            }
        }

        return $return;
    }

}
