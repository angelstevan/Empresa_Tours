<?php
require_once './index.php';

// Encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json');

//  Instanciar la clase con manejo de error si $pdo no está definido
if (!isset($pdo)) {
    // Internal Server Error
    http_response_code(500); 
    echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
    exit();
}

$tour = new Tour($pdo);

switch ($method) {

    // --------------- OBTENER TODOS LOS TOURS ---------------
  case 'GET':
     // Obtener el id desde la query string y validar que sea numérico
    $idTour = $id ?? null;
    
    // --------------------- VERIFICAR SI MANDAMOS ID SI NO ENTONCES TRAE TODOS LOS GUIAS ---------------------------
    if(empty($idTour)){

        // conseguir guias
        try {
            // Obtener todos los guias
            $tours = $tour->obtenerTours();

            // Si hay pacientes, devolver con estado 200 OK
            if ($tours && count($tours) > 0) {
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $tours]);
            } else {
                // Si no hay datos, responder con 204 No Content (opcionalmente se puede usar 200 con lista vacía)
                http_response_code(204);
                echo json_encode(['success' => true, 'data' => []]);
            }
        } catch (Exception $e) {
            // Error inesperado al obtener los datos
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener los tours',
                'details' => $e->getMessage()
            ]);
        }
    break;
        //---------------- OBTENER POR IDENTIFICACION ------------
    }elseif(!$idTour || !is_numeric($idTour)){

         // Responder con error 400 si el Documento es inválido o no está presente
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido o no proporcionado']);
        exit();

    }else{

        try {
            // Intentar obtener el guia por ID
            $tours = $tour->obtenerTourID($idTour);

            if ($tours) {
                // Responder con código 200 OK y los datos
                http_response_code(200);
                echo json_encode(['success' => true, 'data' => $tours]);
            } else {
                // Responder con error 404 si el paciente no existe
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Tour no encontrado']);
            }
        } catch (Exception $e) {
            // Capturar errores inesperados y responder con error 500
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
        }
        break;

    }
    

    //----------- CREAR TOUR -------------

  case 'POST':
  
    //Leer y decodificar el cuerpo de la solicitud
    $data = json_decode(file_get_contents("php://input"), true);

    // Validar si el cuerpo tiene datos válidos
    if (!$data || !is_array($data)) {
        // Bad Request
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos o vacíos aca']);
        exit();
    }

    //  Instanciar la clase con manejo de error si $pdo no está definido
    if (!isset($pdo)) {
        // Internal Server Error
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'Error interno de base de datos']);
        exit();
    }

      // crear cliente
    try {
        $success = $tour->crearTour($data);

        if ($success) {
            // Created
            http_response_code(201); 
            echo json_encode(['success' => true, 'message' => 'Tour creado correctamente']);
        } else {
            // Bad Request (por ejemplo, datos incompletos)
            http_response_code(400); 
            echo json_encode(['success' => false, 'error' => 'No se pudo crear el tour']);
        }
    } catch (Exception $e) {
        // Internal Server Error
        http_response_code(500); 
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }

    break;

    //---------- ACTUALIZAR TOUR ----------

  case 'PUT':

    // Obtener el Documento desde la query string y validar que sea numérico
    $idTour = $id ?? null;
    if (!$idTour || !is_numeric($idTour)) {
        // Responder con error 400 si el Documento es inválido o no está presente
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido o no proporcionado']);
        exit();
    }

    // Leer y decodificar JSON del cuerpo de la petición
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !is_array($data)) {
        // Responder con error 400 si los datos JSON son inválidos o están vacíos
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos o vacíos']);
        exit();
    }

    // Verificar que la conexión PDO exista
    if (!isset($pdo)) {
        // Responder con error 500 si la conexión a la base de datos no está disponible
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexión a base de datos no disponible']);
        exit();
    }

    try {
        // Intentar actualizar el paciente con los datos recibidos
        $success = $tour->actualizarTour($idTour, $data);

        if ($success) {
            // Responder con código 200 si la actualización fue exitosa
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Tour actualizado correctamente']);
        } else {
            // Responder con error 400 si no se pudo actualizar el paciente
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el tour. Verifica los datos.']);
        }
    } catch (Exception $e) {
        // Capturar errores inesperados y responder con error 500
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }

    break;

    // ----------------- ELIMINAR TOUR -----------------

  case 'DELETE':

    // Obtener el Documento del cliente desde la query string y validar que sea numérico
    $idTour = $id ?? null;
    if (!$idTour || !is_numeric($idTour)) {
        // Responder con error 400 si el ID es inválido o no está presente
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido o no proporcionado']);
        exit();
    }

    // Verificar que la conexión PDO exista
    if (!isset($pdo)) {
        // Responder con error 500 si la conexión a la base de datos no está disponible
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: conexión a base de datos no disponible']);
        exit();
    }

    try {
        // Intentar eliminar el paciente con el ID proporcionado
        $success = $tour->eliminarTour($idTour);

        if ($success) {
            // Responder con código 200 No Content si la eliminación fue exitosa
            http_response_code(200);
            // No se envía contenido en el cuerpo para 204, pero se puede enviar mensaje si prefieres:
            echo json_encode(['success' => true, 'message' => 'Tour eliminado correctamente']);
        } else {
            // Responder con error 404 si no se encontró el paciente para eliminar
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Tour no encontrado o ya eliminado']);
        }
    } catch (Exception $e) {
        // Capturar errores inesperados y responder con error 500
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en el servidor', 'details' => $e->getMessage()]);
    }
   
    break;

  default:
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}



?>
