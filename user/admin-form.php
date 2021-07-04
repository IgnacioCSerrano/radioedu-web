<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ADMIN;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

if (!$_SESSION['user']['super']) {
    $util->redirect(Constants::PAGE_ERROR403);
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
        <div class="container w-75 py-5">
            <div class="alert-dialog-box-form pt-2">
                <?php include(Constants::INC_ERROR_ALERT) ?>
                <?php include(Constants::INC_SUCCESS_ALERT) ?>
            </div>

            <form action="<?= basename(__FILE__) ?>" method="POST">
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group my-3 pt-4 text-center">
                    <h4>Centro Educativo (opcional)</h4>
                </div>
                <div class="form-group my-4">
                    <label for="provinciaSelectCreate">Provincia</label>
                    <select class="form-control" id="provinciaSelectCreate" name="provincia-create">
                        <option value="" disabled selected></option>
                        <?php
                        foreach ($provincias as $r) {
                            $provincia = $r['provincia'];
                            echo "<option value='$provincia'>$provincia</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group my-4">
                    <label for="localidadSelectCreate">Localidad</label>
                    <select class="form-control" id="localidadSelectCreate" name="localidad-create">
                        <option value="" disabled selected></option>
                    </select>
                </div>
                <div class="form-group my-4">
                    <label for="centroSelectCreate">Centro</label>
                    <select class="form-control" id="centroSelectCreate" name="centro">
                        <option value="" disabled selected></option>
                    </select>
                </div>
                <div class="form-check pt-4 mb-3">
                    <input class="form-check-input" type="checkbox" name="super" id="superCheck">
                    <label class="form-check-label" for="superCheck">
                        <strong>Super User</strong>
                    </label>
                </div>
                <div class="d-flex justify-content-between my-5">
                    <input type="submit" name="create-admin" class="btn btn-info px-4" value="Crear administrador">
                    <a href="<?= Constants::PAGE_USER_DASH ?>" class="btn btn-danger px-4">Cancelar</a>
                </div>
            </form>
        </div>
        
        <?php include(Constants::INC_FOOTER) ?>