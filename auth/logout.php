<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/alertas.php";
require_once __DIR__ . "/../includes/auth.php";

/*
|--------------------------------------------------------------------------
| CERRAR SESIÓN
|--------------------------------------------------------------------------
*/

destruirSesionUsuario();

/*
|--------------------------------------------------------------------------
| CREAR UNA NUEVA SESIÓN PARA LA ALERTA
|--------------------------------------------------------------------------
|
| destruirSesionUsuario() elimina por completo la sesión anterior.
| Para poder guardar el mensaje de cierre de sesión, iniciamos una nueva.
|
*/

session_start();

guardarAlerta(
    "success",
    "Sesión cerrada",
    "Has cerrado sesión correctamente."
);

redirigir("../index.php");