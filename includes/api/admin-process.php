<?php

$util = new Util();
$db = DatabaseConnect::getInstance();

$errors = array();
$success = array();

// CREACIÓN DE ADMIN

if ( isset($_POST['create-admin']) ) {

    if ( !isset($_POST['username']) || !isset($_POST['email']) ) {
        $errors['form'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $username = strtolower($_POST['username']);
    $email = strtolower($_POST['email']);

    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        $errors['email'] = 'Formato de email incorrecto.';
        return;
    } 

    $user = $db->getUserByUsernameOrEmail($username, $email);

    if ($user) {
        if ( strcasecmp($user['username'], $username) == 0 ) {
            $errors['username'] = 'Ya existe un usuario registrado con ese nombre de usuario.';
        }
        if ( strcasecmp($user['email'], $email) == 0 ) {
            $errors['email'] = 'Ya existe un usuario registrado con ese correo electrónico.';
        }
    } else if ( $db->insertAdmin($username, $email) ) {
        $success['message'] = 'Administrador creado correctamente. Se ha enviado la contraseña por correo a la dirección indicada.';
    } else {
        $errors['db'] = 'No ha sido posible crear el administrador.';
    }

}

// ACTUALIZACIÓN DE PERFIL

elseif ( isset($_POST['update-profile-details']) ) {

    if ( !isset($_POST['username']) || !isset($_POST['email']) ) {
        $errors['form'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $idAdmin = $_SESSION['user']['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        $errors['email'] = 'Formato de email incorrecto.';
        return;
    } 

    $imagen = $util->uploadImage(Constants::IMG_USER_PATH, $_FILES['imagen'], $_SESSION['user']['imagen']);

    if ( strcasecmp($_SESSION['user']['username'], $username) != 0 && $db->isUsernameRegistered($username) != 0 ) {
        $errors['username'] = 'Ya existe un usuario registrado con ese nombre de usuario.';
    }

    if ( strcasecmp($_SESSION['user']['email'], $email) != 0 && $db->isEmailRegistered($email) != 0 ) {
        $errors['email'] = 'Ya existe un usuario registrado con ese correo electrónico.';
    }

    if ( empty($errors) ) {
        if ( $db->updateAdminProfileData($idAdmin, $username, $email, $imagen) ) {
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['imagen'] = $imagen;
            $success['password'] = 'Datos modificados correctamente.';
        } else {
            $errors['update'] = 'Error al modificar datos. Inténtelo de nuevo más tarde.';
        }
    }

}

// MODIFICACIÓN DE CONTRASEÑA

elseif ( isset($_POST['update-password']) ) {

    if ( !isset($_POST['current-password']) || !isset($_POST['new-password']) || !isset($_POST['confirm-password']) ) {
        $errors['form'] = 'Error al enviar formulario: faltan datos.';
        return;
    }

    $idAdmin = $_SESSION['user']['id'];
    $curPassword = $_POST['current-password'];
    $newPassword = $_POST['new-password'];
    $confPassword = $_POST['confirm-password'];

    if (
        !preg_match('@[A-Z]@', $newPassword) || 
        !preg_match('@[a-z]@', $newPassword) || 
        !preg_match('@[0-9]@', $newPassword) || 
        !preg_match('@[^\w]@', $newPassword) || 
        strlen($newPassword) < 8 
    ) {
        $errors['message'] = 'Contraseña no cumple con el formato exigido.';
    } 

    if ( !password_verify( $curPassword, $_SESSION['user']['password'] )) {
        $errors['wrong-password'] = 'Contraseña actual incorrecta.';
    }
    
    if ( $newPassword !== $confPassword ) {
        $errors['confirm'] = 'Ambas contraseñas no coinciden.';
    }

    if ( empty($errors) ) {
        if ( $db->updatePassword($idAdmin, $newPassword) ) {
            $_SESSION['user']['password'] = $newPassword;
            $success['password'] = 'Contraseña modificada correctamente.';
        } else {
            $errors['update'] = 'Error al modificar contraseña. Inténtelo de nuevo más tarde.';
        }
    }

}