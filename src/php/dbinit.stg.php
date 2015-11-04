<?php
$GLOBALS['config']['mysql'] = array(
	
		// UAT DATABASE - DEVELOPMENT	
		'host' => '10.103.109.84\cloud,1500',
		'username' => 'WSCRAPE_user',
		'password' => 'Password1',
		'db' => 'UAT-WSCRAPE'

	);

$GLOBALS['config']['sendItems']['global'] = array(
		'subMain' 		=> "Innodata_Batch_Alert_",
		'globalBody'	=> "<p>Dear Innodata,</p><p>This is to inform you that a new batch has been transferred to Innodata\'s FTP site, for review for producing agreed XML outputs. Attached is a transmission sheet detailing each Material (identified by UPI) in the batch.</p><p><strong>Please could you confirm receipt of these files and get back on an estimated turnaround time (TAT) and cost. Following this we will confirm whether work should proceed.</strong></p><p><u>Notes:</u></p><p>Directory path: /TO INNO/</p><ul><li>Materials are batched into zip files according to a single Conversion Pipeline (indicated in the Batch ID and the column Conversion_Pipeline in the transmission sheet)</li><li>Each Material in the zip file needs to be processed through that Conversion Pipeline </li><li>Details in the transmission sheet confirm the file types we have provided for each Material (see columns Source_File_Type) and the required XML output (based on Schema, see column Output_File_Type)</li></ul><p>Many thanks</p>",
		'systemUser'	=> -1,
		'recNote'		=> array("TO" => "ben.garside@bsigroup.com")
	);

$GLOBALS['config']['sendItems']['dev'] = array(

		//set email settings for main send 
		'subMain' => "UAT::PRE_SEND::" . $GLOBALS['config']['sendItems']['global']['globalSubject'],
		'bodMain' => $GLOBALS['config']['sendItems']['global']['globalBody'],
		'recMain' => array(	"CC" => "ben.garside@bsigroup.com",
							"TO" => "content.operations@bsigroup.com"),

		//Email settings for Pipeline 8 send 
		'subPl8' => "UAT::PRE_SEND(PL8)::" . $GLOBALS['config']['sendItems']['global']['globalSubject'],
		'bodPl8' => $GLOBALS['config']['sendItems']['global']['globalBody'],
		'recPl8' => array(	"CC" => "ben.garside@bsigroup.com",
							"TO" => "content.operations@bsigroup.com"),

		// FTP settings
		'ftp_server' 	=> "ftp.hugatramp.com",
		'ftp_user_name' => "xcptest@hugatramp.com",
		'ftp_user_pass' => "Password1",

		// What items to check for and where to move them
		'activityFrom' 	=> '10',
		'statusFrom'	=> '19',
		'activityTo' 	=> '10',	
		'statusTo'		=> '20',

		// What to say in the log...
		'ItemMoveMsg'	=> "Pre-send done, moving to next stage"

	);

$GLOBALS['config']['sendItems']['prd'] = array(

		//set email settings for main send 
		'subMain' => "UAT::" . $GLOBALS['config']['sendItems']['global']['globalSubject'],
		'bodMain' => $GLOBALS['config']['sendItems']['global']['globalBody'],
		'recMain' => array(	"CC" => "ben.garside@bsigroup.com",
							"TO" => "content.operations@bsigroup.com"),

		//Email settings for Pipeline 8 send 
		'subPl8' => "UAT(PL8)::" . $GLOBALS['config']['sendItems']['global']['globalSubject'],
		'bodPl8' => $GLOBALS['config']['sendItems']['global']['globalBody'],
		'recPl8' => array(	"CC" => "ben.garside@bsigroup.com",
							"TO" => "content.operations@bsigroup.com"),

		// FTP settings
		'ftp_server' 	=> "ftp.hugatramp.com",
		'ftp_user_name' => "xcptest1@hugatramp.com",
		'ftp_user_pass' => "Password1",

		// What items to check for and where to move them
		'activityFrom' 	=> '10',
		'statusFrom'	=> '20',
		'activityTo' 	=> '20',	
		'statusTo'		=> '00',

		// What to say in the log...
		'ItemMoveMsg'	=> "Automatic send to INNODATA"

	);
?>