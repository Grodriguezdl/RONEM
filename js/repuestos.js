//==================================================
// REPUESTOS
//==================================================

let repuestos = [];
let repuestoEditando = null;

//==================================================
// OBTENER ELEMENTO
//==================================================

function elementoRepuesto(id) {
    return document.getElementById(id);
}

//==================================================
// ESCAPAR HTML
//==================================================

function escaparHTMLRepuesto(valor) {
    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

//==================================================
// LISTAR REPUESTOS
//==================================================

async function cargarRepuestos() {

    const tbody = elementoRepuesto("tbodyRepuestos");

    if (tbody) {
        tbody.innerHTML = `
            <tr class="empty-row">
                <td colspan="8">
                    Cargando repuestos...
                </td>
            </tr>
        `;
    }

    try {

        const respuesta = await fetch(
            "api/api_repuestos.php?action=listar",
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const texto = await respuesta.text();

        let datos;

        try {
            datos = JSON.parse(texto);
        } catch (errorJSON) {

            console.error(
                "Respuesta recibida desde api_repuestos.php:",
                texto
            );

            throw new Error(
                "La API devolvió una respuesta inválida."
            );
        }

        if (!respuesta.ok || datos.success === false) {

            throw new Error(
                datos.error ||
                datos.message ||
                "No se pudieron cargar los repuestos."
            );
        }

        repuestos = Array.isArray(datos)
            ? datos
            : [];

        renderRepuestos();

    } catch (error) {

        console.error(
            "Error al cargar repuestos:",
            error
        );

        if (tbody) {

            tbody.innerHTML = `
                <tr class="empty-row">
                    <td
                        colspan="8"
                        class="text-danger"
                    >
                        ${escaparHTMLRepuesto(error.message)}
                    </td>
                </tr>
            `;
        }
    }
}

//==================================================
// MOSTRAR REPUESTOS EN LA TABLA
//==================================================

function renderRepuestos() {

    const tbody = elementoRepuesto("tbodyRepuestos");
    const buscador = elementoRepuesto("buscarRepuesto");

    if (!tbody) {
        return;
    }

    const textoBusqueda = buscador
        ? buscador.value.trim().toLowerCase()
        : "";

    const listaFiltrada = repuestos.filter(
        (repuesto) => {

            const contenido = `
                ${repuesto.Id_repuesto || ""}
                ${repuesto.Nombre || ""}
                ${repuesto.Descripcion || ""}
                ${repuesto.Imagen_URL || ""}
                ${repuesto.Stock || ""}
                ${repuesto.Precio || ""}
            `.toLowerCase();

            return contenido.includes(textoBusqueda);
        }
    );

    if (listaFiltrada.length === 0) {

        tbody.innerHTML = `
            <tr class="empty-row">
                <td colspan="8">
                    No hay repuestos registrados
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML = listaFiltrada.map(
        (repuesto) => {

            const id = Number(
                repuesto.Id_repuesto
            );

            const nombre = escaparHTMLRepuesto(
                repuesto.Nombre
            );

            const descripcion = escaparHTMLRepuesto(
                repuesto.Descripcion
            );

            const rutaImagen = String(
                repuesto.Imagen_URL || ""
            );

            const rutaSegura = escaparHTMLRepuesto(
                rutaImagen
            );

            const stock = Number(
                repuesto.Stock || 0
            );

            const precio = Number(
                repuesto.Precio || 0
            );

            const imagenHTML = rutaImagen !== ""
                ? `
                    <img
                        src="${rutaSegura}"
                        alt="${nombre}"
                        style="
                            width:60px;
                            height:50px;
                            object-fit:contain;
                            border:1px solid #dddddd;
                            border-radius:6px;
                            padding:3px;
                            background:#ffffff;
                        "
                        onerror="
                            this.style.display='none';
                            this.nextElementSibling.style.display='inline-block';
                        "
                    >

                    <i
                        class="bi bi-image text-muted"
                        style="
                            display:none;
                            font-size:24px;
                        "
                    ></i>
                `
                : `
                    <i
                        class="bi bi-image text-muted"
                        style="font-size:24px;"
                    ></i>
                `;

            return `
                <tr>

                    <td>
                        ${id}
                    </td>

                    <td class="text-center">
                        ${imagenHTML}
                    </td>

                    <td>
                        <strong>
                            ${nombre}
                        </strong>
                    </td>

                    <td>
                        ${descripcion}
                    </td>

                    <td>

                        ${
                            rutaImagen !== ""
                                ? `
                                    <small
                                        class="text-muted"
                                        title="${rutaSegura}"
                                        style="
                                            display:inline-block;
                                            max-width:210px;
                                            overflow:hidden;
                                            text-overflow:ellipsis;
                                            white-space:nowrap;
                                        "
                                    >
                                        ${rutaSegura}
                                    </small>
                                `
                                : `
                                    <span class="text-muted">
                                        Sin imagen
                                    </span>
                                `
                        }

                    </td>

                    <td>
                        ${stock}
                    </td>

                    <td>
                        Q${precio.toFixed(2)}
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-warning btn-sm me-1"
                            onclick="editarRepuesto(${id})"
                            title="Editar repuesto"
                        >
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            onclick="eliminarRepuesto(${id})"
                            title="Eliminar repuesto"
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
// GUARDAR REPUESTTO
//==================================================

async function guardarRepuesto() {

    const form = elementoRepuesto(
        "formRepuesto"
    );

    const boton = elementoRepuesto(
        "btnGuardarRepuesto"
    );

    if (!form) {
        return;
    }

    if (!form.checkValidity()) {

        form.reportValidity();

        return;
    }

    const nombre = elementoRepuesto(
        "rNombre"
    ).value.trim();

    const descripcion = elementoRepuesto(
        "rDescripcion"
    ).value.trim();

    const stock = elementoRepuesto(
        "rStock"
    ).value;

    const precio = elementoRepuesto(
        "rPrecio"
    ).value;

    const inputImagen = elementoRepuesto(
        "rImagen"
    );

    const archivo =
        inputImagen &&
        inputImagen.files.length > 0
            ? inputImagen.files[0]
            : null;

    if (
        repuestoEditando === null &&
        !archivo
    ) {

        alert(
            "Selecciona una imagen para el nuevo repuesto."
        );

        return;
    }

    const datos = new FormData();

    if (repuestoEditando !== null) {

        datos.append(
            "id",
            repuestoEditando
        );
    }

    datos.append(
        "nombre",
        nombre
    );

    datos.append(
        "descripcion",
        descripcion
    );

    datos.append(
        "stock",
        stock
    );

    datos.append(
        "precio",
        precio
    );

    if (archivo) {

        datos.append(
            "imagen",
            archivo
        );
    }

    try {

        if (boton) {

            boton.disabled = true;

            boton.dataset.textoOriginal =
                boton.innerHTML;

            boton.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-2"
                ></span>

                Guardando...
            `;
        }

        const respuesta = await fetch(
            "api/api_repuestos.php?action=guardar",
            {
                method: "POST",
                body: datos
            }
        );

        const texto = await respuesta.text();

        let resultado;

        try {

            resultado = JSON.parse(texto);

        } catch (errorJSON) {

            console.error(
                "Respuesta recibida desde api_repuestos.php:",
                texto
            );

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
                resultado.message ||
                "No se pudo guardar el repuesto."
            );
        }

        alert(
            resultado.mensaje ||
            "Repuesto guardado correctamente."
        );

        const modalElemento = elementoRepuesto(
            "modalRepuesto"
        );

        if (modalElemento) {

            bootstrap.Modal
                .getOrCreateInstance(modalElemento)
                .hide();
        }

        limpiarRepuesto();

        await cargarRepuestos();

    } catch (error) {

        console.error(
            "Error al guardar repuesto:",
            error
        );

        alert(
            error.message ||
            "No se pudo guardar el repuesto."
        );

    } finally {

        if (boton) {

            boton.disabled = false;

            boton.innerHTML =
                boton.dataset.textoOriginal ||
                `
                    <i class="bi bi-floppy me-2"></i>
                    Guardar Repuesto
                `;
        }
    }
}

//==================================================
// EDITAR REPUESTO
//==================================================

function editarRepuesto(id) {

    const repuesto = repuestos.find(
        (item) =>
            Number(item.Id_repuesto) ===
            Number(id)
    );

    if (!repuesto) {

        alert(
            "No se encontró el repuesto."
        );

        return;
    }

    repuestoEditando = Number(id);

    elementoRepuesto(
        "repuestoId"
    ).value = repuestoEditando;

    elementoRepuesto(
        "rNombre"
    ).value = repuesto.Nombre || "";

    elementoRepuesto(
        "rDescripcion"
    ).value = repuesto.Descripcion || "";

    elementoRepuesto(
        "rStock"
    ).value = repuesto.Stock ?? "";

    elementoRepuesto(
        "rPrecio"
    ).value = repuesto.Precio ?? "";

    elementoRepuesto(
        "tituloModalRepuesto"
    ).textContent = "Editar Repuesto";

    const rutaImagen = String(
        repuesto.Imagen_URL || ""
    );

    const rutaActual = elementoRepuesto(
        "rImagenActual"
    );

    const contenedorRuta = elementoRepuesto(
        "contenedorRutaRepuesto"
    );

    const imagenVista = elementoRepuesto(
        "vistaImagenRepuesto"
    );

    const contenedorVista = elementoRepuesto(
        "contenedorVistaRepuesto"
    );

    if (rutaActual) {

        rutaActual.value = rutaImagen;
    }

    if (rutaImagen !== "") {

        if (contenedorRuta) {

            contenedorRuta.style.display =
                "block";
        }

        if (imagenVista) {

            imagenVista.src = rutaImagen;
        }

        if (contenedorVista) {

            contenedorVista.style.display =
                "block";
        }

    } else {

        if (contenedorRuta) {

            contenedorRuta.style.display =
                "none";
        }

        if (imagenVista) {

            imagenVista.src = "";
        }

        if (contenedorVista) {

            contenedorVista.style.display =
                "none";
        }
    }

    const modal = elementoRepuesto(
        "modalRepuesto"
    );

    bootstrap.Modal
        .getOrCreateInstance(modal)
        .show();
}

//==================================================
// ELIMINAR REPUESTO
//==================================================

async function eliminarRepuesto(id) {

    const confirmar = confirm(
        "¿Eliminar este repuesto? También se eliminará su imagen."
    );

    if (!confirmar) {
        return;
    }

    try {

        const respuesta = await fetch(
            "api/api_repuestos.php?action=eliminar&id=" +
            encodeURIComponent(id),
            {
                method: "GET",
                cache: "no-store"
            }
        );

        const texto = await respuesta.text();

        let resultado;

        try {

            resultado = JSON.parse(texto);

        } catch (errorJSON) {

            console.error(
                "Respuesta recibida desde api_repuestos.php:",
                texto
            );

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
                resultado.message ||
                "No se pudo eliminar el repuesto."
            );
        }

        alert(
            resultado.mensaje ||
            "Repuesto eliminado correctamente."
        );

        await cargarRepuestos();

    } catch (error) {

        console.error(
            "Error al eliminar repuesto:",
            error
        );

        alert(
            error.message ||
            "No se pudo eliminar el repuesto."
        );
    }
}

//==================================================
// PREVISUALIZAR IMAGEN
//==================================================

function previsualizarImagenRepuesto() {

    const input = elementoRepuesto(
        "rImagen"
    );

    const contenedor = elementoRepuesto(
        "contenedorVistaRepuesto"
    );

    const imagen = elementoRepuesto(
        "vistaImagenRepuesto"
    );

    if (
        !input ||
        !contenedor ||
        !imagen
    ) {
        return;
    }

    const archivo = input.files[0];

    if (!archivo) {

        if (repuestoEditando === null) {

            contenedor.style.display =
                "none";

            imagen.src = "";
        }

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

        alert(
            "Selecciona una imagen JPG, PNG o WEBP."
        );

        input.value = "";

        return;
    }

    if (
        archivo.size >
        5 * 1024 * 1024
    ) {

        alert(
            "La imagen no puede superar los 5 MB."
        );

        input.value = "";

        return;
    }

    const lector = new FileReader();

    lector.onload = function (evento) {

        imagen.src =
            evento.target.result;

        contenedor.style.display =
            "block";
    };

    lector.readAsDataURL(archivo);
}

//==================================================
// LIMPIAR FORMULARIO
//==================================================

function limpiarRepuesto() {

    repuestoEditando = null;

    const form = elementoRepuesto(
        "formRepuesto"
    );

    if (form) {
        form.reset();
    }

    const id = elementoRepuesto(
        "repuestoId"
    );

    if (id) {
        id.value = "";
    }

    const titulo = elementoRepuesto(
        "tituloModalRepuesto"
    );

    if (titulo) {

        titulo.textContent =
            "Agregar Nuevo Repuesto";
    }

    const rutaActual = elementoRepuesto(
        "rImagenActual"
    );

    if (rutaActual) {
        rutaActual.value = "";
    }

    const contenedorRuta = elementoRepuesto(
        "contenedorRutaRepuesto"
    );

    if (contenedorRuta) {

        contenedorRuta.style.display =
            "none";
    }

    const imagen = elementoRepuesto(
        "vistaImagenRepuesto"
    );

    if (imagen) {
        imagen.src = "";
    }

    const contenedorVista = elementoRepuesto(
        "contenedorVistaRepuesto"
    );

    if (contenedorVista) {

        contenedorVista.style.display =
            "none";
    }
}

//==================================================
// EVENTOS
//==================================================

document.addEventListener(
    "DOMContentLoaded",
    () => {

        cargarRepuestos();

        const buscador = elementoRepuesto(
            "buscarRepuesto"
        );

        if (buscador) {

            buscador.addEventListener(
                "input",
                renderRepuestos
            );
        }

        const inputImagen = elementoRepuesto(
            "rImagen"
        );

        if (inputImagen) {

            inputImagen.addEventListener(
                "change",
                previsualizarImagenRepuesto
            );
        }

        const modal = elementoRepuesto(
            "modalRepuesto"
        );

        if (modal) {

            modal.addEventListener(
                "hidden.bs.modal",
                limpiarRepuesto
            );
        }
    }
);