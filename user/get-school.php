<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;

/*
    Esta ruta es accedida mediante jQuery (AJAX) para obtener dinámicamente los datos
    de los centros educativos, con lo que no puede estar situada dentro del directorio 
    'includes' porque sería inaccesible debido a las restricciones de .htaccess
*/

$db = DatabaseConnect::getInstance();
$util = new Util();

/*
    Las peticiones de sufijo '-create' se usan en la creación porque recuperan los datos 
    de localidades/centros libres (no asignados a ninguna radio), 
    mientras que las peticiones de sufijo '-update' se usan en la modificación porque 
    recuperan los datos de localidades/centros libres junto con la localidad/centro del 
    valor pasado por parámetro (código de centro educativo del registro existente)
*/

if ( isset($_GET['provincia-create']) ) {
    echo json_encode( $db->getLocalidadesLibres($_GET['provincia-create']) );
}

elseif ( isset($_GET['localidad-create']) ) {
    echo json_encode( $db->getCentrosLibres($_GET['localidad-create']) );
}

elseif ( isset($_GET['provincia-update']) ) {
    echo json_encode( $db->getLocalidadesLibres($_GET['provincia-update'], $_GET['codigo-centro']) );
}

elseif ( isset($_GET['localidad-update']) ) {
    echo json_encode( $db->getCentrosLibres($_GET['localidad-update'], $_GET['codigo-centro']) );
}
