document.addEventListener("DOMContentLoaded", () => {

    iniciarPestanas();
    iniciarModales();
    iniciarMenuUsuario();
    abrirPestanaDesdeURL();

});


/*==================================================
PESTAÑAS DEL PANEL
==================================================*/

function iniciarPestanas() {

    const enlaces =
        document.querySelectorAll(
            ".nav-link[data-tab]"
        );

    enlaces.forEach(enlace => {

        enlace.addEventListener(
            "click",
            evento => {

                evento.preventDefault();

                const nombrePestana =
                    enlace.dataset.tab;

                if (!nombrePestana) {
                    return;
                }

                mostrarPestana(
                    nombrePestana
                );

                history.replaceState(
                    null,
                    "",
                    `#tab-${nombrePestana}`
                );

            }
        );

    });

}


/*==================================================
MOSTRAR UNA PESTAÑA
==================================================*/

function mostrarPestana(
    nombrePestana
) {

    const enlaces =
        document.querySelectorAll(
            ".nav-link[data-tab]"
        );

    const contenidos =
        document.querySelectorAll(
            ".tab-content"
        );

    const contenidoObjetivo =
        document.getElementById(
            `tab-${nombrePestana}`
        );

    if (!contenidoObjetivo) {

        console.error(
            `No existe la pestaña: tab-${nombrePestana}`
        );

        return;

    }

    contenidos.forEach(contenido => {

        contenido.classList.remove(
            "active"
        );

    });

    enlaces.forEach(enlace => {

        enlace.classList.remove(
            "active"
        );

    });

    contenidoObjetivo.classList.add(
        "active"
    );

    const enlaceActivo =
        document.querySelector(
            `.nav-link[data-tab="${nombrePestana}"]`
        );

    if (enlaceActivo) {

        enlaceActivo.classList.add(
            "active"
        );

    }

    cerrarMenuUsuario();

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

}


/*==================================================
ABRIR PESTAÑA DESDE LA URL
==================================================*/

function abrirPestanaDesdeURL() {

    const hash =
        window.location.hash;

    if (!hash.startsWith("#tab-")) {
        return;
    }

    const nombrePestana =
        hash.replace(
            "#tab-",
            ""
        );

    const contenido =
        document.getElementById(
            `tab-${nombrePestana}`
        );

    if (contenido) {

        mostrarPestana(
            nombrePestana
        );

    }

}


/*==================================================
INICIALIZAR MODALES
==================================================*/

function iniciarModales() {

    const modales =
        document.querySelectorAll(
            ".modal-overlay"
        );

    modales.forEach(modal => {

        modal.addEventListener(
            "click",
            evento => {

                if (
                    evento.target === modal
                ) {

                    closeModal(
                        modal.id
                    );

                }

            }
        );

    });

    document.addEventListener(
        "keydown",
        evento => {

            if (
                evento.key !== "Escape"
            ) {
                return;
            }

            const modalActivo =
                document.querySelector(
                    ".modal-overlay.active, " +
                    ".modal-overlay.show"
                );

            if (modalActivo) {

                closeModal(
                    modalActivo.id
                );

            }

            cerrarMenuUsuario();

        }
    );

}


/*==================================================
ABRIR MODAL
==================================================*/

function openModal(
    idModal
) {

    const modal =
        document.getElementById(
            idModal
        );

    if (!modal) {

        console.error(
            `No existe el modal con ID: ${idModal}`
        );

        return;

    }

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

    const primerCampo =
        modal.querySelector(
            "input:not([type='hidden']), " +
            "select, textarea, button"
        );

    if (primerCampo) {

        setTimeout(
            () => {

                primerCampo.focus();

            },
            100
        );

    }

}


/*==================================================
CERRAR MODAL
==================================================*/

function closeModal(
    idModal
) {

    const modal =
        document.getElementById(
            idModal
        );

    if (!modal) {

        console.error(
            `No existe el modal con ID: ${idModal}`
        );

        return;

    }

    modal.classList.remove(
        "active",
        "show"
    );

    modal.setAttribute(
        "aria-hidden",
        "true"
    );

    document.body.classList.remove(
        "modal-open"
    );

}


/*==================================================
MENÚ DEL USUARIO
==================================================*/

