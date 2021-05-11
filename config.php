<?php

define('DB_SERVER', '26.132.63.148/XE');
define('DB_USERNAME', 'system');
define('DB_PASSWORD', '12345');
 
$link = oci_connect(DB_USERNAME, DB_PASSWORD, DB_SERVER, 'AL32UTF8');
 
if($link === false){
    die("ERROR: Could not connect.");
}
?>