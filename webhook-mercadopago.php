<?php

/**
 * Webhook de MercadoPago
 * Recibe notificaciones de pagos
 */

require_once "controladores/mercadopago.controlador.php";
require_once "controladores/sistema_cobro.controlador.php";
require_once "modelos/mercadopago.modelo.php";
require_once "modelos/sistema_cobro.modelo.php";

// Log de entrada
error_log("Webhook MP recibido: " . json_encode($_GET));

// Verificar que sea una notificación válida
if (!isset($_GET['topic']) || !isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

$topic = $_GET['topic'];
$id = $_GET['id'];

try {
    // Procesar notificación
    $resultado = ControladorMercadoPago::ctrProcesarNotificacionPago($topic, $id);

    if ($resultado) {
        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(200); // Igual 200 para que MP no reintente
        echo json_encode(['success' => false, 'message' => 'Ya procesado o no es payment']);
    }

} catch (Exception $e) {
    error_log("Error en webhook MP: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

