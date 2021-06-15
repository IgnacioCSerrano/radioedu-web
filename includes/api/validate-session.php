<?php 
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

session_start();

$db = DatabaseConnect::getInstance();
$util = new Util();

$isLoggedIn = false;

if ( isset($_SESSION['user']) ) { // comprobación de sesión activa

    $isLoggedIn = true;

} elseif ( isset($_COOKIE['username']) && isset( $_COOKIE['token'] ) ) { // comprobación de cookies

    $isExpiryDateVerified = false;
    $userToken = $db->getTokenByUsername( $_COOKIE['username'] );

    if ( !empty($userToken) ) {
        $current_date = date( 'Y-m-d H:i:s', time() );

        // Validación del token almacenado en la cookie con el valor encriptado de la base de datos
        $isPasswordVerified = password_verify( $_COOKIE['token'], $userToken['clave'] );
       
        // Comprobación de caducidad del token
        $isExpiryDateVerified = $userToken['fecha_caducidad'] >= $current_date;
        
        // Activación de sesión si se cumple validación o invalidación de token y limpiado de cookies en caso contrario
        if ($isPasswordVerified && $isExpiryDateVerified) {

            $_SESSION['user'] = $db->getAdminByUsernameOrEmail($_COOKIE['username']);
            $isLoggedIn = true;
            $util->console_log($isLoggedIn);

        } else {

            $db->markTokenAsExpired($userToken['id']);
            $util->clearAuthCookie();

        }
    } else {

        $util->clearAuthCookie();
        
    }

}
