<?php
require_once Constants::INC_EMAIL_CLASS;
require_once Constants::INC_PHPMAILER;
require_once Constants::INC_PHPMAILER_SMTP;
require_once Constants::INC_PHPMAILER_EXC;

use PHPMailer\PHPMailer\PHPMailer;

function sendEmail($email) 
{
    $phpMailer = new PHPMailer();

    // Propiedades de conexión SMTP

    $phpMailer->isSMTP();
    $phpMailer->Host = 'smtp.gmail.com';
    $phpMailer->SMTPAuth = true;
    $phpMailer->Username = Constants::SENDER_EMAIL_ADDRESS;
    $phpMailer->Password = Constants::SENDER_EMAIL_PASSWORD;
    $phpMailer->Port = '587';
    $phpMailer->SMTPSecure = 'tls';

    /* 
        Registro detallado de conexión SMTP: muestra por pantalla las transcripciones 
        a nivel cliente y servidor (activar solo en operaciones de depuración).
        (http://netcorecloud.com/tutorials/phpmailer-smtp-error-could-not-connect-to-smtp-host/)
    */
    // $phpMailer->SMTPDebug = 2;

    // Propiedades de email
    
    $phpMailer->CharSet = 'UTF-8';
    $phpMailer->isHTML(true);
    $phpMailer->setFrom(Constants::SENDER_EMAIL_ADDRESS, Constants::SENDER_NAME);
    $phpMailer->addAddress($email->getRecipientAddress());
    $phpMailer->Subject = $email->getSubject();
    $phpMailer->Body = $email->getBody();

    if ($phpMailer->send()) {
        return true;
    } else {
        return $phpMailer->ErrorInfo;
    }
}