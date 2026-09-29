//==================================================
// VARIABLES GENERALES
//==================================================

let saldoPuntosActual = 0;

let recompensasCargadas = false;
let canjesCargados = false;

let recompensaSeleccionada = null;
let modalConfirmarCanje = null;


//==================================================
// INICIO
//==================================================

document.addEventListener("DOMContentLoaded", () => {

    inicializarFidelizacion();

});


function inicializarFidelizacion() {

    const modalElement =
        document.getElementById("modalConfirmarCanje");

    if (modalElement) {

        modalConfirmarCanje =
            new bootstrap.Modal(modalElement);

    }

    configurarPestanas();
    configurarBotones();

    cargarTodoFidelizacion();

}


//==================================================
// CONFIGURAR BOTONES
//==================================================

function configurarBotones() {

    const btnActualizar =
        document.getElementById(
            "btnActualizarFidelizacion"
        );

    const btnConfirmarCanje =
        document.getElementById(
            "btnConfirmarCanje"
        );

    if (btnActualizar) {

        btnActualizar.addEventListener(
            "click",
            async () => {

                btnActualizar.disabled = true;

                const contenidoOriginal =
                    btnActualizar.innerHTML;

                btnActualizar.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-2"
                        role="status"
                    ></span>

                    ACTUALIZANDO...
                `;

                await cargarTodoFidelizacion();

                btnActualizar.innerHTML =
                    contenidoOriginal;

                btnActualizar.disabled = false;

            }
        );

    }

    if (btnConfirmarCanje) {

        btnConfirmarCanje.addEventListener(
            "click",
            procesarCanje
        );

    }

}


//==================================================
// PESTAÑAS
//==================================================

function configurarPestanas() {

    const botones =
        document.querySelectorAll(
            ".fidelizacion-tab"
        );

    botones.forEach(boton => {

        boton.addEventListener(
            "click",
            () => {

                const panel =
                    boton.dataset.panel;

                botones.forEach(item => {

                    item.classList.remove(
                        "active"
                    );

                });

                boton.classList.add(
                    "active"
                );

                mostrarPanel(panel);

            }
        );

    });

}


function mostrarPanel(panel) {

    const panelMovimientos =
        document.getElementById(
            "panelMovimientos"
        );

    const panelRecompensas =
        document.getElementById(
            "panelRecompensas"
        );

    const panelCanjes =
        document.getElementById(
            "panelCanjes"
        );

    panelMovimientos?.classList.add(
        "d-none"
    );

    panelRecompensas?.classList.add(
        "d-none"
    );

    panelCanjes?.classList.add(
        "d-none"
    );

    switch (panel) {

        case "recompensas":

            panelRecompensas?.classList.remove(
                "d-none"
            );

            if (!recompensasCargadas) {

                cargarRecompensas();

            }

            break;

        case "canjes":

            panelCanjes?.classList.remove(
                "d-none"
            );

            if (!canjesCargados) {

                cargarCanjes();

            }

            break;

        case "movimientos":
        default:

            panelMovimientos?.classList.remove(
                "d-none"
            );

            break;

    }

}


//==================================================
// CARGAR TODO
//==================================================

async function cargarTodoFidelizacion() {

    limpiarAlerta();

    recompensasCargadas = false;
    canjesCargados = false;

    await Promise.allSettled([
        cargarResumen(),
        cargarMovimientos()
    ]);

    const panelRecompensas =
        document.getElementById(
            "panelRecompensas"
        );

    const panelCanjes =
        document.getElementById(
            "panelCanjes"
        );

    if (
        panelRecompensas &&
        !panelRecompensas.classList.contains(
            "d-none"
        )
    ) {

        await cargarRecompensas();

    }

    if (
        panelCanjes &&
        !panelCanjes.classList.contains(
            "d-none"
        )
    ) {

        await cargarCanjes();

    }

}


//==================================================
// REALIZAR PETICIÓN A LA API
//==================================================

async function consultarAPI(
    action,
    opciones = {}
) {

    const url = new URL(
        RONEM_CONFIG.apiFidelizacion,
        window.location.href
    );

    url.searchParams.set(
        "action",
        action
    );

    if (opciones.parametros) {

        Object.entries(
            opciones.parametros
        ).forEach(([clave, valor]) => {

            url.searchParams.set(
                clave,
                valor
            );

        });

    }

    const configuracion = {
        method:
            opciones.method ??
            "GET",

        cache:
            "no-store",

        credentials:
            "same-origin",

        headers: {
            "Accept":
                "application/json"
        }
    };

    if (opciones.body !== undefined) {

        configuracion.headers[
            "Content-Type"
        ] = "application/json";

        configuracion.body =
            JSON.stringify(
                opciones.body
            );

    }

    const respuesta =
        await fetch(
            url.toString(),
            configuracion
        );

    const contenido =
        await respuesta.text();

    let datos;

    try {

        datos =
            contenido
                ? JSON.parse(contenido)
                : {};

    } catch (error) {

        console.error(
            "Respuesta no válida de la API:",
            contenido
        );

        throw new Error(
            "La API devolvió una respuesta que no es JSON."
        );

    }

    if (
        !respuesta.ok ||
        datos.success === false
    ) {

        throw new Error(
            datos.message ||
            datos.error ||
            `Error HTTP ${respuesta.status}`
        );

    }

    return datos;

}


//==================================================
// CARGAR RESUMEN
//==================================================

async function cargarResumen() {

    try {

        const datos =
            await consultarAPI(
                "resumen_puntos"
            );

        saldoPuntosActual =
            Number(
                datos.saldo ??
                datos.Puntos_disponibles ??
                0
            );

        const totalGanados =
            Number(
                datos.total_ganado ??
                datos.Total_puntos_ganados ??
                0
            );

        const totalUsados =
            Number(
                datos.total_usado ??
                datos.Total_puntos_usados ??
                0
            );

        colocarTexto(
            "puntosDisponibles",
            formatearNumero(
                saldoPuntosActual
            )
        );

        colocarTexto(
            "totalPuntosGanados",
            formatearNumero(
                totalGanados
            )
        );

        colocarTexto(
            "totalPuntosUsados",
            formatearNumero(
                totalUsados
            )
        );

    } catch (error) {

        console.error(
            "Error al cargar el resumen:",
            error
        );

        colocarTexto(
            "puntosDisponibles",
            "--"
        );

        colocarTexto(
            "totalPuntosGanados",
            "--"
        );

        colocarTexto(
            "totalPuntosUsados",
            "--"
        );

        mostrarAlerta(
            "error",
            "No se pudo cargar el resumen de puntos. " +
            error.message
        );

    }

}


//==================================================
// CARGAR MOVIMIENTOS
//==================================================

async function cargarMovimientos() {

    const carga =
        document.getElementById(
            "cargaMovimientos"
        );

    const sinDatos =
        document.getElementById(
            "sinMovimientos"
        );

    const contenedor =
        document.getElementById(
            "contenedorMovimientos"
        );

    const tbody =
        document.getElementById(
            "tbodyMovimientos"
        );

    carga?.classList.remove(
        "d-none"
    );

    sinDatos?.classList.add(
        "d-none"
    );

    contenedor?.classList.add(
        "d-none"
    );

    if (tbody) {

        tbody.innerHTML = "";

    }

    try {

        const datos =
            await consultarAPI(
                "listar_historial"
            );

        const movimientos =
            Array.isArray(datos.historial)
                ? datos.historial
                : Array.isArray(
                    datos.movimientos
                )
                    ? datos.movimientos
                    : [];

        carga?.classList.add(
            "d-none"
        );

        if (
            movimientos.length === 0
        ) {

            sinDatos?.classList.remove(
                "d-none"
            );

            return;

        }

        renderizarMovimientos(
            movimientos
        );

        contenedor?.classList.remove(
            "d-none"
        );

    } catch (error) {

        console.error(
            "Error al cargar movimientos:",
            error
        );

        carga?.classList.add(
            "d-none"
        );

        sinDatos?.classList.remove(
            "d-none"
        );

        mostrarAlerta(
            "error",
            "No se pudieron cargar los movimientos. " +
            error.message
        );

    }

}


function renderizarMovimientos(
    movimientos
) {

    const tbody =
        document.getElementById(
            "tbodyMovimientos"
        );

    if (!tbody) {

        return;

    }

    tbody.innerHTML =
        movimientos
            .map(movimiento => {

                const tipo =
                    String(
                        movimiento.Tipo ??
                        "Ajuste"
                    );

                const puntos =
                    Number(
                        movimiento.Puntos ??
                        0
                    );

                const esPositivo =
                    puntos >= 0;

                return `
                    <tr>

                        <td>
                            ${formatearFecha(
                                movimiento.Fecha
                            )}
                        </td>

                        <td>
                            <span
                                class="
                                    movimiento-tipo
                                    ${claseMovimiento(
                                        tipo
                                    )}
                                "
                            >

                                <i
                                    class="
                                        bi
                                        ${iconoMovimiento(
                                            tipo
                                        )}
                                    "
                                ></i>

                                ${escaparHTML(
                                    tipo
                                )}

                            </span>
                        </td>

                        <td>
                            ${escaparHTML(
                                movimiento.Motivo ??
                                "Movimiento de puntos"
                            )}
                        </td>

                        <td
                            class="
                                ${
                                    esPositivo
                                        ? "puntos-positivos"
                                        : "puntos-negativos"
                                }
                            "
                        >

                            ${
                                esPositivo
                                    ? "+"
                                    : ""
                            }

                            ${formatearNumero(
                                puntos
                            )}

                        </td>

                    </tr>
                `;

            })
            .join("");

}


//==================================================
// CARGAR RECOMPENSAS
//==================================================

async function cargarRecompensas() {

    const carga =
        document.getElementById(
            "cargaRecompensas"
        );

    const sinDatos =
        document.getElementById(
            "sinRecompensas"
        );

    const contenedor =
        document.getElementById(
            "contenedorRecompensas"
        );

    carga?.classList.remove(
        "d-none"
    );

    sinDatos?.classList.add(
        "d-none"
    );

    contenedor?.classList.add(
        "d-none"
    );

    if (contenedor) {

        contenedor.innerHTML = "";

    }

    try {

        const datos =
            await consultarAPI(
                "listar_recompensas"
            );

        saldoPuntosActual =
            Number(
                datos.saldo ??
                saldoPuntosActual
            );

        const recompensas =
            Array.isArray(
                datos.recompensas
            )
                ? datos.recompensas
                : [];

        recompensasCargadas = true;

        carga?.classList.add(
            "d-none"
        );

        if (
            recompensas.length === 0
        ) {

            sinDatos?.classList.remove(
                "d-none"
            );

            return;

        }

        renderizarRecompensas(
            recompensas
        );

        contenedor?.classList.remove(
            "d-none"
        );

    } catch (error) {

        console.error(
            "Error al cargar recompensas:",
            error
        );

        recompensasCargadas = false;

        carga?.classList.add(
            "d-none"
        );

        sinDatos?.classList.remove(
            "d-none"
        );

        mostrarAlerta(
            "error",
            "No se pudieron cargar las recompensas. " +
            error.message
        );

    }

}


function renderizarRecompensas(
    recompensas
) {

    const contenedor =
        document.getElementById(
            "contenedorRecompensas"
        );

    if (!contenedor) {

        return;

    }

    contenedor.innerHTML =
        recompensas
            .map(recompensa => {

                const idRecompensa =
                    Number(
                        recompensa.Id_recompensa ??
                        0
                    );

                const nombre =
                    String(
                        recompensa.Nombre ??
                        "Recompensa"
                    );

                const descripcion =
                    String(
                        recompensa.Descripcion ??
                        "Sin descripción."
                    );

                const tipo =
                    String(
                        recompensa.Tipo ??
                        "Recompensa"
                    );

                const puntos =
                    Number(
                        recompensa.Puntos_requeridos ??
                        0
                    );

                const imagen =
                    normalizarRutaImagen(
                        recompensa.Imagen_URL
                    );

                const stock =
                    recompensa.Stock === null ||
                    recompensa.Stock === undefined ||
                    recompensa.Stock === ""
                        ? null
                        : Number(
                            recompensa.Stock
                        );

                const agotada =
                    stock !== null &&
                    stock <= 0;

                const sinPuntos =
                    saldoPuntosActual <
                    puntos;

                let claseExtra = "";

                if (agotada) {

                    claseExtra =
                        "agotada";

                } else if (sinPuntos) {

                    claseExtra =
                        "sin-puntos";

                }

                let textoBoton =
                    "CANJEAR";

                if (agotada) {

                    textoBoton =
                        "AGOTADO";

                } else if (sinPuntos) {

                    textoBoton =
                        "PUNTOS INSUFICIENTES";

                }

                const imagenHTML =
                    imagen
                        ? `
                            <img
                                src="${escaparAtributo(
                                    imagen
                                )}"
                                alt="${escaparAtributo(
                                    nombre
                                )}"
                                onerror="
                                    this.style.display='none';
                                    this.nextElementSibling.style.display='grid';
                                "
                            >

                            <div
                                class="recompensa-sin-imagen"
                                style="display:none;"
                            >
                                <i class="bi bi-gift"></i>
                            </div>
                        `
                        : `
                            <div
                                class="recompensa-sin-imagen"
                            >
                                <i class="bi bi-gift"></i>
                            </div>
                        `;

                return `
                    <article
                        class="
                            recompensa-card
                            ${claseExtra}
                        "
                    >

                        <div class="recompensa-imagen">

                            ${imagenHTML}

                            <span class="recompensa-tipo">
                                ${escaparHTML(
                                    tipo
                                )}
                            </span>

                        </div>

                        <div class="recompensa-contenido">

                            <h3>
                                ${escaparHTML(
                                    nombre
                                )}
                            </h3>

                            <p class="recompensa-descripcion">
                                ${escaparHTML(
                                    descripcion
                                )}
                            </p>

                            <div class="recompensa-pie">

                                <div class="recompensa-puntos">

                                    <strong>
                                        ${formatearNumero(
                                            puntos
                                        )}
                                    </strong>

                                    <span>
                                        PUNTOS
                                    </span>

                                </div>

                                <button
                                    type="button"
                                    class="btn-canjear"
                                    data-id="${idRecompensa}"
                                    data-nombre="${escaparAtributo(
                                        nombre
                                    )}"
                                    data-puntos="${puntos}"
                                    ${
                                        agotada ||
                                        sinPuntos
                                            ? "disabled"
                                            : ""
                                    }
                                >

                                    <i class="bi bi-gift me-1"></i>

                                    ${textoBoton}

                                </button>

                            </div>

                        </div>

                    </article>
                `;

            })
            .join("");

    contenedor
        .querySelectorAll(
            ".btn-canjear:not(:disabled)"
        )
        .forEach(boton => {

            boton.addEventListener(
                "click",
                () => {

                    abrirModalCanje({
                        id:
                            Number(
                                boton.dataset.id
                            ),

                        nombre:
                            boton.dataset.nombre,

                        puntos:
                            Number(
                                boton.dataset.puntos
                            )
                    });

                }
            );

        });

}


//==================================================
// MODAL DE CANJE
//==================================================

function abrirModalCanje(
    recompensa
) {

    recompensaSeleccionada =
        recompensa;

    colocarTexto(
        "nombreRecompensaCanje",
        recompensa.nombre
    );

    colocarTexto(
        "puntosRecompensaCanje",
        formatearNumero(
            recompensa.puntos
        )
    );

    modalConfirmarCanje?.show();

}


//==================================================
// PROCESAR CANJE
//==================================================

async function procesarCanje() {

    if (
        !recompensaSeleccionada ||
        recompensaSeleccionada.id <= 0
    ) {

        mostrarAlerta(
            "error",
            "No se seleccionó una recompensa válida."
        );

        return;

    }

    const boton =
        document.getElementById(
            "btnConfirmarCanje"
        );

    cambiarEstadoBotonCanje(
        boton,
        true
    );

    try {

        const datos =
            await consultarAPI(
                "canjear",
                {
                    method:
                        "POST",

                    body: {
                        id_recompensa:
                            recompensaSeleccionada.id
                    }
                }
            );

        modalConfirmarCanje?.hide();

        mostrarAlerta(
            "exito",
            datos.message ||
            datos.mensaje ||
            "La recompensa fue canjeada correctamente."
        );

        recompensaSeleccionada =
            null;

        recompensasCargadas =
            false;

        canjesCargados =
            false;

        await Promise.allSettled([
            cargarResumen(),
            cargarMovimientos(),
            cargarRecompensas()
        ]);

    } catch (error) {

        console.error(
            "Error al procesar el canje:",
            error
        );

        mostrarAlerta(
            "error",
            error.message
        );

    } finally {

        cambiarEstadoBotonCanje(
            boton,
            false
        );

    }

}


function cambiarEstadoBotonCanje(
    boton,
    cargando
) {

    if (!boton) {

        return;

    }

    const texto =
        boton.querySelector(
            ".texto-boton"
        );

    const carga =
        boton.querySelector(
            ".cargando-boton"
        );

    boton.disabled =
        cargando;

    texto?.classList.toggle(
        "d-none",
        cargando
    );

    carga?.classList.toggle(
        "d-none",
        !cargando
    );

}


//==================================================
// CARGAR CANJES
//==================================================

async function cargarCanjes() {

    const carga =
        document.getElementById(
            "cargaCanjes"
        );

    const sinDatos =
        document.getElementById(
            "sinCanjes"
        );

    const contenedor =
        document.getElementById(
            "contenedorCanjes"
        );

    const tbody =
        document.getElementById(
            "tbodyCanjes"
        );

    carga?.classList.remove(
        "d-none"
    );

    sinDatos?.classList.add(
        "d-none"
    );

    contenedor?.classList.add(
        "d-none"
    );

    if (tbody) {

        tbody.innerHTML = "";

    }

    try {

        const datos =
            await consultarAPI(
                "listar_canjes"
            );

        const canjes =
            Array.isArray(
                datos.canjes
            )
                ? datos.canjes
                : [];

        canjesCargados = true;

        carga?.classList.add(
            "d-none"
        );

        if (
            canjes.length === 0
        ) {

            sinDatos?.classList.remove(
                "d-none"
            );

            return;

        }

        renderizarCanjes(
            canjes
        );

        contenedor?.classList.remove(
            "d-none"
        );

    } catch (error) {

        console.error(
            "Error al cargar canjes:",
            error
        );

        canjesCargados = false;

        carga?.classList.add(
            "d-none"
        );

        sinDatos?.classList.remove(
            "d-none"
        );

        mostrarAlerta(
            "error",
            "No se pudieron cargar los canjes. " +
            error.message
        );

    }

}


function renderizarCanjes(
    canjes
) {

    const tbody =
        document.getElementById(
            "tbodyCanjes"
        );

    if (!tbody) {

        return;

    }

    tbody.innerHTML =
        canjes
            .map(canje => {

                const estado =
                    String(
                        canje.Estado ??
                        "Pendiente"
                    );

                return `
                    <tr>

                        <td>
                            <span class="codigo-canje">
                                ${escaparHTML(
                                    canje.Codigo_canje ??
                                    "-"
                                )}
                            </span>
                        </td>

                        <td>
                            ${escaparHTML(
                                canje.Nombre_recompensa ??
                                canje.Nombre ??
                                "Recompensa"
                            )}
                        </td>

                        <td>
                            ${formatearNumero(
                                Number(
                                    canje.Puntos_utilizados ??
                                    0
                                )
                            )}
                        </td>

                        <td>
                            ${formatearFecha(
                                canje.Fecha
                            )}
                        </td>

                        <td>
                            <span
                                class="
                                    estado-canje
                                    ${claseEstadoCanje(
                                        estado
                                    )}
                                "
                            >
                                ${escaparHTML(
                                    estado
                                )}
                            </span>
                        </td>

                    </tr>
                `;

            })
            .join("");

}


//==================================================
// CLASES E ICONOS
//==================================================

function claseMovimiento(tipo) {

    const valor =
        String(tipo)
            .trim()
            .toLowerCase();

    switch (valor) {

        case "ganado":

            return "movimiento-ganado";

        case "canjeado":

            return "movimiento-canjeado";

        case "vencido":

            return "movimiento-vencido";

        case "ajuste":
        default:

            return "movimiento-ajuste";

    }

}


function iconoMovimiento(tipo) {

    const valor =
        String(tipo)
            .trim()
            .toLowerCase();

    switch (valor) {

        case "ganado":

            return "bi-arrow-up-circle";

        case "canjeado":

            return "bi-gift";

        case "vencido":

            return "bi-clock-history";

        case "ajuste":
        default:

            return "bi-sliders";

    }

}


function claseEstadoCanje(estado) {

    const valor =
        String(estado)
            .trim()
            .toLowerCase();

    switch (valor) {

        case "disponible":

            return "estado-disponible";

        case "utilizado":

            return "estado-utilizado";

        case "cancelado":

            return "estado-cancelado";

        case "pendiente":
        default:

            return "estado-pendiente";

    }

}


//==================================================
// ALERTAS
//==================================================

function mostrarAlerta(
    tipo,
    mensaje
) {

    const contenedor =
        document.getElementById(
            "alertaFidelizacion"
        );

    if (!contenedor) {

        return;

    }

    let clase =
        "alerta-error";

    let icono =
        "bi-exclamation-circle";

    if (tipo === "exito") {

        clase =
            "alerta-exito";

        icono =
            "bi-check-circle";

    } else if (
        tipo === "advertencia"
    ) {

        clase =
            "alerta-advertencia";

        icono =
            "bi-exclamation-triangle";

    }

    contenedor.innerHTML = `
        <div
            class="
                alerta-fidelizacion
                ${clase}
            "
        >

            <i class="bi ${icono}"></i>

            <span>
                ${escaparHTML(
                    mensaje
                )}
            </span>

        </div>
    `;

    contenedor.scrollIntoView({
        behavior:
            "smooth",

        block:
            "nearest"
    });

}


function limpiarAlerta() {

    const contenedor =
        document.getElementById(
            "alertaFidelizacion"
        );

    if (contenedor) {

        contenedor.innerHTML =
            "";

    }

}


//==================================================
// FORMATEAR VALORES
//==================================================

function formatearNumero(valor) {

    const numero =
        Number(valor);

    if (
        !Number.isFinite(numero)
    ) {

        return "0";

    }

    return numero.toLocaleString(
        "es-GT"
    );

}


function formatearFecha(fecha) {

    if (!fecha) {

        return "-";

    }

    const fechaNormalizada =
        String(fecha)
            .replace(
                " ",
                "T"
            );

    const objetoFecha =
        new Date(
            fechaNormalizada
        );

    if (
        Number.isNaN(
            objetoFecha.getTime()
        )
    ) {

        return escaparHTML(
            fecha
        );

    }

    return objetoFecha.toLocaleString(
        "es-GT",
        {
            year:
                "numeric",

            month:
                "2-digit",

            day:
                "2-digit",

            hour:
                "2-digit",

            minute:
                "2-digit"
        }
    );

}


function normalizarRutaImagen(ruta) {

    if (!ruta) {

        return "";

    }

    const valor =
        String(ruta).trim();

    if (
        valor.startsWith("http://") ||
        valor.startsWith("https://") ||
        valor.startsWith("data:") ||
        valor.startsWith("/")
    ) {

        return valor;

    }

    return (
        RONEM_CONFIG.rutaRaiz +
        valor.replace(
            /^(\.\.\/)+/,
            ""
        )
    );

}


//==================================================
// SEGURIDAD DE TEXTO
//==================================================

function escaparHTML(valor) {

    return String(
        valor ?? ""
    )
        .replaceAll(
            "&",
            "&amp;"
        )
        .replaceAll(
            "<",
            "&lt;"
        )
        .replaceAll(
            ">",
            "&gt;"
        )
        .replaceAll(
            '"',
            "&quot;"
        )
        .replaceAll(
            "'",
            "&#039;"
        );

}


function escaparAtributo(valor) {

    return escaparHTML(
        valor
    );

}


function colocarTexto(
    id,
    texto
) {

    const elemento =
        document.getElementById(id);

    if (elemento) {

        elemento.textContent =
            texto;

    }

}