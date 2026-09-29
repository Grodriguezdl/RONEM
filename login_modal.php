<?php
/*
 * Datos temporales y protección del formulario de inicio de sesión.
 * config/session.php debe cargarse en index.php antes de incluir este archivo.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (
    !isset($_SESSION["csrf_login"]) ||
    !is_string($_SESSION["csrf_login"]) ||
    strlen($_SESSION["csrf_login"]) !== 64
) {
    $_SESSION["csrf_login"] = bin2hex(random_bytes(32));
}

$csrfLogin = $_SESSION["csrf_login"];
$correoLoginAnterior = "";

if (isset($_SESSION["login_correo"])) {
    $correoLoginAnterior = substr(
        trim((string) $_SESSION["login_correo"]),
        0,
        150
    );
    unset($_SESSION["login_correo"]);
}
?>

<!-- ═══════════════════════════════════════
     MODAL LOGIN / REGISTRO
════════════════════════════════════════ -->

<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <!-- Usamos modal-xl para que tenga buen ancho en pantallas grandes -->
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content modal-ronem">
            
            <!-- El ID "container" es vital para el script -->
            <div class="container-modal" id="container">
                
                <!-- BOTON CERRAR -->
                <button 
                    type="button" 
                    class="btn-close btn-close-white custom-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

                <!-- REGISTRO -->
                <div class="form-container sign-up">
                    <form action="auth/registro.php" method="POST">
                        <h1>Crear Cuenta</h1>
                        <div class="social-icons">
                            <a href="auth/google/google_login.php"><i class="fa-brands fa-google"></i></a>
                        </div>
                        <span>o usa tu correo para registrarte</span>
                
                        <input type="text"     name="nombre"   placeholder="Nombre"             required>
                        <input type="text"     name="apellido" placeholder="Apellido"           required>
                        <input type="email"    name="correo"   placeholder="Correo electrónico" maxlength="150" autocomplete="email" autocapitalize="none" spellcheck="false" required>
                        <input type="password" name="password" placeholder="Contraseña" minlength="8" maxlength="72" autocomplete="new-password" required>
                
                        <button type="submit">Registrarse</button>
                    </form>
                </div>

                <!-- LOGIN -->
                <div class="form-container sign-in">
                    <form
                        action="auth/login.php"
                        method="POST"
                        id="formInicioSesion"
                        autocomplete="on"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($csrfLogin, ENT_QUOTES, "UTF-8") ?>"
                        >
                        <h1>Iniciar Sesión</h1>
                        <div class="social-icons">
                            <a href="auth/google/google_login.php"><i class="fa-brands fa-google"></i></a>
                        </div>
                        <span>o usa tu correo y contraseña</span>
                        
                        <input
                            type="email"
                            id="correoLogin"
                            name="correo"
                            placeholder="Correo electrónico"
                            value="<?= htmlspecialchars($correoLoginAnterior, ENT_QUOTES, "UTF-8") ?>"
                            maxlength="150"
                            inputmode="email"
                            autocomplete="email"
                            autocapitalize="none"
                            spellcheck="false"
                            aria-label="Correo electrónico"
                            required
                        >
                        <input
                            type="password"
                            id="passwordLogin"
                            name="password"
                            placeholder="Contraseña"
                            minlength="8"
                            maxlength="72"
                            autocomplete="current-password"
                            aria-label="Contraseña"
                            required
                        >
                        
                        <a href="#" class="forgot-pass">¿Olvidaste tu contraseña?</a>
                        <button type="submit" id="btnIniciarSesion">
                            Ingresar
                        </button>
                    </form>
                </div>

                <!-- PANEL ANIMADO  -->
                <div class="toggle-container">
                    <div class="toggle">
                        <!-- PANEL IZQUIERDO -->
                        <div class="toggle-panel toggle-left">
                            <img src="img/logo.png" class="logo" alt="RONEM">
                            <h1>¡Bienvenido!</h1>
                            <p>Inicia sesión para acceder al sistema</p>
                            <button type="button" class="hidden" id="login">Iniciar Sesión</button>
                        </div>
                        
                        <!-- PANEL DERECHO -->
                        <div class="toggle-panel toggle-right">
                            <img src="img/logo.png" class="logo" alt="RONEM">
                            <h1>¿No tienes una cuenta?</h1>
                            <p>Regístrate para comenzar</p>
                            <button type="button" class="hidden" id="register">Registrarse</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php

$correoVerificacion = "";

if (
    isset($_SESSION["verificacion_pendiente"]) &&
    is_array($_SESSION["verificacion_pendiente"]) &&
    isset($_SESSION["verificacion_pendiente"]["correo"])
) {
    $correoVerificacion = trim(
        (string) $_SESSION["verificacion_pendiente"]["correo"]
    );
}


/**
 * Oculta parcialmente un correo electrónico.
 *
 * Ejemplo:
 * cristel@gmail.com → cr*****@gmail.com
 */
