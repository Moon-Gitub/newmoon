<?php

require_once "conexion.php";

class ModeloMercadoPago {

    /**
     * Registrar intento de pago
     */
    static public function mdlRegistrarIntentoPago($datos) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO mercadopago_intentos 
                (id_cliente_moon, preference_id, monto, descripcion, fecha_creacion, estado)
                VALUES (:id_cliente_moon, :preference_id, :monto, :descripcion, NOW(), 'pendiente')
            ");

            $stmt->bindParam(":id_cliente_moon", $datos["id_cliente_moon"], PDO::PARAM_INT);
            $stmt->bindParam(":preference_id", $datos["preference_id"], PDO::PARAM_STR);
            $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $stmt->lastInsertId();
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error registrando intento de pago: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar pago confirmado
     */
    static public function mdlRegistrarPagoConfirmado($datos) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO mercadopago_pagos 
                (id_cliente_moon, payment_id, preference_id, monto, estado, 
                fecha_pago, payment_type, payment_method_id, datos_json)
                VALUES (:id_cliente_moon, :payment_id, :preference_id, :monto, :estado,
                NOW(), :payment_type, :payment_method_id, :datos_json)
            ");

            $stmt->bindParam(":id_cliente_moon", $datos["id_cliente_moon"], PDO::PARAM_INT);
            $stmt->bindParam(":payment_id", $datos["payment_id"], PDO::PARAM_STR);
            $stmt->bindParam(":preference_id", $datos["preference_id"], PDO::PARAM_STR);
            $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":payment_type", $datos["payment_type"], PDO::PARAM_STR);
            $stmt->bindParam(":payment_method_id", $datos["payment_method_id"], PDO::PARAM_STR);
            $stmt->bindParam(":datos_json", $datos["datos_json"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $stmt->lastInsertId();
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error registrando pago confirmado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de intento
     */
    static public function mdlActualizarEstadoIntento($preferenceId, $estado) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE mercadopago_intentos 
                SET estado = :estado, fecha_actualizacion = NOW()
                WHERE preference_id = :preference_id
            ");

            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":preference_id", $preferenceId, PDO::PARAM_STR);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error actualizando estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de pagos de cliente
     */
    static public function mdlObtenerHistorialPagos($idClienteMoon) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM mercadopago_pagos 
                WHERE id_cliente_moon = :id_cliente_moon 
                ORDER BY fecha_pago DESC
            ");

            $stmt->bindParam(":id_cliente_moon", $idClienteMoon, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error obteniendo historial: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un pago ya fue procesado
     */
    static public function mdlVerificarPagoProcesado($paymentId) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM mercadopago_pagos 
                WHERE payment_id = :payment_id
            ");

            $stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_STR);
            $stmt->execute();

            $resultado = $stmt->fetch();
            return $resultado['total'] > 0;

        } catch (Exception $e) {
            error_log("Error verificando pago: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar webhook recibido
     */
    static public function mdlRegistrarWebhook($datos) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO mercadopago_webhooks 
                (topic, resource_id, datos_json, fecha_recibido, procesado)
                VALUES (:topic, :resource_id, :datos_json, NOW(), 0)
            ");

            $stmt->bindParam(":topic", $datos["topic"], PDO::PARAM_STR);
            $stmt->bindParam(":resource_id", $datos["resource_id"], PDO::PARAM_STR);
            $stmt->bindParam(":datos_json", $datos["datos_json"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $stmt->lastInsertId();
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error registrando webhook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar webhook como procesado
     */
    static public function mdlMarcarWebhookProcesado($id) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE mercadopago_webhooks 
                SET procesado = 1, fecha_procesado = NOW()
                WHERE id = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error marcando webhook: " . $e->getMessage());
            return false;
        }
    }
}

