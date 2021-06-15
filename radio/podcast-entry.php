<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_RADIO;

$util = new Util();
$db = DatabaseConnect::getInstance();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

if ( isset($_GET[Constants::PARAM_PODC]) ) {
    $idPodcast = $_GET[Constants::PARAM_PODC];

    $podcast = $db->getPodcastById($idPodcast);

    if (!$podcast) {
        $util->redirect(Constants::PAGE_ERROR404);
    }

    $radio = $db->getRadioById($podcast['id_radio']);

    if ( $radio['id_admin'] != $_SESSION['user']['id'] ) {
        $util->redirect(Constants::PAGE_ERROR403);
    }
    
    $comments = $db->getCommentsByPodcast($podcast['id']);
} else {
    $util->redirect(Constants::PAGE_ERROR404);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => $podcast['titulo'])) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container">
        
        <div class="p-4 text-center position-relative">
            <h1 class="p-4 text-center w-50 m-auto">
                <?= $podcast['titulo'] ?>
            </h1>
            <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $podcast['id_radio']) ?>" 
                class="btn btn-outline-dark item-float-left">Volver</a>
            <div class="item-float-right">
                <form action="<?= sprintf('%s?%s=%s', basename(__FILE__), Constants::PARAM_PODC, $idPodcast) ?>" method="POST" id="blockPodForm">
                    <input type="hidden" name="block-podcast" value="<?= $podcast['id'] ?>">
                    <div class="form-check">
                        <input type="checkbox" name="podcast-state" class="form-check-input" id="checkBlockPod" 
                            <?= $podcast['bloqueado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="checkBlockPod">Bloquear comentarios</label>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center py-5">
            <img src="<?= ROOT_FOLDER . $podcast['imagen'] ?>" alt="Logo Podcast" class="radio-logo">
        </div>
        <div class="pt-5">
            <?= $podcast['cuerpo'] ?>
        </div>

        <!-- Source: https://codepen.io/davatron5000/pen/uqktG -->

        <div class="py-5" id="pcast-player">
            <div class="pcast-player-controls d-flex justity-content-around align-items-center rounded">
                <button class="pcast-play mr-1"><i class="fa fa-play"></i></button>
                <button class="pcast-pause mr-1"><i class="fa fa-pause"></i></button>
                <button class="pcast-rewind"><i class="fa fa-fast-backward"></i></button>
                <span class="pcast-current-time pcast-time">00:00:00</span>
                <progress class="pcast-progress" value="0"></progress>
                <span class="pcast-duration pcast-time">00:00:00</span>
                <button class="pcast-speed mr-1">1x</button>
                <button class="pcast-mute"><i class="fa fa-volume-up"></i></button>
            </div>
            <audio src="<?= ROOT_FOLDER . $podcast['audio'] ?>"></audio>
            <a class="pcast-download" href="<?= ROOT_FOLDER . $podcast['audio'] ?>" download>Descargar audio</a>
        </div>

        <div class="wrapper py-5 mb-5">
            <form action="<?= sprintf('%s?%s=%s', basename(__FILE__), Constants::PARAM_PODC, $idPodcast) ?>" method="POST" class="form">
                <div class="input-group textarea">
                    <textarea id="mensaje" name="mensaje" placeholder="Escriba un comentario..." required></textarea>
                </div>
                <div class="input-group pb-4">
                    <input type="submit" name="send-comment" class="btn btn-info" value="Enviar comentario">
                </div>
            </form>
            <div>
                <?php foreach($comments as $c) : ?>
                    <div class="card card-white post my-4">
                        <div class="post-heading">
                            <div class="float-left image">
                                <img src="<?= ROOT_FOLDER . $c['imagen'] ?>" class="rounded-circle avatar" alt="Avatar">
                            </div>
                            <div class="float-left meta">
                                <div class="title h5">
                                    <p><?= $c['username'] ?></p>
                                </div>
                                <h6 class="text-muted time"><?= $c['fecha_registro']; ?></h6>
                            </div>
                        </div> 
                        <div class="post-description"> 
                            <p>
                                <?= $c['mensaje']; ?>
                            </p>
                        </div>
                        <a href="#" class="btn btn-danger delete modalComment"
                                data-toggle="modal" data-target="#modalDeleteComment" data-id="<?= $c['id'] ?>">
                            <i class="far fa-trash-alt"></i>
                        </a>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalDeleteComment" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Borrar comentario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modalMessage"></div>
                    <div class="modal-footer">
                        <form action="<?= sprintf('%s?%s=%s', basename(__FILE__), Constants::PARAM_PODC, $idPodcast) ?>" method="POST" id="deleteForm">
                            <input type="hidden" name="delete-comment" id="idComment"/>
                        </form>
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="deleteItem" data-dismiss="modal">Borrar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include(Constants::INC_FOOTER) ?>