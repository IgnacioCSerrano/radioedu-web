<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ACCESS;

$util = new Util();

if ($isLoggedIn) {
    $util->redirect(ROOT_FOLDER);
}

if ( isset($_SESSION['success-code']) ) {
    $util->redirect(Constants::PAGE_NEW_PASSW);
}

if ( isset($_SESSION['error-message']) ) {
    $errors['message'] = $_SESSION['error-message'];
    unset($_SESSION['error-message']);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Solicitud Código')) ?>

<body class="access-body">

    <div class="container mt-4">
        <?php include(Constants::INC_ERROR_ALERT) ?>
    </div>

    <div class="container admin-form">
    
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="<?= basename(__FILE__) ?>" method="POST" autocomplete="off">
                    <h2 class="text-center pb-3">Recuperación de contraseña</h2>

                    <div class="alert text-center ellipsis" style="padding: 0.4rem 0.4rem">
                        <span>Por favor, introduzca la dirección de correo electrónico asociada a su cuenta.</span>
                    </div>
                    <div class="form-group pt-2">
                        <input class="form-control" type="email" name="email" placeholder="Correo electrónico" required value="<?= $email ?>">
                    </div>
                    <div class="form-group pt-3">
                        <input class="form-control button" type="submit" name="check-email" value="Solicitar código">
                    </div>
                    <div class="form-group">
                        <a href="../" class="btn d-block btn-outline-dark">Volver</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php include(Constants::INC_FOOTER) ?>