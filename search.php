<?php
// Archivo: search.php
// Propósito: Contiene la lógica de búsqueda de palabras en el diccionario, incluyendo
//            búsqueda exacta y búsqueda tolerante a errores usando el algoritmo de Levenshtein.
// Autor: Blackbox AI Assistant
// Fecha: 2024-01-15

// Requisitos del servidor: PHP 7.4+
//                         Extensión PDO de PHP habilitada.

// Incluir el archivo de configuración para la conexión a la base de datos
require_once 'config.php';

// -----------------------------------------------------
// 1. Función auxiliar para calcular la distancia de Levenshtein
// -----------------------------------------------------
/**
 * Calcula la distancia de Levenshtein entre dos cadenas.
 * La distancia de Levenshtein es el número mínimo de ediciones (inserciones, eliminaciones o sustituciones)
 * necesarias para transformar una cadena en la otra.
 *
 * @param string $str1 La primera cadena.
 * @param string $str2 La segunda cadena.
 * @return int La distancia de Levenshtein.
 */
function levenshteinDistance(string $str1, string $str2): int
{
    // EXPLICACIÓN DEL ALGORITMO DE LEVENSHTEIN:
    // El algoritmo de Levenshtein es un algoritmo de programación dinámica que calcula la distancia
    // entre dos secuencias de caracteres. Se basa en construir una matriz (o tabla) donde cada celda
    // [i][j] representa la distancia de Levenshtein entre el prefijo de longitud 'i' de str1
    // y el prefijo de longitud 'j' de str2.

    // Pasos del algoritmo:
    // 1. Inicialización de la matriz:
    //    - La primera fila (i=0) se inicializa con 0, 1, 2, ..., j (costo de insertar j caracteres para igualar).
    //    - La primera columna (j=0) se inicializa con 0, 1, 2, ..., i (costo de eliminar i caracteres para igualar).
    //    Esto representa el costo de transformar una cadena vacía en un prefijo de la otra, o viceversa.

    // 2. Relleno de la matriz:
    //    Para cada celda [i][j] (donde i > 0 y j > 0):
    //    - Si los caracteres str1[i-1] y str2[j-1] son iguales, el costo de sustitución es 0.
    //      En este caso, la distancia es la misma que la de los prefijos anteriores: matrix[i-1][j-1].
    //    - Si los caracteres son diferentes, el costo de sustitución es 1.
    //      La distancia en matrix[i][j] es el mínimo de tres operaciones posibles, más su costo:
    //      a) Eliminación: matrix[i-1][j] + 1 (eliminar str1[i-1])
    //      b) Inserción: matrix[i][j-1] + 1 (insertar str2[j-1])
    //      c) Sustitución: matrix[i-1][j-1] + 1 (sustituir str1[i-1] por str2[j-1])

    // 3. Resultado final:
    //    La distancia de Levenshtein entre las dos cadenas completas se encuentra en la celda inferior derecha de la matriz:
    //    matrix[len(str1)][len(str2)].

    // Ejemplo: "cheater" vs "cheatrr"
    // str1 = "cheater" (longitud 7)
    // str2 = "cheatrr" (longitud 7)

    // Matriz (simplificada, solo para ilustrar el punto final):
    //       "" c h e a t r r
    //    "" 0  1 2 3 4 5 6 7
    //    c  1  0 1 2 3 4 5 6
    //    h  2  1 0 1 2 3 4 5
    //    e  3  2 1 0 1 2 3 4
    //    a  4  3 2 1 0 1 2 3
    //    t  5  4 3 2 1 0 1 2
    //    e  6  5 4 3 2 1 1 2  (Aquí 'e' vs 'r', costo 1. Min(matrix[5][6]+1, matrix[6][5]+1, matrix[5][5]+1) = Min(1+1, 1+1, 0+1) = 1)
    //    r  7  6 5 4 3 2 1 1  (Aquí 'r' vs 'r', costo 0. Min(matrix[6][7]+1, matrix[7][6]+1, matrix[6][6]+0) = Min(2+1, 1+1, 1+0) = 1)

    // La distancia final es 1, ya que solo se necesita una sustitución ('e' por 'r' en la sexta posición)
    // para transformar "cheater" en "cheatrr".

    // PHP tiene una función nativa `levenshtein()` que es más eficiente que una implementación manual.
    // La usaremos para este propósito.
    return levenshtein($str1, $str2);
}

