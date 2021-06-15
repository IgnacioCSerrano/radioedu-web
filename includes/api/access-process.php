<?php

$util = new Util();
$db = DatabaseConnect::getInstance();

$handle = '';
$email = '';
$errors = array();

// INICIO DE SESIÓN

if ( isset($_POST['login']) ) {

    if ( !isset($_POST['handle']) || !isset($_POST['password']) ) {
        $errors['form'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $handle = $_POST['handle'];
    $password = $_POST['password'];

    $user = $db->getAdminByUsernameOrEmail($handle);

    if ($user) {

        if ( password_verify( $password, $user['password'] ) ) {
            $_SESSION['user'] = $user;
            $_SESSION['logged'] = true;

            // Establece cookies de autenticación si casilla 'Recuérdame' está activa
            if ( isset( $_POST['remember'] ) ) {

                // Cookie conserva validez durante 1 mes
                $cookie_expiration_time = time() + Constants::COOKIE_EXP_TIME;
                setcookie('username', $user['username'], $cookie_expiration_time);
                $token = $util->generateCode(Constants::CODE_ALPHANUMERIC, Constants::TOKEN_LENTH_SHORT);
                setcookie('token', $token, $cookie_expiration_time);
                $expiry_date = date('Y-m-d H:i:s', $cookie_expiration_time);

                // Inhabilita token actual
                $userToken = $db->getTokenByUsername( $user['username'] );
                if ($userToken) {
                    $db->markTokenAsExpired($userToken['id']);
                }
                // Inserta nuevo token en base de datos
                $db->insertToken(password_hash($token, PASSWORD_DEFAULT), $expiry_date, $user['id']);

            } else {

                $util->clearAuthCookie();

            }

            $util->redirect(ROOT_FOLDER);
        } else {

            $errors['password'] = 'Contraseña incorrecta.';

        }
        
    } else {

        $errors['user'] = 'Usuario no registrado.';

    }

}

// SOLICITUD DE CÓDIGO DE VERIFICACIÓN

elseif ( isset($_POST['check-email']) ) {

    if ( !isset($_POST['email']) ) {
        $errors['form'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $email = $_POST['email'];

    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        $errors['email'] = 'Formato de email incorrecto.';
        return;
    } 

    $user = $db->getAdminByUsernameOrEmail($email);

    if ( !empty($user) ) {
        
        $code = $util->generateCode(Constants::CODE_NUMERIC, Constants::CODE_LENGTH);

        if ( $db->updateCode($user['id'], $code) ) {
            $emailObject = new Email(
                $email, 
                'Código de validación | ' . gmdate('Y-m-d H:i:s \U\T\C'),
                <<<HTML
                <div>
                    <p style="padding-bottom: 15px">Estimado usuario:<p>
                    <p style="padding-bottom: 15px">Su código de recuperación de contraseña es <strong>$code</strong>. Recuerde que es una clave de uso único.</p>
                    <p style="padding-bottom: 15px">Si no ha solicitado un cambio de contraseña puede ignorar el contenido de este correo.</p>
                    <p>Atentamente,</p>
                    <address>
                        Radio Educativa de la Consejería de Educación y Empleo de la Junta de Extremadura
                    </address>
                </div>
                HTML
            );

            $result = sendEmail($emailObject);

            if ($result === true) {
                $_SESSION['email'] = $email;
                $util->redirect(Constants::PAGE_CODE_VAL);
            } else {
                $util->console_log($result);
                $errors['code'] = 'Error al enviar código. Inténtelo de nuevo más tarde.';
            }
        } else {
            $errors['db'] = 'Error de conexión con la base de datos.';
        }
    } else {
        $errors['email'] = 'Cuenta de correo no registrada.';
    }

}

// COMPROBACIÓN DE CÓDIGO DE VERIFICACIÓN

elseif ( isset ($_POST['check-code']) ) {

    if ( !isset($_POST['code']) ) {
        $_SESSION['error-code'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $code = $_POST['code'];

    $user = $db->getAdminByUsernameOrEmail( $_SESSION['email'] );
    $db->nullifyCode($user['id']);
    
    if ( password_verify($code, $user['reset_code']) ) {
        $_SESSION['success-code'] = true;
        $util->redirect(Constants::PAGE_NEW_PASSW);
    } else {
        unset($_SESSION['email']);
        $_SESSION['error-message'] = 'Código incorrecto. Vuelva a solicitar uno nuevo.';
    }

}

// MODIFICACIÓN DE CONTRASEÑA TRAS RECIBIR CÓDIGO

elseif ( isset($_POST['change-password']) ) {

    if ( !isset($_POST['password']) || !isset($_POST['confirm-password']) ) {
        $errors['form'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $password = $_POST['password'];
    $confPassword = $_POST['confirm-password'];

    $errors = array();

    if (
        !preg_match('@[A-Z]@', $password) || 
        !preg_match('@[a-z]@', $password) || 
        !preg_match('@[0-9]@', $password) || 
        !preg_match('@[^\w]@', $password) || 
        strlen($password) < 8 
    ) {
        $errors['message'] = 'Contraseña no cumple con el formato exigido.';
    } elseif ( $password !== $confPassword ) {
        $errors['confirm'] = 'Las contraseñas no coinciden.';
    }

    if ( empty($errors) ) {
        $user = $db->getAdminByUsernameOrEmail($_SESSION['email']);
        unset($_SESSION['email']);
        if ( $db->updatePassword($user['id'], $password) ) {
            $_SESSION['success-message'] = 'Contraseña modificada correctamente. Puede usarla ahora para iniciar sesión.';
            unset($_SESSION['success-code']);
            $util->redirect(Constants::PAGE_LOGIN);
        } else {
            $errors['db'] = 'No ha sido posible cambiar la contraseña.';
        }
    }
    
}
