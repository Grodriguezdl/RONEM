//==================================================// USUARIOS//==================================================

let usuarios = [];let usuarioEditando = null;

//==================================================// INICIAR//==================================================

document.addEventListener("DOMContentLoaded", async () => {

const buscador = document.getElementById("searchUsuario");

if (buscador) {
    buscador.addEventListener("input", () => {
        renderUsuarios(buscador.value);
    });
}

const modalUsuario =
    document.getElementById("modalUsuario");

if (modalUsuario) {
    modalUsuario.addEventListener(
        "hidden.bs.modal",
        limpiarUsuario
    );
}

await cargarRoles();
await cargarUsuarios();

});

//==================================================// CARGAR ROLES//==================================================

async function cargarRoles() {

const combo = document.getElementById("uRol");

if (!combo) {
    return;
}

combo.innerHTML =
    '<option value="">Cargando roles...</option>';

try {

    const respuesta = await fetch(
        "api/api_usuarios.php?action=roles",
        {
            method: "GET",
            cache: "no-store"
        }
    );

    const datos = await leerRespuestaUsuario(
        respuesta
    );

    if (!respuesta.ok || datos.success === false) {
        throw new Error(
            datos.message ||
            "No se pudieron cargar los roles."
        );
    }

    combo.innerHTML =
        '<option value="">Seleccionar rol</option>';

    const roles = Array.isArray(datos.data)
        ? datos.data
        : [];

    roles.forEach(rol => {

        combo.insertAdjacentHTML(
            "beforeend",
            `
                <option value="${Number(rol.Id_rol)}">
                    ${escaparHTMLUsuario(rol.Nombre)}
                </option>
            `
        );
    });

} catch (error) {

    console.error(
        "Error al cargar roles:",
        error
    );

    combo.innerHTML =
        '<option value="">Error al cargar roles</option>';
}

}

//==================================================// CARGAR USUARIOS//==================================================

async function cargarUsuarios() {

const tbody = document.getElementById(
    "tbodyUsuarios"
);

if (!tbody) {
    return;
}

tbody.innerHTML = `
    <tr class="empty-row">
        <td colspan="7">
            Cargando usuarios...
        </td>
    </tr>
`;

try {

    const respuesta = await fetch(
        "api/api_usuarios.php?action=listar",
        {
            method: "GET",
            cache: "no-store"
        }
    );

    const datos = await leerRespuestaUsuario(
        respuesta
    );

    if (!respuesta.ok || datos.success === false) {
        throw new Error(
            datos.message ||
            "No se pudieron cargar los usuarios."
        );
    }

    usuarios = Array.isArray(datos.data)
        ? datos.data
        : [];

    renderUsuarios();

} catch (error) {

    console.error(
        "Error al cargar usuarios:",
        error
    );

    tbody.innerHTML = `
        <tr class="empty-row">
            <td colspan="7" class="text-danger">
                ${escaparHTMLUsuario(error.message)}
            </td>
        </tr>
    `;
}

}

//==================================================// RENDERIZAR//==================================================

