<?php

declare(strict_types=1);

require_once "../config/conexion.php";
require_once "../fpdf186/fpdf.php";

//==================================================
// VALIDAR ID DE LA ORDEN
//==================================================

$idOrden = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($idOrden <= 0) {
    http_response_code(422);
    exit("El ID de la orden no es válido.");
}

//==================================================
// FUNCIONES AUXILIARES
//==================================================

function textoPDF(mixed $texto): string
{
    $texto = trim((string) ($texto ?? ""));

    if ($texto === "") {
        return "No especificado";
    }

    $convertido = iconv(
        "UTF-8",
        "windows-1252//TRANSLIT",
        $texto
    );

    return $convertido !== false
        ? $convertido
        : $texto;
}

function obtenerCampo(
    array $registro,
    array $posiblesNombres,
    mixed $valorPredeterminado = ""
): mixed {

    foreach ($posiblesNombres as $nombre) {

        if (
            array_key_exists($nombre, $registro) &&
            $registro[$nombre] !== null &&
            $registro[$nombre] !== ""
        ) {
            return $registro[$nombre];
        }
    }

    return $valorPredeterminado;
}

function formatearFechaOrden(mixed $fecha): string
{
    if ($fecha === null || trim((string) $fecha) === "") {
        return "No especificada";
    }

    $timestamp = strtotime((string) $fecha);

    if ($timestamp === false) {
        return (string) $fecha;
    }

    return date(
        "d/m/Y H:i",
        $timestamp
    );
}

//==================================================
// CONSULTAR ORDEN, VEHÍCULO Y PROPIETARIO
//==================================================

$sqlOrden = "
    SELECT
        o.*,

        v.Id_vehiculo AS Vehiculo_Id,
        v.Id_usuario AS Vehiculo_Id_usuario,
        v.Marca AS Vehiculo_Marca,
        v.Linea AS Vehiculo_Linea,
        v.Modelo AS Vehiculo_Modelo,
        v.Color AS Vehiculo_Color,
        v.Placa AS Vehiculo_Placa,
        v.No_chasis AS Vehiculo_No_chasis,
        v.Tipo_vehiculo AS Vehiculo_Tipo,
        v.Cilindraje AS Vehiculo_Cilindraje,
        v.Combustible AS Vehiculo_Combustible,
        v.Kilometraje AS Vehiculo_Kilometraje,
        v.Estado AS Vehiculo_Estado,
        v.Imagen_URL AS Vehiculo_Imagen,

        u.Nombre AS Propietario_Nombre,
        u.Apellido AS Propietario_Apellido,
        u.Correo AS Propietario_Correo,
        u.Telefono AS Propietario_Telefono

    FROM Ordenes o

    LEFT JOIN Vehiculos v
        ON v.Id_vehiculo = o.Id_vehiculo

    LEFT JOIN Usuarios u
        ON u.Id_usuario = v.Id_usuario

    WHERE o.Id_orden = ?

    LIMIT 1
";

$stmtOrden = $conn->prepare($sqlOrden);

if (!$stmtOrden) {
    http_response_code(500);

    exit(
        "No se pudo preparar la consulta de la orden: " .
        $conn->error
    );
}

$stmtOrden->bind_param(
    "i",
    $idOrden
);

if (!$stmtOrden->execute()) {
    http_response_code(500);

    exit(
        "No se pudo consultar la orden: " .
        $stmtOrden->error
    );
}

$resultadoOrden = $stmtOrden->get_result();

if ($resultadoOrden->num_rows === 0) {

    $stmtOrden->close();

    http_response_code(404);

    exit("La orden solicitada no existe.");
}

$orden = $resultadoOrden->fetch_assoc();

$stmtOrden->close();

//==================================================
// CONSULTAR HISTORIAL DE LA ORDEN
//==================================================

$sqlHistorial = "
    SELECT
        h.Id_historial,
        h.Id_orden,
        h.Id_estado,
        h.Comentario,
        h.Fecha,
        h.Id_usuario,

        u.Nombre,
        u.Apellido

    FROM Historial h

    LEFT JOIN Usuarios u
        ON u.Id_usuario = h.Id_usuario

    WHERE h.Id_orden = ?

    ORDER BY
        h.Fecha ASC,
        h.Id_historial ASC
