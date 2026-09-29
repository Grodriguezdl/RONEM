document.addEventListener("DOMContentLoaded", () => {

    const formulario = document.getElementById("formSolicitud");

    formulario.addEventListener("submit", async (e) => {

        e.preventDefault();

        const formData = new FormData();

        formData.append("nombre", document.getElementById("nombre").value);
        formData.append("apellido", document.getElementById("apellido").value);
        formData.append("dpi", document.getElementById("dpi").value);
        formData.append("fecha_nacimiento", document.getElementById("fecha_nacimiento").value);
        formData.append("edad", document.getElementById("edad").value);
        formData.append("genero", document.getElementById("genero").value);
        formData.append("telefono", document.getElementById("telefono").value);
        formData.append("correo", document.getElementById("correo").value);
        formData.append("direccion", document.getElementById("direccion").value);

        formData.append(
            "id_nivel",
            document.getElementById("id_nivel").value
        );

        formData.append(
            "id_tipo_licencia",
            document.getElementById("id_tipo_licencia").value
        );

        formData.append(
            "nombre_emergencia",
            document.getElementById("nombre_emergencia").value
        );

        formData.append(
            "contacto_emergencia",
            document.getElementById("contacto_emergencia").value
        );

        formData.append(
            "condiciones_medicas",
            document.getElementById("condiciones_medicas").value
        );

        formData.append(
            "acepta_terminos",
            document.getElementById("terminos").checked ? 1 : 0
        );

        console.log(
            "Nivel:",
            document.getElementById("id_nivel").value
        );

        console.log(
            "Licencia:",
            document.getElementById("id_tipo_licencia").value
        );

        try {

            const respuesta = await fetch("php/guardar_solicitud.php", {
                method: "POST",
                body: formData
            });

            const texto = await respuesta.text();

            console.log(texto);

            const data = JSON.parse(texto);

            document.getElementById("mensajeSolicitud").textContent =
                data.message;

            const modalSolicitud = bootstrap.Modal.getOrCreateInstance(
                document.getElementById("modalSolicitud")
            );

            modalSolicitud.show();

            if (data.success) {
                formulario.reset();
            }

        } catch (error) {

            console.error(error);

            document.getElementById("mensajeSolicitud").textContent =
                "Error en el servidor.";

            const modalSolicitud = bootstrap.Modal.getOrCreateInstance(
                document.getElementById("modalSolicitud")
            );

            modalSolicitud.show();
        }

    });

});