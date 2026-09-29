<?php

declare(strict_types=1);

/**
 * Indica si existe una sesión de usuario válida.
 */
function estaAutenticado(): bool
{
    return isset(
        $_SESSION["autenticado"],
        $_SESSION["id_usuario"],
        $_SESSION["roles"]
    )
    && $_SESSION["autenticado"] === true
    && is_array($_SESSION["roles"]);
}

/**
 * Comprueba si el usuario tiene un rol específico.
 */
function tieneRol(string $rol): bool
{
    if (!estaAutenticado()) {
        return false;
    }

    /*
     * El Super Administrador siempre tiene acceso.
     */
    if (esSuperAdministrador()) {
        return true;
    }

    $rol = strtolower(trim($rol));

    return in_array(
        $rol,
        $_SESSION["roles"],
        true
    );
}

/**
 * Comprueba si el usuario posee por lo menos
 * uno de los roles indicados.
 */
function tieneAlgunRol(array $rolesPermitidos): bool
{
    if (!estaAutenticado()) {
        return false;
    }

    /*
     * El Super Administrador siempre tiene acceso.
     */
    if (esSuperAdministrador()) {
        return true;
    }

    foreach ($rolesPermitidos as $rol) {
        if (tieneRol((string) $rol)) {
            return true;
        }
    }

    return false;
}

/**
 * Obliga al visitante a iniciar sesión.
 */
function requiereAutenticacion(
    string $rutaLogin = "../../index.php"
): void {
    if (estaAutenticado()) {
        return;
    }

    guardarAlerta(
        "warning",
        "Acceso restringido",
        "Debes iniciar sesión para acceder a esta sección."
    );

    redirigir($rutaLogin);
}

/**
 * Protege una página para un rol específico.
 */
function requiereRol(
    string $rol,
    string $rutaDenegada = "../../index.php"
): void {
    requiereAutenticacion($rutaDenegada);

    if (tieneRol($rol)) {
        return;
    }

    guardarAlerta(
        "danger",
        "Acceso denegado",
        "No tienes permisos para acceder a esta sección."
    );

    redirigir($rutaDenegada);
}

/**
 * Protege una página para varios roles posibles.
 */
function requiereAlgunRol(
    array $rolesPermitidos,
    string $rutaDenegada = "../../index.php"
): void {
    requiereAutenticacion($rutaDenegada);

    if (tieneAlgunRol($rolesPermitidos)) {
        return;
    }

    guardarAlerta(
        "danger",
        "Acceso denegado",
        "No tienes permisos para acceder a esta sección."
    );

    redirigir($rutaDenegada);
}

/**
 * Devuelve la cantidad de roles del usuario.
 */
function cantidadRolesUsuario(): int
{
    if (!estaAutenticado()) {
        return 0;
    }

    return count($_SESSION["roles"]);
}

/**
 * Indica si el usuario tiene más de un rol.
 */
function tieneMultiplesRoles(): bool
{
    return cantidadRolesUsuario() > 1;
}