<?php

declare(strict_types=1);
/*
|--------------------------------------------------------------------------
| SUPER ADMINISTRADOR
|--------------------------------------------------------------------------
|
| Esta cuenta posee acceso total al sistema,
| independientemente de los roles asignados
| en la base de datos.
|
*/

const SUPER_ADMIN_EMAIL = "ronemoficial@gmail.com";

/**
 * Obtiene todos los roles asignados a un usuario.
 *
 * Ejemplo de resultado:
 *
 * [
 *     "empleado",
 *     "tecnico"
 * ]
 */
function obtenerRolesUsuario(
    mysqli $conn,
    int $idUsuario
): array {
    $sql = "
        SELECT r.Nombre
        FROM Roles_usuarios AS ru
        INNER JOIN Roles AS r
            ON r.Id_rol = ru.Id_rol
        WHERE ru.Id_usuario = ?
        ORDER BY r.Id_rol ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $idUsuario
    );

    $stmt->execute();

    $stmt->bind_result($nombreRol);

    $roles = [];

    while ($stmt->fetch()) {
        $roles[] = strtolower(
            trim((string) $nombreRol)
        );
    }

    $stmt->close();

    /*
     * Elimina posibles valores repetidos
     * y reorganiza los índices del array.
     */
    return array_values(
        array_unique($roles)
    );
}

/**
 * Crea la sesión oficial del usuario.
 *
 * Todos los métodos de acceso utilizarán esta función:
 *
 * - login manual;
 * - Google;
 * - creación inicial del administrador.
 */
function crearSesionUsuario(
    array $usuario,
    array $roles
): void {
    /*
     * Cambia el identificador de sesión después
     * de autenticar al usuario.
     *
     * Esto reduce el riesgo de fijación de sesión.
     */
    session_regenerate_id(true);

    $_SESSION["id_usuario"] = (int) $usuario["Id_usuario"];
    $_SESSION["nombre"] = (string) $usuario["Nombre"];
    $_SESSION["apellido"] = (string) $usuario["Apellido"];
    $_SESSION["correo"] = (string) $usuario["Correo"];

    $_SESSION["roles"] = array_values(
        array_unique($roles)
    );

    $_SESSION["autenticado"] = true;

    /*
     * Conservamos el instante en que se creó la sesión.
     * Después podrá utilizarse para controlar expiraciones.
     */
    $_SESSION["inicio_sesion"] = time();
}

/**
 * Verifica si el usuario autenticado es el
 * Super Administrador del sistema.
 */
function esSuperAdministrador(): bool
{
    if (!isset($_SESSION["correo"])) {
        return false;
    }

    return normalizarCorreo(
        (string) $_SESSION["correo"]
    ) === normalizarCorreo(
        SUPER_ADMIN_EMAIL
    );
}

/**
 * Elimina completamente la sesión del usuario.
 */
function destruirSesionUsuario(): void
{
    $_SESSION = [];

    /*
     * Elimina también la cookie de sesión,
     * si PHP está utilizando cookies.
     */
    if (ini_get("session.use_cookies")) {
        $parametros = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $parametros["path"],
            $parametros["domain"],
            $parametros["secure"],
            $parametros["httponly"]
        );
    }

    session_destroy();
}

/**
 * Define el rol que el usuario utilizará
 * durante la sesión actual.
 */
function establecerRolActivo(string $rol): void
{
    $_SESSION["rol_activo"] = strtolower(
        trim($rol)
    );
}

/**
 * Redirige al usuario según los roles
 * que tiene asignados.
 *
 * Esta función será utilizada por:
 *
 * - login manual;
 * - inicio de sesión con Google;
 * - futuros métodos de autenticación.
 */
function redirigirSegunRoles(): void
{
    if (!estaAutenticado()) {
        guardarAlerta(
            "danger",
            "Sesión no válida",
            "No fue posible establecer una sesión válida."
        );

        redirigir("/RONEM/index.php");
    }

    $roles = $_SESSION["roles"];

    /*
     * Al iniciar una sesión nueva todavía
     * no debe existir un rol activo anterior.
     */
    unset($_SESSION["rol_activo"]);

    /*
     * El Super Administrador siempre pasa
     * por el selector de acceso.
     */
    if (esSuperAdministrador()) {
        redirigir(
            "/RONEM/seleccionar_acceso.php"
        );
    }

    /*
     * Un usuario con varios roles debe escoger
     * con cuál desea ingresar.
     */
    if (count($roles) > 1) {
        redirigir(
            "/RONEM/seleccionar_acceso.php"
        );
    }

    /*
     * Administrador.
     */
    if (in_array("administrador", $roles, true)) {
        establecerRolActivo("administrador");

        redirigir(
            "/RONEM/panel_admin.php"
        );
    }

    /*
     * Empleado.
     */
    if (in_array("empleado", $roles, true)) {
        establecerRolActivo("empleado");

        redirigir(
            "/RONEM/panel_empleado.php"
        );
    }

    /*
     * Técnico.
     */
    if (in_array("tecnico", $roles, true)) {
        establecerRolActivo("tecnico");

        redirigir(
            "/RONEM/panel_tecnico.php"
        );
    }

    /*
     * Cliente.
     */
    if (in_array("cliente", $roles, true)) {
        establecerRolActivo("cliente");

        redirigir(
            "/RONEM/index.php"
        );
    }

    /*
     * Si llegó hasta aquí, ninguno de sus roles
     * es reconocido por el sistema.
     */
    destruirSesionUsuario();

    session_start();

    guardarAlerta(
        "danger",
        "Rol no reconocido",
        "Tu cuenta no tiene un rol válido asignado."
    );

    redirigir("/RONEM/index.php");
}

/**
 * Completa el inicio de sesión del usuario.
 *
 * Crea la sesión y ejecuta la redirección
 * correspondiente en un único proceso.
 */
function iniciarSesionUsuario(
    array $usuario,
    array $roles
): void {
    if (empty($roles)) {
        guardarAlerta(
            "danger",
            "Sin roles asignados",
            "Tu cuenta no tiene roles configurados."
        );

        redirigir("/RONEM/index.php");
    }

    crearSesionUsuario(
        $usuario,
        $roles
    );

    redirigirSegunRoles();
}