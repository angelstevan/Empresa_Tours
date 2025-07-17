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

class Tour{

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerTours(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM tours");
            $tours = $stmt->fetchAll(PDO::FETCH_ASSOC); // Solo devuelve datos asociativos
            if($tours){
                return $tours;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerTours: " . $e->getMessage()); // Logging
            return [];
        }
    }

    public function obtenerTourID($id): ?array {
        if (!is_numeric($id)) return null; // Validación básica

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmt->execute([$id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);

            if($tour){
                return $tour;
            }else{
                return null;
            }
            
        } catch (PDOException $e) {
            error_log("Error en obtenerTourID: " . $e->getMessage());
            return null;
        }
    }

    public function crearTour($data): bool {
        // Validación y sanitización básica
        $nombre = htmlspecialchars($data['nombre'] ?? '');
        $ciudad = htmlspecialchars($data['ciudad'] ?? '');
        $descripcion = htmlspecialchars($data['descripcion'] ?? '');
        $precio = (double)($data['precio'] ?? 0.00);
        $cuposTotales = (int)($data['cupos_totales'] ?? 0);
        $idGuia = (int)($data['guias_identificacion'] ?? 0);

        try {
            $stmt = $this->pdo->prepare("INSERT INTO tours (nombre, ciudad, descripcion, precio, cupos_totales, guias_identificacion) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$nombre, $ciudad, $descripcion, $precio, $cuposTotales, $idGuia]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarTour($id, $data): bool {
        if (!is_numeric($id)) return false;

        // Validación y sanitización
        $nombre = htmlspecialchars($data['nombre'] ?? '');
        $ciudad = htmlspecialchars($data['ciudad'] ?? '');
        $descripcion = htmlspecialchars($data['descripcion'] ?? '');
        $precio = (double)($data['precio'] ?? 0);
        $cuposTotales = (int)($data['cupos_totales'] ?? 0);
        $idGuia = (int)($data['guias_identificacion'] ?? 0);

        try {
            $stmt = $this->pdo->prepare("UPDATE tours SET nombre = ?, ciudad = ?, descripcion = ?, precio = ?, cupos_totales = ?, guias_identificacion = ? WHERE id = ?");
            return $stmt->execute([$nombre, $ciudad, $descripcion, $precio, $cuposTotales, $idGuia, $id]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarTour($id): bool {
        if (!is_numeric($id)) return false;

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmt->execute([$id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);

            if($tour)
            {

                $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id = ?");
                return $stmt->execute([$id]);

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