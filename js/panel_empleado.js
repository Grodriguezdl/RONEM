"use strict";

/* =========================================================
   CONFIGURACIÓN DE RUTAS
========================================================= */

const API = {
    productos:
        "api/api_empleado_productos.php",

    clientes:
        "api/api_empleado_clientes.php",

    ventas:
        "api/api_ventas.php",

    procesarVenta:
        "api/procesar_venta.php",

    canjes:
        "api/api_empleado_canjes.php"
};


/* =========================================================
   ESTADO GENERAL DEL PANEL
========================================================= */

const estadoPanel = {
    productos: [],
    clientes: [],
    ventas: [],
    canjes: [],

    carrito: new Map(),

    cargandoVenta: false
};


/* =========================================================
   REFERENCIAS DEL DOM
========================================================= */

const elementos = {
    sidebar:
        document.getElementById("sidebarEmpleado"),

    btnMenuMovil:
        document.getElementById("btnMenuMovil"),

    tituloSeccion:
        document.getElementById("tituloSeccion"),

    descripcionSeccion:
        document.getElementById("descripcionSeccion"),

    menuItems:
        document.querySelectorAll(".menu-item"),

    secciones:
        document.querySelectorAll(".seccion-panel"),

    botonesIrSeccion:
        document.querySelectorAll("[data-ir-seccion]"),


    /* Resumen */

    resumenTotalVentas:
        document.getElementById("resumenTotalVentas"),

    resumenMontoVentas:
        document.getElementById("resumenMontoVentas"),

    resumenProductos:
        document.getElementById("resumenProductos"),

    resumenCanjes:
        document.getElementById("resumenCanjes"),

    tablaVentasRecientes:
        document.getElementById("tablaVentasRecientes"),


    /* Nueva venta */

    buscarProductoVenta:
        document.getElementById("buscarProductoVenta"),

    listaProductosVenta:
        document.getElementById("listaProductosVenta"),

    clienteVenta:
        document.getElementById("clienteVenta"),

    carritoVenta:
        document.getElementById("carritoVenta"),

    cantidadProductosVenta:
        document.getElementById("cantidadProductosVenta"),

    totalVenta:
        document.getElementById("totalVenta"),

    btnProcesarVenta:
        document.getElementById("btnProcesarVenta"),


    /* Productos */

    buscarProductos:
        document.getElementById("buscarProductos"),

    tablaProductos:
        document.getElementById("tablaProductos"),


    /* Clientes */

    buscarClientes:
        document.getElementById("buscarClientes"),

    tablaClientes:
        document.getElementById("tablaClientes"),


    /* Historial */

    tablaHistorialVentas:
        document.getElementById("tablaHistorialVentas"),

    btnActualizarHistorial:
        document.getElementById("btnActualizarHistorial"),


    /* Canjes */

    filtroEstadoCanje:
        document.getElementById("filtroEstadoCanje"),

    buscarCanjes:
        document.getElementById("buscarCanjes"),

    tablaCanjes:
        document.getElementById("tablaCanjes"),


    /* Modal */

    modalDetalleVenta:
        document.getElementById("modalDetalleVenta"),

    contenidoDetalleVenta:
        document.getElementById("contenidoDetalleVenta")
};


/* =========================================================
   INFORMACIÓN DE LAS SECCIONES
========================================================= */

const informacionSecciones = {
    inicio: {
        titulo: "Inicio",
        descripcion:
            "Resumen general de las operaciones."
    },

    "nueva-venta": {
        titulo: "Nueva venta",
        descripcion:
            "Selecciona los productos y registra una venta."
    },

    productos: {
        titulo: "Productos",
        descripcion:
            "Consulta el catálogo y las existencias."
    },

    clientes: {
        titulo: "Clientes",
        descripcion:
            "Consulta clientes y puntos de fidelización."
    },

    historial: {
        titulo: "Historial de ventas",
        descripcion:
            "Revisa las ventas registradas."
    },

    canjes: {
        titulo: "Canjes",
        descripcion:
            "Valida y administra los canjes de clientes."
    }
};


/* =========================================================
   INICIALIZACIÓN
========================================================= */

document.addEventListener("DOMContentLoaded", async () => {
    configurarNavegacion();
    configurarEventos();
    renderizarCarrito();

    await cargarDatosIniciales();
});


async function cargarDatosIniciales() {
    await Promise.allSettled([
        cargarProductos(),
        cargarClientes(),
        cargarVentas(),
        cargarCanjes()
    ]);

    actualizarResumen();
}


/* =========================================================
   NAVEGACIÓN DEL PANEL
========================================================= */

function configurarNavegacion() {
    elementos.menuItems.forEach((boton) => {
        boton.addEventListener("click", () => {
            const seccion =
                boton.dataset.seccion;

            mostrarSeccion(seccion);
        });
    });

    elementos.botonesIrSeccion.forEach((boton) => {
        boton.addEventListener("click", () => {
            const seccion =
                boton.dataset.irSeccion;

            mostrarSeccion(seccion);
        });
    });

    elementos.btnMenuMovil?.addEventListener(
        "click",
        () => {
            elementos.sidebar?.classList.toggle(
                "abierto"
            );
        }
    );

    document.addEventListener("click", (evento) => {
        if (window.innerWidth > 900) {
            return;
        }

        if (
            !elementos.sidebar ||
            !elementos.btnMenuMovil
        ) {
            return;
        }

        const clicDentroSidebar =
            elementos.sidebar.contains(
                evento.target
            );

        const clicBotonMenu =
            elementos.btnMenuMovil.contains(
                evento.target
            );

        if (
            !clicDentroSidebar &&
            !clicBotonMenu
        ) {
            elementos.sidebar.classList.remove(
                "abierto"
            );
        }
    });
}


