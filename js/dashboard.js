// ==================================================// RONEM - DASHBOARD ADMINISTRATIVO// ==================================================

let graficaVentasDashboard = null;

document.addEventListener("DOMContentLoaded", () => {cargarDashboard();});

// ==================================================// CARGAR TODO EL DASHBOARD// ==================================================

async function cargarDashboard() {await Promise.all([cargarResumenDashboard(),cargarGraficaVentasDashboard(),cargarProductosBajosDashboard()]);}

// ==================================================// TARJETAS Y RESUMEN MENSUAL// ==================================================

async function cargarResumenDashboard() {try {const respuesta = await fetch("../../api/api_dashboard.php?action=resumen",{method: "GET",headers: {Accept: "application/json"},cache: "no-store"});

const datos = await leerRespuestaJSON(respuesta);

if (!respuesta.ok || datos.success === false) {
  throw new Error(
    datos.error || "No se pudo cargar el resumen del dashboard."
  );
}

const tarjetas = datos.tarjetas || {};
const resumen = datos.resumen_mensual || {};

colocarTexto(
  "dashVentasHoy",
  formatearNumero(tarjetas.ventas_hoy)
);

colocarTexto(
  "dashTotalVentasHoy",
  formatearMoneda(tarjetas.total_ventas_hoy)
);

colocarTexto(
  "dashCompras",
  formatearNumero(tarjetas.compras_hoy)
);

colocarTexto(
  "dashTotalCompras",
  formatearMoneda(tarjetas.total_compras_hoy)
);

colocarTexto(
  "dashClientes",
  formatearNumero(tarjetas.clientes)
);

colocarTexto(
  "dashVehiculos",
  formatearNumero(tarjetas.vehiculos)
);

colocarTexto(
  "dashTecnicos",
  formatearNumero(tarjetas.tecnicos_activos)
);

colocarTexto(
  "dashProductosBajos",
  formatearNumero(tarjetas.productos_bajos)
);

colocarTexto(
  "dashVentasMesActual",
  formatearMoneda(resumen.ventas_mes_actual)
);

colocarTexto(
  "dashVentasMesAnterior",
  formatearMoneda(resumen.ventas_mes_anterior)
);

actualizarComparacionMensual(
  Number(resumen.comparacion_porcentaje || 0)
);

} catch (error) {console.error("Error al cargar el resumen:", error);

mostrarErrorDashboard(
  "No se pudo cargar el resumen del dashboard."
);

}}

// ==================================================// COMPARACIÓN MENSUAL// ==================================================

function actualizarComparacionMensual(porcentaje) {const elemento = document.getElementById("dashComparacionMensual");

if (!elemento) return;

const valor = Number(porcentaje);

elemento.classList.remove("comparacion-positiva","comparacion-negativa","comparacion-neutral");

if (valor > 0) {elemento.classList.add("comparacion-positiva");

elemento.innerHTML = `
  <i class="bi bi-arrow-up-right"></i>
  ${formatearPorcentaje(valor)}
  respecto al mes anterior
`;

return;

}

if (valor < 0) {elemento.classList.add("comparacion-negativa");

elemento.innerHTML = `
  <i class="bi bi-arrow-down-right"></i>
  ${formatearPorcentaje(Math.abs(valor))}
  respecto al mes anterior
`;

return;

}

elemento.classList.add("comparacion-neutral");

elemento.innerHTML = `
    <i class="bi bi-dash-lg"></i>
    Sin variación respecto al mes anterior
`;}

// ==================================================// GRÁFICA DE VENTAS// ==================================================

