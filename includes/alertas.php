<?php

declare(strict_types=1);

/**
 * Guarda una alerta temporal en la sesión.
 *
 * Tipos permitidos:
 * - success
 * - danger
 * - warning
 * - info
 */
function guardarAlerta(
    string $tipo,
    string $titulo,
    string $texto
): void {
    $tiposPermitidos = [
        "success",
        "danger",
        "warning",
        "info"
    ];

    $tipo = strtolower(trim($tipo));

    if (!in_array($tipo, $tiposPermitidos, true)) {
        $tipo = "info";
    }

    $_SESSION["alerta"] = [
        "tipo" => $tipo,
        "titulo" => trim($titulo),
        "texto" => trim($texto)
    ];
}

/**
 * Recupera la alerta guardada y la elimina de la sesión.
 *
 * De esta forma, cada alerta se muestra una sola vez.
 */
function obtenerAlerta(): ?array
{
    if (
        !isset($_SESSION["alerta"]) ||
        !is_array($_SESSION["alerta"])
    ) {
        return null;
    }

    $alerta = $_SESSION["alerta"];

    unset($_SESSION["alerta"]);

    return $alerta;
}

/**
 * Convierte los tipos internos de RONEM
 * al nombre utilizado por SweetAlert2.
 */
function convertirTipoSweetAlert(string $tipo): string
{
    switch ($tipo) {
        case "success":
            return "success";

        case "danger":
            return "error";

        case "warning":
            return "warning";

        default:
            return "info";
    }
}

/**
 * Muestra una alerta utilizando SweetAlert2.
 *
 * Debe llamarse después de cargar la librería
 * de SweetAlert2 en la página.
 */
function mostrarAlerta(): void
{
    $alerta = obtenerAlerta();

    if ($alerta === null) {
        return;
    }

    $tipo = convertirTipoSweetAlert(
        (string) ($alerta["tipo"] ?? "info")
    );

    $titulo = json_encode(
        (string) ($alerta["titulo"] ?? ""),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    );

    $texto = json_encode(
        (string) ($alerta["texto"] ?? ""),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    );

    $icono = json_encode($tipo);

    echo "
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') {
                    console.error('SweetAlert2 no está cargado.');
                    return;
                }

                Swal.fire({
                    icon: {$icono},
                    title: {$titulo},
                    text: {$texto},
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    ";
}