function mostrarSeccion(nombreSeccion) {
    const informacion =
        informacionSecciones[nombreSeccion];

    if (!informacion) {
        return;
    }

    elementos.secciones.forEach((seccion) => {
        seccion.classList.remove("activa");
    });

    elementos.menuItems.forEach((item) => {
        item.classList.remove("active");
    });

    const seccionActiva =
        document.getElementById(
            `seccion-${nombreSeccion}`
        );

    const menuActivo =
        document.querySelector(
            `.menu-item[data-seccion="${nombreSeccion}"]`
        );

    seccionActiva?.classList.add("activa");
    menuActivo?.classList.add("active");

    if (elementos.tituloSeccion) {
        elementos.tituloSeccion.textContent =
            informacion.titulo;
    }

    if (elementos.descripcionSeccion) {
        elementos.descripcionSeccion.textContent =
            informacion.descripcion;
    }

    elementos.sidebar?.classList.remove(
        "abierto"
    );

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}


/* =========================================================
   EVENTOS GENERALES
========================================================= */

function configurarEventos() {
    elementos.buscarProductoVenta?.addEventListener(
        "input",
        aplicarDebounce((evento) => {
            filtrarProductosVenta(
                evento.target.value
            );
        }, 250)
    );

    elementos.buscarProductos?.addEventListener(
        "input",
        aplicarDebounce((evento) => {
            filtrarTablaProductos(
                evento.target.value
            );
        }, 250)
    );

    elementos.buscarClientes?.addEventListener(
        "input",
        aplicarDebounce((evento) => {
            filtrarTablaClientes(
                evento.target.value
            );
        }, 250)
    );

    elementos.buscarCanjes?.addEventListener(
        "input",
        aplicarDebounce((evento) => {
            filtrarCanjes(
                evento.target.value
            );
        }, 250)
    );

    elementos.filtroEstadoCanje?.addEventListener(
        "change",
        () => {
            filtrarCanjes(
                elementos.buscarCanjes?.value ?? ""
            );
        }
    );

    elementos.btnActualizarHistorial?.addEventListener(
        "click",
        async () => {
            await cargarVentas(true);
        }
    );

    elementos.btnProcesarVenta?.addEventListener(
        "click",
        procesarVenta
    );
}


/* =========================================================
   PETICIONES HTTP
========================================================= */

async function solicitarJSON(
    url,
    opciones = {}
) {
    const configuracion = {
        credentials: "same-origin",
        ...opciones
    };

    let respuesta;

    try {
        respuesta = await fetch(
            url,
            configuracion
        );
    } catch (error) {
        throw new Error(
            "No fue posible conectar con el servidor."
        );
    }

    const texto =
        await respuesta.text();

    let datos = {};

    if (texto.trim() !== "") {
        try {
            datos = JSON.parse(texto);
        } catch (error) {
            console.error(
                "Respuesta no válida:",
                texto
            );

            throw new Error(
                "El servidor devolvió una respuesta no válida."
            );
        }
    }

    if (!respuesta.ok) {
        throw new Error(
            datos.message ||
            datos.mensaje ||
            `Error HTTP ${respuesta.status}.`
        );
    }

    if (
        datos.success === false
    ) {
        throw new Error(
            datos.message ||
            datos.mensaje ||
            "La operación no pudo completarse."
        );
    }

    return datos;
}


/* =========================================================
   PRODUCTOS
========================================================= */

async function cargarProductos(
    mostrarMensaje = false
) {
    colocarFilaCargando(
        elementos.tablaProductos,
        5,
        "Cargando productos..."
    );

    colocarMensaje(
        elementos.listaProductosVenta,
        "Cargando productos..."
    );

    try {
        const respuesta =
            await solicitarJSON(
                `${API.productos}?action=listar`
            );

        estadoPanel.productos =
            obtenerArregloRespuesta(respuesta);

        renderizarTablaProductos(
            estadoPanel.productos
        );

        renderizarProductosVenta(
            estadoPanel.productos
        );

        actualizarResumen();

        if (mostrarMensaje) {
            mostrarNotificacion(
                "Productos actualizados.",
                "exito"
            );
        }

    } catch (error) {
        colocarFilaError(
            elementos.tablaProductos,
            5,
            error.message
        );

        colocarMensaje(
            elementos.listaProductosVenta,
            error.message,
            true
        );

        mostrarNotificacion(
            error.message,
            "error"
        );
    }
}


function renderizarTablaProductos(productos) {
    if (!elementos.tablaProductos) {
        return;
    }

    if (!productos.length) {
        colocarFilaVacia(
            elementos.tablaProductos,
            5,
            "No se encontraron productos."
        );

        return;
    }

    elementos.tablaProductos.innerHTML =
        productos.map((producto) => {
            const stock =
                numeroEntero(
                    producto.Stock
                );

            const activo =
                numeroEntero(
                    producto.Estado
                ) === 1;

            const claseStock =
                stock <= 5
                    ? "stock-bajo"
                    : "stock-normal";

            return `
                <tr>
                    <td>
                        <strong>
                            ${escaparHTML(
                                producto.Nombre
                            )}
                        </strong>

                        ${
                            producto.Descripcion
                                ? `
                                    <div class="text-muted small mt-1">
                                        ${escaparHTML(
                                            recortarTexto(
                                                producto.Descripcion,
                                                70
                                            )
                                        )}
                                    </div>
                                `
                                : ""
                        }
                    </td>

                    <td>
                        ${escaparHTML(
                            producto.Categoria ||
                            "Sin categoría"
                        )}
                    </td>

                    <td>
                        ${formatearMoneda(
                            producto.Precio
                        )}
                    </td>

                    <td>
                        <span class="${claseStock}">
                            ${stock}
                        </span>
                    </td>

                    <td>
                        ${crearBadge(
                            activo
                                ? "Activo"
                                : "Inactivo"
                        )}
                    </td>
                </tr>
            `;
        }).join("");
}


