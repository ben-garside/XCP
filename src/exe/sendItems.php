<?php

require("../php/init.php");

echo "\n***********************************\n";
echo "************ + START + ************\n";
echo "***********************************\n";

if (php_sapi_name() == "cli") {
    if(!$argv[1] == 'dev' && !$argv[1] == 'prd'){
    	die('Must send arg prd or dev');
    } else {
    	$runType = $argv[1];
    }
} else {
    die('Must be run from CLI');
}

// SETTINGS

//directories
$dateFile = '../toSend/' . date("Ymd");
$itemDir = '../items';
$date = date("Y-m-d");

//globals
$globalBody 	= "<p>Dear Innodata,</p><p>This is to inform you that a new batch has been transferred to Innodata\'s FTP site, for review for producing agreed XML outputs. Attached is a transmission sheet detailing each Material (identified by UPI) in the batch.</p><p><strong>Please could you confirm receipt of these files and get back on an estimated turnaround time (TAT) and cost. Following this we will confirm whether work should proceed.</strong></p><p><u>Notes:</u></p><p>Directory path: /TO INNO/". $date . "</p><ul><li>Materials are batched into zip files according to a single Conversion Pipeline (indicated in the Batch ID and the column Conversion_Pipeline in the transmission sheet)</li><li>Each Material in the zip file needs to be processed through that Conversion Pipeline </li><li>Details in the transmission sheet confirm the file types we have provided for each Material (see columns Source_File_Type) and the required XML output (based on Schema, see column Output_File_Type)</li></ul><p>Many thanks</p>";
$globalSubject 	= "Innodata_Batch_Alert_" . $date;
$recNote 		= array("TO" => "ben.garside@bsigroup.com");
$systemUser		= -1;
//soft-hard
if ($runType =='dev'){

	//set email settings for main send 
	$subMain = "UAT::PRE_SEND::" . $globalSubject;
	$bodMain = $globalBody;
	$recMain = array(	"CC" => "ben.garside@bsigroup.com",
						"TO" => "content.operations@bsigroup.com");

	//Email settings for Pipeline 8 send 
	$subPl8 = "UAT::PRE_SEND(PL8)::" . $globalSubject;
	$bodPl8 = $globalBody;
	$recPl8 = array(	"TO" => "content.operations@bsigroup.com",
						"CC" => "ben.garside@bsigroup.com");

	// FTP settings
	$ftp_server = "ftp.hugatramp.com"; 
	$ftp_user_name = "xcptest@hugatramp.com"; 
	$ftp_user_pass = "Password1";

	// What items to check for and where to move them
	$activityFrom 	= '10';
	$statusFrom		= '19';

	$activityTo 	= '10';	
	$statusTo		= '20';

	$ItemMoveMsg	= "Pre-send done, moving to next stage";

	echo "\nRunning as: dev\n";

} elseif ($runType == 'prd') {

	//set email settings for main send 
	$subMain = "UAT::" . $globalSubject;
	$bodMain = $globalBody;
	$recMain = array(	"TO" => "content.operations@bsigroup.com",
						"CC" => "ben.garside@bsigroup.com");
	
	//set email settings for Pipeline 8 send 
	$subPl8 = "UAT::(PL8)" . $globalSubject;
	$bodPl8 = $globalBody;
	$recPl8 = array(	"TO" => "content.operations@bsigroup.com",
						"CC" => "ben.garside@bsigroup.com");

	// Set FTP settings
	// $ftp_server = "203.55.173.10"; 
	// $ftp_user_name = "FTP-BSI"; 
	// $ftp_user_pass = "PL4789mn";
	$ftp_server = "ftp.hugatramp.com"; 
	$ftp_user_name = "xcptest1@hugatramp.com"; 
	$ftp_user_pass = "Password1";

	// What items to check for and where to move them
	$activityFrom 	= '10';
	$statusFrom		= '20';	

	$activityTo 	= '20';
	$statusTo		= '00';

	$ItemMoveMsg	= "Automatic send to INNODATA";

	echo "\nRunning as: prd\n";

} else {
	die('runType must be declared as either prd or dev');

}