// -----------------------------------------------------
// 2. Función principal de búsqueda
// -----------------------------------------------------
/**
 * Realiza una búsqueda de palabras en el diccionario con tolerancia a errores.
 * Implementa una estrategia de búsqueda en dos niveles: primero exacta, luego aproximada.
 *
 * @param PDO $connection Objeto de conexión PDO a la base de datos.
 * @param string $searchTerm El término de búsqueda ingresado por el usuario.
 * @return array Un array de resultados, cada uno con 'word', 'definition' y 'distance'.
 */
function searchWord(PDO $connection, string $searchTerm): array
{
    $results = [];
    $searchTerm = trim(strtolower($searchTerm)); // Normalizar término de búsqueda a minúsculas y sin espacios extra

    // Comentarios explicando estrategia:
    // La estrategia de búsqueda se divide en dos niveles para priorizar la precisión:
    // 1. Búsqueda Exacta: Se intenta encontrar coincidencias directas o muy cercanas primero.
    // 2. Búsqueda Aproximada (Levenshtein): Si no se encuentran resultados exactos, se expande la búsqueda
    //    para incluir palabras con pequeñas diferencias, utilizando la distancia de Levenshtein.
    //    Esto mejora la experiencia del usuario al corregir errores tipográficos.

    // Nivel 1: Búsqueda exacta (o muy cercana con LIKE)
    // Se busca la palabra que coincida exactamente o que empiece con el término de búsqueda.
    // Esto es más rápido y devuelve los resultados más relevantes primero.
    $stmtExact = $connection->prepare("SELECT word, definition FROM words WHERE word LIKE :searchTermExact ORDER BY word ASC LIMIT 10");
    $stmtExact->execute([':searchTermExact' => $searchTerm . '%']); // Busca palabras que empiezan con el término
    $exactResults = $stmtExact->fetchAll(PDO::FETCH_ASSOC);

    // Si se encuentran resultados exactos o muy cercanos, los añadimos y terminamos la búsqueda.
    // Para este proyecto, si hay resultados exactos, no se procede con Levenshtein para mantener la simplicidad
    // y priorizar la relevancia directa.
    if (!empty($exactResults)) {
        foreach ($exactResults as $row) {
            $results[] = [
                'word' => $row['word'],
                'definition' => $row['definition'],
                'distance' => levenshteinDistance($searchTerm, strtolower($row['word'])) // Distancia 0 si es exacta
            ];
        }
        // Ordenar por distancia (0 primero) y luego alfabéticamente
        usort($results, function($a, $b) {
            if ($a['distance'] == $b['distance']) {
                return strcmp($a['word'], $b['word']);
            }
            return $a['distance'] <=> $b['distance'];
        });
        return array_slice($results, 0, 10); // Limitar a 10 resultados
    }

    // Nivel 2: Si no hay resultados exactos, buscar con Levenshtein <= 2
    // Se recuperan todas las palabras del diccionario para calcular la distancia de Levenshtein.
    // Esta es una operación que puede ser costosa para diccionarios muy grandes,
    // pero para 100 palabras es aceptable. Para diccionarios más grandes, se necesitarían
    // índices especializados o algoritmos de búsqueda de cadenas aproximada más avanzados.
    $stmtAllWords = $connection->query("SELECT word, definition FROM words");
    $allWords = $stmtAllWords->fetchAll(PDO::FETCH_ASSOC);

    $approximateResults = [];
    foreach ($allWords as $row) {
        $wordInDb = strtolower($row['word']);
        $distance = levenshteinDistance($searchTerm, $wordInDb);

        // Tolerancia de máximo 2 caracteres de diferencia
        if ($distance <= 2) {
            $approximateResults[] = [
                'word' => $row['word'],
                'definition' => $row['definition'],
                'distance' => $distance
            ];
        }
    }

    // Ordenar resultados por relevancia:
    // 1. Primero por distancia de Levenshtein (menor distancia = más relevante).
    // 2. Luego alfabéticamente por palabra si las distancias son iguales.
    usort($approximateResults, function($a, $b) {
        if ($a['distance'] == $b['distance']) {
            return strcmp($a['word'], $b['word']);
        }
        return $a['distance'] <=> $b['distance'];
    });

    // Limitar resultados a 10 palabras máximo
    return array_slice($approximateResults, 0, 10);
}

// Fin del archivo search.php