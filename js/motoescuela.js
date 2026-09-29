//==================================================
// VARIABLES GENERALES
//==================================================

let solicitudMotoescuela = null;
let historialMotoescuela = [];


//==================================================
// INICIO
//==================================================

document.addEventListener("DOMContentLoaded", () => {

    configurarEventos();
    cargarMotoescuela();

});


//==================================================
// EVENTOS
//==================================================

function configurarEventos() {

    const btnActualizar =
        document.getElementById(
            "btnActualizarMotoescuela"
        );

    if (btnActualizar) {

        btnActualizar.addEventListener(
            "click",
            actualizarMotoescuela
        );

    }

}


//==================================================
// ACTUALIZAR INFORMACIÓN
//==================================================

async function actualizarMotoescuela() {

    const boton =
        document.getElementById(
            "btnActualizarMotoescuela"
        );

    if (!boton) {

        await cargarMotoescuela();
        return;

    }

    const contenidoOriginal =
        boton.innerHTML;

    boton.disabled = true;

    boton.innerHTML = `
        <span
            class="spinner-border spinner-border-sm me-2"
            role="status"
        ></span>

        ACTUALIZANDO...
    `;

    await cargarMotoescuela();

    boton.innerHTML =
        contenidoOriginal;

    boton.disabled = false;

}


//==================================================
// CARGAR TODO
//==================================================

async function cargarMotoescuela() {

    limpiarAlerta();

    mostrarCargaPrincipal();

    try {

        const datos =
            await consultarAPI(
                "obtener_progreso"
            );

        const solicitud =
            datos.solicitud ?? null;

        const historial =
            Array.isArray(datos.historial)
                ? datos.historial
                : [];

        solicitudMotoescuela =
            solicitud;

        historialMotoescuela =
            historial;

        if (!solicitud) {

            mostrarSinSolicitud();
            return;

        }

        mostrarContenidoPrincipal();

        renderizarSolicitud(
            solicitud
        );

        renderizarHistorial(
            historial
        );

    } catch (error) {

        console.error(
            "Error al cargar Moto Escuela:",
            error
        );

        mostrarSinSolicitud();

        mostrarAlerta(
            "error",
            "No se pudo cargar la información de Moto Escuela. " +
            error.message
        );

    }

}


//==================================================
// CONSULTAR API
//==================================================

