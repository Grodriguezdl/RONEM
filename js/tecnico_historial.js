/*==================================================
MÓDULO DE HISTORIAL DEL PANEL TÉCNICO
==================================================*/

let historialTecnico = [];


/*==================================================
INICIALIZAR MÓDULO
==================================================*/

document.addEventListener(
    "DOMContentLoaded",
    () => {

        cargarHistorialTecnico();
        iniciarBuscadorHistorialTecnico();

    }
);


/*==================================================
CARGAR HISTORIAL GENERAL
==================================================*/

async function cargarHistorialTecnico() {

    const contenedor =
        document.getElementById(
            "container-historial"
        );

    if (!contenedor) {
        return;
    }

    if (
        typeof window.mostrarCarga ===
        "function"
    ) {

        window.mostrarCarga(
            "container-historial",
            "Cargando historial de trabajos..."
        );

    } else {

        contenedor.innerHTML = `
            <p class="text-muted">
                Cargando historial de trabajos...
            </p>
        `;

    }

    try {

        const resultado =
            await consultarAPIHistorialTecnico(
                "api/api_historial_tecnico.php?action=listar"
            );

        historialTecnico =
            extraerRegistrosHistorialTecnico(
                resultado
            );

        renderHistorialTecnico(
            historialTecnico
        );

    } catch (error) {

        console.error(
            "Error al cargar historial:",
            error
        );

        if (
            typeof window.mostrarError ===
            "function"
        ) {

            window.mostrarError(
                "container-historial",
                error.message
            );

        } else {

            contenedor.innerHTML = `
                <div class="error-state">

                    <h2>
                        Ocurrió un error
                    </h2>

                    <p>
                        ${escaparHTMLHistorial(
                            error.message
                        )}
                    </p>

                </div>
            `;

        }

    }

}


/*==================================================
CONSULTAR HISTORIAL DE UNA ORDEN
==================================================*/

async function cargarHistorialPorOrdenTecnico(
    idOrden
) {

    idOrden =
        Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        alert(
            "El ID de la orden no es válido."
        );

        return [];

    }

    try {

        const resultado =
            await consultarAPIHistorialTecnico(
                "api/api_historial_tecnico.php" +
                "?action=por_orden" +
                `&id_orden=${encodeURIComponent(
                    idOrden
                )}`
            );

        return extraerRegistrosHistorialTecnico(
            resultado
        );

    } catch (error) {

        console.error(
            `Error al cargar historial de la orden ${idOrden}:`,
            error
        );

        alert(error.message);

        return [];

    }

}


/*==================================================
CONSULTAR API
==================================================*/

async function consultarAPIHistorialTecnico(
    url
) {

    const respuesta = await fetch(
        url,
        {
            method: "GET",
            cache: "no-store",
            headers: {
                "Accept": "application/json"
            }
        }
    );

    const textoRespuesta =
        await respuesta.text();

    let resultado;

    try {

        resultado =
            JSON.parse(textoRespuesta);

    } catch (error) {

        console.error(
            "Respuesta de historial:",
            textoRespuesta
        );

        throw new Error(
            "La API de historial devolvió una respuesta inválida."
        );

    }

    if (!respuesta.ok) {

        throw new Error(
            resultado.mensaje ||
            resultado.message ||
            "No se pudo consultar el historial."
        );

    }

    if (
        resultado &&
        typeof resultado === "object" &&
        !Array.isArray(resultado) &&
        Object.prototype.hasOwnProperty.call(
            resultado,
            "success"
        ) &&
        resultado.success === false
    ) {

        throw new Error(
            resultado.mensaje ||
            resultado.message ||
            "No se pudo cargar el historial."
        );

    }

    return resultado;

}


/*==================================================
EXTRAER REGISTROS DE LA RESPUESTA
==================================================*/

function extraerRegistrosHistorialTecnico(
    resultado
) {

    if (Array.isArray(resultado)) {

        return resultado;

    }

    if (
        resultado &&
        Array.isArray(resultado.data)
    ) {

        return resultado.data;

    }

    if (
        resultado &&
        Array.isArray(resultado.historial)
    ) {

        return resultado.historial;

    }

    if (
        resultado &&
        Array.isArray(resultado.registros)
    ) {

        return resultado.registros;

    }

    return [];

}


