document.addEventListener("DOMContentLoaded", () => {

    //=====================================
    // ELEMENTOS PRINCIPALES
    //=====================================

    const contenedorVehiculos =
        document.getElementById("contenedorVehiculos");

    const estadoCarga =
        document.getElementById("estadoCarga");

    const sinVehiculos =
        document.getElementById("sinVehiculos");

    const sinResultados =
        document.getElementById("sinResultados");

    const alertaVehiculos =
        document.getElementById("alertaVehiculos");

    //=====================================
    // CONTADORES
    //=====================================

    const totalVehiculos =
        document.getElementById("totalVehiculos");

    const totalActivos =
        document.getElementById("totalActivos");

    const totalInactivos =
        document.getElementById("totalInactivos");

    //=====================================
    // FILTROS
    //=====================================

    const buscarVehiculo =
        document.getElementById("buscarVehiculo");

    const filtroTipo =
        document.getElementById("filtroTipo");

    const filtroEstado =
        document.getElementById("filtroEstado");

    //=====================================
    // FORMULARIO
    //=====================================

    const formVehiculo =
        document.getElementById("formVehiculo");

    const idVehiculo =
        document.getElementById("idVehiculo");

    const marca =
        document.getElementById("marca");

    const linea =
        document.getElementById("linea");

    const modelo =
        document.getElementById("modelo");

    const color =
        document.getElementById("color");

    const placa =
        document.getElementById("placa");

    const noChasis =
        document.getElementById("noChasis");

    const tipoVehiculo =
        document.getElementById("tipoVehiculo");

    const cilindraje =
        document.getElementById("cilindraje");

    const combustible =
        document.getElementById("combustible");

    const kilometraje =
        document.getElementById("kilometraje");

    const estado =
        document.getElementById("estado");

    const imagen =
        document.getElementById("imagen");

    const previewImagen =
        document.getElementById("previewImagen");

    //=====================================
    // BOTONES
    //=====================================

    const btnNuevoVehiculo =
        document.getElementById("btnNuevoVehiculo");

    const btnGuardarVehiculo =
        document.getElementById("btnGuardarVehiculo");

    const btnConfirmarEliminar =
        document.getElementById("btnConfirmarEliminar");

    //=====================================
    // MODALES
    //=====================================

    const modalVehiculoElemento =
        document.getElementById("modalVehiculo");

    const modalEliminarElemento =
        document.getElementById("modalEliminarVehiculo");

    const modalVehiculo =
        bootstrap.Modal.getOrCreateInstance(
            modalVehiculoElemento
        );

    const modalEliminar =
        bootstrap.Modal.getOrCreateInstance(
            modalEliminarElemento
        );

    const tituloModalVehiculo =
        document.getElementById("tituloModalVehiculo");

    const nombreVehiculoEliminar =
        document.getElementById("nombreVehiculoEliminar");

    //=====================================
    // VARIABLES
    //=====================================

    let vehiculos = [];
    let idVehiculoEliminar = null;

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
    // OBTENER RUTA DE IMAGEN
    //=====================================

    function obtenerRutaImagen(ruta) {

        if (!ruta) {

            return RONEM_CONFIG.imagenPredeterminada;

        }

        if (
            ruta.startsWith("http://") ||
            ruta.startsWith("https://") ||
            ruta.startsWith("data:")
        ) {

            return ruta;

        }

        return (
            RONEM_CONFIG.rutaRaiz +
            ruta.replace(/^\/+/, "")
        );

    }

    //=====================================
    // FORMATEAR NÚMEROS
    //=====================================

    function formatearNumero(numero) {

        return new Intl.NumberFormat("es-GT").format(
            Number(numero || 0)
        );

    }

    //=====================================
    // MOSTRAR ALERTA
    //=====================================

    function mostrarAlerta(
        mensaje,
        tipo = "success"
    ) {

        alertaVehiculos.innerHTML = `
            <div
                class="alert alert-${tipo} alert-dismissible fade show alerta-panel"
                role="alert"
            >
                <i class="bi ${
                    tipo === "success"
                        ? "bi-check-circle"
                        : tipo === "warning"
                            ? "bi-exclamation-triangle"
                            : "bi-x-circle"
                } me-2"></i>

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
    // ACTUALIZAR CONTADORES
    //=====================================

    function actualizarContadores() {

        const activos = vehiculos.filter(
            vehiculo =>
                Number(vehiculo.Estado) === 1
        ).length;

        const inactivos = vehiculos.filter(
            vehiculo =>
                Number(vehiculo.Estado) === 0
        ).length;

        totalVehiculos.textContent =
            vehiculos.length;

        totalActivos.textContent =
            activos;

        totalInactivos.textContent =
            inactivos;

    }

    //=====================================
    // GENERAR TARJETA
    //=====================================

    function generarTarjetaVehiculo(vehiculo) {

        const estaActivo =
            Number(vehiculo.Estado) === 1;

        const nombreCompleto =
            `${vehiculo.Marca} ${vehiculo.Linea}`;

        const rutaImagen =
            obtenerRutaImagen(
                vehiculo.Imagen_URL
            );

        return `
            <div class="col-12 col-md-6 col-xl-4">

                <article class="vehiculo-card">

                    <div class="vehiculo-imagen">

                        <img
                            src="${escaparHTML(rutaImagen)}"
                            alt="${escaparHTML(nombreCompleto)}"
                            loading="lazy"
                            onerror="
                                this.onerror = null;
                                this.src = '${escaparHTML(
                                    RONEM_CONFIG.imagenPredeterminada
                                )}';
                            "
                        >

                        <span class="tipo-chip">
                            ${escaparHTML(
                                vehiculo.Tipo_vehiculo
                            )}
                        </span>

                        <span class="
                            estado-chip
                            ${estaActivo
                                ? "activo"
                                : "inactivo"}
                        ">

                            <i class="bi ${
                                estaActivo
                                    ? "bi-check-circle"
                                    : "bi-x-circle"
                            } me-1"></i>

                            ${estaActivo
                                ? "Activo"
                                : "Inactivo"}

                        </span>

                    </div>

                    <div class="vehiculo-contenido">

                        <div class="vehiculo-titulo">

                            <div>

                                <small>
                                    ${escaparHTML(
                                        vehiculo.Marca
                                    )}
                                </small>

                                <h2>
                                    ${escaparHTML(
                                        vehiculo.Linea
                                    )}
                                </h2>

                            </div>

                            <span class="modelo-chip">

                                ${escaparHTML(
                                    vehiculo.Modelo
                                )}

                            </span>

                        </div>

                        <div class="vehiculo-datos">

                            <div class="vehiculo-dato">

                                <i class="bi bi-credit-card-2-front"></i>

                                <div class="vehiculo-dato-contenido">

                                    <small>
                                        Placa
                                    </small>

                                    <strong>
                                        ${escaparHTML(
                                            vehiculo.Placa
                                        )}
                                    </strong>

                                </div>

                            </div>

                            <div class="vehiculo-dato">

                                <i class="bi bi-palette"></i>

                                <div class="vehiculo-dato-contenido">

                                    <small>
                                        Color
                                    </small>

                                    <strong>
                                        ${escaparHTML(
                                            vehiculo.Color
                                        )}
                                    </strong>

                                </div>

                            </div>

                            <div class="vehiculo-dato">

                                <i class="bi bi-speedometer2"></i>

                                <div class="vehiculo-dato-contenido">

                                    <small>
                                        Kilometraje
                                    </small>

                                    <strong>

                                        ${formatearNumero(
                                            vehiculo.Kilometraje
                                        )} km

                                    </strong>

                                </div>

                            </div>

                            <div class="vehiculo-dato">

                                <i class="bi bi-fuel-pump"></i>

                                <div class="vehiculo-dato-contenido">

                                    <small>
                                        Combustible
                                    </small>

                                    <strong>
                                        ${escaparHTML(
                                            vehiculo.Combustible
                                        )}
                                    </strong>

                                </div>

                            </div>

                            <div class="vehiculo-dato">

                                <i class="bi bi-gear"></i>

                                <div class="vehiculo-dato-contenido">

                                    <small>
                                        Cilindraje
                                    </small>

                                    <strong>

                                        ${formatearNumero(
                                            vehiculo.Cilindraje
                                        )} cc

                                    </strong>

                                </div>

                            </div>

                            <div class="vehiculo-dato">

                                <i class="bi bi-upc-scan"></i>

                                <div class="vehiculo-dato-contenido">

                                    <small>
                                        No. de chasis
                                    </small>

                                    <strong>
                                        ${escaparHTML(
                                            vehiculo.No_chasis
                                        )}
                                    </strong>

                                </div>

                            </div>

                        </div>

                        <div class="vehiculo-acciones">

                            <button
                                type="button"
                                class="btn btn-outline-light btnEditarVehiculo"
                                data-id="${vehiculo.Id_vehiculo}"
                            >

                                <i class="bi bi-pencil-square me-1"></i>
                                Editar

                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-danger btnEliminarVehiculo"
                                data-id="${vehiculo.Id_vehiculo}"
                            >

                                <i class="bi bi-trash3 me-1"></i>
                                Eliminar

                            </button>

                        </div>

                    </div>

                </article>

            </div>
        `;

    }

    //=====================================
    // RENDERIZAR VEHÍCULOS
    //=====================================

    function renderizarVehiculos(
        lista = vehiculos
    ) {

        estadoCarga.classList.add("d-none");

        contenedorVehiculos.innerHTML = "";

        sinVehiculos.classList.add("d-none");
        sinResultados.classList.add("d-none");

        if (vehiculos.length === 0) {

            sinVehiculos.classList.remove("d-none");
            return;

        }

        if (lista.length === 0) {

            sinResultados.classList.remove("d-none");
            return;

        }

        contenedorVehiculos.innerHTML =
            lista
                .map(generarTarjetaVehiculo)
                .join("");

    }

    //=====================================
    // CARGAR VEHÍCULOS
    //=====================================

    async function cargarVehiculos() {

        estadoCarga.classList.remove("d-none");

        sinVehiculos.classList.add("d-none");
        sinResultados.classList.add("d-none");

        contenedorVehiculos.innerHTML = "";

        try {

            const respuesta = await fetch(
                `${RONEM_CONFIG.apiVehiculos}?action=listar`,
                {
                    method: "GET",
                    headers: {
                        "X-Requested-With":
                            "XMLHttpRequest"
                    }
                }
            );

            const resultado =
                await respuesta.json();

            if (
                !respuesta.ok ||
                !resultado.success
            ) {

                throw new Error(
                    resultado.message ||
                    "No fue posible cargar los vehículos."
                );

            }

            vehiculos =
                Array.isArray(resultado.data)
                    ? resultado.data
                    : [];

            actualizarContadores();
            renderizarVehiculos();

        } catch (error) {

            estadoCarga.classList.add("d-none");

            mostrarAlerta(
                error.message,
                "danger"
            );

        }

    }

    //=====================================
    // FILTRAR VEHÍCULOS
    //=====================================

    function filtrarVehiculos() {

        const texto =
            buscarVehiculo.value
                .trim()
                .toLowerCase();

        const tipoSeleccionado =
            filtroTipo.value
                .trim()
                .toLowerCase();

        const estadoSeleccionado =
            filtroEstado.value;

        const resultado = vehiculos.filter(
            vehiculo => {

                const datosBusqueda = [
                    vehiculo.Marca,
                    vehiculo.Linea,
                    vehiculo.Modelo,
                    vehiculo.Color,
                    vehiculo.Placa,
                    vehiculo.No_chasis,
                    vehiculo.Tipo_vehiculo,
                    vehiculo.Combustible
                ]
                    .join(" ")
                    .toLowerCase();

                const coincideTexto =
                    datosBusqueda.includes(texto);

                const coincideTipo =
                    tipoSeleccionado === "" ||
                    String(
                        vehiculo.Tipo_vehiculo
                    )
                        .toLowerCase() ===
                        tipoSeleccionado;

                const coincideEstado =
                    estadoSeleccionado === "" ||
                    String(
                        vehiculo.Estado
                    ) === estadoSeleccionado;

                return (
                    coincideTexto &&
                    coincideTipo &&
                    coincideEstado
                );

            }
        );

        renderizarVehiculos(resultado);

    }

    //=====================================
    // LIMPIAR FORMULARIO
    //=====================================

    function limpiarFormulario() {

        formVehiculo.reset();

        formVehiculo.classList.remove(
            "was-validated"
        );

        idVehiculo.value = "";

        estado.value = "1";

        imagen.value = "";

        previewImagen.src =
            RONEM_CONFIG.imagenPredeterminada;

        tituloModalVehiculo.textContent =
            "Registrar vehículo";

        const textoBoton =
            btnGuardarVehiculo.querySelector(
                ".texto-boton"
            );

        textoBoton.innerHTML = `
            <i class="bi bi-floppy me-2"></i>
            Guardar vehículo
        `;

    }

    //=====================================
    // CARGAR DATOS PARA EDITAR
    //=====================================

    function editarVehiculo(id) {

        const vehiculo = vehiculos.find(
            item =>
                Number(item.Id_vehiculo) ===
                Number(id)
        );

        if (!vehiculo) {

            mostrarAlerta(
                "No fue posible encontrar el vehículo.",
                "danger"
            );

            return;

        }

        idVehiculo.value =
            vehiculo.Id_vehiculo;

        marca.value =
            vehiculo.Marca ?? "";

        linea.value =
            vehiculo.Linea ?? "";

        modelo.value =
            vehiculo.Modelo ?? "";

        color.value =
            vehiculo.Color ?? "";

        placa.value =
            vehiculo.Placa ?? "";

        noChasis.value =
            vehiculo.No_chasis ?? "";

        tipoVehiculo.value =
            vehiculo.Tipo_vehiculo ?? "";

        cilindraje.value =
            vehiculo.Cilindraje ?? "";

        combustible.value =
            vehiculo.Combustible ?? "";

        kilometraje.value =
            vehiculo.Kilometraje ?? "";

        estado.value =
            String(vehiculo.Estado);

        imagen.value = "";

        previewImagen.src =
            obtenerRutaImagen(
                vehiculo.Imagen_URL
            );

        tituloModalVehiculo.textContent =
            "Editar vehículo";

        const textoBoton =
            btnGuardarVehiculo.querySelector(
                ".texto-boton"
            );

        textoBoton.innerHTML = `
            <i class="bi bi-check-lg me-2"></i>
            Guardar cambios
        `;

        modalVehiculo.show();

    }

    //=====================================
    // ACTIVAR CARGA DEL BOTÓN
    //=====================================

    function activarCargaBoton(activado) {

        btnGuardarVehiculo.disabled =
            activado;

        const texto =
            btnGuardarVehiculo.querySelector(
                ".texto-boton"
            );

        const cargando =
            btnGuardarVehiculo.querySelector(
                ".cargando-boton"
            );

        texto.classList.toggle(
            "d-none",
            activado
        );

        cargando.classList.toggle(
            "d-none",
            !activado
        );

    }

    //=====================================
    // PREVISUALIZAR IMAGEN
    //=====================================

    imagen.addEventListener(
        "change",
        () => {

            const archivo =
                imagen.files[0];

            if (!archivo) {
                return;
            }

            const tiposPermitidos = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (
                !tiposPermitidos.includes(
                    archivo.type
                )
            ) {

                imagen.value = "";

                mostrarAlerta(
                    "La imagen debe ser JPG, PNG o WEBP.",
                    "danger"
                );

                return;

            }

            const limite =
                5 * 1024 * 1024;

            if (archivo.size > limite) {

                imagen.value = "";

                mostrarAlerta(
                    "La imagen no debe superar los 5 MB.",
                    "danger"
                );

                return;

            }

            previewImagen.src =
                URL.createObjectURL(
                    archivo
                );

        }
    );

    //=====================================
    // GUARDAR VEHÍCULO
    //=====================================

    formVehiculo.addEventListener(
        "submit",
        async evento => {

            evento.preventDefault();

            if (
                !formVehiculo.checkValidity()
            ) {

                formVehiculo.classList.add(
                    "was-validated"
                );

                return;

            }

            activarCargaBoton(true);

            try {

                const datos =
                    new FormData(
                        formVehiculo
                    );

                const respuesta =
                    await fetch(
                        `${RONEM_CONFIG.apiVehiculos}?action=guardar`,
                        {
                            method: "POST",
                            body: datos,
                            headers: {
                                "X-Requested-With":
                                    "XMLHttpRequest"
                            }
                        }
                    );

                const resultado =
                    await respuesta.json();

                if (
                    !respuesta.ok ||
                    !resultado.success
                ) {

                    throw new Error(
                        resultado.message ||
                        "No fue posible guardar el vehículo."
                    );

                }

                modalVehiculo.hide();

                mostrarAlerta(
                    resultado.message,
                    "success"
                );

                await cargarVehiculos();

            } catch (error) {

                mostrarAlerta(
                    error.message,
                    "danger"
                );

            } finally {

                activarCargaBoton(false);

            }

        }
    );

    //=====================================
    // EVENTOS DE LAS TARJETAS
    //=====================================

    contenedorVehiculos.addEventListener(
        "click",
        evento => {

            const botonEditar =
                evento.target.closest(
                    ".btnEditarVehiculo"
                );

            const botonEliminar =
                evento.target.closest(
                    ".btnEliminarVehiculo"
                );

            if (botonEditar) {

                editarVehiculo(
                    botonEditar.dataset.id
                );

            }

            if (botonEliminar) {

                const id =
                    botonEliminar.dataset.id;

                const vehiculo =
                    vehiculos.find(
                        item =>
                            Number(
                                item.Id_vehiculo
                            ) === Number(id)
                    );

                if (!vehiculo) {

                    mostrarAlerta(
                        "No fue posible encontrar el vehículo.",
                        "danger"
                    );

                    return;

                }

                idVehiculoEliminar =
                    vehiculo.Id_vehiculo;

                nombreVehiculoEliminar.textContent =
                    `${vehiculo.Marca} ${vehiculo.Linea} (${vehiculo.Placa})`;

                modalEliminar.show();

            }

        }
    );

    //=====================================
    // CONFIRMAR ELIMINACIÓN
    //=====================================

    btnConfirmarEliminar.addEventListener(
        "click",
        async () => {

            if (!idVehiculoEliminar) {
                return;
            }

            btnConfirmarEliminar.disabled =
                true;

            const contenidoOriginal =
                btnConfirmarEliminar.innerHTML;

            btnConfirmarEliminar.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                ></span>
                Eliminando...
            `;

            try {

                const datos =
                    new FormData();

                datos.append(
                    "id",
                    idVehiculoEliminar
                );

                const respuesta =
                    await fetch(
                        `${RONEM_CONFIG.apiVehiculos}?action=eliminar`,
                        {
                            method: "POST",
                            body: datos,
                            headers: {
                                "X-Requested-With":
                                    "XMLHttpRequest"
                            }
                        }
                    );

                const resultado =
                    await respuesta.json();

                if (
                    !respuesta.ok ||
                    !resultado.success
                ) {

                    throw new Error(
                        resultado.message ||
                        "No fue posible eliminar el vehículo."
                    );

                }

                modalEliminar.hide();

                mostrarAlerta(
                    resultado.message,
                    "success"
                );

                await cargarVehiculos();

            } catch (error) {

                mostrarAlerta(
                    error.message,
                    "danger"
                );

            } finally {

                idVehiculoEliminar = null;

                btnConfirmarEliminar.disabled =
                    false;

                btnConfirmarEliminar.innerHTML =
                    contenidoOriginal;

            }

        }
    );

    //=====================================
    // BOTÓN NUEVO VEHÍCULO
    //=====================================

    btnNuevoVehiculo.addEventListener(
        "click",
        limpiarFormulario
    );

    // También limpia el formulario al utilizar
    // el botón del mensaje sin vehículos.

    document
        .querySelectorAll(
            '[data-bs-target="#modalVehiculo"]'
        )
        .forEach(boton => {

            boton.addEventListener(
                "click",
                () => {

                    if (
                        !boton.classList.contains(
                            "btnEditarVehiculo"
                        )
                    ) {

                        limpiarFormulario();

                    }

                }
            );

        });

    //=====================================
    // LIMPIAR AL CERRAR EL MODAL
    //=====================================

    modalVehiculoElemento.addEventListener(
        "hidden.bs.modal",
        limpiarFormulario
    );

    modalEliminarElemento.addEventListener(
        "hidden.bs.modal",
        () => {

            idVehiculoEliminar = null;

        }
    );

    //=====================================
    // EVENTOS DE FILTROS
    //=====================================

    buscarVehiculo.addEventListener(
        "input",
        filtrarVehiculos
    );

    filtroTipo.addEventListener(
        "change",
        filtrarVehiculos
    );

    filtroEstado.addEventListener(
        "change",
        filtrarVehiculos
    );

    // Convertir placa y chasis a mayúsculas.

    placa.addEventListener(
        "input",
        () => {

            placa.value =
                placa.value.toUpperCase();

        }
    );

    noChasis.addEventListener(
        "input",
        () => {

            noChasis.value =
                noChasis.value.toUpperCase();

        }
    );

    //=====================================
    // INICIAR MÓDULO
    //=====================================

    cargarVehiculos();

});