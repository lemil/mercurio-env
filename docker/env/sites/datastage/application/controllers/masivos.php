<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."/third_party/PHPExcel.php"; 

class Masivos extends CI_Controller {

	//
	private $url_prefix = 'https://www.masivos.com/actualizables/';
	private $dest_prefix = './upload/';
	private $dump_data = array();
	private $filename_template = 'precios%s.xls';
	private $filename = null;

	public function __construct(){
		parent::__construct();

		$dmy =  date('dmy',  strtotime("last Saturday") ); //Ej: 150918
		$this->filename = sprintf($this->filename_template,$dmy);
	}


	public function getRemoteUrl() {
		return $this->url_prefix.$this->filename;
	}

	public function getLocalFilename(){
		return $this->dest_prefix.$this->filename;
	}

	//Pages
	public function index() {
		$data = array();
		$data['filename'] = $this->getRemoteUrl();

		$this->load->view('masivos/main', $data);
	}

	//Process
	public function run()
	{
		//libs
		$this->load->library('remoteclient');
        $this->load->library('excel');
        $this->load->database();



		$filename = $this->filename;
		
		echo 'Step1 - Download... </br>'; 
		$cont = 1;
		if($cont == 1){	//Step1
			$cont = $this->descarga($filename);
		}

		echo 'Step2 - Read... </br>'; 
		if($cont == 1){	//Step2
			$cont = $this->procesar($filename);
		}

		echo 'Step3 - Save... </br>'; 
		if($cont == 1){	//Step3
			$cont = $this->guardar($filename);
		}

		echo 'Done!</br>';
	}


	public function descarga($filename)
	{

        $url = $this->getRemoteUrl();
		$destfile = $this->getLocalFilename();

		$this->remoteclient->getfile($url,$destfile);

		return 1;
	}


	public function procesar($filename)
	{ 
		$inputFileName = './upload/' . $filename;
		
		echo $inputFileName .'<br>';


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

		$dump_data = $pricelist;

		var_dump($dump_data);

		return 1;
	}


	public function guardar($filename)
	{
		//Get JobId
		$id_djob = $this->newJob();

		//Insert row
		foreach($this->dump_data as $row) {
			$data = array(
			        'id_djob' 		=> $id_djob,
			        'id_vendor' 	=> 'My Name',
			        'vendor_art' 	=> $row['art_id'],
			        'vendor_desc' 	=> $row['desc'],
			        'vendor_uxb'	=> $row['uxb'], 
			        'vendor_prc' 	=> $row['prc_raw']
			);

			$this->db->insert('dump_data', $data);
		}
		
		return 1;
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
		$data = array(
		        'id_dtype' 		=> 1,
		        'url' 			=> $this->getRemoteUrl()
		);

		$this->db->insert('dump_job', $data);
	}


}
