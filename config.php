<?php
// Archivo: config.php
// Propósito: Configuración de la conexión a la base de datos MySQL.
// Autor: Blackbox AI Assistant
// Fecha: 2024-01-15

// Requisitos del servidor: PHP 7.4+ (para sintaxis de tipo de retorno y null coalescing)
//                         Extensión PDO de PHP habilitada.

// -----------------------------------------------------
// 1. Variables de Configuración de la Base de Datos
// -----------------------------------------------------
// Define las credenciales y detalles de conexión a la base de datos.
// Es una buena práctica mantener estas configuraciones en un archivo separado
// para facilitar su gestión y seguridad.
define('DB_HOST', 'localhost');     // Host de la base de datos (comúnmente 'localhost' para desarrollo)
define('DB_USER', 'root');         // Nombre de usuario de la base de datos
define('DB_PASS', '');             // Contraseña del usuario de la base de datos (vacío si no hay)
define('DB_NAME', 'dictionary_db'); // Nombre de la base de datos a la que nos conectaremos

// -----------------------------------------------------
// 2. Conexión a MySQL usando PDO (PHP Data Objects)
// -----------------------------------------------------
// Se ha elegido PDO sobre MySQLi por las siguientes razones:
// - Soporte para múltiples bases de datos: PDO puede trabajar con diferentes tipos de bases de datos
//   (MySQL, PostgreSQL, SQLite, etc.) con una interfaz unificada.
// - Prepared Statements nativos: PDO ofrece una forma más robusta y segura de usar prepared statements,
//   lo que ayuda a prevenir ataques de inyección SQL de forma más efectiva.
// - Manejo de errores consistente: PDO permite configurar el modo de error para lanzar excepciones,
//   lo que simplifica el manejo de errores de la base de datos.

/**
 * @var PDO|null $pdo Objeto de conexión PDO. Será null si la conexión falla.
 */
$pdo = null; // Inicializamos la variable de conexión a null

try {
    // Construye la cadena DSN (Data Source Name) para la conexión PDO.
    // charset=utf8mb4 es importante para el soporte completo de caracteres Unicode.
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    // Opciones de PDO:
    // - PDO::ATTR_ERRMODE: Configura cómo PDO maneja los errores. PDO::ERRMODE_EXCEPTION hace que PDO
    //   lance excepciones en caso de errores, lo que permite un manejo de errores estructurado con try-catch.
    // - PDO::ATTR_DEFAULT_FETCH_MODE: Establece el modo de recuperación predeterminado para los resultados.
    //   PDO::FETCH_ASSOC recupera las filas como un array asociativo (nombre de columna => valor).
    // - PDO::ATTR_EMULATE_PREPARES: Deshabilita la emulación de prepared statements. Cuando es false,
    //   PDO usa prepared statements nativos del controlador de la base de datos, lo cual es más seguro y eficiente.
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Intenta establecer la conexión a la base de datos.
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Opcional: Mensaje de éxito en la conexión (solo para depuración, se puede eliminar en producción)
    // echo "Conexión a la base de datos exitosa.<br>";

} catch (PDOException $e) {
    // -----------------------------------------------------
    // 3. Manejo de Errores de Conexión
    // -----------------------------------------------------
    // Si ocurre un error durante la conexión, se captura la excepción PDOException.
    // Se muestra un mensaje descriptivo al usuario y se registra el error internamente.
    // En un entorno de producción, se debería evitar mostrar detalles técnicos del error
    // directamente al usuario por razones de seguridad.
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// -----------------------------------------------------
// 4. Función para cerrar la conexión (opcional con PDO)
// -----------------------------------------------------
// Con PDO, la conexión se cierra automáticamente cuando el objeto PDO es destruido
// (por ejemplo, al final del script o cuando se le asigna null).
// Sin embargo, se puede definir una función explícita si se desea un control más granular.

/**
 * Cierra la conexión PDO a la base de datos.
 *
 * @param PDO|null $connection El objeto PDO de conexión.
 * @return void
 */
function closeDbConnection(?PDO $connection): void
{
    // Asignar null al objeto PDO lo desconecta de la base de datos.
    $connection = null;
    // Opcional: Mensaje de cierre (solo para depuración)
    // echo "Conexión a la base de datos cerrada.<br>";
}

// Fin del archivo config.php
