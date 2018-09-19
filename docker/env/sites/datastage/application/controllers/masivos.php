<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."/third_party/PHPExcel.php"; 

class Masivos extends CI_Controller {

	public function index()
	{
		$this->load->view('masivos/main');
	}

	public function descarga($filename)
	{
		//libs
		$this->load->library('remoteclient');
        $this->load->library('excel');

		$url = "https://www.masivos.com/actualizables/".$filename;

		echo $url;

		$destfile = "./upload/" . $filename;

		$this->remoteclient->getfile($url,$destfile);

	}

	public function procesar($filename)
	{

		$inputFileName = './upload/' . $filename;

		//  Read your Excel workbook
		try {
		    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
		    $objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}

		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(1); 
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();

		$pricelist = array();
		$qprices = 0;

		//  Loop through each row of the worksheet in turn
		for ($row = 3; $row <= $highestRow; $row++){ 
		    //  Read a row of data into an array
		    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
		                                    NULL,
		                                    TRUE,
		                                    FALSE);

		    for($qcols = 0 ; $qcols < 3; $qcols++){
			    $offs = 6 * $qcols;

			    $art_id 	= $rowData[0][$offs+0];
			    $desc 		= $rowData[0][$offs+1];
			    $uxb 		= $rowData[0][$offs+2];
			    $prc_raw 	= $rowData[0][$offs+3];

		    	$r = array(	'art_id' 	=>  ($art_id 	== null ? 0 : $art_id),
				    		'desc' 		=>  ($desc 		== null ? ' ': strtolower($desc)),
				    		'uxb' 		=>  ($uxb 		== null ? 1: $uxb),
				    		'prc_raw' 	=>  ($prc_raw 	== null ? 0: $prc_raw)
		    				 );
		    	$pricelist[$qprices++] = $r;
			}
		
		}

		$json_pricelist = json_encode($pricelist);

		header("Content-type: application/json; charset=utf-8");
		
		echo $json_pricelist;
	}

}
