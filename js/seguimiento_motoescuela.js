//==================================================
// SEGUIMIENTO DE MOTO ESCUELA - ADMINISTRACIÓN
//==================================================

const API_SEGUIMIENTO_MOTOESCUELA =
    "api/api_motoescuela.php";

let solicitudesSeguimientoMotoescuela = [];
let estadosSeguimientoMotoescuela = [];
let solicitudSeguimientoActual = null;
let modalSeguimientoMotoescuela = null;

//==================================================
// INICIAR MÓDULO
//==================================================

document.addEventListener("DOMContentLoaded", () => {

    inicializarModalSeguimiento();

    configurarBuscadorSeguimiento();

    configurarFormularioSeguimiento();

    configurarCambioEstadoSeguimiento();

    cargarEstadosSeguimientoMotoescuela();

    cargarSolicitudesSeguimientoMotoescuela();
});

//==================================================
// OBTENER ELEMENTO
//==================================================

function elementoSeguimiento(id) {
    return document.getElementById(id);
}

//==================================================
// INICIALIZAR MODAL BOOTSTRAP
//==================================================

function inicializarModalSeguimiento() {

    const modalElemento = elementoSeguimiento(
        "modalSeguimientoMotoescuela"
    );

    if (
        modalElemento &&
        typeof bootstrap !== "undefined"
    ) {
        modalSeguimientoMotoescuela =
            bootstrap.Modal.getOrCreateInstance(
                modalElemento
            );
    }
}

//==================================================
// ESCAPAR HTML
//==================================================

function escaparHTMLSeguimiento(valor) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

//==================================================
// LEER RESPUESTA DE LA API
//==================================================

async function leerRespuestaSeguimiento(respuesta) {

    const texto = await respuesta.text();

    let resultado;

    try {

        resultado = JSON.parse(texto);

    } catch (error) {

        console.error(
            "Respuesta inválida de la API:",
            texto
        );

        throw new Error(
            "La API devolvió una respuesta inválida."
        );
    }

    if (!respuesta.ok || resultado.success === false) {

        throw new Error(
            resultado.mensaje ||
            resultado.error ||
            "Ocurrió un error en la solicitud."
        );
    }

    return resultado;
}

//==================================================
// MOSTRAR ALERTA
//==================================================

function mostrarAlertaSeguimiento(
    mensaje,
    tipo = "success"
) {

    if (typeof Swal !== "undefined") {

        Swal.fire({
            icon:
                tipo === "danger"
                    ? "error"
                    : tipo,
            title:
                tipo === "success"
                    ? "Proceso completado"
                    : "Aviso",
            text: mensaje,
            confirmButtonText: "Aceptar"
        });

        return;
    }

    alert(mensaje);
}

//==================================================
// FORMATEAR FECHA
//==================================================

function formatearFechaSeguimiento(fecha) {

    if (!fecha) {
        return "Sin fecha";
    }

    const fechaNormalizada = String(fecha)
        .replace(" ", "T");

    const objetoFecha =
        new Date(fechaNormalizada);

    if (Number.isNaN(objetoFecha.getTime())) {
        return escaparHTMLSeguimiento(fecha);
    }

    return objetoFecha.toLocaleString(
        "es-GT",
        {
            dateStyle: "medium",
            timeStyle: "short"
        }
    );
}

//==================================================
// MOSTRAR VALOR O TEXTO ALTERNATIVO
//==================================================

function valorSeguimiento(
    dato,
    textoAlternativo = "No registrado"
) {

    if (
        dato === null ||
        dato === undefined ||
        String(dato).trim() === ""
    ) {
        return textoAlternativo;
    }

    return dato;
}

//==================================================
// CLASE DEL ESTADO
//==================================================

function claseEstadoSeguimiento(porcentaje) {

    const progreso = Number(porcentaje || 0);

    if (progreso >= 100) {
        return "bg-success";
    }

    if (progreso >= 75) {
        return "bg-primary";
    }

    if (progreso >= 40) {
        return "bg-warning text-dark";
    }

    if (progreso > 0) {
        return "bg-info text-dark";
    }

    return "bg-secondary";
}

//==================================================
// CONFIGURAR BUSCADOR
//==================================================

function configurarBuscadorSeguimiento() {

    const buscador = elementoSeguimiento(
        "buscarSolicitud"
    );

    if (!buscador) {
        return;
    }

    buscador.addEventListener(
        "input",
        renderSolicitudesSeguimientoMotoescuela
    );
}

