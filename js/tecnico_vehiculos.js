/*==================================================
MÓDULO DE VEHÍCULOS DEL PANEL TÉCNICO
==================================================*/

let vehiculosTecnico = [];


/*==================================================
INICIALIZAR MÓDULO
==================================================*/

document.addEventListener(
    "DOMContentLoaded",
    () => {

        cargarVehiculosTecnico();
        iniciarBuscadorVehiculosTecnico();

    }
);


/*==================================================
CARGAR VEHÍCULOS DESDE LA API
==================================================*/

async function cargarVehiculosTecnico() {

    const contenedor =
        document.getElementById(
            "container-vehiculos"
        );

    if (!contenedor) {
        return;
    }

    if (
        typeof window.mostrarCarga ===
        "function"
    ) {

        window.mostrarCarga(
            "container-vehiculos",
            "Cargando vehículos del taller..."
        );

    } else {

        contenedor.innerHTML = `
            <p class="text-muted">
                Cargando vehículos del taller...
            </p>
        `;

    }

    try {

        const respuesta = await fetch(
            "api/api_vehiculos_tecnico.php?action=listar",
            {
                method: "GET",
                cache: "no-store",
                headers: {
                    "Accept": "application/json"
                }
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(textoRespuesta);

        } catch (error) {

            console.error(
                "Respuesta recibida:",
                textoRespuesta
            );

            throw new Error(
                "La API de vehículos devolvió una respuesta inválida."
            );

        }

        if (!respuesta.ok) {

            throw new Error(
                resultado.mensaje ||
                "No se pudieron consultar los vehículos."
            );

        }

        if (!resultado.success) {

            throw new Error(
                resultado.mensaje ||
                "No se pudieron cargar los vehículos."
            );

        }

        vehiculosTecnico =
            Array.isArray(resultado.data)
                ? resultado.data
                : [];

        renderVehiculosTecnico(
            vehiculosTecnico
        );

    } catch (error) {

        console.error(
            "Error al cargar vehículos:",
            error
        );

        if (
            typeof window.mostrarError ===
            "function"
        ) {

            window.mostrarError(
                "container-vehiculos",
                error.message
            );

        } else {

            contenedor.innerHTML = `
                <div class="error-state">

                    <h2>
                        Ocurrió un error
                    </h2>

                    <p>
                        ${escaparHTMLVehiculos(
                            error.message
                        )}
                    </p>

                </div>
            `;

        }

    }

}


/*==================================================
RENDERIZAR VEHÍCULOS
==================================================*/

function renderVehiculosTecnico(
    vehiculos
) {

    const contenedor =
        document.getElementById(
            "container-vehiculos"
        );

    if (!contenedor) {
        return;
    }

    if (
        !Array.isArray(vehiculos) ||
        vehiculos.length === 0
    ) {

        if (
            typeof window.mostrarVacio ===
            "function"
        ) {

            window.mostrarVacio(
                "container-vehiculos",
                "No hay vehículos",
                "No existen vehículos relacionados con órdenes de servicio."
            );

        } else {

            contenedor.innerHTML = `
                <div class="empty-state">

                    <h2>
                        No hay vehículos
                    </h2>

                    <p>
                        No existen vehículos relacionados
                        con órdenes de servicio.
                    </p>

                </div>
            `;

        }

        return;

    }

    contenedor.innerHTML = `
        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Propietario
                        </th>

                        <th>
                            Vehículo
                        </th>

                        <th>
                            Tipo
                        </th>

                        <th>
                            Placa
                        </th>

                        <th>
                            Chasis
                        </th>

                        <th>
                            Kilometraje
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody>

                    ${vehiculos
                        .map(
                            vehiculo =>
                                crearFilaVehiculoTecnico(
                                    vehiculo
                                )
                        )
                        .join("")}

                </tbody>

            </table>

        </div>
    `;

}


/*==================================================
CREAR FILA DE VEHÍCULO
==================================================*/

function crearFilaVehiculoTecnico(
    vehiculo
) {

    const idVehiculo =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Id_vehiculo",
                "id_vehiculo"
            ],
            0
        );

    const idUsuario =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Id_usuario",
                "id_usuario"
            ],
            ""
        );

    const propietario =
        obtenerNombrePropietarioVehiculo(
            vehiculo
        );

    const marca =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Marca",
                "marca"
            ],
            "Sin marca"
        );

    const linea =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Linea",
                "linea"
            ],
            ""
        );

    const modelo =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Modelo",
                "modelo"
            ],
            ""
        );

    const tipo =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Tipo_vehiculo",
                "tipo_vehiculo"
            ],
            "No especificado"
        );

    const placa =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Placa",
                "placa"
            ],
            "Sin placa"
        );

    const chasis =
        obtenerValorVehiculo(
            vehiculo,
            [
                "No_chasis",
                "no_chasis"
            ],
            "No registrado"
        );

    const kilometraje =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Kilometraje",
                "kilometraje"
            ],
            null
        );

    const estado =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Estado",
                "estado"
            ],
            "No especificado"
        );

    const descripcionVehiculo = [
        marca,
        linea,
        modelo
    ]
        .filter(valor => {

            return (
                valor !== null &&
                valor !== undefined &&
                String(valor).trim() !== ""
            );

        })
        .join(" ");

    const kilometrajeTexto =
        formatearKilometrajeVehiculo(
            kilometraje
        );

    const propietarioTexto =
        propietario !== ""
            ? propietario
            : (
                idUsuario !== ""
                    ? `Usuario #${idUsuario}`
                    : "No registrado"
            );

    return `
        <tr
            data-id-vehiculo="${escaparHTMLVehiculos(
                idVehiculo
            )}"
        >

            <td>

                #${escaparHTMLVehiculos(
                    idVehiculo
                )}

            </td>

            <td>

                ${escaparHTMLVehiculos(
                    propietarioTexto
                )}

            </td>

            <td>

                <strong>

                    ${escaparHTMLVehiculos(
                        descripcionVehiculo ||
                        "Vehículo no especificado"
                    )}

                </strong>

            </td>

            <td>

                ${escaparHTMLVehiculos(
                    tipo
                )}

            </td>

            <td>

                <span class="vehicle-plate">

                    ${escaparHTMLVehiculos(
                        placa
                    )}

                </span>

            </td>

            <td>

                ${escaparHTMLVehiculos(
                    chasis
                )}

            </td>

            <td>

                ${escaparHTMLVehiculos(
                    kilometrajeTexto
                )}

            </td>

            <td>

                <span class="${obtenerClaseEstadoVehiculo(
                    estado
                )}">

                    ${escaparHTMLVehiculos(
                        estado
                    )}

                </span>

            </td>

            <td>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="verDetalleVehiculoTecnico(${Number(
                        idVehiculo
                    )})"
                >

                    Ver detalles

                </button>

            </td>

        </tr>
    `;

}