async function consultarAPI(
    action,
    opciones = {}
) {

    const url = new URL(
        RONEM_MOTOESCUELA_CONFIG.api,
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
            "Respuesta no válida:",
            contenido
        );

        throw new Error(
            "La API devolvió una respuesta inválida."
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
// MOSTRAR ESTADOS PRINCIPALES
//==================================================

function mostrarCargaPrincipal() {

    const carga =
        document.getElementById(
            "cargaMotoescuela"
        );

    const sinSolicitud =
        document.getElementById(
            "sinSolicitudMotoescuela"
        );

    const contenido =
        document.getElementById(
            "contenidoMotoescuela"
        );

    carga?.classList.remove(
        "d-none"
    );

    sinSolicitud?.classList.add(
        "d-none"
    );

    contenido?.classList.add(
        "d-none"
    );

}


function mostrarSinSolicitud() {

    const carga =
        document.getElementById(
            "cargaMotoescuela"
        );

    const sinSolicitud =
        document.getElementById(
            "sinSolicitudMotoescuela"
        );

    const contenido =
        document.getElementById(
            "contenidoMotoescuela"
        );

    carga?.classList.add(
        "d-none"
    );

    contenido?.classList.add(
        "d-none"
    );

    sinSolicitud?.classList.remove(
        "d-none"
    );

}


function mostrarContenidoPrincipal() {

    const carga =
        document.getElementById(
            "cargaMotoescuela"
        );

    const sinSolicitud =
        document.getElementById(
            "sinSolicitudMotoescuela"
        );

    const contenido =
        document.getElementById(
            "contenidoMotoescuela"
        );

    carga?.classList.add(
        "d-none"
    );

    sinSolicitud?.classList.add(
        "d-none"
    );

    contenido?.classList.remove(
        "d-none"
    );

}


//==================================================
// RENDERIZAR SOLICITUD
//==================================================

function renderizarSolicitud(
    solicitud
) {

    const idSolicitud =
        Number(
            solicitud.Id_solicitud ??
            0
        );

    const estadoActual =
        String(
            solicitud.Estado_actual ??
            solicitud.Nombre_estado ??
            "Solicitud recibida"
        );

    const descripcionEstado =
        String(
            solicitud.Descripcion_estado ??
            "Tu proceso de Moto Escuela se encuentra registrado."
        );

    const porcentaje =
        normalizarPorcentaje(
            solicitud.Porcentaje_progreso
        );

    const fechaActualizacion =
        solicitud.Fecha_actualizacion ??
        solicitud.Fecha ??
        null;

    colocarTexto(
        "resumenEstadoActual",
        estadoActual
    );

    colocarTexto(
        "resumenPorcentaje",
        `${porcentaje}%`
    );

    colocarTexto(
        "resumenIdSolicitud",
        `#${idSolicitud}`
    );

    colocarTexto(
        "resumenFechaActualizacion",
        formatearFecha(
            fechaActualizacion
        )
    );

    colocarTexto(
        "tituloEstadoActual",
        estadoActual
    );

    colocarTexto(
        "descripcionEstadoActual",
        descripcionEstado
    );

    colocarTexto(
        "textoPorcentajeProgreso",
        `${porcentaje}%`
    );

    colocarTexto(
        "porcentajeCircular",
        `${porcentaje}%`
    );

    actualizarBarraProgreso(
        porcentaje
    );

    actualizarCirculoProgreso(
        porcentaje
    );

    renderizarDatosSolicitud(
        solicitud
    );

    mostrarMensajeCompletado(
        porcentaje
    );

}


//==================================================
// DATOS DE SOLICITUD
//==================================================

function renderizarDatosSolicitud(
    solicitud
) {

    const nombre =
        String(
            solicitud.Nombre ??
            ""
        ).trim();

    const apellido =
        String(
            solicitud.Apellido ??
            ""
        ).trim();

    const nombreCompleto =
        `${nombre} ${apellido}`
            .trim() ||
        "Sin información";

    colocarTexto(
        "datoNombreCompleto",
        nombreCompleto
    );

    colocarTexto(
        "datoDPI",
        textoSeguro(
            solicitud.DPI
        )
    );

    colocarTexto(
        "datoFechaNacimiento",
        formatearSoloFecha(
            solicitud.Fecha_nacimiento
        )
    );

    colocarTexto(
        "datoGenero",
        textoSeguro(
            solicitud.Genero
        )
    );

    colocarTexto(
        "datoTelefono",
        textoSeguro(
            solicitud.Telefono
        )
    );

    colocarTexto(
        "datoCorreo",
        textoSeguro(
            solicitud.Correo
        )
    );

    colocarTexto(
        "datoDireccion",
        textoSeguro(
            solicitud.Direccion
        )
    );

}


//==================================================
// BARRA DE PROGRESO
//==================================================

function actualizarBarraProgreso(
    porcentaje
) {

    const barra =
        document.getElementById(
            "barraProgresoMotoescuela"
        );

    const contenedor =
        document.getElementById(
            "barraProgresoAccesible"
        );

    if (barra) {

        barra.style.width =
            `${porcentaje}%`;

    }

    if (contenedor) {

        contenedor.setAttribute(
            "aria-valuenow",
            porcentaje
        );

    }

}


//==================================================
// CÍRCULO DE PROGRESO
//==================================================

function actualizarCirculoProgreso(
    porcentaje
) {

    const circulo =
        document.getElementById(
            "circuloProgresoMotoescuela"
        );

    if (!circulo) {

        return;

    }

    const radio = 66;

    const circunferencia =
        2 * Math.PI * radio;

    const desplazamiento =
        circunferencia -
        (
            porcentaje /
            100
        ) *
        circunferencia;

    circulo.style.strokeDasharray =
        circunferencia;

    circulo.style.strokeDashoffset =
        desplazamiento;

}


//==================================================
// MENSAJE DE CURSO COMPLETADO
//==================================================

function mostrarMensajeCompletado(
    porcentaje
) {

    const mensaje =
        document.getElementById(
            "mensajeCursoCompletado"
        );

    if (!mensaje) {

        return;

    }

    mensaje.classList.toggle(
        "d-none",
        porcentaje < 100
    );

}


//==================================================
// RENDERIZAR HISTORIAL
//==================================================

function renderizarHistorial(
    historial
) {

    const carga =
        document.getElementById(
            "cargaHistorialMotoescuela"
        );

    const sinHistorial =
        document.getElementById(
            "sinHistorialMotoescuela"
        );

    const lineaTiempo =
        document.getElementById(
            "lineaTiempoMotoescuela"
        );

    carga?.classList.add(
        "d-none"
    );

    sinHistorial?.classList.add(
        "d-none"
    );

    lineaTiempo?.classList.add(
        "d-none"
    );

    if (
        !Array.isArray(historial) ||
        historial.length === 0
    ) {

        sinHistorial?.classList.remove(
            "d-none"
        );

        return;

    }

    if (!lineaTiempo) {

        return;

    }

    lineaTiempo.innerHTML =
        historial
            .map(
                crearElementoHistorial
            )
            .join("");

    lineaTiempo.classList.remove(
        "d-none"
    );

}


//==================================================
// CREAR ELEMENTO DE HISTORIAL
//==================================================

function crearElementoHistorial(
    registro
) {

    const estadoAnterior =
        textoSeguro(
            registro.Estado_anterior,
            "Sin estado anterior"
        );

    const estadoNuevo =
        textoSeguro(
            registro.Estado_nuevo,
            "Estado actualizado"
        );

    const porcentaje =
        normalizarPorcentaje(
            registro.Porcentaje_progreso
        );

    const comentario =
        textoSeguro(
            registro.Comentario,
            "Sin comentarios adicionales."
        );

    const fecha =
        formatearFecha(
            registro.Fecha
        );

    const cambio =
        estadoAnterior ===
        "Sin estado anterior"
            ? `Inicio del proceso en ${estadoNuevo}.`
            : `Cambio de ${estadoAnterior} a ${estadoNuevo}.`;

    return `
        <article class="timeline-item">

            <span class="timeline-punto"></span>

            <div class="timeline-card">

                <div class="timeline-superior">

                    <span class="timeline-estado">

                        <i class="bi bi-flag"></i>

                        ${escaparHTML(
                            estadoNuevo
                        )}

                    </span>

                    <strong class="timeline-porcentaje">

                        ${porcentaje}%

                    </strong>

                </div>

                <h3>

                    ${escaparHTML(
                        estadoNuevo
                    )}

                </h3>

                <p class="timeline-cambio">

                    ${escaparHTML(
                        cambio
                    )}

                </p>

                <p class="timeline-comentario">

                    ${escaparHTML(
                        comentario
                    )}

                </p>

                <span class="timeline-fecha">

                    <i class="bi bi-calendar3 me-2"></i>

                    ${escaparHTML(
                        fecha
                    )}

                </span>

            </div>

        </article>
    `;

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
            "alertaMotoescuela"
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
                alerta-motoescuela
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

}


function limpiarAlerta() {

    const contenedor =
        document.getElementById(
            "alertaMotoescuela"
        );

    if (contenedor) {

        contenedor.innerHTML =
            "";

    }

}


//==================================================
// FORMATEAR VALORES
//==================================================

function normalizarPorcentaje(
    valor
) {

    const numero =
        Number(valor);

    if (
        !Number.isFinite(numero)
    ) {

        return 0;

    }

    return Math.min(
        100,
        Math.max(
            0,
            Math.round(numero)
        )
    );

}


function formatearFecha(
    fecha
) {

    if (!fecha) {

        return "Sin registro";

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

        return String(fecha);

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


function formatearSoloFecha(
    fecha
) {

    if (!fecha) {

        return "Sin información";

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

        return String(fecha);

    }

    return objetoFecha.toLocaleDateString(
        "es-GT",
        {
            year:
                "numeric",

            month:
                "long",

            day:
                "2-digit"
        }
    );

}


function textoSeguro(
    valor,
    valorDefecto = "Sin información"
) {

    if (
        valor === null ||
        valor === undefined ||
        String(valor).trim() === ""
    ) {

        return valorDefecto;

    }

    return String(valor).trim();

}


//==================================================
// COLOCAR TEXTO
//==================================================

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


//==================================================
// SEGURIDAD
//==================================================

function escaparHTML(
    valor
) {

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