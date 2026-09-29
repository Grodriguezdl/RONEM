<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/mailer.php";


/**
 * Envía el código de verificación de correo.
 */
function enviarCodigoVerificacion(
    string $correo,
    string $nombre,
    string $codigo
): bool {
    try {
        $mail = crearMailer();

        $mail->addAddress(
            $correo,
            $nombre
        );

        $mail->Subject =
            "Código de verificación de RONEM";

        $nombreSeguro = htmlspecialchars(
            $nombre,
            ENT_QUOTES,
            "UTF-8"
        );

        $codigoSeguro = htmlspecialchars(
            $codigo,
            ENT_QUOTES,
            "UTF-8"
        );

        $mail->Body = "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
            </head>

            <body style='
                margin: 0;
                padding: 30px;
                background: #f4f4f4;
                font-family: Arial, sans-serif;
                color: #222;
            '>

                <div style='
                    max-width: 560px;
                    margin: auto;
                    background: #ffffff;
                    padding: 35px;
                    border-radius: 14px;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
                '>

                    <h1 style='
                        margin-top: 0;
                        text-align: center;
                    '>
                        RONEM
                    </h1>

                    <p>
                        Hola, <strong>{$nombreSeguro}</strong>.
                    </p>

                    <p>
                        Utiliza el siguiente código para verificar
                        tu correo electrónico:
                    </p>

                    <div style='
                        margin: 28px 0;
                        padding: 18px;
                        text-align: center;
                        background: #f2f2f2;
                        border-radius: 10px;
                        font-size: 34px;
                        font-weight: bold;
                        letter-spacing: 10px;
                    '>
                        {$codigoSeguro}
                    </div>

                    <p>
                        Este código expirará en
                        <strong>5 minutos</strong>.
                    </p>

                    <p>
                        Si no solicitaste crear esta cuenta,
                        puedes ignorar este mensaje.
                    </p>

                    <hr style='
                        border: none;
                        border-top: 1px solid #dddddd;
                        margin: 28px 0;
                    '>

                    <p style='
                        margin-bottom: 0;
                        font-size: 13px;
                        color: #777777;
                        text-align: center;
                    '>
                        RONEM — Mensaje automático
                    </p>

                </div>

            </body>
            </html>
        ";

        $mail->AltBody =
            "Hola {$nombre}. " .
            "Tu código de verificación de RONEM es: {$codigo}. " .
            "El código expirará en 5 minutos.";

        $mail->send();

        return true;

    } catch (Throwable $error) {
        error_log(
            "Error enviando código de verificación: " .
            $error->getMessage()
        );

        return false;
    }
}