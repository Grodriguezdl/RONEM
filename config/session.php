<?php

declare(strict_types=1);

/*
 * Este archivo inicia la sesión de forma segura.
 * Debe incluirse antes de enviar cualquier HTML al navegador.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {

    /*
     * Evita que JavaScript pueda leer la cookie de sesión.
     */
    ini_set("session.cookie_httponly", "1");

    /*
     * Reduce el riesgo de que la cookie sea enviada
     * desde solicitudes originadas en otros sitios.
     */
    ini_set("session.cookie_samesite", "Lax");

    /*
     * Hostinger usa HTTPS en el dominio.
     * La cookie solo debe enviarse mediante HTTPS.
     */
    if (
        isset($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    ) {
        ini_set("session.cookie_secure", "1");
    }

    /*
     * Evita aceptar identificadores de sesión
     * que no hayan sido creados por PHP.
     */
    ini_set("session.use_strict_mode", "1");

    session_start();
}