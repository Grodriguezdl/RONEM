//==================================================
// VARIABLES GLOBALES
//==================================================

let solicitudes = [];
let estadosSolicitudes = [];
let solicitudActual = null;

//==================================================
// ELEMENTOS DEL HTML
//==================================================

function obtenerElemento(id) {
    return document.getElementById(id);
}

//==================================================
// MOSTRAR MENSAJES
//==================================================

function mostrarErrorSolicitud(mensaje) {
    alert(
        mensaje ||
        "Ocurrió un error al procesar la solicitud."
    );
}

//==================================================
// CARGAR SOLICITUDES
//==================================================

async function cargarSolicitudes() {

    const tbody = obtenerElemento("tbodySolicitudes");

    if (tbody) {

        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center">
                    Cargando solicitudes...
                </td>
            </tr>
        `;
    }

    try {

        const respuesta = await fetch(
            "api/api_solicitudes.php?action=listar",
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let datos;

        try {

            datos = JSON.parse(textoRespuesta);

        } catch (errorJSON) {

            console.error(
                "Respuesta recibida de la API:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió una respuesta inválida."
            );
        }

        if (!respuesta.ok || datos.success === false) {

            throw new Error(
                datos.error ||
                "No se pudieron cargar las solicitudes."
            );
        }

        solicitudes =
            Array.isArray(datos)
                ? datos
                : [];

        renderSolicitudes();

    } catch (error) {

        console.error(
            "Error al cargar solicitudes:",
            error
        );

        if (tbody) {

            tbody.innerHTML = `
                <tr>
                    <td
                        colspan="10"
                        class="text-center text-danger"
                    >
                        ${escaparHTML(
                            error.message ||
                            "No se pudieron cargar las solicitudes."
                        )}
                    </td>
                </tr>
            `;
        }

        const chip =
            obtenerElemento("chipSolicitudes");

        if (chip) {
            chip.textContent = "0 solicitudes";
        }
    }
}

//==================================================
// RENDERIZAR SOLICITUDES
//==================================================

function renderSolicitudes() {

    const tbody =
        obtenerElemento("tbodySolicitudes");

    const chip =
        obtenerElemento("chipSolicitudes");

    const buscador =
        obtenerElemento("buscarSolicitud");

    if (!tbody) {

        console.warn(
            "No existe el elemento #tbodySolicitudes."
        );

        return;
    }

    const textoBusqueda = buscador
        ? buscador.value.trim().toLowerCase()
        : "";

    const solicitudesFiltradas =
        solicitudes.filter((solicitud) => {

            const contenido = `
                ${solicitud.Id_solicitud || ""}
                ${solicitud.Id_usuario || ""}
                ${solicitud.Nombre || ""}
                ${solicitud.Apellido || ""}
                ${solicitud.DPI || ""}
                ${solicitud.Telefono || ""}
                ${solicitud.Correo || ""}
                ${solicitud.Nivel || ""}
                ${solicitud.TipoLicencia || ""}
                ${solicitud.Estado || ""}
            `.toLowerCase();

            return contenido.includes(
                textoBusqueda
            );
        });

    if (chip) {

        chip.textContent =
            `${solicitudesFiltradas.length} solicitud` +
            `${solicitudesFiltradas.length === 1
                ? ""
                : "es"
            }`;
    }

    if (solicitudesFiltradas.length === 0) {

        tbody.innerHTML = `
            <tr class="empty-row">
                <td
                    colspan="10"
                    class="text-center"
                >
                    No hay solicitudes registradas.
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML =
        solicitudesFiltradas
            .map((solicitud) => {

                const claseEstado =
                    obtenerClaseEstado(
                        solicitud.Estado
                    );

                const idSolicitud =
                    Number(
                        solicitud.Id_solicitud
                    );

                return `
                    <tr>

                        <td>
                            ${escaparHTML(
                                solicitud.Id_solicitud
                            )}
                        </td>

                        <td>
                            ${escaparHTML(
                                solicitud.Nombre
                            )}
                            ${escaparHTML(
                                solicitud.Apellido
                            )}
                        </td>

                        <td>
                            ${escaparHTML(
                                solicitud.DPI
                            )}
                        </td>

                        <td>
                            ${escaparHTML(
                                solicitud.Telefono
                            )}
                        </td>

                        <td>
                            ${escaparHTML(
                                solicitud.Correo
                            )}
                        </td>

                        <td>
                            ${escaparHTML(
                                solicitud.Nivel
                            )}
                        </td>

                        <td>
                            ${escaparHTML(
                                solicitud.TipoLicencia
                            )}
                        </td>

                        <td>
                            ${formatearFecha(
                                solicitud.Fecha_solicitud
                            )}
                        </td>

                        <td>
                            <span class="badge ${claseEstado}">
                                ${escaparHTML(
                                    solicitud.Estado
                                )}
                            </span>
                        </td>

                        <td>

                            <button
                                type="button"
                                class="btn btn-primary btn-sm me-1"
                                onclick="verSolicitud(${idSolicitud})"
                                title="Ver solicitud"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                            <button
                                type="button"
                                class="btn btn-danger btn-sm"
                                onclick="eliminarSolicitud(${idSolicitud})"
                                title="Eliminar solicitud"
                            >
                                <i class="bi bi-trash"></i>
                            </button>

                        </td>

                    </tr>
                `;
            })
            .join("");
}

//==================================================
// CARGAR ESTADOS
//==================================================

async function cargarEstadosSolicitudes() {

    try {

        const respuesta = await fetch(
            "api/api_solicitudes.php?action=estados",
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let datos;

        try {

            datos = JSON.parse(textoRespuesta);

        } catch (errorJSON) {

            console.error(
                "Respuesta de estados:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió estados inválidos."
            );
        }

        if (!respuesta.ok || datos.success === false) {

            throw new Error(
                datos.error ||
                "No se pudieron cargar los estados."
            );
        }

        estadosSolicitudes =
            Array.isArray(datos)
                ? datos
                : [];

        llenarSelectEstados();

    } catch (error) {

        console.error(
            "Error al cargar estados:",
            error
        );
    }
}

//==================================================
// LLENAR SELECT DE ESTADOS
//==================================================

function llenarSelectEstados() {

    const select =
        obtenerElemento("sEstadoSelect");

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un estado
        </option>
    `;

    estadosSolicitudes.forEach((estado) => {

        const option =
            document.createElement("option");

        option.value =
            estado.Id_estado;

        option.textContent =
            estado.Nombre;

        select.appendChild(option);
    });
}

//==================================================
// VER DETALLE
//==================================================

async function verSolicitud(id) {

    solicitudActual = id;

    try {

        const respuesta = await fetch(
            "api/api_solicitudes.php?action=detalle&id=" +
            encodeURIComponent(id),
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let datos;

        try {

            datos = JSON.parse(textoRespuesta);

        } catch (errorJSON) {

            console.error(
                "Respuesta de detalle:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió un detalle inválido."
            );
        }

        if (!respuesta.ok || datos.success === false) {

            throw new Error(
                datos.error ||
                "No se pudo obtener la solicitud."
            );
        }

        const solicitud =
            datos.solicitud;

        colocarValor(
            "sNombre",
            solicitud.Nombre
        );

        colocarValor(
            "sApellido",
            solicitud.Apellido
        );

        colocarValor(
            "sDpi",
            solicitud.DPI
        );

        colocarValor(
            "sTelefono",
            solicitud.Telefono
        );

        colocarValor(
            "sCorreo",
            solicitud.Correo
        );

        colocarValor(
            "sNivel",
            solicitud.Nivel
        );

        colocarValor(
            "sTipoLic",
            solicitud.TipoLicencia
        );

        colocarValor(
            "sFecha",
            formatearFecha(
                solicitud.Fecha_solicitud
            )
        );

        const estadoSelect =
            obtenerElemento("sEstadoSelect");

        if (estadoSelect) {

            estadoSelect.value =
                solicitud.Id_estado || "";
        }

        const observacion =
            obtenerElemento("sObservacion");

        if (observacion) {
            observacion.value = "";
        }

        await cargarHistorialSolicitud(id);

        const modalElemento =
            obtenerElemento("modalSolicitud");

        if (!modalElemento) {

            mostrarErrorSolicitud(
                "No se encontró el modal de solicitudes."
            );

            return;
        }

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElemento
            );

        modal.show();

    } catch (error) {

        console.error(
            "Error al obtener solicitud:",
            error
        );

        mostrarErrorSolicitud(
            error.message
        );
    }
}

//==================================================
// CARGAR HISTORIAL
//==================================================

async function cargarHistorialSolicitud(id) {

    const contenedor =
        obtenerElemento("historialSolicitud");

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div class="text-muted">
            Cargando historial...
        </div>
    `;

    try {

        const respuesta = await fetch(
            "api/api_solicitudes.php?action=historial&id=" +
            encodeURIComponent(id),
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let datos;

        try {

            datos = JSON.parse(textoRespuesta);

        } catch (errorJSON) {

            console.error(
                "Respuesta de historial:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió un historial inválido."
            );
        }

        if (!respuesta.ok || datos.success === false) {

            throw new Error(
                datos.error ||
                "No se pudo cargar el historial."
            );
        }

        const historial =
            Array.isArray(datos.historial)
                ? datos.historial
                : [];

        if (historial.length === 0) {

            contenedor.innerHTML = `
                <div class="text-muted">
                    No hay cambios registrados.
                </div>
            `;

            return;
        }

        contenedor.innerHTML =
            historial.map((registro) => `
                <div class="border rounded p-2 mb-2">

                    <div>
                        <strong>Estado:</strong>
                        ${escaparHTML(
                            registro.Estado ||
                            "Sin estado"
                        )}
                    </div>

                    <div>
                        <strong>Fecha:</strong>
                        ${formatearFecha(
                            registro.Fecha_cambio
                        )}
                    </div>

                    ${
                        registro.Observacion
                            ? `
                                <div>
                                    <strong>
                                        Observación:
                                    </strong>

                                    ${escaparHTML(
                                        registro.Observacion
                                    )}
                                </div>
                            `
                            : ""
                    }

                </div>
            `).join("");

    } catch (error) {

        console.error(
            "Error al cargar historial:",
            error
        );

        contenedor.innerHTML = `
            <div class="text-danger">
                ${escaparHTML(
                    error.message ||
                    "No se pudo cargar el historial."
                )}
            </div>
        `;
    }
}

//==================================================
// ACTUALIZAR ESTADO
//==================================================

async function actualizarEstadoSolicitud() {

    if (!solicitudActual) {

        mostrarErrorSolicitud(
            "No se ha seleccionado ninguna solicitud."
        );

        return;
    }

    const estadoSelect =
        obtenerElemento("sEstadoSelect");

    const observacionInput =
        obtenerElemento("sObservacion");

    const boton =
        obtenerElemento(
            "btnActualizarSolicitud"
        );

    const idEstado =
        estadoSelect
            ? estadoSelect.value
            : "";

    const observacion =
        observacionInput
            ? observacionInput.value.trim()
            : "";

    if (!idEstado) {

        mostrarErrorSolicitud(
            "Seleccione el nuevo estado de la solicitud."
        );

        return;
    }

    const datos =
        new FormData();

    datos.append(
        "id",
        solicitudActual
    );

    datos.append(
        "id_estado",
        idEstado
    );

    datos.append(
        "observacion",
        observacion
    );

    try {

        if (boton) {

            boton.disabled = true;

            boton.dataset.textoOriginal =
                boton.innerHTML;

            boton.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm"
                ></span>
                Guardando...
            `;
        }

        const respuesta = await fetch(
            "api/api_solicitudes.php?action=actualizarEstado",
            {
                method: "POST",
                body: datos
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(textoRespuesta);

        } catch (errorJSON) {

            console.error(
                "Respuesta de actualización:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió una respuesta inválida."
            );
        }

        if (
            !respuesta.ok ||
            resultado.success === false
        ) {

            throw new Error(
                resultado.error ||
                "No se pudo actualizar el estado."
            );
        }

        alert(
            resultado.mensaje ||
            "Estado actualizado correctamente."
        );

        await cargarSolicitudes();

        const modalElemento =
            obtenerElemento("modalSolicitud");

        if (modalElemento) {

            const modal =
                bootstrap.Modal.getInstance(
                    modalElemento
                );

            if (modal) {
                modal.hide();
            }
        }

        limpiarSolicitud();

    } catch (error) {

        console.error(
            "Error al actualizar estado:",
            error
        );

        mostrarErrorSolicitud(
            error.message
        );

    } finally {

        if (boton) {

            boton.disabled = false;

            boton.innerHTML =
                boton.dataset.textoOriginal ||
                `
                    <i class="bi bi-check2-circle me-2"></i>
                    Actualizar Estado
                `;
        }
    }
}

//==================================================
// ELIMINAR SOLICITUD
//==================================================

async function eliminarSolicitud(id) {

    const confirmar = confirm(
        "¿Está seguro de eliminar esta solicitud?"
    );

    if (!confirmar) {
        return;
    }

    try {

        const respuesta = await fetch(
            "api/api_solicitudes.php?action=eliminar&id=" +
            encodeURIComponent(id),
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(textoRespuesta);

        } catch (errorJSON) {

            console.error(
                "Respuesta de eliminación:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió una respuesta inválida."
            );
        }

        if (
            !respuesta.ok ||
            resultado.success === false
        ) {

            throw new Error(
                resultado.error ||
                "No se pudo eliminar la solicitud."
            );
        }

        alert(
            resultado.mensaje ||
            "Solicitud eliminada correctamente."
        );

        await cargarSolicitudes();

    } catch (error) {

        console.error(
            "Error al eliminar solicitud:",
            error
        );

        mostrarErrorSolicitud(
            error.message
        );
    }
}

//==================================================
// LIMPIAR MODAL
//==================================================

function limpiarSolicitud() {

    solicitudActual = null;

    const ids = [
        "sNombre",
        "sApellido",
        "sDpi",
        "sTelefono",
        "sCorreo",
        "sNivel",
        "sTipoLic",
        "sFecha",
        "sObservacion"
    ];

    ids.forEach((id) => {
        colocarValor(id, "");
    });

    const select =
        obtenerElemento("sEstadoSelect");

    if (select) {
        select.value = "";
    }

    const historial =
        obtenerElemento(
            "historialSolicitud"
        );

    if (historial) {
        historial.innerHTML = "";
    }
}

//==================================================
// FUNCIONES AUXILIARES
//==================================================

function colocarValor(id, valor) {

    const elemento =
        obtenerElemento(id);

    if (!elemento) {
        return;
    }

    if (
        elemento.tagName === "INPUT" ||
        elemento.tagName === "TEXTAREA" ||
        elemento.tagName === "SELECT"
    ) {

        elemento.value =
            valor ?? "";

    } else {

        elemento.textContent =
            valor ?? "";
    }
}

function formatearFecha(fecha) {

    if (!fecha) {
        return "Sin fecha";
    }

    const fechaObjeto =
        new Date(
            String(fecha).replace(
                " ",
                "T"
            )
        );

    if (
        Number.isNaN(
            fechaObjeto.getTime()
        )
    ) {

        return escaparHTML(fecha);
    }

    return fechaObjeto.toLocaleString(
        "es-GT",
        {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit"
        }
    );
}

function obtenerClaseEstado(estado) {

    const nombre =
        String(
            estado || ""
        ).toLowerCase();

    if (
        nombre.includes("aprob") ||
        nombre.includes("acept") ||
        nombre.includes("complet")
    ) {

        return "bg-success";
    }

    if (
        nombre.includes("rechaz") ||
        nombre.includes("cancel")
    ) {

        return "bg-danger";
    }

    if (
        nombre.includes("proceso") ||
        nombre.includes("revisión") ||
        nombre.includes("revision")
    ) {

        return "bg-primary";
    }

    return "bg-warning text-dark";
}

function escaparHTML(valor) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

//==================================================
// EVENTOS
//==================================================

document.addEventListener(
    "DOMContentLoaded",
    () => {

        cargarSolicitudes();
        cargarEstadosSolicitudes();

        const buscador =
            obtenerElemento(
                "buscarSolicitud"
            );

        if (buscador) {

            buscador.addEventListener(
                "input",
                renderSolicitudes
            );
        }

        const modal =
            obtenerElemento(
                "modalSolicitud"
            );

        if (modal) {

            modal.addEventListener(
                "hidden.bs.modal",
                limpiarSolicitud
            );
        }
    }
);