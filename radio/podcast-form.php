<?php
require_once '../includes/classes/Constants.php';
require_once Constants::INC_VAL_SESSION;
require_once Constants::INC_RADIO;

$util = new Util();

if (!$isLoggedIn) {
    $util->redirect(Constants::PAGE_LOGIN);
}

$db = DatabaseConnect::getInstance();

$radio = array();
$podcast = array();

if ( isset($_GET[Constants::PARAM_RADIO]) ) {
    $radio = $db->getRadioById($_GET[Constants::PARAM_RADIO]);
    
    if (!$radio) {
        $util->redirect(Constants::PAGE_ERROR404);
    } 

    if ($radio['id'] != $_SESSION['user']['id_radio']) {
        $util->redirect(Constants::PAGE_ERROR403);
    }

    if ( isset($_GET['id-podcast']) ) {
        $podcast = $db->getPodcastById($_GET['id-podcast']);

        if (!$podcast || $podcast['id_radio'] != $radio['id']) {
            $util->redirect(Constants::PAGE_ERROR404);
        } 
    }
} else {
    $util->redirect(Constants::PAGE_ERROR404);
}
?>

<?php $util->includeWithVariables(Constants::INC_HEADER, array('title' => ($podcast ? 'Modificación' : 'Creación') . ' Podcast')) ?>

<body>

    <?php include(Constants::INC_NAVBAR) ?>

    <div class="container">
        <div class="text-center position-relative w-100">
            <h1 class="p-5 mb-5 text-center">Formulario de <?= $podcast ? 'modificación' : 'creación' ?> de podcast</h1>
            <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $radio['id']) ?>" 
                class="btn btn-outline-dark item-float-left">Volver</a>
        </div>

        <div class="alert-dialog-box-form">
            <?php include(Constants::INC_ERROR_ALERT) ?>
        </div>

        <div class="w-75 mx-auto">
            <form action="<?= sprintf('%s?%s=%s%s', Constants::PAGE_PODC_FORM, Constants::PARAM_RADIO, $radio['id'], 
                    ($podcast ? sprintf('&%s=%s', Constants::PARAM_PODC, $podcast['id']) : '')) ?>" 
                method="POST" enctype="multipart/form-data">

                <div class="form-group mb-5">
                    <label for="titulo">Título</label>  
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?= $podcast['titulo'] ?? '' ?>" required>
                </div>

                <div class="form-group my-5">
                    <label for="cuerpo">Cuerpo</label>
                    <textarea name="cuerpo" id="cuerpo"><?= $podcast['cuerpo'] ?? '' ?></textarea>
                </div>
                <script>
                    ClassicEditor
                        .create(document.querySelector('#cuerpo'))
                        .then(editor => {
                            console.log(editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });
                </script>

                <div class="img-container form-group d-flex align-items-start justify-content-between w-100 my-5">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="imgInput" name="imagen" accept="image/*">
                        <label class="custom-file-label" for="imgInput" data-browse="Explorar">
                            <?= $podcast ? str_replace(Constants::IMG_PODCAST_PATH, '', $podcast['imagen']) : 'Subir imagen' ?>
                        </label>
                    </div>
                    <div class="img-template">
                        <img src="<?= $podcast
                                        ? ROOT_FOLDER . $podcast['imagen']
                                        : ROOT_FOLDER . Constants::IMG_PODCAST_PH ?>" 
                                            alt="Logo Podcast" id="imgDefault" class="img-fluid <?= $podcast ? 'd-none' : '' ?>">
                        <img src="<?= $podcast
                                        ? ROOT_FOLDER . $podcast['imagen']
                                        : '' ?>" alt="Logo Podcast" id="imgCustom" 
                                            class="img-fluid <?= $podcast ? '' : 'd-none' ?>">
                    </div>
                    <button type="button" id="btnResetImg" class="btn btn-danger invisible"><i class="fas fa-times"></i></button>
                </div>

                <div class="form-group d-flex align-items-start justify-content-between w-100 my-5">
                    <div class="custom-file w-100">
                        <input type="file" class="custom-file-input" id="audioInput" name="audio" 
                            accept="audio/*" <?= $podcast ? '' : 'required' ?>>
                        <label class="custom-file-label" for="audioInput" data-browse="Explorar">
                            <?= $podcast ? str_replace(Constants::AUDIO_PATH, '', $podcast['audio']) : 'Subir pista de audio' ?>
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between my-5 pb-5">
                    <?php if ($podcast) : ?>
                        <input type="hidden" name="img-podcast" id="imgUpdate" value="<?= str_replace(Constants::IMG_PODCAST_PATH, '', $podcast['imagen']) ?>">
                        <input type="hidden" name="audio-podcast" value="<?= $podcast['audio'] ?>">
                        <input type="submit" name="update-podcast" class="btn btn-success px-4 mr-5" value="Modificar podcast">
                    <?php else : ?>
                        <input type="submit" name="create-podcast" class="btn btn-info px-4 mr-5" value="Crear podcast">
                    <?php endif; ?>
                    <a href="<?= sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $radio['id']) ?>" 
                        class="btn btn-danger px-4">Cancelar</a>
                </div>

            </form>
        </div>

        <?php include(Constants::INC_FOOTER) ?>