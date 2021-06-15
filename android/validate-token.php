<?php 
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

$db = DatabaseConnect::getInstance();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    print json_encode($db->validateToken(), JSON_PRETTY_PRINT);
    
}