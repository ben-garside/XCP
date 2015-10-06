<?php 

require("../php/init.php");
$db = DB::getInstance();
$outArray = array();
		
$data = $db->query("SELECT TOP 100 * FROM [UAT-XCP].[dbo].[FILE_COLLATION]");
						
$results = $data->results();
//print_r($results);

	foreach($results as $result) {
		$outArray['aaData'][] = array(
            $result->ID,$result->XCP_ID,$result->FILE_NAME,$result->FILE_LOCATION,$result->COLLATION_DATE,$result->STATUS
		);
	} 

print(json_encode($outArray));

?>