<?php

/*
  Connetion class to database
 */

class Database {

    private $connection;
    private $responsed = null;
    public $_insert = false;
    public $_delete = false;
    public $_update = false;
    public $_requireLastId = false;
    public $is_multi = false;

    public function __construct() {
        
    }

    public function connect($sql = null, $return_array = false) {
        if (!isset($this->connection)) {
            $conn = new mysqli("localhost", "root", "", "purchase_orders");
            $conn->set_charset('UTF-8');
            $this->connection = $conn;
            if ($sql != null)
                $this->sql = $sql;
            return $this->execute($return_array);
        }
    }

    public function execute($return_array = false) {
        if (!empty($this->sql)) {
            $this->sql = $this->each($this->sql);
            if ($this->sql != "") {
                $this->responsed = $this->connection->query($this->sql);
//                $this->free();
            }
            if ($this->sql != "" && $this->is_multi) {
                $this->responsed = $this->multi($this->sql);
//                $this->free();
            }
            file_put_contents("query.txt", $this->sql . "\n", FILE_APPEND);
            if (!$this->responsed) {

                if ($this->connection->errno == 1062) {
                    return "duplicated";
                }
                file_put_contents("err.txt", $this->connection->error . "\n");
                return $this->connection->error;
            }

            $return = ($this->num_rows($this->responsed) > 0) ? $this->whiled() : (($this->_requireLastId == false) ? 0 : $this->connection->insert_id);

            if ($return == 0 && !$return_array) {
                $return = array("data" => 0);
            }

            $this->disconnect();
            return $return;
        }
        return "ERROR SQL EMPTY";
    }

    function num_rows($result) {
        if ($this->_insert == false && $this->_delete == false && $this->_update == false) {
            return $result->num_rows;
        } else {
            return 0;
        }
    }

    function setConfig($param = array()) {
        if (!empty($param) && is_array($param)) {
            foreach ($param as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    function last_id() {
        return $this->connection->insert_id;
    }

    function to_array($result) {
        return $result->fetch_assoc();
    }

    public function affected_rows() {
        $countRows = $this->connection->affected_rows;
        return $countRows;
    }

    public function free() {
//        $this->connection->next_result();
    }

    public function multi($query) {
        $this->connection->multi_query($query);
    }

    public function next_result() {
        $this->connection->next_result();
    }

    public function store_result() {
        $this->connection->store_result();
    }

    public function disconnect() {
        $this->connection->close();
    }

    private function whiled() {
        $json = array();
        while ($tmp = $this->to_array($this->responsed)) {
            $json[] = $tmp;
            $this->free();
        }

        $keys["keys"] = array_keys($json);

        foreach ($keys["keys"] as $key => $value) {
            $keys["KEY"] = array_keys($json[$value]);
            break;
        }
        unset($keys["keys"]);

        $json = array("data" => $json);
        $json = array_merge($json, $keys);
        return $json;
    }

    public function each($array) {
        if ($this->is($array, false, 'array')) {
            $tmp = "";
            $sql = "";
            $where = "";
            foreach ($array as $key => $value) {
                if ($this->is($value, false, 'array')) {
                    foreach ($value as $k => $val) {
                        if ($key == "INNER JOIN") {
                            $sql .= "$key  $k ON  $val  ";
                        } elseif ($key == "LEFT JOIN") {
                            $sql .= "$key  $k ON  $val  ";
                        } elseif ($key == "WHERE") {
                            $where .= " $key  $k =  $val ";
                        } else {
                            $sql .= "$key = $val ";
                        }
                    }
                } elseif ($key == "ORDER BY" || $key == "GROUP BY") {
                    $tmp .= " $key  $value  ";
                } elseif ($key == "SHOW") {
                    $sql .= " $key  $value  ";
                } elseif ($key == "LIMIT") {
                    $sql .= " $key  $value  ";
                } elseif ($key == "INSERT INTO") {
                    $this->_insert = true;
                    $sql .= " $key  $value  ";
                } elseif ($key == "()") {
                    $sql .= " ( $value ) ";
                } elseif ($key == "VALUES") {
                    $sql .= " $key ( $value ) ";
                } elseif ($key == "DELETE FROM") {
                    $this->_delete = true;
                    $sql .= "$key  $value ";
                } elseif ($key == "SET") {
                    $sql .= "$key  $value ";
                    $this->_update = true;
                } else {
                    $sql .= " $key $value ";
                }
            }
            return $sql .= $where . $tmp;
        } elseif ($this->is($array, false, 'string')) {
            if (isset($this->is_selectString) && !$this->is_selectString)
                $this->is_multi = TRUE;
            return $array;
        }
    }

    public function is($is, $mult = false, $gettype = "") {
        $return = false;
        if ($mult) {
            $is = explode(',', $is);
            foreach ($is as $key => $value):
                $return = (isset($value) && !empty($value)) ? true : false;
            endforeach;
        }elseif ($gettype != "") {
            if (isset($is) && !empty($is) && gettype($is) == $gettype)
                $return = true;
        }else {
            if (isset($is) && !empty($is))
                $return = true;
        }
        return $return;
    }

}
