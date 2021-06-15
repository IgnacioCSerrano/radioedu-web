<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ADMIN;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

$db = DatabaseConnect::getInstance();
$provincias = $db->getProvinciasLibres();
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Creación Admin')) ?>

<body>

    <?php include(Constants::INC_NAVBAR); ?>
    
    <div class="container">
        <div class="text-center position-relative w-100">
            <h1 class="p-5 text-center">Formulario de creación de administrador</h1>
            <a href="<?= Constants::PAGE_USER_DASH ?>" class="btn btn-outline-dark item-float-left">Volver</a>
        </div>
        <div class="container w-75">

            <form action="<?= basename(__FILE__) ?>" method="POST">
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="d-flex justify-content-between my-5">
                    <input type="submit" name="create-admin" class="btn btn-info px-4" value="Crear administrador">
                    <a href="<?= Constants::PAGE_USER_DASH ?>" class="btn btn-danger px-4">Cancelar</a>
                </div>
            </form>
            <?php include(Constants::INC_ERROR_ALERT) ?>
            <?php include(Constants::INC_SUCCESS_ALERT) ?>
        </div>
        
        <?php include(Constants::INC_FOOTER) ?>