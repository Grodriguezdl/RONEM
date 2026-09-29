<?php
session_start();

// Obtenemos el nombre y apellido guardados por login.php
$nombreUsuario = trim((string)($_SESSION["nombre"] ?? ""));
$apellidoUsuario = trim((string)($_SESSION["apellido"] ?? ""));
$nombreCompletoLogueado = trim($nombreUsuario . " " . $apellidoUsuario);

// Si por alguna razón no hay nombre/apellido pero sí un usuario en sesión
if ($nombreCompletoLogueado === "" && isset($_SESSION["usuario"])) {
    $nombreCompletoLogueado = (string)$_SESSION["usuario"];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RONEM – Ventas e Inventario</title>

  <!-- Favicon y Fuentes -->
  <link rel="icon" type="image/png" href="favicoB.png">
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Librería para Generar PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <!-- Hojas de Estilos -->
  <link rel="stylesheet" href="css/stylesventas.css?v=1.2">

  <style>
    /* Estilos para el Modal de Factura */
    .modal-factura {
      display: none;
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 10000;
      justify-content: center;
      align-items: center;
    }
    .factura-box {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 320px;
      font-family: monospace;
      color: #333;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .factura-header { text-align: center; border-bottom: 1px dashed #aaa; padding-bottom: 10px; margin-bottom: 10px; }
    .factura-header h3 { margin: 0; font-size: 1.3rem; }
    .factura-info { font-size: 0.85rem; margin-bottom: 10px; border-bottom: 1px dashed #aaa; padding-bottom: 10px; }
    .factura-tabla { width: 100%; font-size: 0.85rem; border-collapse: collapse; margin-bottom: 10px; }
    .factura-tabla th { text-align: left; border-bottom: 1px solid #ddd; }
    .factura-tabla td { padding: 3px 0; }
    .factura-total { text-align: right; font-weight: bold; font-size: 1rem; border-top: 1px dashed #aaa; padding-top: 8px; margin-top: 5px; }
    .factura-acciones { display: flex; gap: 10px; margin-top: 15px; }
    .factura-acciones button { flex: 1; padding: 8px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .btn-factura-pdf { background: #dc3545; color: white; }
    .btn-factura-close { background: #6c757d; color: white; }
  </style>
</head>

<body>

  <!-- NAVEGACIÓN PRINCIPAL -->
  <nav class="navbar-ronem">
    <div class="nav-brand">
      <div class="nav-logo-box">
        <img src="favicoB.png" alt="RONEM" onerror="this.style.visibility='hidden'">
      </div>
      <div class="nav-wordmark">
        <span class="nw-main">RONEM</span>
        <span class="nw-sub">RONEMMA INC</span>
      </div>
    </div>

    <div class="nav-links">
      <a href="index.php" class="nav-link-item" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
        Inicio
      </a>
      <button type="button" class="nav-link-item active" id="btnVentas" onclick="irASeccion('apartado-ventas', this)">
        Ventas
      </button>
      <button type="button" class="nav-link-item" id="btnInventario" onclick="irASeccion('apartado-inventario', this)">
        Inventario
      </button>
    </div>
  </nav>

  <!-- SECCIÓN DE VENTAS Y CARRITO -->
  <main class="apartado" id="apartado-ventas">
    <div class="page-wrapper">

      <!-- Formulario de Selección de Producto -->
      <section class="card-ronem">
        <div class="card-title-ronem">Detalle de Venta Actual</div>

        <div class="form-row">
          <div class="form-group" style="flex: 2;">
            <label for="prodNombre">Nombre del producto</label>
            <input type="text" id="prodNombre" class="form-control" placeholder="Ej. Casco Integral" list="listaProductosVenta">
            <datalist id="listaProductosVenta"></datalist>
          </div>

          <div class="form-group">
            <label for="prodCodigo">Código</label>
            <input type="text" id="prodCodigo" class="form-control" placeholder="RNM-001" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="prodCategoria">Categoría</label>
            <input type="text" id="prodCategoria" class="form-control" readonly>
          </div>

          <div class="form-group">
            <label for="prodPrecio">Precio (Q)</label>
            <input type="number" id="prodPrecio" class="form-control" value="0.00" step="0.01" readonly>
          </div>

          <div class="form-group">
            <label for="prodStock">Stock</label>
            <input type="number" id="prodStock" class="form-control" value="0" readonly>
          </div>

          <div class="form-group">
            <label for="prodCantidad">Cantidad a vender</label>
            <input type="number" id="prodCantidad" class="form-control" value="1" min="1">
          </div>
        </div>

        <button type="button" class="btn-ronem btn-primary" onclick="agregarAlCarrito()">
          <i class="bi bi-plus-lg"></i> Añadir a la lista
        </button>

        <!-- Tabla de Carrito -->
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody id="carritoBody">
              <tr class="empty-row">
                <td colspan="5">No hay productos en el carrito</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Barra de Total -->
      <div class="total-bar">
        <span class="total-label">
          <i class="bi bi-cart3"></i> Total en carrito
        </span>
        <span class="total-amount" id="granTotal">Q0.00</span>
      </div>

      <!-- Registro de la Venta -->
      <section class="card-ronem">
        <div class="card-title-ronem">Registrar Venta</div>

        <div class="form-row">
          <div class="form-group">
            <label for="clienteVenta">Cliente</label>
            <input type="text" id="clienteVenta" class="form-control" placeholder="Nombre del cliente" value="<?= htmlspecialchars($nombreCompletoLogueado, ENT_QUOTES, 'UTF-8') ?>">
          </div>

          <div class="form-group">
            <label for="metodoPagoVenta">Método de Pago</label>
            <select id="metodoPagoVenta" class="form-control">
              <option value="">Seleccionar pago</option>
              <option value="Efectivo" selected>Efectivo</option>
              <option value="Tarjeta de Crédito/Débito">Tarjeta de Crédito/Débito</option>
              <option value="Transferencia Bancaria">Transferencia Bancaria</option>
            </select>
          </div>

          <div class="form-group">
            <label for="estadoVenta">Estado</label>
            <select id="estadoVenta" class="form-control">
              <option value="Pendiente">Pendiente</option>
              <option value="Completado" selected>Completado</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="cajeroVenta">Cajero</label>
            <select id="cajeroVenta" class="form-control">
              <option value="">Seleccionar cajero</option>
              <option value="Gabriel">Gabriel</option>
              <option value="Andrea">Andrea</option>
              <option value="Gerson">Gerson</option>
              <option value="Cristel">Cristel</option>
              <option value="Melvin">Melvin</option>
              <option value="Emanuel">Emanuel</option>
            </select>
          </div>

          <div class="form-group">
            <label for="fechaVenta">Fecha</label>
            <input type="date" id="fechaVenta" class="form-control">
          </div>
        </div>

        <button type="button" class="btn-ronem btn-confirm" onclick="registrarVenta()">
          <i class="bi bi-check2-circle"></i> Registrar Venta
        </button>
      </section>

    </div>
  </main>

  <!-- SECCIÓN DE INVENTARIO -->
  <main class="apartado" id="apartado-inventario">
    <div class="page-wrapper">

      <section class="card-ronem">
        <div class="card-header">
          <div class="card-title-ronem">Inventario de Productos</div>
          <button type="button" class="btn-ronem btn-primary" onclick="cargarProductos()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
          </button>
        </div>

        <div class="tbl-wrap">
          <table id="inventario-table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="inventarioBody">
              <tr class="empty-row">
                <td colspan="6">Cargando inventario...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </div>
  </main>

  <!-- MODAL DE FACTURA -->
  <div class="modal-factura" id="modalFactura">
    <div>
      <div class="factura-box" id="facturaDocumento">
        <div class="factura-header">
          <h3>RONEM INC</h3>
          <small>Comprobante de Venta</small>
        </div>
        <div class="factura-info">
          <div><strong>Fecha:</strong> <span id="factFecha"></span></div>
          <div><strong>Cliente:</strong> <span id="factCliente"></span></div>
          <div><strong>Cajero:</strong> <span id="factCajero"></span></div>
          <div><strong>Pago:</strong> <span id="factPago"></span></div>
          <div><strong>Estado:</strong> <span id="factEstado"></span></div>
        </div>
        <table class="factura-tabla">
          <thead>
            <tr>
              <th>Cant.</th>
              <th>Producto</th>
              <th style="text-align: right;">Total</th>
            </tr>
          </thead>
          <tbody id="factBody">
          </tbody>
        </table>
        <div class="factura-total">
          TOTAL: <span id="factTotal"></span>
        </div>
      </div>

      <div class="factura-acciones">
        <button class="btn-factura-pdf" onclick="descargarPDF()"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</button>
        <button class="btn-factura-close" onclick="cerrarFactura()">Cerrar</button>
      </div>
    </div>
  </div>

  <!-- LÓGICA JAVASCRIPT -->
  <script>
    const usuarioLogueadoOriginal = "<?= htmlspecialchars($nombreCompletoLogueado, ENT_QUOTES, 'UTF-8') ?>";
    let productos = [];
    let carrito = [];

    document.addEventListener("DOMContentLoaded", function () {
      const fechaVenta = document.getElementById("fechaVenta");
      if (fechaVenta) {
        fechaVenta.valueAsDate = new Date();
      }

      cargarProductos();

      const inputProducto = document.getElementById("prodNombre");
      if (inputProducto) {
        inputProducto.addEventListener("change", seleccionarProducto);
      }

      actualizarBotonActivo();
    });

    function irASeccion(idSeccion, boton) {
      const seccion = document.getElementById(idSeccion);
      if (!seccion) return;

      document.querySelectorAll(".nav-link-item").forEach(elemento => {
        elemento.classList.remove("active");
      });

      if (boton) {
        boton.classList.add("active");
      }

      seccion.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    window.addEventListener("scroll", actualizarBotonActivo);

    function actualizarBotonActivo() {
      const inventario = document.getElementById("apartado-inventario");
      const btnVentas = document.getElementById("btnVentas");
      const btnInventario = document.getElementById("btnInventario");

      if (!inventario || !btnVentas || !btnInventario) return;

      const posicionInventario = inventario.getBoundingClientRect().top;

      btnVentas.classList.remove("active");
      btnInventario.classList.remove("active");

      if (posicionInventario <= 120) {
        btnInventario.classList.add("active");
      } else {
        btnVentas.classList.add("active");
      }
    }

    async function cargarProductos() {
      const tbody = document.getElementById("inventarioBody");
      if (tbody) {
        tbody.innerHTML = `
          <tr class="empty-row">
            <td colspan="6">Cargando inventario...</td>
          </tr>
        `;
      }

      try {
        const respuesta = await fetch("api/api_productos.php?action=listar");
        const datos = await respuesta.json();

        if (!respuesta.ok || datos.success === false || !Array.isArray(datos)) {
          throw new Error(datos.error || datos.message || "No se pudieron cargar los productos.");
        }

        productos = datos;
        renderInventario();
        llenarListaProductos();

      } catch (error) {
        console.error("Error al cargar productos:", error);
        if (tbody) {
          tbody.innerHTML = `
            <tr class="empty-row">
              <td colspan="6">No se pudo cargar el inventario</td>
            </tr>
          `;
        }
      }
    }

    function llenarListaProductos() {
      const lista = document.getElementById("listaProductosVenta");
      if (!lista) return;

      lista.innerHTML = productos.map(producto => `
        <option value="${escaparHTML(producto.Nombre)}">
      `).join("");
    }

    function seleccionarProducto() {
      const nombre = document.getElementById("prodNombre").value.trim();
      const producto = productos.find(item => item.Nombre === nombre);

      if (!producto) {
        limpiarDatosProducto();
        return;
      }

      document.getElementById("prodCodigo").value = producto.Id_producto;
      document.getElementById("prodCategoria").value = producto.Categoria || "";
      document.getElementById("prodPrecio").value = Number(producto.Precio).toFixed(2);
      document.getElementById("prodStock").value = Number(producto.Stock);
    }

    function renderInventario() {
      const tbody = document.getElementById("inventarioBody");
      if (!tbody) return;

      if (productos.length === 0) {
        tbody.innerHTML = `
          <tr class="empty-row">
            <td colspan="6">No hay productos registrados</td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = productos.map(producto => {
        const id = Number(producto.Id_producto);
        const stock = Number(producto.Stock || 0);
        const estado = Number(producto.Estado || 0);

        return `
          <tr>
            <td class="td-code">RNM-${String(id).padStart(3, "0")}</td>
            <td>
              <div class="td-product">
                <div>
                  <div class="prod-name">${escaparHTML(producto.Nombre)}</div>
                  <div class="prod-ref">${escaparHTML(producto.Descripcion || "")}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge badge-motor">${escaparHTML(producto.Categoria || "Sin categoría")}</span>
            </td>
            <td class="td-price">${formatearMoneda(producto.Precio)}</td>
            <td>
              <span class="stock-num ${claseStock(stock)}">${stock}</span>
            </td>
            <td>${estadoProducto(estado, stock)}</td>
          </tr>
        `;
      }).join("");
    }

    function agregarAlCarrito() {
      const idProducto = Number(document.getElementById("prodCodigo").value);
      const producto = productos.find(item => Number(item.Id_producto) === idProducto);
      const cantidad = Number(document.getElementById("prodCantidad").value);

      if (!producto || cantidad <= 0) {
        alert("Selecciona un producto y una cantidad válida.");
        return;
      }

      const stock = Number(producto.Stock);
      if (cantidad > stock) {
        alert("La cantidad supera el stock disponible.");
        return;
      }

      const existente = carrito.find(item => item.idProducto === idProducto);

      if (existente) {
        const cantidadTotal = existente.cantidad + cantidad;
        if (cantidadTotal > stock) {
          alert("La cantidad total supera el stock disponible.");
          return;
        }
        existente.cantidad = cantidadTotal;
        existente.subtotal = existente.precio * existente.cantidad;
      } else {
        carrito.push({
          idProducto,
          nombre: producto.Nombre,
          cantidad,
          precio: Number(producto.Precio),
          subtotal: Number(producto.Precio) * cantidad
        });
      }

      renderCarrito();
      limpiarFormularioProducto();
    }

    function renderCarrito() {
      const tbody = document.getElementById("carritoBody");
      const totalElemento = document.getElementById("granTotal");

      if (!tbody || !totalElemento) return;

      if (carrito.length === 0) {
        tbody.innerHTML = `
          <tr class="empty-row">
            <td colspan="5">No hay productos en el carrito</td>
          </tr>
        `;
        totalElemento.textContent = "Q0.00";
        return;
      }

      let total = 0;
      tbody.innerHTML = carrito.map((item, indice) => {
        total += item.subtotal;

        return `
          <tr>
            <td>${escaparHTML(item.nombre)}</td>
            <td>${item.cantidad}</td>
            <td>${formatearMoneda(item.precio)}</td>
            <td>${formatearMoneda(item.subtotal)}</td>
            <td>
              <button type="button" class="btn-eliminar" onclick="eliminarDelCarrito(${indice})" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
              <button type="button" class="btn-editar" onclick="editarDelCarrito(${indice})" title="Editar cantidad" style="background: #e9ecef; border: 1px solid #ced4da; color: #495057; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 5px;">
                <i class="bi bi-pencil"></i>
              </button>
            </td>
          </tr>
        `;
      }).join("");

      totalElemento.textContent = formatearMoneda(total);
    }

    function eliminarDelCarrito(indice) {
      carrito.splice(indice, 1);
      renderCarrito();
    }

    function editarDelCarrito(indice) {
      const item = carrito[indice];
      const producto = productos.find(p => Number(p.Id_producto) === Number(item.idProducto));
      
      const stockDisponible = producto ? Number(producto.Stock) : item.cantidad;

      const nuevaCantidadStr = prompt(`Ingrese la nueva cantidad para "${item.nombre}":`, item.cantidad);
      
      if (nuevaCantidadStr === null) return;

      const nuevaCantidad = Number(nuevaCantidadStr);

      if (isNaN(nuevaCantidad) || nuevaCantidad <= 0) {
        alert("Por favor, ingrese una cantidad válida mayor a 0.");
        return;
      }

      if (nuevaCantidad > stockDisponible) {
        alert(`La cantidad solicitada (${nuevaCantidad}) supera el stock disponible (${stockDisponible}).`);
        return;
      }

      item.cantidad = nuevaCantidad;
      item.subtotal = item.precio * nuevaCantidad;

      renderCarrito();
    }

    async function registrarVenta() {
      if (carrito.length === 0) {
        alert("Agrega al menos un producto al carrito.");
        return;
      }

      const cliente = document.getElementById("clienteVenta").value.trim();
      const metodoPago = document.getElementById("metodoPagoVenta").value;
      const cajero = document.getElementById("cajeroVenta").value;
      const estado = document.getElementById("estadoVenta").value;
      const fecha = document.getElementById("fechaVenta").value;

      if (!cliente) {
        alert("Por favor, escribe el nombre del cliente.");
        return;
      }

      if (!metodoPago) {
        alert("Por favor, selecciona un método de pago.");
        return;
      }

      if (!cajero) {
        alert("Por favor, selecciona un cajero.");
        return;
      }

      // 1. Descontar stock localmente
      carrito.forEach(itemCarrito => {
        const prod = productos.find(p => Number(p.Id_producto) === Number(itemCarrito.idProducto));
        if (prod) {
          prod.Stock = Math.max(0, Number(prod.Stock) - itemCarrito.cantidad);
        }
      });

      renderInventario();

      // 2. Intentar guardar en backend
      try {
        await fetch("api/api_ventas.php?action=registrar", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            cliente,
            metodoPago,
            cajero,
            estado,
            fecha,
            detalles: carrito
          })
        });
      } catch (err) {
        console.warn("Nota: No se pudo conectar a api_ventas.php, pero la simulación local funcionó.", err);
      }

      // 3. Cargar datos en la Factura
      document.getElementById("factCliente").textContent = cliente;
      document.getElementById("factCajero").textContent = cajero;
      document.getElementById("factPago").textContent = metodoPago;
      document.getElementById("factEstado").textContent = estado;
      document.getElementById("factFecha").textContent = fecha;

      let total = 0;
      const factBody = document.getElementById("factBody");
      factBody.innerHTML = carrito.map(item => {
        total += item.subtotal;
        return `
          <tr>
            <td>${item.cantidad}</td>
            <td>${escaparHTML(item.nombre)}</td>
            <td style="text-align: right;">${formatearMoneda(item.subtotal)}</td>
          </tr>
        `;
      }).join("");

      document.getElementById("factTotal").textContent = formatearMoneda(total);

      // Mostrar Modal
      document.getElementById("modalFactura").style.display = "flex";
    }

    function descargarPDF() {
      const elemento = document.getElementById("facturaDocumento");
      const cliente = document.getElementById("factCliente").textContent || "Venta";

      const opciones = {
        margin:       [5, 5, 5, 5],
        filename:     `Factura_${cliente}_RONEM.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
          scale: 2, 
          logging: false,
          useCORS: true 
        },
        jsPDF:        { unit: 'mm', format: 'a6', orientation: 'portrait' }
      };

      const contenedorTemporal = document.createElement('div');
      contenedorTemporal.style.width = '300px';
      contenedorTemporal.style.padding = '10px';
      contenedorTemporal.style.background = '#ffffff';
      contenedorTemporal.style.color = '#000000';
      contenedorTemporal.innerHTML = elemento.innerHTML;

      html2pdf().set(opciones).from(contenedorTemporal).save();
    }

    function cerrarFactura() {
      document.getElementById("modalFactura").style.display = "none";
      carrito = [];
      renderCarrito();
      document.getElementById("clienteVenta").value = usuarioLogueadoOriginal;
      document.getElementById("cajeroVenta").value = "";
    }

    function limpiarFormularioProducto() {
      document.getElementById("prodNombre").value = "";
      limpiarDatosProducto();
      document.getElementById("prodCantidad").value = "1";
    }

    function limpiarDatosProducto() {
      document.getElementById("prodCodigo").value = "";
      document.getElementById("prodCategoria").value = "";
      document.getElementById("prodPrecio").value = "0.00";
      document.getElementById("prodStock").value = "0";
    }

    function claseStock(stock) {
      if (stock <= 0) return "stock-out";
      if (stock <= 10) return "stock-low";
      return "stock-ok";
    }

    function estadoProducto(estado, stock) {
      if (estado !== 1) {
        return `<span class="estado-dot estado-agotado">Inactivo</span>`;
      }
      if (stock <= 0) {
        return `<span class="estado-dot estado-agotado">Agotado</span>`;
      }
      if (stock <= 10) {
        return `<span class="estado-dot estado-bajo">Stock bajo</span>`;
      }
      return `<span class="estado-dot estado-activo">Activo</span>`;
    }

    function formatearMoneda(valor) {
      return Number(valor).toLocaleString("es-GT", {
        style: "currency",
        currency: "GTQ",
        minimumFractionDigits: 2
      });
    }

    function escaparHTML(valor) {
      return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }
  </script>

</body>
</html>