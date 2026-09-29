//==================================================
// VENTAS
//==================================================

let ventas = [];
let ventaSeleccionada = null;

//==================================================
// INICIO
//==================================================

document.addEventListener("DOMContentLoaded", () => {

    cargarVentas();

    const buscador =
        document.getElementById("buscarVenta");

    if (buscador) {
        buscador.addEventListener(
            "input",
            renderVentas
        );
    }
});

//==================================================
// CARGAR VENTAS
//==================================================

async function cargarVentas() {

    const tbody =
        document.getElementById("tbodyVentas");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr class="empty-row">
            <td colspan="7">
                Cargando ventas...
            </td>
        </tr>
    `;

    try {

        const respuesta = await fetch(
            "/RONEM/api/api_ventas.php?action=listar",
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const datos =
            await leerRespuestaVenta(respuesta);

        if (
            !respuesta.ok ||
            datos.success === false
        ) {
            throw new Error(
                datos.message ||
                "No se pudieron cargar las ventas."
            );
        }

        ventas = Array.isArray(datos.data)
            ? datos.data
            : [];

        renderVentas();

    } catch (error) {

        console.error(
            "Error al cargar ventas:",
            error
        );

        tbody.innerHTML = `
            <tr class="empty-row">
                <td colspan="7" class="text-danger">
                    ${escaparHTMLVenta(error.message)}
                </td>
            </tr>
        `;
    }
}

//==================================================
// RENDER
//==================================================

function renderVentas() {

    const tbody =
        document.getElementById("tbodyVentas");

    const chip =
        document.getElementById("chipVentas");

    const buscador =
        document.getElementById("buscarVenta");

    if (!tbody) {
        return;
    }

    const termino = buscador
        ? buscador.value.trim().toLowerCase()
        : "";

    const filtradas = ventas.filter(venta => {

        const contenido = `
            ${venta.Id_detalle ?? ""}
            ${venta.Id_venta ?? ""}
            ${venta.Producto ?? ""}
            ${venta.Cantidad ?? ""}
            ${venta.Precio_unidad ?? ""}
            ${venta.Subtotal ?? ""}
        `.toLowerCase();

        return contenido.includes(termino);
    });

    if (chip) {
        chip.textContent =
            `${filtradas.length} detalle` +
            `${filtradas.length === 1 ? "" : "s"}`;
    }

    if (filtradas.length === 0) {

        tbody.innerHTML = `
            <tr class="empty-row">
                <td colspan="7">
                    No hay ventas registradas
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML = filtradas.map(venta => {

        const idDetalle =
            Number(venta.Id_detalle);

        const idVenta =
            Number(venta.Id_venta);

        return `
            <tr>
                <td>
                    ${idDetalle}
                </td>

                <td>
                    ${idVenta}
                </td>

                <td>
                    ${escaparHTMLVenta(venta.Producto)}
                </td>

                <td>
                    ${Number(venta.Cantidad)}
                </td>

                <td>
                    ${formatearMonedaVenta(
                        venta.Precio_unidad
                    )}
                </td>

                <td>
                    ${formatearMonedaVenta(
                        venta.Subtotal
                    )}
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm me-1"
                        onclick="verDetalleVenta(${idDetalle})"
                        title="Ver detalle"
                    >
                        <i class="bi bi-eye"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-info btn-sm me-1"
                        onclick="verVentaCompleta(${idVenta})"
                        title="Ver venta completa"
                    >
                        <i class="bi bi-receipt"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger btn-sm"
                        onclick="eliminarDetalleVenta(${idDetalle})"
                        title="Eliminar"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join("");
}

//==================================================
// VER DETALLE
//==================================================

async function verDetalleVenta(idDetalle) {

    try {

        const respuesta = await fetch(
            "/RONEM/api/api_ventas.php?action=detalle&id=" +
encodeURIComponent(idDetalle),
            {
                cache: "no-store"
            }
        );

        const datos =
            await leerRespuestaVenta(respuesta);

        if (
            !respuesta.ok ||
            datos.success === false
        ) {
            throw new Error(
                datos.message ||
                "No se pudo cargar el detalle."
            );
        }

        const detalle = datos.detalle;

        colocarValorVenta(
            "vIdDetalle",
            detalle.Id_detalle
        );

        colocarValorVenta(
            "vIdVenta",
            detalle.Id_venta
        );

        colocarValorVenta(
            "vProducto",
            detalle.Producto
        );

        colocarValorVenta(
            "vCantidad",
            detalle.Cantidad
        );

        colocarValorVenta(
            "vPrecioUnidad",
            formatearMonedaVenta(
                detalle.Precio_unidad
            )
        );

        colocarValorVenta(
            "vSubtotal",
            formatearMonedaVenta(
                detalle.Subtotal
            )
        );

        bootstrap.Modal
            .getOrCreateInstance(
                document.getElementById(
                    "modalDetalleVenta"
                )
            )
            .show();

    } catch (error) {

        console.error(error);
        alert(error.message);
    }
}

//==================================================
// VER VENTA COMPLETA
//==================================================

async function verVentaCompleta(idVenta) {

    ventaSeleccionada = Number(idVenta);

    const tbody = document.getElementById(
        "tbodyDetalleVentaCompleta"
    );

    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5">
                    Cargando venta...
                </td>
            </tr>
        `;
    }

    try {

        const respuesta = await fetch(
            "/RONEM/api/api_ventas.php?action=porVenta&id=" +
encodeURIComponent(idVenta),
            {
                cache: "no-store"
            }
        );

        const datos =
            await leerRespuestaVenta(respuesta);

        if (
            !respuesta.ok ||
            datos.success === false
        ) {
            throw new Error(
                datos.message ||
                "No se pudo cargar la venta."
            );
        }

        colocarValorVenta(
            "numeroVentaCompleta",
            datos.idVenta
        );

        colocarValorVenta(
            "totalVentaCompleta",
            formatearMonedaVenta(datos.total)
        );

        renderDetalleVentaCompleta(
            Array.isArray(datos.detalles)
                ? datos.detalles
                : []
        );

        bootstrap.Modal
            .getOrCreateInstance(
                document.getElementById(
                    "modalVentaCompleta"
                )
            )
            .show();

    } catch (error) {

        console.error(error);

        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-danger">
                        ${escaparHTMLVenta(error.message)}
                    </td>
                </tr>
            `;
        }

        alert(error.message);
    }
}

//==================================================
// RENDER VENTA COMPLETA
//==================================================

function renderDetalleVentaCompleta(detalles) {

    const tbody = document.getElementById(
        "tbodyDetalleVentaCompleta"
    );

    if (!tbody) {
        return;
    }

    if (detalles.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td colspan="5">
                    Esta venta no tiene productos
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML = detalles.map(detalle => `
        <tr>
            <td>
                ${escaparHTMLVenta(detalle.Producto)}
            </td>

            <td>
                ${Number(detalle.Cantidad)}
            </td>

            <td>
                ${formatearMonedaVenta(
                    detalle.Precio_unidad
                )}
            </td>

            <td>
                ${formatearMonedaVenta(
                    detalle.Subtotal
                )}
            </td>

            <td>
                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="eliminarDetalleVenta(
                        ${Number(detalle.Id_detalle)},
                        true
                    )"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join("");
}

//==================================================
// ELIMINAR
//==================================================

async function eliminarDetalleVenta(
    idDetalle,
    recargarModal = false
) {

    if (
        !confirm(
            "¿Eliminar este detalle y devolver el producto al stock?"
        )
    ) {
        return;
    }

    const datos = new FormData();

    datos.append("id", idDetalle);

    try {

        const respuesta = await fetch(
            "/RONEM/api/api_ventas.php?action=eliminar",
            {
                method: "POST",
                body: datos
            }
        );

        const resultado =
            await leerRespuestaVenta(respuesta);

        if (
            !respuesta.ok ||
            resultado.success === false
        ) {
            throw new Error(
                resultado.message ||
                "No se pudo eliminar el detalle."
            );
        }

        alert(resultado.message);

        await cargarVentas();

        if (
            recargarModal &&
            ventaSeleccionada !== null
        ) {
            await verVentaCompleta(
                ventaSeleccionada
            );
        }

    } catch (error) {

        console.error(error);
        alert(error.message);
    }
}

//==================================================
// UTILIDADES
//==================================================

async function leerRespuestaVenta(respuesta) {

    const texto = await respuesta.text();

    try {
        return JSON.parse(texto);
    } catch (error) {

        console.error(
            "Respuesta de api_ventas.php:",
            texto
        );

        throw new Error(
            "El servidor devolvió una respuesta no válida."
        );
    }
}

function colocarValorVenta(id, valor) {

    const elemento =
        document.getElementById(id);

    if (!elemento) {
        return;
    }

    if (
        elemento instanceof HTMLInputElement ||
        elemento instanceof HTMLTextAreaElement ||
        elemento instanceof HTMLSelectElement
    ) {
        elemento.value = valor ?? "";
    } else {
        elemento.textContent = valor ?? "";
    }
}

function formatearMonedaVenta(valor) {

    const numero = Number(valor);

    if (!Number.isFinite(numero)) {
        return "Q0.00";
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

function escaparHTMLVenta(valor) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}