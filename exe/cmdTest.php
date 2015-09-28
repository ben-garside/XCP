<?php
if (php_sapi_name() == "cli") {
    // In cli-mode
    var_dump($argv);
    if(!$argv[1] = 'dev' && !$argv[1] = 'prd'){
    	die('Must send arg prd or dev');
    }
} else {
    die('Must be run from CLI');
}

?>