";

$stmtHistorial = $conn->prepare($sqlHistorial);

if (!$stmtHistorial) {
    http_response_code(500);

    exit(
        "No se pudo preparar el historial: " .
        $conn->error
    );
}

$stmtHistorial->bind_param(
    "i",
    $idOrden
);

if (!$stmtHistorial->execute()) {
    http_response_code(500);

    exit(
        "No se pudo consultar el historial: " .
        $stmtHistorial->error
    );
}

$resultadoHistorial =
    $stmtHistorial->get_result();

$historial = [];

while (
    $filaHistorial =
        $resultadoHistorial->fetch_assoc()
) {
    $historial[] = $filaHistorial;
}

$stmtHistorial->close();

//==================================================
// OBTENER CAMPOS DE LA ORDEN
//==================================================

$fallaReportada = obtenerCampo(
    $orden,
    [
        "Falla_reportada",
        "falla_reportada",
        "Falla",
        "Descripcion",
        "Trabajo_solicitado",
        "Motivo"
    ],
    "No especificada"
);

$observaciones = obtenerCampo(
    $orden,
    [
        "Observaciones",
        "Observacion",
        "Comentario",
        "Notas",
        "Diagnostico"
    ],
    "Sin observaciones"
);

$fechaIngreso = obtenerCampo(
    $orden,
    [
        "Fecha_ingreso",
        "Fecha_entrada",
        "Fecha_recepcion",
        "Fecha",
        "Fecha_creacion"
    ]
);

$fechaEntrega = obtenerCampo(
    $orden,
    [
        "Fecha_entrega",
        "Fecha_salida",
        "Fecha_finalizacion",
        "Fecha_estimada"
    ]
);

$idEstado = obtenerCampo(
    $orden,
    [
        "Id_estado",
        "Estado",
        "Id_estado_mantenimiento"
    ],
    "No asignado"
);

$idTecnico = obtenerCampo(
    $orden,
    [
        "Id_tecnico",
        "Id_usuario_tecnico",
        "Id_usuario"
    ],
    "No asignado"
);

$tiempoTrabajado = obtenerCampo(
    $orden,
    [
        "Tiempo_trabajado",
        "Tiempo",
        "Horas_trabajadas",
        "Duracion"
    ],
    "No registrado"
);

$nombrePropietario = trim(
    (
        $orden["Propietario_Nombre"] ??
        ""
    ) .
    " " .
    (
        $orden["Propietario_Apellido"] ??
        ""
    )
);

if ($nombrePropietario === "") {
    $nombrePropietario =
        "No registrado";
}

//==================================================
// CLASE DEL DOCUMENTO PDF
//==================================================

class PDFOrden extends FPDF
{
    public int $numeroOrden = 0;

    public function Header(): void
    {
        $rutaLogo =
            dirname(__DIR__) .
            "/favicoB.png";

        if (is_file($rutaLogo)) {
            $this->Image(
                $rutaLogo,
                15,
                10,
                19,
                19
            );
        }

        $this->SetXY(
            39,
            10
        );

        $this->SetFont(
            "Arial",
            "B",
            18
        );

        $this->Cell(
            0,
            8,
            textoPDF("RONEMMA"),
            0,
            1
        );

        $this->SetX(39);

        $this->SetFont(
            "Arial",
            "",
            9
        );

        $this->Cell(
            0,
            6,
            textoPDF(
                "Reporte de orden de trabajo"
            ),
            0,
            1
        );

        $this->SetDrawColor(
            185,
            20,
            35
        );

        $this->SetLineWidth(0.8);

        $this->Line(
            15,
            32,
            195,
            32
        );

        $this->Ln(9);
    }

