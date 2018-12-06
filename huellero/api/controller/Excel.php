<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHPEXCEL
 *
 * @author ADMIM
 */
require_once 'Lib/PHPExcel.php';

class EXCEL {

    public $Creator = "Luis Ramos";
    public $LastModifiedBy = "Luis Ramos";
    public $Title = "Office 2007 XLSX Test Document";
    public $Subject = "Office 2007 XLSX Test Document";
    public $Description = "Office 2007 XLSX Test Document";
    public $TitleSheet = 'hoja1';
    public $sheet = null;
    public $sheetIndex = 0;
    public $ext = '.xlsx';
    public $CellValue = array();
    public $FormatNumberCell = array();
    public $Filename = null;
    public $File = null;
    private $targeFile = "";
    public $sheetData = null;
    public $Download = false;
    private $Excel = null;
    private $Data = null;
    public $Nocenter = array();
    private $versionExcel = 'Excel2007';
    private $First = null;
    public $styleArray = array(
        'font' => array(
            'bold' => false,
            'color' => array('rgb' => '000000'),
            'size' => 12,
            'name' => 'Arial'
    ));
    public $_DATA = '{"data":[{"id":"2300208","No_requisicion":"2300208","Usuario":"Yaniris  Noriega","Fecha_Aprobacion":"2017-05-17 11:48:47","Estado":"0"},{"id":"2300207","No_requisicion":"2300207","Usuario":"Yaniris  Noriega","Fecha_Aprobacion":"2017-05-17 09:39:23","Estado":"0"},{"id":"2300206","No_requisicion":"2300206","Usuario":"Yaniris  Noriega","Fecha_Aprobacion":"2017-05-16 14:01:35","Estado":"0"},{"id":"2700050","No_requisicion":"2700050","Usuario":"Jassir  Vega","Fecha_Aprobacion":"2017-05-13 11:51:15","Estado":"0"},{"id":"0900341","No_requisicion":"0900341","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-12 00:00:01","Estado":"0"},{"id":"0900340","No_requisicion":"0900340","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-11 14:11:17","Estado":"0"},{"id":"7200115","No_requisicion":"7200115","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-05-11 13:36:27","Estado":"0"},{"id":"0900339","No_requisicion":"0900339","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-11 11:42:17","Estado":"0"},{"id":"0900338","No_requisicion":"0900338","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-11 11:17:56","Estado":"0"},{"id":"0900337","No_requisicion":"0900337","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-11 00:00:01","Estado":"0"},{"id":"0900336","No_requisicion":"0900336","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-10 16:10:31","Estado":"0"},{"id":"0900335","No_requisicion":"0900335","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-10 15:43:10","Estado":"0"},{"id":"0900334","No_requisicion":"0900334","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-05-09 12:26:32","Estado":"0"},{"id":"7200114","No_requisicion":"7200114","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-05-09 09:45:29","Estado":"0"},{"id":"7200113","No_requisicion":"7200113","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-05-08 15:07:52","Estado":"0"},{"id":"7200111","No_requisicion":"7200111","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-05-03 10:27:45","Estado":"0"},{"id":"7200108","No_requisicion":"7200108","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-04-28 00:00:01","Estado":"0"},{"id":"0900332","No_requisicion":"0900332","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-04-26 13:13:11","Estado":"0"},{"id":"0700166","No_requisicion":"0700166","Usuario":"Carlos  Sanchez","Fecha_Aprobacion":"2017-04-26 00:00:01","Estado":"0"},{"id":"0900331","No_requisicion":"0900331","Usuario":"Lloyd  Jay","Fecha_Aprobacion":"2017-04-25 09:33:09","Estado":"0"},{"id":"7200106","No_requisicion":"7200106","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-04-20 15:30:01","Estado":"0"},{"id":"7200105","No_requisicion":"7200105","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-04-19 15:51:59","Estado":"0"},{"id":"7200104","No_requisicion":"7200104","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-04-19 15:11:26","Estado":"0"},{"id":"7200102","No_requisicion":"7200102","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-04-19 14:51:04","Estado":"0"},{"id":"7200087","No_requisicion":"7200087","Usuario":"Nathaly  Galvis","Fecha_Aprobacion":"2017-03-31 00:00:01","Estado":"0"},{"id":"0700143","No_requisicion":"0700143","Usuario":"Carlos  Sanchez","Fecha_Aprobacion":"2017-01-26 14:32:41","Estado":"0"},{"id":"0700135","No_requisicion":"0700135","Usuario":"Carlos  Sanchez","Fecha_Aprobacion":"2017-01-20 14:06:07","Estado":"0"},{"id":"0301004","No_requisicion":"0301004","Usuario":"Alfredo  Martinez","Fecha_Aprobacion":"2016-12-30 13:53:03","Estado":"0"},{"id":"0300977","No_requisicion":"0300977","Usuario":"Alfredo  Martinez","Fecha_Aprobacion":"2016-10-12 15:09:18","Estado":"0"},{"id":"0700125","No_requisicion":"0700125","Usuario":"Carlos  Sanchez","Fecha_Aprobacion":"2016-09-08 15:07:25","Estado":"0"}],"KEY":["id","No_requisicion","Usuario","Fecha_Aprobacion","Estado"]}';

