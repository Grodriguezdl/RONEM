# RONEM 🏍️

Plataforma web para un taller y tienda de motocicletas, desarrollada con **PHP**, **MySQL**, **JavaScript**, **HTML** y **CSS**. Integra en un solo sistema el sitio público, la tienda de repuestos y accesorios, las órdenes de servicio del taller, una moto escuela con seguimiento de progreso y un programa de fidelización por puntos, con paneles distintos para clientes, técnicos, empleados y administradores.

> *Web platform for a motorcycle workshop and store, built with PHP, MySQL, JavaScript, HTML and CSS. It combines the public site, a parts and accessories store, workshop service orders, a riding school with progress tracking and a loyalty points program, with separate dashboards for customers, technicians, employees and administrators.*

## Funcionalidades

**Autenticación y seguridad**
- Registro con **verificación por código enviado al correo** (con opción de reenvío).
- Inicio de sesión con correo y contraseña o con **cuenta de Google (OAuth 2.0)**.
- Contraseñas y códigos de verificación guardados con hash (`password_hash`).
- Bloqueo temporal de la cuenta tras varios intentos fallidos de inicio de sesión.
- Sesiones configuradas de forma segura (cookies `HttpOnly`, `SameSite` y modo estricto).
- **Sistema multirrol**: un mismo usuario puede tener varios roles y elegir con cuál entrar.

**Sitio público**
- Página de inicio con banner, servicios, catálogo de productos y noticias.
- Solicitudes de servicio técnico y de inscripción a la moto escuela.
- Formulario de empleo con envío de CV.

**Panel del cliente**
- Registro y gestión de sus vehículos.
- Historial de servicios realizados.
- Seguimiento de su avance en la moto escuela por etapas.
- Programa de **fidelización**: acumulación de puntos, catálogo de recompensas y canjes.

**Panel del técnico**
- Órdenes de trabajo asignadas, con estados desde "Recibida" hasta "Entregada".
- Consulta de vehículos e historial de servicio.
- Registro de los repuestos utilizados en cada orden.
- Consulta de manuales técnicos.

**Panel del empleado**
- Punto de venta: selección de productos, detalle y registro de ventas.
- Historial de ventas, gestión de productos y clientes.
- Validación de canjes de recompensas.

**Administración**
- Dashboard con indicadores generales.
- Gestión de usuarios y roles, noticias, banner, servicios, repuestos y solicitudes.
- Panel del administrador de taller: agenda, órdenes, equipo e historial.
- **Reportes de órdenes en PDF** generados con FPDF.
- Notificaciones por correo con PHPMailer.

## Capturas de pantalla

| Inicio | Panel del cliente |
|---|---|
| ![Inicio](docs/capturas/inicio.png) | ![Cliente](docs/capturas/cliente.png) |

| Panel del técnico | Ventas |
|---|---|
| ![Técnico](docs/capturas/tecnico.png) | ![Ventas](docs/capturas/ventas.png) |

## Tecnologías

- **Backend:** PHP 8 (MySQLi)
- **Base de datos:** MySQL / MariaDB
- **Frontend:** HTML, CSS y JavaScript, con endpoints en PHP que responden en JSON (`api/`)
- **Librerías:** PHPMailer (correo SMTP), Google API Client (inicio de sesión con Google), FPDF (reportes PDF)
- **Dependencias:** Composer
- **Hosting:** Hostinger

## Estructura del proyecto

```
RONEM/
├── api/            # Endpoints que consumen los paneles vía JavaScript
├── auth/           # Registro, login, verificación y login con Google
├── config/         # Conexión, correo y sesión (archivos reales excluidos de Git)
├── includes/       # Autenticación, permisos, correos y utilidades
├── paneles/        # Vistas del panel del cliente y del técnico
├── reportes/       # Generación de reportes PDF
├── modals/         # Ventanas modales de los servicios
├── js/ · css/ · img/
├── uploads/        # Archivos subidos por usuarios (excluidos de Git)
├── fpdf186/        # Librería FPDF
├── database/
│   └── ronem.sql   # Estructura, datos de catálogo y usuarios de prueba
└── index.php
```

## Requisitos

- PHP 8.0 o superior
- MySQL o MariaDB
- Composer
- Un servidor local como **XAMPP** o **Laragon**
- Opcional: una cuenta SMTP (por ejemplo, Brevo) y credenciales OAuth de Google Cloud

## Instalación y ejecución

1. Clona el repositorio dentro de la carpeta de tu servidor local (por ejemplo, `htdocs` en XAMPP):
   ```bash
   git clone https://github.com/Grodriguezdl/RONEM.git
   cd RONEM
   ```
2. Instala las dependencias:
   ```bash
   composer install
   ```
3. Importa la base de datos desde phpMyAdmin o por consola:
   ```bash
   mysql -u root -p < database/ronem.sql
   ```
4. Crea los archivos de configuración a partir de los ejemplos y completa tus datos:
   ```bash
   cp config/conexion.example.php config/conexion.php
   cp config/mailer.example.php config/mailer.php
   cp auth/google/config.example.php auth/google/config.php
   ```
5. Abre `http://localhost/RONEM` en el navegador.

El registro de nuevos usuarios requiere configurar el correo SMTP, y el botón de Google requiere credenciales OAuth. Para probar el sistema sin configurarlos, usa las cuentas de prueba.

### Usuarios de prueba

Todas las cuentas usan la contraseña `Demo1234`.

| Rol | Correo |
|---|---|
| Administrador | `admin@ronem.demo` |
| Administrador de taller | `taller@ronem.demo` |
| Empleado | `empleado@ronem.demo` |
| Técnico | `tecnico@ronem.demo` |
| Cliente | `cliente@ronem.demo` |

## Lo que aprendí

- Diseñar un sistema de **roles múltiples** con permisos y paneles distintos para cada tipo de usuario.
- Implementar autenticación completa: verificación por correo, OAuth con Google, hash de contraseñas y protección contra intentos repetidos.
- Separar la interfaz de la lógica consumiendo endpoints PHP desde JavaScript.
- Modelar una base de datos relacional amplia (más de 30 tablas) para ventas, taller, escuela y fidelización.
- Integrar librerías de terceros con Composer y publicar el proyecto en un hosting real.

## Mejoras futuras

- Mover las credenciales a variables de entorno (`.env`).
- Agregar protección CSRF a los formularios.
- Pruebas automatizadas de los endpoints de la API.
- Gestionar FPDF también con Composer.

## Autores

- **Gabriel Rodríguez** · [GitHub](https://github.com/Grodriguezdl) · [LinkedIn](#)