/*==================================================
RENDERIZAR HISTORIAL
==================================================*/

function renderHistorialTecnico(
    registros
) {

    const contenedor =
        document.getElementById(
            "container-historial"
        );

    if (!contenedor) {
        return;
    }

    if (
        !Array.isArray(registros) ||
        registros.length === 0
    ) {

        if (
            typeof window.mostrarVacio ===
            "function"
        ) {

            window.mostrarVacio(
                "container-historial",
                "No hay historial",
                "Todavía no existen registros de trabajos realizados."
            );

        } else {

            contenedor.innerHTML = `
                <div class="empty-state">

                    <h2>
                        No hay historial
                    </h2>

                    <p>
                        Todavía no existen registros
                        de trabajos realizados.
                    </p>

                </div>
            `;

        }

        return;

    }

    contenedor.innerHTML = `
        <div class="history-list">

            ${registros
                .map(
                    registro =>
                        crearRegistroHistorialTecnico(
                            registro
                        )
                )
                .join("")}

        </div>
    `;

}


/*==================================================
CREAR REGISTRO DE HISTORIAL
==================================================*/

function crearRegistroHistorialTecnico(
    registro
) {

    const idHistorial =
        obtenerValorHistorial(
            registro,
            [
                "Id_historial",
                "id_historial"
            ],
            0
        );

    const idOrden =
        obtenerValorHistorial(
            registro,
            [
                "Id_orden",
                "id_orden"
            ],
            0
        );

    const fecha =
        obtenerValorHistorial(
            registro,
            [
                "Fecha",
                "fecha",
                "Fecha_registro",
                "fecha_registro",
                "Fecha_actualizacion",
                "fecha_actualizacion"
            ],
            ""
        );

    const estadoAnterior =
        obtenerValorHistorial(
            registro,
            [
                "Estado_anterior",
                "estado_anterior",
                "Anterior",
                "anterior"
            ],
            ""
        );

    const estadoNuevo =
        obtenerValorHistorial(
            registro,
            [
                "Estado_nuevo",
                "estado_nuevo",
                "Nuevo_estado",
                "nuevo_estado",
                "Estado",
                "estado"
            ],
            "Sin estado"
        );

    const descripcion =
        obtenerValorHistorial(
            registro,
            [
                "Descripcion",
                "descripcion",
                "Observaciones",
                "observaciones",
                "Detalle",
                "detalle",
                "Comentario",
                "comentario"
            ],
            "Sin descripción registrada."
        );

    const tecnico =
        obtenerNombreTecnicoHistorial(
            registro
        );

    const vehiculo =
        obtenerDescripcionVehiculoHistorial(
            registro
        );

    const placa =
        obtenerValorHistorial(
            registro,
            [
                "Placa",
                "placa"
            ],
            ""
        );

    const servicio =
        obtenerValorHistorial(
            registro,
            [
                "Servicio",
                "servicio",
                "Tipo_servicio",
                "tipo_servicio",
                "Trabajo_realizado",
                "trabajo_realizado"
            ],
            ""
        );

    const cambioEstadoHTML =
        estadoAnterior !== ""
            ? `
                <div class="history-status-change">

                    <span class="history-status-old">

                        ${escaparHTMLHistorial(
                            estadoAnterior
                        )}

                    </span>

                    <span class="history-status-arrow">
                        →
                    </span>

                    <span class="${obtenerClaseEstadoHistorial(
                        estadoNuevo
                    )}">

                        ${escaparHTMLHistorial(
                            estadoNuevo
                        )}

                    </span>

                </div>
            `
            : `
                <span class="${obtenerClaseEstadoHistorial(
                    estadoNuevo
                )}">

                    ${escaparHTMLHistorial(
                        estadoNuevo
                    )}

                </span>
            `;

    return `
        <article
            class="history-item"
            data-id-historial="${escaparHTMLHistorial(
                idHistorial
            )}"
            data-id-orden="${escaparHTMLHistorial(
                idOrden
            )}"
        >

            <div class="history-marker">

                <span class="history-marker-dot"></span>

                <span class="history-marker-line"></span>

            </div>

            <div class="history-content">

                <div class="history-header">

                    <div>

                        <span class="history-date">

                            ${escaparHTMLHistorial(
                                formatearFechaHistorial(
                                    fecha
                                )
                            )}

                        </span>

                        <h2 class="history-title">

                            ${
                                Number(idOrden) > 0
                                    ? `Orden #${escaparHTMLHistorial(
                                        idOrden
                                    )}`
                                    : "Registro de historial"
                            }

                        </h2>

                    </div>

                    ${cambioEstadoHTML}

                </div>

                <div class="history-description">

                    <p>

                        ${escaparHTMLHistorial(
                            descripcion
                        )}

                    </p>

                </div>

                <div class="history-details">

                    ${
                        servicio !== ""
                            ? `
                                <p>

                                    <strong>
                                        Servicio:
                                    </strong>

                                    ${escaparHTMLHistorial(
                                        servicio
                                    )}

                                </p>
                            `
                            : ""
                    }

                    ${
                        vehiculo !== ""
                            ? `
                                <p>

                                    <strong>
                                        Vehículo:
                                    </strong>

                                    ${escaparHTMLHistorial(
                                        vehiculo
                                    )}

                                </p>
                            `
                            : ""
                    }

                    ${
                        placa !== ""
                            ? `
                                <p>

                                    <strong>
                                        Placa:
                                    </strong>

                                    ${escaparHTMLHistorial(
                                        placa
                                    )}

                                </p>
                            `
                            : ""
                    }

                    ${
                        tecnico !== ""
                            ? `
                                <p>

                                    <strong>
                                        Técnico:
                                    </strong>

                                    ${escaparHTMLHistorial(
                                        tecnico
                                    )}

                                </p>
                            `
                            : ""
                    }

                </div>

                ${
                    Number(idOrden) > 0
                        ? `
                            <div class="history-actions">

                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    onclick="verHistorialOrdenTecnico(${Number(
                                        idOrden
                                    )})"
                                >

                                    Ver historial de orden

                                </button>

                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    onclick="abrirReporteOrdenTecnico(${Number(
                                        idOrden
                                    )})"
                                >

                                    Generar PDF

                                </button>

                            </div>
                        `
                        : ""
                }

            </div>

        </article>
    `;

}


