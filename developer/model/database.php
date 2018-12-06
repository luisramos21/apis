<?php

/*
  CLASE PARA LA CONEXION Y LA GESTION DE LA BASE DE DATOS Y LA PAGINA WEB
 */

class Database {

    private $conexion;
    private $conn;

    public function connect() {
        if (!isset($this->conexion)) {
            $conn = new mysqli("localhost", "disetronica", "d1s3tr0n", "developer");
            $this->conexion = $conn;
        }
    }

    public function execute($sql) {
        $resultado = $this->conexion->query($sql);
        if (!$resultado) {
            return $this->conexion->error;
            exit;
        }
        return $resultado;
    }

    function num_rows($result) {
        if(isset($result->num_rows)){
            return $result->num_rows;
        }
        
    }

    function to_array($result) {
        //if(!is_resource($result)) return false;
        return $result->fetch_assoc();
    }

    public function disconnect() {
        $this->conexion->close();
        $this->conexion = null;
    }

    public function affected_rows() {
        return $this->conexion->affected_rows > 0;
    }

    public function get_insert_id() {
        return $this->conexion->insert_id;
    }

    public function get_error() {
        return $this->conexion->error;
    }

}
?>