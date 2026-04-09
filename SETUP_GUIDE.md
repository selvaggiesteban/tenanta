# Guía de Configuración del Entorno de Desarrollo Local para Tenanta

Esta guía detalla los comandos necesarios para configurar el entorno de desarrollo local para el proyecto Tenanta. El modelo de IA se encargará de las modificaciones de código, pero la ejecución de estos comandos de shell es responsabilidad del usuario.

## Requisitos Previos

Asegúrate de tener instalados los siguientes componentes en tu sistema (Windows, según lo confirmado):

*   **PHP 8.3+**
*   **Composer 2.x**
*   **Node.js 20+**
*   **MySQL 8.0+** (Para entorno de producción/VPS. Para desarrollo local se ha configurado SQLite por defecto.)
*   **Redis 7.x**

## Pasos de Configuración (Ejecutar en el directorio `tenanta`)

1.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```
    *Nota: Si ya tienes las dependencias instaladas, este comando puede terminar rápidamente con "Nothing to install, update or remove".*

2.  **Instalar dependencias de Node.js:**
    ```bash
    npm install --legacy-peer-deps
    ```
    *Nota: Este comando puede tardar varios minutos en completarse, especialmente la primera vez. Por favor, deja que finalice sin interrupciones.*

3.  **Configurar el archivo `.env`:**
    El modelo de IA ya ha configurado el archivo `.env` para usar SQLite para el desarrollo local.
    Si necesitas cambiar a MySQL (por ejemplo, para pruebas más cercanas a producción o para el despliegue), deberás editar manualmente las siguientes líneas en `.env`:
    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=tenanta # O el nombre de tu base de datos MySQL
    DB_USERNAME=root    # O tu usuario de MySQL
    DB_PASSWORD=        # O tu contraseña de MySQL
    ```
    Y asegurarte de que tu servidor MySQL esté en ejecución y accesible con las credenciales proporcionadas.

4.  **Generar la clave de aplicación:**
    ```bash
    php artisan key:generate
    ```

5.  **Generar el secreto JWT:**
    ```bash
    php artisan jwt:secret --force
    ```

6.  **Crear el archivo de base de datos SQLite (si no existe):**
    Si estás usando SQLite para desarrollo local, asegúrate de que el archivo `database/database.sqlite` exista. Si no, puedes crearlo manualmente:
    ```powershell
    New-Item -Path database/database.sqlite -ItemType File
    ```
    *Nota: Si el archivo ya existe, el comando anterior fallará indicando que ya existe, lo cual es normal.*

7.  **Ejecutar las migraciones de la base de datos:**
    ```bash
    php artisan migrate
    ```
    *Nota: Si se usa SQLite y se encuentra un error relacionado con índices fulltext, el modelo de IA ya ha revertido cualquier modificación al archivo de migración. Si este error persiste, se deberá modificar la migración manualmente o usar MySQL.* 

8.  **Sembrar la base de datos (opcional, para datos de prueba):**
    ```bash
    php artisan db:seed
    ```

9.  **Compilar los activos de frontend:**
    ```bash
    npm run build
    ```

10. **Iniciar el servidor de desarrollo (backend y frontend):**
    ```bash
    composer dev
    ```
    *Este comando iniciará el servidor Laravel, el servidor de cola, el monitor de logs y el servidor de desarrollo de Vite (frontend).*

---