//naming config
$namingArray = array(	1 => 	array(	"name" => "BSI-01-CONV-PDF-BSISDS-",
										"Conversion_Type" => "CONV", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "N/A", 
										"Output_File_Type" => "BSISDS"
								),
						2 =>	array(	"name" => "BSI-02-CONV-PDF-ISOSTS-", 
										"Conversion_Type" => "CONV", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "N/A", 
										"Output_File_Type" => "ISOSTS"
								),
						3 =>	array(	"name" => "BSI-03-TRANS-", 
										"Conversion_Type" => "TRANS", 
										"Source_File_Type_1" => "N/A", 
										"Source_File_Type_2" => "N/A", 
										"Output_File_Type" => "N/A"
								),
						4 =>	array(	"name" => "BSI-04-CAPT-PDF-ISOSTS-", 
										"Conversion_Type" => "CAPT", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "ISOSTS", 
										"Output_File_Type" => "ISOSTS"
								),
						5 =>	array(	"name" => "BSI-05-EXTR-PDF-ISOSTS-",
										"Conversion_Type" => "EXTR", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "N/A", 
										"Output_File_Type" => "ISOSTS"
								),
						6 =>	array(	"name" => "BSI-06-EXTR-PDF-ISOSTS-", 
										"Conversion_Type" => "EXTR", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "N/A", 
										"Output_File_Type" => "ISOSTS"
								),
						7 =>	array(	"name" => "BSI-07-CONS-PDF-ISOSTS-", 
										"Conversion_Type" => "CONS", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "ISOSTS", 
										"Output_File_Type" => "ISOSTS"
								),
						8 =>	array(	"name" => "BSI-08-TERM_MAP-", 
										"Conversion_Type" => "TERM_MAP", 
										"Source_File_Type_1" => "PDF", 
										"Source_File_Type_2" => "N/A", 
										"Output_File_Type" => "ISOSTS"
								)
					);
// CSV Headings
$headers = array(	1  => "XCP_ID",
					2  => "Innodata_Batch_ID[zip filename]",
					3  => "Batch_Sent_Date", 
					4  => "Batch_Return_Date",
					5  => "Conversion_Pipeline",
					6  => "Conversion_Type",
					7  => "Document_Filename (UPI)",
					8  => "Standard_ID",
					9  => "Source_File_Type_1",
					10  => "Source_File_Type_2",
					11 => "Output_File_Type",
					12 => "Page_count",
					13 => "Document_Title",
					14 => "TAT",
					15 => "Estimated return date"
				);

// END OF SETTINGS

//get items at the activity with stage...
$items = Activity::showAtStage($activityFrom,$statusFrom);

