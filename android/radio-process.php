<?php 
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

$db = DatabaseConnect::getInstance();
$util = new Util();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $response = array();
    $user = $db->validateToken();

    if ($user) {

        $idSub = $user['id'];

        // OBTENCIÓN DE RADIOS

        if ( isset($_POST['get-radios']) ) {

            $response['payload'] = $db->getAllRadiosSub($idSub);

        }

        // OBTENCIÓN DE PODCASTS

        elseif ( isset($_POST['get-podcasts']) ) {

            if ( !isset($_POST['id-radio']) ) {
                return;
            }

            $idRadio = $_POST['id-radio'];

            $response['payload'] = $db->getPodcastsFavByRadioId($idRadio, $idSub);

        }

        // OBTENCIÓN DE PODCAST INDIVIDUAL

        elseif ( isset($_POST['get-podcast']) ) {

            if ( !isset($_POST['id-podcast']) ) {
                return;
            }

            $idPodcast = $_POST['id-podcast'];

            $response['payload'] = $db->getPodcastsFavById($idPodcast, $idSub);

        }

        // OBTENCIÓN DE COMENTARIOS

        elseif ( isset($_POST['get-comments']) ) {

            if ( !isset($_POST['id-podcast']) ) {
                return;
            }

            $idPodcast = $_POST['id-podcast'];

            $response['payload'] = $db->getCommentsByPodcast($idPodcast);

        }

        // SUSCRIPCIÓN

        elseif ( isset($_POST['subscribe']) ) {

            if ( !isset($_POST['id-radio']) ) {
                return;
            }

            $idRadio = $_POST['id-radio'];

            $response['success'] = $db->subscribeRadio($idSub, $idRadio);

        }

        // CANCELACIÓN DE SUSCRIPCIÓN

        elseif ( isset($_POST['unsubscribe']) ) {

            if ( !isset($_POST['id-radio']) ) {
                return;
            }

            $idRadio = $_POST['id-radio'];

            $response['success'] = $db->unsubscribeRadio($idSub, $idRadio);

        }

        // CANCELACIÓN DE TODAS LAS SUSCRIPCIONES

        elseif ( isset($_POST['unsub-all-radios']) ) {

            $response['message'] = 'Error al darse de baja. Inténtalo de nuevo más tarde.';

            if ( $db->unsubscribeAllRadios($idSub) ) {
                $response['success'] = true;
                $response['message'] = '¡Suscripciones canceladas!';
            }

        }

        // FAVORITO

        elseif ( isset($_POST['like']) ) {

            if ( !isset($_POST['id-podcast']) ) {
                return;
            }

            $idPodcast = $_POST['id-podcast'];

            $response['success'] = $db->likePodcast($idSub, $idPodcast);

        }

        // ELIMINAR FAVORITO

        elseif ( isset($_POST['unlike']) ) {

            if ( !isset($_POST['id-podcast']) ) {
                return;
            }

            $idPodcast = $_POST['id-podcast'];

            $response['success'] = $db->unlikePodcast($idSub, $idPodcast);

        }

        // INCREMENTO DE VISITAS DE PODCAST

        elseif ( isset($_POST['increment-view-count']) ) {

            if ( !isset($_POST['id-podcast']) ) {
                return;
            }

            $idPodcast = $_POST['id-podcast'];

            $response['success'] = $db->incrementViewCount($idPodcast);

        }

        // INCREMENTO DE REPRODUCCIONES DE PODCAST

        elseif ( isset($_POST['increment-play-count']) ) {

            if ( !isset($_POST['id-podcast']) ) {
                return;
            }

            $idPodcast = $_POST['id-podcast'];

            $response['success'] = $db->incrementPlayCount($idPodcast);

        }

        // ENVÍO DE COMENTARIO

        elseif ( isset($_POST['send-comment']) ) {

            if ( !isset($_POST['mensaje']) || !isset($_POST['id-podcast']) ) {
                return;
            }

            $mensaje = $_POST['mensaje'];
            $idPodcast = $_POST['id-podcast'];

            $result = $db->insertComment($mensaje, $idSub, $idPodcast);

            if ( is_bool($result) ) {
                $response['success'] = $result;
            } else {
                $response['message'] = $result['message'];
                $response['success'] = false;
            }

        }

        // MODIFICACIÓN DE COMENTARIO

        elseif ( isset($_POST['update-comment']) ) {

            if ( !isset($_POST['id-comment']) || !isset($_POST['mensaje']) ) {
                return;
            }

            $idComentario = $_POST['id-comment'];
            $mensaje = $_POST['mensaje'];

            $response['success'] = $db->updateComment($idComentario, $mensaje);

        }

        // BORRADO DE COMENTARIO

        elseif ( isset($_POST['delete-comment']) ) {

            if ( !isset($_POST['id-comment']) ) {
                return;
            }

            $idComentario = $_POST['id-comment'];

            $response['success'] = $db->deleteComment($idComentario);

        }
        
    } else {

        $response['message'] = 'Token inválido';

    }
    
    print json_encode($response, JSON_PRETTY_PRINT);

}