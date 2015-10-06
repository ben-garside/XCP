<?php 

date_default_timezone_set('Europe/London');
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);
require("php/init.php");

$db = DB::getInstance();
$user = new User();
if(!$user->isLoggedIn()){
	Session::flash('login-error','You must be logged in to view this page!');
    Redirect::to('login.php?nexturl=index.php');
}

$sql = "SELECT [id],[username] FROM [USERS]";
$user_data = $db->query($sql);                   
$user_results = $user_data->results();
$userList = array();


foreach ($user_results as $value) {
	$userList[$value->id] = $value->username;
}

$header = array('xcp_id',
                  'material_id',
                  'materialDescription',
                  'projectType',
                  'projectForecastPubl',
                  'feed_name',
                  'stream_id'
                  );

$sql = "SELECT act + ':' + status STAGE, act, status, name, description FROM ACT_STATUS_2 order by act + ':' + status asc";
$stages = $db->query($sql);                   
$stages_results = $stages->results();
$stagesList = array();

foreach ($stages_results as $value) {
  $stagesList[$value->STAGE] = array('act' => $value->act,
                                    'status' => $value->status,
                                    'name' => $value->name,
                                    'description' => $value->description
                                    );
  array_push($header, $value->STAGE . " - iterations");
  array_push($header, $value->STAGE . " - firstDate");
  array_push($header, $value->STAGE . " - firstAllocatedDate");
  array_push($header, $value->STAGE . " - firstAllocatedUser");
  array_push($header, $value->STAGE . " - lastDate");
  array_push($header, $value->STAGE . " - lastAllocatedDate");
  array_push($header, $value->STAGE . " - lastAllocatedUser");
}

$sql = "SELECT * FROM [dbo].maindata WHERE projectStatus = 'PUBL'";
$data = $db->query($sql);                   
$xcpItems = $data->results();

$filename = date("Y-m-d_His") . "_" . $user->data()->username; 
$line = array();
foreach ($xcpItems as $key => $value) {
  $auditDetails = array();
  $test = array();

  array_push($test, $value->xcp_id);
  array_push($test, $value->material_id);
  array_push($test, $value->materialDescription);
  array_push($test, $value->projectType);
  array_push($test, $value->projectForecastPubl);
  array_push($test, $value->feed_name);
  array_push($test, $value->stream_id);

  $sql = "SELECT * FROM [dbo].[auditItems] where xcpid = '".$value->xcp_id."'";
  $auditData = $db->query($sql);
  $auditData = $auditData->results();

  foreach ($auditData as $key => $auditValue) {
    $auditDetails[$auditValue->stage] = array('iterations' => $auditValue->iterations,
                                              'firstDate' => $auditValue->firstDate,
                                              'firstAllocatedDate' => $auditValue->firstAllocatedDate,
                                              'firstAllocatedUser' => $userList[$auditValue->firstAllocatedUser],
                                              'lastDate' => $auditValue->lastDate,
                                              'lastAllocatedDate' => $auditValue->lastAllocatedDate,
                                              'lastAllocatedUser' => $userList[$auditValue->lastAllocatedUser]  
                                              );
  }

  foreach ($stagesList as $stage => $details) {
    $i = 0;
    if($auditDetails[$stage]){
      foreach ($auditDetails[$stage] as $key => $auditDetail) {
        array_push($test, $auditDetail);
      }      
    } else {
      while ($i <= 6) {
        array_push($test, "");
        $i++;
      }
    }

  }

  $line[] = $test;
}


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator($user->data()->username)
               ->setLastModifiedBy($user->data()->username)
               ->setTitle($filename)
               ->setSubject("")
               ->setDescription("");


// Add some data
$objPHPExcel->getActiveSheet()->fromArray($header);

$objPHPExcel->getActiveSheet()->fromArray($line,61,'A2');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Data');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename .'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
