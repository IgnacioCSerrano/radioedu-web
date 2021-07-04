<?php
require_once './includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

if (basename($_SERVER['REQUEST_URI'], '.php') == 'index') {
    $util->redirect(ROOT_FOLDER);
}
?>

<!-- Primera versión realizada por: Ignacio Cuevas Serrano -->

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Inicio')) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container menu-inicio d-flex justify-content-center align-items-center flex-wrap">
        <?php if ($_SESSION['user']['codigo_centro']) : ?>
            <div class="box rojo">
                <a href="<?= Constants::PAGE_RADIO_DASH ?>" class="d-flex flex-column justify-content-center align-items-center">
                    <span><i class="fas fa-broadcast-tower"></i></span>
                    <p>Radio</p>
                </a>
            </div>
        <?php endif; ?>
        <div class="box verde">
            <a href="<?= Constants::PAGE_USER_DASH ?>" class="d-flex flex-column justify-content-center align-items-center">
                <span><i class="fas fa-users"></i></span>
                <p>Usuarios</p>
            </a>
        </div>
        <div class="box azul">
            <a href="<?= Constants::PAGE_PROFILE_FORM ?>" class="d-flex flex-column justify-content-center align-items-center">
                <span><i class="fas fa-cogs"></i></span>
                <p>Perfil</p>
            </a>
        </div>

        <?php include(Constants::INC_FOOTER) ?>
        