function renderizarProductosVenta(productos) {
    if (!elementos.listaProductosVenta) {
        return;
    }

    const productosDisponibles =
        productos.filter((producto) => {
            return (
                numeroEntero(
                    producto.Estado
                ) === 1
            );
        });

    if (!productosDisponibles.length) {
        colocarMensaje(
            elementos.listaProductosVenta,
            "No hay productos disponibles."
        );

        return;
    }

    elementos.listaProductosVenta.innerHTML =
        productosDisponibles.map((producto) => {
            const idProducto =
                numeroEntero(
                    producto.Id_producto
                );

            const stock =
                numeroEntero(
                    producto.Stock
                );

            const agotado =
                stock <= 0;

            return `
                <article class="producto-venta-card">

                    <div>
                        <h4>
                            ${escaparHTML(
                                producto.Nombre
                            )}
                        </h4>

                        <p>
                            ${escaparHTML(
                                producto.Categoria ||
                                "Sin categoría"
                            )}
                        </p>
                    </div>

                    <div class="producto-venta-info">

                        <span class="producto-venta-precio">
                            ${formatearMoneda(
                                producto.Precio
                            )}
                        </span>

                        <span class="${
                            stock <= 5
                                ? "stock-bajo"
                                : "stock-normal"
                        }">
                            Stock: ${stock}
                        </span>

                    </div>

                    <button
                        class="btn-agregar-producto"
                        type="button"
                        data-agregar-producto="${idProducto}"
                        ${agotado ? "disabled" : ""}
                    >
                        <i class="bi bi-cart-plus"></i>

                        ${
                            agotado
                                ? "Sin existencias"
                                : "Agregar"
                        }
                    </button>

                </article>
            `;
        }).join("");

    elementos.listaProductosVenta
        .querySelectorAll(
            "[data-agregar-producto]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    agregarProductoAlCarrito(
                        numeroEntero(
                            boton.dataset
                                .agregarProducto
                        )
                    );
                }
            );
        });
}


function filtrarProductosVenta(texto) {
    const termino =
        normalizarTexto(texto);

    if (!termino) {
        renderizarProductosVenta(
            estadoPanel.productos
        );

        return;
    }

    const filtrados =
        estadoPanel.productos.filter(
            (producto) => {
                const contenido =
                    normalizarTexto(
                        [
                            producto.Nombre,
                            producto.Descripcion,
                            producto.Categoria
                        ].join(" ")
                    );

                return contenido.includes(
                    termino
                );
            }
        );

    renderizarProductosVenta(
        filtrados
    );
}


function filtrarTablaProductos(texto) {
    const termino =
        normalizarTexto(texto);

    if (!termino) {
        renderizarTablaProductos(
            estadoPanel.productos
        );

        return;
    }

    const filtrados =
        estadoPanel.productos.filter(
            (producto) => {
                const contenido =
                    normalizarTexto(
                        [
                            producto.Nombre,
                            producto.Descripcion,
                            producto.Categoria,
                            producto.Precio,
                            producto.Stock
                        ].join(" ")
                    );

                return contenido.includes(
                    termino
                );
            }
        );

    renderizarTablaProductos(
        filtrados
    );
}


/* =========================================================
   CLIENTES
========================================================= */

async function cargarClientes() {
    colocarFilaCargando(
        elementos.tablaClientes,
        5,
        "Cargando clientes..."
    );

    try {
        const respuesta =
            await solicitarJSON(
                `${API.clientes}?action=listar`
            );

        estadoPanel.clientes =
            obtenerArregloRespuesta(respuesta);

        renderizarTablaClientes(
            estadoPanel.clientes
        );

        renderizarSelectorClientes();

    } catch (error) {
        colocarFilaError(
            elementos.tablaClientes,
            5,
            error.message
        );

        mostrarNotificacion(
            error.message,
            "error"
        );
    }
}


function renderizarTablaClientes(clientes) {
    if (!elementos.tablaClientes) {
        return;
    }

    if (!clientes.length) {
        colocarFilaVacia(
            elementos.tablaClientes,
            5,
            "No se encontraron clientes."
        );

        return;
    }

    elementos.tablaClientes.innerHTML =
        clientes.map((cliente) => {
            const activo =
                numeroEntero(
                    cliente.Activo
                ) === 1;

            const verificado =
                numeroEntero(
                    cliente.Correo_Verificado
                ) === 1;

            return `
                <tr>
                    <td>
                        <strong>
                            ${escaparHTML(
                                cliente.Nombre_completo ||
                                `${cliente.Nombre ?? ""} ${cliente.Apellido ?? ""}`.trim() ||
                                "Cliente"
                            )}
                        </strong>
                    </td>

                    <td>
                        ${escaparHTML(
                            cliente.Correo
                        )}
                    </td>

                    <td>
                        <strong>
                            ${numeroEntero(
                                cliente.Puntos_disponibles
                            )}
                        </strong>
                    </td>

                    <td>
                        ${crearBadge(
                            verificado
                                ? "Verificado"
                                : "No verificado"
                        )}
                    </td>

                    <td>
                        ${crearBadge(
                            activo
                                ? "Activo"
                                : "Inactivo"
                        )}
                    </td>
                </tr>
            `;
        }).join("");
}


function renderizarSelectorClientes() {
    if (!elementos.clienteVenta) {
        return;
    }

    const valorActual =
        elementos.clienteVenta.value;

    const clientesActivos =
        estadoPanel.clientes.filter(
            (cliente) =>
                numeroEntero(
                    cliente.Activo
                ) === 1
        );

    elementos.clienteVenta.innerHTML = `
        <option value="">
            Consumidor final
        </option>

        ${clientesActivos.map((cliente) => `
            <option
                value="${numeroEntero(
                    cliente.Id_usuario
                )}"
            >
                ${escaparHTML(
                    cliente.Nombre_completo ||
                    `${cliente.Nombre ?? ""} ${cliente.Apellido ?? ""}`.trim()
                )}
                — ${escaparHTML(
                    cliente.Correo
                )}
            </option>
        `).join("")}
    `;

    const existeValor =
        [...elementos.clienteVenta.options]
            .some(
                (opcion) =>
                    opcion.value === valorActual
            );

    if (existeValor) {
        elementos.clienteVenta.value =
            valorActual;
    }
}


function filtrarTablaClientes(texto) {
    const termino =
        normalizarTexto(texto);

    if (!termino) {
        renderizarTablaClientes(
            estadoPanel.clientes
        );

        return;
    }

    const filtrados =
        estadoPanel.clientes.filter(
            (cliente) => {
                const contenido =
                    normalizarTexto(
                        [
                            cliente.Nombre,
                            cliente.Apellido,
                            cliente.Nombre_completo,
                            cliente.Correo,
                            cliente.Puntos_disponibles
                        ].join(" ")
                    );

                return contenido.includes(
                    termino
                );
            }
        );

    renderizarTablaClientes(
        filtrados
    );
}


