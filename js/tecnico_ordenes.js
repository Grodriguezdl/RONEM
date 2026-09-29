/*==================================================
MÓDULO DE ÓRDENES DEL PANEL TÉCNICO
==================================================*/

let ordenesTecnico = [];
let ordenTecnicoEditando = null;


/*==================================================
INICIALIZAR MÓDULO
==================================================*/

document.addEventListener(
    "DOMContentLoaded",
    () => {

        cargarOrdenesTecnico();
        iniciarFormularioOrdenTecnico();
        iniciarBuscadorOrdenesTecnico();
        iniciarFiltrosOrdenesTecnico();

    }
);


/*==================================================
CARGAR ÓRDENES
==================================================*/

async function cargarOrdenesTecnico() {

    const contenedor =
        document.getElementById(
            "container-ordenes"
        );

    if (!contenedor) {
        return;
    }

    if (
        typeof window.mostrarCarga ===
        "function"
    ) {

        window.mostrarCarga(
            "container-ordenes",
            "Cargando órdenes de trabajo..."
        );

    } else {

        contenedor.innerHTML = `
            <p class="text-muted">
                Cargando órdenes de trabajo...
            </p>
        `;

    }

    try {

        const resultado =
            await consultarAPIOrdenesTecnico(
                "api/api_ordenes_tecnico.php?action=listar"
            );

        ordenesTecnico =
            extraerOrdenesTecnico(
                resultado
            );

        renderOrdenesTecnico(
            ordenesTecnico
        );

    } catch (error) {

        console.error(
            "Error al cargar órdenes:",
            error
        );

        if (
            typeof window.mostrarError ===
            "function"
        ) {

            window.mostrarError(
                "container-ordenes",
                error.message
            );

        } else {

            contenedor.innerHTML = `
                <div class="error-state">

                    <h2>
                        Ocurrió un error
                    </h2>

                    <p>
                        ${escaparHTMLOrdenes(
                            error.message
                        )}
                    </p>

                </div>
            `;

        }

    }

}


/*==================================================
CONSULTAR API DE ÓRDENES
==================================================*/

async function consultarAPIOrdenesTecnico(
    url,
    opciones = {}
) {

    const configuracion = {
        method: "GET",
        cache: "no-store",
        headers: {
            "Accept": "application/json"
        },
        ...opciones
    };

    const respuesta = await fetch(
        url,
        configuracion
    );

    const textoRespuesta =
        await respuesta.text();

    let resultado;

    try {

        resultado =
            JSON.parse(textoRespuesta);

    } catch (error) {

        console.error(
            "Respuesta recibida de órdenes:",
            textoRespuesta
        );

        throw new Error(
            "La API de órdenes devolvió una respuesta inválida."
        );

    }

    if (!respuesta.ok) {

        throw new Error(
            resultado.mensaje ||
            resultado.message ||
            "No se pudo completar la operación."
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
            "No se pudo completar la operación."
        );

    }

    return resultado;

}


/*==================================================
EXTRAER ARREGLO DE ÓRDENES
==================================================*/

