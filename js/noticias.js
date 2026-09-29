//==============================================================// NOTICIAS//==============================================================

let noticiasCache = [];let noticiaEditandoId = null;

//==============================================================// INICIO//==============================================================

document.addEventListener("DOMContentLoaded", () => {

const tbody =
    document.getElementById("tbodyNoticias");

if (tbody) {
    configurarInputNoticia();
    cargarNoticias();
}

const carouselBody =
    document.getElementById(
        "carouselNoticiasBody"
    );

const indicadores =
    document.getElementById(
        "carouselIndicadores"
    );

const carousel =
    document.getElementById(
        "carouselNoticias"
    );

if (
    carouselBody &&
    indicadores &&
    carousel &&
    typeof cargarNoticiasCarousel === "function"
) {
    cargarNoticiasCarousel();
}

});

//==============================================================// INPUT DE IMAGEN//==============================================================

function configurarInputNoticia() {

const input =
    document.getElementById("inputImgNoticia");

const nombre =
    document.getElementById("nombreImgNoticia");

if (!input || !nombre) {
    return;
}

input.addEventListener("change", () => {

    const archivo = input.files?.[0];

    if (!archivo) {
        nombre.textContent =
            "Sin archivos seleccionados";

        return;
    }

    const permitidos = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (!permitidos.includes(archivo.type)) {

        alert(
            "Seleccione una imagen JPG, PNG o WEBP."
        );

        input.value = "";

        nombre.textContent =
            "Sin archivos seleccionados";

        return;
    }

    if (archivo.size > 5 * 1024 * 1024) {

        alert(
            "La imagen no debe superar los 5 MB."
        );

        input.value = "";

        nombre.textContent =
            "Sin archivos seleccionados";

        return;
    }

    nombre.textContent = archivo.name;
});

}//==============================================================// CARGAR NOTICIAS EN EL PANEL ADMINISTRATIVO//==============================================================

async function cargarNoticias() {

const tbody =
    document.getElementById("tbodyNoticias");

if (!tbody) {
    return;
}

tbody.innerHTML = `
    <tr class="empty-row">
        <td colspan="4">
            Cargando noticias...
        </td>
    </tr>
`;

try {

    const respuesta = await fetch(
        "api/api_noticias.php?action=listar",
        {
            method: "GET",
            cache: "no-store"
        }
    );

    const textoRespuesta =
        await respuesta.text();

    let resultado;

    try {
        resultado = JSON.parse(textoRespuesta);
    } catch (error) {
        console.error(
            "Respuesta no válida de api_noticias.php:",
            textoRespuesta
        );

        throw new Error(
            "El servidor devolvió una respuesta no válida."
        );
    }

    if (!respuesta.ok) {
        throw new Error(
            resultado.mensaje ||
            "No se pudieron cargar las noticias."
        );
    }

    if (!Array.isArray(resultado)) {
        throw new Error(
            resultado.mensaje ||
            "El formato recibido no es válido."
        );
    }

    noticiasCache = resultado;

    renderNoticias();

} catch (error) {

    console.error(
        "Error al cargar noticias:",
        error
    );

    tbody.innerHTML = `
        <tr class="empty-row">
            <td colspan="4">
                <div class="text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    ${escaparHTML(
                        error.message ||
                        "Error al cargar noticias."
                    )}
                </div>
            </td>
        </tr>
    `;
}

}

//==============================================================// MOSTRAR NOTICIAS EN LA TABLA//==============================================================

