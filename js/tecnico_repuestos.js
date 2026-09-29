/*==================================================
MÓDULO DE REPUESTOS DEL PANEL TÉCNICO
==================================================*/

let repuestosTecnico = [];


/*==================================================
INICIALIZAR
==================================================*/

document.addEventListener("DOMContentLoaded", () => {

    cargarRepuestosTecnico();
    iniciarBuscadorRepuestosTecnico();

});


/*==================================================
CARGAR REPUESTOS
==================================================*/

async function cargarRepuestosTecnico() {

    const contenedor =
        document.getElementById(
            "container-repuestos"
        );

    if (!contenedor) {
        return;
    }

    if (
        typeof window.mostrarCarga ===
        "function"
    ) {

        window.mostrarCarga(
            "container-repuestos",
            "Cargando inventario de repuestos..."
        );

    } else {

        contenedor.innerHTML = `
            <p class="text-muted">
                Cargando inventario de repuestos...
            </p>
        `;

    }

    try {

        const respuesta =
            await fetch(
                "api/api_repuestos_tecnico.php?action=listar",
                {
                    method: "GET",
                    cache: "no-store",
                    headers: {
                        "Accept": "application/json"
                    }
                }
            );

        const texto =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(texto);

        } catch (error) {

            console.error(
                "Respuesta de la API de repuestos:",
                texto
            );

            throw new Error(
                "La API de repuestos devolvió una respuesta inválida."
            );

        }

        if (!respuesta.ok) {

            throw new Error(
                resultado.mensaje ||
                resultado.message ||
                "No se pudieron cargar los repuestos."
            );

        }

        if (
            resultado &&
            typeof resultado === "object" &&
            !Array.isArray(resultado) &&
            resultado.success === false
        ) {

            throw new Error(
                resultado.mensaje ||
                resultado.message ||
                "No se pudieron cargar los repuestos."
            );

        }

        repuestosTecnico =
            extraerRepuestosTecnico(
                resultado
            );

        renderRepuestosTecnico(
            repuestosTecnico
        );

    } catch (error) {

        console.error(
            "Error al cargar repuestos:",
            error
        );

        if (
            typeof window.mostrarError ===
            "function"
        ) {

            window.mostrarError(
                "container-repuestos",
                error.message
            );

        } else {

            contenedor.innerHTML = `
                <div class="error-state">

                    <h2>
                        Ocurrió un error
                    </h2>

                    <p>
                        ${escaparHTMLRepuestos(
                            error.message
                        )}
                    </p>

                </div>
            `;

        }

    }

}


/*==================================================
EXTRAER ARREGLO DE REPUESTOS
==================================================*/