function extraerOrdenesTecnico(
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
        Array.isArray(resultado.ordenes)
    ) {

        return resultado.ordenes;

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
RENDERIZAR ÓRDENES
==================================================*/

function renderOrdenesTecnico(
    ordenes
) {

    const contenedor =
        document.getElementById(
            "container-ordenes"
        );

    if (!contenedor) {
        return;
    }

    if (
        !Array.isArray(ordenes) ||
        ordenes.length === 0
    ) {

        if (
            typeof window.mostrarVacio ===
            "function"
        ) {

            window.mostrarVacio(
                "container-ordenes",
                "No hay órdenes",
                "Todavía no existen órdenes de trabajo registradas."
            );

        } else {

            contenedor.innerHTML = `
                <div class="empty-state">

                    <h2>
                        No hay órdenes
                    </h2>

                    <p>
                        Todavía no existen órdenes
                        de trabajo registradas.
                    </p>

                </div>
            `;

        }

        return;

    }

    contenedor.innerHTML = `
        <div class="orders-grid">

            ${ordenes
                .map(
                    orden =>
                        crearTarjetaOrdenTecnico(
                            orden
                        )
                )
                .join("")}

        </div>
    `;

}
/*==================================================
CREAR TARJETA DE ORDEN
==================================================*/

function crearTarjetaOrdenTecnico(
    orden
) {

    const idOrden =
        obtenerValorOrden(
            orden,
            [
                "Id_orden",
                "id_orden"
            ],
            0
        );

    const idVehiculo =
        obtenerValorOrden(
            orden,
            [
                "Id_vehiculo",
                "id_vehiculo"
            ],
            ""
        );

    const idUsuario =
        obtenerValorOrden(
            orden,
            [
                "Id_usuario",
                "id_usuario"
            ],
            ""
        );

    const cliente =
        obtenerNombreClienteOrden(
            orden
        );

    const vehiculo =
        obtenerDescripcionVehiculoOrden(
            orden
        );

    const placa =
        obtenerValorOrden(
            orden,
            [
                "Placa",
                "placa"
            ],
            "Sin placa"
        );

    const servicio =
        obtenerValorOrden(
            orden,
            [
                "Servicio",
                "servicio",
                "Tipo_servicio",
                "tipo_servicio",
                "Motivo",
                "motivo"
            ],
            "Servicio no especificado"
        );

    const descripcion =
        obtenerValorOrden(
            orden,
            [
                "Descripcion",
                "descripcion",
                "Problema",
                "problema",
                "Observaciones",
                "observaciones"
            ],
            "Sin descripción registrada."
        );

    const fechaIngreso =
        obtenerValorOrden(
            orden,
            [
                "Fecha_ingreso",
                "fecha_ingreso",
                "Fecha",
                "fecha"
            ],
            ""
        );

    const fechaEntrega =
        obtenerValorOrden(
            orden,
            [
                "Fecha_entrega",
                "fecha_entrega",
                "Fecha_salida",
                "fecha_salida"
            ],
            ""
        );

    const estado =
        obtenerValorOrden(
            orden,
            [
                "Estado",
                "estado",
                "Nombre_estado",
                "nombre_estado"
            ],
            "Recibida"
        );

    const kilometraje =
        obtenerValorOrden(
            orden,
            [
                "Kilometraje",
                "kilometraje"
            ],
            ""
        );

    const costo =
        obtenerValorOrden(
            orden,
            [
                "Costo",
                "costo",
                "Costo_total",
                "costo_total",
                "Total",
                "total"
            ],
            ""
        );

    const clienteTexto =
        cliente !== ""
            ? cliente
            : (
                idUsuario !== ""
                    ? `Usuario #${idUsuario}`
                    : "Cliente no registrado"
            );

    return `
        <article
            class="order-card"
            data-id-orden="${escaparHTMLOrdenes(
                idOrden
            )}"
            data-estado="${escaparHTMLOrdenes(
                normalizarTextoOrdenes(
                    estado
                )
            )}"
        >

            <div class="order-card-header">

                <div>

                    <span class="order-number">

                        Orden #${escaparHTMLOrdenes(
                            idOrden
                        )}

                    </span>

                    <h2 class="order-title">

                        ${escaparHTMLOrdenes(
                            servicio
                        )}

                    </h2>

                </div>

                <span class="${obtenerClaseEstadoOrden(
                    estado
                )}">

                    ${escaparHTMLOrdenes(
                        estado
                    )}

                </span>

            </div>

            <div class="order-card-body">

                <div class="order-main-data">

                    <p>

                        <strong>
                            Cliente:
                        </strong>

                        ${escaparHTMLOrdenes(
                            clienteTexto
                        )}

                    </p>

                    <p>

                        <strong>
                            Vehículo:
                        </strong>

                        ${escaparHTMLOrdenes(
                            vehiculo ||
                            (
                                idVehiculo !== ""
                                    ? `Vehículo #${idVehiculo}`
                                    : "No registrado"
                            )
                        )}

                    </p>

                    <p>

                        <strong>
                            Placa:
                        </strong>

                        ${escaparHTMLOrdenes(
                            placa
                        )}

                    </p>

                    <p>

                        <strong>
                            Fecha de ingreso:
                        </strong>

                        ${escaparHTMLOrdenes(
                            formatearFechaOrden(
                                fechaIngreso
                            )
                        )}

                    </p>

                    <p>

                        <strong>
                            Fecha de entrega:
                        </strong>

                        ${escaparHTMLOrdenes(
                            formatearFechaOrden(
                                fechaEntrega
                            )
                        )}

                    </p>

                    ${
                        kilometraje !== ""
                            ? `
                                <p>

                                    <strong>
                                        Kilometraje:
                                    </strong>

                                    ${escaparHTMLOrdenes(
                                        formatearKilometrajeOrden(
                                            kilometraje
                                        )
                                    )}

                                </p>
                            `
                            : ""
                    }

                    ${
                        costo !== ""
                            ? `
                                <p>

                                    <strong>
                                        Costo:
                                    </strong>

                                    ${escaparHTMLOrdenes(
                                        formatearPrecioOrden(
                                            costo
                                        )
                                    )}

                                </p>
                            `
                            : ""
                    }

                </div>

                <div class="order-description">

                    <strong>
                        Descripción:
                    </strong>

                    <p>
                        ${escaparHTMLOrdenes(
                            descripcion
                        )}
                    </p>

                </div>

                <div class="order-state-control">

                    <label
                        for="estado-orden-${escaparHTMLOrdenes(
                            idOrden
                        )}"
                    >

                        Cambiar estado

                    </label>

                    <select
                        id="estado-orden-${escaparHTMLOrdenes(
                            idOrden
                        )}"
                        class="form-control"
                    >

                        ${crearOpcionesEstadoOrden(
                            estado
                        )}

                    </select>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="actualizarEstadoOrdenTecnico(${Number(
                            idOrden
                        )})"
                    >

                        Guardar estado

                    </button>

                </div>

            </div>

            <div class="order-card-actions">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="verDetalleOrdenTecnico(${Number(
                        idOrden
                    )})"
                >

                    Ver detalles

                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="verHistorialOrdenDesdeOrdenes(${Number(
                        idOrden
                    )})"
                >

                    Ver historial

                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="generarReporteOrdenTecnico(${Number(
                        idOrden
                    )})"
                >

                    Generar PDF

                </button>

            </div>

        </article>
    `;

}


/*==================================================
CREAR OPCIONES DE ESTADO
==================================================*/

function crearOpcionesEstadoOrden(
    estadoActual
) {

    const estados = [
        "Recibida",
    "En Diagnostico",
    "Esperando Repuesto",
    "En Reparacion",
    "Prueba de Ruta",
    "Lista para Entrega",
    "Entregada",
    "Cancelada",
    "Garantia",
    "Finalizada"
    ];

    const estadoNormalizado =
        normalizarTextoOrdenes(
            estadoActual
        );

    const existeEstado =
        estados.some(
            estado =>
                normalizarTextoOrdenes(
                    estado
                ) === estadoNormalizado
        );

    if (
        !existeEstado &&
        String(estadoActual).trim() !== ""
    ) {

        estados.unshift(
            String(estadoActual).trim()
        );

    }

    return estados
        .map(
            estado => {

                const seleccionado =
                    normalizarTextoOrdenes(
                        estado
                    ) === estadoNormalizado
                        ? "selected"
                        : "";

                return `
                    <option
                        value="${escaparHTMLOrdenes(
                            estado
                        )}"
                        ${seleccionado}
                    >

                        ${escaparHTMLOrdenes(
                            estado
                        )}

                    </option>
                `;

            }
        )
        .join("");

}
/*==================================================
ACTUALIZAR ESTADO DE UNA ORDEN
==================================================*/

async function actualizarEstadoOrdenTecnico(
    idOrden
) {

    idOrden = Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        mostrarAlertaOrdenes(
            "El ID de la orden no es válido.",
            "error"
        );

        return;

    }

    const selectEstado =
        document.getElementById(
            `estado-orden-${idOrden}`
        );

    if (!selectEstado) {

        mostrarAlertaOrdenes(
            "No se encontró el selector de estado.",
            "error"
        );

        return;

    }

    const nuevoEstado =
        String(
            selectEstado.value || ""
        ).trim();

    if (nuevoEstado === "") {

        mostrarAlertaOrdenes(
            "Selecciona un estado válido.",
            "warning"
        );

        return;

    }

    const boton =
        selectEstado
            .closest(".order-state-control")
            ?.querySelector("button");

    const textoOriginal =
        boton
            ? boton.innerHTML
            : "";

    try {

        if (boton) {

            boton.disabled = true;

            boton.innerHTML = `
                Guardando...
            `;

        }

        const datos =
            new FormData();

        datos.append(
            "id",
            String(idOrden)
        );

        datos.append(
            "id_orden",
            String(idOrden)
        );

        datos.append(
            "Id_orden",
            String(idOrden)
        );

        datos.append(
            "estado",
            nuevoEstado
        );

        datos.append(
            "nombre_estado",
            nuevoEstado
        );

        const resultado =
            await consultarAPIOrdenesTecnico(
                "api/api_ordenes_tecnico.php?action=actualizar_estado",
                {
                    method: "POST",
                    body: datos
                }
            );

        mostrarAlertaOrdenes(
            resultado.mensaje ||
            resultado.message ||
            "El estado de la orden se actualizó correctamente.",
            "success"
        );

        await cargarOrdenesTecnico();

    } catch (error) {

        console.error(
            "Error al actualizar estado:",
            error
        );

        mostrarAlertaOrdenes(
            error.message,
            "error"
        );

    } finally {

        if (boton) {

            boton.disabled = false;
            boton.innerHTML = textoOriginal;

        }

    }

}


/*==================================================
VER DETALLE DE ORDEN
==================================================*/

function verDetalleOrdenTecnico(
    idOrden
) {

    idOrden = Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        mostrarAlertaOrdenes(
            "El ID de la orden no es válido.",
            "error"
        );

        return;

    }

    const orden =
        ordenesTecnico.find(
            item =>
                Number(
                    obtenerValorOrden(
                        item,
                        [
                            "Id_orden",
                            "id_orden"
                        ],
                        0
                    )
                ) === idOrden
        );

    if (!orden) {

        mostrarAlertaOrdenes(
            "No se encontró la orden seleccionada.",
            "error"
        );

        return;

    }

    abrirModalDetalleOrdenTecnico(
        orden
    );

}


/*==================================================
ABRIR MODAL DE DETALLE
==================================================*/

function abrirModalDetalleOrdenTecnico(
    orden
) {

    const idOrden =
        obtenerValorOrden(
            orden,
            [
                "Id_orden",
                "id_orden"
            ],
            0
        );

    const cliente =
        obtenerNombreClienteOrden(
            orden
        );

    const vehiculo =
        obtenerDescripcionVehiculoOrden(
            orden
        );

    const placa =
        obtenerValorOrden(
            orden,
            [
                "Placa",
                "placa"
            ],
            "Sin placa"
        );

    const estado =
        obtenerValorOrden(
            orden,
            [
                "Estado",
                "estado",
                "Nombre_estado",
                "nombre_estado"
            ],
            "Recibida"
        );

    const servicio =
        obtenerValorOrden(
            orden,
            [
                "Servicio",
                "servicio",
                "Tipo_servicio",
                "tipo_servicio",
                "Motivo",
                "motivo"
            ],
            "Servicio no especificado"
        );

    const descripcion =
        obtenerValorOrden(
            orden,
            [
                "Descripcion",
                "descripcion",
                "Problema",
                "problema",
                "Observaciones",
                "observaciones"
            ],
            "Sin descripción registrada."
        );

    const diagnostico =
        obtenerValorOrden(
            orden,
            [
                "Diagnostico",
                "diagnostico"
            ],
            "Sin diagnóstico registrado."
        );

    const solucion =
        obtenerValorOrden(
            orden,
            [
                "Solucion",
                "solucion",
                "Trabajo_realizado",
                "trabajo_realizado"
            ],
            "Sin trabajo realizado registrado."
        );

    const fechaIngreso =
        obtenerValorOrden(
            orden,
            [
                "Fecha_ingreso",
                "fecha_ingreso",
                "Fecha",
                "fecha"
            ],
            ""
        );

    const fechaEntrega =
        obtenerValorOrden(
            orden,
            [
                "Fecha_entrega",
                "fecha_entrega",
                "Fecha_salida",
                "fecha_salida"
            ],
            ""
        );

    const kilometraje =
        obtenerValorOrden(
            orden,
            [
                "Kilometraje",
                "kilometraje"
            ],
            ""
        );

    const costo =
        obtenerValorOrden(
            orden,
            [
                "Costo",
                "costo",
                "Costo_total",
                "costo_total",
                "Total",
                "total"
            ],
            ""
        );

    cerrarModalDetalleOrdenTecnico();

    const overlay =
        document.createElement(
            "div"
        );

    overlay.id =
        "modalDetalleOrdenTecnico";

    overlay.className =
        "modal-overlay";

    overlay.innerHTML = `
        <div
            class="modal-content order-detail-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tituloDetalleOrdenTecnico"
        >

            <div class="modal-header">

                <div>

                    <span class="order-number">
                        Orden #${escaparHTMLOrdenes(
                            idOrden
                        )}
                    </span>

                    <h2 id="tituloDetalleOrdenTecnico">
                        Detalle de la orden
                    </h2>

                </div>

                <button
                    type="button"
                    class="modal-close"
                    aria-label="Cerrar"
                    onclick="cerrarModalDetalleOrdenTecnico()"
                >
                    &times;
                </button>

            </div>

            <div class="modal-body">

                <div class="detail-grid">

                    <div class="detail-item">

                        <span class="detail-label">
                            Cliente
                        </span>

                        <span class="detail-value">
                            ${escaparHTMLOrdenes(
                                cliente ||
                                "Cliente no registrado"
                            )}
                        </span>

                    </div>

                    <div class="detail-item">

                        <span class="detail-label">
                            Vehículo
                        </span>

                        <span class="detail-value">
                            ${escaparHTMLOrdenes(
                                vehiculo ||
                                "Vehículo no registrado"
                            )}
                        </span>

                    </div>

                    <div class="detail-item">

                        <span class="detail-label">
                            Placa
                        </span>

                        <span class="detail-value">
                            ${escaparHTMLOrdenes(
                                placa
                            )}
                        </span>

                    </div>

                    <div class="detail-item">

                        <span class="detail-label">
                            Estado
                        </span>

                        <span class="${obtenerClaseEstadoOrden(
                            estado
                        )}">
                            ${escaparHTMLOrdenes(
                                estado
                            )}
                        </span>

                    </div>

                    <div class="detail-item">

                        <span class="detail-label">
                            Servicio
                        </span>

                        <span class="detail-value">
                            ${escaparHTMLOrdenes(
                                servicio
                            )}
                        </span>

                    </div>

                    <div class="detail-item">

                        <span class="detail-label">
                            Fecha de ingreso
                        </span>

                        <span class="detail-value">
                            ${escaparHTMLOrdenes(
                                formatearFechaOrden(
                                    fechaIngreso
                                )
                            )}
                        </span>

                    </div>

                    <div class="detail-item">

                        <span class="detail-label">
                            Fecha de entrega
                        </span>

                        <span class="detail-value">
                            ${escaparHTMLOrdenes(
                                formatearFechaOrden(
                                    fechaEntrega
                                )
                            )}
                        </span>

                    </div>

                    ${
                        kilometraje !== ""
                            ? `
                                <div class="detail-item">

                                    <span class="detail-label">
                                        Kilometraje
                                    </span>

                                    <span class="detail-value">
                                        ${escaparHTMLOrdenes(
                                            formatearKilometrajeOrden(
                                                kilometraje
                                            )
                                        )}
                                    </span>

                                </div>
                            `
                            : ""
                    }

                    ${
                        costo !== ""
                            ? `
                                <div class="detail-item">

                                    <span class="detail-label">
                                        Costo
                                    </span>

                                    <span class="detail-value">
                                        ${escaparHTMLOrdenes(
                                            formatearPrecioOrden(
                                                costo
                                            )
                                        )}
                                    </span>

                                </div>
                            `
                            : ""
                    }

                </div>

                <div class="detail-section">

                    <h3>
                        Descripción
                    </h3>

                    <p>
                        ${escaparHTMLOrdenes(
                            descripcion
                        )}
                    </p>

                </div>

                <div class="detail-section">

                    <h3>
                        Diagnóstico
                    </h3>

                    <p>
                        ${escaparHTMLOrdenes(
                            diagnostico
                        )}
                    </p>

                </div>

                <div class="detail-section">

                    <h3>
                        Trabajo realizado
                    </h3>

                    <p>
                        ${escaparHTMLOrdenes(
                            solucion
                        )}
                    </p>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="cerrarModalDetalleOrdenTecnico()"
                >
                    Cerrar
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="generarReporteOrdenTecnico(${Number(
                        idOrden
                    )})"
                >
                    Generar PDF
                </button>

            </div>

        </div>
    `;

    document.body.appendChild(
        overlay
    );

    document.body.classList.add(
        "modal-open"
    );

    overlay.addEventListener(
        "click",
        evento => {

            if (evento.target === overlay) {

                cerrarModalDetalleOrdenTecnico();

            }

        }
    );

}


/*==================================================
CERRAR MODAL DE DETALLE
==================================================*/

function cerrarModalDetalleOrdenTecnico() {

    const modal =
        document.getElementById(
            "modalDetalleOrdenTecnico"
        );

    if (modal) {

        modal.remove();

    }

    document.body.classList.remove(
        "modal-open"
    );

}
/*==================================================
GENERAR REPORTE PDF
==================================================*/

function generarReporteOrdenTecnico(
    idOrden
) {

    idOrden = Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        mostrarAlertaOrdenes(
            "El ID de la orden no es válido.",
            "error"
        );

        return;

    }

    const url =
        `reportes/reporte.php?id_orden=${encodeURIComponent(
            idOrden
        )}`;

    window.open(
        url,
        "_blank",
        "noopener,noreferrer"
    );

}


/*==================================================
VER HISTORIAL DESDE ÓRDENES
==================================================*/

function verHistorialOrdenDesdeOrdenes(
    idOrden
) {

    idOrden = Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        mostrarAlertaOrdenes(
            "El ID de la orden no es válido.",
            "error"
        );

        return;

    }

    if (
        typeof window.cambiarTab ===
        "function"
    ) {

        window.cambiarTab(
            "historial"
        );

    }

    if (
        typeof window.cargarHistorialTecnico ===
        "function"
    ) {

        window.cargarHistorialTecnico(
            idOrden
        );

        return;

    }

    mostrarAlertaOrdenes(
        "El módulo de historial todavía no está disponible.",
        "warning"
    );

}


/*==================================================
INICIAR FORMULARIO DE ORDEN
==================================================*/

function iniciarFormularioOrdenTecnico() {

    const formulario =
        document.getElementById(
            "formOrdenTecnico"
        );

    if (!formulario) {
        return;
    }

    formulario.addEventListener(
        "submit",
        guardarOrdenTecnico
    );

}


/*==================================================
GUARDAR ORDEN
==================================================*/

async function guardarOrdenTecnico(
    evento
) {

    evento.preventDefault();

    const formulario =
        evento.currentTarget;

    if (!(formulario instanceof HTMLFormElement)) {
        return;
    }

    const datos =
        new FormData(formulario);

    const idOrden =
        obtenerCampoFormularioOrden(
            formulario,
            [
                "id",
                "id_orden",
                "Id_orden"
            ]
        );

    const accion =
        idOrden !== ""
            ? "editar"
            : "guardar";

    const botonGuardar =
        formulario.querySelector(
            'button[type="submit"]'
        );

    const textoOriginal =
        botonGuardar
            ? botonGuardar.innerHTML
            : "";

    try {

        if (botonGuardar) {

            botonGuardar.disabled = true;

            botonGuardar.innerHTML = `
                Guardando...
            `;

        }

        if (idOrden !== "") {

            datos.set(
                "id",
                idOrden
            );

            datos.set(
                "id_orden",
                idOrden
            );

            datos.set(
                "Id_orden",
                idOrden
            );

        }

        const resultado =
            await consultarAPIOrdenesTecnico(
                `api/api_ordenes_tecnico.php?action=${accion}`,
                {
                    method: "POST",
                    body: datos
                }
            );

        mostrarAlertaOrdenes(
            resultado.mensaje ||
            resultado.message ||
            (
                accion === "editar"
                    ? "La orden fue actualizada correctamente."
                    : "La orden fue registrada correctamente."
            ),
            "success"
        );

        formulario.reset();

        ordenTecnicoEditando = null;

        cerrarModalFormularioOrdenTecnico();

        await cargarOrdenesTecnico();

    } catch (error) {

        console.error(
            "Error al guardar la orden:",
            error
        );

        mostrarAlertaOrdenes(
            error.message,
            "error"
        );

    } finally {

        if (botonGuardar) {

            botonGuardar.disabled = false;
            botonGuardar.innerHTML =
                textoOriginal;

        }

    }

}


/*==================================================
OBTENER CAMPO DEL FORMULARIO
==================================================*/

function obtenerCampoFormularioOrden(
    formulario,
    nombres
) {

    for (const nombre of nombres) {

        const campo =
            formulario.elements.namedItem(
                nombre
            );

        if (
            campo &&
            "value" in campo
        ) {

            const valor =
                String(
                    campo.value || ""
                ).trim();

            if (valor !== "") {

                return valor;

            }

        }

    }

    return "";

}


/*==================================================
ABRIR MODAL DE FORMULARIO
==================================================*/

function abrirModalFormularioOrdenTecnico(
    orden = null
) {

    const modal =
        document.getElementById(
            "modalOrdenTecnico"
        );

    if (!modal) {

        mostrarAlertaOrdenes(
            "No se encontró el formulario de órdenes.",
            "error"
        );

        return;

    }

    ordenTecnicoEditando =
        orden || null;

    completarFormularioOrdenTecnico(
        orden
    );

    modal.classList.add(
        "active"
    );

    modal.setAttribute(
        "aria-hidden",
        "false"
    );

    document.body.classList.add(
        "modal-open"
    );

}


/*==================================================
CERRAR MODAL DEL FORMULARIO
==================================================*/

function cerrarModalFormularioOrdenTecnico() {

    const modal =
        document.getElementById(
            "modalOrdenTecnico"
        );

    if (!modal) {
        return;
    }

    modal.classList.remove(
        "active"
    );

    modal.setAttribute(
        "aria-hidden",
        "true"
    );

    document.body.classList.remove(
        "modal-open"
    );

    const formulario =
        document.getElementById(
            "formOrdenTecnico"
        );

    if (formulario) {

        formulario.reset();

    }

    ordenTecnicoEditando = null;

}


/*==================================================
COMPLETAR FORMULARIO
==================================================*/

function completarFormularioOrdenTecnico(
    orden
) {

    const formulario =
        document.getElementById(
            "formOrdenTecnico"
        );

    if (!formulario) {
        return;
    }

    formulario.reset();

    if (!orden) {

        asignarCampoFormularioOrden(
            formulario,
            [
                "id",
                "id_orden",
                "Id_orden"
            ],
            ""
        );

        return;

    }

    asignarCampoFormularioOrden(
        formulario,
        [
            "id",
            "id_orden",
            "Id_orden"
        ],
        obtenerValorOrden(
            orden,
            [
                "Id_orden",
                "id_orden"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "id_vehiculo",
            "Id_vehiculo"
        ],
        obtenerValorOrden(
            orden,
            [
                "Id_vehiculo",
                "id_vehiculo"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "id_usuario",
            "Id_usuario"
        ],
        obtenerValorOrden(
            orden,
            [
                "Id_usuario",
                "id_usuario"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "servicio",
            "Servicio",
            "tipo_servicio",
            "Tipo_servicio"
        ],
        obtenerValorOrden(
            orden,
            [
                "Servicio",
                "servicio",
                "Tipo_servicio",
                "tipo_servicio",
                "Motivo",
                "motivo"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "descripcion",
            "Descripcion",
            "problema",
            "Problema"
        ],
        obtenerValorOrden(
            orden,
            [
                "Descripcion",
                "descripcion",
                "Problema",
                "problema"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "diagnostico",
            "Diagnostico"
        ],
        obtenerValorOrden(
            orden,
            [
                "Diagnostico",
                "diagnostico"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "solucion",
            "Solucion",
            "trabajo_realizado",
            "Trabajo_realizado"
        ],
        obtenerValorOrden(
            orden,
            [
                "Solucion",
                "solucion",
                "Trabajo_realizado",
                "trabajo_realizado"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "fecha_ingreso",
            "Fecha_ingreso"
        ],
        convertirFechaParaInputOrden(
            obtenerValorOrden(
                orden,
                [
                    "Fecha_ingreso",
                    "fecha_ingreso",
                    "Fecha",
                    "fecha"
                ],
                ""
            )
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "fecha_entrega",
            "Fecha_entrega"
        ],
        convertirFechaParaInputOrden(
            obtenerValorOrden(
                orden,
                [
                    "Fecha_entrega",
                    "fecha_entrega",
                    "Fecha_salida",
                    "fecha_salida"
                ],
                ""
            )
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "kilometraje",
            "Kilometraje"
        ],
        obtenerValorOrden(
            orden,
            [
                "Kilometraje",
                "kilometraje"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "costo",
            "Costo",
            "costo_total",
            "Costo_total"
        ],
        obtenerValorOrden(
            orden,
            [
                "Costo",
                "costo",
                "Costo_total",
                "costo_total",
                "Total",
                "total"
            ],
            ""
        )
    );

    asignarCampoFormularioOrden(
        formulario,
        [
            "estado",
            "Estado"
        ],
        obtenerValorOrden(
            orden,
            [
                "Estado",
                "estado",
                "Nombre_estado",
                "nombre_estado"
            ],
            "Recibida"
        )
    );

}
/*==================================================
ASIGNAR VALOR A CAMPO DEL FORMULARIO
==================================================*/

function asignarCampoFormularioOrden(
    formulario,
    nombres,
    valor
) {

    for (const nombre of nombres) {

        const campo =
            formulario.elements.namedItem(
                nombre
            );

        if (
            campo &&
            "value" in campo
        ) {

            campo.value =
                valor === null ||
                valor === undefined
                    ? ""
                    : String(valor);

            return true;

        }

    }

    return false;

}


/*==================================================
EDITAR ORDEN
==================================================*/

function editarOrdenTecnico(
    idOrden
) {

    idOrden = Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        mostrarAlertaOrdenes(
            "El ID de la orden no es válido.",
            "error"
        );

        return;

    }

    const orden =
        ordenesTecnico.find(
            item =>
                Number(
                    obtenerValorOrden(
                        item,
                        [
                            "Id_orden",
                            "id_orden"
                        ],
                        0
                    )
                ) === idOrden
        );

    if (!orden) {

        mostrarAlertaOrdenes(
            "No se encontró la orden seleccionada.",
            "error"
        );

        return;

    }

    abrirModalFormularioOrdenTecnico(
        orden
    );

}


/*==================================================
ELIMINAR ORDEN
==================================================*/

async function eliminarOrdenTecnico(
    idOrden
) {

    idOrden = Number(idOrden);

    if (
        !Number.isInteger(idOrden) ||
        idOrden <= 0
    ) {

        mostrarAlertaOrdenes(
            "El ID de la orden no es válido.",
            "error"
        );

        return;

    }

    const confirmar =
        window.confirm(
            "¿Seguro que deseas eliminar esta orden?"
        );

    if (!confirmar) {
        return;
    }

    try {

        const datos =
            new FormData();

        datos.append(
            "id",
            String(idOrden)
        );

        datos.append(
            "id_orden",
            String(idOrden)
        );

        datos.append(
            "Id_orden",
            String(idOrden)
        );

        const resultado =
            await consultarAPIOrdenesTecnico(
                "api/api_ordenes_tecnico.php?action=eliminar",
                {
                    method: "POST",
                    body: datos
                }
            );

        mostrarAlertaOrdenes(
            resultado.mensaje ||
            resultado.message ||
            "La orden fue eliminada correctamente.",
            "success"
        );

        cerrarModalDetalleOrdenTecnico();

        await cargarOrdenesTecnico();

    } catch (error) {

        console.error(
            "Error al eliminar orden:",
            error
        );

        mostrarAlertaOrdenes(
            error.message,
            "error"
        );

    }

}


/*==================================================
INICIAR BUSCADOR
==================================================*/

function iniciarBuscadorOrdenesTecnico() {

    const buscador =
        document.getElementById(
            "buscarOrdenes"
        ) ||
        document.getElementById(
            "buscadorOrdenes"
        );

    if (!buscador) {
        return;
    }

    buscador.addEventListener(
        "input",
        filtrarOrdenesTecnico
    );

}


/*==================================================
INICIAR FILTROS
==================================================*/

function iniciarFiltrosOrdenesTecnico() {

    const filtroEstado =
        document.getElementById(
            "filtroEstadoOrdenes"
        ) ||
        document.getElementById(
            "filtroEstadoOrden"
        );

    if (filtroEstado) {

        filtroEstado.addEventListener(
            "change",
            filtrarOrdenesTecnico
        );

    }

}


/*==================================================
FILTRAR ÓRDENES
==================================================*/

function filtrarOrdenesTecnico() {

    const buscador =
        document.getElementById(
            "buscarOrdenes"
        ) ||
        document.getElementById(
            "buscadorOrdenes"
        );

    const filtroEstado =
        document.getElementById(
            "filtroEstadoOrdenes"
        ) ||
        document.getElementById(
            "filtroEstadoOrden"
        );

    const textoBusqueda =
        normalizarTextoOrdenes(
            buscador
                ? buscador.value
                : ""
        );

    const estadoSeleccionado =
        normalizarTextoOrdenes(
            filtroEstado
                ? filtroEstado.value
                : ""
        );

    const resultado =
        ordenesTecnico.filter(
            orden => {

                const idOrden =
                    obtenerValorOrden(
                        orden,
                        [
                            "Id_orden",
                            "id_orden"
                        ],
                        ""
                    );

                const cliente =
                    obtenerNombreClienteOrden(
                        orden
                    );

                const vehiculo =
                    obtenerDescripcionVehiculoOrden(
                        orden
                    );

                const placa =
                    obtenerValorOrden(
                        orden,
                        [
                            "Placa",
                            "placa"
                        ],
                        ""
                    );

                const servicio =
                    obtenerValorOrden(
                        orden,
                        [
                            "Servicio",
                            "servicio",
                            "Tipo_servicio",
                            "tipo_servicio",
                            "Motivo",
                            "motivo"
                        ],
                        ""
                    );

                const descripcion =
                    obtenerValorOrden(
                        orden,
                        [
                            "Descripcion",
                            "descripcion",
                            "Problema",
                            "problema",
                            "Observaciones",
                            "observaciones"
                        ],
                        ""
                    );

                const estado =
                    obtenerValorOrden(
                        orden,
                        [
                            "Estado",
                            "estado",
                            "Nombre_estado",
                            "nombre_estado"
                        ],
                        ""
                    );

                const textoCompleto =
                    normalizarTextoOrdenes(
                        [
                            idOrden,
                            cliente,
                            vehiculo,
                            placa,
                            servicio,
                            descripcion,
                            estado
                        ].join(" ")
                    );

                const coincideBusqueda =
                    textoBusqueda === "" ||
                    textoCompleto.includes(
                        textoBusqueda
                    );

                const coincideEstado =
                    estadoSeleccionado === "" ||
                    estadoSeleccionado === "todos" ||
                    normalizarTextoOrdenes(
                        estado
                    ) === estadoSeleccionado;

                return (
                    coincideBusqueda &&
                    coincideEstado
                );

            }
        );

    renderOrdenesTecnico(
        resultado
    );

}


/*==================================================
LIMPIAR FILTROS
==================================================*/

function limpiarFiltrosOrdenesTecnico() {

    const buscador =
        document.getElementById(
            "buscarOrdenes"
        ) ||
        document.getElementById(
            "buscadorOrdenes"
        );

    const filtroEstado =
        document.getElementById(
            "filtroEstadoOrdenes"
        ) ||
        document.getElementById(
            "filtroEstadoOrden"
        );

    if (buscador) {

        buscador.value = "";

    }

    if (filtroEstado) {

        filtroEstado.value = "";

    }

    renderOrdenesTecnico(
        ordenesTecnico
    );

}


/*==================================================
OBTENER NOMBRE DEL CLIENTE
==================================================*/

function obtenerNombreClienteOrden(
    orden
) {

    const nombreCompleto =
        obtenerValorOrden(
            orden,
            [
                "Cliente",
                "cliente",
                "Nombre_cliente",
                "nombre_cliente",
                "NombreCompleto",
                "nombre_completo"
            ],
            ""
        );

    if (nombreCompleto !== "") {

        return String(
            nombreCompleto
        ).trim();

    }

    const nombre =
        obtenerValorOrden(
            orden,
            [
                "Nombre",
                "nombre",
                "Nombre_usuario",
                "nombre_usuario"
            ],
            ""
        );

    const apellido =
        obtenerValorOrden(
            orden,
            [
                "Apellido",
                "apellido",
                "Apellido_usuario",
                "apellido_usuario"
            ],
            ""
        );

    return [
        nombre,
        apellido
    ]
        .filter(
            valor =>
                String(valor).trim() !== ""
        )
        .join(" ")
        .trim();

}


/*==================================================
OBTENER DESCRIPCIÓN DEL VEHÍCULO
==================================================*/

function obtenerDescripcionVehiculoOrden(
    orden
) {

    const descripcionCompleta =
        obtenerValorOrden(
            orden,
            [
                "Vehiculo",
                "vehiculo",
                "Descripcion_vehiculo",
                "descripcion_vehiculo"
            ],
            ""
        );

    if (descripcionCompleta !== "") {

        return String(
            descripcionCompleta
        ).trim();

    }

    const tipo =
        obtenerValorOrden(
            orden,
            [
                "Tipo_vehiculo",
                "tipo_vehiculo",
                "Tipo",
                "tipo"
            ],
            ""
        );

    const marca =
        obtenerValorOrden(
            orden,
            [
                "Marca",
                "marca"
            ],
            ""
        );

    const linea =
        obtenerValorOrden(
            orden,
            [
                "Linea",
                "linea",
                "Línea",
                "línea"
            ],
            ""
        );

    const modelo =
        obtenerValorOrden(
            orden,
            [
                "Modelo",
                "modelo"
            ],
            ""
        );

    return [
        tipo,
        marca,
        linea,
        modelo
    ]
        .filter(
            valor =>
                String(valor).trim() !== ""
        )
        .join(" ")
        .trim();

}
/*==================================================
OBTENER VALOR DE UNA ORDEN
==================================================*/

function obtenerValorOrden(
    objeto,
    propiedades,
    valorDefecto = ""
) {

    if (
        !objeto ||
        typeof objeto !== "object"
    ) {

        return valorDefecto;

    }

    for (const propiedad of propiedades) {

        if (
            Object.prototype.hasOwnProperty.call(
                objeto,
                propiedad
            )
        ) {

            const valor =
                objeto[propiedad];

            if (
                valor !== null &&
                valor !== undefined &&
                String(valor).trim() !== ""
            ) {

                return valor;

            }

        }

    }

    return valorDefecto;

}


/*==================================================
CLASE CSS SEGÚN ESTADO
==================================================*/

function obtenerClaseEstadoOrden(
    estado
) {

    const valor =
        normalizarTextoOrdenes(
            estado
        );

    const clases = {
        "recibida":
            "status-badge status-pending",

        "en diagnostico":
            "status-badge status-diagnostic",

        "esperando repuesto":
            "status-badge status-waiting",

        "en reparacion":
            "status-badge status-progress",

        "prueba de ruta":
            "status-badge status-testing",

        "lista para entrega":
            "status-badge status-ready",

        "entregada":
            "status-badge status-delivered",

        "cancelada":
            "status-badge status-cancelled",

        "garantia":
            "status-badge status-warranty",

        "finalizada":
            "status-badge status-completed"
    };

    return (
        clases[valor] ||
        "status-badge status-default"
    );

}


/*==================================================
FORMATEAR FECHA
==================================================*/

function formatearFechaOrden(
    fecha
) {

    if (
        fecha === null ||
        fecha === undefined ||
        String(fecha).trim() === ""
    ) {

        return "No definida";

    }

    const texto =
        String(fecha).trim();

    const fechaSolo =
        texto.split(" ")[0];

    const partes =
        fechaSolo.split("-");

    if (
        partes.length === 3 &&
        partes[0].length === 4
    ) {

        const [
            anio,
            mes,
            dia
        ] = partes;

        return `${dia}/${mes}/${anio}`;

    }

    const fechaObjeto =
        new Date(texto);

    if (
        Number.isNaN(
            fechaObjeto.getTime()
        )
    ) {

        return texto;

    }

    return fechaObjeto.toLocaleDateString(
        "es-GT",
        {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
        }
    );

}


/*==================================================
CONVERTIR FECHA PARA INPUT
==================================================*/

function convertirFechaParaInputOrden(
    fecha
) {

    if (
        fecha === null ||
        fecha === undefined ||
        String(fecha).trim() === ""
    ) {

        return "";

    }

    const texto =
        String(fecha).trim();

    const coincidencia =
        texto.match(
            /^(\d{4})-(\d{2})-(\d{2})/
        );

    if (coincidencia) {

        return `${coincidencia[1]}-${coincidencia[2]}-${coincidencia[3]}`;

    }

    const objetoFecha =
        new Date(texto);

    if (
        Number.isNaN(
            objetoFecha.getTime()
        )
    ) {

        return "";

    }

    const anio =
        objetoFecha.getFullYear();

    const mes =
        String(
            objetoFecha.getMonth() + 1
        ).padStart(
            2,
            "0"
        );

    const dia =
        String(
            objetoFecha.getDate()
        ).padStart(
            2,
            "0"
        );

    return `${anio}-${mes}-${dia}`;

}


/*==================================================
FORMATEAR KILOMETRAJE
==================================================*/

function formatearKilometrajeOrden(
    kilometraje
) {

    const numero =
        Number(kilometraje);

    if (
        !Number.isFinite(numero)
    ) {

        return String(kilometraje);

    }

    return `${numero.toLocaleString(
        "es-GT"
    )} km`;

}


/*==================================================
FORMATEAR PRECIO
==================================================*/

function formatearPrecioOrden(
    precio
) {

    const numero =
        Number(precio);

    if (
        !Number.isFinite(numero)
    ) {

        return String(precio);

    }

    return numero.toLocaleString(
        "es-GT",
        {
            style: "currency",
            currency: "GTQ",
            minimumFractionDigits: 2
        }
    );

}


/*==================================================
NORMALIZAR TEXTO
==================================================*/

function normalizarTextoOrdenes(
    texto
) {

    return String(
        texto === null ||
        texto === undefined
            ? ""
            : texto
    )
        .normalize("NFD")
        .replace(
            /[\u0300-\u036f]/g,
            ""
        )
        .toLowerCase()
        .trim();

}


/*==================================================
ESCAPAR HTML
==================================================*/

function escaparHTMLOrdenes(
    valor
) {

    return String(
        valor === null ||
        valor === undefined
            ? ""
            : valor
    )
        .replace(
            /&/g,
            "&amp;"
        )
        .replace(
            /</g,
            "&lt;"
        )
        .replace(
            />/g,
            "&gt;"
        )
        .replace(
            /"/g,
            "&quot;"
        )
        .replace(
            /'/g,
            "&#039;"
        );

}


/*==================================================
MOSTRAR ALERTA
==================================================*/

function mostrarAlertaOrdenes(
    mensaje,
    tipo = "info"
) {

    if (
        typeof window.mostrarAlerta ===
        "function"
    ) {

        window.mostrarAlerta(
            mensaje,
            tipo
        );

        return;

    }

    if (
        typeof window.Swal !==
        "undefined"
    ) {

        const iconos = {
            success: "success",
            error: "error",
            warning: "warning",
            info: "info"
        };

        window.Swal.fire({
            text: mensaje,
            icon:
                iconos[tipo] ||
                "info",
            confirmButtonText:
                "Aceptar"
        });

        return;

    }

    window.alert(
        mensaje
    );

}


/*==================================================
CERRAR MODAL CON ESC
==================================================*/

document.addEventListener(
    "keydown",
    evento => {

        if (
            evento.key !==
            "Escape"
        ) {

            return;

        }

        cerrarModalDetalleOrdenTecnico();
        cerrarModalFormularioOrdenTecnico();

    }
);


/*==================================================
EXPONER FUNCIONES GLOBALMENTE
==================================================*/

window.cargarOrdenesTecnico =
    cargarOrdenesTecnico;

window.actualizarEstadoOrdenTecnico =
    actualizarEstadoOrdenTecnico;

window.verDetalleOrdenTecnico =
    verDetalleOrdenTecnico;

window.cerrarModalDetalleOrdenTecnico =
    cerrarModalDetalleOrdenTecnico;

window.generarReporteOrdenTecnico =
    generarReporteOrdenTecnico;

window.verHistorialOrdenDesdeOrdenes =
    verHistorialOrdenDesdeOrdenes;

window.abrirModalFormularioOrdenTecnico =
    abrirModalFormularioOrdenTecnico;

window.cerrarModalFormularioOrdenTecnico =
    cerrarModalFormularioOrdenTecnico;

window.editarOrdenTecnico =
    editarOrdenTecnico;

window.eliminarOrdenTecnico =
    eliminarOrdenTecnico;

window.filtrarOrdenesTecnico =
    filtrarOrdenesTecnico;

window.limpiarFiltrosOrdenesTecnico =
    limpiarFiltrosOrdenesTecnico;