<?php
$util = new Util();
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="<?= ROOT_FOLDER ?>">
        <img src="<?= ROOT_FOLDER . Constants::IMG_LOGO ?>" alt="Logo App" class="nav-brand-icon">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar" aria-controls="collapsingNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-collapse collapse" id="collapsingNavbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item <?= $util->echoActiveIfRequestMatches("radioedu"); ?>">
                <a class="nav-link" href="<?= ROOT_FOLDER ?>">Inicio <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item <?= $util->echoActiveIfRequestMatches("radio") ?>">
                <a class="nav-link" href="<?= Constants::PAGE_RADIO_DASH ?>">Radios</a>
            </li>
            <li class="nav-item <?= $util->echoActiveIfRequestMatches("user") ?>">
                <a class="nav-link" href="<?= Constants::PAGE_USER_DASH ?>">Usuarios</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?= ROOT_FOLDER . $_SESSION['user']['imagen'] ?>" alt="Avatar" class="nav-user-icon d-inline-block align-midle rounded-circle">
                    <?= strtolower($_SESSION['user']['username']) ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item <?= $util->echoActiveIfRequestMatches("personal") ?>" href="<?= Constants::PAGE_PROFILE ?>">
                        <i class="fas fa-cog"></i> Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= Constants::PAGE_LOGOUT ?>"> <i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                </div>
            </li>
        </ul>
    </div>
</nav>