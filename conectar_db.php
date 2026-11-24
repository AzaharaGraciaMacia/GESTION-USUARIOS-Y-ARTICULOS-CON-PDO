<?php
// Archivo de conexión reutilizable

// Declaro constantes con los datos de mi BD
const HOSTNAME = "localhost";
const DATABASE = "azahara_tienda";
const USER_DB = "root";
const PASSWORD = "";
const PORT_DB = 3308;
// Conexión centralizada: la variable static guarda el objeto PDO entre llamadas,
// así toda la página reutiliza la misma conexión en vez de abrir una nueva cada vez.
function conectar(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . HOSTNAME . ";port=" . PORT_DB . ";dbname=" . DATABASE . ";charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, USER_DB, PASSWORD);
// Configuración de atributos de seguridad y rendimiento
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
// Registro en log y muestra de mensaje genérico
        error_log("Error PDO: " . $e->getMessage());
        die("Ocurrió un error al conectar con la base de datos.");
    }
}

?>