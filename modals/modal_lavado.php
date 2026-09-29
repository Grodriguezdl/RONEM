<?php
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../php/obtener_productos.php";

$idServicioModal = 1;

$sqlServicioModal = "
    SELECT
        Id_servicio,
        Nombre,
        Descripcion,
        Icono
    FROM Servicios
    WHERE Id_servicio = ?
      AND Estado = 1
    LIMIT 1
";

$stmtServicioModal = $conn->prepare($sqlServicioModal);
$servicioModal = null;
$productosModal = [];

if (!$stmtServicioModal) {
    error_log("Error al preparar el servicio del modal modalLavado: " . $conn->error);
} else {
    $stmtServicioModal->bind_param("i", $idServicioModal);

    if (!$stmtServicioModal->execute()) {
        error_log("Error al consultar el servicio del modal modalLavado: " . $stmtServicioModal->error);
    } else {
        $resultadoServicioModal = $stmtServicioModal->get_result();
        $servicioModal = $resultadoServicioModal->fetch_assoc();

        if ($servicioModal) {
            $productosModal = obtenerProductosServicio($conn, $idServicioModal);
        }
    }

    $stmtServicioModal->close();
}
?>

<div
    class="modal fade cat-modal"
    id="modalLavado"
    tabindex="-1"
    aria-labelledby="modalLavadoTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <?php if ($servicioModal): ?>
                <div class="cat-modal-header">
                    <div class="cat-modal-heading">
                        <span class="cat-icon" aria-hidden="true">
                            <i class="<?php echo htmlspecialchars($servicioModal["Icono"], ENT_QUOTES, "UTF-8"); ?>"></i>
                        </span>

                        <div>
                            <h2
                                class="cat-modal-title"
                                id="modalLavadoTitle"
                            >
                                <?php echo htmlspecialchars($servicioModal["Nombre"], ENT_QUOTES, "UTF-8"); ?>
                            </h2>

                            <p class="cat-modal-description">
                                <?php echo htmlspecialchars($servicioModal["Descripcion"], ENT_QUOTES, "UTF-8"); ?>
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="cat-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar catálogo"
                    >
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="cat-body">
                    <?php if (!empty($productosModal)): ?>
                        <div class="row g-3">
                            <?php foreach ($productosModal as $productoModal): ?>
                                <?php
                                $stockModal = (int) $productoModal["Stock"];
                                $claseStockModal = "";

                                if ($stockModal === 0) {
                                    $claseStockModal = "cat-stock-empty";
                                } elseif ($stockModal <= 5) {
                                    $claseStockModal = "cat-stock-low";
                                }
                                ?>

                                <div class="col-12 col-md-6">
                                    <article class="cat-product cat-reveal">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <h3 class="cat-product-name">
                                                <?php echo htmlspecialchars($productoModal["Nombre"], ENT_QUOTES, "UTF-8"); ?>
                                            </h3>

                                            <?php if ((int) $productoModal["Destacado"] === 1): ?>
                                                <span class="cat-badge">
                                                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                                                    Destacado
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="cat-product-description">
                                            <?php echo htmlspecialchars($productoModal["Descripcion"], ENT_QUOTES, "UTF-8"); ?>
                                        </p>

                                        <div class="cat-product-data">
                                            <span class="cat-price">
                                                <small>Precio</small>
                                                Q<?php echo htmlspecialchars(number_format((float) $productoModal["Precio"], 2), ENT_QUOTES, "UTF-8"); ?>
                                            </span>

                                            <span class="cat-stock <?php echo htmlspecialchars($claseStockModal, ENT_QUOTES, "UTF-8"); ?>">
                                                <i class="bi bi-box-seam" aria-hidden="true"></i>
                                                Stock:
                                                <?php echo htmlspecialchars((string) $stockModal, ENT_QUOTES, "UTF-8"); ?>
                                            </span>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="cat-modal-empty">
                            <div>
                                <i class="bi bi-box-seam" aria-hidden="true"></i>
                                <p class="mb-0">No hay productos disponibles.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="cat-modal-header">
                    <div>
                        <h2 class="cat-modal-title" id="modalLavadoTitle">
                            Servicio no disponible
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="cat-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar catálogo"
                    >
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="cat-body">
                    <div class="cat-modal-empty">
                        <div>
                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                            <p class="mb-0">No hay productos disponibles.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
unset(
    $idServicioModal,
    $sqlServicioModal,
    $stmtServicioModal,
    $servicioModal,
    $productosModal
);
?>