function renderNoticias() {

const tbody =
    document.getElementById("tbodyNoticias");

if (!tbody) {
    return;
}

if (!Array.isArray(noticiasCache)) {
    noticiasCache = [];
}

if (noticiasCache.length === 0) {

    tbody.innerHTML = `
        <tr class="empty-row">
            <td colspan="4">
                No hay imágenes registradas
            </td>
        </tr>
    `;

    return;
}

tbody.innerHTML = noticiasCache.map(noticia => {

    const id = Number(
        noticia.Id_noticia ?? 0
    );

    const descripcion = String(
        noticia.Descripcion ??
        ""
    );

    const imagen = obtenerRutaImagen(
        noticia.Imagen_URL
    );

    const fecha = formatearFechaHora(
        noticia.Fecha
    );

    return `
        <tr>
            <td>
                <img
                    src="${escaparHTML(imagen)}"
                    alt="${escaparHTML(descripcion)}"
                    style="
                        width: 110px;
                        height: 65px;
                        object-fit: cover;
                        border-radius: 7px;
                        border: 1px solid #dddddd;
                    "
                    onerror="
                        this.src='favicoB.png';
                        this.style.objectFit='contain';
                    "
                >
            </td>

            <td>
                ${escaparHTML(descripcion)}
            </td>

            <td>
                ${escaparHTML(fecha)}
            </td>

            <td>
                <div class="d-flex gap-2">

                    <button
                        type="button"
                        class="btn btn-sm btn-warning"
                        onclick="editarNoticia(${id})"
                        title="Editar noticia"
                    >
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        onclick="eliminarNoticia(${id})"
                        title="Eliminar noticia"
                    >
                        <i class="bi bi-trash"></i>
                    </button>

                </div>
            </td>
        </tr>
    `;

}).join("");

}

//==============================================================// AGREGAR O ACTUALIZAR NOTICIA//==============================================================