if (!function_exists("ocultarCorreoVerificacion")) {

    function ocultarCorreoVerificacion(
        string $correo
    ): string {
    if (
        $correo === "" ||
        strpos($correo, "@") === false
    ) {
        return "";
    }

    [$usuarioCorreo, $dominioCorreo] =
        explode("@", $correo, 2);

    if (
        $usuarioCorreo === "" ||
        $dominioCorreo === ""
    ) {
        return "";
    }

    $cantidadVisible = min(
        2,
        strlen($usuarioCorreo)
    );

    $parteVisible = substr(
        $usuarioCorreo,
        0,
        $cantidadVisible
    );

    $cantidadOculta = max(
        3,
        strlen($usuarioCorreo) -
        $cantidadVisible
    );

    return
        $parteVisible .
        str_repeat("*", $cantidadOculta) .
        "@" .
        $dominioCorreo;
}
}

$correoOculto =
    ocultarCorreoVerificacion(
        $correoVerificacion
    );
?>

<!-- ═══════════════════════════════════════
     MODAL DE VERIFICACIÓN DE CORREO
════════════════════════════════════════ -->

<div
    class="modal fade"
    id="modalVerificacionCorreo"
    tabindex="-1"
    aria-labelledby="tituloVerificacionCorreo"
    aria-hidden="true"
    aria-modal="true"
    role="dialog"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
