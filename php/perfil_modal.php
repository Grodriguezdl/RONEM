<?php
if(!isset($_SESSION["id_usuario"])) return;

require_once __DIR__ . "/../config/conexion.php";

$id = $_SESSION["id_usuario"];

$sql = mysqli_prepare($conn,"
SELECT
    Nombre,
    Apellido,
    Correo
FROM Usuarios
WHERE Id_usuario = ?
LIMIT 1
");

mysqli_stmt_bind_param($sql,"i",$id);
mysqli_stmt_execute($sql);

$usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($sql));
?>

<div class="modal fade" id="perfilModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content perfil-modal">

            <!-- HEADER -->
            <div class="perfil-header">

                <button
                    type="button"
                    class="btn-close btn-close-white perfil-close"
                    data-bs-dismiss="modal">
                </button>

                <div class="perfil-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>

                <h2 class="perfil-title">
                    Mi Perfil
                </h2>

                <p class="perfil-subtitle">
                    Administra la información de tu cuenta.
                </p>

            </div>

            <!-- BODY -->
            <div class="perfil-body">

                <form action="php/actualizar_perfil.php" method="POST">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="perfil-label">
                                Nombre
                            </label>

                            <input
                                type="text"
                                class="form-control perfil-input"
                                name="nombre"
                                value="<?= htmlspecialchars($usuario["Nombre"]) ?>"
                                required>

                        </div>

                        <div class="col-md-6">

                            <label class="perfil-label">
                                Apellido
                            </label>

                            <input
                                type="text"
                                class="form-control perfil-input"
                                name="apellido"
                                value="<?= htmlspecialchars($usuario["Apellido"]) ?>"
                                required>

                        </div>

                    </div>

                    <div class="mt-3">

                        <label class="perfil-label">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            class="form-control perfil-input"
                            name="correo"
                            value="<?= htmlspecialchars($usuario["Correo"]) ?>"
                            required>

                    </div>

                    <div class="perfil-actions">

                        <button
                            type="button"
                            class="btn btn-perfil-secondary"
                            data-bs-dismiss="modal">

                            Cancelar

                        </button>

                        <button
                            type="submit"
                            class="btn btn-perfil-primary">

                            Guardar Cambios

                        </button>

                    </div>

                </form>

                <hr class="perfil-divider">

                <div class="perfil-footer">

                    <button
                        type="button"
                        class="btn btn-perfil-outline">

                        <i class="bi bi-key-fill me-2"></i>
                        Cambiar contraseña

                    </button>

                    <a
                        href="auth/logout.php"
                        class="btn btn-perfil-logout">

                        <i class="bi bi-box-arrow-right me-2"></i>
                        Cerrar sesión

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>