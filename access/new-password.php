<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ACCESS;

$util = new Util();

if ($isLoggedIn) {
    $util->redirect(ROOT_FOLDER);
}

if ( !isset($_SESSION['success-code']) ) {
    $util->redirect(Constants::PAGE_CODE_REQ);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Nueva Contraseña')) ?>

<body class="access-body">

    <div class="container mt-4">
        <?php include(Constants::INC_ERROR_ALERT) ?>
    </div>

    <div class="container admin-form">
    
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="<?= basename(__FILE__) ?>" method="POST" autocomplete="off">
                    <h2 class="text-center pb-3">Nueva Contraseña</h2>
                    <div class="alert text-center ellipsis" style="padding: 0.4rem 0.4rem">
                        <span>Por favor, elija una nueva contraseña segura con <?= Constants::PASSW_MUST ?></span>
                    </div>
                    <div class="form-group pt-2">
                        <input class="form-control" type="password" pattern="<?= Constants::PASSW_REGEX ?>" title="Contraseña debe contener al menos <?=  Constants::PASSW_MUST ?>" name="password" placeholder="Contraseña" autocomplete="on" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="confirm-password" placeholder="Confirmar contraseña" autocomplete="on" required>
                    </div>
                    <div class="form-group pt-3">
                        <input class="form-control button" type="submit" name="change-password" value="Cambiar">
                    </div>
                    <div class="form-group">
                        <a href="../" class="btn d-block btn-outline-dark">Volver</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php include(Constants::INC_FOOTER) ?>