/* =========================================================
   CARRITO DE VENTA
========================================================= */

function agregarProductoAlCarrito(idProducto) {
    const producto =
        estadoPanel.productos.find(
            (item) =>
                numeroEntero(
                    item.Id_producto
                ) === idProducto
        );

    if (!producto) {
        mostrarNotificacion(
            "El producto seleccionado no existe.",
            "error"
        );

        return;
    }

    const stock =
        numeroEntero(
            producto.Stock
        );

    if (stock <= 0) {
        mostrarNotificacion(
            "El producto no tiene existencias.",
            "advertencia"
        );

        return;
    }

    const productoActual =
        estadoPanel.carrito.get(
            idProducto
        );

    if (productoActual) {
        if (
            productoActual.cantidad >= stock
        ) {
            mostrarNotificacion(
                `Solo hay ${stock} unidades disponibles.`,
                "advertencia"
            );

            return;
        }

        productoActual.cantidad += 1;

        estadoPanel.carrito.set(
            idProducto,
            productoActual
        );

    } else {
        estadoPanel.carrito.set(
            idProducto,
            {
                idProducto,
                nombre:
                    producto.Nombre,
                precio:
                    numeroDecimal(
                        producto.Precio
                    ),
                stock,
                cantidad: 1
            }
        );
    }

    renderizarCarrito();

    mostrarNotificacion(
        "Producto agregado a la venta.",
        "exito",
        1800
    );
}


function cambiarCantidadCarrito(
    idProducto,
    cambio
) {
    const producto =
        estadoPanel.carrito.get(
            idProducto
        );

    if (!producto) {
        return;
    }

    const nuevaCantidad =
        producto.cantidad + cambio;

    if (nuevaCantidad <= 0) {
        estadoPanel.carrito.delete(
            idProducto
        );

        renderizarCarrito();
        return;
    }

    if (
        nuevaCantidad > producto.stock
    ) {
        mostrarNotificacion(
            `Solo hay ${producto.stock} unidades disponibles.`,
            "advertencia"
        );

        return;
    }

    producto.cantidad =
        nuevaCantidad;

    estadoPanel.carrito.set(
        idProducto,
        producto
    );

    renderizarCarrito();
}


function quitarProductoCarrito(idProducto) {
    estadoPanel.carrito.delete(
        idProducto
    );

    renderizarCarrito();
}


function renderizarCarrito() {
    if (!elementos.carritoVenta) {
        return;
    }

    const productos =
        [...estadoPanel.carrito.values()];

    if (!productos.length) {
        elementos.carritoVenta.innerHTML = `
            <p class="mensaje-vacio">
                No hay productos agregados.
            </p>
        `;

        actualizarTotalesCarrito();
        return;
    }

    elementos.carritoVenta.innerHTML =
        productos.map((producto) => {
            const subtotal =
                producto.precio *
                producto.cantidad;

            return `
                <article class="carrito-item">

                    <div>
                        <h4>
                            ${escaparHTML(
                                producto.nombre
                            )}
                        </h4>

                        <p>
                            ${formatearMoneda(
                                producto.precio
                            )}
                            ×
                            ${producto.cantidad}
                            =
                            <strong>
                                ${formatearMoneda(
                                    subtotal
                                )}
                            </strong>
                        </p>
                    </div>

                    <div class="carrito-item-controles">

                        <button
                            class="btn-cantidad"
                            type="button"
                            data-restar-producto="${producto.idProducto}"
                            title="Restar"
                        >
                            <i class="bi bi-dash"></i>
                        </button>

                        <span class="cantidad-carrito">
                            ${producto.cantidad}
                        </span>

                        <button
                            class="btn-cantidad"
                            type="button"
                            data-sumar-producto="${producto.idProducto}"
                            title="Agregar"
                        >
                            <i class="bi bi-plus"></i>
                        </button>

                        <button
                            class="btn-quitar-carrito"
                            type="button"
                            data-quitar-producto="${producto.idProducto}"
                            title="Quitar"
                        >
                            <i class="bi bi-trash"></i>
                        </button>

                    </div>

                </article>
            `;
        }).join("");

    elementos.carritoVenta
        .querySelectorAll(
            "[data-restar-producto]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    cambiarCantidadCarrito(
                        numeroEntero(
                            boton.dataset
                                .restarProducto
                        ),
                        -1
                    );
                }
            );
        });

    elementos.carritoVenta
        .querySelectorAll(
            "[data-sumar-producto]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    cambiarCantidadCarrito(
                        numeroEntero(
                            boton.dataset
                                .sumarProducto
                        ),
                        1
                    );
                }
            );
        });

    elementos.carritoVenta
        .querySelectorAll(
            "[data-quitar-producto]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    quitarProductoCarrito(
                        numeroEntero(
                            boton.dataset
                                .quitarProducto
                        )
                    );
                }
            );
        });

    actualizarTotalesCarrito();
}


function actualizarTotalesCarrito() {
    const productos =
        [...estadoPanel.carrito.values()];

    const cantidadTotal =
        productos.reduce(
            (total, producto) =>
                total + producto.cantidad,
            0
        );

    const montoTotal =
        productos.reduce(
            (total, producto) =>
                total +
                (
                    producto.precio *
                    producto.cantidad
                ),
            0
        );

    if (elementos.cantidadProductosVenta) {
        elementos.cantidadProductosVenta
            .textContent =
            String(cantidadTotal);
    }

    if (elementos.totalVenta) {
        elementos.totalVenta.textContent =
            formatearMoneda(montoTotal);
    }

    if (elementos.btnProcesarVenta) {
        elementos.btnProcesarVenta.disabled =
            productos.length === 0 ||
            estadoPanel.cargandoVenta;
    }
}


/* =========================================================
   PROCESAR VENTA
========================================================= */

