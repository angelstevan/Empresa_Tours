<?php

// Permite solo orígenes específicos (ajusta a tu dominio o frontend local)
$allowed_origin = '*'; // cambia esto según tu entorno
header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Credentials: true"); // Si usas cookies o tokens

// Métodos y headers permitidos
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejo de solicitud preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Guia{

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerGuias(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM guias");
            $guias = $stmt->fetchAll(PDO::FETCH_ASSOC); // Solo devuelve datos asociativos
            if($guias){
                return $guias;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerGuias: " . $e->getMessage()); // Logging
            return [];
        }
    }

    public function obtenerGuiaIdentificacion($identificacion): ?array {
        if (!is_numeric($identificacion)) return null; // Validación básica

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM guias WHERE identificacion = ?");
            $stmt->execute([$identificacion]);
            $guia = $stmt->fetch(PDO::FETCH_ASSOC);

            if($guia){
                return $guia;
            }else{
                return null;
            }
            
        } catch (PDOException $e) {
            error_log("Error en obtenerGuiaIdentificacion: " . $e->getMessage());
            return null;
        }
    }

    public function crearGuia($data): bool {
        // Validación y sanitización básica
        $identificacion = (int)($data['identificacion'] ?? 0);
        $nombres = htmlspecialchars($data['nombres'] ?? '');
        $apellidos = htmlspecialchars($data['apellidos'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO guias VALUES (?, ?, ?, ?)");
            return $stmt->execute([$identificacion, $nombres, $apellidos, $telefono]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarGuia($identificacion, $data): bool {
        if (!is_numeric($identificacion)) return false;

        // Validación y sanitización
        $nombres = htmlspecialchars($data['nombres'] ?? '');
        $apellidos = htmlspecialchars($data['apellidos'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);

        try {
            $stmt = $this->pdo->prepare("UPDATE guias SET nombres = ?, apellidos = ?, telefono = ? WHERE identificacion = ?");
            return $stmt->execute([$nombres, $apellidos, $telefono, $identificacion]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarGuia($identificacion): bool {
        if (!is_numeric($identificacion)) return false;

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM guias WHERE identificacion = ?");
            $stmt->execute([$identificacion]);
            $guia = $stmt->fetch(PDO::FETCH_ASSOC);

            if($guia)
            {

                $stmt = $this->pdo->prepare("DELETE FROM guias WHERE identificacion = ?");
                return $stmt->execute([$identificacion]);

            }else{
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

}

?>