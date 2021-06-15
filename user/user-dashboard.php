<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

$subs = DatabaseConnect::getInstance()->getAllSubscribers();
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Usuarios')) ?>

<body>

    <?php include(Constants::INC_NAVBAR); ?>
    
    <div class="container">
        <div class="text-center position-relative">
            <h1 class="p-5 text-center">Listado de usuarios</h1>
            <a href="<?= Constants::PAGE_USER_FORM ?>" class="btn btn-primary item-float-right">Crear admin</a>
        </div>
        <table class="table table-striped user-table">
            <thead>
                <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Correo electrónico</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Centro</th>
                    <th scope="col">Verificado</th>
                    <th scope="col">Imagen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subs as $key => $sub) : ?>
                <tr>
                    <td>
                        <?= strtolower($sub['username']) ?>
                    </td>
                    <td>
                        <?= $sub['email'] ?>
                    </td>
                    <td>
                        <?= $sub['nombre'] . ' ' . $sub['apellidos'] ?>
                    </td>
                    <td>
                        <?= $sub['denominacion'] ? sprintf('%s (%s)', $sub['denominacion'], $sub['localidad']) : '- sin especificar -' ?>
                    </td>
                    <td>
                        <?= $sub['activation_key'] ? 'NO' : 'SÍ' ?>
                    </td>
                    <td>
                        <img src="<?= ROOT_FOLDER . $sub['imagen'] ?>" alt="Avatar" class="rounded">
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        
        <?php include(Constants::INC_FOOTER) ?>