async function procesarVenta() {
    if (estadoPanel.cargandoVenta) {
        return;
    }

    const productos =
        [...estadoPanel.carrito.values()];

    if (!productos.length) {
        mostrarNotificacion(
            "Debes agregar productos a la venta.",
            "advertencia"
        );

        return;
    }

    const confirmado =
        window.confirm(
            "¿Deseas registrar esta venta?"
        );

    if (!confirmado) {
        return;
    }

    const idClienteTexto =
        elementos.clienteVenta?.value ?? "";

    const idCliente =
        idClienteTexto === ""
            ? null
            : numeroEntero(
                idClienteTexto
            );

    const cuerpo = {
        idCliente,

        productos:
            productos.map((producto) => ({
                idProducto:
                    producto.idProducto,

                cantidad:
                    producto.cantidad
            }))
    };

    estadoPanel.cargandoVenta = true;

    cambiarEstadoBotonProcesar(
        true
    );

    try {
        const respuesta =
            await solicitarJSON(
                API.procesarVenta,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify(cuerpo)
                }
            );

        const venta =
            respuesta.venta ?? {};

        mostrarNotificacion(
            respuesta.message ||
            `Venta #${venta.Id_venta ?? ""} registrada correctamente.`,
            "exito",
            4500
        );

        estadoPanel.carrito.clear();

        if (elementos.clienteVenta) {
            elementos.clienteVenta.value = "";
        }

        if (elementos.buscarProductoVenta) {
            elementos.buscarProductoVenta.value =
                "";
        }

        renderizarCarrito();

        await Promise.allSettled([
            cargarProductos(),
            cargarVentas()
        ]);

        mostrarSeccion("historial");

    } catch (error) {
        mostrarNotificacion(
            error.message,
            "error",
            5000
        );

    } finally {
        estadoPanel.cargandoVenta = false;

        cambiarEstadoBotonProcesar(
            false
        );

        actualizarTotalesCarrito();
    }
}


function cambiarEstadoBotonProcesar(cargando) {
    if (!elementos.btnProcesarVenta) {
        return;
    }

    elementos.btnProcesarVenta.innerHTML =
        cargando
            ? `
                <span
                    class="spinner-border spinner-border-sm"
                    aria-hidden="true"
                ></span>
                Procesando...
            `
            : `
                <i class="bi bi-check-circle"></i>
                Procesar venta
            `;

    elementos.btnProcesarVenta.disabled =
        cargando ||
        estadoPanel.carrito.size === 0;
}


/* =========================================================
   VENTAS E HISTORIAL
========================================================= */

async function cargarVentas(
    mostrarMensaje = false
) {
    colocarFilaCargando(
        elementos.tablaVentasRecientes,
        6,
        "Cargando ventas..."
    );

    colocarFilaCargando(
        elementos.tablaHistorialVentas,
        7,
        "Cargando historial..."
    );

    try {
        const respuesta =
            await solicitarJSON(
                `${API.ventas}?action=listarVentas`
            );

        estadoPanel.ventas =
            obtenerArregloRespuesta(respuesta);

        renderizarVentasRecientes();
        renderizarHistorialVentas();
        actualizarResumen();

        if (mostrarMensaje) {
            mostrarNotificacion(
                "Historial actualizado.",
                "exito"
            );
        }

    } catch (error) {
        colocarFilaError(
            elementos.tablaVentasRecientes,
            6,
            error.message
        );

        colocarFilaError(
            elementos.tablaHistorialVentas,
            7,
            error.message
        );

        mostrarNotificacion(
            error.message,
            "error"
        );
    }
}


function renderizarVentasRecientes() {
    if (!elementos.tablaVentasRecientes) {
        return;
    }

    const ventasRecientes =
        estadoPanel.ventas.slice(0, 5);

    if (!ventasRecientes.length) {
        colocarFilaVacia(
            elementos.tablaVentasRecientes,
            6,
            "Todavía no hay ventas registradas."
        );

        return;
    }

    elementos.tablaVentasRecientes.innerHTML =
        ventasRecientes.map((venta) => `
            <tr>
                <td>
                    #${numeroEntero(
                        venta.Id_venta
                    )}
                </td>

                <td>
                    ${escaparHTML(
                        venta.Cliente ||
                        "Consumidor final"
                    )}
                </td>

                <td>
                    ${formatearFecha(
                        venta.Fecha
                    )}
                </td>

                <td>
                    ${numeroEntero(
                        venta.Total_productos ??
                        venta.Cantidad_productos ??
                        0
                    )}
                </td>

                <td>
                    <strong>
                        ${formatearMoneda(
                            venta.Total
                        )}
                    </strong>
                </td>

                <td>
                    ${crearBadge(
                        venta.Estado ||
                        "Completada"
                    )}
                </td>
            </tr>
        `).join("");
}


