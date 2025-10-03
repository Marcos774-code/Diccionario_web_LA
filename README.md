# Diccionario Web con Búsqueda Tolerante a Errores

Este proyecto implementa un diccionario web simple en PHP con una base de datos MySQL, que incluye una funcionalidad de búsqueda tolerante a errores utilizando el algoritmo de Levenshtein.

## Características

*   **Base de Datos MySQL:** Almacena palabras y sus definiciones.
*   **Importación CSV:** Script para cargar datos desde un archivo CSV.
*   **Búsqueda en 2 Niveles:**
    *   Búsqueda exacta (o por prefijo).
    *   Búsqueda aproximada con Levenshtein (tolerancia de hasta 2 caracteres de diferencia) si no hay resultados exactos.
*   **Interfaz Web:** HTML con estilos CSS responsivos.
*   **Código Limpio y Comentado:** Cada archivo incluye comentarios detallados sobre su propósito, decisiones de diseño y funcionamiento.
*   **Seguridad Básica:** Uso de Prepared Statements para prevenir SQL Injection.

## Estructura de Archivos


## Requisitos del Servidor

*   **Servidor Web:** Apache o Nginx (con PHP-FPM)
*   **PHP:** Versión 7.4 o superior
    *   Extensión `pdo_mysql` habilitada.
*   **MySQL:** Versión 5.7 o superior

## Instrucciones de Instalación Paso a Paso

Sigue estos pasos para configurar y ejecutar el diccionario web en tu entorno local:

### **Paso 1: Descargar los Archivos**

1.  Crea una carpeta en tu servidor web (por ejemplo, `htdocs/diccionario-web/` si usas XAMPP/WAMP, o `www/diccionario-web/` si usas MAMP).
2.  Guarda todos los archivos proporcionados (`config.php`, `create_database.sql`, `import_csv.php`, `search.php`, `index.php`, `styles.css`, `diccionario.csv`) dentro de esta carpeta.

### **Paso 2: Configurar la Base de Datos MySQL**

1.  **Accede a MySQL:** Abre tu cliente MySQL (phpMyAdmin, MySQL Workbench, o la línea de comandos).
2.  **Ejecuta `create_database.sql`:**
    *   Abre el archivo `create_database.sql`.
    *   Copia todo el contenido del archivo.
    *   Pega y ejecuta el script en tu cliente MySQL. Esto creará la base de datos `dictionary_db` y la tabla `words`.

    ```sql
    -- Ejemplo de ejecución en línea de comandos MySQL:
    -- mysql -u tu_usuario -p < create_database.sql
    ```

### **Paso 3: Configurar `config.php`**

1.  Abre el archivo `config.php`.
2.  Modifica las siguientes constantes con tus credenciales de MySQL:

    ```php
    define('DB_HOST', 'localhost');     // Generalmente 'localhost'
    define('DB_USER', 'root');         // Tu usuario de MySQL
    define('DB_PASS', '');             // Tu contraseña de MySQL (vacío si no tienes)
    define('DB_NAME', 'dictionary_db'); // El nombre de la base de datos que creaste
    ```

### **Paso 4: Importar los Datos del CSV**

1.  Abre tu terminal o línea de comandos.
2.  Navega hasta la carpeta donde guardaste los archivos del proyecto (por ejemplo, `cd C:\xampp\htdocs\diccionario-web`).
3.  Ejecuta el script de importación:

    ```bash
    php import_csv.php
    ```
4.  Verifica la salida en la terminal. Deberías ver un reporte de cuántos registros fueron insertados y cuántos duplicados (si los hubiera) fueron encontrados.

### **Paso 5: Acceder al Diccionario Web**

1.  Abre tu navegador web.
2.  Navega a la URL donde está alojado tu proyecto. Por ejemplo:
    *   `http://localhost/diccionario-web/` (si usas XAMPP/WAMP)
    *   `http://localhost:8888/diccionario-web/` (si usas MAMP)

¡Listo! Deberías ver la interfaz del diccionario. Puedes empezar a buscar palabras.

## Explicación Conceptual del Algoritmo de Levenshtein

El algoritmo de Levenshtein, también conocido como "distancia de edición", es una métrica que cuantifica la diferencia entre dos secuencias de caracteres (cadenas). Se define como el **número mínimo de ediciones de un solo carácter** (inserciones, eliminaciones o sustituciones) necesarias para transformar una cadena en la otra.

### **¿Cómo funciona?**

Imagina que tienes dos palabras, por ejemplo, "gato" y "pato". Para transformar "gato" en "pato", solo necesitas una sustitución (cambiar la 'g' por una 'p'). Por lo tanto, la distancia de Levenshtein entre "gato" y "pato" es 1.

Si comparamos "casa" y "casas":
*   Para transformar "casa" en "casas", necesitas una inserción (añadir una 's' al final). Distancia = 1.

Si comparamos "mesa" y "masa":
*   Para transformar "mesa" en "masa", necesitas una sustitución (cambiar la 'e' por una 'a'). Distancia = 1.

Si comparamos "cheater" y "cheatrr":
*   Para transformar "cheater" en "cheatrr", necesitas una sustitución (cambiar la 'e' por una 'r' en la sexta posición). Distancia = 1.

El algoritmo logra esto construyendo una matriz (una tabla) donde cada celda representa la distancia entre un prefijo de la primera palabra y un prefijo de la segunda. Se llena esta matriz de forma sistemática, calculando el costo mínimo para llegar a cada estado. La celda final de la matriz contiene la distancia de Levenshtein entre las dos palabras completas.

### **Aplicación en el Diccionario**

En este diccionario web, el algoritmo de Levenshtein se utiliza para la **búsqueda tolerante a errores**. Cuando un usuario busca una palabra y no se encuentra una coincidencia exacta, el sistema calcula la distancia de Levenshtein entre el término de búsqueda del usuario y cada palabra en el diccionario.

*   Si la distancia es 0, significa que la palabra es una coincidencia exacta.
*   Si la distancia es 1 o 2 (como se configuró en este proyecto), se considera que la palabra es una "sugerencia" o una posible corrección de un error tipográfico.

Esto permite que el diccionario sugiera palabras relevantes incluso si el usuario comete pequeños errores al escribir, mejorando significativamente la experiencia de usuario.

---