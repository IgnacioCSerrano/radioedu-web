<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ACCESS;

$util = new Util();

if ($isLoggedIn) {
    $util->redirect(ROOT_FOLDER);
}

if ( !isset($_SESSION['email']) ) {
    $util->redirect(Constants::PAGE_CODE_REQ);
}

if ( isset($_SESSION['success-code']) ) {
    $util->redirect(Constants::PAGE_NEW_PASSW);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Validación Código')) ?>

<body class="access-body">

    <div class="container admin-form">
    
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="<?= basename(__FILE__) ?>" method="POST" autocomplete="off">
                    <h2 class="text-center pb-3">Código de verificación</h2>
                    <div class="alert text-center ellipsis" style="padding: 0.4rem 0.4rem">
                        <span>Se ha enviado un código a la dirección <?= $_SESSION['email'] ?></span>
                    </div>
                    <div class="form-group pt-2">
                        <input class="form-control" type="text" pattern="\d*" title="El código es una cadena numérica de <?= Constants::CODE_LENGTH ?> dígitos." minlength="<?= Constants::CODE_LENGTH ?>" maxlength="<?= Constants::CODE_LENGTH ?>" name="code" placeholder="Código" required>
                    </div>
                    <div class="form-group pt-3">
                        <input class="form-control button" type="submit" name="check-code" value="Comprobar">
                    </div>
                    <div class="form-group">
                        <a href="../" class="btn d-block btn-outline-dark">Volver</a>
                    </div>
                </form> 
            </div>
        </div>
        
        <?php include(Constants::INC_FOOTER) ?>