/*==================================================
MOSTRAR HISTORIAL DE UNA ORDEN
==================================================*/

async function verHistorialOrdenTecnico(
    idOrden
) {

    const registros =
        await cargarHistorialPorOrdenTecnico(
            idOrden
        );

    if (
        !Array.isArray(registros) ||
        registros.length === 0
    ) {

        alert(
            `La orden #${idOrden} no tiene registros de historial.`
        );

        return;

    }

    const contenido =
        registros
            .map(
                (registro, indice) => {

                    const fecha =
                        obtenerValorHistorial(
                            registro,
                            [
                                "Fecha",
                                "Fecha_registro",
                                "Fecha_actualizacion"
                            ],
                            ""
                        );

                    const estado =
                        obtenerValorHistorial(
                            registro,
                            [
                                "Estado_nuevo",
                                "Nuevo_estado",
                                "Estado"
                            ],
                            "Sin estado"
                        );

                    const descripcion =
                        obtenerValorHistorial(
                            registro,
                            [
                                "Descripcion",
                                "Observaciones",
                                "Detalle",
                                "Comentario"
                            ],
                            "Sin descripción"
                        );

                    return [
                        `${indice + 1}. ${formatearFechaHistorial(
                            fecha
                        )}`,
                        `Estado: ${estado}`,
                        `Detalle: ${descripcion}`
                    ].join("\n");

                }
            )
            .join("\n\n");

    alert(
        `Historial de la orden #${idOrden}\n\n${contenido}`
    );

}


