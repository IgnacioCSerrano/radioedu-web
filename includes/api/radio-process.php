<?php

$db = DatabaseConnect::getInstance();
$util = new Util();

$errors = array();

// CREACIÓN DE RADIO

if ( isset($_POST['create-radio']) ) {

    if ( !isset($_POST['nombre']) ) {
        $errors['form'] = Constants::ERROR_FORM;
        return;
    }

    $nombre = $_POST['nombre'];
    $imagen = $util->uploadImage(Constants::IMG_RADIO_PATH, $_FILES['imagen'], Constants::IMG_RADIO_PH);

    if ($db->insertRadio($nombre, $imagen, $_SESSION['user']['codigo_centro'], $_SESSION['user']['id'])) {
        $util->redirect(Constants::PAGE_RADIO_DASH);
    } else {
        $errors['insert'] = 'Error al crear radio. Inténtelo de nuevo más tarde.';
    }

}

// MODIFICACIÓN DE RADIO

elseif ( isset($_POST['update-radio']) ) {

    if ( !isset($_POST['nombre']) ) {
        $errors['form'] = Constants::ERROR_FORM;
        return;
    }

    $nombre = $_POST['nombre'];
    $imagen = $util->uploadImage(Constants::IMG_RADIO_PATH, $_FILES['imagen'], 
        Constants::IMG_RADIO_PATH . $_POST['img-radio']);
    $idRadio = $_SESSION['user']['id_radio'];

    if ($db->updateRadio($idRadio, $nombre, $imagen)) {
        $util->redirect(Constants::PAGE_RADIO_DASH);
    } else {
        $errors['update'] = 'Error al modificar radio. Inténtelo de nuevo más tarde.';
    }

}

// BORRADO DE RADIO

elseif ( isset($_POST['delete-radio']) ) {

    $idRadio = $_POST['delete-radio'];

    if ($db->deleteRadio($_SESSION['user']['id'], $idRadio)) {
        $util->redirect(Constants::PAGE_RADIO_DASH);
    } else {
        $errors['delete'] = 'Error al borrar radio. Inténtelo de nuevo más tarde.';
    }

}

// CREACIÓN DE PODCAST

elseif ( isset($_POST['create-podcast']) ) {

    if ( 
        !isset($_POST['titulo']) || 
        !isset($_POST['cuerpo']) || 
        $_FILES['audio']['size'] == 0 ||
        !isset($_GET['id-radio'])
    ) {
        $errors['form'] = Constants::ERROR_FORM;
        return;
    }

    $titulo = $_POST['titulo'];
    $cuerpo = $_POST['cuerpo'];
    $imagen = $util->uploadImage(Constants::IMG_PODCAST_PATH, $_FILES['imagen'], Constants::IMG_PODCAST_PH);
    $audio = $util->uploadAudio(Constants::AUDIO_PATH, $_FILES['audio']);
    $idRadio = $_GET['id-radio'];

    $nombreRadio = $db->getRadioById($idRadio)['nombre'];

    $tokenArray = array();
    foreach ($db->getFbTokenRadio($idRadio) as $row) {
        array_push($tokenArray, $row['fb_token']);
    }

    if ( $db->insertPodcast($titulo, $cuerpo, $imagen, $audio, $idRadio) ) {
        $nombreRadio = $db->getRadioById($idRadio)['nombre'];

        $util->sendNotification($nombreRadio, sprintf('¡Nueva entrada de %s!: "%s"', $nombreRadio, $titulo), $tokenArray); // envío de notificaciones

        $util->redirect(sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $idRadio));
    } else {
        $errors['insert'] = 'Error al crear podcast. Inténtelo de nuevo más tarde.';
    }

}

// MODIFICACIÓN DE PODCAST

elseif ( isset($_POST['update-podcast']) ) {

    if ( 
        !isset($_POST['titulo']) || 
        !isset($_POST['cuerpo']) || 
        !isset($_POST['img-podcast']) || 
        !isset($_GET['id-podcast']) ||
        !isset($_GET['id-radio'])
    ) {
        $errors['form'] = Constants::ERROR_FORM;
        return;
    }

    $titulo = $_POST['titulo'];
    $cuerpo = $_POST['cuerpo'];
    $imagen = $util->uploadImage(Constants::IMG_PODCAST_PATH, $_FILES['imagen'], 
        Constants::IMG_PODCAST_PATH . $_POST['img-podcast']);
    $audio = empty(basename($_FILES['audio']['name'])) 
        ? $_POST['audio-podcast']
        : $util->uploadAudio(Constants::AUDIO_PATH, $_FILES['audio']);
    $idPodcast = $_GET['id-podcast'];
    $idRadio = $_GET['id-radio'];

    if ( $db->updatePodcast($idPodcast, $titulo, $cuerpo, $imagen, $audio) ) {
        $util->redirect(sprintf('%s?%s=%s', Constants::PAGE_PODC_DASH, Constants::PARAM_RADIO, $idRadio));
    } else {
        $errors['update'] = 'Error al modificar podcast. Inténtelo de nuevo más tarde.';
    }

}

// BORRADO DE PODCAST

elseif ( isset($_POST['delete-podcast']) ) {

    $idPodcast = $_POST['delete-podcast'];

    if ( !$db->deletePodcast($_SESSION['user']['id'], $idPodcast) ) {
        $errors['delete'] = 'Error al borrar podcast. Inténtelo de nuevo más tarde.';
    }

}

// ENVÍO DE COMENTARIO

elseif ( isset($_POST['send-comment']) ) {

    if ( !isset($_POST['mensaje']) || !isset($_GET['id-podcast']) ) {
        $errors['form'] = Constants::ERROR_FORM;
        return;
    }

    $mensaje = $_POST['mensaje'];
    $idUsuario = $_SESSION['user']['id'];
    $idPodcast = $_GET['id-podcast'];

    if ( $db->insertCommentAdmin($mensaje, $idUsuario, $idPodcast) ) {
        $util->redirect(sprintf('%s?%s=%s', Constants::PAGE_PODC_ENTRY, Constants::PARAM_PODC, $idPodcast));
    } else {
        $errors['insert'] = 'Error al enviar comentario. Inténtelo de nuevo más tarde.';
    }

}

// BORRADO DE COMENTARIO

elseif ( isset($_POST['delete-comment']) ) {

    if ( !isset($_GET['id-podcast']) ) {
        $errors['form'] = 'Error al borrar comentario. No ha sido posible recuperar la clave primaria.';
        return;
    }

    $idComment = $_POST['delete-comment'];
    $idPodcast = $_GET['id-podcast'];

    if ( $db->deleteCommentByAdmin($_SESSION['user']['id'], $idComment) ) {
        $util->redirect(sprintf('%s?%s=%s', Constants::PAGE_PODC_ENTRY, Constants::PARAM_PODC, $idPodcast));
    } else {
        $errors['delete'] = 'Error al borrar comentario. Inténtelo de nuevo más tarde.';
    }

}

// BLOQUEO DE COMENTARIOS EN PODCAST

elseif ( isset($_POST['block-podcast']) ) {

    $idPodcast = $_POST['block-podcast'];
    $state = isset($_POST['podcast-state']) ? 1 : 0;
    $db->blockPodcast($idPodcast, $state);
    
}

