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

class Reserva{

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerReservas(): array {
        try {

            $stmt = $this->pdo->query("SELECT reservas.fecha_reserva, clientes.nombres, tours.nombre, tours.descripcion, 
            tours.ciudad, tours.precio, tours_has_reservas.cantidad_personas,reservas.total 
            FROM tours_has_reservas JOIN reservas ON reservas.id = tours_has_reservas.reserva_id 
            JOIN tours ON tours.id = tours_has_reservas.tour_id 
            JOIN clientes ON clientes.numero_documento = reservas.cliente_numero_documento WHERE reservas.estado = 'Creada'");

            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC); // Solo devuelve datos asociativos
            if($reservas){
                return $reservas;
            }else{
                return ["sin datos"];
            }
        } catch (PDOException $e) {
            error_log("Error en obtenerReservas: " . $e->getMessage()); // Logging
            return [];
        }
    }

    public function obtenerReservaID($id): ?array {
        if (!is_numeric($id)) return null; // Validación básica

        try {

            $stmt = $this->pdo->prepare("SELECT reservas.fecha_reserva, clientes.nombres, tours.nombre, tours.descripcion, 
            tours.ciudad, tours.precio, tours_has_reservas.cantidad_personas,reservas.total 
            FROM tours_has_reservas JOIN reservas ON reservas.id = tours_has_reservas.reserva_id 
            JOIN tours ON tours.id = tours_has_reservas.tour_id 
            JOIN clientes ON clientes.numero_documento = reservas.cliente_numero_documento WHERE reservas.id = ? AND reservas.estado = 'Creada'");
            $stmt->execute([$id]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

            if($reserva){
                return $reserva;
            }else{
                return null;
            }
            
        } catch (PDOException $e) {
            error_log("Error en obtenerReservaID: " . $e->getMessage());
            return null;
        }
    }

    public function crearReserva($data): array {
        // Validación y sanitización básica
        $idTour = (int)($data['idTour'] ?? 0);
        $cantidadPersonas = (int)($data['cantidadPersonas'] ?? 0);
        $clienteDocumento = (int)($data['clienteDocumento'] ?? 0);

        try {

            // validamos si existe el tour
            $stmtValidarTour = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmtValidarTour->execute([$idTour]);
            $tour = $stmtValidarTour->fetch(PDO::FETCH_ASSOC);

            if(!$tour){

              return ['success' => false, 'error' => 'El Tour no existe'];

            }

            // validamos si el cliente existe
            $stmtValidarCliente = $this->pdo->prepare("SELECT * FROM clientes WHERE numero_documento = ?");
            $stmtValidarCliente->execute([$clienteDocumento]);
            $cliente = $stmtValidarCliente->fetch(PDO::FETCH_ASSOC);

            if(!$cliente){

                return ['success' => false, 'error' => 'El cliente no existe'];

            }

            // se toma el valor de los cupos disponibles del tour
            $cuposDisponibles = $tour["cupos_totales"];

            if($cantidadPersonas <= 0 || $cantidadPersonas > $cuposDisponibles){

                return ['success' => false, 'error' => 'Cantidad de personas no válida o sin cupos disponibles'];

            }

            // se crea la reserva con el cliente
            $stmtReserva = $this->pdo->prepare("INSERT INTO reservas (fecha_reserva, cliente_numero_documento, estado) VALUES (NOW(), ?, 'Creada')");
            $stmtReserva->execute([$clienteDocumento]);

            // tomamos el id recien creado de la creacion de la reserva
            $idReservaGenerado = $this->pdo->lastInsertId();

            // si todo pasa procedemos a crear la reserva

            $stmtActualizarReserva = $this->pdo->prepare("UPDATE reservas SET total = ? WHERE id = ?");
            $stmtActualizarReserva->execute([$tour["precio"] * $cantidadPersonas, $idReservaGenerado]);

            $stmtTours_has_reservas = $this->pdo->prepare("INSERT INTO tours_has_reservas VALUES (?, ?, ?)");
            $stmtTours_has_reservas->execute([$idReservaGenerado, $idTour, $cantidadPersonas]);

            $stmtActualizarCantidadTour = $this->pdo->prepare("UPDATE tours SET cupos_totales = ? WHERE id = ?");
            $stmtActualizarCantidadTour->execute([$cuposDisponibles - $cantidadPersonas, $idTour]);

            return ['success' => true, 'message' => 'Reserva creada correctamente'];
            

        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos', 'details' => $e->getMessage()];
        }
    }

    public function actualizarReserva($idReserva, $data): array {
        if (!is_numeric($idReserva)) return ['success' => false, 'error' => 'El ID no es valido'];;

        // Validación y sanitización
        $clienteDocumento = (int)($data['clienteDocumento'] ?? 0);

        try {

            // validamos si el cliente existe
            $stmtValidarCliente = $this->pdo->prepare("SELECT * FROM clientes WHERE numero_documento = ?");
            $stmtValidarCliente->execute([$clienteDocumento]);
            $cliente = $stmtValidarCliente->fetch(PDO::FETCH_ASSOC);

            if(!$cliente){

                return ['success' => false, 'error' => 'El cliente no existe'];

            }

            $stmtActualizarReserva = $this->pdo->prepare("UPDATE reservas SET cliente_numero_documento = ? WHERE id = ?");
            $stmtActualizarReserva->execute([$clienteDocumento, $idReserva]);

            return ['success' => true, 'message' => 'Reserva actualizada correctamente'];

        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en base de datos', 'details' => $e->getMessage()];
        }
    }

    public function eliminarReserva($idReserva): bool {
        if (!is_numeric($idReserva)) return false;

        try {

            $stmt = $this->pdo->prepare("SELECT * FROM reservas WHERE id = ? AND estado = 'Creada'");
            $stmt->execute([$idReserva]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

            if($reserva)
            {

                $stmt = $this->pdo->prepare("UPDATE reservas SET estado = 'Eliminado' WHERE id = ?");
                return $stmt->execute([$idReserva]);


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