<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FilesController
 *
 * @author admin
 */
require_once PATH . "controller/UsersController.php";

class FilesController extends UsersController {

    private $InvalidsValids = array("zip", "exe", "jpg", "ico", "png", "jpeg", "jar", "rar", "mp3", "mp4",
        "waw", "ogg", "midi", "avi", "mpeg", "mov", "wmv", "rm", "flv", "BMP", "bpm", "gif", "GIF", "tif");
    private $code_segure = 7894;

    public function __construct() {
        parent::__construct();
    }

    public function getFiles($_request) {
        $response = array("status" => "Error", "data" => array());
        if ($this->isSession()) {

            if (empty($_request)) {
                $_request["dir"] = "../../";
                $response["request"] = $_request;
            }
            $response["status"] = 'OK';
            $response["data"] = $this->listFolderFiles($_request);
        } else {
            $response['status'] = "NO_SESSION";
        }
        return $this->_return($response);
    }

    private function listFolderFiles($_request) {
        $dir = "../";
        $path = PATH;

        if (isset($_request['dir'])) {
            $dir = $_request['dir'];
        }

        if (isset($_request['path'])) {
            $path = $_request['path'];
        }

        $stringFiles = "";
        $FilesArray = array();
        $files = scandir($dir);

        natcasesort($files);

        if (count($files) > 2) { /* The 2 accounts for . and .. */

//            $stringFiles .= "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
            // All dirs

            foreach ($files as $file) {

                if (file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file)) {

                    $FilesArray["directories"][] = array(
                        "path" => htmlentities($dir . $file),
                        "filename" => $file
                    );

//                    $stringFiles .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "/\">" . htmlentities($file) . "</a></li>";
                }
            }
            // All files

            foreach ($files as $file) {

                if (file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($path . $dir . $file) && is_file($dir . $file)) {

                    $ext = preg_replace('/^.*\./', '', $file);
                    $FilesArray["Files"][] = array(
                        "path" => htmlentities($dir . $file),
                        "filename" => $file,
                        "extension" => $ext
                    );
//                    $stringFiles .= "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "\">" . htmlentities($file) . "</a></li>";
                }
            }

//            $stringFiles .= "</ul>";
        }
        return $FilesArray;
    }

    function getContentsFile($file) {

        $response = array("status" => "Error", "data" => array());
        if ($this->isSession()) {

            $content = array();

            if (isset($file['file']) && file_exists($file['file'])) {

                $name = explode("/", $file['file']);

                $name = $name[count($name) - 1];
                $contentString = "";
                $extension = $this->get_extension($name);
                if ($extension) {
                    if (!in_array($extension, $this->InvalidsValids)) {
                        $contentString = file_get_contents($file['file']);
                    }
                }
                $content["name"] = $name;
                $content['path'] = realpath($file['file']);
                $content["content"] = $contentString;
            }
            $response["status"] = 'OK';
            $response["data"] = $content;
        } else {
            $response['status'] = "NO_SESSION";
        }
        return $this->_return($response);
    }

    function removeFile($_request) {
        $response = array("status" => "Error");
        if ($this->isSession()) {

            if (isset($_request['path']) && !empty($_request['path']) && file_exists($_request['path'])) {
                if ((int) $_request['code'] === $this->code_segure) {
                    unlink($_request['path']);
                    $response["status"] = 'OK';
                } else {
                    $response["status"] = 'Error_code';
                }
            } else {
                $response["status"] = 'ERROR File';
            }
        } else {
            $response['status'] = "NO_SESSION";
        }
        return $this->_return($response);
    }

    function removeFolder($_request) {
        $response = array("status" => "Error");
        if ($this->isSession()) {

            if (isset($_request['path']) && !empty($_request['path']) && is_dir($_request['path'])) {
                if ((int) $_request['code'] === $this->code_segure) {
                    $response["status"] = $this->removeDirectory($_request['path']) ? 'OK' : "Error delete folder.";
                } else {
                    $response["status"] = 'Error_code';
                }
            } else {
                $response["status"] = 'ERROR File';
            }
        } else {
            $response['status'] = "NO_SESSION";
        }
        return $this->_return($response);
    }

    private function removeDirectory($path) {
        try {
            $files = glob($path . '/*');
            foreach ($files as $file) {
                is_dir($file) ? $this->removeDirectory($file) : unlink($file);
            }
            rmdir($path);
            return true;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return false;
    }

    function saveFile($_request) {
        $response = array("status" => "Error");

        $find = array("&gt;", "&lt;", "&quot;");
        $replace = array('>', "<", '"');

        if ($this->isSession()) {
            if (isset($_request['file']) && file_exists($_request['file'])) {
                //save old file 
                $_oldContent = file_get_contents($_request['file']);
                $old_file = $_request['file'] . ".old";
                $_request['content'] = str_replace($find, $replace, $_request['content']);

                if (strlen($_oldContent) != strlen($_request['content'])) {
                    file_put_contents($old_file, $_oldContent);
                    $file = str_replace('/', '\\', $old_file);
                    exec('attrib +H ' . escapeshellarg($file), $res);
                    file_put_contents("logger.txt", print_r($res, true));
                } else if (file_exists($old_file)) {
                    unlink($old_file);
                }
                //save new file
                file_put_contents($_request["file"], $_request["content"]);
                $response["status"] = 'OK';
            } else {
                $response["status"] = 'ERROR FILE';
            }
        } else {
            $response['status'] = "NO_SESSION";
        }
        return $this->_return($response);
    }

    function get_extension($file_name) {
        try {
            if (!preg_match("/^([a-zA-Z0-9àèìòùáéíóúäëïöüÀÈÌÒÙÁÉÍÓÚÄËÏÖÜ\s\._-]+)$/i", $file_name))
                return false;
            else {
                $file_name = explode(".", $file_name);
                if (!count($file_name) > 1) {

                    return false;
                } else {
                    $extension = (String) strtolower(end($file_name));

                    return $extension;
                }
            }
        } catch (Excetpion $e) {
            return false;
        }
    }

    function renameFile($_request) {
        $response = array("status" => "Error");
        if ($this->isSession()) {

            if (isset($_request['new_path']) && !empty($_request['new_path']) && isset($_request['last_path']) && !empty($_request['last_path']) && file_exists($_request['last_path'])) {
                if ((int) $_request['code'] === $this->code_segure) {
                    $response["status"] = rename($_request['last_path'], $_request['new_path']) ? 'OK' : "ERROR Rename file or folder";
                } else {
                    $response["status"] = 'Error_code';
                }
            } else {
                $response["status"] = 'ERROR File';
            }
        } else {
            $response['status'] = "NO_SESSION";
        }
        return $this->_return($response);
    }

}