>
    <div
        class="
            modal-dialog
            modal-dialog-centered
            modal-verificacion-dialog
        "
    >
        <div class="modal-content modal-verificacion-ronem">

            <div class="verificacion-cabecera">

                <div class="verificacion-icono">
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </div>

                <span class="verificacion-marca">
                    RONEM
                </span>

                <h2 id="tituloVerificacionCorreo">
                    Verifica tu correo
                </h2>

                <p>
                    Hemos enviado un código de seis dígitos a:
                </p>

                <?php if ($correoOculto !== ""): ?>

                    <strong class="correo-verificacion">
                        <?= htmlspecialchars(
                            $correoOculto,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </strong>

                <?php endif; ?>

            </div>


            <form
                action="auth/verificar.php"
                method="POST"
                id="formVerificacionCorreo"
                class="form-verificacion-correo"
                autocomplete="off"
            >

                <!--
                    Este campo será completado por JavaScript
                    uniendo las seis casillas.
                -->
                <input
                    type="hidden"
                    name="codigo"
                    id="codigoVerificacionCompleto"
                >


                <div
                    class="codigo-verificacion"
                    id="contenedorCodigoVerificacion"
                    aria-label="Código de verificación"
                >

                    <input
                        type="text"
                        class="codigo-casilla"
                        inputmode="numeric"
                        pattern="[0-9]"
                        maxlength="1"
                        placeholder=" "
                        autocomplete="one-time-code"
                        aria-label="Primer dígito"
                    >

                    <input
                        type="text"
                        class="codigo-casilla"
                        inputmode="numeric"
                        pattern="[0-9]"
                        maxlength="1"
                        placeholder=" "
                        aria-label="Segundo dígito"
                    >

                    <input
                        type="text"
                        class="codigo-casilla"
                        inputmode="numeric"
                        pattern="[0-9]"
                        maxlength="1"
                        placeholder=" "
                        aria-label="Tercer dígito"
                    >

                    <input
                        type="text"
                        class="codigo-casilla"
                        inputmode="numeric"
                        pattern="[0-9]"
                        maxlength="1"
                        placeholder=" "
                        aria-label="Cuarto dígito"
                    >

                    <input
                        type="text"
                        class="codigo-casilla"
                        inputmode="numeric"
                        pattern="[0-9]"
                        maxlength="1"
                        placeholder=" "
                        aria-label="Quinto dígito"
                    >

                    <input
                        type="text"
                        class="codigo-casilla"
                        inputmode="numeric"
                        pattern="[0-9]"
                        maxlength="1"
                        placeholder=" "
                        aria-label="Sexto dígito"
                    >

                </div>


                <p
                    class="mensaje-codigo"
                    id="mensajeCodigoVerificacion"
                    aria-live="polite"
                >
                    El código expirará en 5 minutos.
                </p>


                <button
                    type="submit"
                    class="btn-verificar-correo"
                    id="btnVerificarCorreo"
                    disabled
                >
                    <i class="fa-solid fa-shield-halved"></i>
                    Verificar correo
                </button>


                <div class="reenvio-codigo">

                    <span>
                        ¿No recibiste el código?
                    </span>

                    <button
                        type="button"
                        id="btnReenviarCodigo"
                        class="btn-reenviar-codigo"
                        disabled
                    >
                        Reenviar en 60 s
                    </button>

                </div>


                <a
                    href="index.php"
                    class="enlace-volver-verificacion"
                >
                    Verificar más tarde
                </a>

            </form>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     SCRIPT PARA CAMBIAR ENTRE LOGIN/REGISTRO
════════════════════════════════════════ -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | CAMBIO ENTRE LOGIN Y REGISTRO
    |--------------------------------------------------------------------------
    */

    const container =
        document.getElementById("container");

    const registerBtn =
        document.getElementById("register");

    const loginBtn =
        document.getElementById("login");


    if (registerBtn && container) {
        registerBtn.addEventListener(
            "click",
            function () {
                container.classList.add("active");
            }
        );
    }


    if (loginBtn && container) {
        loginBtn.addEventListener(
            "click",
            function () {
                container.classList.remove("active");
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EVITAR ENVÍOS DOBLES DEL LOGIN
    |--------------------------------------------------------------------------
    */

    const formularioLogin =
        document.getElementById("formInicioSesion");

    const btnIniciarSesion =
        document.getElementById("btnIniciarSesion");

    if (formularioLogin && btnIniciarSesion) {
        formularioLogin.addEventListener(
            "submit",
            function (evento) {
                if (!formularioLogin.checkValidity()) {
                    evento.preventDefault();
                    formularioLogin.reportValidity();
                    return;
                }

                btnIniciarSesion.disabled = true;
                btnIniciarSesion.textContent = "Ingresando...";
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MODAL DE VERIFICACIÓN
    |--------------------------------------------------------------------------
    */

    const modalVerificacionElemento =
        document.getElementById(
            "modalVerificacionCorreo"
        );

    const casillasCodigo =
        Array.from(
            modalVerificacionElemento
                ? modalVerificacionElemento
                    .querySelectorAll(
                        ".codigo-casilla"
                    )
                : []
        );

    const codigoCompleto =
        document.getElementById(
            "codigoVerificacionCompleto"
        );

    const btnVerificar =
        document.getElementById(
            "btnVerificarCorreo"
        );

    const formularioVerificacion =
        document.getElementById(
            "formVerificacionCorreo"
        );

    const btnReenviar =
        document.getElementById(
            "btnReenviarCodigo"
        );


    /**
     * Une los seis números y actualiza el botón.
     */
    function actualizarCodigoCompleto() {

        const codigo = casillasCodigo
            .map(function (input) {
                return input.value;
            })
            .join("");


        if (codigoCompleto) {
            codigoCompleto.value = codigo;
        }


        if (btnVerificar) {
            btnVerificar.disabled =
                !/^\d{6}$/.test(codigo);
        }
    }


    /**
     * Limpia cualquier valor que no sea numérico.
     */
    casillasCodigo.forEach(
        function (casilla, indice) {

            casilla.addEventListener(
                "input",
                function () {

                    casilla.value =
                        casilla.value
                            .replace(/\D/g, "")
                            .slice(0, 1);


                    if (
                        casilla.value !== "" &&
                        indice <
                        casillasCodigo.length - 1
                    ) {
                        casillasCodigo[
                            indice + 1
                        ].focus();
                    }


                    actualizarCodigoCompleto();
                }
            );


            casilla.addEventListener(
                "keydown",
                function (evento) {

                    if (
                        evento.key === "Backspace" &&
                        casilla.value === "" &&
                        indice > 0
                    ) {
                        casillasCodigo[
                            indice - 1
                        ].focus();
                    }


                    if (
                        evento.key === "ArrowLeft" &&
                        indice > 0
                    ) {
                        evento.preventDefault();

                        casillasCodigo[
                            indice - 1
                        ].focus();
                    }


                    if (
                        evento.key === "ArrowRight" &&
                        indice <
                        casillasCodigo.length - 1
                    ) {
                        evento.preventDefault();

                        casillasCodigo[
                            indice + 1
                        ].focus();
                    }
                }
            );


            casilla.addEventListener(
                "focus",
                function () {
                    casilla.select();
                }
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PEGAR LOS SEIS DÍGITOS
    |--------------------------------------------------------------------------
    */

    if (casillasCodigo.length > 0) {

        casillasCodigo[0].addEventListener(
            "paste",
            function (evento) {

                evento.preventDefault();

                const textoPegado =
                    evento.clipboardData
                        .getData("text")
                        .replace(/\D/g, "")
                        .slice(0, 6);


                textoPegado
                    .split("")
                    .forEach(
                        function (numero, indice) {

                            if (casillasCodigo[indice]) {
                                casillasCodigo[
                                    indice
                                ].value = numero;
                            }
                        }
                    );


                actualizarCodigoCompleto();


                const indiceFinal =
                    Math.min(
                        textoPegado.length,
                        casillasCodigo.length
                    ) - 1;


                if (
                    indiceFinal >= 0 &&
                    casillasCodigo[indiceFinal]
                ) {
                    casillasCodigo[
                        indiceFinal
                    ].focus();
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDAR ANTES DE ENVIAR
    |--------------------------------------------------------------------------
    */

    if (formularioVerificacion) {

        formularioVerificacion.addEventListener(
            "submit",
            function (evento) {

                actualizarCodigoCompleto();

                if (
                    !codigoCompleto ||
                    !/^\d{6}$/.test(
                        codigoCompleto.value
                    )
                ) {
                    evento.preventDefault();

                    casillasCodigo[0]?.focus();
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONTADOR DE REENVÍO
    |--------------------------------------------------------------------------
    */

    let segundosReenvio = 60;

    function actualizarContadorReenvio() {

        if (!btnReenviar) {
            return;
        }


        if (segundosReenvio > 0) {

            btnReenviar.disabled = true;

            btnReenviar.textContent =
                "Reenviar en " +
                segundosReenvio +
                " s";

            segundosReenvio--;

            window.setTimeout(
                actualizarContadorReenvio,
                1000
            );

            return;
        }


        btnReenviar.disabled = false;
        btnReenviar.textContent =
            "Reenviar código";
    }


    actualizarContadorReenvio();


    /*
     * Este botón funcionará cuando creemos
     * auth/reenviar_codigo.php.
     */
    if (btnReenviar) {

        btnReenviar.addEventListener(
            "click",
            function () {

                window.location.href =
                    "auth/reenviar_codigo.php";
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ABRIR MODAL AUTOMÁTICAMENTE
    |--------------------------------------------------------------------------
    */

    const parametros =
        new URLSearchParams(
            window.location.search
        );

    const abrirVerificacion =
        parametros.get("verificar") === "1";
        
    const abrirLogin =
        parametros.get("login") === "1";
    
    
    if (
        abrirVerificacion &&
        modalVerificacionElemento &&
        typeof bootstrap !== "undefined"
    ) {
        const modalLoginElemento =
            document.getElementById(
                "loginModal"
            );


        if (modalLoginElemento) {
            const instanciaLogin =
                bootstrap.Modal.getInstance(
                    modalLoginElemento
                );

            if (instanciaLogin) {
                instanciaLogin.hide();
            }
        }


        const modalVerificacion =
            new bootstrap.Modal(
                modalVerificacionElemento
            );

        modalVerificacion.show();


        modalVerificacionElemento
            .addEventListener(
                "shown.bs.modal",
                function () {
                    casillasCodigo[0]?.focus();
                },
                {
                    once: true
                }
            );
    }
    
    /*
|--------------------------------------------------------------------------
| ABRIR LOGIN DESPUÉS DE VERIFICAR
|--------------------------------------------------------------------------
*/

if (
    abrirLogin &&
    container &&
    typeof bootstrap !== "undefined"
) {
    /*
     * Asegura que se muestre el lado
     * de inicio de sesión.
     */
    container.classList.remove("active");

    const modalLoginElemento =
        document.getElementById(
            "loginModal"
        );

    if (modalLoginElemento) {
        const modalLogin =
            bootstrap.Modal.getOrCreateInstance(
                modalLoginElemento
            );

        modalLogin.show();
    }
}

});
</script>