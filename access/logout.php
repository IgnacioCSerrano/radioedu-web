<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;

$util = new Util();

if ( !$isLoggedIn ) {
    $util->redirect(Constants::PAGE_LOGIN);
} 

session_destroy();
$util->clearAuthCookie();
$util->redirect(ROOT_FOLDER);