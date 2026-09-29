"use strict";

//=====================================
// VARIABLES GENERALES
//=====================================

let compras = [];
let servicios = [];

let comprasCargadas = false;
let serviciosCargados = false;

let modalDetalleCompra = null;

//=====================================
// INICIO
//=====================================

document.addEventListener("DOMContentLoaded", () => {

    inicializarModal();

    configurarEventos();

    cargarCompras();

});

//=====================================
// CONFIGURAR MODAL
//=====================================

function inicializarModal() {

    const elementoModal =
        document.getElementById("modalDetalleCompra");

    if (!elementoModal) {
        return;
    }

    modalDetalleCompra =
        bootstrap.Modal.getOrCreateInstance(
            elementoModal
        );

}

//=====================================
// CONFIGURAR EVENTOS
//=====================================

function configurarEventos() {

    const tabs =
        document.querySelectorAll(".historial-tab");

    tabs.forEach(tab => {

        tab.addEventListener("click", () => {

            const panel =
                tab.dataset.panel;

            cambiarPanel(panel, tab);

        });

    });

    const btnActualizar =
        document.getElementById(
            "btnActualizarHistorial"
        );

    if (btnActualizar) {

        btnActualizar.addEventListener(
            "click",
            actualizarHistorial
        );

    }

}

//=====================================
// CAMBIAR PANEL
//=====================================

function cambiarPanel(panel, botonActivo) {

    document
        .querySelectorAll(".historial-tab")
        .forEach(tab => {

            tab.classList.remove("active");

        });

    if (botonActivo) {

        botonActivo.classList.add("active");

    }

    const panelCompras =
        document.getElementById(
            "panelCompras"
        );

    const panelServicios =
        document.getElementById(
            "panelServicios"
        );

    if (panel === "servicios") {

        panelCompras?.classList.add("d-none");

        panelServicios?.classList.remove("d-none");

        if (!serviciosCargados) {

            cargarServicios();

        }

        return;

    }

    panelServicios?.classList.add("d-none");

    panelCompras?.classList.remove("d-none");

    if (!comprasCargadas) {

        cargarCompras();

    }

}

//=====================================
// ACTUALIZAR TODO
//=====================================

