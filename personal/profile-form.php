<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_ADMIN;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Perfil')) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container position-relative">
        <h1 class="p-5 text-center">Modificación de perfil</h1>

        <div class="alert-dialog-box">
            <?php include(Constants::INC_ERROR_ALERT) ?>
            <?php include(Constants::INC_SUCCESS_ALERT) ?>
        </div>
        
        <div class="row py-4 mb-5">

            <div class="col-md-5">
                <form action="<?= basename(__FILE__) ?>" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="upload-profile-image d-flex justify-content-center pb-5">
                        <div class="text-center position-relative">
                            <div class="d-flex justify-content-center">
                                <img class="camera-icon" src="<?= ROOT_FOLDER . Constants::IMG_CAMERA ?>" alt="Icono Cámara">
                            </div>
                            <img src="<?= ROOT_FOLDER . $_SESSION['user']['imagen'] ?>" alt="Avatar" class="img rounded-circle" id="imgProfile">
                            <small class="form-text text-black-50">Elije una imagen de tu dispositivo</small>
                            <input type="file" class="form-control-file" id="imgProfileInput" name="imagen" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= $_SESSION['user']['username'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $_SESSION['user']['email'] ?>" required>
                    </div>
                    <div class="submit-btn text-center my-5">
                        <input type="submit" name="update-profile-details" class="btn btn-success px-4 w-100" value="Modificar datos">
                    </div>
                </form>
            </div>

            <div class="col-md-5 ml-auto d-flex flex-column justify-content-end">
                <small class="text-center py-5">La nueva contraseña debe contener <?= Constants::PASSW_MUST ?></small>
                <form action="<?= basename(__FILE__) ?>" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label for="password">Contraseña actual</label>
                        <input type="password" class="form-control" id="curPassword" name="current-password" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña nueva</label>
                        <input type="password" pattern="<?= Constants::PASSW_REGEX ?>" title="Contraseña debe contener al menos <?=  Constants::PASSW_MUST ?>" class="form-control" id="password" name="new-password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm-password" required>
                    </div>
                    <div class="submit-btn text-center my-5">
                        <input type="submit" name="update-password" class="btn btn-info px-4 w-100" value="Modificar contraseña">
                    </div>
                </form>
            </div>

        </div>

        <?php include(Constants::INC_FOOTER) ?>