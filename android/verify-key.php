<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

$util = new Util();
$mensaje = '';

if ( isset($_GET['key']) ) {

    $db = DatabaseConnect::getInstance();
    $key = $_GET['key'];
    
    if ( $db->isKeyValid($key) ) {

        if ( $db->nullifyKey($key) ) {
            $mensaje = 'Cuenta verificada correctamente. Ahora puede iniciar sesión.'; 
        } else {
            $mensaje = 'Error: no ha sido posible verificar la cuenta. Inténtelo de nuevo más tarde.'; 
        }

    } else {

        $mensaje = 'Error: cuenta ya ha sido verificada o no es válida.'; 
        
    }
    
} else {

    exit();

}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Verificación')); ?>

<body>
    <p class="m-3"><?= $mensaje ?></p>
</body>

</html>