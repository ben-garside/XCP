<?php 

require("../../php/init.php");
echo getcwd();
$db = DB::getInstance();
$outArray = array();
		
$data = $db->query("SELECT TOP 100 * FROM [UAT-XCP].[dbo].[FILE_COLLATION]");
						
$results = $data->results();

	foreach($results as $result) {
		foreach($result as $key => $item){
			$outArray[$key] = $item;
		}
	} 

print(json_encode($outArray));

?>