function renderizarHistorialVentas() {
    if (!elementos.tablaHistorialVentas) {
        return;
    }

    if (!estadoPanel.ventas.length) {
        colocarFilaVacia(
            elementos.tablaHistorialVentas,
            7,
            "Todavía no hay ventas registradas."
        );

        return;
    }

    elementos.tablaHistorialVentas.innerHTML =
        estadoPanel.ventas.map((venta) => {
            const idVenta =
                numeroEntero(
                    venta.Id_venta
                );

            return `
                <tr>
                    <td>
                        #${idVenta}
                    </td>

                    <td>
                        <strong>
                            ${escaparHTML(
                                venta.Cliente ||
                                "Consumidor final"
                            )}
                        </strong>

                        ${
                            venta.Correo_cliente
                                ? `
                                    <div class="text-muted small mt-1">
                                        ${escaparHTML(
                                            venta.Correo_cliente
                                        )}
                                    </div>
                                `
                                : ""
                        }
                    </td>

                    <td>
                        ${formatearFecha(
                            venta.Fecha
                        )}
                    </td>

                    <td>
                        ${numeroEntero(
                            venta.Total_productos ??
                            venta.Cantidad_productos ??
                            0
                        )}
                    </td>

                    <td>
                        <strong>
                            ${formatearMoneda(
                                venta.Total
                            )}
                        </strong>
                    </td>

                    <td>
                        ${crearBadge(
                            venta.Estado ||
                            "Completada"
                        )}
                    </td>

                    <td>
                        <button
                            class="btn-accion-tabla btn-accion-info"
                            type="button"
                            data-ver-venta="${idVenta}"
                            title="Ver detalle"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join("");

    elementos.tablaHistorialVentas
        .querySelectorAll(
            "[data-ver-venta]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    verDetalleVenta(
                        numeroEntero(
                            boton.dataset.verVenta
                        )
                    );
                }
            );
        });
}


async function verDetalleVenta(idVenta) {
    if (
        !idVenta ||
        !elementos.modalDetalleVenta ||
        !elementos.contenidoDetalleVenta
    ) {
        return;
    }

    elementos.contenidoDetalleVenta.innerHTML = `
        <div class="text-center py-4">
            <div
                class="spinner-border"
                role="status"
            ></div>

            <p class="mt-3 mb-0">
                Cargando detalle...
            </p>
        </div>
    `;

    const modal =
        bootstrap.Modal.getOrCreateInstance(
            elementos.modalDetalleVenta
        );

    modal.show();

    try {
        const respuesta =
            await solicitarJSON(
                `${API.ventas}?action=porVenta&id=${encodeURIComponent(
                    idVenta
                )}`
            );

        const detalles =
            obtenerDetallesVenta(
                respuesta
            );

        const venta =
            estadoPanel.ventas.find(
                (item) =>
                    numeroEntero(
                        item.Id_venta
                    ) === idVenta
            ) ?? {};

        renderizarDetalleVentaModal(
            venta,
            detalles
        );

    } catch (error) {
        elementos.contenidoDetalleVenta.innerHTML = `
            <div class="mensaje-error">
                ${escaparHTML(
                    error.message
                )}
            </div>
        `;
    }
}


function renderizarDetalleVentaModal(
    venta,
    detalles
) {
    if (!elementos.contenidoDetalleVenta) {
        return;
    }

    const totalCalculado =
        detalles.reduce(
            (total, detalle) => {
                const cantidad =
                    numeroEntero(
                        detalle.Cantidad
                    );

                const precio =
                    numeroDecimal(
                        detalle.Precio_unidad ??
                        detalle.Precio ??
                        0
                    );

                return total +
                    cantidad * precio;
            },
            0
        );

    const total =
        venta.Total ??
        totalCalculado;

    elementos.contenidoDetalleVenta.innerHTML = `
        <div class="detalle-venta-resumen">

            <div class="detalle-venta-dato">
                <span>Venta</span>
                <strong>
                    #${numeroEntero(
                        venta.Id_venta
                    )}
                </strong>
            </div>

            <div class="detalle-venta-dato">
                <span>Cliente</span>
                <strong>
                    ${escaparHTML(
                        venta.Cliente ||
                        "Consumidor final"
                    )}
                </strong>
            </div>

            <div class="detalle-venta-dato">
                <span>Total</span>
                <strong>
                    ${formatearMoneda(
                        total
                    )}
                </strong>
            </div>

        </div>

        ${
            detalles.length
                ? `
                    <div class="tabla-responsive">
                        <table class="tabla-panel">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                ${detalles.map((detalle) => {
                                    const cantidad =
                                        numeroEntero(
                                            detalle.Cantidad
                                        );

                                    const precio =
                                        numeroDecimal(
                                            detalle.Precio_unidad ??
                                            detalle.Precio ??
                                            0
                                        );

                                    return `
                                        <tr>
                                            <td>
                                                ${escaparHTML(
                                                    detalle.Producto ??
                                                    detalle.Nombre_producto ??
                                                    detalle.Nombre ??
                                                    `Producto #${detalle.Id_producto ?? ""}`
                                                )}
                                            </td>

                                            <td>
                                                ${cantidad}
                                            </td>

                                            <td>
                                                ${formatearMoneda(
                                                    precio
                                                )}
                                            </td>

                                            <td>
                                                <strong>
                                                    ${formatearMoneda(
                                                        cantidad *
                                                        precio
                                                    )}
                                                </strong>
                                            </td>
                                        </tr>
                                    `;
                                }).join("")}
                            </tbody>
                        </table>
                    </div>
                `
                : `
                    <p class="mensaje-vacio">
                        No se encontraron detalles para esta venta.
                    </p>
                `
        }
    `;
}


/* =========================================================
   CANJES
========================================================= */

async function cargarCanjes() {
    colocarFilaCargando(
        elementos.tablaCanjes,
        7,
        "Cargando canjes..."
    );

    try {
        const respuesta =
            await solicitarJSON(
                `${API.canjes}?action=listar`
            );

        estadoPanel.canjes =
            obtenerArregloRespuesta(respuesta);

        renderizarCanjes(
            estadoPanel.canjes
        );

        actualizarResumen();

    } catch (error) {
        colocarFilaError(
            elementos.tablaCanjes,
            7,
            error.message
        );

        mostrarNotificacion(
            error.message,
            "error"
        );
    }
}


function renderizarCanjes(canjes) {
    if (!elementos.tablaCanjes) {
        return;
    }

    if (!canjes.length) {
        colocarFilaVacia(
            elementos.tablaCanjes,
            7,
            "No se encontraron canjes."
        );

        return;
    }

    elementos.tablaCanjes.innerHTML =
        canjes.map((canje) => {
            const idCanje =
                numeroEntero(
                    canje.Id_canje
                );

            const estado =
                canje.Estado ||
                "Pendiente";

            let acciones = "";

            if (estado === "Pendiente") {
                acciones = `
                    <button
                        class="btn-accion-tabla btn-accion-exito"
                        type="button"
                        data-disponible-canje="${idCanje}"
                        title="Marcar disponible"
                    >
                        <i class="bi bi-check-lg"></i>
                    </button>
                `;
            }

            if (estado === "Disponible") {
                acciones = `
                    <button
                        class="btn-accion-tabla btn-accion-info"
                        type="button"
                        data-utilizar-canje="${idCanje}"
                        title="Marcar utilizado"
                    >
                        <i class="bi bi-gift-fill"></i>
                    </button>
                `;
            }

            if (!acciones) {
                acciones = `
                    <span class="text-muted">
                        —
                    </span>
                `;
            }

            return `
                <tr>
                    <td>
                        <strong>
                            ${escaparHTML(
                                canje.Codigo_canje
                            )}
                        </strong>
                    </td>

                    <td>
                        <strong>
                            ${escaparHTML(
                                canje.Cliente
                            )}
                        </strong>

                        ${
                            canje.Correo
                                ? `
                                    <div class="text-muted small mt-1">
                                        ${escaparHTML(
                                            canje.Correo
                                        )}
                                    </div>
                                `
                                : ""
                        }
                    </td>

                    <td>
                        ${escaparHTML(
                            canje.Recompensa
                        )}
                    </td>

                    <td>
                        ${numeroEntero(
                            canje.Puntos_utilizados
                        )}
                    </td>

                    <td>
                        ${formatearFecha(
                            canje.Fecha
                        )}
                    </td>

                    <td>
                        ${crearBadge(
                            estado
                        )}
                    </td>

                    <td>
                        <div class="d-flex gap-2">
                            ${acciones}
                        </div>
                    </td>
                </tr>
            `;
        }).join("");

    elementos.tablaCanjes
        .querySelectorAll(
            "[data-disponible-canje]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    marcarCanjeDisponible(
                        numeroEntero(
                            boton.dataset
                                .disponibleCanje
                        )
                    );
                }
            );
        });

    elementos.tablaCanjes
        .querySelectorAll(
            "[data-utilizar-canje]"
        )
        .forEach((boton) => {
            boton.addEventListener(
                "click",
                () => {
                    utilizarCanje(
                        numeroEntero(
                            boton.dataset
                                .utilizarCanje
                        )
                    );
                }
            );
        });
}


function filtrarCanjes(texto) {
    const termino =
        normalizarTexto(texto);

    const estadoSeleccionado =
        elementos.filtroEstadoCanje?.value ??
        "";

    const filtrados =
        estadoPanel.canjes.filter(
            (canje) => {
                const coincideEstado =
                    !estadoSeleccionado ||
                    canje.Estado ===
                        estadoSeleccionado;

                const contenido =
                    normalizarTexto(
                        [
                            canje.Codigo_canje,
                            canje.Cliente,
                            canje.Correo,
                            canje.Recompensa,
                            canje.Estado
                        ].join(" ")
                    );

                const coincideTexto =
                    !termino ||
                    contenido.includes(
                        termino
                    );

                return (
                    coincideEstado &&
                    coincideTexto
                );
            }
        );

    renderizarCanjes(
        filtrados
    );
}


async function marcarCanjeDisponible(idCanje) {
    const confirmado =
        window.confirm(
            "¿Deseas marcar este canje como disponible?"
        );

    if (!confirmado) {
        return;
    }

    try {
        const respuesta =
            await solicitarJSON(
                `${API.canjes}?action=disponible`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify({
                            idCanje
                        })
                }
            );

        mostrarNotificacion(
            respuesta.message ||
            "Canje actualizado.",
            "exito"
        );

        await cargarCanjes();

    } catch (error) {
        mostrarNotificacion(
            error.message,
            "error"
        );
    }
}


async function utilizarCanje(idCanje) {
    const confirmado =
        window.confirm(
            "¿Confirmas que el cliente utilizó este canje?"
        );

    if (!confirmado) {
        return;
    }

    try {
        const respuesta =
            await solicitarJSON(
                `${API.canjes}?action=utilizar`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body:
                        JSON.stringify({
                            idCanje
                        })
                }
            );

        mostrarNotificacion(
            respuesta.message ||
            "Canje utilizado correctamente.",
            "exito"
        );

        await cargarCanjes();

    } catch (error) {
        mostrarNotificacion(
            error.message,
            "error"
        );
    }
}


/* =========================================================
   RESUMEN DEL INICIO
========================================================= */

function actualizarResumen() {
    const ventas =
        estadoPanel.ventas;

    const totalVendido =
        ventas.reduce(
            (total, venta) =>
                total +
                numeroDecimal(
                    venta.Total
                ),
            0
        );

    const productosDisponibles =
        estadoPanel.productos.filter(
            (producto) => {
                return (
                    numeroEntero(
                        producto.Estado
                    ) === 1 &&
                    numeroEntero(
                        producto.Stock
                    ) > 0
                );
            }
        ).length;

    const canjesPendientes =
        estadoPanel.canjes.filter(
            (canje) =>
                canje.Estado ===
                "Pendiente"
        ).length;

    if (elementos.resumenTotalVentas) {
        elementos.resumenTotalVentas
            .textContent =
            String(ventas.length);
    }

    if (elementos.resumenMontoVentas) {
        elementos.resumenMontoVentas
            .textContent =
            formatearMoneda(
                totalVendido
            );
    }

    if (elementos.resumenProductos) {
        elementos.resumenProductos
            .textContent =
            String(
                productosDisponibles
            );
    }

    if (elementos.resumenCanjes) {
        elementos.resumenCanjes
            .textContent =
            String(
                canjesPendientes
            );
    }
}


/* =========================================================
   NOTIFICACIONES
========================================================= */

function mostrarNotificacion(
    mensaje,
    tipo = "info",
    duracion = 3500
) {
    let contenedor =
        document.getElementById(
            "contenedorNotificaciones"
        );

    if (!contenedor) {
        contenedor =
            document.createElement("div");

        contenedor.id =
            "contenedorNotificaciones";

        Object.assign(
            contenedor.style,
            {
                position: "fixed",
                top: "20px",
                right: "20px",
                zIndex: "2000",
                display: "flex",
                flexDirection: "column",
                gap: "10px",
                width:
                    "min(380px, calc(100vw - 40px))"
            }
        );

        document.body.appendChild(
            contenedor
        );
    }

    const colores = {
        exito: {
            fondo: "#198754",
            icono:
                "bi-check-circle-fill"
        },

        error: {
            fondo: "#dc3545",
            icono:
                "bi-x-circle-fill"
        },

        advertencia: {
            fondo: "#d99b16",
            icono:
                "bi-exclamation-triangle-fill"
        },

        info: {
            fondo: "#0d6efd",
            icono:
                "bi-info-circle-fill"
        }
    };

    const configuracion =
        colores[tipo] ??
        colores.info;

    const notificacion =
        document.createElement("div");

    Object.assign(
        notificacion.style,
        {
            padding: "14px 16px",
            borderRadius: "12px",
            background:
                configuracion.fondo,
            color: "#ffffff",
            boxShadow:
                "0 12px 28px rgba(0,0,0,.18)",
            display: "flex",
            alignItems: "flex-start",
            gap: "10px",
            opacity: "0",
            transform:
                "translateX(20px)",
            transition:
                "opacity .2s ease, transform .2s ease"
        }
    );

    const icono =
        document.createElement("i");

    icono.className =
        `bi ${configuracion.icono}`;

    icono.style.fontSize = "18px";

    const texto =
        document.createElement("div");

    texto.textContent =
        String(mensaje);

    texto.style.flex = "1";
    texto.style.fontWeight = "600";
    texto.style.fontSize = "14px";

    const cerrar =
        document.createElement("button");

    cerrar.type = "button";
    cerrar.innerHTML =
        '<i class="bi bi-x-lg"></i>';

    Object.assign(
        cerrar.style,
        {
            border: "none",
            background: "transparent",
            color: "#ffffff",
            cursor: "pointer",
            padding: "0"
        }
    );

    notificacion.append(
        icono,
        texto,
        cerrar
    );

    contenedor.appendChild(
        notificacion
    );

    requestAnimationFrame(() => {
        notificacion.style.opacity =
            "1";

        notificacion.style.transform =
            "translateX(0)";
    });

    const eliminar = () => {
        notificacion.style.opacity =
            "0";

        notificacion.style.transform =
            "translateX(20px)";

        window.setTimeout(() => {
            notificacion.remove();
        }, 220);
    };

    cerrar.addEventListener(
        "click",
        eliminar
    );

    window.setTimeout(
        eliminar,
        duracion
    );
}


/* =========================================================
   UTILIDADES DE RESPUESTAS
========================================================= */

function obtenerArregloRespuesta(respuesta) {
    const posibles = [
        respuesta.data,
        respuesta.ventas,
        respuesta.productos,
        respuesta.clientes,
        respuesta.canjes,
        respuesta.resultados
    ];

    const encontrado =
        posibles.find(
            (valor) =>
                Array.isArray(valor)
        );

    return encontrado ?? [];
}


function obtenerDetallesVenta(respuesta) {
    const posibles = [
        respuesta.data,
        respuesta.detalles,
        respuesta.productos,
        respuesta.detalle,
        respuesta.venta?.detalles,
        respuesta.venta?.productos
    ];

    const encontrado =
        posibles.find(
            (valor) =>
                Array.isArray(valor)
        );

    return encontrado ?? [];
}


/* =========================================================
   UTILIDADES VISUALES
========================================================= */

function crearBadge(estado) {
    const texto =
        String(estado || "Sin estado");

    const clase =
        normalizarTexto(texto)
            .replace(/\s+/g, "-");

    return `
        <span class="badge-estado estado-${escaparAtributo(
            clase
        )}">
            ${escaparHTML(texto)}
        </span>
    `;
}


function colocarFilaCargando(
    tbody,
    columnas,
    mensaje
) {
    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="${columnas}">
                <span
                    class="spinner-border spinner-border-sm me-2"
                    aria-hidden="true"
                ></span>

                ${escaparHTML(mensaje)}
            </td>
        </tr>
    `;
}


