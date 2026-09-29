<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/../vendor/autoload.php";


/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN SMTP
|--------------------------------------------------------------------------
|
| Coloca aquí tus datos actuales de Brevo.
| No publiques estas credenciales ni las subas a GitHub.
|
*/

const SMTP_HOST = "smtp-relay.brevo.com";
const SMTP_PORT = 587;

const SMTP_USUARIO = "TU_USUARIO_SMTP";
const SMTP_CLAVE = "TU_CLAVE_SMTP";

const CORREO_REMITENTE = "correo@tudominio.com";
const NOMBRE_REMITENTE = "RONEM";


/**
 * Crea y configura una instancia de PHPMailer.
 *
 * @throws Exception
 */
function crearMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();

    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;

    $mail->SMTPAuth = true;

    $mail->Username = SMTP_USUARIO;
    $mail->Password = SMTP_CLAVE;

    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;

    $mail->CharSet = "UTF-8";

    $mail->setFrom(
        CORREO_REMITENTE,
        NOMBRE_REMITENTE
    );

    $mail->isHTML(true);

    return $mail;
}