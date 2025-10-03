<?php
// Archivo: index.php
// Propósito: Interfaz principal del diccionario web. Maneja la entrada del usuario,
//            la lógica de búsqueda y la presentación de resultados.
// Autor: Blackbox AI Assistant
// Fecha: 2024-01-15

// Requisitos del servidor: PHP 7.4+
//                         Extensión PDO de PHP habilitada.

// Incluir la configuración de la base de datos y la lógica de búsqueda
require_once 'config.php';
require_once 'search.php';

// Inicializar variables
$searchTerm = '';
$searchResults = [];
$message = '';
$hasExactMatch = false; // Para saber si se encontró una coincidencia exacta

// -----------------------------------------------------
// Lógica PHP para procesar la búsqueda
// -----------------------------------------------------
// Si hay un término de búsqueda en la URL (parámetro 'q' en GET)
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $searchTerm = htmlspecialchars(trim($_GET['q'])); // Sanitizar input del usuario
    $searchResults = searchWord($pdo, $searchTerm); // Ejecutar la función de búsqueda

    // Determinar si hay una coincidencia exacta entre los resultados
    foreach ($searchResults as $result) {
        if (strtolower($result['word']) === strtolower($searchTerm)) {
            $hasExactMatch = true;
            break;
        }
    }

    // Generar mensajes basados en los resultados
    if (empty($searchResults)) {
        $message = "No se encontraron resultados para '" . $searchTerm . "'.";
    } elseif (!$hasExactMatch && !empty($searchResults)) {
        // Si no hay resultados exactos pero sí similares
        $message = "No se encontró '" . $searchTerm . "'. ¿Quisiste decir...?:";
    }
} else {
    // Si no hay búsqueda, mostrar mensaje de bienvenida
    $message = "Bienvenido al Diccionario Web. ¡Busca una palabra!";
}

// Cerrar la conexión a la base de datos al final del script
closeDbConnection($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Meta tags, título, CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diccionario Web</title>
    <!-- Enlace al archivo de estilos CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- Favicon (opcional) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📚</text></svg>">
</head>
<body>
    <!-- Barra superior con título del diccionario -->
    <header class="header">
        <h1 class="header-title">Diccionario Web</h1>
    </header>

    <!-- Sección de búsqueda -->
    <main class="main-content">
        <section class="search-section">
            <form method="GET" action="index.php" class="search-form">
                <input
                    type="search"
                    name="q"
                    placeholder="Busca una palabra..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                    class="search-input"
                    aria-label="Término de búsqueda"
                >
                <button type="submit" class="search-button">Buscar</button>
            </form>
        </section>

        <!-- Panel de resultados -->
        <section class="results-section">
            <?php if (!empty($message)): ?>
                <p class="info-message <?php echo (strpos($message, 'No se encontraron') !== false) ? 'error-message' : ''; ?>">
                    <?php echo $message; ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($searchResults) && ($hasExactMatch || strpos($message, 'Quisiste decir') !== false)): ?>
                <div class="results-grid">
                    <?php foreach ($searchResults as $result): ?>
                        <div class="result-card">
                            <h3 class="result-word"><?php echo htmlspecialchars($result['word']); ?></h3>
                            <p class="result-definition"><?php echo htmlspecialchars($result['definition']); ?></p>
                            <?php if ($result['distance'] > 0): ?>
                                <span class="result-distance">Distancia: <?php echo $result['distance']; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer con información -->
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Diccionario Web. Desarrollado por Blackbox AI Assistant.</p>
    </footer>
</body>
</html>