/*==================================================
ABRIR REPORTE PDF DE LA ORDEN
==================================================*/

function abrirReporteOrdenTecnico(
    idOrden
) {

    idOrden =
        Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        alert(
            "El ID de la orden no es válido."
        );

        return;

    }

    const ruta =
        `reportes/reporte_orden.php?id=${encodeURIComponent(
            idOrden
        )}`;

    const ventana =
        window.open(
            ruta,
            "_blank",
            "noopener,noreferrer"
        );

    if (!ventana) {

        alert(
            "El navegador bloqueó la apertura del reporte. Permite las ventanas emergentes para este sitio."
        );

    }

}


/*==================================================
INICIAR BUSCADOR DE HISTORIAL
==================================================*/

function iniciarBuscadorHistorialTecnico() {

    const buscador =
        document.getElementById(
            "buscar-historial"
        );

    if (!buscador) {

        /*
         * El campo se agregará cuando
         * modifiquemos panel_tecnico.php.
         */

        return;

    }

    buscador.addEventListener(
        "input",
        () => {

            filtrarHistorialTecnico(
                buscador.value
            );

        }
    );

}


/*==================================================
FILTRAR HISTORIAL
==================================================*/

function filtrarHistorialTecnico(
    terminoBusqueda
) {

    const termino =
        normalizarTextoHistorial(
            terminoBusqueda
        );

    if (termino === "") {

        renderHistorialTecnico(
            historialTecnico
        );

        return;

    }

    const registrosFiltrados =
        historialTecnico.filter(
            registro => {

                const contenido = [
                    obtenerValorHistorial(
                        registro,
                        ["Id_historial"],
                        ""
                    ),
                    obtenerValorHistorial(
                        registro,
                        ["Id_orden"],
                        ""
                    ),
                    obtenerValorHistorial(
                        registro,
                        ["Estado_anterior"],
                        ""
                    ),
                    obtenerValorHistorial(
                        registro,
                        [
                            "Estado_nuevo",
                            "Estado"
                        ],
                        ""
                    ),
                    obtenerValorHistorial(
                        registro,
                        [
                            "Descripcion",
                            "Observaciones",
                            "Detalle"
                        ],
                        ""
                    ),
                    obtenerValorHistorial(
                        registro,
                        ["Servicio"],
                        ""
                    ),
                    obtenerValorHistorial(
                        registro,
                        ["Placa"],
                        ""
                    ),
                    obtenerDescripcionVehiculoHistorial(
                        registro
                    ),
                    obtenerNombreTecnicoHistorial(
                        registro
                    )
                ].join(" ");

                return normalizarTextoHistorial(
                    contenido
                ).includes(
                    termino
                );

            }
        );

    renderHistorialTecnico(
        registrosFiltrados
    );

}


/*==================================================
OBTENER NOMBRE DEL TÉCNICO
==================================================*/

function obtenerNombreTecnicoHistorial(
    registro
) {

    const nombreCompleto =
        obtenerValorHistorial(
            registro,
            [
                "Tecnico",
                "tecnico",
                "Nombre_tecnico",
                "nombre_tecnico",
                "Tecnico_nombre"
            ],
            ""
        );

    if (
        String(nombreCompleto).trim() !== ""
    ) {

        return String(
            nombreCompleto
        ).trim();

    }

    const nombre =
        obtenerValorHistorial(
            registro,
            [
                "Nombre",
                "nombre"
            ],
            ""
        );

    const apellido =
        obtenerValorHistorial(
            registro,
            [
                "Apellido",
                "apellido"
            ],
            ""
        );

    return `${nombre} ${apellido}`.trim();

}


/*==================================================
OBTENER DESCRIPCIÓN DEL VEHÍCULO
==================================================*/