function renderUsuarios(filtro = "") {

const tbody = document.getElementById(
    "tbodyUsuarios"
);

if (!tbody) {
    return;
}

const termino = String(filtro)
    .trim()
    .toLowerCase();

const filtrados = usuarios.filter(usuario => {

    const contenido = `
        ${usuario.Id_usuario ?? ""}
        ${usuario.Nombre ?? ""}
        ${usuario.Apellido ?? ""}
        ${usuario.Correo ?? ""}
        ${usuario.Rol ?? ""}
    `.toLowerCase();

    return contenido.includes(termino);
});

if (filtrados.length === 0) {

    tbody.innerHTML = `
        <tr class="empty-row">
            <td colspan="7">
                No hay usuarios registrados
            </td>
        </tr>
    `;

    return;
}

tbody.innerHTML = filtrados.map(usuario => {

    const id = Number(usuario.Id_usuario);
    const activo = Number(usuario.Activo) === 1;

    return `
        <tr>
            <td>
                ${id}
            </td>

            <td>
                ${escaparHTMLUsuario(usuario.Nombre)}
            </td>

            <td>
                ${escaparHTMLUsuario(usuario.Apellido)}
            </td>

            <td>
                ${escaparHTMLUsuario(usuario.Correo)}
            </td>

            <td>
                ${escaparHTMLUsuario(
                    usuario.Rol || "Sin rol"
                )}
            </td>

            <td>
                <span class="badge ${
                    activo
                        ? "bg-success"
                        : "bg-danger"
                }">
                    ${
                        activo
                            ? "Activo"
                            : "Inactivo"
                    }
                </span>
            </td>

            <td>
                <button
                    type="button"
                    class="btn btn-warning btn-sm me-1"
                    onclick="editarUsuario(${id})"
                    title="Editar"
                >
                    <i class="bi bi-pencil"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="eliminarUsuario(${id})"
                    title="Eliminar"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
}).join("");

}

//==================================================// GUARDAR//==================================================

async function guardarUsuario() {

const formulario = document.getElementById(
    "formUsuario"
);

if (!formulario) {
    return;
}

if (!formulario.checkValidity()) {
    formulario.reportValidity();
    return;
}

const password =
    document.getElementById("uPassword").value;

if (
    usuarioEditando === null &&
    password.length < 8
) {
    alert(
        "La contraseña debe contener al menos 8 caracteres."
    );

    return;
}

if (
    usuarioEditando !== null &&
    password !== "" &&
    password.length < 8
) {
    alert(
        "La nueva contraseña debe contener al menos 8 caracteres."
    );

    return;
}

const datos = new FormData();

if (usuarioEditando !== null) {
    datos.append("id", usuarioEditando);
}

datos.append(
    "nombre",
    document.getElementById("uNombre").value.trim()
);

datos.append(
    "apellido",
    document.getElementById("uApellido").value.trim()
);

datos.append(
    "correo",
    document.getElementById("uEmail").value
        .trim()
        .toLowerCase()
);

datos.append(
    "password",
    password
);

datos.append(
    "rol",
    document.getElementById("uRol").value
);

datos.append(
    "estado",
    document.getElementById("uEstado").value
);

const boton = document.getElementById(
    "btnGuardarUsuario"
);

try {

    if (boton) {
        boton.disabled = true;
        boton.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span>
            Guardando...
        `;
    }

    const respuesta = await fetch(
        "api/api_usuarios.php?action=guardar",
        {
            method: "POST",
            body: datos
        }
    );

    const resultado =
        await leerRespuestaUsuario(respuesta);

    if (
        !respuesta.ok ||
        resultado.success === false
    ) {
        throw new Error(
            resultado.message ||
            "No se pudo guardar el usuario."
        );
    }

    bootstrap.Modal
        .getOrCreateInstance(
            document.getElementById("modalUsuario")
        )
        .hide();

    alert(resultado.message);

    limpiarUsuario();

    await cargarUsuarios();

} catch (error) {

    console.error(
        "Error al guardar usuario:",
        error
    );

    alert(error.message);

} finally {

    if (boton) {
        boton.disabled = false;
        boton.innerHTML = `
            <i class="bi bi-floppy me-2"></i>
            Guardar Usuario
        `;
    }
}

}

//==================================================// EDITAR//==================================================

function editarUsuario(id) {

const usuario = usuarios.find(
    elemento =>
        Number(elemento.Id_usuario) === Number(id)
);

if (!usuario) {
    alert("No se encontró el usuario.");
    return;
}

usuarioEditando = Number(id);

document.getElementById(
    "tituloModalUsuario"
).textContent = "Editar Usuario";

document.getElementById("uNombre").value =
    usuario.Nombre ?? "";

document.getElementById("uApellido").value =
    usuario.Apellido ?? "";

document.getElementById("uEmail").value =
    usuario.Correo ?? "";

document.getElementById("uRol").value =
    String(usuario.Id_rol ?? "");

document.getElementById("uEstado").value =
    String(Number(usuario.Activo));

const password =
    document.getElementById("uPassword");

password.value = "";
password.required = false;
password.placeholder =
    "Dejar vacío para conservar la contraseña";

bootstrap.Modal
    .getOrCreateInstance(
        document.getElementById("modalUsuario")
    )
    .show();

}

//==================================================// ELIMINAR//==================================================

async function eliminarUsuario(id) {

if (!confirm("¿Eliminar este usuario?")) {
    return;
}

const datos = new FormData();

datos.append("id", id);

try {

    const respuesta = await fetch(
        "api/api_usuarios.php?action=eliminar",
        {
            method: "POST",
            body: datos
        }
    );

    const resultado =
        await leerRespuestaUsuario(respuesta);

    if (
        !respuesta.ok ||
        resultado.success === false
    ) {
        throw new Error(
            resultado.message ||
            "No se pudo eliminar el usuario."
        );
    }

    alert(resultado.message);

    await cargarUsuarios();

} catch (error) {

    console.error(
        "Error al eliminar usuario:",
        error
    );

    alert(error.message);
}

}

//==================================================// LIMPIAR//==================================================

function limpiarUsuario() {

usuarioEditando = null;

const formulario =
    document.getElementById("formUsuario");

if (formulario) {
    formulario.reset();
}

document.getElementById(
    "tituloModalUsuario"
).textContent = "Agregar Nuevo Usuario";

const password =
    document.getElementById("uPassword");

if (password) {
    password.value = "";
    password.required = true;
    password.placeholder = "Mínimo 8 caracteres";
}

}

//==================================================// LEER JSON//==================================================

async function leerRespuestaUsuario(respuesta) {

const texto = await respuesta.text();

try {
    return JSON.parse(texto);
} catch (error) {

    console.error(
        "Respuesta recibida:",
        texto
    );

    throw new Error(
        "El servidor devolvió una respuesta no válida."
    );
}

}

//==================================================// ESCAPAR HTML//==================================================

function escaparHTMLUsuario(valor) {

return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

}