    public function Footer(): void
    {
        $this->SetY(-15);

        $this->SetDrawColor(
            210,
            210,
            210
        );

        $this->Line(
            15,
            $this->GetY(),
            195,
            $this->GetY()
        );

        $this->SetY(-12);

        $this->SetFont(
            "Arial",
            "",
            8
        );

        $this->SetTextColor(
            100,
            100,
            100
        );

        $this->Cell(
            90,
            7,
            textoPDF(
                "Generado el " .
                date("d/m/Y H:i")
            ),
            0,
            0,
            "L"
        );

        $this->Cell(
            90,
            7,
            textoPDF(
                "Página " .
                $this->PageNo()
            ),
            0,
            0,
            "R"
        );
    }

    public function tituloSeccion(
        string $titulo
    ): void {

        $this->Ln(3);

        $this->SetFillColor(
            40,
            40,
            40
        );

        $this->SetTextColor(
            255,
            255,
            255
        );

        $this->SetFont(
            "Arial",
            "B",
            11
        );

        $this->Cell(
            0,
            8,
            textoPDF($titulo),
            0,
            1,
            "L",
            true
        );

        $this->SetTextColor(
            0,
            0,
            0
        );

        $this->Ln(2);
    }

    public function filaDato(
        string $etiqueta,
        mixed $valor
    ): void {

        $anchoEtiqueta = 50;
        $anchoValor = 130;

        $x = $this->GetX();
        $y = $this->GetY();

        $this->SetFillColor(
            238,
            238,
            238
        );

        $this->SetFont(
            "Arial",
            "B",
            9
        );

        $this->MultiCell(
            $anchoEtiqueta,
            7,
            textoPDF($etiqueta),
            1,
            "L",
            true
        );

        $altoEtiqueta =
            $this->GetY() - $y;

        $this->SetXY(
            $x + $anchoEtiqueta,
            $y
        );

        $this->SetFont(
            "Arial",
            "",
            9
        );

        $this->MultiCell(
            $anchoValor,
            7,
            textoPDF($valor),
            1,
            "L",
            false
        );

        $altoValor =
            $this->GetY() - $y;

        $altoFinal = max(
            $altoEtiqueta,
            $altoValor
        );

        $this->SetY(
            $y + $altoFinal
        );
    }
}

//==================================================
// CREAR PDF
//==================================================

$pdf = new PDFOrden(
    "P",
    "mm",
    "Letter"
);

$pdf->numeroOrden = $idOrden;

$pdf->SetMargins(
    15,
    15,
    15
);

$pdf->SetAutoPageBreak(
    true,
    18
);

$pdf->AddPage();

//==================================================
// ENCABEZADO DE LA ORDEN
//==================================================

$pdf->SetFont(
    "Arial",
    "B",
    16
);

$pdf->SetTextColor(
    185,
    20,
    35
);

$pdf->Cell(
    0,
    10,
    textoPDF(
        "ORDEN DE TRABAJO #" .
        $idOrden
    ),
    0,
    1,
    "C"
);

$pdf->SetTextColor(
    0,
    0,
    0
);

//==================================================
// INFORMACIÓN GENERAL
//==================================================

$pdf->tituloSeccion(
    "INFORMACIÓN GENERAL"
);

$pdf->filaDato(
    "Número de orden",
    $idOrden
);

$pdf->filaDato(
    "Estado / ID estado",
    $idEstado
);

$pdf->filaDato(
    "Técnico / ID usuario",
    $idTecnico
);

$pdf->filaDato(
    "Fecha de ingreso",
    formatearFechaOrden(
        $fechaIngreso
    )
);

$pdf->filaDato(
    "Fecha de entrega",
    formatearFechaOrden(
        $fechaEntrega
    )
);

$pdf->filaDato(
    "Tiempo trabajado",
    $tiempoTrabajado
);

//==================================================
// DATOS DEL PROPIETARIO
//==================================================

$pdf->tituloSeccion(
    "DATOS DEL PROPIETARIO"
);

$pdf->filaDato(
    "Propietario",
    $nombrePropietario
);

$pdf->filaDato(
    "ID usuario",
    $orden["Vehiculo_Id_usuario"] ??
    "No registrado"
);

$pdf->filaDato(
    "Correo",
    $orden["Propietario_Correo"] ??
    "No registrado"
);

$pdf->filaDato(
    "Teléfono",
    $orden["Propietario_Telefono"] ??
    "No registrado"
);

//==================================================
// DATOS DEL VEHÍCULO
//==================================================

