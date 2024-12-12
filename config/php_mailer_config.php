<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
function enviarCorreo($destinatario, $asunto, $cuerpo)
{
    $email = new PHPMailer(true);
    try {
        $email->isSMTP();
        $email->Host = 'smtp.gmail.com';
        $email->SMTPAuth = true;
        $email->Username = 'nicolastorrico0@gmail.com';
        $email->Password = 'jkze gbkv hucs ntao';
        $email->SMTPSecure = 'tls';
        $email->Port = 587;
        $email->setFrom('nicolastorrico0@gmail.com', "Proyecto Emprendedor");
        $email->addAddress($destinatario);
        $email->isHTML(true);
        $email->Subject = $asunto;
        $email->Body = $cuerpo;
        $email->Send();
    } catch (Exception $e) {
        throw new Exception("Error al enviar el email por favor intente mas tarde:" . $e->getMessage());
    }
}
