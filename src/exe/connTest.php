<?php

require("../php/init.php");

echo "\n***********************************\n";
echo "************ + START + ************\n";
echo "***********************************\n";

// Config
// TODO: Move out of this file
//ftp
$ftp_server = "203.55.173.10"; 
$ftp_user_name = "FTP-BSI"; 
$ftp_user_pass = "PL4789mn";

#$ftp_server = "ftp.hugatramp.com"; 
#$ftp_user_name = "xcptest@hugatramp.com"; 
#$ftp_user_pass = "xcpTest";

//directories


	//Check FTP Stuff
	echo "\n**** CONNECTING TO FTP ****\n";
	echo "***************************\n";
	$conn_id = ftp_connect($ftp_server); 
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
	if(ftp_pasv( $conn_id, true )) 
      echo "** Passive mode -  it worked\n"; 
    else 
      echo "** Passive mode -  it didn't work\n";
	if ((!$conn_id) || (!$login_result)) { 
        echo "!! FTP connection has failed!\n"; 
        echo "!! Attempted to connect to $ftp_server for user $ftp_user_name\n"; 
        die('Could not connect to FTP server.'); 
    }
    echo "** Conection made - $ftp_server for user $ftp_user_name \n";
    //ftp_mkdir($conn_id, 'TEST');

    // get contents of the current directory
	print_r(ftp_rawlist($conn_id, '.'));
