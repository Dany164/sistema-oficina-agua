# Sistema Oficina de Agua

Sistema web para digitalizar el control de una oficina comunitaria de agua potable: mantenimiento de tarifas, control de clientes, registro de lecturas de contadores casa por casa, cálculo automático de consumo y cobro, generación de recibo imprimible, control de pagos en oficina y un dashboard de estado de cuenta de clientes.

[Tablero de Jira](https://danysspace.atlassian.net/jira/software/projects/SCRUM/boards/1)

## Tabla de Contenidos
- [Equipo de Desarrollo](#equipo-de-desarrollo)
- [Requisitos](#requisitos)
- [Instalación y Configuración](#instalación-y-configuración)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Tecnologías Utilizadas](#tecnologías-utilizadas)

---

## Equipo de Desarrollo

| Nombre completo | Carnet | Correo electrónico | Rol |
|---|---|---|---|
| Luis Alfonso Morán Juárez | 0905-23-15636 | lmoranj@miumg.edu.gt | Auth + BD + Dashboard |
| Dany Miguel Mateo Hernández  | 0905-23-19399 | dmateoh@miumg.edu.gt | Tarifas + Lecturas + Depuración |
| Víctor Daniel Sosa López  | 0905-23-8230 | vsosal1@miumg.edu.gt | Pagos + Clientes + Usuarios |
| Edgar Guillermo Chinchilla Chinchilla | 0905-23-13243 | echinchillac4@miumg.edu.gt | Contadores + Servicio + Despliegue |
>**Líder de equipo:** Edgar Guillermo Chinchilla Chinchilla

---

## Requisitos

Antes de instalar el proyecto, asegúrate de tener:

- PHP >= 8.2
- Composer
- CodeIgniter 4.7 o superior (se instala vía Composer)
- MariaDB / MySQL >= 10.4.32
- Servidor web (XAMPP, o el servidor embebido de CodeIgniter)
- Git

## Instalación y Configuración

1. Clonar el repositorio
```bash
   git clone https://github.com/Dany164/sistema-oficina-agua.git
   cd sistema-oficina-agua
```

2. Instalar dependencias de PHP
```bash
   composer install
```

3. Configurar variables de entorno
```bash
   cp env .env
```
   Editar `.env` y configurar, como mínimo:

```
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = oficina_agua
database.default.username = tu_usuario_de_mariadb
database.default.password = tu_contraseña_de_mariadb
database.default.DBDriver = MySQLi
database.default.port = 3306
```  
> **Nota:** estos valores dependen de tu instalación local de MariaDB  
>  Por ejemplo, en XAMPP normalmente es `usuario = root` y `contraseña` vacía por defecto

4. Crear la base de datos  
   Ejecutar el script `schema_oficina_agua.sql` en tu gestor de MariaDB
   (phpMyAdmin, o por terminal).  
>**Nota:** Al configurar la base de datos, usa `app/Database/schema/schema_oficina_agua.sql` (el archivo sin sufijo).  
>El archivo `schema_oficina_agua(anterior).sql` se conserva solo como referencia histórica.  

5. Ejecutar migraciones pendientes
```bash
   php spark migrate
```

6. Poblar datos de prueba
```bash
   php spark db:seed DatabaseSeeder
```

7. Levantar el servidor local
```bash
   php spark serve
```

8. Acceder a la aplicación  
   Visitar `http://localhost:8080/login` en el navegador.

## Estructura del Proyecto

 ```bash
sistema-oficina-agua/
├── app/
│   ├── Controllers/         # Lógica de cada módulo
│   │   ├── Auth.php         # Login / logout
│   │   ├── Main.php         # Dashboard
│   │   ├── Clientes.php
│   │   ├── Contadores.php
│   │   ├── Lecturas.php
│   │   ├── Pagos.php
│   │   ├── Servicios.php
│   │   ├── Tarifas.php
│   │   └── Usuarios.php
│   ├── Models/                          # Acceso a datos por tabla
│   ├── Views/                           # Vistas PHP, organizadas por módulo
│   │   ├── layouts/                     # Layouts base (main, auth) y partials
│   │   ├── auth/, clientes/, contadores/,
│   │   │   lecturas/, pagos/, servicios/,
│   │   │   tarifas/, usuarios/          # Una carpeta por módulo
│   │   └── primera_vista.php            # Vista del dashboard
│   ├── Filters/                         # Filtros de ruta (ej. AuthFilter)
│   ├── Config/                          # Configuración del framework (Routes, Filters, App, etc.)
│   └── Database/
│       ├── Migrations/            # Cambios incrementales a la BD
│       ├── Seeds/                 # Datos de prueba
│       └── schema/                # Script SQL base del esquema completo
├── docs/
│   └── modelo-er/              # Diagramas de entidad-relación
├── public/                     # Punto de entrada web y assets (CSS/JS de SB Admin)
├── tests/                      # Pruebas del proyecto
├── writable/                   # Cache, logs, sesiones (generado en tiempo de ejecución)
├── composer.json               # Dependencias PHP
└── env                         # Plantilla de variables de entorno
```

### Módulos principales

| Módulo | Controlador | Descripción |
|---|---|---|
| Autenticación | `Auth.php` | Login, logout, sesiones |
| Dashboard | `Main.php` | Resumen de estado de cuenta y contadores pendientes |
| Clientes | `Clientes.php` | CRUD de clientes |
| Contadores | `Contadores.php` | CRUD de contadores de agua |
| Lecturas | `Lecturas.php` | Registro y corrección de lecturas |
| Pagos | `Pagos.php` | Registro de pagos, recibos, anulaciones |
| Servicios | `Servicios.php` | Tipos de servicio (1/4 paja, 1/2 paja, etc.) |
| Tarifas | `Tarifas.php` | Tarifas vigentes por tipo de servicio |
| Usuarios | `Usuarios.php` | Gestión de usuarios y roles |

## Tecnologías Utilizadas

- **Backend:** PHP 8.2, CodeIgniter 4
- **Frontend:** Bootstrap 5, plantilla SB Admin
- **Base de datos:** MariaDB
- **Control de versiones:** Git / GitHub