<?php
/**
 * This file is responsible for creating and initializing the FileMaker object.
 * This object allows to manipulate data in the database. 
*/
    
    //to include the FileMaker PHP API
    require_once ('FileMaker.php');

     define('FM_HOST', '172.16.9.42');
     define('FM_FILE', 'userActivities.fmp12');
     define('FM_USER', 'admin');
     define('FM_PASS', 'mindfire');
    
    //to create the FileMaker Object
    $fm = new FileMaker(FM_FILE, FM_HOST, FM_USER, FM_PASS);
    
    return $fm;
?>
