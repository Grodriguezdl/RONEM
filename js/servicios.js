//==================================================
// SERVICIOS
//==================================================

const API_SERVICIOS = "api/api_servicios.php";

let servicios = [];
let servicioEditando = null;

//==================================================
// INICIAR SERVICIOS
//==================================================

document.addEventListener("DOMContentLoaded", () => {

    cargarServicios();

    const buscador = document.getElementById(
        "buscarServicio"
    );

    if (buscador) {

        buscador.addEventListener(
            "input",
            renderServicios
        );
    }

    const modal = document.getElementById(
        "modalServicio"
    );

    if (modal) {

        modal.addEventListener(
            "hidden.bs.modal",
            limpiarFormularioServicio
        );
    }
});

//==================================================
// CARGAR SERVICIOS
//==================================================

async function cargarServicios() {

    const tbody = document.getElementById(
        "tbodyServicios"
    );

    if (!tbody) {

        console.error(
            "No se encontró tbodyServicios."
        );

        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div
                    class="spinner-border spinner-border-sm"
                    role="status"
                ></div>

                <span class="ms-2">
                    Cargando servicios...
                </span>
            </td>
        </tr>
    `;

    try {

        const respuesta = await fetch(
            `${API_SERVICIOS}?action=listar`,
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const texto = await respuesta.text();

        console.log(
            "Respuesta API Servicios:",
            texto
        );

        let resultado;

        try {

            resultado = JSON.parse(texto);

        } catch (error) {

            throw new Error(
                "La API de servicios no devolvió JSON válido. Revisa la consola."
            );
        }

        if (!respuesta.ok) {

            throw new Error(
                resultado.error ||
                resultado.mensaje ||
                resultado.message ||
                "No se pudieron cargar los servicios."
            );
        }

        /*
         * Admite cualquiera de estas respuestas:
         *
         * [...]
         *
         * { data: [...] }
         *
         * { servicios: [...] }
         */

        if (Array.isArray(resultado)) {

            servicios = resultado;

        } else if (
            resultado &&
            Array.isArray(resultado.data)
        ) {

            servicios = resultado.data;

        } else if (
            resultado &&
            Array.isArray(resultado.servicios)
        ) {

            servicios = resultado.servicios;

        } else {

            servicios = [];
        }

        renderServicios();

    } catch (error) {

        console.error(
            "Error al cargar servicios:",
            error
        );

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="8"
                    class="text-center text-danger py-4"
                >
                    ${escaparHTMLServicio(
                        error.message
                    )}
                </td>
            </tr>
        `;

        actualizarContadorServicios(0);
    }
}

//==================================================
// RENDERIZAR SERVICIOS
//==================================================

