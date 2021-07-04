<?php 
require_once '../includes/classes/Constants.php';
require_once Constants::INC_UTIL_CLASS;
require_once Constants::INC_CONN_CLASS;

$db = DatabaseConnect::getInstance();
$util = new Util();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $response = array();
    $response['success'] = false;

    // INICIO DE SESIÓN

    if ( isset($_POST['login']) ) {

        if ( !isset($_POST['handle']) || !isset($_POST['password']) ) {
            return;
        }

        $handle = $_POST['handle'];
        $password = $_POST['password'];
        
        $user = $db->getSubByUsernameOrEmail($handle);

        if ($user) {
            if ($user['activation_key'] != null) {
                $response['message'] = 'Cuenta no verificada.';
            } else if ( password_verify($password, $user['password']) ) {
                $bearToken = $util->generateCode(Constants::CODE_ALPHANUMERIC, Constants::TOKEN_LENTH_SHORT);
                if ( $db->updateSubBearToken( $user['id'], password_hash($bearToken, PASSWORD_DEFAULT)) ) {
                    $response['success'] = true;
                    $user['bearer_token'] = $bearToken;
                    $response['user'] = $user;
                }
            } else {
                $response['message'] = 'Contraseña incorrecta.';
            }
        } else {
            $response['message'] = 'Usuario no registrado o sin verificar.';
        }
    }

    // REGISTRO DE CUENTA

    elseif ( isset($_POST['signup']) ) {

        if ( 
            !isset($_POST['username']) || 
            !isset($_POST['password']) ||
            !isset($_POST['email']) ||
            !isset($_POST['nombre']) ||
            !isset($_POST['apellidos'])
        ) {
            return;
        }

        $errors = array();

        $username = strtolower($_POST['username']);
        $password = $_POST['password'];
        $email = strtolower($_POST['email']);
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $codigoCentro = $_POST['codigo-centro'] ?? null;

        if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $errors['email'] = 'Formato de email incorrecto.';
            return;
        } 

        $user = $db->getUserByUsernameOrEmail($username, $email);

        if ($user) {
            if (strcasecmp($user['username'], $username) == 0) {
                $response['message'] = 'Ya existe un usuario registrado con ese nombre de usuario.';
            }
            if (strcasecmp($user['email'], $email) == 0) {
                $response['message'] = 'Ya existe un usuario registrado con ese correo electrónico.';
            }
        } elseif ( $db->insertSubscriber($username, password_hash($password, PASSWORD_DEFAULT), 
            $email, $nombre, $apellidos, $codigoCentro) 
        ) {
            $response['success'] = true;
        } else {
            $response['message'] = 'No ha sido posible crear el usuario. Inténtalo de nuevo más tarde.';
        }
    }

    // SOLICITUD DE CÓDIGO DE VERIFICACIÓN

    elseif ( isset($_POST['check-email']) ) {

        if ( !isset($_POST['email']) ) {
            return;
        }

        $email = $_POST['email'];

        if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $errors['email'] = 'Formato de email incorrecto.';
            return;
        } 

        $user = $db->getSubByUsernameOrEmail($email);

        if ( !empty($user) ) {
            $code = $util->generateCode(Constants::CODE_NUMERIC, Constants::CODE_LENGTH);

            if ( $db->updateCode($user['id'], $code) ) {
                $emailObject = new Email(
                    $email, 
                    'Código de validación | ' . gmdate('Y-m-d H:i:s \U\T\C'),
                    <<<HTML
                    <div>
                        <p style="padding-bottom: 15px">Estimado usuario:<p>
                        <p style="padding-bottom: 15px">Tu código de recuperación de contraseña es <strong>$code</strong>. 
                        ¡Recuerda que es una clave de uso único!</p>
                        <p style="padding-bottom: 15px">Si no has solicitado un cambio de contraseña, 
                        por favor ignora el contenido de este correo.</p>
                        <p>Atentamente,</p>
                        <p>
                            <address>Radio Educativa de la Consejería de Educación y Empleo de la Junta de Extremadura</address>
                        </p>
                    </div>
                    HTML
                );
    
                $result = sendEmail($emailObject);
    
                if ($result === true) {
                    $response['success'] = true;
                } else {
                    $response['message'] = 'Error al enviar código. Inténtelo de nuevo más tarde.';
                }
            } else {
                $response['message'] = 'Error de conexión con la base de datos. Inténtelo de nuevo más tarde.';
            }
        } else {
            $response['message'] = 'Cuenta de correo no registrada o sin activar.';
        }

    }
    
    // COMPROBACIÓN DE CÓDIGO DE VERIFICACIÓN

    elseif ( isset ($_POST['check-code']) ) {

        if ( !isset($_POST['code']) ) {
            return;
        }

        $user = $db->getSubByUsernameOrEmail( $_POST['email'] );
        $db->nullifyCode($user['id']);
        
        if ( password_verify($code, $user['reset_code']) ) {
            $response['success'] = true;
        }

    }

    // MODIFICACIÓN DE CONTRASEÑA TRAS RECIBIR CÓDIGO

    elseif ( isset($_POST['change-password']) ) {

        if ( !isset($_POST['new-password']) || !isset($_POST['confirm-password']) || !isset($_POST['email']) ) {
            return;
        }

        $errors = array();

        $password = $_POST['new-password'];
        $cPassword = $_POST['confirm-password'];
        $email = $_POST['email'];
    
        if ( strlen($password) < Constants::PASSW_LENGTH_SHORT ) {
            $errors['length'] = 'La contraseña tiene una longitud inferior a ' . Constants::PASSW_LENGTH_SHORT . ' caracteres.';
        }
    
        if ($password !== $cPassword) {
            $errors['match'] = 'Las contraseñas no coinciden.';
        }
    
        if ( empty($errors) ) {
            $user = $db->getSubByUsernameOrEmail($email);
            $response['success'] = $db->updatePassword($user['id'], $password);
        }

    }

    // ALMACENAMIENTO DE FIREBASE TOKEN

    elseif ( isset($_POST['store-fb-token']) ) {

        if ( !isset($_POST['id-sub']) || !isset($_POST['fb-token']) ) {
            return;
        }

        $idSub = $_POST['id-sub'];
        $fbToken = $_POST['fb-token'];

        $response['success'] = $db->updateSubFbToken($idSub, $fbToken);

    }

    print json_encode($response, JSON_PRETTY_PRINT);
    
}