<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."/third_party/PHPExcel.php"; 

class Masivos extends CI_Controller {

	//
	$url_prefix = "https://www.masivos.com/actualizables/";
	$dest_prefix = "./upload/";
	$dump_data = array();


	public function index()
	{
		$this->load->view('masivos/main');
	}


	public function run()
	{
		$dmy =  date('dmy',  strtotime("last Saturday") ); //Ej: 150918
		$filename = $url_prefix.'/precios'.$dmy.'.xls';
		

		if($cont == 1){	//Step1
			$cont = $this->descarga($filename);
		}

		if($cont == 1){	//Step2
			$cont = $this->procesar($filename);
		}

		if($cont == 1){	//Step3
			$cont = $this->guardar($filename);
		}


	}


	public function descarga($filename)
	{
		//libs
		$this->load->library('remoteclient');
        $this->load->library('excel');

		$url = $url_prefix.$filename;

		echo $url;

		$destfile = $dest_prefix. $filename;

		$this->remoteclient->getfile($url,$destfile);

		return 1;
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

		$dump_data = $pricelist 

		return 1;

		//$json_pricelist = json_encode($pricelist);
		//header("Content-type: application/json; charset=utf-8");
		//echo $json_pricelist;
	}


	public function guardar($filename)
	{
		//
		$this->load->database();


		//Get JobId
		$id_djob = $this->newJob();



		//Insert row
		foreach($dump_data as $row) {
			$data = array(
			        'id_djob' 		=> $id_djob,
			        'id_vendor' 	=> 'My Name',
			        'vendor_art' 	=> $row['art_id'],
			        'vendor_desc' 	=> $row['desc'],
			        'vendor_uxb'	=> $row['uxb'], 
			        'vendor_prc' 	=> $row['prc_raw']
			);

			$this->db->insert('mytable', $data);
		}
		


	}

	public function newJob() {

/*
Field,Type,Null,Key,Default,Extra
"id_djob","int(11)","NO","PRI",,"auto_increment"
"id_dtype","int(11)","NO",,"0",
"url","varchar(2048)","NO",,,
"request","varchar(1024)","YES",,,
"response","varchar(1024)","YES",,,
"ts_begin","timestamp","NO",,"CURRENT_TIMESTAMP",
"ts_end","timestamp","YES",,,
*/
		$this->

		$this->db->
	}


}