function colocarFilaVacia(
    tbody,
    columnas,
    mensaje
) {
    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="${columnas}">
                ${escaparHTML(mensaje)}
            </td>
        </tr>
    `;
}


function colocarFilaError(
    tbody,
    columnas,
    mensaje
) {
    if (!tbody) {
        return;
    }

    tbody.innerHTML = `
        <tr>
            <td colspan="${columnas}">
                <div class="mensaje-error">
                    ${escaparHTML(mensaje)}
                </div>
            </td>
        </tr>
    `;
}


function colocarMensaje(
    contenedor,
    mensaje,
    error = false
) {
    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <p class="${
            error
                ? "mensaje-error"
                : "mensaje-vacio"
        }">
            ${escaparHTML(mensaje)}
        </p>
    `;
}


/* =========================================================
   FORMATO Y VALIDACIÓN
========================================================= */

function formatearMoneda(valor) {
    const numero =
        numeroDecimal(valor);

    return new Intl.NumberFormat(
        "es-GT",
        {
            style: "currency",
            currency: "GTQ",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    ).format(numero);
}


function formatearFecha(valor) {
    if (!valor) {
        return "Sin fecha";
    }

    const texto =
        String(valor).trim();

    const fecha =
        new Date(
            texto.includes("T")
                ? texto
                : texto.replace(
                    " ",
                    "T"
                )
        );

    if (
        Number.isNaN(
            fecha.getTime()
        )
    ) {
        return escaparHTML(texto);
    }

    return new Intl.DateTimeFormat(
        "es-GT",
        {
            dateStyle: "medium",
            timeStyle: "short"
        }
    ).format(fecha);
}


function numeroEntero(valor) {
    const numero =
        Number.parseInt(
            valor,
            10
        );

    return Number.isFinite(numero)
        ? numero
        : 0;
}


function numeroDecimal(valor) {
    const numero =
        Number.parseFloat(valor);

    return Number.isFinite(numero)
        ? numero
        : 0;
}


function normalizarTexto(valor) {
    return String(valor ?? "")
        .normalize("NFD")
        .replace(
            /[\u0300-\u036f]/g,
            ""
        )
        .toLowerCase()
        .trim();
}


function recortarTexto(
    texto,
    longitud
) {
    const valor =
        String(texto ?? "");

    if (
        valor.length <= longitud
    ) {
        return valor;
    }

    return (
        valor.slice(
            0,
            longitud
        ).trim() +
        "..."
    );
}


function escaparHTML(valor) {
    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}


function escaparAtributo(valor) {
    return escaparHTML(valor)
        .replace(/\s+/g, "-");
}


function aplicarDebounce(
    funcion,
    espera = 300
) {
    let temporizador;

    return (...argumentos) => {
        window.clearTimeout(
            temporizador
        );

        temporizador =
            window.setTimeout(
                () => {
                    funcion(
                        ...argumentos
                    );
                },
                espera
            );
    };
}