$pdf->tituloSeccion(
    "DATOS DEL VEHÍCULO"
);

$pdf->filaDato(
    "ID vehículo",
    $orden["Vehiculo_Id"] ??
    "No registrado"
);

$pdf->filaDato(
    "Tipo de vehículo",
    $orden["Vehiculo_Tipo"] ??
    "No registrado"
);

$pdf->filaDato(
    "Placa",
    $orden["Vehiculo_Placa"] ??
    "No registrada"
);

$pdf->filaDato(
    "Marca",
    $orden["Vehiculo_Marca"] ??
    "No registrada"
);

$pdf->filaDato(
    "Línea",
    $orden["Vehiculo_Linea"] ??
    "No registrada"
);

$pdf->filaDato(
    "Modelo",
    $orden["Vehiculo_Modelo"] ??
    "No registrado"
);

$pdf->filaDato(
    "Color",
    $orden["Vehiculo_Color"] ??
    "No registrado"
);

$pdf->filaDato(
    "Número de chasis",
    $orden["Vehiculo_No_chasis"] ??
    "No registrado"
);

$pdf->filaDato(
    "Cilindraje",
    isset($orden["Vehiculo_Cilindraje"])
        ? $orden["Vehiculo_Cilindraje"] .
          " cc"
        : "No registrado"
);

$pdf->filaDato(
    "Combustible",
    $orden["Vehiculo_Combustible"] ??
    "No registrado"
);

$pdf->filaDato(
    "Kilometraje",
    isset($orden["Vehiculo_Kilometraje"])
        ? number_format(
            (float) $orden[
                "Vehiculo_Kilometraje"
            ],
            0,
            ".",
            ","
        ) . " km"
        : "No registrado"
);

//==================================================
// DETALLES DEL SERVICIO
//==================================================

$pdf->tituloSeccion(
    "DETALLES DEL SERVICIO"
);

$pdf->filaDato(
    "Falla reportada",
    $fallaReportada
);

$pdf->filaDato(
    "Observaciones",
    $observaciones
);

//==================================================
// HISTORIAL
//==================================================

$pdf->tituloSeccion(
    "HISTORIAL DE LA ORDEN"
);

if (count($historial) === 0) {

    $pdf->SetFont(
        "Arial",
        "I",
        9
    );

    $pdf->Cell(
        0,
        9,
        textoPDF(
            "No hay movimientos registrados para esta orden."
        ),
        1,
        1,
        "C"
    );

} else {

    foreach ($historial as $movimiento) {

        $nombreUsuario = trim(
            (
                $movimiento["Nombre"] ??
                ""
            ) .
            " " .
            (
                $movimiento["Apellido"] ??
                ""
            )
        );

        if ($nombreUsuario === "") {
            $nombreUsuario =
                "Usuario #" .
                (
                    $movimiento["Id_usuario"] ??
                    "N/D"
                );
        }

        $pdf->SetFillColor(
            245,
            245,
            245
        );

        $pdf->SetFont(
            "Arial",
            "B",
            9
        );

        $pdf->Cell(
            90,
            7,
            textoPDF(
                formatearFechaOrden(
                    $movimiento["Fecha"] ??
                    ""
                )
            ),
            1,
            0,
            "L",
            true
        );

        $pdf->Cell(
            90,
            7,
            textoPDF(
                "Estado ID: " .
                (
                    $movimiento["Id_estado"] ??
                    "N/D"
                )
            ),
            1,
            1,
            "L",
            true
        );

        $pdf->SetFont(
            "Arial",
            "",
            9
        );

        $pdf->MultiCell(
            180,
            7,
            textoPDF(
                "Responsable: " .
                $nombreUsuario .
                "\nComentario: " .
                (
                    $movimiento["Comentario"] ??
                    "Sin comentario"
                )
            ),
            1,
            "L"
        );

        $pdf->Ln(2);
    }
}

//==================================================
// SALIDA DEL PDF
//==================================================

$nombreArchivo =
    "orden_trabajo_" .
    $idOrden .
    ".pdf";

$pdf->Output(
    "I",
    $nombreArchivo
);

exit;