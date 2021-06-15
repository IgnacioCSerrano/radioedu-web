<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_RADIO;

$util = new Util();
$db = DatabaseConnect::getInstance();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

$radios = $db->getRadiosByAdmin();
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => 'Radios')) ?>

<body>

    <?php include(Constants::INC_NAVBAR); ?>

    <div class="container">
        <div class="text-center position-relative">
            <h1 class="p-5 mb-5 text-center">Listado de radios</h1>
            <a href="<?= Constants::PAGE_RADIO_FORM ?>" class="btn btn-primary item-float-right">Crear radio</a>
        </div>

        <?php include(Constants::INC_ERROR_ALERT) ?>

        <div class="list-group">
            <?php foreach ($radios as $key => $radio) : ?>
                <div class="radio-item-list d-flex py-2">
                    <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $radio['id']) ?>" 
                        class="list-group-item list-group-item-action d-flex justify-content-between">
                        <img src="<?= ROOT_FOLDER . $radio['imagen'] ?>" alt="Logo Radio" class="rounded">
                        <?= strtoupper($radio['nombre']) ?>
                    </a>
                    <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_RADIO_FORM, Constants::PARAM_RADIO, $radio['id']) ?>" 
                        class="btn btn-success d-flex justify-content-center align-items-center ml-4">
                        <i class="far fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-danger d-flex justify-content-center align-items-center ml-2 modalRadio"
                        data-toggle="modal" data-target="#modalDeleteRadio" data-id="<?= $radio['id'] ?>" 
                        data-name="<?= $radio['nombre'] ?>">
                        <i class="far fa-trash-alt"></i>
                    </a>
                </div>
            <?php endforeach ?>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalDeleteRadio" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Borrar radio</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalMessage"></div>
                    <div class="modal-footer">
                        <form action="<?= basename(__FILE__) ?>" method="POST" id="deleteForm">
                            <input type="hidden" name="delete-radio" id="idRadio"/>
                        </form>
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="deleteItem" data-dismiss="modal">Borrar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include(Constants::INC_FOOTER) ?>