<?php
// Archivo: import_csv.php
// Propósito: Script para importar datos de un archivo CSV a la tabla 'words' de la base de datos.
// Autor: Blackbox AI Assistant
// Fecha: 2024-01-15

// INSTRUCCIONES DE USO:
// 1. Colocar el archivo "diccionario.csv" en la misma carpeta que este script.
// 2. Asegurarse de que la base de datos 'dictionary_db' y la tabla 'words' existan (ejecutar create_database.sql).
// 3. Ejecutar este script desde la terminal: php import_csv.php
// 4. Verificar el reporte de importación en la consola.

// Requisitos del servidor: PHP 7.4+
//                         Extensión PDO de PHP habilitada.

// Incluir el archivo de configuración para la conexión a la base de datos
require_once 'config.php';

// -----------------------------------------------------
// 1. Configuración del archivo CSV
// -----------------------------------------------------
$csvFile = 'diccionario.csv'; // Nombre del archivo CSV a importar
$insertedRecords = 0;         // Contador de registros insertados exitosamente
$duplicateRecords = 0;        // Contador de registros duplicados

echo "--- Iniciando importación de CSV a la base de datos ---\n";
echo "Archivo CSV: " . $csvFile . "\n";

// -----------------------------------------------------
// 2. Validar que el archivo CSV existe
// -----------------------------------------------------
if (!file_exists($csvFile)) {
    die("Error: El archivo CSV '" . $csvFile . "' no se encontró en la misma carpeta.\n");
}

// -----------------------------------------------------
// 3. Abrir el archivo CSV para lectura
// -----------------------------------------------------
// 'r' abre el archivo para lectura.
$handle = fopen($csvFile, 'r');
if ($handle === false) {
    die("Error: No se pudo abrir el archivo CSV '" . $csvFile . "'.\n");
}

// -----------------------------------------------------
// 4. Preparar la consulta SQL para inserción
// -----------------------------------------------------
// Se utiliza un prepared statement para prevenir inyecciones SQL y mejorar el rendimiento.
// ON DUPLICATE KEY UPDATE es una estrategia para manejar duplicados si 'word' fuera PRIMARY KEY,
// pero como es UNIQUE, un INSERT simple con manejo de excepciones es más directo para este caso.
// La columna 'word' tiene una restricción UNIQUE, por lo que un intento de insertar una palabra
// ya existente generará una excepción PDOException.
$stmt = $pdo->prepare("INSERT INTO words (word, definition) VALUES (:word, :definition)");

// -----------------------------------------------------
// 5. Leer y procesar el archivo CSV
// -----------------------------------------------------
// fgetcsv() lee una línea del archivo CSV y la parsea en un array.
$rowNum = 0;
while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    $rowNum++;

    // Saltar la primera fila (encabezados)
    if ($rowNum == 1) {
        echo "Saltando fila de encabezados: " . implode(', ', $data) . "\n";
        continue;
    }

    // Validar que la fila tiene el número esperado de columnas (Palabra, Definicion)
    if (count($data) < 2) {
        echo "Advertencia: Fila " . $rowNum . " ignorada por formato incorrecto (menos de 2 columnas).\n";
        continue;
    }

    // Asignar los valores de las columnas a variables
    // Se asume que la primera columna es "Palabra" y la segunda es "Definicion"
    $word = trim($data[0]);       // Eliminar espacios en blanco al inicio/final
    $definition = trim($data[1]); // Eliminar espacios en blanco al inicio/final

    // Validar que la palabra y definición no estén vacías
    if (empty($word) || empty($definition)) {
        echo "Advertencia: Fila " . $rowNum . " ignorada por tener palabra o definición vacía.\n";
        continue;
    }

    try {
        // Ejecutar el prepared statement con los datos de la fila actual
        $stmt->execute([
            ':word' => $word,
            ':definition' => $definition
        ]);
        $insertedRecords++; // Incrementar contador de inserciones exitosas
        echo "Insertado: '" . $word . "'\n";
    } catch (PDOException $e) {
        // Manejar duplicados apropiadamente
        // Código de error 23000 (SQLSTATE) y 1062 (MySQL error code) indican entrada duplicada para clave UNIQUE.
        if ($e->getCode() == '23000' && strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
            $duplicateRecords++; // Incrementar contador de duplicados
            echo "Advertencia: La palabra '" . $word . "' ya existe en la base de datos. Saltando.\n";
        } else {
            // Otros errores de base de datos
            echo "Error al insertar la fila " . $rowNum . " ('" . $word . "'): " . $e->getMessage() . "\n";
        }
    }
}

// -----------------------------------------------------
// 6. Cerrar el archivo CSV
// -----------------------------------------------------
fclose($handle);

// -----------------------------------------------------
// 7. Mostrar reporte final
// -----------------------------------------------------
echo "\n--- Reporte de Importación ---\n";
echo "Registros procesados (excluyendo encabezado): " . ($rowNum - 1) . "\n";
echo "Registros insertados exitosamente: " . $insertedRecords . "\n";
echo "Registros duplicados encontrados: " . $duplicateRecords . "\n";
echo "--- Importación finalizada ---\n";

// Cerrar la conexión a la base de datos (opcional, se cierra al final del script)
closeDbConnection($pdo);

// Fin del archivo import_csv.php