function iniciarMenuUsuario() {

    const botonUsuario =
        document.getElementById(
            "btn-user-menu"
        );

    const menuUsuario =
        document.getElementById(
            "user-dropdown"
        );

    if (
        !botonUsuario ||
        !menuUsuario
    ) {
        return;
    }

    botonUsuario.addEventListener(
        "click",
        evento => {

            evento.stopPropagation();

            const menuAbierto =
                menuUsuario.classList.toggle(
                    "active"
                );

            botonUsuario.classList.toggle(
                "active",
                menuAbierto
            );

            botonUsuario.setAttribute(
                "aria-expanded",
                menuAbierto
                    ? "true"
                    : "false"
            );

        }
    );

    menuUsuario.addEventListener(
        "click",
        evento => {

            evento.stopPropagation();

        }
    );

    document.addEventListener(
        "click",
        () => {

            cerrarMenuUsuario();

        }
    );

    const enlacesPestanaUsuario =
        document.querySelectorAll(
            "[data-user-tab]"
        );

    enlacesPestanaUsuario.forEach(
        enlace => {

            enlace.addEventListener(
                "click",
                evento => {

                    evento.preventDefault();

                    const nombrePestana =
                        enlace.dataset.userTab;

                    if (!nombrePestana) {
                        return;
                    }

                    mostrarPestana(
                        nombrePestana
                    );

                    history.replaceState(
                        null,
                        "",
                        `#tab-${nombrePestana}`
                    );

                }
            );

        }
    );

}


/*==================================================
CERRAR MENÚ DEL USUARIO
==================================================*/

function cerrarMenuUsuario() {

    const botonUsuario =
        document.getElementById(
            "btn-user-menu"
        );

    const menuUsuario =
        document.getElementById(
            "user-dropdown"
        );

    if (menuUsuario) {

        menuUsuario.classList.remove(
            "active"
        );

    }

    if (botonUsuario) {

        botonUsuario.classList.remove(
            "active"
        );

        botonUsuario.setAttribute(
            "aria-expanded",
            "false"
        );

    }

}


/*==================================================
NORMALIZAR TEXTO
==================================================*/

function normalizarTexto(
    texto
) {

    return String(texto ?? "")
        .toLowerCase()
        .normalize("NFD")
        .replace(
            /[\u0300-\u036f]/g,
            ""
        )
        .trim();

}


/*==================================================
MOSTRAR CARGA
==================================================*/

function mostrarCarga(
    idContenedor,
    mensaje = "Cargando información..."
) {

    const contenedor =
        document.getElementById(
            idContenedor
        );

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div class="loading-state">

            <div class="spinner"></div>

            <p>
                ${escaparHTML(
                    mensaje
                )}
            </p>

        </div>
    `;

}


/*==================================================
MOSTRAR ESTADO VACÍO
==================================================*/

function mostrarVacio(
    idContenedor,
    titulo = "No hay información",
    mensaje = "No existen registros disponibles."
) {

    const contenedor =
        document.getElementById(
            idContenedor
        );

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div class="empty-state">

            <h2>
                ${escaparHTML(
                    titulo
                )}
            </h2>

            <p>
                ${escaparHTML(
                    mensaje
                )}
            </p>

        </div>
    `;

}


/*==================================================
MOSTRAR ERROR
==================================================*/

function mostrarError(
    idContenedor,
    mensaje = "No se pudo cargar la información."
) {

    const contenedor =
        document.getElementById(
            idContenedor
        );

    if (!contenedor) {
        return;
    }

    contenedor.innerHTML = `
        <div class="error-state">

            <h2>
                Ocurrió un error
            </h2>

            <p>
                ${escaparHTML(
                    mensaje
                )}
            </p>

        </div>
    `;

}


/*==================================================
ESCAPAR CONTENIDO HTML
==================================================*/

function escaparHTML(
    valor
) {

    return String(valor ?? "")
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


/*==================================================
EXPONER FUNCIONES COMPARTIDAS
==================================================*/

window.openModal =
    openModal;

window.closeModal =
    closeModal;

window.mostrarPestana =
    mostrarPestana;

window.mostrarCarga =
    mostrarCarga;

window.mostrarVacio =
    mostrarVacio;

window.mostrarError =
    mostrarError;

window.normalizarTexto =
    normalizarTexto;

window.escaparHTML =
    escaparHTML;