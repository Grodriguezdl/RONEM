const configuracionReportes = {
  usuarios: {
    titulo: "Gestión de Usuarios",
    tabla: "#page-usuarios table",
    excluirColumnas: [7],
    orientacion: "landscape",
    nombre: "usuarios"
  },

  noticias: {
    titulo: "Noticias y Novedades",
    tabla: "#page-noticias table",
    excluirColumnas: [0, 3],
    orientacion: "portrait",
    nombre: "noticias"
  },

  solicitudes: {
    titulo: "Solicitudes Moto Escuela",
    tabla: "#page-solicitudes table",
    excluirColumnas: [9],
    orientacion: "landscape",
    nombre: "solicitudes_moto_escuela"
  },

  productos: {
    titulo: "Gestión de Productos",
    tabla: "#page-productos table",
    excluirColumnas: [1, 8],
    orientacion: "landscape",
    nombre: "productos"
  },

  ventas: {
    titulo: "Detalles de Ventas",
    tabla: "#page-ventas table",
    excluirColumnas: [6],
    orientacion: "landscape",
    nombre: "ventas"
  },

  repuestos: {
    titulo: "Gestión de Repuestos",
    tabla: "#page-repuestos table",
    excluirColumnas: [5],
    orientacion: "landscape",
    nombre: "repuestos"
  }
};

function limpiarTextoPDF(valor) {
  return String(valor ?? "")
    .replace(/\s+/g, " ")
    .trim();
}

function obtenerDatosTablaPDF(tabla, excluirColumnas) {
  const encabezados = Array.from(
    tabla.querySelectorAll("thead th")
  )
    .filter((_, indice) => !excluirColumnas.includes(indice))
    .map(celda => limpiarTextoPDF(celda.textContent));

  const filas = Array.from(
    tabla.querySelectorAll("tbody tr")
  )
    .filter(fila => !fila.classList.contains("empty-row"))
    .map(fila =>
      Array.from(fila.querySelectorAll("td"))
        .filter((_, indice) => !excluirColumnas.includes(indice))
        .map(celda => limpiarTextoPDF(celda.textContent))
    )
    .filter(fila => fila.length > 0);

  return {
    encabezados,
    filas
  };
}

function descargarTablaPDF(tipo) {
  const configuracion = configuracionReportes[tipo];

  if (!configuracion) {
    alert("El reporte solicitado no existe.");
    return;
  }

  const tabla = document.querySelector(
    configuracion.tabla
  );

  if (!tabla) {
    alert("No se encontró la tabla del apartado.");
    return;
  }

  const datos = obtenerDatosTablaPDF(
    tabla,
    configuracion.excluirColumnas
  );

  if (datos.filas.length === 0) {
    alert("No hay registros para descargar.");
    return;
  }

  if (!window.jspdf) {
    alert("No se pudo cargar la librería jsPDF.");
    return;
  }

  const { jsPDF } = window.jspdf;

  const documento = new jsPDF({
    orientation: configuracion.orientacion,
    unit: "mm",
    format: "a4"
  });

  const fecha = new Date().toLocaleString("es-GT");

  documento.setFont("helvetica", "bold");
  documento.setFontSize(16);

  documento.text(
    "RONEM - " + configuracion.titulo,
    14,
    16
  );

  documento.setFont(
    "helvetica",
    "normal"
  );

  documento.setFontSize(9);

  documento.text(
    "Generado: " + fecha,
    14,
    22
  );

  documento.autoTable({
    head: [datos.encabezados],
    body: datos.filas,
    startY: 28,
    theme: "grid",

    styles: {
      fontSize: 8,
      cellPadding: 2,
      overflow: "linebreak",
      valign: "middle"
    },

    headStyles: {
      fillColor: [20, 20, 20],
      textColor: [255, 255, 255],
      fontStyle: "bold"
    },

    alternateRowStyles: {
      fillColor: [245, 245, 245]
    },

    margin: {
      top: 28,
      right: 10,
      bottom: 14,
      left: 10
    },

    didDrawPage: function () {
      const numeroPagina =
        documento.internal.getNumberOfPages();

      documento.setFontSize(8);

      documento.text(
        "Página " + numeroPagina,
        documento.internal.pageSize.getWidth() - 22,
        documento.internal.pageSize.getHeight() - 6
      );
    }
  });

  const fechaArchivo = new Date()
    .toISOString()
    .slice(0, 10);

  documento.save(
    "RONEM_" +
    configuracion.nombre +
    "_" +
    fechaArchivo +
    ".pdf"
  );
}