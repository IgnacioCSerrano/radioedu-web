<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_RADIO;

$util = new Util();
$db = DatabaseConnect::getInstance();

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

    $param = Constants::PARAM_DATE;
    $baseUrl = sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $radio['id']);
    $entries = $db->getEntries($radio['id']);
    $entry = $_GET[Constants::PARAM_DATE] ?? null;

    if ($entry) {
        $split = explode("-", $entry);
        $podcasts = $db->getPodcastsByDate($radio['id'], $split[0], $split[1]);
        if (empty($podcasts)) {
            $util->redirect(Constants::PAGE_ERROR404);
        }
    } else {
        $page = $_GET[Constants::PARAM_PAGE] ?? 1;
        $totaPages = ceil($db->getNumberPodcasts($radio['id']) / Constants::REC_PER_PAGE);
        
        if ($page < 1 || ($totaPages > 0 && $page > $totaPages)) {
            $util->redirect(Constants::PAGE_ERROR404);
        }

        $offset = ($page - 1) * Constants::REC_PER_PAGE;
        $podcasts = $db->getPodcastsByRadioId($radio['id'], $offset, Constants::REC_PER_PAGE);
    }
} else {
    $util->redirect(Constants::PAGE_ERROR404);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => $radio['nombre'])) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container">
        <div class="p-4 text-center position-relative">
            <h1 class="p-4 text-center w-50 m-auto"><?= $radio['nombre'] ?></h1>
            <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_FORM, Constants::PARAM_RADIO, $radio['id']) ?>" 
                class="btn btn-primary item-float-right">Crear podcast</a>
            <a href="<?= Constants::PAGE_RADIO_DASH ?>" class="btn btn-outline-dark item-float-left">Volver</a>
        </div>

        <?php 
        if (isset($totaPages) && $totaPages > 1) {
            include(Constants::INC_PAGINATION);
        } 
        ?>

        <div class="d-flex justify-content-around align-items-center">
            <div class="py-5 my-5">
                <a href="<?= $baseUrl ?>">
                    <img src="<?= ROOT_FOLDER . $radio['imagen'] ?>" alt="Logo Radio" class="radio-logo rounded">
                </a>
                
            </div>
            <div class="form-group w-25">
                <label for="entries">Entradas</label>
                <select class="form-control" id="entries" name="entries">
                    <option value="" disabled <?= isset($split) ? '' : 'selected' ?>>Elegir el mes</option>
                    <?php
                    foreach ($entries as $r) {
                        $year = $r['YEAR'];
                        $month = $r['MONTH'];
                        $monthName = $r['MONTHNAME'];
                        $total = $r['TOTAL'];
                        echo isset($split) && $split[0] . '-' . $split[1] == $year . '-' . $month 
                            ? "<option selected value='$baseUrl&$param=$year-$month'>$monthName $year ($total)</option>"
                            : "<option value='$baseUrl&$param=$year-$month'>$monthName $year ($total)</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row mb-5 pb-5">
            <?php foreach ($podcasts as $p) : ?>
                <div class="col-12 col-lg-4 d-flex justify-content-center">
                    <div class="entry card text-dark bg-light my-5">
                        <img class="card-img-top" src="<?= ROOT_FOLDER . $p['imagen'] ?>" alt="Logo Podcast">
                        <div class="card-body">
                            <span class="float-right fecha"><?= $p['fecha_creacion']; ?></span>
                            <h5 class="card-title"><?= $p['titulo']; ?></h5>
                            <p class="card-text pt-2">
                                <?= strlen($p['cuerpo']) > 50 
                                    ? strip_tags(substr( $p['cuerpo'], 0, 50) ) . '...' : 
                                    strip_tags( $p['cuerpo'] ); ?>
                            </p>
                            <a href="<?= sprintf('%s?%s=%s&%s=%s', 
                                    Constants::PAGE_PODC_FORM, Constants::PARAM_RADIO, $radio['id'], Constants::PARAM_PODC, $p['id']) ?>" 
                                class="btn btn-success btn-entry edit">
                                <i class="far fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-danger btn-entry delete modalPodcast"
                                data-toggle="modal" data-target="#modalDeletePodcast" data-id="<?= $p['id'] ?>">
                                <i class="far fa-trash-alt"></i>
                            </a>
                            <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_ENTRY, Constants::PARAM_PODC, $p['id']) ?>" 
                                class="btn btn-info btn-entry view">Visitar</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalDeletePodcast" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Borrar podcast</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalMessage"></div>
                    <div class="modal-footer">
                        <form action="<?=  sprintf('%s?%s=%s', basename(__FILE__), Constants::PARAM_RADIO, $radio['id']) ?>" method="POST" id="deleteForm">
                            <input type="hidden" name="delete-podcast" id="idPodcast"/>
                        </form>
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="deleteItem" data-dismiss="modal">Borrar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($totaPages) && $totaPages > 1) : ?>
            <div class="pb-5">
                <?php include(Constants::INC_PAGINATION); ?>
            </div>
        <?php endif; ?>

        <?php include(Constants::INC_FOOTER) ?>