async function cargarGraficaVentasDashboard() {const canvas = document.getElementById("graficaVentasDashboard");

if (!canvas) return;

try {const respuesta = await fetch("/RONEM/api/api_dashboard.php?action=graficaVentas",{method: "GET",headers: {Accept: "application/json"},cache: "no-store"});

const datos = await leerRespuestaJSON(respuesta);

if (!respuesta.ok || datos.success === false) {
    throw new Error(
        datos.message ||
        datos.error ||
        "No se pudo cargar el resumen del dashboard."
    );
}

const labels = Array.isArray(datos.labels)
  ? datos.labels.map(formatearMes)
  : [];

const valores = Array.isArray(datos.valores)
  ? datos.valores.map(valor => Number(valor || 0))
  : [];

destruirGraficaAnterior();

graficaVentasDashboard = new Chart(canvas, {
  type: "line",

  data: {
    labels,

    datasets: [
      {
        label: "Ventas",
        data: valores,
        borderColor: "#ca2942",
        backgroundColor: "rgba(202, 41, 66, 0.12)",
        borderWidth: 3,
        fill: true,
        tension: 0.35,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: "#ca2942",
        pointBorderColor: "#ffffff",
        pointBorderWidth: 2
      }
    ]
  },

  options: {
    responsive: true,
    maintainAspectRatio: false,

    interaction: {
      mode: "index",
      intersect: false
    },

    plugins: {
      legend: {
        display: false
      },

      tooltip: {
        callbacks: {
          label(contexto) {
            return ` Ventas: ${formatearMoneda(
              contexto.parsed.y
            )}`;
          }
        }
      }
    },

    scales: {
      x: {
        grid: {
          display: false
        },

        ticks: {
          color: "#6b5f62",
          font: {
            family: "Barlow",
            size: 12
          }
        }
      },

      y: {
        beginAtZero: true,

        grid: {
          color: "rgba(6, 8, 7, 0.08)"
        },

        ticks: {
          color: "#6b5f62",

          callback(valor) {
            return formatearMonedaCompacta(valor);
          }
        }
      }
    }
  }
});

} catch (error) {console.error("Error al cargar la gráfica:", error);

const contenedor = canvas.parentElement;

if (contenedor) {
  contenedor.innerHTML = `
    <div class="dashboard-error">
      <i class="bi bi-exclamation-triangle"></i>
      No se pudo cargar la gráfica de ventas.
    </div>
  `;
}

}}

function destruirGraficaAnterior() {if (graficaVentasDashboard) {graficaVentasDashboard.destroy();graficaVentasDashboard = null;}}

// ==================================================// PRODUCTOS CON STOCK BAJO// ==================================================

async function cargarProductosBajosDashboard() {const tbody = document.getElementById("tbodyProductosBajosDashboard");

if (!tbody) return;

tbody.innerHTML = `
    <tr class="empty-row">
        <td colspan="5">
            Cargando productos...
        </td>
    </tr>
`;

try {const respuesta = await fetch("/RONEM/api/api_dashboard.php?action=productosBajos",{method: "GET",headers: {Accept: "application/json"},cache: "no-store"});

const datos = await leerRespuestaJSON(respuesta);

if (!respuesta.ok || datos.success === false) {
throw new Error(
    datos.message ||
    datos.error ||
    "No se pudieron cargar los productos bajos."
);
}

const productos = Array.isArray(datos.productos)
  ? datos.productos
  : [];

renderProductosBajosDashboard(productos);

} catch (error) {console.error("Error al cargar productos con stock bajo:",error);

tbody.innerHTML = `
  <tr class="empty-row">
    <td colspan="5">
      Error al cargar los productos con stock bajo
    </td>
  </tr>
`;

}}

