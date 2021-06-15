<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_RADIO;

$util = new Util();
$db = DatabaseConnect::getInstance();

$radio = array();
$provincias = array();
$localidades = array();
$centros = array();
$centro = null;

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

if ( isset($_GET[Constants::PARAM_RADIO]) ) {
    $radio = $db->getRadioById($_GET[Constants::PARAM_RADIO]);

    if (!$radio) {
        $util->redirect(Constants::PAGE_ERROR404);
    }

    if ( $radio['id_admin'] != $_SESSION['user']['id'] ) {
        $util->redirect(Constants::PAGE_ERROR403);
    }

    $centro = $db->getCentroByCodigo($radio['codigo_centro']);
    $provincias = $db->getProvinciasLibres($radio['codigo_centro']);
    $localidades = $db->getLocalidadesLibres($centro['provincia'], $radio['codigo_centro']);
    $centros = $db->getCentrosLibres($centro['localidad'], $radio['codigo_centro']);
} else {
    $provincias = $db->getProvinciasLibres();
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => ($radio ? 'Modificación' : 'Creación') . ' Radio')) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container">
        <div class="text-center position-relative w-100">
            <h1 class="p-5 mb-5 text-center">Formulario de <?= $radio ? 'modificación' : 'creación' ?> de radio</h1>
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
                    <input type="hidden" name="cod-centro" id="codCentro" value="<?= $radio['codigo_centro'] ?>">

                    <div class="form-group my-5">
                        <label for="provinciaSelectUpdate">Provincia</label>
                        <select class="form-control" id="provinciaSelectUpdate" name="provincia-update" required>
                            <?php
                            foreach ($provincias as $r) {
                                $p = $r['provincia'];
                                echo $p === $centro['provincia']
                                    ? "<option value='$p' selected>$p</option>"
                                    : "<option value='$p'>$p</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group my-5">
                        <label for="localidadSelectUpdate">Localidad</label>
                        <select class="form-control" id="localidadSelectUpdate" name="localidad-update" required>
                            <?php
                            foreach ($localidades as $r) {
                                $l = $r['localidad'];
                                echo $l === $centro['localidad']
                                    ? "<option value='$l' selected>$l</option>"
                                    : "<option value='$l'>$l</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group my-5">
                        <label for="centroSelectUpdate">Centro</label>
                        <select class="form-control" id="centroSelectUpdate" name="centro" required>
                            <?php
                            foreach ($centros as $r) {
                                $nom = $r['denominacion'];
                                $cod = $r['codigo'];
                                echo $nom === $centro['denominacion']
                                    ? "<option value='$cod' selected>$nom</option>"
                                    : "<option value='$cod'>$nom</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="submit-btn d-flex justify-content-between my-5 pb-5">
                        <input type="hidden" name="id-radio" value="<?= $radio['id'] ?>">
                        <input type="submit" name="update-radio" class="btn btn-success px-4 mr-5" value="Modificar radio">
                        <a href="<?= Constants::PAGE_RADIO_DASH ?>" class="btn btn-danger px-4">Cancelar</a>
                    </div>

                <?php else : ?>

                    <div class="form-group my-4">
                        <label for="provinciaSelectCreate">Provincia</label>
                        <select class="form-control" id="provinciaSelectCreate" name="provincia-create" required>
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
                        <select class="form-control" id="localidadSelectCreate" name="localidad-create" required>
                            <option value="" disabled selected></option>
                        </select>
                    </div>
                    <div class="form-group my-4">
                        <label for="centroSelectCreate">Centro</label>
                        <select class="form-control" id="centroSelectCreate" name="centro" required>
                            <option value="" disabled selected></option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between my-5 pb-5">
                        <input type="submit" name="create-radio" class="btn btn-info px-4 mr-5" value="Crear radio">
                        <a href="<?= Constants::PAGE_RADIO_DASH ?>" class="btn btn-danger px-4">Cancelar</a>
                    </div>

                <?php endif; ?>

            </form>
        </div>

        <?php include(Constants::INC_FOOTER) ?>