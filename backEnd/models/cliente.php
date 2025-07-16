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

class Cliente{

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerClientes(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM clientes");
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Solo devuelve datos asociativos
        } catch (PDOException $e) {
            error_log("Error en obtenerClientes: " . $e->getMessage()); // Logging
            return [];
        }
    }

    public function obtenerClientesNumeroDocumento($numero_documento): ?array {
        if (!is_numeric($numero_documento)) return null; // Validación básica

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE numero_documento = ?");
            $stmt->execute([$numero_documento]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerClientesNumeroDocumento: " . $e->getMessage());
            return null;
        }
    }

    public function crearCliente($data): bool {
        // Validación y sanitización básica
        $numero_documento = (int)($data['telefono'] ?? 0);
        $nombres = htmlspecialchars($data['nombres'] ?? '');
        $apellidos = htmlspecialchars($data['apellidos'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);
        $email = htmlspecialchars($data['email'] ?? '');

        try {
            $stmt = $this->pdo->prepare("INSERT INTO clientes VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$numero_documento, $nombres, $apellidos, $telefono, $email]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarCliente($numero_documento, $data): bool {
        if (!is_numeric($numero_documento)) return false;

        // Validación y sanitización
        $nombres = htmlspecialchars($data['nombres'] ?? '');
        $apellidos = htmlspecialchars($data['apellidos'] ?? '');
        $telefono = (int)($data['telefono'] ?? 0);
        $email = htmlspecialchars($data['email'] ?? '');

        try {
            $stmt = $this->pdo->prepare("UPDATE clientes SET nombres = ?, apellidos = ?, telefono = ?, email = ? WHERE numero_documento = ?");
            return $stmt->execute([$nombres, $apellidos, $telefono, $email, $numero_documento]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarCliente($numero_documento): bool {
        if (!is_numeric($numero_documento)) return false;

        try {
            $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE numero_documento = ?");
            return $stmt->execute([$numero_documento]);
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

}

?>