function renderProductosBajosDashboard(productos) {const tbody = document.getElementById("tbodyProductosBajosDashboard");

if (!tbody) return;

if (productos.length === 0) {tbody.innerHTML = `
    <tr class="empty-row">
        <td colspan="5">
            No hay productos con stock bajo
        </td>
    </tr>
`;

return;

}

tbody.innerHTML = productos.map(producto => {const id = Number(producto.Id_producto || 0);const nombre = escaparHTML(producto.Nombre || "Sin nombre");const stock = Number(producto.Stock || 0);const precio = Number(producto.Precio || 0);const estado = Number(producto.Estado || 0);

  return `
    <tr>
      <td>${id}</td>

      <td>
        <strong>${nombre}</strong>
      </td>

      <td>
        <span class="${obtenerClaseStock(stock)}">
          ${stock}
        </span>
      </td>

      <td>${formatearMoneda(precio)}</td>

      <td>
        <span class="badge ${
          estado === 1
            ? "bg-success"
            : "bg-danger"
        }">
          ${estado === 1 ? "Activo" : "Inactivo"}
        </span>
      </td>
    </tr>
  `;
})
.join("");

}

function obtenerClaseStock(stock) {if (stock <= 0) {return "stock-num stock-out";}

if (stock <= 5) {return "stock-num stock-low";}

return "stock-num stock-ok";}

// ==================================================// ACTUALIZAR DASHBOARD// ==================================================

async function actualizarDashboard() {const boton = document.getElementById("btnActualizarDashboard");

if (boton) {boton.disabled = true;

boton.innerHTML = `
  <span
    class="spinner-border spinner-border-sm"
    aria-hidden="true"
  ></span>
  Actualizando...
`;

}

try {await cargarDashboard();} finally {if (boton) {boton.disabled = false;

  boton.innerHTML = `
    <i class="bi bi-arrow-clockwise"></i>
    Actualizar
  `;
}

}}

// ==================================================// FUNCIONES AUXILIARES// ==================================================

function colocarTexto(id, valor) {const elemento = document.getElementById(id);

if (elemento) {elemento.textContent = valor;}}

async function leerRespuestaJSON(respuesta) {const texto = await respuesta.text();

if (!texto.trim()) {throw new Error("El servidor devolvió una respuesta vacía.");}

try {return JSON.parse(texto);} catch (error) {console.error("Respuesta recibida:", texto);

throw new Error(
  "El servidor devolvió una respuesta no válida."
);

}}

function formatearNumero(valor) {const numero = Number(valor || 0);

return numero.toLocaleString("es-GT");}

function formatearMoneda(valor) {const numero = Number(valor || 0);

return numero.toLocaleString("es-GT", {style: "currency",currency: "GTQ",minimumFractionDigits: 2});}

function formatearMonedaCompacta(valor) {
    const numero = Number(valor || 0);

    if (numero >= 1000000) {
        return `Q${(numero / 1000000).toFixed(1)}M`;
    }

    if (numero >= 1000) {
        return `Q${(numero / 1000).toFixed(1)}K`;
    }

    return `Q${numero.toFixed(0)}`;
}

function formatearPorcentaje(valor) {
    const numero = Number(valor || 0);

    return `${numero.toLocaleString("es-GT", {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1
    })}%`;
}

function formatearMes(valor) {
    const texto = String(valor || "");
    const partes = texto.split("-");

    if (partes.length !== 2) {
        return texto;
    }

    const anio = Number(partes[0]);
    const mes = Number(partes[1]);

    if (!anio || !mes) {
        return texto;
    }

    const fecha = new Date(anio, mes - 1, 1);

    const nombreMes = fecha.toLocaleDateString(
        "es-GT",
        {
            month: "short"
        }
    );

    return `${nombreMes} ${anio}`;
}

function escaparHTML(valor) {
    return String(valor ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function mostrarErrorDashboard(mensaje) {const contenedor = document.getElementById("mensajeDashboard");

if (!contenedor) return;

contenedor.innerHTML = `<div
   class="alert alert-danger alert-dismissible fade show"
   role="alert"
 ><i class="bi bi-exclamation-triangle me-2"></i>${escaparHTML(mensaje)}

  <button
    type="button"
    class="btn-close"
    data-bs-dismiss="alert"
    aria-label="Cerrar"
  ></button>
</div>

`;}