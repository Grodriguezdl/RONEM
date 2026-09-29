/*==================================================
MÓDULO DE MANUALES DEL PANEL TÉCNICO
==================================================*/

let manualesTecnico = [];
let manualTecnicoEditando = null;


/*==================================================
INICIALIZAR MÓDULO
==================================================*/

document.addEventListener(
    "DOMContentLoaded",
    () => {

        cargarManualesTecnico();
        iniciarFormularioManualTecnico();
        iniciarBuscadorManualesTecnico();

    }
);


/*==================================================
CARGAR MANUALES
==================================================*/

async function cargarManualesTecnico() {

    const contenedor =
        document.getElementById(
            "container-manuales"
        );

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div class="manuales-loading">
            <p>
                Cargando manuales...
            </p>
        </div>
    `;

    try {

        const resultado =
            await consultarAPIManualesTecnico(
                "api/api_manuales.php?action=listar"
            );

        manualesTecnico =
            extraerManualesTecnico(
                resultado
            );

        renderManualesTecnico(
            manualesTecnico
        );

    } catch (error) {

        console.error(
            "Error al cargar manuales:",
            error
        );

        contenedor.innerHTML = `
            <div class="error-state">

                <h2>
                    No se pudieron cargar los manuales
                </h2>

                <p>
                    ${escaparHTMLManuales(
                        error.message
                    )}
                </p>

            </div>
        `;

    }

}


/*==================================================
CONSULTAR API DE MANUALES
==================================================*/

async function consultarAPIManualesTecnico(
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

    const respuesta =
        await fetch(
            url,
            configuracion
        );

    const textoRespuesta =
        await respuesta.text();

    let resultado;

    try {

        resultado =
            JSON.parse(
                textoRespuesta
            );

    } catch (error) {

        console.error(
            "Respuesta recibida de manuales:",
            textoRespuesta
        );

        throw new Error(
            "La API de manuales devolvió una respuesta inválida."
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
EXTRAER ARREGLO DE MANUALES
==================================================*/

function extraerManualesTecnico(
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
        Array.isArray(resultado.manuales)
    ) {

        return resultado.manuales;

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
RENDERIZAR MANUALES
==================================================*/

function renderManualesTecnico(
    manuales
) {

    const contenedor =
        document.getElementById(
            "container-manuales"
        );

    if (!contenedor) {
        return;
    }

    if (
        !Array.isArray(manuales) ||
        manuales.length === 0
    ) {

        contenedor.innerHTML = `
            <div class="empty-state">

                <h2>
                    No hay manuales
                </h2>

                <p>
                    Todavía no existen manuales técnicos registrados.
                </p>

            </div>
        `;

        return;

    }

    contenedor.innerHTML = `
        <div class="manuales-grid">

            ${manuales
                .map(
                    manual =>
                        crearTarjetaManualTecnico(
                            manual
                        )
                )
                .join("")}

        </div>
    `;

}


/*==================================================
CREAR TARJETA DE MANUAL
==================================================*/

function crearTarjetaManualTecnico(
    manual
) {

    const idManual =
        obtenerValorManual(
            manual,
            [
                "Id_manual",
                "id_manual",
                "ID_manual",
                "id"
            ],
            0
        );

    const titulo =
        obtenerValorManual(
            manual,
            [
                "Titulo",
                "titulo",
                "Nombre",
                "nombre"
            ],
            "Manual sin título"
        );

    const descripcion =
        obtenerValorManual(
            manual,
            [
                "Descripcion",
                "descripcion"
            ],
            "Sin descripción."
        );

    const categoria =
        obtenerValorManual(
            manual,
            [
                "Categoria",
                "categoria",
                "Tipo",
                "tipo"
            ],
            "General"
        );

    const archivo =
        obtenerValorManual(
            manual,
            [
                "Archivo",
                "archivo",
                "Ruta_archivo",
                "ruta_archivo",
                "PDF",
                "pdf",
                "Documento",
                "documento"
            ],
            ""
        );

    const fecha =
        obtenerValorManual(
            manual,
            [
                "Fecha",
                "fecha",
                "Fecha_registro",
                "fecha_registro"
            ],
            ""
        );

    return `
        <article
            class="manual-card"
            data-id-manual="${escaparHTMLManuales(
                idManual
            )}"
        >

            <div class="manual-icon">
                PDF
            </div>

            <div class="manual-card-body">

                <span class="manual-category">
                    ${escaparHTMLManuales(
                        categoria
                    )}
                </span>

                <h2 class="manual-title">
                    ${escaparHTMLManuales(
                        titulo
                    )}
                </h2>

                <p class="manual-description">
                    ${escaparHTMLManuales(
                        descripcion
                    )}
                </p>

                ${
                    fecha !== ""
                        ? `
                            <p class="manual-date">
                                Fecha:
                                ${escaparHTMLManuales(
                                    formatearFechaManual(
                                        fecha
                                    )
                                )}
                            </p>
                        `
                        : ""
                }

            </div>

            <div class="manual-card-actions">

                ${
                    archivo !== ""
                        ? `
                            <button
                                type="button"
                                class="btn btn-primary"
                                onclick="abrirManualTecnico(
                                    '${escaparAtributoJSManual(
                                        archivo
                                    )}'
                                )"
                            >
                                Abrir PDF
                            </button>
                        `
                        : `
                            <button
                                type="button"
                                class="btn btn-secondary"
                                disabled
                            >
                                Sin archivo
                            </button>
                        `
                }

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="editarManualTecnico(${Number(
                        idManual
                    )})"
                >
                    Editar
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    onclick="eliminarManualTecnico(${Number(
                        idManual
                    )})"
                >
                    Eliminar
                </button>

            </div>

        </article>
    `;

}
/*==================================================
INICIAR FORMULARIO DE MANUALES
==================================================*/

function iniciarFormularioManualTecnico() {

    const formulario =
        document.getElementById(
            "formManualTecnico"
        );

    if (!formulario) {
        return;
    }

    formulario.addEventListener(
        "submit",
        guardarManualTecnico
    );

}


/*==================================================
GUARDAR O EDITAR MANUAL
==================================================*/

async function guardarManualTecnico(
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

    const idManual =
        obtenerCampoFormularioManual(
            formulario,
            [
                "id",
                "id_manual",
                "Id_manual"
            ]
        );

    const titulo =
        obtenerCampoFormularioManual(
            formulario,
            [
                "titulo",
                "Titulo",
                "nombre",
                "Nombre"
            ]
        );

    const archivo =
        formulario.querySelector(
            'input[type="file"]'
        );

    if (titulo === "") {

        mostrarAlertaManuales(
            "Escribe el título del manual.",
            "warning"
        );

        return;

    }

    if (
        idManual === "" &&
        (
            !archivo ||
            !archivo.files ||
            archivo.files.length === 0
        )
    ) {

        mostrarAlertaManuales(
            "Selecciona un archivo PDF.",
            "warning"
        );

        return;

    }

    if (
        archivo &&
        archivo.files &&
        archivo.files.length > 0
    ) {

        const archivoSeleccionado =
            archivo.files[0];

        const nombreArchivo =
            String(
                archivoSeleccionado.name || ""
            ).toLowerCase();

        const esPDF =
            archivoSeleccionado.type ===
                "application/pdf" ||
            nombreArchivo.endsWith(
                ".pdf"
            );

        if (!esPDF) {

            mostrarAlertaManuales(
                "El archivo seleccionado debe ser un PDF.",
                "warning"
            );

            return;

        }

        const limiteBytes =
            15 * 1024 * 1024;

        if (
            archivoSeleccionado.size >
            limiteBytes
        ) {

            mostrarAlertaManuales(
                "El archivo PDF no debe superar los 15 MB.",
                "warning"
            );

            return;

        }

    }

    const accion =
        idManual !== ""
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

        if (idManual !== "") {

            datos.set(
                "id",
                idManual
            );

            datos.set(
                "id_manual",
                idManual
            );

            datos.set(
                "Id_manual",
                idManual
            );

        }

        const resultado =
            await consultarAPIManualesTecnico(
                `api/api_manuales.php?action=${accion}`,
                {
                    method: "POST",
                    body: datos
                }
            );

        mostrarAlertaManuales(
            resultado.mensaje ||
            resultado.message ||
            (
                accion === "editar"
                    ? "El manual fue actualizado correctamente."
                    : "El manual fue registrado correctamente."
            ),
            "success"
        );

        formulario.reset();

        manualTecnicoEditando = null;

        cerrarModalManualTecnico();

        await cargarManualesTecnico();

    } catch (error) {

        console.error(
            "Error al guardar el manual:",
            error
        );

        mostrarAlertaManuales(
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

function obtenerCampoFormularioManual(
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
ABRIR MODAL DE MANUAL
==================================================*/

function abrirModalManualTecnico(
    manual = null
) {

    const modal =
        document.getElementById(
            "modalManualTecnico"
        );

    if (!modal) {

        mostrarAlertaManuales(
            "No se encontró el formulario de manuales.",
            "error"
        );

        return;

    }

    manualTecnicoEditando =
        manual || null;

    completarFormularioManualTecnico(
        manual
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
CERRAR MODAL DE MANUAL
==================================================*/

function cerrarModalManualTecnico() {

    const modal =
        document.getElementById(
            "modalManualTecnico"
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
            "formManualTecnico"
        );

    if (formulario) {

        formulario.reset();

    }

    manualTecnicoEditando = null;

}


/*==================================================
COMPLETAR FORMULARIO DEL MANUAL
==================================================*/

function completarFormularioManualTecnico(
    manual
) {

    const formulario =
        document.getElementById(
            "formManualTecnico"
        );

    if (!formulario) {
        return;
    }

    formulario.reset();

    if (!manual) {

        asignarCampoFormularioManual(
            formulario,
            [
                "id",
                "id_manual",
                "Id_manual"
            ],
            ""
        );

        return;

    }

    asignarCampoFormularioManual(
        formulario,
        [
            "id",
            "id_manual",
            "Id_manual"
        ],
        obtenerValorManual(
            manual,
            [
                "Id_manual",
                "id_manual",
                "ID_manual",
                "id"
            ],
            ""
        )
    );

    asignarCampoFormularioManual(
        formulario,
        [
            "titulo",
            "Titulo",
            "nombre",
            "Nombre"
        ],
        obtenerValorManual(
            manual,
            [
                "Titulo",
                "titulo",
                "Nombre",
                "nombre"
            ],
            ""
        )
    );

    asignarCampoFormularioManual(
        formulario,
        [
            "descripcion",
            "Descripcion"
        ],
        obtenerValorManual(
            manual,
            [
                "Descripcion",
                "descripcion"
            ],
            ""
        )
    );

    asignarCampoFormularioManual(
        formulario,
        [
            "categoria",
            "Categoria",
            "tipo",
            "Tipo"
        ],
        obtenerValorManual(
            manual,
            [
                "Categoria",
                "categoria",
                "Tipo",
                "tipo"
            ],
            ""
        )
    );

}


/*==================================================
ASIGNAR CAMPO DEL FORMULARIO
==================================================*/

function asignarCampoFormularioManual(
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
EDITAR MANUAL
==================================================*/

function editarManualTecnico(
    idManual
) {

    idManual = Number(idManual);

    if (
        !Number.isInteger(idManual) ||
        idManual <= 0
    ) {

        mostrarAlertaManuales(
            "El ID del manual no es válido.",
            "error"
        );

        return;

    }

    const manual =
        manualesTecnico.find(
            item =>
                Number(
                    obtenerValorManual(
                        item,
                        [
                            "Id_manual",
                            "id_manual",
                            "ID_manual",
                            "id"
                        ],
                        0
                    )
                ) === idManual
        );

    if (!manual) {

        mostrarAlertaManuales(
            "No se encontró el manual seleccionado.",
            "error"
        );

        return;

    }

    abrirModalManualTecnico(
        manual
    );

}


/*==================================================
ELIMINAR MANUAL
==================================================*/

async function eliminarManualTecnico(
    idManual
) {

    idManual = Number(idManual);

    if (
        !Number.isInteger(idManual) ||
        idManual <= 0
    ) {

        mostrarAlertaManuales(
            "El ID del manual no es válido.",
            "error"
        );

        return;

    }

    const confirmar =
        window.confirm(
            "¿Seguro que deseas eliminar este manual?"
        );

    if (!confirmar) {
        return;
    }

    try {

        const datos =
            new FormData();

        datos.append(
            "id",
            String(idManual)
        );

        datos.append(
            "id_manual",
            String(idManual)
        );

        datos.append(
            "Id_manual",
            String(idManual)
        );

        const resultado =
            await consultarAPIManualesTecnico(
                "api/api_manuales.php?action=eliminar",
                {
                    method: "POST",
                    body: datos
                }
            );

        mostrarAlertaManuales(
            resultado.mensaje ||
            resultado.message ||
            "El manual fue eliminado correctamente.",
            "success"
        );

        await cargarManualesTecnico();

    } catch (error) {

        console.error(
            "Error al eliminar el manual:",
            error
        );

        mostrarAlertaManuales(
            error.message,
            "error"
        );

    }

}


/*==================================================
ABRIR MANUAL PDF
==================================================*/

function abrirManualTecnico(
    archivo
) {

    archivo =
        String(
            archivo || ""
        ).trim();

    if (archivo === "") {

        mostrarAlertaManuales(
            "Este manual no tiene un archivo PDF registrado.",
            "warning"
        );

        return;

    }

    const ruta =
        normalizarRutaManual(
            archivo
        );

    window.open(
        ruta,
        "_blank",
        "noopener,noreferrer"
    );

}


/*==================================================
NORMALIZAR RUTA DEL PDF
==================================================*/

function normalizarRutaManual(
    archivo
) {

    const ruta =
        String(
            archivo || ""
        )
            .trim()
            .replace(
                /\\/g,
                "/"
            );

    if (
        ruta.startsWith(
            "http://"
        ) ||
        ruta.startsWith(
            "https://"
        ) ||
        ruta.startsWith(
            "/"
        )
    ) {

        return ruta;

    }

    if (
        ruta.startsWith(
            "../"
        ) ||
        ruta.startsWith(
            "./"
        )
    ) {

        return ruta;

    }

    /*
    Si la base de datos guarda algo como:

    uploads/manuales/manual.pdf

    y panel_tecnico.php está dentro de:

    paneles/tecnico/

    se necesitan dos niveles hacia atrás.
    */

    return `../../${ruta}`;

}


/*==================================================
INICIAR BUSCADOR DE MANUALES
==================================================*/

function iniciarBuscadorManualesTecnico() {

    const buscador =
        document.getElementById(
            "buscarManuales"
        ) ||
        document.getElementById(
            "buscadorManuales"
        );

    if (!buscador) {
        return;
    }

    buscador.addEventListener(
        "input",
        filtrarManualesTecnico
    );

}


/*==================================================
FILTRAR MANUALES
==================================================*/

function filtrarManualesTecnico() {

    const buscador =
        document.getElementById(
            "buscarManuales"
        ) ||
        document.getElementById(
            "buscadorManuales"
        );

    const textoBusqueda =
        normalizarTextoManuales(
            buscador
                ? buscador.value
                : ""
        );

    if (textoBusqueda === "") {

        renderManualesTecnico(
            manualesTecnico
        );

        return;

    }

    const resultado =
        manualesTecnico.filter(
            manual => {

                const idManual =
                    obtenerValorManual(
                        manual,
                        [
                            "Id_manual",
                            "id_manual",
                            "ID_manual",
                            "id"
                        ],
                        ""
                    );

                const titulo =
                    obtenerValorManual(
                        manual,
                        [
                            "Titulo",
                            "titulo",
                            "Nombre",
                            "nombre"
                        ],
                        ""
                    );

                const descripcion =
                    obtenerValorManual(
                        manual,
                        [
                            "Descripcion",
                            "descripcion"
                        ],
                        ""
                    );

                const categoria =
                    obtenerValorManual(
                        manual,
                        [
                            "Categoria",
                            "categoria",
                            "Tipo",
                            "tipo"
                        ],
                        ""
                    );

                const textoCompleto =
                    normalizarTextoManuales(
                        [
                            idManual,
                            titulo,
                            descripcion,
                            categoria
                        ].join(" ")
                    );

                return textoCompleto.includes(
                    textoBusqueda
                );

            }
        );

    renderManualesTecnico(
        resultado
    );

}


/*==================================================
LIMPIAR BUSCADOR
==================================================*/

function limpiarBusquedaManualesTecnico() {

    const buscador =
        document.getElementById(
            "buscarManuales"
        ) ||
        document.getElementById(
            "buscadorManuales"
        );

    if (buscador) {

        buscador.value = "";

    }

    renderManualesTecnico(
        manualesTecnico
    );

}
/*==================================================
OBTENER VALOR DE UN MANUAL
==================================================*/

function obtenerValorManual(
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
FORMATEAR FECHA DEL MANUAL
==================================================*/

function formatearFechaManual(
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
NORMALIZAR TEXTO DE MANUALES
==================================================*/

function normalizarTextoManuales(
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

function escaparHTMLManuales(
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
ESCAPAR TEXTO PARA ONCLICK
==================================================*/

function escaparAtributoJSManual(
    valor
) {

    return String(
        valor === null ||
        valor === undefined
            ? ""
            : valor
    )
        .replace(
            /\\/g,
            "\\\\"
        )
        .replace(
            /'/g,
            "\\'"
        )
        .replace(
            /"/g,
            "&quot;"
        )
        .replace(
            /\r/g,
            ""
        )
        .replace(
            /\n/g,
            ""
        );

}


/*==================================================
MOSTRAR ALERTA
==================================================*/

function mostrarAlertaManuales(
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

        cerrarModalManualTecnico();

    }
);


/*==================================================
EXPONER FUNCIONES GLOBALMENTE
==================================================*/

window.cargarManualesTecnico =
    cargarManualesTecnico;

window.abrirModalManualTecnico =
    abrirModalManualTecnico;

window.cerrarModalManualTecnico =
    cerrarModalManualTecnico;

window.guardarManualTecnico =
    guardarManualTecnico;

window.editarManualTecnico =
    editarManualTecnico;

window.eliminarManualTecnico =
    eliminarManualTecnico;

window.abrirManualTecnico =
    abrirManualTecnico;

window.filtrarManualesTecnico =
    filtrarManualesTecnico;

window.limpiarBusquedaManualesTecnico =
    limpiarBusquedaManualesTecnico;