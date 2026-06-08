# FesolCheck — Sistema de Control de Jornada Laboral

> Aplicación web para la gestión integral del fichaje, horarios, vacaciones y reportes del personal de una empresa.

**Producción:** [fesolcheck.ricardorm.es](https://fesolcheck.ricardorm.es)

---

## Características principales

- **Fichaje digital** — Registro de entrada, pausa, reanudación y salida con soporte de jornada partida (mañana/tarde)
- **Gestión de horarios** — Solicitudes de horario/vacaciones/médico con flujo de aprobación admin → empleado
- **Cuadrante de vacaciones** — Visualización mensual con alertas automáticas cuando más del 33 % del equipo coincide de baja
- **Reportes PDF y Excel** — Generación de informes mensuales/anuales por empleado siguiendo el formato RDL 8/2019
- **Widget meteorológico** — Alertas de condiciones extremas vía OpenWeatherMap; bloquea automáticamente el fichaje en alerta roja
- **Chatbot IA** — Asistente integrado con modelo LLM local (Ollama), responde consultas sobre el uso de la aplicación
- **Restricción por IP** — Control opcional que limita el fichaje a IPs o subredes autorizadas (soporte CIDR)
- **Sistema de tickets** — Canal de soporte interno entre empleados y administrador
- **Multi-empresa** — Un mismo usuario puede pertenecer a varias empresas y cambiar de contexto al iniciar sesión
- **Recuperación de contraseña** — Flujo seguro por email con código de 6 dígitos (caducidad 15 min)

---

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.2, PDO |
| Base de datos | MySQL / MariaDB 10.4 |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| PDF | [TCPDF](https://tcpdf.org/) |
| Excel | [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/) |
| Email | [PHPMailer](https://github.com/PHPMailer/PHPMailer) + Gmail SMTP |
| Chatbot | [Ollama](https://ollama.com/) (`asistente-fichajes`) vía NDJSON streaming |
| Clima | [OpenWeatherMap API](https://openweathermap.org/api) |

---

## Requisitos

- PHP 8.2 o superior con extensiones `pdo_mysql`, `mbstring`, `gd`, `zip`
- MySQL / MariaDB 10.4 o superior
- Servidor web Apache (XAMPP en local, IONOS en producción)
- Instancia de [Ollama](https://ollama.com/) accesible públicamente (para el chatbot)
- Clave de API de [OpenWeatherMap](https://openweathermap.org/api) (para el widget de clima)
- Cuenta de Gmail con contraseña de aplicación (para el envío de emails)

---

## Instalación local

```bash
# 1. Clonar en la carpeta de XAMPP
git clone <repo-url> c:/xampp/htdocs/Proyecto_Fichaje

# 2. Crear la base de datos
mysql -u root -e "CREATE DATABASE sistema_fichajes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Importar el esquema
mysql -u root sistema_fichajes < BD/sistema_fichajes.sql
```

### Configurar credenciales

Copia la plantilla de secretos y rellena los valores reales:

```bash
cp secrets.example.php secrets.php
```

Edita `secrets.php`:

```php
define('MAIL_USERNAME',  'tu@gmail.com');
define('MAIL_PASSWORD',  'xxxx xxxx xxxx xxxx'); // contraseña de aplicación de Google
define('MAIL_FROM',      'tu@gmail.com');
define('OWM_API_KEY',    'tu_clave_openweathermap');
```

> `secrets.php` está en `.gitignore` y nunca debe subirse al repositorio.

Accede a la aplicación en `http://localhost/Proyecto_Fichaje/login.php`.

---

## Estructura del proyecto

```
Proyecto_Fichaje/
├── panel.php                  # Router principal y layout (header/sidebar/main)
├── login.php                  # Pantalla de acceso
├── config.php                 # Conexión PDO a la base de datos
├── secrets.php                # Credenciales (excluido de git)
├── secrets.example.php        # Plantilla de credenciales
│
├── secciones/
│   ├── fichaje/               # Reloj, historial, modificación, validación IP
│   ├── horario/               # Peticiones, cuadrantes, vacaciones, plantillas, calendario
│   ├── reportes/              # Generación PDF/Excel, historial de documentos
│   ├── empleados/             # Lista, alta de usuario, nóminas
│   ├── tickets/               # Sistema de soporte interno
│   ├── perfil/                # Datos personales, foto, contraseña
│   ├── notificaciones/        # Panel de avisos
│   ├── vacaciones/            # Cuadrante anual (vista admin)
│   ├── tipos-jornada/         # Plantillas personalizadas de jornada
│   ├── ia/                    # Chatbot (proxy NDJSON → Ollama)
│   └── api/                   # Endpoints AJAX (50+ archivos)
│
├── css/                       # Hojas de estilo por sección
├── js/                        # Scripts de apoyo
├── img/                       # Imágenes y logos
├── BD/                        # Esquema SQL y notas de BD
├── PHPMailer/                 # Librería de email
├── tcpdf/                     # Generación PDF
└── PhpSpreadsheet-master/     # Exportación Excel
```

---

## Roles y permisos

### Administrador

| Sección | Capacidades |
|---------|------------|
| Fichaje | Ver, modificar y añadir fichajes de cualquier empleado |
| Horario | Aprobar / rechazar solicitudes, editar cuadrantes, gestionar plantillas |
| Vacaciones | Cuadrante mensual con alertas de cobertura |
| Reportes | Generar, previsualizar y descargar PDF/Excel |
| Empleados | Crear, editar, activar/desactivar; subir nóminas |
| Tickets | Ver y responder todos los tickets |
| IP | Habilitar restricción y gestionar IPs autorizadas |

### Empleado

| Sección | Capacidades |
|---------|------------|
| Fichaje | Registrar entrada / pausa / reanudación / salida |
| Horario | Crear y enviar solicitudes de horario, vacaciones y citas médicas |
| Mis nóminas | Descargar documentos subidos por el administrador |
| Mis tickets | Crear tickets y responder en el hilo |
| Perfil | Cambiar foto de perfil y contraseña |

---

## Base de datos

**Nombre:** `sistema_fichajes`

| Tabla | Propósito |
|-------|-----------|
| `usuario` | Usuarios del sistema |
| `empresa` | Empresas (multi-tenant) |
| `empresa_usuario` | Relación usuario ↔ empresa con rol (admin/empleado) |
| `fichaje` | Registros de entrada, pausa, reanudación y salida |
| `horarios` | Horarios aprobados por día |
| `solicitudes_horario` | Cabecera de cada solicitud de cambio |
| `detalle_solicitud_horario` | Desglose diario de una solicitud |
| `vacaciones_acumulado` | Saldo de días de vacaciones por usuario/año |
| `tipos_jornada_custom` | Plantillas de jornada personalizadas por empresa |
| `reportes` | Historial de reportes generados |
| `notificaciones` | Avisos del sistema |
| `incidencias` | Tickets de soporte |
| `mensajes_ticket` | Hilo de mensajes de cada ticket |
| `documentos` | Nóminas y documentos PDF |
| `horas_extra` | Acumulador de horas extra por año |
| `empresa_ips_autorizadas` | Lista blanca de IPs para restricción de fichaje |
| `historial_validaciones` | Auditoría de todas las aprobaciones y rechazos |

---

## Flujos principales

### Solicitud de horario / vacaciones

```
Empleado crea borradores en su calendario
    → "Enviar a validar" genera solicitud_horario + detalle_solicitud_horario
    → Admin ve la petición en "Peticiones de horario"
    → Admin aprueba o rechaza (motivo obligatorio en rechazo)
    → Notificación automática al empleado
    → Si aprobada → se crean registros en tabla horarios
```

### Alerta meteorológica roja

```
Widget OWM detecta viento ≥ 90 km/h o lluvia ≥ 60 mm/h
    → Modal de aviso visible para el empleado
    → Botones de fichaje deshabilitados
    → Email automático a todos los empleados de la empresa
    → Estado persistido en sessionStorage hasta que mejoren las condiciones
```

### Generación de reporte

```
Admin selecciona empleado / mes / año / tipo
    → Preview de datos del periodo
    → Genera PDF (TCPDF, formato RDL 8/2019)
    → Guarda en disco + registro en tabla reportes
    → Empleado puede descargarlo desde "Mis nóminas"
```

---

## Configuración del servidor (producción)

El proyecto se despliega en IONOS. El chatbot IA llama directamente desde el navegador a `https://ia.ricardorm.es/api/generate` (instancia Ollama con SSL), evitando timeouts de PHP.

Asegúrate de que el servidor tenga:

```
# .htaccess o configuración Apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

---

## Variables de entorno / secretos

| Constante | Descripción |
|-----------|-------------|
| `MAIL_HOST` | Servidor SMTP (por defecto `smtp.gmail.com`) |
| `MAIL_PORT` | Puerto SMTP (por defecto `587`) |
| `MAIL_USERNAME` | Cuenta de Gmail remitente |
| `MAIL_PASSWORD` | Contraseña de aplicación de Google |
| `MAIL_FROM` | Dirección del remitente |
| `MAIL_FROM_NAME` | Nombre visible del remitente |
| `OWM_API_KEY` | Clave de OpenWeatherMap |

Todas se definen en `secrets.php` (ver `secrets.example.php` como plantilla).

---

## Licencia

Proyecto académico / TFG — Ricardo Ruiz Mocholi