/*==================================================
VER DETALLE DEL VEHÍCULO
==================================================*/

async function verDetalleVehiculoTecnico(
    idVehiculo
) {

    idVehiculo =
        Number(idVehiculo);

    if (
        !Number.isInteger(idVehiculo) ||
        idVehiculo <= 0
    ) {

        alert(
            "El ID del vehículo no es válido."
        );

        return;

    }

    try {

        const respuesta = await fetch(
            "api/api_vehiculos_tecnico.php" +
            `?action=obtener&id=${encodeURIComponent(
                idVehiculo
            )}`,
            {
                method: "GET",
                cache: "no-store",
                headers: {
                    "Accept": "application/json"
                }
            }
        );

        const textoRespuesta =
            await respuesta.text();

        let resultado;

        try {

            resultado =
                JSON.parse(textoRespuesta);

        } catch (error) {

            console.error(
                "Respuesta recibida:",
                textoRespuesta
            );

            throw new Error(
                "La API devolvió una respuesta inválida."
            );

        }

        if (
            !respuesta.ok ||
            !resultado.success
        ) {

            throw new Error(
                resultado.mensaje ||
                "No se pudo consultar el vehículo."
            );

        }

        mostrarDetalleVehiculoTecnico(
            resultado.data
        );

    } catch (error) {

        console.error(
            "Error al consultar vehículo:",
            error
        );

        alert(error.message);

    }

}