function extraerRepuestosTecnico(
    resultado
) {

    if (
        Array.isArray(resultado)
    ) {

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
        Array.isArray(resultado.repuestos)
    ) {

        return resultado.repuestos;

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
RENDERIZAR REPUESTOS
==================================================*/

function renderRepuestosTecnico(
    repuestos
) {

    const contenedor =
        document.getElementById(
            "container-repuestos"
        );

    if (!contenedor) {
        return;
    }

    if (
        !Array.isArray(repuestos) ||
        repuestos.length === 0
    ) {

        if (
            typeof window.mostrarVacio ===
            "function"
        ) {

            window.mostrarVacio(
                "container-repuestos",
                "No hay repuestos",
                "No existen repuestos disponibles en el inventario."
            );

        } else {

            contenedor.innerHTML = `
                <div class="empty-state">

                    <h2>
                        No hay repuestos
                    </h2>

                    <p>
                        No existen repuestos disponibles.
                    </p>

                </div>
            `;

        }

        return;

    }

    contenedor.innerHTML = `

        <div class="repuestos-grid-tecnico">

            ${repuestos
                .map(
                    repuesto =>
                        crearTarjetaRepuestoTecnico(
                            repuesto
                        )
                )
                .join("")}

        </div>

    `;

}


/*==================================================
CREAR TARJETA
==================================================*/

function crearTarjetaRepuestoTecnico(
    repuesto
) {

    const id =
        obtenerValorRepuesto(
            repuesto,
            [
                "Id_repuesto",
                "id_repuesto",
                "Id",
                "id"
            ],
            0
        );

    const nombre =
        obtenerValorRepuesto(
            repuesto,
            [
                "Nombre",
                "nombre"
            ],
            "Repuesto sin nombre"
        );

    const descripcion =
        obtenerValorRepuesto(
            repuesto,
            [
                "Descripcion",
                "descripcion"
            ],
            "Sin descripción registrada."
        );

    const imagen =
        normalizarRutaImagenRepuesto(

            obtenerValorRepuesto(
                repuesto,
                [
                    "Imagen_URL",
                    "imagen_url",
                    "Imagen",
                    "imagen"
                ],
                ""
            )

        );

    const stock =
        Number(

            obtenerValorRepuesto(
                repuesto,
                [
                    "Stock",
                    "stock"
                ],
                0
            )

        );

    const precio =
        Number(

            obtenerValorRepuesto(
                repuesto,
                [
                    "Precio",
                    "precio"
                ],
                0
            )

        );

    const estado =
        obtenerValorRepuesto(
            repuesto,
            [
                "Estado",
                "estado"
            ],
            stock > 0
                ? "Disponible"
                : "Agotado"
        );

    let claseStock =
        "disponible";

    if (stock <= 0) {

        claseStock =
            "agotado";

    } else if (stock <= 5) {

        claseStock =
            "stock-bajo";

    }

    return `

        <article class="repuesto-card-tecnico">

            <div class="repuesto-imagen-area">

                <img

                    src="${escaparHTMLRepuestos(imagen)}"

                    alt="${escaparHTMLRepuestos(nombre)}"

                    class="repuesto-imagen-tecnico"

                    loading="lazy"

                    onerror="
                        this.onerror=null;
                        this.src='img/Repuestos/repuesto-generico.jpg';
                    "

                >

            </div>

            <div class="repuesto-info-tecnico">

                <div class="repuesto-encabezado-tecnico">

                    <span class="repuesto-id-tecnico">

                        #${escaparHTMLRepuestos(id)}

                    </span>

                    <span class="repuesto-estado-tecnico ${claseStock}">

                        ${escaparHTMLRepuestos(estado)}

                    </span>

                </div>

                <h3 class="repuesto-nombre-tecnico">

                    ${escaparHTMLRepuestos(nombre)}

                </h3>

                <p class="repuesto-descripcion-tecnico">

                    ${escaparHTMLRepuestos(descripcion)}

                </p>

                <div class="repuesto-datos-tecnico">

                    <div class="repuesto-dato-tecnico">

                        <span>
                            Stock
                        </span>

                        <strong>

                            ${Number.isFinite(stock)
                                ? stock.toLocaleString("es-GT")
                                : "0"}

                        </strong>

                    </div>

                    <div class="repuesto-dato-tecnico">

                        <span>
                            Precio
                        </span>

                        <strong class="repuesto-precio-tecnico">

                            ${formatearPrecioRepuesto(precio)}

                        </strong>

                    </div>

                </div>

                <button

                    type="button"

                    class="btn-repuesto-detalle"

                    onclick="verDetalleRepuestoTecnico(${Number(id)})"

                >

                    Ver detalles

                </button>

            </div>

        </article>

    `;

}


/*==================================================
VER DETALLE
==================================================*/

function verDetalleRepuestoTecnico(
    idRepuesto
) {

    const id =
        Number(idRepuesto);

    const repuesto =
        repuestosTecnico.find(
            item => {

                const actual =
                    Number(

                        obtenerValorRepuesto(
                            item,
                            [
                                "Id_repuesto",
                                "id_repuesto",
                                "Id",
                                "id"
                            ],
                            0
                        )

                    );

                return actual === id;

            }
        );

    if (!repuesto) {

        alert(
            "No se encontró el repuesto."
        );

        return;

    }

    alert(

        `Repuesto #${id}\n\n` +

        `Nombre: ${obtenerValorRepuesto(repuesto,["Nombre","nombre"])}\n` +

        `Descripción: ${obtenerValorRepuesto(repuesto,["Descripcion","descripcion"])}\n` +

        `Stock: ${obtenerValorRepuesto(repuesto,["Stock","stock"])}\n` +

        `Precio: ${formatearPrecioRepuesto(
            obtenerValorRepuesto(repuesto,["Precio","precio"])
        )}`

    );

}
/*==================================================
BUSCADOR
==================================================*/

function iniciarBuscadorRepuestosTecnico() {

    const buscador =
        document.getElementById(
            "buscar-repuesto"
        );

    if (!buscador) {
        return;
    }

    buscador.addEventListener(
        "input",
        () => {

            const termino =
                normalizarTextoRepuesto(
                    buscador.value
                );

            if (termino === "") {

                renderRepuestosTecnico(
                    repuestosTecnico
                );

                return;

            }

            const filtrados =
                repuestosTecnico.filter(
                    repuesto => {

                        const contenido = [

                            obtenerValorRepuesto(
                                repuesto,
                                [
                                    "Id_repuesto",
                                    "id_repuesto"
                                ],
                                ""
                            ),

                            obtenerValorRepuesto(
                                repuesto,
                                [
                                    "Nombre",
                                    "nombre"
                                ],
                                ""
                            ),

                            obtenerValorRepuesto(
                                repuesto,
                                [
                                    "Descripcion",
                                    "descripcion"
                                ],
                                ""
                            ),

                            obtenerValorRepuesto(
                                repuesto,
                                [
                                    "Stock",
                                    "stock"
                                ],
                                ""
                            ),

                            obtenerValorRepuesto(
                                repuesto,
                                [
                                    "Precio",
                                    "precio"
                                ],
                                ""
                            ),

                            obtenerValorRepuesto(
                                repuesto,
                                [
                                    "Estado",
                                    "estado"
                                ],
                                ""
                            )

                        ].join(" ");

                        return normalizarTextoRepuesto(
                            contenido
                        ).includes(
                            termino
                        );

                    }
                );

            renderRepuestosTecnico(
                filtrados
            );

        }
    );

}


/*==================================================
OBTENER VALOR DEL REPUESTO
==================================================*/

function obtenerValorRepuesto(
    registro,
    campos,
    valorPredeterminado = ""
) {

    if (
        !registro ||
        typeof registro !== "object"
    ) {

        return valorPredeterminado;

    }

    for (
        const campo of campos
    ) {

        if (
            Object.prototype.hasOwnProperty.call(
                registro,
                campo
            ) &&
            registro[campo] !== null &&
            registro[campo] !== undefined &&
            String(registro[campo]).trim() !== ""
        ) {

            return registro[campo];

        }

    }

    return valorPredeterminado;

}


/*==================================================
NORMALIZAR RUTA DE IMAGEN
==================================================*/

function normalizarRutaImagenRepuesto(
    ruta
) {

    let valor =
        String(ruta ?? "").trim();

    valor =
        valor.replace(
            /^(\.\.\/)+/,
            ""
        );

    valor =
        valor.replace(
            /^(\.\/)+/,
            ""
        );

    valor =
        valor.replace(
            /^\/+/,
            ""
        );

    if (valor === "") {

        return "img/Repuestos/repuesto-generico.jpg";

    }

    return valor;

}


/*==================================================
FORMATEAR PRECIO
==================================================*/

function formatearPrecioRepuesto(
    precio
) {

    const numero =
        Number(precio);

    if (
        !Number.isFinite(numero)
    ) {

        return String(
            precio ?? ""
        );

    }

    return numero.toLocaleString(
        "es-GT",
        {
            style: "currency",
            currency: "GTQ",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );

}


/*==================================================
NORMALIZAR TEXTO
==================================================*/

function normalizarTextoRepuesto(
    valor
) {

    return String(valor ?? "")
        .toLowerCase()
        .normalize("NFD")
        .replace(
            /[\u0300-\u036f]/g,
            ""
        )
        .trim();

}


/*==================================================
ESCAPAR HTML
==================================================*/

function escaparHTMLRepuestos(
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
EXPONER FUNCIONES
==================================================*/

window.cargarRepuestosTecnico =
    cargarRepuestosTecnico;

window.renderRepuestosTecnico =
    renderRepuestosTecnico;

window.verDetalleRepuestoTecnico =
    verDetalleRepuestoTecnico;