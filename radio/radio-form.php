<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_RADIO;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

if (!$_SESSION['user']['codigo_centro']) {
    $util->redirect(ROOT_FOLDER);
}

$db = DatabaseConnect::getInstance();

$radio = $db->getRadioById($_SESSION['user']['id_radio']);
$centro = $db->getCentroByCodigo($_SESSION['user']['codigo_centro']);
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => ($radio ? 'Modificación' : 'Creación') . ' Radio')) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container">
        <div class="text-center position-relative w-100">
            <h1 class="p-5 mb-5 text-center"><?= $radio ? 'Modificar' : 'Crear' ?> radio de <?= $centro['denominacion'] ?></h1>
            <a href="<?= Constants::PAGE_RADIO_DASH ?>" class="btn btn-outline-dark item-float-left">Volver</a>
        </div>

        <div class="alert-dialog-box-form">
            <?php include(Constants::INC_ERROR_ALERT) ?>
        </div>

        <div class="w-75 mx-auto">
            <form action="<?= sprintf('%s%s', Constants::PAGE_RADIO_FORM, 
                    ($radio ? sprintf('?%s=%s', Constants::PARAM_RADIO, $radio['id']) : '')) ?>" 
                method="POST" enctype="multipart/form-data">

                <div class="form-group mb-5">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $radio['nombre'] ?? '' ?>" required>
                </div>

                <div class="img-container form-group d-flex align-items-start justify-content-between w-100 my-5">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="imgInput" name="imagen" accept="image/*">
                        <label class="custom-file-label" for="imgInput" data-browse="Explorar">
                            <?= $radio ? str_replace(Constants::IMG_RADIO_PATH, '', $radio['imagen']) : 'Subir imagen' ?>
                        </label>
                    </div>
                    <div class="img-template">
                        <img src="<?= $radio
                                        ? ROOT_FOLDER . $radio['imagen']
                                        : ROOT_FOLDER . Constants::IMG_RADIO_PH ?>" 
                                            alt="Logo Radio" id="imgDefault" class="img-fluid <?= $radio ? 'd-none' : '' ?>">
                        <img src="<?= $radio
                                        ? ROOT_FOLDER . $radio['imagen']
                                        : '' ?>" alt="Logo Radio" id="imgCustom" class="img-fluid <?= $radio ? '' : 'd-none' ?>">
                    </div>
                    <button type="button" id="btnResetImg" class="btn btn-danger invisible"><i class="fas fa-times"></i></button>
                </div>

                <?php if ($radio) : ?>
                    <input type="hidden" name="img-radio" id="imgUpdate" 
                        value="<?= str_replace(Constants::IMG_RADIO_PATH, '', $radio['imagen']) ?>">
                <?php endif; ?>

                <div class="d-flex justify-content-between my-5 py-5">
                    <input type="submit" name="<?= $radio ? 'update-radio' : 'create-radio' ?>" class="btn btn-info px-4 mr-5" value="<?= $radio ? 'Modificar' : 'Crear' ?> radio">
                    <a href="<?= Constants::PAGE_RADIO_DASH ?>" class="btn btn-danger px-4">Cancelar</a>
                </div>
            </form>
        </div>

        <?php include(Constants::INC_FOOTER) ?>