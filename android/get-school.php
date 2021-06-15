<?php 
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

$db = DatabaseConnect::getInstance();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if ( !count($_GET) ) {
        print json_encode( $db->getAllProvincias() );
    } 
    
    elseif ( isset($_GET['provincia']) ) {
        print json_encode( $db->getAllLocalidades($_GET['provincia']) );
    } 
    
    elseif ( isset($_GET['localidad']) ) {
        print json_encode( $db->getAllCentros($_GET['localidad']) );
    }
    
}