/*==================================================
MOSTRAR DETALLE DEL VEHÍCULO
==================================================*/

function mostrarDetalleVehiculoTecnico(
    vehiculo
) {

    if (!vehiculo) {

        alert(
            "No se encontró información del vehículo."
        );

        return;

    }

    const propietario =
        obtenerNombrePropietarioVehiculo(
            vehiculo
        ) || "No registrado";

    const detalle = [
        [
            "ID del vehículo",
            obtenerValorVehiculo(
                vehiculo,
                ["Id_vehiculo"],
                "No registrado"
            )
        ],
        [
            "Propietario",
            propietario
        ],
        [
            "ID usuario",
            obtenerValorVehiculo(
                vehiculo,
                ["Id_usuario"],
                "No registrado"
            )
        ],
        [
            "Marca",
            obtenerValorVehiculo(
                vehiculo,
                ["Marca"],
                "No registrada"
            )
        ],
        [
            "Línea",
            obtenerValorVehiculo(
                vehiculo,
                ["Linea"],
                "No registrada"
            )
        ],
        [
            "Modelo",
            obtenerValorVehiculo(
                vehiculo,
                ["Modelo"],
                "No registrado"
            )
        ],
        [
            "Color",
            obtenerValorVehiculo(
                vehiculo,
                ["Color"],
                "No registrado"
            )
        ],
        [
            "Placa",
            obtenerValorVehiculo(
                vehiculo,
                ["Placa"],
                "No registrada"
            )
        ],
        [
            "No. de chasis",
            obtenerValorVehiculo(
                vehiculo,
                ["No_chasis"],
                "No registrado"
            )
        ],
        [
            "Tipo de vehículo",
            obtenerValorVehiculo(
                vehiculo,
                ["Tipo_vehiculo"],
                "No especificado"
            )
        ],
        [
            "Cilindraje",
            formatearCilindrajeVehiculo(
                obtenerValorVehiculo(
                    vehiculo,
                    ["Cilindraje"],
                    null
                )
            )
        ],
        [
            "Combustible",
            obtenerValorVehiculo(
                vehiculo,
                ["Combustible"],
                "No especificado"
            )
        ],
        [
            "Kilometraje",
            formatearKilometrajeVehiculo(
                obtenerValorVehiculo(
                    vehiculo,
                    ["Kilometraje"],
                    null
                )
            )
        ],
        [
            "Estado",
            obtenerValorVehiculo(
                vehiculo,
                ["Estado"],
                "No especificado"
            )
        ]
    ];

    const contenido =
        detalle
            .map(([etiqueta, valor]) => {

                return (
                    `${etiqueta}: ${valor}`
                );

            })
            .join("\n");

    alert(contenido);

}


/*==================================================
BUSCADOR DE VEHÍCULOS
==================================================*/

function iniciarBuscadorVehiculosTecnico() {

    const buscador =
        document.getElementById(
            "buscar-vehiculo"
        );

    if (!buscador) {
        return;
    }

    /*
     * El tecnico.js actual ya puede tener
     * un evento de búsqueda. Este listener
     * vuelve a renderizar desde el arreglo
     * original para mantener los datos.
     */

    buscador.addEventListener(
        "input",
        () => {

            filtrarVehiculosTecnico(
                buscador.value
            );

        }
    );

}


/*==================================================
FILTRAR VEHÍCULOS
==================================================*/

function filtrarVehiculosTecnico(
    terminoBusqueda
) {

    const termino =
        normalizarTextoVehiculos(
            terminoBusqueda
        );

    if (termino === "") {

        renderVehiculosTecnico(
            vehiculosTecnico
        );

        return;

    }

    const filtrados =
        vehiculosTecnico.filter(
            vehiculo => {

                const campos = [
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Id_vehiculo"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Id_usuario"],
                        ""
                    ),
                    obtenerNombrePropietarioVehiculo(
                        vehiculo
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Marca"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Linea"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Modelo"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Color"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Placa"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["No_chasis"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Tipo_vehiculo"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Combustible"],
                        ""
                    ),
                    obtenerValorVehiculo(
                        vehiculo,
                        ["Estado"],
                        ""
                    )
                ];

                const contenido =
                    normalizarTextoVehiculos(
                        campos.join(" ")
                    );

                return contenido.includes(
                    termino
                );

            }
        );

    renderVehiculosTecnico(
        filtrados
    );

}