//==================================================
// CARGAR SOLICITUDES
//==================================================

async function cargarSolicitudesSeguimientoMotoescuela() {

    const tbody = elementoSeguimiento(
        "tbodySolicitudes"
    );

    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-4">
                <div
                    class="spinner-border spinner-border-sm"
                    role="status"
                ></div>

                <span class="ms-2">
                    Cargando solicitudes...
                </span>
            </td>
        </tr>
    `;

    try {

        const respuesta = await fetch(
            `${API_SEGUIMIENTO_MOTOESCUELA}?action=listar`,
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const resultado =
            await leerRespuestaSeguimiento(respuesta);

        solicitudesSeguimientoMotoescuela =
            Array.isArray(resultado.data)
                ? resultado.data
                : [];

        renderSolicitudesSeguimientoMotoescuela();

    } catch (error) {

        console.error(error);

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="10"
                    class="text-center text-danger py-4"
                >
                    ${escaparHTMLSeguimiento(
                        error.message
                    )}
                </td>
            </tr>
        `;

        actualizarContadorSeguimiento(0);
    }
}

//==================================================
// RENDERIZAR SOLICITUDES
//==================================================

function renderSolicitudesSeguimientoMotoescuela() {

    const tbody = elementoSeguimiento(
        "tbodySolicitudes"
    );

    if (!tbody) {
        return;
    }

    const buscador = elementoSeguimiento(
        "buscarSolicitud"
    );

    const textoBusqueda = buscador
        ? buscador.value
            .trim()
            .toLowerCase()
        : "";

    const solicitudesFiltradas =
        solicitudesSeguimientoMotoescuela.filter(
            (solicitud) => {

                const contenido = `
                    ${solicitud.Id_solicitud ?? ""}
                    ${solicitud.Id_usuario ?? ""}
                    ${solicitud.Nombre ?? ""}
                    ${solicitud.Apellido ?? ""}
                    ${solicitud.DPI ?? ""}
                    ${solicitud.Telefono ?? ""}
                    ${solicitud.Correo ?? ""}
                    ${solicitud.Nivel ?? ""}
                    ${solicitud.Tipo_licencia ?? ""}
                    ${solicitud.Estado_motoescuela ?? ""}
                `.toLowerCase();

                return contenido.includes(
                    textoBusqueda
                );
            }
        );

    actualizarContadorSeguimiento(
        solicitudesFiltradas.length
    );

    if (solicitudesFiltradas.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="10"
                    class="text-center py-4"
                >
                    No hay solicitudes registradas.
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML =
        solicitudesFiltradas.map(
            (solicitud) => {

                const idSolicitud = Number(
                    solicitud.Id_solicitud
                );

                const nombreCompleto = `
                    ${solicitud.Nombre ?? ""}
                    ${solicitud.Apellido ?? ""}
                `.trim();

                const estado =
                    solicitud.Estado_motoescuela ||
                    "Sin estado";

                const progreso = Math.min(
                    100,
                    Math.max(
                        0,
                        Number(
                            solicitud
                                .Porcentaje_progreso || 0
                        )
                    )
                );

                return `
                    <tr>

                        <td>
                            ${idSolicitud}
                        </td>

                        <td>
                            <strong>
                                ${escaparHTMLSeguimiento(
                                    nombreCompleto
                                )}
                            </strong>

                            <small
                                class="d-block text-muted"
                            >
                                Usuario #${escaparHTMLSeguimiento(
                                    solicitud.Id_usuario
                                )}
                            </small>
                        </td>

                        <td>
                            ${escaparHTMLSeguimiento(
                                valorSeguimiento(
                                    solicitud.DPI
                                )
                            )}
                        </td>

                        <td>
                            ${escaparHTMLSeguimiento(
                                valorSeguimiento(
                                    solicitud.Telefono
                                )
                            )}
                        </td>

                        <td>
                            ${escaparHTMLSeguimiento(
                                valorSeguimiento(
                                    solicitud.Correo
                                )
                            )}
                        </td>

                        <td>
                            ${escaparHTMLSeguimiento(
                                valorSeguimiento(
                                    solicitud.Nivel,
                                    "Sin nivel"
                                )
                            )}
                        </td>

                        <td>
                            ${escaparHTMLSeguimiento(
                                valorSeguimiento(
                                    solicitud.Tipo_licencia,
                                    "Sin licencia"
                                )
                            )}
                        </td>

                        <td>
                            ${formatearFechaSeguimiento(
                                solicitud.Fecha_solicitud
                            )}
                        </td>

                        <td style="min-width:150px;">

                            <span
                                class="badge ${claseEstadoSeguimiento(
                                    progreso
                                )}"
                            >
                                ${escaparHTMLSeguimiento(
                                    estado
                                )}
                            </span>

                            <div
                                class="progress mt-2"
                                style="height:7px;"
                            >
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width:${progreso}%"
                                    aria-valuenow="${progreso}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
                            </div>

                            <small class="text-muted">
                                ${progreso}% completado
                            </small>

                        </td>

                        <td>

                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                onclick="abrirSeguimientoMotoescuela(${idSolicitud})"
                                title="Ver seguimiento"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </td>

                    </tr>
                `;
            }
        ).join("");
}

//==================================================
// ACTUALIZAR CONTADOR
//==================================================

function actualizarContadorSeguimiento(cantidad) {

    const contador =
        elementoSeguimiento("chipSolicitudes");

    if (!contador) {
        return;
    }

    contador.textContent =
        cantidad === 1
            ? "1 solicitud"
            : `${cantidad} solicitudes`;
}

//==================================================
// CARGAR ESTADOS
//==================================================

async function cargarEstadosSeguimientoMotoescuela() {

    const select = elementoSeguimiento(
        "seguimientoEstadoNuevo"
    );

    if (select) {

        select.innerHTML = `
            <option value="">
                Cargando estados...
            </option>
        `;
    }

    try {

        const respuesta = await fetch(
            `${API_SEGUIMIENTO_MOTOESCUELA}?action=estados`,
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const resultado =
            await leerRespuestaSeguimiento(respuesta);

        estadosSeguimientoMotoescuela =
            Array.isArray(resultado.data)
                ? resultado.data
                : [];

        llenarEstadosSeguimiento();

    } catch (error) {

        console.error(error);

        if (select) {

            select.innerHTML = `
                <option value="">
                    Error al cargar estados
                </option>
            `;
        }
    }
}

//==================================================
// LLENAR SELECT DE ESTADOS
//==================================================

function llenarEstadosSeguimiento(
    idSeleccionado = null
) {

    const select = elementoSeguimiento(
        "seguimientoEstadoNuevo"
    );

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un estado
        </option>
    `;

    estadosSeguimientoMotoescuela.forEach(
        (estado) => {

            const option =
                document.createElement("option");

            option.value =
                estado.Id_estado_motoescuela;

            option.textContent =
                `${estado.Nombre} - ` +
                `${estado.Porcentaje_progreso}%`;

            option.dataset.progreso =
                estado.Porcentaje_progreso;

            option.dataset.descripcion =
                estado.Descripcion || "";

            if (
                Number(idSeleccionado) ===
                Number(
                    estado.Id_estado_motoescuela
                )
            ) {
                option.selected = true;
            }

            select.appendChild(option);
        }
    );
}

//==================================================
// ABRIR SEGUIMIENTO
//==================================================

async function abrirSeguimientoMotoescuela(
    idSolicitud
) {

    solicitudSeguimientoActual =
        Number(idSolicitud);

    limpiarModalSeguimiento();

    const idInput = elementoSeguimiento(
        "seguimientoIdSolicitud"
    );

    if (idInput) {
        idInput.value = solicitudSeguimientoActual;
    }

    const contenedorDatos = elementoSeguimiento(
        "seguimientoDatosAlumno"
    );

    const contenedorHistorial =
        elementoSeguimiento(
            "seguimientoHistorial"
        );

    if (contenedorDatos) {

        contenedorDatos.innerHTML = `
            <div class="text-center py-4">
                <div
                    class="spinner-border spinner-border-sm"
                    role="status"
                ></div>

                <span class="ms-2">
                    Cargando información...
                </span>
            </div>
        `;
    }

    if (contenedorHistorial) {

        contenedorHistorial.innerHTML = `
            <div class="text-center py-4">
                Cargando historial...
            </div>
        `;
    }

    if (modalSeguimientoMotoescuela) {
        modalSeguimientoMotoescuela.show();
    }

    try {

        await Promise.all([
            cargarDetalleSeguimiento(
                solicitudSeguimientoActual
            ),
            cargarHistorialSeguimiento(
                solicitudSeguimientoActual
            )
        ]);

    } catch (error) {

        console.error(error);

        mostrarAlertaSeguimiento(
            error.message,
            "danger"
        );
    }
}

//==================================================
// CARGAR DETALLE
//==================================================

async function cargarDetalleSeguimiento(
    idSolicitud
) {

    const respuesta = await fetch(
        `${API_SEGUIMIENTO_MOTOESCUELA}` +
        `?action=detalle&id=${encodeURIComponent(
            idSolicitud
        )}`,
        {
            method: "GET",
            cache: "no-store"
        }
    );

    const resultado =
        await leerRespuestaSeguimiento(respuesta);

    mostrarDetalleSeguimiento(resultado.data);
}

//==================================================
// MOSTRAR DETALLE
//==================================================

function mostrarDetalleSeguimiento(solicitud) {

    const contenedor = elementoSeguimiento(
        "seguimientoDatosAlumno"
    );

    if (!contenedor) {
        return;
    }

    const nombreCompleto = `
        ${solicitud.Nombre ?? ""}
        ${solicitud.Apellido ?? ""}
    `.trim();

    const progreso = Math.min(
        100,
        Math.max(
            0,
            Number(
                solicitud.Porcentaje_progreso || 0
            )
        )
    );

    contenedor.innerHTML = `
        <div class="row g-3">

            <div class="col-md-6">
                <small class="text-muted">
                    Nombre completo
                </small>

                <div class="fw-semibold">
                    ${escaparHTMLSeguimiento(
                        nombreCompleto
                    )}
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">
                    DPI
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.DPI
                        )
                    )}
                </div>
            </div>

            <div class="col-md-3">
                <small class="text-muted">
                    Edad
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Edad
                        )
                    )}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">
                    Teléfono
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Telefono
                        )
                    )}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">
                    Correo
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Correo
                        )
                    )}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">
                    Fecha de nacimiento
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Fecha_nacimiento
                        )
                    )}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">
                    Nivel
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Nivel,
                            "Sin nivel"
                        )
                    )}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">
                    Tipo de licencia
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Tipo_licencia,
                            "Sin licencia"
                        )
                    )}
                </div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">
                    Género
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Genero
                        )
                    )}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-muted">
                    Contacto de emergencia
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Nombre_emergencia
                        )
                    )}
                </div>

                <div class="small text-muted">
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Contacto_emergencia
                        )
                    )}
                </div>
            </div>

            <div class="col-md-6">
                <small class="text-muted">
                    Condiciones médicas
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Condiciones_medicas,
                            "Ninguna registrada"
                        )
                    )}
                </div>
            </div>

            <div class="col-12">
                <small class="text-muted">
                    Dirección
                </small>

                <div>
                    ${escaparHTMLSeguimiento(
                        valorSeguimiento(
                            solicitud.Direccion
                        )
                    )}
                </div>
            </div>

        </div>
    `;

    actualizarProgresoVisualSeguimiento(
        progreso,
        solicitud.Estado_motoescuela ||
        "Sin estado",
        solicitud.Descripcion_estado || ""
    );

    llenarEstadosSeguimiento(
        solicitud.Id_estado_motoescuela
    );
}

//==================================================
// ACTUALIZAR PROGRESO VISUAL
//==================================================

function actualizarProgresoVisualSeguimiento(
    progreso,
    estado,
    descripcion = ""
) {

    const barra = elementoSeguimiento(
        "seguimientoBarraProgreso"
    );

    const porcentaje = elementoSeguimiento(
        "seguimientoPorcentaje"
    );

    const estadoTexto = elementoSeguimiento(
        "seguimientoEstadoActual"
    );

    const descripcionTexto =
        elementoSeguimiento(
            "seguimientoDescripcionEstado"
        );

    const progresoSeguro = Math.min(
        100,
        Math.max(0, Number(progreso || 0))
    );

    if (barra) {

        barra.style.width =
            `${progresoSeguro}%`;

        barra.setAttribute(
            "aria-valuenow",
            progresoSeguro
        );

        barra.textContent =
            `${progresoSeguro}%`;
    }

    if (porcentaje) {
        porcentaje.textContent =
            `${progresoSeguro}%`;
    }

    if (estadoTexto) {
        estadoTexto.textContent =
            estado || "Sin estado";
    }

    if (descripcionTexto) {
        descripcionTexto.textContent =
            descripcion || "";
    }
}

//==================================================
// CARGAR HISTORIAL
//==================================================

async function cargarHistorialSeguimiento(
    idSolicitud
) {

    const contenedor = elementoSeguimiento(
        "seguimientoHistorial"
    );

    const respuesta = await fetch(
        `${API_SEGUIMIENTO_MOTOESCUELA}` +
        `?action=historial&id=${encodeURIComponent(
            idSolicitud
        )}`,
        {
            method: "GET",
            cache: "no-store"
        }
    );

    const resultado =
        await leerRespuestaSeguimiento(respuesta);

    const historial =
        Array.isArray(resultado.data)
            ? resultado.data
            : [];

    if (!contenedor) {
        return;
    }

    if (historial.length === 0) {

        contenedor.innerHTML = `
            <div
                class="text-center text-muted py-4"
            >
                <i
                    class="bi bi-clock-history fs-2"
                ></i>

                <p class="mb-0 mt-2">
                    Todavía no existen cambios
                    registrados.
                </p>
            </div>
        `;

        return;
    }

    contenedor.innerHTML = historial.map(
        (registro) => {

            const estadoAnterior =
                registro.Estado_anterior ||
                "Sin estado anterior";

            const estadoNuevo =
                registro.Estado_nuevo ||
                "Estado actualizado";

            const responsable = `
                ${registro.Nombre_usuario ?? ""}
                ${registro.Apellido_usuario ?? ""}
            `.trim();

            return `
                <div
                    class="border-start border-3
                           ps-3 pb-4 position-relative"
                >

                    <div
                        class="position-absolute
                               rounded-circle bg-primary"
                        style="
                            width:12px;
                            height:12px;
                            left:-7px;
                            top:4px;
                        "
                    ></div>

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-start
                               gap-3"
                    >

                        <div>

                            <strong>
                                ${escaparHTMLSeguimiento(
                                    estadoNuevo
                                )}
                            </strong>

                            <div
                                class="small text-muted"
                            >
                                ${escaparHTMLSeguimiento(
                                    estadoAnterior
                                )}
                                →
                                ${escaparHTMLSeguimiento(
                                    estadoNuevo
                                )}
                            </div>

                        </div>

                        <span
                            class="badge ${claseEstadoSeguimiento(
                                registro.Progreso_nuevo
                            )}"
                        >
                            ${Number(
                                registro.Progreso_nuevo || 0
                            )}%
                        </span>

                    </div>

                    ${
                        registro.Comentario
                            ? `
                                <p class="mt-2 mb-1">
                                    ${escaparHTMLSeguimiento(
                                        registro.Comentario
                                    )}
                                </p>
                            `
                            : `
                                <p
                                    class="mt-2 mb-1
                                           text-muted fst-italic"
                                >
                                    Sin comentario.
                                </p>
                            `
                    }

                    <small class="text-muted">

                        <i class="bi bi-calendar3"></i>

                        ${formatearFechaSeguimiento(
                            registro.Fecha
                        )}

                        ${
                            responsable
                                ? `
                                    · Actualizado por
                                    ${escaparHTMLSeguimiento(
                                        responsable
                                    )}
                                `
                                : ""
                        }

                    </small>

                </div>
            `;
        }
    ).join("");
}

//==================================================
// CONFIGURAR CAMBIO DE ESTADO
//==================================================

function configurarCambioEstadoSeguimiento() {

    const select = elementoSeguimiento(
        "seguimientoEstadoNuevo"
    );

    if (!select) {
        return;
    }

    select.addEventListener("change", () => {

        const option =
            select.options[select.selectedIndex];

        const progreso = Number(
            option?.dataset?.progreso || 0
        );

        const descripcion =
            option?.dataset?.descripcion || "";

        const vistaPrevia = elementoSeguimiento(
            "seguimientoVistaPreviaEstado"
        );

        if (!select.value) {

            if (vistaPrevia) {
                vistaPrevia.innerHTML = "";
            }

            return;
        }

        if (vistaPrevia) {

            vistaPrevia.innerHTML = `
                <div
                    class="alert alert-light border mt-2 mb-0"
                >
                    <div
                        class="d-flex
                               justify-content-between"
                    >
                        <strong>
                            ${escaparHTMLSeguimiento(
                                option.textContent
                            )}
                        </strong>

                        <span>
                            ${progreso}%
                        </span>
                    </div>

                    ${
                        descripcion
                            ? `
                                <small
                                    class="text-muted"
                                >
                                    ${escaparHTMLSeguimiento(
                                        descripcion
                                    )}
                                </small>
                            `
                            : ""
                    }
                </div>
            `;
        }
    });
}

//==================================================
// CONFIGURAR FORMULARIO
//==================================================

function configurarFormularioSeguimiento() {

    const formulario = elementoSeguimiento(
        "formSeguimientoMotoescuela"
    );

    if (!formulario) {
        return;
    }

    formulario.addEventListener(
        "submit",
        actualizarEstadoSeguimiento
    );
}

//==================================================
// ACTUALIZAR ESTADO
//==================================================

async function actualizarEstadoSeguimiento(evento) {

    evento.preventDefault();

    const idSolicitud = Number(
        elementoSeguimiento(
            "seguimientoIdSolicitud"
        )?.value || 0
    );

    const idEstadoNuevo = Number(
        elementoSeguimiento(
            "seguimientoEstadoNuevo"
        )?.value || 0
    );

    const comentario =
        elementoSeguimiento(
            "seguimientoComentario"
        )?.value.trim() || "";

    if (!idSolicitud) {

        mostrarAlertaSeguimiento(
            "No se encontró la solicitud.",
            "danger"
        );

        return;
    }

    if (!idEstadoNuevo) {

        mostrarAlertaSeguimiento(
            "Selecciona el nuevo estado.",
            "danger"
        );

        return;
    }

    const boton = elementoSeguimiento(
        "btnActualizarSeguimiento"
    );

    const textoOriginal =
        boton?.innerHTML || "";

    if (boton) {

        boton.disabled = true;

        boton.innerHTML = `
            <span
                class="spinner-border
                       spinner-border-sm me-2"
            ></span>
            Guardando...
        `;
    }

    try {

        const datos = new FormData();

        datos.append(
            "id_solicitud",
            idSolicitud
        );

        datos.append(
            "id_estado_nuevo",
            idEstadoNuevo
        );

        datos.append(
            "comentario",
            comentario
        );

        /*
         * Respaldo opcional:
         * si tu página tiene un input oculto con el ID
         * del administrador, se enviará a la API.
         */

        const inputUsuario = elementoSeguimiento(
            "idUsuarioSesion"
        );

        if (
            inputUsuario &&
            Number(inputUsuario.value) > 0
        ) {
            datos.append(
                "id_usuario",
                inputUsuario.value
            );
        }

        const respuesta = await fetch(
            `${API_SEGUIMIENTO_MOTOESCUELA}` +
            "?action=actualizar_estado",
            {
                method: "POST",
                body: datos
            }
        );

        const resultado =
            await leerRespuestaSeguimiento(respuesta);

        mostrarAlertaSeguimiento(
            resultado.mensaje ||
            "Estado actualizado correctamente."
        );

        const comentarioInput =
            elementoSeguimiento(
                "seguimientoComentario"
            );

        if (comentarioInput) {
            comentarioInput.value = "";
        }

        const vistaPrevia = elementoSeguimiento(
            "seguimientoVistaPreviaEstado"
        );

        if (vistaPrevia) {
            vistaPrevia.innerHTML = "";
        }

        await Promise.all([
            cargarSolicitudesSeguimientoMotoescuela(),

            cargarDetalleSeguimiento(
                idSolicitud
            ),

            cargarHistorialSeguimiento(
                idSolicitud
            )
        ]);

    } catch (error) {

        console.error(error);

        mostrarAlertaSeguimiento(
            error.message,
            "danger"
        );

    } finally {

        if (boton) {

            boton.disabled = false;

            boton.innerHTML =
                textoOriginal ||
                `
                    <i class="bi bi-check-circle"></i>
                    Actualizar estado
                `;
        }
    }
}

//==================================================
// LIMPIAR MODAL
//==================================================

function limpiarModalSeguimiento() {

    const idSolicitud = elementoSeguimiento(
        "seguimientoIdSolicitud"
    );

    const comentario = elementoSeguimiento(
        "seguimientoComentario"
    );

    const vistaPrevia = elementoSeguimiento(
        "seguimientoVistaPreviaEstado"
    );

    const datos = elementoSeguimiento(
        "seguimientoDatosAlumno"
    );

    const historial = elementoSeguimiento(
        "seguimientoHistorial"
    );

    if (idSolicitud) {
        idSolicitud.value = "";
    }

    if (comentario) {
        comentario.value = "";
    }

    if (vistaPrevia) {
        vistaPrevia.innerHTML = "";
    }

    if (datos) {
        datos.innerHTML = "";
    }

    if (historial) {
        historial.innerHTML = "";
    }

    actualizarProgresoVisualSeguimiento(
        0,
        "Sin estado",
        ""
    );
}

//==================================================
// RECARGAR MANUALMENTE
//==================================================

function recargarSeguimientoMotoescuela() {
    cargarSolicitudesSeguimientoMotoescuela();
}