<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| HELPERS GENERALES
|--------------------------------------------------------------------------
|
| Este archivo contiene únicamente funciones reutilizables
| que no pertenecen a ningún módulo específico.
|
*/

/**
 * Redirige al usuario.
 */
function redirigir(string $ruta): void
{
    header("Location: " . $ruta);
    exit;
}

/**
 * Elimina espacios innecesarios.
 */
function limpiarTexto(string $valor): string
{
    return trim($valor);
}

/**
 * Convierte un correo en minúsculas.
 */
function normalizarCorreo(string $correo): string
{
    return strtolower(trim($correo));
}

/**
 * Guarda temporalmente datos del formulario.
 * Nunca guarda contraseñas.
 */
function guardarDatosFormulario(array $datos): void
{
    unset(
        $datos["password"],
        $datos["contrasena"]
    );

    $_SESSION["datos_formulario"] = $datos;
}

/**
 * Recupera los datos guardados.
 */
function obtenerDatosFormulario(): array
{
    if (
        !isset($_SESSION["datos_formulario"]) ||
        !is_array($_SESSION["datos_formulario"])
    ) {
        return [];
    }

    $datos = $_SESSION["datos_formulario"];

    unset($_SESSION["datos_formulario"]);

    return $datos;
}