function renderServicios() {

    const tbody = document.getElementById(
        "tbodyServicios"
    );

    if (!tbody) {
        return;
    }

    const buscador = document.getElementById(
        "buscarServicio"
    );

    const busqueda = buscador
        ? buscador.value.trim().toLowerCase()
        : "";

    const filtrados = servicios.filter(
        servicio => {

            const contenido = [
                servicio.Id_servicio,
                servicio.Nombre,
                servicio.Descripcion,
                servicio.Icono,
                servicio.Orden,
                Number(servicio.Estado) === 1
                    ? "activo"
                    : "inactivo",
                servicio.Fecha
            ]
                .join(" ")
                .toLowerCase();

            return contenido.includes(busqueda);
        }
    );

    actualizarContadorServicios(
        filtrados.length
    );

    if (filtrados.length === 0) {

        tbody.innerHTML = `
            <tr class="empty-row">
                <td colspan="8">
                    No hay servicios registrados
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML = filtrados.map(
        servicio => {

            const id = Number(
                servicio.Id_servicio || 0
            );

            const nombre = String(
                servicio.Nombre || ""
            );

            const descripcion = String(
                servicio.Descripcion || ""
            );

            const icono = String(
                servicio.Icono || "bi-tools"
            ).trim();

            const orden = Number(
                servicio.Orden || 0
            );

            const estado = Number(
                servicio.Estado
            );

            const fecha = formatearFechaServicio(
                servicio.Fecha
            );

            return `
                <tr>

                    <td>
                        ${id}
                    </td>

                    <td class="text-center">

                        <i
                            class="bi ${escaparHTMLServicio(
                                icono
                            )}"
                            style="font-size:24px;"
                        ></i>

                        <div class="small text-muted">
                            ${escaparHTMLServicio(
                                icono
                            )}
                        </div>

                    </td>

                    <td>
                        <strong>
                            ${escaparHTMLServicio(
                                nombre
                            )}
                        </strong>
                    </td>

                    <td>
                        ${escaparHTMLServicio(
                            descripcion ||
                            "Sin descripción"
                        )}
                    </td>

                    <td>
                        ${orden}
                    </td>

                    <td>

                        <span
                            class="badge ${
                                estado === 1
                                    ? "bg-success"
                                    : "bg-danger"
                            }"
                        >
                            ${
                                estado === 1
                                    ? "Activo"
                                    : "Inactivo"
                            }
                        </span>

                    </td>

                    <td>
                        ${escaparHTMLServicio(
                            fecha
                        )}
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-sm btn-warning me-1"
                            onclick="editarServicio(${id})"
                            title="Editar servicio"
                        >
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            onclick="eliminarServicio(${id})"
                            title="Eliminar servicio"
                        >
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>
            `;
        }
    ).join("");
}

//==================================================
// ABRIR MODAL PARA NUEVO SERVICIO
//==================================================

function abrirNuevoServicio() {

    limpiarFormularioServicio();

    const modal = document.getElementById(
        "modalServicio"
    );

    if (!modal) {

        console.error(
            "No se encontró modalServicio."
        );

        return;
    }

    bootstrap.Modal
        .getOrCreateInstance(modal)
        .show();
}

//==================================================
// EDITAR SERVICIO
//==================================================

function editarServicio(id) {

    const servicio = servicios.find(
        item =>
            Number(item.Id_servicio) ===
            Number(id)
    );

    if (!servicio) {

        alert(
            "No se encontró el servicio."
        );

        return;
    }

    servicioEditando = Number(id);

    document.getElementById(
        "servicioId"
    ).value = servicio.Id_servicio || "";

    document.getElementById(
        "servicioNombre"
    ).value = servicio.Nombre || "";

    document.getElementById(
        "servicioDescripcion"
    ).value = servicio.Descripcion || "";

    document.getElementById(
        "servicioIcono"
    ).value = servicio.Icono || "";

    document.getElementById(
        "servicioOrden"
    ).value = Number(
        servicio.Orden || 0
    );

    document.getElementById(
        "servicioEstado"
    ).value =
        Number(servicio.Estado) === 1
            ? "1"
            : "0";

    document.getElementById(
        "tituloModalServicio"
    ).textContent = "Editar Servicio";

    document.getElementById(
        "btnGuardarServicio"
    ).innerHTML = `
        <i class="bi bi-floppy me-2"></i>
        Actualizar Servicio
    `;

    bootstrap.Modal
        .getOrCreateInstance(
            document.getElementById(
                "modalServicio"
            )
        )
        .show();
}

//==================================================
// GUARDAR SERVICIO
//==================================================

async function guardarServicio() {

    const formulario = document.getElementById(
        "formServicio"
    );

    if (!formulario) {
        return;
    }

    if (!formulario.checkValidity()) {

        formulario.reportValidity();

        return;
    }

    const nombre = document.getElementById(
        "servicioNombre"
    ).value.trim();

    const descripcion = document.getElementById(
        "servicioDescripcion"
    ).value.trim();

    const icono = document.getElementById(
        "servicioIcono"
    ).value.trim();

    const orden = document.getElementById(
        "servicioOrden"
    ).value;

    const estado = document.getElementById(
        "servicioEstado"
    ).value;

    const datos = new FormData();

    datos.append(
        "id",
        servicioEditando !== null
            ? servicioEditando
            : ""
    );

    datos.append(
        "nombre",
        nombre
    );

    datos.append(
        "descripcion",
        descripcion
    );

    datos.append(
        "icono",
        icono
    );

    datos.append(
        "orden",
        orden
    );

    datos.append(
        "estado",
        estado
    );

    const boton = document.getElementById(
        "btnGuardarServicio"
    );

    const textoAnterior = boton.innerHTML;

    boton.disabled = true;

    boton.innerHTML = `
        <span
            class="spinner-border spinner-border-sm me-2"
        ></span>
        Guardando...
    `;

    try {

        const respuesta = await fetch(
            `${API_SERVICIOS}?action=guardar`,
            {
                method: "POST",
                body: datos
            }
        );

        const texto = await respuesta.text();

        console.log(
            "Respuesta guardar servicio:",
            texto
        );

        let resultado;

        try {

            resultado = JSON.parse(texto);

        } catch (error) {

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
                resultado.mensaje ||
                resultado.message ||
                "No se pudo guardar el servicio."
            );
        }

        alert(
            resultado.mensaje ||
            resultado.message ||
            "Servicio guardado correctamente."
        );

        bootstrap.Modal
            .getOrCreateInstance(
                document.getElementById(
                    "modalServicio"
                )
            )
            .hide();

        limpiarFormularioServicio();

        await cargarServicios();

    } catch (error) {

        console.error(
            "Error al guardar servicio:",
            error
        );

        alert(error.message);

    } finally {

        boton.disabled = false;
        boton.innerHTML = textoAnterior;
    }
}

//==================================================
// ELIMINAR SERVICIO
//==================================================

async function eliminarServicio(id) {

    const servicio = servicios.find(
        item =>
            Number(item.Id_servicio) ===
            Number(id)
    );

    const nombre = servicio
        ? servicio.Nombre
        : "este servicio";

    if (
        !confirm(
            `¿Deseas eliminar "${nombre}"?`
        )
    ) {
        return;
    }

    try {

        const respuesta = await fetch(
            `${API_SERVICIOS}?action=eliminar&id=${encodeURIComponent(
                id
            )}`,
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const texto = await respuesta.text();

        console.log(
            "Respuesta eliminar servicio:",
            texto
        );

        let resultado;

        try {

            resultado = JSON.parse(texto);

        } catch (error) {

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
                resultado.mensaje ||
                resultado.message ||
                "No se pudo eliminar el servicio."
            );
        }

        alert(
            resultado.mensaje ||
            resultado.message ||
            "Servicio eliminado correctamente."
        );

        await cargarServicios();

    } catch (error) {

        console.error(
            "Error al eliminar servicio:",
            error
        );

        alert(error.message);
    }
}

//==================================================
// LIMPIAR FORMULARIO
//==================================================

function limpiarFormularioServicio() {

    servicioEditando = null;

    const formulario = document.getElementById(
        "formServicio"
    );

    if (formulario) {
        formulario.reset();
    }

    const id = document.getElementById(
        "servicioId"
    );

    if (id) {
        id.value = "";
    }

    const orden = document.getElementById(
        "servicioOrden"
    );

    if (orden) {
        orden.value = "0";
    }

    const estado = document.getElementById(
        "servicioEstado"
    );

    if (estado) {
        estado.value = "1";
    }

    const titulo = document.getElementById(
        "tituloModalServicio"
    );

    if (titulo) {
        titulo.textContent = "Agregar Servicio";
    }

    const boton = document.getElementById(
        "btnGuardarServicio"
    );

    if (boton) {

        boton.disabled = false;

        boton.innerHTML = `
            <i class="bi bi-floppy me-2"></i>
            Guardar Servicio
        `;
    }
}

//==================================================
// CONTADOR
//==================================================

function actualizarContadorServicios(cantidad) {

    const chip = document.getElementById(
        "chipServicios"
    );

    if (!chip) {
        return;
    }

    chip.textContent =
        cantidad === 1
            ? "1 servicio"
            : `${cantidad} servicios`;
}

//==================================================
// FORMATEAR FECHA
//==================================================

function formatearFechaServicio(fecha) {

    if (!fecha) {
        return "Sin fecha";
    }

    const normalizada = String(fecha)
        .replace(" ", "T");

    const objetoFecha = new Date(normalizada);

    if (Number.isNaN(objetoFecha.getTime())) {
        return String(fecha);
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
// ESCAPAR HTML
//==================================================

function escaparHTMLServicio(valor) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

//==================================================
// RECARGAR SERVICIOS
//==================================================

function recargarServicios() {
    cargarServicios();
}