if(count($items) > 0) {

	//Check FTP Stuff
	echo "\n**** CONNECTING TO FTP ****\n";
	echo "***************************\n";
	$conn_id = ftp_connect($ftp_server); 
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
	ftp_pasv($conn_id, true);
	if ((!$conn_id) || (!$login_result)) { 
        echo "!! FTP connection has failed!\n"; 
        echo "!! Attempted to connect to $ftp_server for user $ftp_user_name\n"; 
        die('Could not connect to FTP server.'); 
    }
    echo "** Conection made...\n";

	echo "\n**** START DOING STUFF ****\n";
	echo "***************************\n"; 
	   
	// Create container
	echo "* Creating container: $dateFile\n";
	if(!file_exists ( $dateFile )) {
		if (!mkdir($dateFile, 0777)) {
			die('Failed to create folder...');
		}
	} else {
		die('Already collated for today.');
	}

	// Create manifest file
	echo "* Creating manifest file: " . $dateFile . "/transmissionSheet_" . $date . ".csv\n";
	$manifest = fopen($dateFile . "/transmissionSheet_" . $date . ".csv", "w");
	fwrite($manifest, implode(",", $headers) . "\n");

	echo "\n** Create folders **\n";
	echo "********************\n";
	
	// Look at all items to be sent
	foreach ($items as $item) {

		// Create pipeline folder, if it doesn't exist
		echo "\nITEM => " . $item->xcp_id . " (" . $item->stream_id . ")\n";
		if(!file_exists ( $dateFile . "/" . $namingArray[$item->stream_id]["name"] . $date )){
			if (!mkdir($dateFile . "/" . $namingArray[$item->stream_id]["name"] . $date, 0777)) {
				die('!! Failed to create folder...');
			}
			$pipelinesInUse[] =  $item->stream_id;
			echo "    Creating ZIP container: " . $dateFile . "/" . $namingArray[$item->stream_id]["name"] . $date . "\n";
			$foldersToDelete[] = $dateFile . "/" . $namingArray[$item->stream_id]["name"] . $date;
		} else {
			echo "    ZIP container already exists\n";
		}
		
		// Copy files to pipeline folder
		echo "    Putting: " . $itemDir . "/" . $item->file_location . "\n";
		echo "     - INTO: " . $dateFile . "/" . $namingArray[$item->stream_id]["name"] . $date . "/" . $item->file_location ."\n";
		if (!copy($itemDir . "/" . $item->file_location, $dateFile . "/" . $namingArray[$item->stream_id]["name"] . $date . "/" . $item->file_location)) {
		   die("!! failed to copy " . $itemDir . "/" . $item->file_location . "...");
		}

		// Manifest Stuff
		echo "    Adding data to manifest...\n";
		$details = array(	$item->XCPID,
							$namingArray[$item->stream_id]["name"] . $date,
							$date,
							"",
							$item->stream_id,
							$namingArray[$item->stream_id]["Conversion_Type"],
							$item->material_id,
							$item->materialDescription,
							$namingArray[$item->stream_id]["Source_File_Type_1"],
							$namingArray[$item->stream_id]["Source_File_Type_2"],
							$namingArray[$item->stream_id]["Output_File_Type"],
							$item->pageCount,
							str_replace(",", " ", $item->materialTitle),
							"",
							""
						);
		fwrite($manifest, implode(",", $details) . "\n");

		// Move to next activity
		echo "    Updating item in XCP: " . $item->XCPID . "\n";
		$item = new Activity($item->XCPID);
		$item->moveToActivity($activityTo, $statusTo, $systemUser, false, $ItemMoveMsg);



	} //End for each
	echo "\n*** Closing manifest\n";
	fclose($manifest);
	$filesToSend[] = array("/transmissionSheet_" . $date . ".csv", $dateFile . "/transmissionSheet_" . $date . ".csv");

	//ZIP Content
	echo "\n***** ZIPPING CONTENT *****\n";
	echo "***************************\n";
	$piplineBatches = scandir($dateFile);
	foreach ($piplineBatches as $piplineBatch) {
		$filesToDelete = array();
    	if ($piplineBatch === '.' or $piplineBatch === '..') continue;
	    if (is_dir($dateFile . '/' . $piplineBatch)) {
	    	$piplineItems = scandir($dateFile . '/' . $piplineBatch);
	    	$zip = new ZipArchive();
	    	echo "\n*** CREATING ZIP\n";
	    	echo "     - " .$dateFile . '/' . $piplineBatch . '.zip'. "\n";
	    	if ($zip->open( $dateFile . '/' . $piplineBatch . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE) {
			    exit("!! Cannot open zip archive");
			}
			echo "*** ADD FILES TO ZIP\n";
	    	foreach ( $piplineItems as $piplineItem ) {
    			if ( $piplineItem === '.' or $piplineItem === '..') continue;
	    		if ( !is_dir($dateFile . '/' . $piplineBatch . '/' . $piplineItem)) {
	    			echo '    - ' . $dateFile . '/' . $piplineBatch . '/' . $piplineItem . "\n";
	    			$zip->addFile($dateFile . '/' . $piplineBatch . '/' . $piplineItem, $piplineItem);
	    			$filesToDelete[] = $dateFile . '/' . $piplineBatch . '/' . $piplineItem;
	    		}
	    	}
	    	$filesToSend[] = array($piplineBatch  . '.zip', $dateFile . '/' . $piplineBatch . '.zip');
	    	$zip->close();
    	}
    	echo "*** DELETE FILES\n";
    	foreach ($filesToDelete as $file) {
    		echo "    - $file \n";
		    unlink($file);
		}
	}
	echo "\n*** DELETE FOLDERS ****\n";
	echo "************************\n";
	foreach ($foldersToDelete as $folder) {
		echo "    - $folder \n";
		rmdir($folder);
	}

	echo "\n*** FTP CONTENT ****\n";
	echo "************************\n";
    //Create new dir on FTP server as todays date and go to it
    echo "\n*** Create folder on FTP: $date\n";
    ftp_mkdir($conn_id, 'To_INNO/' . $date);

    // Upload files
    echo "*** Uploading files...\n";
    foreach ($filesToSend as $key => $file) {
    	echo "    - Uploading: $file[0]\n";
    	ftp_put($conn_id, 'To_INNO/' . $date . "/" . $file[0], $file[1] , FTP_BINARY);
    }

    // Email and shit
	echo "\n**** Starting Email ****\n";
	echo "************************\n";

    if(in_array('8', $pipelinesInUse)) {
    	echo "** Sending email for pipeline 8...\n";
	    email($recPl8, $bodPl8, $subPl8, array($dateFile . "/transmissionSheet_" . $date . ".csv" => "transmissionSheet_" . $date . ".csv"));
    }

    echo "** Sending email for all other pipelines...\n";
    email($recMain, $bodMain, $subMain, array($dateFile . "/transmissionSheet_" . $date . ".csv" => "transmissionSheet_" . $date . ".csv"));

	echo "\n**** TIDY UP ****\n";
	echo "*****************\n";

    // Delete files that have been uploaded
	echo "** Delete files that have been uploaded\n";
    foreach ($filesToSend as $file) {
    	echo "   - $file[0]\n";
    	unlink($file[1]);
    }

    // Delete temp date folder#
    echo "** Delete temp folder\n";
    echo "   - $dateFile[0]\n";
    rmdir($dateFile);


} else {
	echo "\n**** NO FILES TO SEND ****\n";
	echo "**************************\n";
    echo "** Send out notification...\n";
    email($recNote, '<p>no files</p>', 'XCP::No files');

}

	echo "\n*********************************\n";
	echo "************ + END + ************\n";
	echo "*********************************\n";

function email($to = array(), $body = null, $subject = null, $attachment = array()) {

	if($body && $subject && $to){

		echo "Sending emails...\n";
		$mail = new PHPMailer;
		
		foreach ($to as $key => $value) {
			if($key == 'TO'){
				$mail->addAddress($value);
				$okToSend = true;
			} elseif($key == 'CC') {
				$mail->addCC($value);
			} elseif($key == 'BCC') {
				$mail->addBCC($value);
			}
		}

		if(!$okToSend){
			echo 'Must have at least one TO';
		    exit;
		}

	    $mail->isSMTP();
	    $mail->Host = '10.103.109.71';
	    $mail->SMTPAuth = false;
	    $mail->Port = 25;
	    $mail->SMTPOptions = array (
	        'ssl' => array(
	            'verify_peer'  => false,
	            'verify_peer_name'  => false,
	            'allow_self_signed' => true
	        )
	    );

	 	// $mail->IsSMTP();
	 	// $mail->Host 			= 'just137.justhost.com';
		// $mail->SMTPAuth   	= true;                  	// enable SMTP authentication
		// $mail->Port       	= 26;                    	// set the SMTP port for the GMAIL server
		// $mail->Username   	= "test@hugatramp.com"; 	// SMTP account username
		// $mail->Password   	= "Password1!";        		// SMTP account password
		
		$mail->Subject 		= $subject;
		$mail->MsgHTML($body);
		$mail->SetFrom('xcp_noreply@bsigroup.com', 'XCP');	
	    if($attachment){
	    	foreach ($attachment as $fileIn => $valueOut) {
	    		$mail->addAttachment($fileIn, $valueOut);
	    	}
	    }
		if(!$mail->send()) {
		    echo 'Message could not be sent.';
		    echo 'Mailer Error: ' . $mail->ErrorInfo;
		    exit;
		}
		echo "** Sent.\n";			
	} else {
		echo 'Missing settings in email';
		exit;
	}
	

}