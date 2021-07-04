<?php 
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

$db = DatabaseConnect::getInstance();
$util = new Util();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = array();
    $response = array();
    $response['success'] = false;

    $user = $db->validateToken();

    if ($user) {

        $idSub = $user['id'];

        // OBTENCIÓN DE DATOS DE PERFIL

        if ( isset($_POST['get-profile']) ) {

            $response['payload'] = $user;

        }

        // SUBIDA DE IMAGEN
        
        elseif ( isset($_POST['upload-image']) ) {

            if ( !isset($_POST['data']) ) {
                return;
            }

            $imageData = $_POST['data'];
            $imagePath = $util->uploadMobileImage(Constants::IMG_USER_PATH, $imageData);

            if ($imagePath) {
                $response['payload'] = $imagePath;
                $response['success'] = $db->updateSubImage($idSub, $imagePath);
            }

        }

        // ACTUALIZACIÓN DE DATOS DE PERFIL

        elseif ( isset($_POST['update-profile-data']) ) {
            
            if ( 
                !isset($_POST['username']) || 
                !isset($_POST['current-username']) ||
                !isset($_POST['nombre']) || 
                !isset($_POST['apellidos'])
            ) {
                return;
            }

            $response['message'] = 'Error al actualizar datos. Inténtalo de nuevo más tarde.';

            $username = $_POST['username'];
            $curUsername = $_POST['current-username'];

            $u = $db->getUserByUsernameOrEmail($username);

            if ($u && $u['username'] != $curUsername) {
                $response['message'] = 'Ya existe un usuario registrado con ese nombre de usuario.';
            } else {
                $nombre = $_POST['nombre'];
                $apellidos = $_POST['apellidos'];
                $codigoCentro = $_POST['codigo-centro'] ?? null;

                if ($db->updateSubProfileData($idSub, $username, $nombre, $apellidos, $codigoCentro)) {
                    $response['success'] = true;
                    $response['message'] = 'Datos actualizados correctamente.';
                }
            }

        }

        // CAMBIO DE CONTRASEÑA

        elseif ( isset($_POST['change-password']) ) {

            if ( !isset($_POST['current-password']) || !isset($_POST['new-password']) || !isset($_POST['confirm-password']) ) {
                return;
            }

            $response['message'] = 'Error al actualizar contraseña. Inténtalo de nuevo más tarde.';

            $curPassword = $_POST['current-password'];
            $newPassword = $_POST['new-password'];
            $confPassword = $_POST['confirm-password'];

            if ( password_verify($curPassword, $user['password']) ) {
                if ( strlen($newPassword) < Constants::PASSW_LENGTH_SHORT ) {
                    $errors['length'] = 'La contraseña tiene una longitud inferior a ' . Constants::PASSW_LENGTH_SHORT . ' caracteres.';
                }
            
                if ($newPassword !== $confPassword) {
                    $errors['match'] = 'Las contraseñas no coinciden.';
                }
            
                if ( empty($errors) && $db->updatePassword($user['id'], $newPassword) ) {
                    $response['success'] = true;
                    $response['message'] = 'Contraseña actualizada correctamente.';
                }
            } else {
                $response['message'] = 'Contraseña actual incorrecta.';
            }

        }

        // CAMBIO DE EMAIL

        elseif ( isset($_POST['change-email']) ) {

            if ( !isset($_POST['email']) ) {
                return;
            }

            $response['message'] = 'Error al actualizar correo. Inténtalo de nuevo más tarde.';

            $email = $_POST['email'];

            if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
                $errors['email'] = 'Formato de email incorrecto.';
                return;
            } 

            $curEmail = $_POST['current-email'];

            $u = $db->getUserByUsernameOrEmail(null, $email);
            
            if ($u && $u['email'] != $curEmail) {
                $response['message'] = 'Ya existe un usuario registrado con esa dirección de correo.';
            } else {
                $response['success'] = $db->updateEmail($user['id'], $email);
            }

        }

        // BORRADO DE CUENTA

        elseif ( isset($_POST['delete-account']) ) {
            $response['success'] = $db->deleteSubscriber($user);
        }
        
    } else {

        $response['message'] = 'Token inválido';
        
    }

    print json_encode($response, JSON_PRETTY_PRINT);

}
