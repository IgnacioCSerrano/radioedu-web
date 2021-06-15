<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ACCESS;

$util = new Util();

if ($isLoggedIn) {
    $util->redirect(ROOT_FOLDER);
}

if ( isset($_SESSION['success-message']) ) {
    $success['message'] = $_SESSION['success-message'];
    unset($_SESSION['success-message']);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Inicio Sesión')) ?>

<body class="access-body">

    <div class="container">
        
        <div class="container mt-4">
            <?php include(Constants::INC_ERROR_ALERT) ?>
            <?php include(Constants::INC_SUCCESS_ALERT) ?>
        </div>

        <div class="container admin-form">
            <div class="row">
                <div class="col-md-4 offset-md-4 form">
                    <div>
                        <img src="<?= ROOT_FOLDER . Constants::IMG_LOGO ?>" alt="Logo App" class="img-fluid">
                    </div>
                    <form action="<?= basename(__FILE__) ?>" method="POST" autocomplete="off">
                        <h3 class="text-center py-4">Inicio de sesión</h3>
                        <div class="form-group">
                            <input class="form-control" type="text" name="handle" placeholder="Usuario o correo electrónico" required value="<?= $handle ?>">
                        </div>
                        <div class="form-group">
                            <input class="form-control" type="password" name="password" placeholder="Contraseña" autocomplete="on" required>
                        </div>
                        <div class="checkbox my-4">
                            <label>
                                <input type="checkbox" id="remember" name="remember"> Recuérdame
                            </label>
                        </div>
                        <div class="form-group mb-0">
                            <input type="submit" name="login" value="Inicio" class="form-control button">
                        </div>
                        <div class="link forget-pass text-center mt-3 mb-0"><a href="<?= Constants::PAGE_CODE_REQ ?>">¿Ha olvidado su contraseña?</a></div>
                    </form>
                </div>
            </div>
        </div>

        <?php include(Constants::INC_FOOTER) ?>