async function actualizarHistorial() {

    const boton =
        document.getElementById(
            "btnActualizarHistorial"
        );

    if (boton) {

        boton.disabled = true;

        boton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-2"
                role="status"
            ></span>
            ACTUALIZANDO...
        `;

    }

    comprasCargadas = false;
    serviciosCargados = false;

    try {

        await Promise.all([
            cargarCompras(false),
            cargarServicios(false)
        ]);

        mostrarAlerta(
            "success",
            "El historial fue actualizado correctamente."
        );

    } catch (error) {

        console.error(
            "Error al actualizar el historial:",
            error
        );

    } finally {

        if (boton) {

            boton.disabled = false;

            boton.innerHTML = `
                <i class="bi bi-arrow-clockwise me-2"></i>
                ACTUALIZAR HISTORIAL
            `;

        }

    }

}

//=====================================
// CARGAR COMPRAS
//=====================================

async function cargarCompras(
    mostrarError = true
) {

    const carga =
        document.getElementById(
            "cargaCompras"
        );

    const sinCompras =
        document.getElementById(
            "sinCompras"
        );

    const tabla =
        document.getElementById(
            "contenedorTablaCompras"
        );

    carga?.classList.remove("d-none");
    sinCompras?.classList.add("d-none");
    tabla?.classList.add("d-none");

    try {

        const respuesta = await fetch(
            `${RONEM_CONFIG.apiHistorial}?action=listar_compras`,
            {
                method: "GET",
                cache: "no-store",
                credentials: "same-origin"
            }
        );

        const datos =
            await obtenerJSONSeguro(respuesta);

        if (!respuesta.ok) {

            throw new Error(
                datos.message ||
                datos.error ||
                `Error HTTP ${respuesta.status}`
            );

        }

        if (datos.success === false) {

            throw new Error(
                datos.message ||
                datos.error ||
                "No fue posible cargar las compras."
            );

        }

        compras =
            Array.isArray(datos.compras)
                ? datos.compras
                : Array.isArray(datos)
                    ? datos
                    : [];

        comprasCargadas = true;

        renderCompras();

        actualizarResumenCompras();

    } catch (error) {

        compras = [];
        comprasCargadas = false;

        carga?.classList.add("d-none");
        tabla?.classList.add("d-none");
        sinCompras?.classList.remove("d-none");

        actualizarResumenCompras();

        console.error(
            "Error al cargar las compras:",
            error
        );

        if (mostrarError) {

            mostrarAlerta(
                "danger",
                "No se pudieron cargar las compras. " +
                error.message
            );

        }

        throw error;

    }

}

//=====================================
// RENDERIZAR COMPRAS
//=====================================

function renderCompras() {

    const carga =
        document.getElementById(
            "cargaCompras"
        );

    const sinCompras =
        document.getElementById(
            "sinCompras"
        );

    const tabla =
        document.getElementById(
            "contenedorTablaCompras"
        );

    const tbody =
        document.getElementById(
            "tbodyCompras"
        );

    carga?.classList.add("d-none");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = "";

    if (compras.length === 0) {

        tabla?.classList.add("d-none");
        sinCompras?.classList.remove("d-none");

        return;

    }

    sinCompras?.classList.add("d-none");
    tabla?.classList.remove("d-none");

    tbody.innerHTML = compras
        .map(venta => {

            const idVenta =
                Number(
                    venta.Id_venta ??
                    venta.id_venta ??
                    0
                );

            const fecha =
                formatearFecha(
                    venta.Fecha ??
                    venta.fecha
                );

            const total =
                formatearMoneda(
                    venta.Total ??
                    venta.total ??
                    0
                );

            const estado =
                String(
                    venta.Estado ??
                    venta.estado ??
                    "Registrada"
                );

            return `
                <tr>

                    <td>

                        <strong>
                            #${idVenta}
                        </strong>

                    </td>

                    <td>

                        <span>
                            ${escaparHTML(fecha)}
                        </span>

                    </td>

                    <td>

                        <strong>
                            ${escaparHTML(total)}
                        </strong>

                    </td>

                    <td>

                        <span
                            class="estado-badge ${claseEstadoVenta(estado)}"
                        >
                            ${escaparHTML(estado)}
                        </span>

                    </td>

                    <td class="text-center">

                        <button
                            type="button"
                            class="btn-ver-detalle"
                            onclick="verDetalleCompra(${idVenta})"
                        >

                            <i class="bi bi-eye me-1"></i>
                            Ver detalle

                        </button>

                    </td>

                </tr>
            `;

        })
        .join("");

}

//=====================================
// RESUMEN DE COMPRAS
//=====================================

function actualizarResumenCompras() {

    const totalCompras =
        document.getElementById(
            "totalCompras"
        );

    if (totalCompras) {

        totalCompras.textContent =
            compras.length;

    }

}

//=====================================
// VER DETALLE DE COMPRA
//=====================================

async function verDetalleCompra(idVenta) {

    const idDetalle =
        document.getElementById(
            "idVentaDetalle"
        );

    const carga =
        document.getElementById(
            "cargaDetalleCompra"
        );

    const contenedor =
        document.getElementById(
            "contenedorDetalleCompra"
        );

    const sinDetalle =
        document.getElementById(
            "sinDetalleCompra"
        );

    const tbody =
        document.getElementById(
            "tbodyDetalleCompra"
        );

    const total =
        document.getElementById(
            "totalDetalleCompra"
        );

    if (idDetalle) {

        idDetalle.textContent =
            idVenta;

    }

    carga?.classList.remove("d-none");
    contenedor?.classList.add("d-none");
    sinDetalle?.classList.add("d-none");

    if (tbody) {

        tbody.innerHTML = "";

    }

    if (total) {

        total.textContent = "Q0.00";

    }

    modalDetalleCompra?.show();

    try {

        const respuesta = await fetch(
            `${RONEM_CONFIG.apiHistorial}` +
            `?action=detalle_compra` +
            `&id=${encodeURIComponent(idVenta)}`,
            {
                method: "GET",
                cache: "no-store",
                credentials: "same-origin"
            }
        );

        const datos =
            await obtenerJSONSeguro(respuesta);

        if (!respuesta.ok) {

            throw new Error(
                datos.message ||
                datos.error ||
                `Error HTTP ${respuesta.status}`
            );

        }

        if (datos.success === false) {

            throw new Error(
                datos.message ||
                datos.error ||
                "No fue posible cargar el detalle."
            );

        }

        const detalle =
            Array.isArray(datos.detalle)
                ? datos.detalle
                : Array.isArray(datos)
                    ? datos
                    : [];

        carga?.classList.add("d-none");

        if (detalle.length === 0) {

            contenedor?.classList.add("d-none");
            sinDetalle?.classList.remove("d-none");

            return;

        }

        sinDetalle?.classList.add("d-none");
        contenedor?.classList.remove("d-none");

        let totalCompra = 0;

        if (tbody) {

            tbody.innerHTML = detalle
                .map(item => {

                    const nombre =
                        item.Nombre ??
                        item.Nombre_producto ??
                        item.Producto ??
                        "Producto";

                    const cantidad =
                        Number(
                            item.Cantidad ??
                            item.cantidad ??
                            0
                        );

                    const precioUnidad =
                        Number(
                            item.Precio_unidad ??
                            item.Precio_Unitario ??
                            item.precio_unidad ??
                            0
                        );

                    const subtotalBD =
                        Number(
                            item.Subtotal ??
                            item.subtotal
                        );

                    const subtotal =
                        Number.isFinite(subtotalBD)
                            ? subtotalBD
                            : cantidad * precioUnidad;

                    totalCompra += subtotal;

                    return `
                        <tr>

                            <td>

                                <strong>
                                    ${escaparHTML(nombre)}
                                </strong>

                            </td>

                            <td class="text-center">

                                ${cantidad}

                            </td>

                            <td>

                                ${escaparHTML(
                                    formatearMoneda(
                                        precioUnidad
                                    )
                                )}

                            </td>

                            <td>

                                <strong>

                                    ${escaparHTML(
                                        formatearMoneda(
                                            subtotal
                                        )
                                    )}

                                </strong>

                            </td>

                        </tr>
                    `;

                })
                .join("");

        }

        const totalRespuesta =
            Number(
                datos.total ??
                datos.Total
            );

        if (
            Number.isFinite(totalRespuesta) &&
            totalRespuesta >= 0
        ) {

            totalCompra = totalRespuesta;

        }

        if (total) {

            total.textContent =
                formatearMoneda(
                    totalCompra
                );

        }

    } catch (error) {

        carga?.classList.add("d-none");
        contenedor?.classList.add("d-none");
        sinDetalle?.classList.remove("d-none");

        console.error(
            "Error al cargar el detalle:",
            error
        );

        mostrarAlerta(
            "danger",
            "No se pudo cargar el detalle de la compra. " +
            error.message
        );

    }

}

//=====================================
// CARGAR SERVICIOS
//=====================================

async function cargarServicios(
    mostrarError = true
) {

    const carga =
        document.getElementById(
            "cargaServicios"
        );

    const sinServicios =
        document.getElementById(
            "sinServicios"
        );

    const contenedor =
        document.getElementById(
            "contenedorServicios"
        );

    carga?.classList.remove("d-none");
    sinServicios?.classList.add("d-none");
    contenedor?.classList.add("d-none");

    try {

        const respuesta = await fetch(
            `${RONEM_CONFIG.apiHistorial}?action=listar_servicios`,
            {
                method: "GET",
                cache: "no-store",
                credentials: "same-origin"
            }
        );

        const datos =
            await obtenerJSONSeguro(respuesta);

        if (!respuesta.ok) {

            throw new Error(
                datos.message ||
                datos.error ||
                `Error HTTP ${respuesta.status}`
            );

        }

        if (datos.success === false) {

            throw new Error(
                datos.message ||
                datos.error ||
                "No fue posible cargar los servicios."
            );

        }

        servicios =
            Array.isArray(datos.servicios)
                ? datos.servicios
                : Array.isArray(datos.historial)
                    ? datos.historial
                    : Array.isArray(datos)
                        ? datos
                        : [];

        serviciosCargados = true;

        renderServicios();

        actualizarResumenServicios();

    } catch (error) {

        servicios = [];
        serviciosCargados = false;

        carga?.classList.add("d-none");
        contenedor?.classList.add("d-none");
        sinServicios?.classList.remove("d-none");

        actualizarResumenServicios();

        console.error(
            "Error al cargar los servicios:",
            error
        );

        if (mostrarError) {

            mostrarAlerta(
                "danger",
                "No se pudieron cargar los servicios de taller. " +
                error.message
            );

        }

        throw error;

    }

}

//=====================================
// RENDERIZAR SERVICIOS
//=====================================

function renderServicios() {

    const carga =
        document.getElementById(
            "cargaServicios"
        );

    const sinServicios =
        document.getElementById(
            "sinServicios"
        );

    const contenedor =
        document.getElementById(
            "contenedorServicios"
        );

    carga?.classList.add("d-none");

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = "";

    if (servicios.length === 0) {

        contenedor.classList.add("d-none");
        sinServicios?.classList.remove("d-none");

        return;

    }

    sinServicios?.classList.add("d-none");
    contenedor.classList.remove("d-none");

    contenedor.innerHTML = servicios
        .map(servicio => {

            const idHistorial =
                Number(
                    servicio.Id_historial ??
                    servicio.id_historial ??
                    0
                );

            const idOrden =
                Number(
                    servicio.Id_orden ??
                    servicio.id_orden ??
                    0
                );

            const estado =
                String(
                    servicio.Estado ??
                    servicio.Nombre_estado ??
                    servicio.Estado_mantenimiento ??
                    "Sin estado"
                );

            const comentario =
                servicio.Comentario ??
                servicio.comentario ??
                "Sin comentarios registrados.";

            const fecha =
                formatearFecha(
                    servicio.Fecha ??
                    servicio.fecha
                );

            const vehiculo =
                construirNombreVehiculo(
                    servicio
                );

            const problema =
                servicio.Descripcion_problema ??
                servicio.Problema ??
                servicio.Descripcion ??
                "Sin descripción registrada.";

            return `
                <article class="servicio-card">

                    <div class="servicio-card-encabezado">

                        <div>

                            <span class="servicio-id">

                                HISTORIAL #${idHistorial}

                            </span>

                            <h3>

                                Orden de taller #${idOrden}

                            </h3>

                        </div>

                        <span
                            class="estado-badge ${claseEstadoMantenimiento(estado)}"
                        >

                            ${escaparHTML(estado)}

                        </span>

                    </div>

                    <div class="servicio-datos">

                        <div class="servicio-dato">

                            <i class="bi bi-car-front"></i>

                            <div>

                                <small>
                                    Vehículo
                                </small>

                                <strong>

                                    ${escaparHTML(
                                        vehiculo
                                    )}

                                </strong>

                            </div>

                        </div>

                        <div class="servicio-dato">

                            <i class="bi bi-calendar3"></i>

                            <div>

                                <small>
                                    Fecha del registro
                                </small>

                                <strong>

                                    ${escaparHTML(
                                        fecha
                                    )}

                                </strong>

                            </div>

                        </div>

                        <div class="servicio-dato">

                            <i class="bi bi-tools"></i>

                            <div>

                                <small>
                                    Servicio o problema
                                </small>

                                <strong>

                                    ${escaparHTML(
                                        problema
                                    )}

                                </strong>

                            </div>

                        </div>

                    </div>

                    <div class="servicio-comentario">

                        <i class="bi bi-chat-left-text"></i>

                        ${escaparHTML(
                            comentario
                        )}

                    </div>

                </article>
            `;

        })
        .join("");

}

//=====================================
// RESUMEN DE SERVICIOS
//=====================================

function actualizarResumenServicios() {

    const totalServicios =
        document.getElementById(
            "totalServicios"
        );

    const totalFinalizados =
        document.getElementById(
            "totalFinalizados"
        );

    if (totalServicios) {

        totalServicios.textContent =
            servicios.length;

    }

    const finalizados =
        servicios.filter(servicio => {

            const estado =
                normalizarTexto(
                    servicio.Estado ??
                    servicio.Nombre_estado ??
                    servicio.Estado_mantenimiento ??
                    ""
                );

            return (
                estado === "finalizada" ||
                estado === "finalizado" ||
                estado === "entregada" ||
                estado === "entregado"
            );

        }).length;

    if (totalFinalizados) {

        totalFinalizados.textContent =
            finalizados;

    }

}

//=====================================
// CONSTRUIR NOMBRE DEL VEHÍCULO
//=====================================

function construirNombreVehiculo(servicio) {

    const marca =
        servicio.Marca ??
        servicio.marca ??
        "";

    const linea =
        servicio.Linea ??
        servicio.linea ??
        "";

    const placa =
        servicio.Placa ??
        servicio.placa ??
        "";

    const partes = [];

    if (marca) {
        partes.push(marca);
    }

    if (linea) {
        partes.push(linea);
    }

    let nombre =
        partes.join(" ");

    if (placa) {

        nombre +=
            nombre
                ? ` — ${placa}`
                : placa;

    }

    return (
        nombre.trim() ||
        "Vehículo no especificado"
    );

}

//=====================================
// CLASE DEL ESTADO DE MANTENIMIENTO
//=====================================

function claseEstadoMantenimiento(estado) {

    const texto =
        normalizarTexto(estado);

    switch (texto) {

        case "recibida":
        case "recibido":

            return "estado-recibida";

        case "en diagnostico":
        case "diagnostico":

            return "estado-diagnostico";

        case "esperando repuesto":

            return "estado-repuesto";

        case "en reparacion":
        case "reparacion":

            return "estado-reparacion";

        case "prueba de ruta":

            return "estado-prueba";

        case "lista para entrega":
        case "listo para entrega":

            return "estado-lista";

        case "entregada":
        case "entregado":

            return "estado-entregada";

        case "cancelada":
        case "cancelado":

            return "estado-cancelada";

        case "garantia":

            return "estado-garantia";

        case "finalizada":
        case "finalizado":

            return "estado-finalizada";

        default:

            return "estado-desconocido";

    }

}

//=====================================
// CLASE DEL ESTADO DE VENTA
//=====================================

function claseEstadoVenta(estado) {

    const texto =
        normalizarTexto(estado);

    if (
        texto === "completada" ||
        texto === "completado" ||
        texto === "pagada" ||
        texto === "pagado" ||
        texto === "finalizada"
    ) {

        return "estado-finalizada";

    }

    if (
        texto === "cancelada" ||
        texto === "cancelado" ||
        texto === "anulada" ||
        texto === "anulado"
    ) {

        return "estado-cancelada";

    }

    if (
        texto === "pendiente"
    ) {

        return "estado-diagnostico";

    }

    return "estado-recibida";

}

//=====================================
// FORMATEAR MONEDA
//=====================================

function formatearMoneda(valor) {

    const numero =
        Number(valor);

    const valorSeguro =
        Number.isFinite(numero)
            ? numero
            : 0;

    return valorSeguro.toLocaleString(
        "es-GT",
        {
            style: "currency",
            currency: "GTQ",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}

//=====================================
// FORMATEAR FECHA
//=====================================

function formatearFecha(valor) {

    if (!valor) {

        return "Sin fecha";

    }

    const fechaTexto =
        String(valor).trim();

    /*
    MySQL normalmente devuelve:
    2026-07-21 18:40:00

    Se reemplaza el espacio para que JavaScript
    pueda interpretar correctamente la fecha.
    */

    const fecha =
        new Date(
            fechaTexto.replace(
                " ",
                "T"
            )
        );

    if (
        Number.isNaN(
            fecha.getTime()
        )
    ) {

        return fechaTexto;

    }

    return fecha.toLocaleString(
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

//=====================================
// NORMALIZAR TEXTO
//=====================================

function normalizarTexto(valor) {

    return String(valor ?? "")
        .trim()
        .toLowerCase()
        .normalize("NFD")
        .replace(
            /[\u0300-\u036f]/g,
            ""
        );

}

//=====================================
// ESCAPAR HTML
//=====================================

function escaparHTML(valor) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

}

//=====================================
// OBTENER JSON DE FORMA SEGURA
//=====================================

async function obtenerJSONSeguro(
    respuesta
) {

    const texto =
        await respuesta.text();

    if (!texto.trim()) {

        return {};

    }

    try {

        return JSON.parse(texto);

    } catch (error) {

        console.error(
            "Respuesta no válida del servidor:",
            texto
        );

        throw new Error(
            "El servidor devolvió una respuesta inválida."
        );

    }

}

//=====================================
// MOSTRAR ALERTA
//=====================================

function mostrarAlerta(
    tipo,
    mensaje
) {

    const contenedor =
        document.getElementById(
            "alertaHistorial"
        );

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div
            class="alert alert-${tipo} alert-dismissible fade show"
            role="alert"
        >

            <i class="bi ${iconoAlerta(tipo)} me-2"></i>

            ${escaparHTML(mensaje)}

            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="alert"
                aria-label="Cerrar"
            ></button>

        </div>
    `;

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

}

//=====================================
// ICONO DE ALERTA
//=====================================

function iconoAlerta(tipo) {

    switch (tipo) {

        case "success":

            return "bi-check-circle";

        case "warning":

            return "bi-exclamation-triangle";

        case "info":

            return "bi-info-circle";

        default:

            return "bi-x-circle";

    }

}