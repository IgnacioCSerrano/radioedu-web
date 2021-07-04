<?php

// NOMBRE DE DIRECTORIO QUE CONTIENE SITIO WEB (CARPETA CONTENEDORA)

defined('ROOT_FOLDER') || 
    define('ROOT_FOLDER', '/radioedu/');

// RUTA ABSOLUTA (EN MÁQUINA SERVIDOR) DEL DIRECTORIO QUE CONTIENE SITIO WEB

defined('ROOT_PATH') || 
    define('ROOT_PATH', 
        $_SERVER['DOCUMENT_ROOT'] . ROOT_FOLDER);

// URL DE DOMINIO WEB

defined('DOMAIN_URL') ||
    define('DOMAIN_URL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ROOT_FOLDER);

class Constants {

    // COMÚN

    public const ADMIN              = 'administrador';
    public const SUB                = 'suscriptor';
    public const MYSQL_INIT_CMD     = 'SET lc_time_names=\'es_ES\'';
    public const ERROR_FORM         = 'Error al enviar el formulario: No se han rellenado todos los campos.';

    public const REC_PER_PAGE       = 15;
    public const PAG_INTERV         = 6;

    // CREDENCIALES DE CONEXIÓN A BASE DE DATOS

    public const DB_HOST        = 'localhost';
    public const DB_NAME        = 'radioedu_db';
    public const DB_USER        = 'root';
    public const DB_PASSWORD    = 'root';

    // CREDENCIALES DE CUENTA DE CORREO CORPORATIVO Y PARÁMETROS DE CONEXIÓN

    public const SENDER_NAME            = 'RadioEdu';
    public const SENDER_EMAIL_ADDRESS   = '';
    public const SENDER_EMAIL_PASSWORD  = '';

    // PARÁMETROS PHPMAILER

    public const MAIL_HOST              = 'smtp.gmail.com';
    public const MAIL_PORT              = '587';
    public const MAIL_SMTP              = 'tls';
    public const MAIL_CHARSET           = 'UTF-8';

    // FIREBASE API

    public const FCM_URL = 'https://fcm.googleapis.com/fcm/send';
    public const FCM_API = ''; // Firebase Console -> Project Settings -> Cloud Messaging -> Server key

    // SEGURIDAD

    public const CODE_ALPHANUMERIC  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    public const CODE_NUMERIC       = '0123456789';
    public const COOKIE_EXP_TIME    = 30 * 24 * 60 * 60;
    public const TOKEN_LENTH_SHORT  = 72;
    public const TOKEN_LENGTH_LONG  = 255;

    public const CODE_LENGTH        = 6;
    public const PASSW_LENGTH_LONG  = 8;
    public const PASSW_LENGTH_SHORT = 6;
    public const PASSW_REGEX        = '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{' . self::PASSW_LENGTH_LONG . ',}$';
    public const PASSW_MUST         = 'mayúscula, minúscula, número, carácter especial y longitud mínima de ' . self::PASSW_LENGTH_LONG . ' caracteres.';
     

    // RUTA VERIFICACIÓN

    public const PATH_VERIFY  = DOMAIN_URL . 'android/verify-key.php';

    // RUTAS IMG

    public const IMG_PATH           = 'static/img/';
    public const IMG_USER_PATH      = self::IMG_PATH . 'user/';
    public const IMG_RADIO_PATH     = self::IMG_PATH . 'radio/';
    public const IMG_PODCAST_PATH   = self::IMG_PATH . 'podcast/';
    public const IMG_LOGO           = self::IMG_PATH . 'logo.png';
    public const IMG_CAMERA         = self::IMG_PATH . 'camera-solid.svg';
    public const IMG_ADMIN_PH       = self::IMG_USER_PATH . 'admin-placeholder.png';
    public const IMG_SUB_PH         = self::IMG_USER_PATH . 'sub-placeholder.png';
    public const IMG_RADIO_PH       = self::IMG_RADIO_PATH . 'radio-placeholder.png';
    public const IMG_PODCAST_PH     = self::IMG_PODCAST_PATH . 'podcast-placeholder.png';

    // RUTAS AUDIO

    public const AUDIO_PATH = 'static/audio/';

    // RUTAS INCLUDE

    public const INC_CLASS_PATH     = ROOT_PATH . 'includes/classes/';
    public const INC_UTIL_CLASS     = self::INC_CLASS_PATH . 'Util.php';
    public const INC_CONN_CLASS     = self::INC_CLASS_PATH . 'DatabaseConnect.php';
    public const INC_EMAIL_CLASS    = self::INC_CLASS_PATH . 'Email.php';

    public const INC_PHPMAILER_PATH = ROOT_PATH . 'includes/phpmailer/';
    public const INC_PHPMAILER      = self::INC_PHPMAILER_PATH . 'PHPMailer.php';
    public const INC_PHPMAILER_SMTP = self::INC_PHPMAILER_PATH . 'SMTP.php';
    public const INC_PHPMAILER_EXC  = self::INC_PHPMAILER_PATH . 'Exception.php';

    public const INC_API_PATH       = ROOT_PATH . 'includes/api/';
    public const INC_VAL_SESSION    = self::INC_API_PATH . 'validate-session.php';
    public const INC_ACCESS         = self::INC_API_PATH . 'access-process.php';
    public const INC_ADMIN          = self::INC_API_PATH . 'admin-process.php';
    public const INC_RADIO          = self::INC_API_PATH . 'radio-process.php';

    public const INC_COMP_PATH      = ROOT_PATH . 'includes/components/';
    public const INC_HEADER         = self::INC_COMP_PATH . 'header.php';
    public const INC_NAVBAR         = self::INC_COMP_PATH . 'navbar.php';
    public const INC_FOOTER         = self::INC_COMP_PATH . 'footer.php';
    public const INC_ERROR_ALERT    = self::INC_COMP_PATH . 'alert-error.php';
    public const INC_SUCCESS_ALERT  = self::INC_COMP_PATH . 'alert-success.php';
    public const INC_PAGINATION     = self::INC_COMP_PATH . 'pagination.php';

    // RUTAS PÁGINAS ACCESO

    public const PAGE_ACCESS        = 'access';
    public const PAGE_ACCESS_PATH   = ROOT_FOLDER . self::PAGE_ACCESS . '/';
    public const PAGE_LOGIN         = self::PAGE_ACCESS_PATH . 'login.php';
    public const PAGE_LOGOUT        = self::PAGE_ACCESS_PATH . 'logout.php';
    public const PAGE_CODE_REQ      = self::PAGE_ACCESS_PATH . 'code-request.php';
    public const PAGE_CODE_VAL      = self::PAGE_ACCESS_PATH . 'code-validation.php';
    public const PAGE_NEW_PASSW     = self::PAGE_ACCESS_PATH . 'new-password.php';
    
    // RUTAS PÁGINAS RADIO

    public const PAGE_RADIO         = 'radio';
    public const PAGE_RADIO_PATH    = ROOT_FOLDER . self::PAGE_RADIO . '/';
    public const PAGE_RADIO_DASH    = self::PAGE_RADIO_PATH . 'radio-dashboard.php';
    public const PAGE_RADIO_FORM    = self::PAGE_RADIO_PATH . 'radio-form.php';
    public const PAGE_PODC_DASH     = self::PAGE_RADIO_PATH . 'podcast-dashboard.php';
    public const PAGE_PODC_FORM     = self::PAGE_RADIO_PATH . 'podcast-form.php';
    public const PAGE_PODC_ENTRY    = self::PAGE_RADIO_PATH . 'podcast-entry.php';

    // RUTAS PÁGINAS USUARIO

    public const PAGE_USER          = 'user';
    public const PAGE_USER_PATH     = ROOT_FOLDER . self::PAGE_USER . '/';
    public const PAGE_USER_DASH     = self::PAGE_USER_PATH . 'user-dashboard.php';
    public const PAGE_USER_FORM     = self::PAGE_USER_PATH . 'admin-form.php';

    // RUTAS PÁGINAS PERFIL

    public const PAGE_PROFILE       = 'personal';
    public const PAGE_PROFILE_PATH  = ROOT_FOLDER . self::PAGE_PROFILE .  '/';
    public const PAGE_PROFILE_FORM  = self::PAGE_PROFILE_PATH . 'profile-form.php';

    // RUTAS PÁGINAS ERROR

    public const PAGE_ERROR         = 'errors';
    public const PAGE_ERROR_PATH    = ROOT_FOLDER . self::PAGE_ERROR . '/';
    public const PAGE_ERROR403      = self::PAGE_ERROR_PATH . 'error403.php';
    public const PAGE_ERROR404      = self::PAGE_ERROR_PATH . 'error404.php';

    // PARÁMETROS (RUTA)

    public const PARAM_RADIO    = 'id-radio';
    public const PARAM_PODC     = 'id-podcast';
    public const PARAM_PAGE     = 'page';
    public const PARAM_DATE     = 'date';
    public const PARAM_KEY      = 'key';
    
}