/*==================================================
OBTENER NOMBRE DEL PROPIETARIO
==================================================*/

function obtenerNombrePropietarioVehiculo(
    vehiculo
) {

    const nombreCompleto =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Usuario",
                "Propietario",
                "Nombre_usuario",
                "Nombre_completo"
            ],
            ""
        );

    if (
        String(nombreCompleto).trim() !==
        ""
    ) {

        return String(
            nombreCompleto
        ).trim();

    }

    const nombre =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Nombre",
                "nombre"
            ],
            ""
        );

    const apellido =
        obtenerValorVehiculo(
            vehiculo,
            [
                "Apellido",
                "apellido"
            ],
            ""
        );

    return `${nombre} ${apellido}`.trim();

}


/*==================================================
OBTENER VALOR DE UN CAMPO
==================================================*/

function obtenerValorVehiculo(
    registro,
    posiblesCampos,
    valorPredeterminado = ""
) {

    if (
        !registro ||
        typeof registro !== "object"
    ) {

        return valorPredeterminado;

    }

    for (
        const campo of posiblesCampos
    ) {

        if (
            Object.prototype.hasOwnProperty.call(
                registro,
                campo
            ) &&
            registro[campo] !== null &&
            registro[campo] !== undefined &&
            String(registro[campo]).trim() !==
            ""
        ) {

            return registro[campo];

        }

    }

    return valorPredeterminado;

}


/*==================================================
FORMATEAR KILOMETRAJE
==================================================*/

function formatearKilometrajeVehiculo(
    kilometraje
) {

    if (
        kilometraje === null ||
        kilometraje === undefined ||
        String(kilometraje).trim() ===
        ""
    ) {

        return "No registrado";

    }

    const numero =
        Number(kilometraje);

    if (!Number.isFinite(numero)) {

        return String(kilometraje);

    }

    return (
        numero.toLocaleString(
            "es-GT",
            {
                maximumFractionDigits: 0
            }
        ) +
        " km"
    );

}


/*==================================================
FORMATEAR CILINDRAJE
==================================================*/

function formatearCilindrajeVehiculo(
    cilindraje
) {

    if (
        cilindraje === null ||
        cilindraje === undefined ||
        String(cilindraje).trim() ===
        ""
    ) {

        return "No registrado";

    }

    const numero =
        Number(cilindraje);

    if (!Number.isFinite(numero)) {

        return String(cilindraje);

    }

    return (
        numero.toLocaleString(
            "es-GT",
            {
                maximumFractionDigits: 0
            }
        ) +
        " cc"
    );

}


/*==================================================
CLASE VISUAL DEL ESTADO
==================================================*/

function obtenerClaseEstadoVehiculo(
    estado
) {

    const estadoNormalizado =
        normalizarTextoVehiculos(
            estado
        );

    if (
        estadoNormalizado === "activo" ||
        estadoNormalizado === "disponible"
    ) {

        return "badge badge-success";

    }

    if (
        estadoNormalizado.includes(
            "mantenimiento"
        ) ||
        estadoNormalizado.includes(
            "taller"
        ) ||
        estadoNormalizado.includes(
            "reparacion"
        )
    ) {

        return "badge badge-repair";

    }

    if (
        estadoNormalizado === "inactivo" ||
        estadoNormalizado.includes(
            "fuera"
        )
    ) {

        return "badge badge-danger";

    }

    return "badge badge-secondary";

}


/*==================================================
NORMALIZAR TEXTO
==================================================*/

function normalizarTextoVehiculos(
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

function escaparHTMLVehiculos(
    valor
) {

    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

}


/*==================================================
EXPONER FUNCIONES NECESARIAS
==================================================*/

window.cargarVehiculosTecnico =
    cargarVehiculosTecnico;

window.renderVehiculosTecnico =
    renderVehiculosTecnico;

window.verDetalleVehiculoTecnico =
    verDetalleVehiculoTecnico;