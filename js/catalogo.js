/**
 * Catálogo dinámico de servicios - RONEMMA ENTERPRISE
 * Requiere Bootstrap 5 Bundle cargado antes de este archivo.
 */
(() => {
    "use strict";

    const SELECTORS = {
        modal: ".cat-modal",
        card: ".cat-card",
        reveal: ".cat-reveal",
        catalogButton: "[data-cat-modal]",
        closeButton: ".cat-close"
    };

    /**
     * Comprueba que Bootstrap esté disponible.
     *
     * @returns {boolean}
     */
    function bootstrapDisponible() {
        return typeof window.bootstrap !== "undefined"
            && typeof window.bootstrap.Modal !== "undefined";
    }

    /**
     * Inicializa todas las instancias de los modales del catálogo.
     */
    function inicializarModales() {
        if (!bootstrapDisponible()) {
            console.error("Bootstrap 5 no está disponible para inicializar el catálogo.");
            return;
        }

        document.querySelectorAll(SELECTORS.modal).forEach((modalElement) => {
            window.bootstrap.Modal.getOrCreateInstance(modalElement, {
                backdrop: true,
                focus: true,
                keyboard: true
            });

            modalElement.addEventListener("show.bs.modal", () => {
                document.body.classList.add("cat-modal-open");
            });

            modalElement.addEventListener("hidden.bs.modal", () => {
                document.body.classList.remove("cat-modal-open");

                const activador = document.querySelector(
                    `[data-bs-target="#${CSS.escape(modalElement.id)}"]`
                );

                if (activador instanceof HTMLElement) {
                    activador.focus();
                }
            });
        });
    }

    /**
     * Valida que cada botón apunte a un modal existente.
     */
    function validarBotonesCatalogo() {
        document.querySelectorAll(SELECTORS.catalogButton).forEach((boton) => {
            const destino = boton.getAttribute("data-bs-target");

            if (!destino || !destino.startsWith("#")) {
                boton.setAttribute("aria-disabled", "true");
                boton.classList.add("disabled");
                return;
            }

            const modal = document.querySelector(destino);

            if (!modal || !modal.classList.contains("cat-modal")) {
                boton.setAttribute("aria-disabled", "true");
                boton.classList.add("disabled");
                console.warn(`No se encontró el modal del catálogo: ${destino}`);
            }
        });
    }

    /**
     * Agrega una respuesta visual suave al interactuar con las tarjetas.
     */
    function inicializarEventosTarjetas() {
        document.querySelectorAll(SELECTORS.card).forEach((tarjeta) => {
            tarjeta.addEventListener("pointerenter", () => {
                tarjeta.classList.add("cat-card-active");
            });

            tarjeta.addEventListener("pointerleave", () => {
                tarjeta.classList.remove("cat-card-active");
            });
        });
    }

    /**
     * Muestra gradualmente los elementos visibles usando IntersectionObserver.
     */
    function inicializarAnimaciones() {
        const elementos = document.querySelectorAll(SELECTORS.reveal);

        if (!("IntersectionObserver" in window)) {
            elementos.forEach((elemento) => elemento.classList.add("cat-visible"));
            return;
        }

        const observador = new IntersectionObserver(
            (entradas, observer) => {
                entradas.forEach((entrada) => {
                    if (!entrada.isIntersecting) {
                        return;
                    }

                    entrada.target.classList.add("cat-visible");
                    observer.unobserve(entrada.target);
                });
            },
            {
                threshold: 0.15,
                rootMargin: "0px 0px -40px 0px"
            }
        );

        elementos.forEach((elemento) => observador.observe(elemento));
    }

    /**
     * Inicializa el módulo completo.
     */
    function inicializarCatalogo() {
        validarBotonesCatalogo();
        inicializarModales();
        inicializarEventosTarjetas();
        inicializarAnimaciones();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", inicializarCatalogo);
    } else {
        inicializarCatalogo();
    }
})();