async function agregarNoticia() {

const inputImagen =
    document.getElementById("inputImgNoticia");

const inputDescripcion =
    document.getElementById("descNoticia");

const nombreImagen =
    document.getElementById("nombreImgNoticia");

if (!inputImagen || !inputDescripcion) {
    alert(
        "No se encontraron los campos de la noticia."
    );

    return;
}

const descripcion =
    inputDescripcion.value.trim();

const archivo =
    inputImagen.files[0];

if (descripcion === "") {
    alert(
        "Debe escribir una descripción."
    );

    inputDescripcion.focus();

    return;
}

if (
    noticiaEditandoId === null &&
    !archivo
) {
    alert(
        "Debe seleccionar una imagen."
    );

    return;
}

if (archivo) {

    const tiposPermitidos = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (!tiposPermitidos.includes(archivo.type)) {

        alert(
            "Solo se permiten imágenes JPG, PNG o WEBP."
        );

        inputImagen.value = "";

        if (nombreImagen) {
            nombreImagen.textContent =
                "Sin archivos seleccionados";
        }

        return;
    }

    const limite = 5 * 1024 * 1024;

    if (archivo.size > limite) {

        alert(
            "La imagen no debe superar los 5 MB."
        );

        inputImagen.value = "";

        if (nombreImagen) {
            nombreImagen.textContent =
                "Sin archivos seleccionados";
        }

        return;
    }
}

const datos = new FormData();

if (noticiaEditandoId !== null) {
    datos.append(
        "id",
        noticiaEditandoId
    );
}

datos.append(
    "descripcion",
    descripcion
);

if (archivo) {
    datos.append(
        "imagen",
        archivo
    );
}

const boton =
    document.querySelector(
        "#page-noticias button[onclick='agregarNoticia()']"
    );

const textoAnterior =
    boton ? boton.innerHTML : "";

try {

    if (boton) {
        boton.disabled = true;

        boton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm"
                aria-hidden="true"
            ></span>
            Guardando...
        `;
    }

    const respuesta = await fetch(
        "api/api_noticias.php?action=guardar",
        {
            method: "POST",
            body: datos
        }
    );

    const textoRespuesta =
        await respuesta.text();

    let resultado;

    try {
        resultado = JSON.parse(textoRespuesta);
    } catch (error) {

        console.error(
            "Respuesta del servidor:",
            textoRespuesta
        );

        throw new Error(
            "El servidor devolvió una respuesta no válida."
        );
    }

    if (
        !respuesta.ok ||
        resultado.success === false
    ) {
        throw new Error(
            resultado.mensaje ||
            "No se pudo guardar la noticia."
        );
    }

    alert(
        resultado.mensaje ||
        "Noticia guardada correctamente."
    );

    limpiarFormularioNoticia();

    await cargarNoticias();

} catch (error) {

    console.error(
        "Error al guardar noticia:",
        error
    );

    alert(
        error.message ||
        "No se pudo guardar la noticia."
    );

} finally {

    if (boton) {
        boton.disabled = false;

        boton.innerHTML =
            textoAnterior ||
            '<i class="bi bi-plus-lg"></i> Agregar Imagen';
    }
}

}

//==============================================================// EDITAR NOTICIA//==============================================================

function editarNoticia(id) {

const noticia = noticiasCache.find(
    item =>
        Number(item.Id_noticia) ===
        Number(id)
);

if (!noticia) {
    alert(
        "No se encontró la noticia seleccionada."
    );

    return;
}

noticiaEditandoId = Number(id);

const descripcion =
    document.getElementById("descNoticia");

const nombreImagen =
    document.getElementById("nombreImgNoticia");

const boton =
    document.querySelector(
        "#page-noticias button[onclick='agregarNoticia()']"
    );

if (descripcion) {

    descripcion.value =
        noticia.Descripcion ?? "";

    descripcion.focus();
}

if (nombreImagen) {
    nombreImagen.textContent =
        "Mantener imagen actual o seleccionar una nueva";
}

if (boton) {
    boton.innerHTML = `
        <i class="bi bi-floppy"></i>
        Guardar Cambios
    `;
}

const formulario =
    document.querySelector(
        "#page-noticias .form-section-bg"
    );

if (formulario) {
    formulario.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
}

}

//==============================================================// ELIMINAR NOTICIA//==============================================================

async function eliminarNoticia(id) {

const confirmar = confirm(
    "¿Está seguro de eliminar esta noticia?"
);

if (!confirmar) {
    return;
}

const datos = new FormData();

datos.append(
    "id",
    id
);

try {

    const respuesta = await fetch(
        "api/api_noticias.php?action=eliminar",
        {
            method: "POST",
            body: datos
        }
    );

    const textoRespuesta =
        await respuesta.text();

    let resultado;

    try {
        resultado = JSON.parse(textoRespuesta);
    } catch (error) {

        console.error(
            "Respuesta del servidor:",
            textoRespuesta
        );

        throw new Error(
            "El servidor devolvió una respuesta no válida."
        );
    }

    if (
        !respuesta.ok ||
        resultado.success === false
    ) {
        throw new Error(
            resultado.mensaje ||
            "No se pudo eliminar la noticia."
        );
    }

    alert(
        resultado.mensaje ||
        "Noticia eliminada correctamente."
    );

    if (
        noticiaEditandoId !== null &&
        Number(noticiaEditandoId) === Number(id)
    ) {
        limpiarFormularioNoticia();
    }

    await cargarNoticias();

} catch (error) {

    console.error(
        "Error al eliminar noticia:",
        error
    );

    alert(
        error.message ||
        "No se pudo eliminar la noticia."
    );
}

}

//==============================================================// LIMPIAR FORMULARIO//==============================================================

function limpiarFormularioNoticia() {

noticiaEditandoId = null;

const inputImagen =
    document.getElementById("inputImgNoticia");

const inputDescripcion =
    document.getElementById("descNoticia");

const nombreImagen =
    document.getElementById("nombreImgNoticia");

const boton =
    document.querySelector(
        "#page-noticias button[onclick='agregarNoticia()']"
    );

if (inputImagen) {
    inputImagen.value = "";
}

if (inputDescripcion) {
    inputDescripcion.value = "";
}

if (nombreImagen) {
    nombreImagen.textContent =
        "Sin archivos seleccionados";
}

if (boton) {
    boton.innerHTML = `
        <i class="bi bi-plus-lg"></i>
        Agregar Imagen
    `;
}

}

//==============================================================// CARRUSEL DE NOTICIAS DE LA PÁGINA PÚBLICA//==============================================================

async function cargarNoticiasCarousel() {

const carouselBody =
    document.getElementById(
        "carouselNoticiasBody"
    );

const indicadores =
    document.getElementById(
        "carouselIndicadores"
    );

const carouselPrincipal =
    document.getElementById(
        "carouselNoticias"
    );

if (
    !carouselBody ||
    !indicadores ||
    !carouselPrincipal
) {
    return;
}

carouselBody.innerHTML = `
    <div class="noticias-cargando">

        <div
            class="spinner-border text-danger"
            role="status"
        >
            <span class="visually-hidden">
                Cargando...
            </span>
        </div>

        <p>Cargando noticias...</p>

    </div>
`;

indicadores.innerHTML = "";

try {

    const respuesta = await fetch(
        "api/api_noticias.php?action=listar",
        {
            method: "GET",
            cache: "no-store"
        }
    );

    const textoRespuesta =
        await respuesta.text();

    let noticias;

    try {
        noticias = JSON.parse(textoRespuesta);
    } catch (error) {

        console.error(
            "Respuesta recibida:",
            textoRespuesta
        );

        throw new Error(
            "La respuesta recibida no es válida."
        );
    }

    if (!respuesta.ok) {
        throw new Error(
            noticias.mensaje ||
            "No se pudieron consultar las noticias."
        );
    }

    if (!Array.isArray(noticias)) {
        throw new Error(
            noticias.mensaje ||
            "La respuesta recibida no es válida."
        );
    }

    if (noticias.length === 0) {
        mostrarCarouselVacio();
        return;
    }

    carouselBody.innerHTML = "";
    indicadores.innerHTML = "";

    noticias.forEach(
        (noticia, indice) => {

            const activa =
                indice === 0
                    ? "active"
                    : "";

            const descripcion =
                escaparHTML(
                    noticia.Descripcion ||
                    "Noticia RONEM"
                );

            const fecha =
                formatearFecha(
                    noticia.Fecha
                );

            const rutaImagen =
                obtenerRutaImagen(
                    noticia.Imagen_URL
                );

            //==============================================
            // INDICADOR
            //==============================================

            const indicador =
                document.createElement(
                    "button"
                );

            indicador.type =
                "button";

            indicador.setAttribute(
                "data-bs-target",
                "#carouselNoticias"
            );

            indicador.setAttribute(
                "data-bs-slide-to",
                indice.toString()
            );

            indicador.setAttribute(
                "aria-label",
                `Noticia ${indice + 1}`
            );

            if (indice === 0) {

                indicador.classList.add(
                    "active"
                );

                indicador.setAttribute(
                    "aria-current",
                    "true"
                );
            }

            indicadores.appendChild(
                indicador
            );

            //==============================================
            // DIAPOSITIVA
            //==============================================

            const slide =
                document.createElement(
                    "div"
                );

            slide.className =
                `carousel-item ${activa}`;

            slide.innerHTML = `
                <div class="noticia-slide">

                    <img
                        src="${escaparHTML(rutaImagen)}"
                        class="d-block w-100 noticia-imagen"
                        alt="${descripcion}"
                        loading="${indice === 0 ? "eager" : "lazy"}"
                        onerror="
                            this.src='favicoB.png';
                            this.classList.add('noticia-imagen-error');
                        "
                    >

                    <div class="noticia-filtro"></div>

                    <div class="carousel-caption noticia-caption">

                        <div class="noticia-contenido">

                            <span class="noticia-etiqueta">

                                <i class="bi bi-newspaper"></i>

                                NOTICIAS RONEM

                            </span>

                            <h3 class="noticia-descripcion">
                                ${descripcion}
                            </h3>

                            <div class="noticia-fecha">

                                <i class="bi bi-calendar-event"></i>

                                ${fecha}

                            </div>

                        </div>

                    </div>

                </div>
            `;

            carouselBody.appendChild(
                slide
            );
        }
    );

    const controles =
        carouselPrincipal.querySelectorAll(
            ".carousel-control-prev, .carousel-control-next"
        );

    controles.forEach(control => {

        control.style.display =
            noticias.length > 1
                ? ""
                : "none";
    });

    indicadores.style.display =
        noticias.length > 1
            ? "flex"
            : "none";

    if (
        typeof bootstrap !==
        "undefined"
    ) {

        const instanciaAnterior =
            bootstrap.Carousel.getInstance(
                carouselPrincipal
            );

        if (instanciaAnterior) {
            instanciaAnterior.dispose();
        }

        new bootstrap.Carousel(
            carouselPrincipal,
            {
                interval: 6000,
                ride: "carousel",
                pause: "hover",
                touch: true,
                wrap: true
            }
        );
    }

} catch (error) {

    console.error(
        "Error al cargar el carrusel:",
        error
    );

    carouselBody.innerHTML = `
        <div class="noticias-error">

            <i class="bi bi-exclamation-triangle"></i>

            <h3>
                No se pudieron cargar las noticias
            </h3>

            <p>
                ${escaparHTML(
                    error.message ||
                    "Verifique la conexión con el servidor."
                )}
            </p>

            <button
                type="button"
                class="btn btn-outline-danger"
                onclick="cargarNoticiasCarousel()"
            >
                Intentar nuevamente
            </button>

        </div>
    `;

    indicadores.innerHTML = "";
}

}

//==============================================================// CARRUSEL VACÍO//==============================================================

function mostrarCarouselVacio() {

const carouselBody =
    document.getElementById(
        "carouselNoticiasBody"
    );

const indicadores =
    document.getElementById(
        "carouselIndicadores"
    );

if (
    !carouselBody ||
    !indicadores
) {
    return;
}

indicadores.innerHTML = "";

carouselBody.innerHTML = `
    <div class="noticias-vacio">

        <i class="bi bi-newspaper"></i>

        <h3>
            No hay noticias disponibles
        </h3>

        <p>
            Cuando registres noticias desde el panel
            administrativo aparecerán automáticamente aquí.
        </p>

    </div>
`;

}

//==============================================================// RUTA DE LA IMAGEN//==============================================================

function obtenerRutaImagen(ruta) {

if (!ruta) {
    return "favicoB.png";
}

ruta = String(ruta).trim();

if (
    ruta.startsWith("http://") ||
    ruta.startsWith("https://") ||
    ruta.startsWith("data:")
) {
    return ruta;
}

ruta = ruta.replace(
    /^(\.\.\/)+/,
    ""
);

ruta = ruta.replace(
    /^(\.\/)+/,
    ""
);

ruta = ruta.replace(
    /^\/+/,
    ""
);

return ruta;

}

//==============================================================// FORMATEAR FECHA PARA EL CARRUSEL//==============================================================

function formatearFecha(fechaBaseDatos) {

if (!fechaBaseDatos) {
    return "Fecha no disponible";
}

const fechaCompatible =
    String(fechaBaseDatos)
        .replace(" ", "T");

const fecha =
    new Date(fechaCompatible);

if (
    Number.isNaN(
        fecha.getTime()
    )
) {
    return fechaBaseDatos;
}

return fecha.toLocaleDateString(
    "es-GT",
    {
        day: "2-digit",
        month: "long",
        year: "numeric"
    }
);

}

//==============================================================// FORMATEAR FECHA PARA LA TABLA//==============================================================

function formatearFechaHora(fechaBaseDatos) {

if (!fechaBaseDatos) {
    return "Fecha no disponible";
}

const fechaCompatible =
    String(fechaBaseDatos)
        .replace(" ", "T");

const fecha =
    new Date(fechaCompatible);

if (
    Number.isNaN(
        fecha.getTime()
    )
) {
    return fechaBaseDatos;
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

//==============================================================// ESCAPAR HTML//==============================================================

function escaparHTML(valor) {

return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

}