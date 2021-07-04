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

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Radios')) ?>

<body>

    <?php include(Constants::INC_NAVBAR); ?>

    <div class="container">
        <div class="text-center position-relative">
            <h1 class="p-5 mb-5 text-center"><?= $centro['denominacion'] . ' (' . $centro['localidad'] . ')' ?></h1>
            <a href="<?= Constants::PAGE_RADIO_FORM ?>" class="btn btn-primary item-float-right"><?= $radio ? 'Modificar' : 'Crear' ?> radio</a>
        </div>

        <?php include(Constants::INC_ERROR_ALERT) ?>

        <div class="list-group">
            <?php if ($radio) : ?>
                <div class="radio-item-list d-flex py-2">
                    <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $radio['id']) ?>" 
                        class="list-group-item list-group-item-action d-flex justify-content-center">
                        <img src="<?= ROOT_FOLDER . $radio['imagen'] ?>" alt="Logo Radio" class="rounded mr-5">
                        <?= strtoupper($radio['nombre']) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php include(Constants::INC_FOOTER) ?>