function obtenerDescripcionVehiculoHistorial(
    registro
) {

    const vehiculoCompleto =
        obtenerValorHistorial(
            registro,
            [
                "Vehiculo",
                "vehiculo",
                "Descripcion_vehiculo"
            ],
            ""
        );

    if (
        String(vehiculoCompleto).trim() !== ""
    ) {

        return String(
            vehiculoCompleto
        ).trim();

    }

    const marca =
        obtenerValorHistorial(
            registro,
            [
                "Marca",
                "marca"
            ],
            ""
        );

    const linea =
        obtenerValorHistorial(
            registro,
            [
                "Linea",
                "linea"
            ],
            ""
        );

    const modelo =
        obtenerValorHistorial(
            registro,
            [
                "Modelo",
                "modelo"
            ],
            ""
        );

    return [
        marca,
        linea,
        modelo
    ]
        .filter(valor => {

            return (
                valor !== null &&
                valor !== undefined &&
                String(valor).trim() !== ""
            );

        })
        .join(" ");

}


/*==================================================
OBTENER VALOR DE UN CAMPO
==================================================*/

function obtenerValorHistorial(
    registro,
    posiblesCampos,
    valorPredeterminado = ""
) {

    if (
        !registro ||
        typeof registro !== "object"
    ) {

        return valorPredeterminado;

    }

    for (
        const campo of posiblesCampos
    ) {

        if (
            Object.prototype.hasOwnProperty.call(
                registro,
                campo
            ) &&
            registro[campo] !== null &&
            registro[campo] !== undefined &&
            String(registro[campo]).trim() !== ""
        ) {

            return registro[campo];

        }

    }

    return valorPredeterminado;

}


/*==================================================
FORMATEAR FECHA
==================================================*/

function formatearFechaHistorial(
    fecha
) {

    if (
        fecha === null ||
        fecha === undefined ||
        String(fecha).trim() === ""
    ) {

        return "Fecha no registrada";

    }

    const textoFecha =
        String(fecha).trim();

    const fechaNormalizada =
        textoFecha.includes(" ")
            ? textoFecha.replace(" ", "T")
            : textoFecha;

    const objetoFecha =
        new Date(fechaNormalizada);

    if (
        Number.isNaN(
            objetoFecha.getTime()
        )
    ) {

        return textoFecha;

    }

    return objetoFecha.toLocaleString(
        "es-GT",
        {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        }
    );

}


/*==================================================
OBTENER CLASE DEL ESTADO
==================================================*/

function obtenerClaseEstadoHistorial(
    estado
) {

    const valor =
        normalizarTextoHistorial(
            estado
        );

    if (
        valor.includes("finalizada") ||
        valor.includes("finalizado") ||
        valor.includes("completada") ||
        valor.includes("completado") ||
        valor.includes("entregada") ||
        valor.includes("entregado")
    ) {

        return "badge badge-success";

    }

    if (
        valor.includes("reparacion") ||
        valor.includes("proceso") ||
        valor.includes("trabajando") ||
        valor.includes("diagnostico")
    ) {

        return "badge badge-repair";

    }

    if (
        valor.includes("pendiente") ||
        valor.includes("espera") ||
        valor.includes("repuesto")
    ) {

        return "badge badge-warning";

    }

    if (
        valor.includes("cancelada") ||
        valor.includes("cancelado") ||
        valor.includes("rechazada") ||
        valor.includes("rechazado")
    ) {

        return "badge badge-danger";

    }

    return "badge badge-secondary";

}


/*==================================================
NORMALIZAR TEXTO
==================================================*/

function normalizarTextoHistorial(
    valor
) {

    return String(valor ?? "")
        .toLowerCase()
        .normalize("NFD")
        .replace(
            /[\u0300-\u036f]/g,
            ""
        )
        .trim();

}


/*==================================================
ESCAPAR HTML
==================================================*/

function escaparHTMLHistorial(
    valor
) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

}


/*==================================================
EXPONER FUNCIONES NECESARIAS
==================================================*/

window.cargarHistorialTecnico =
    cargarHistorialTecnico;

window.cargarHistorialPorOrdenTecnico =
    cargarHistorialPorOrdenTecnico;

window.renderHistorialTecnico =
    renderHistorialTecnico;

window.verHistorialOrdenTecnico =
    verHistorialOrdenTecnico;

window.abrirReporteOrdenTecnico =
    abrirReporteOrdenTecnico;