    public function __construct($File = null, $nameFileGlobal = null, $config = array()) {
        $this->__Properties();
        $this->First = $this->Excel->getSheet(0);
        if ($File == null) {
            if ($config != "") {
                foreach ($config as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->{$key} = $value;
                    }
                }
            }
        } else {
            $this->LoadFile($File, $nameFileGlobal);
        }
    }

    function Execute() {
        // Create new PHPExcel object

        if ($this->sheet != null) {
            $this->First = $this->Excel->getSheet(0)->setTitle($this->sheet);
        }
        if (isset($this->Data) && $this->Data != null) {
            $this->__Values($this->First);
        }
        $this->Save();
    }

    function RenameSheet($title) {
        $this->sheet = $title;
    }

    private function LoadFile($File = null, $nameFileGlobal = null) {
        if (isset($_FILES) && !empty($_FILES) && $nameFileGlobal != null && $nameFileGlobal != "") {
            $this->targeFile .= basename($_FILES[$nameFileGlobal]["name"]);
            move_uploaded_file($_FILES[$nameFileGlobal]["tmp_name"], $this->targeFile);
        } elseif ($File != NULL && $File != "" && (strpos($File, ".xls")) || strpos($File, "/") || strpos($File, ".xslx")) {
            $this->targeFile = $File;
        } else {
            echo "FILE No Valido ($File)";
            exit;
        }

        if ($this->targeFile != null && file_exists($this->targeFile)) {
            $excel = PHPExcel_IOFactory::load($this->targeFile);
            $this->sheetData = $excel->getActiveSheet()->toArray(null, true, true, true);
            unlink($this->targeFile);
        } else {
            echo "FILE ($this->targeFile) No existe.";
            exit;
        }
    }

    function CellValues($array = array(), $keyscols = array()) {
        //Temp function 

        if (!empty($keyscols)) {

            foreach ($keyscols as $key => $value) {
                $i = 2;
                $this->CellValue[$key . 1] = $value;
                foreach ($array as $k => $val) {
                    if (array_key_exists($value, $val)) {
                        $this->CellValue[$key . $i] = $val[$value];
                        $i++;
                    }
                }
                unset($keyscols[$key]);
            }
        } else {
            echo "mal" . "<br>";
        }
        return $this->CellValue;
    }

    function ValuesStyles($sheet, $options = array()) {

        if (empty($options))
            return;

        if (!empty($options) && isset($options['columns']) && isset($options['data']) && isset($options['styles'])) {

            $this->setHeaderReporte($sheet, $options);

            $stylesColums = array(
                'font' => array(
                    'color' => array('rgb' => '000000'),
                    'size' => 16,
                    "bold" => true
            ));
            $options['type'] = array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'FFFF00'
            ));
            $ColumnRowNumber = 15;
            $columnsCreates = array();
            $i = 16;
			
            foreach ($options['data'] as $keyData => $data) {
               
                foreach ($options['columns'] as $letter => $columnValue) {
					
                    if (!in_array($columnValue,$columnsCreates)) {
                        $sheet->getColumnDimension($letter)
                                ->setAutoSize(true);
                        $sheet->SetCellValue($letter . $ColumnRowNumber, $columnValue)
                                ->getStyle($letter . $ColumnRowNumber)->getNumberFormat()
                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $sheet->getStyle($letter . $ColumnRowNumber)->applyFromArray($stylesColums);
                        $columnsCreates[] = $columnValue;
                    }
                    if (!in_array($letter, $this->Nocenter)) {
                        $sheet->getStyle($letter . $ColumnRowNumber)
                                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    }
                    //set data in celda
                    if (array_key_exists($columnValue, $data)) {

                        $Celda = $letter . $i;
                        if (!in_array($letter, $this->Nocenter)) {
                            $sheet->getStyle($Celda)
                                    ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        }

                        $sheet->SetCellValue($Celda, $data[$columnValue])
                                ->getStyle($Celda)->getNumberFormat()
                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        if (isset($options['conditions'])) {
                            foreach ($options['conditions'] as $keyConditions => $valueCondtions) {

                                if (isset($data[$keyConditions]) && $valueCondtions == $data[$keyConditions]) {
                                    $this->Excel->getActiveSheet()->getStyle($Celda)->applyFromArray($options['styles']);
                                }
                            }
                        }
                    }
                }
                $i++;
            }
        }
    }
    
    function setSheet($pos, $name, $values = null, $options = array()) {
        if ($values != null) {
            $this->CellValue = $values;
        }
        if ($pos != "" && $name != "") {
            $sheet = $this->Excel->createSheet($pos)->setTitle($name);
            if (empty($options)) {
                $this->__Values($sheet, $options);
            } else if (!empty($options) && isset($options['styles'])) {
                $this->ValuesStyles($sheet, $options);
            }
            return $sheet;
        } elseif ($values != null && (int) $pos == 0) {
            $this->__Values($this->First);
            return $this->First;
        } else if ($values == null && (int) $pos == 0 && !empty($options) && isset($options['styles'])) {
            $this->ValuesStyles($this->First, $options);
            return $this->First;
        }
        $messaje = "Fail Create sheet($name,$pos)";
        file_put_contents("shellsEroors.log", $messaje . "\n", FILE_APPEND);

        echo $messaje;
        return $messaje;
    }

    function __Properties() {
        $this->Excel = new PHPExcel();
        $this->Excel->getProperties()->setCreator($this->Creator)
                ->setLastModifiedBy($this->LastModifiedBy)
                ->setTitle($this->Title)
                ->setSubject($this->Subject)
                ->setDescription($this->Description);
    }

    function __Values($sheet = null, $styles = array()) {
        //file_put_contents("styles.txt", "\n");
        $last_Letter = "";
        $current_Letter = "A";
        $Firts_Key = "A1";
        $LastKey = "";
        if ($this->CellValue != "") {
            foreach ($this->CellValue as $key => $value) {
                if (is_string($key) && !is_integer($key)) {
                    $letter = substr($key, 0, 1); //A-Z
                    $sheet->getColumnDimension($letter)
                            ->setAutoSize(true);
                    if (!in_array($letter, $this->Nocenter)) {
                        $sheet->getStyle($key)
                                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    }
                    if (in_array($letter, $this->FormatNumberCell)) {
                        $sheet->SetCellValue($key, $value)
                                ->getStyle($key)->getNumberFormat()
                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                    } else {
                        $sheet->SetCellValue($key, $value)
                                ->getStyle($key)->getNumberFormat()
                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    }
                } else {
                    echo "KEY No Valido ($key)";
                    exit;
                }
            }
            $this->CellValue = array();
        } else {
            echo 'Cell values Empty';
            exit;
        }
    }

    function lock($sheet = null, $lock = 'A1:A19', $unlock = array()) {
        if ($sheet != "" && $sheet != null && is_string($sheet)) {
//            //PROTECT THE CELL RANGE
            if ($lock != null) {
                $sheet->protectCells($lock, 'PHP');

                $sheet->getProtection()->setSheet(true);
                $sheet->getStyle('B1:Z500')->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
            } else {
                echo 'Range lock null';
                exit;
            }
        } elseif ($sheet != "" && $sheet != null && ( is_object($sheet) || is_array($sheet))) {
            if (is_object($sheet)) {
                $sheet = array($sheet);
            }
            foreach ($sheet as $key => $value) {
                $value->protectCells($lock, 'PHP');
                $value->getProtection()->setSheet(true);
                $this->unlock($sheet, $unlock);
            }
        } else {
            echo "sheet($shell) no existe";
            exit;
        }
    }

    function comments($sheet = array(), $commets = array()) {
        if ($sheet != "" && $commets != "") {
            foreach ($commets as $key => $value) {
                if ($value != "") {
                    $sheet
                            ->getComment($key)
                            ->setAuthor('Luis Ramos');
                    $objCommentRichText = $sheet
                                    ->getComment($key)
                                    ->getText()->createTextRun('ERROR : ');
                    $objCommentRichText->getFont()->setBold(true);

                    $sheet
                            ->getComment($key)
                            ->getText()->createTextRun($value);
                } else {
                    
                }
            }
        } else {
            echo 'errorororo';
            exit;
        }
    }

    function unlock($sheet = array(), $lock = null) {
        if ($lock != null && $sheet != "") {
            foreach ($sheet as $key => $value) {
                if (is_array($lock)) {
                    foreach ($lock as $k => $val) {
                        $value->getStyle($val)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                    }
                } else {
                    $value->getStyle($lock)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
                }
            }
        }
    }

    private function Save() {
        if ($this->Filename == "" && $this->Filename == null) {
            $this->Filename = str_replace('.php', $this->ext, __FILE__);
        } elseif ($this->Filename != "") {
            $this->Filename = $this->Filename . $this->ext;
        }
        if ($this->ext == '.xlsx') {
            //continue
        } elseif ($this->ext == '.xls') {
            $this->versionExcel = 'Excel5';
        } else {
            echo "Extension No valida($this->ext)";
            exit;
        }
        $objWriter = PHPExcel_IOFactory::createWriter($this->Excel, $this->versionExcel);
        if ($this->Download && $this->ext == ".xls") {
            // We'll be outputting an excel file
            header('Content-type: application/vnd.ms-excel');
            // It will be called file.xls
            header('Content-Disposition: attachment; filename="' . $this->Filename . '"');
            $this->Filename = "php://output";
        } elseif ($this->Download && $this->ext == ".xlsx") {
            // We'll be outputting an excel file
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Disposition: attachment; filename="' . $this->Filename . '"');
            $this->Filename = 'php://output';
        }
        if (strpos($this->Filename, "/")) {
            $tmp = explode("/", $this->Filename);
            $this->File = end($tmp);
        }
//        ob_clean();
        $objWriter->save($this->Filename);
    }

    function setHeaderReporte($sheet, $options = array()) {

        $rangeReporte = '---';
        $PeriodoReporte = 'Test';
        $HoraReporte = date('i:s');

        if (isset($options['desde']) && isset($options['hasta'])) {
            $rangeReporte = " desde {$options['desde']} hasta  {$options['hasta']}";
        }

        if (isset($options['Periodo'])) {
            $PeriodoReporte = $options['Periodo'];
        }

        if (isset($options['hour'])) {
            $HoraReporte = $options['hour'];
        }


        #Merge Cell Title Reporte
        $sheet->mergeCells('B3:G8');

        #Merge Cell Title Reporte
        $sheet->mergeCells('B9:G9');

        #RowHeight in row 21 of Title reporte
        $sheet->getRowDimension('9')->setRowHeight(21);

        #title Reporte
        $sheet->SetCellValue("B9", "Reporte Huellero automatico")
                ->getStyle("B9")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("B9")->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '000000'),
                'size' => 16,
                "bold" => true
        )));
        ## alignment  Center Title
        $sheet->getStyle("B9")
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        # Merge Space before Title Reporte
        $sheet->mergeCells('B10:G10');
        #RowHeight in row 10 of before Title reporte
        $sheet->getRowDimension('10')->setRowHeight(15);

        #title Fecha reporte
        $sheet->SetCellValue("B11", "Fecha Reporte : ")
                ->getStyle("B11")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("B11")->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '000000'),
                'size' => 12,
                "bold" => true
        )));

        #Value Fecha reporte
        $sheet->SetCellValue("C11", date('l j F Y h:i:s'))
                ->getStyle("C11")->getNumberFormat()
                ->setFormatCode('[$-240A]dddd d" de "mmmm" de "yyyy;@');
        $sheet->mergeCells('C11:E11');

        ## alignment Center Periodo Reporte
        $sheet->getStyle("C11")
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        #title Reporte desde
        $sheet->SetCellValue("B12", "Reporte Generado : ")
                ->getStyle("B12")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("B12")->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '000000'),
                'size' => 12,
                "bold" => true
        )));

        #Value Fecha reporte
        $sheet->SetCellValue("C12", $rangeReporte)
                ->getStyle("C12")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->mergeCells('C12:E12');

        ## alignment Center Periodo Reporte
        $sheet->getStyle("C12")
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        #Periodo Reporte
        $sheet->SetCellValue("F11", "Periodo Reporte   : ")
                ->getStyle("F11")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("F11")->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '000000'),
                'size' => 12,
                "bold" => true
        )));
        #Value Periodo Reporte
        $sheet->SetCellValue("G11", $PeriodoReporte)
                ->getStyle("G11")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        ## alignment Center Periodo Reporte
        $sheet->getStyle("G11")
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        #Periodo Reporte
        $sheet->SetCellValue("F12", "Hora Programada :")
                ->getStyle("F12")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("F12")->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '000000'),
                'size' => 12,
                "bold" => true
        )));
        #Value Periodo Reporte
        $sheet->SetCellValue("G12", $HoraReporte)
                ->getStyle("G12")->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        ## alignment Center Periodo Reporte
        $sheet->getStyle("G12")
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $dir = realpath(PATH);
        file_put_contents('server.log', $dir);
        $this->setImage($sheet, array(
            'name' => 'Imagen DST',
            'image' => "$dir/images/DST.png",
            'cord' => 'A3',
            'w' => 320,
            'h' => 108,
            'X' => 100,
            'Y' => 0
        ));
    }

    function SetImage($sheet, $options = array()) {

        list($ancho, $alto, $tipo, $atributos) = getimagesize($options['image']);
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName($options['name'] ? $options['name'] : 'name');
        $objDrawing->setDescription($options['name'] ? $options['name'] : 'name');
        $objDrawing->setPath($options['image']);
        $objDrawing->setCoordinates($options['cord']);
        //setOffsetX works properly
        $objDrawing->setOffsetX($options['X'] ? $options['X'] : 5);
        $objDrawing->setOffsetY($options['Y'] ? $options['Y'] : 5);
        //set width, height
        $objDrawing->setWidth($options['w'] ? $options['w'] : $ancho);
        $objDrawing->setHeight($options['h'] ? $options['h'] : $alto);

        $objDrawing->setWorksheet($sheet);
    }

}

//
//$excel = new EXCEL(null, null, array("ext" => ".xlsx", "Download" => true, "Filename" => "miFile", 'sheet' => 'Mis Datos'));
////$excel = new EXCEL('PHPEXCEL.xlsx');
////function cooordenadas data TMP
//$excel->setSheet(json_decode($excel->_DATA, TRUE));
//$datos = $excel->CellValues(json_decode($excel->_DATA, TRUE));
//$sheet1 = $excel->setSheet(0, "", $datos);
////$sheet2 = $excel->setSheet(1, 'Datos',$datos);
//$excel->lock(array($sheet1));
//$excel->execute();
//$excel = new EXCEL(null, null, array("Filename" => "../upload/imports/miFile", 'sheet' => 'Mis Datos', 'Nocenter' => array('C', 'G')));
/* $datos = $excel->CellValues($data, array("A" => 'id_prod', 'B' => 'No_Requisicion', 'C' => 'usuario',
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
  , "file" => $excel->File)); */
//foreach ($excel->sheetData as $key => $values) {
//    echo "{$values["A"]}<br>";
//}
?>