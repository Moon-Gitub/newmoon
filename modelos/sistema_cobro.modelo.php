<?php

require_once "conexion.php";

class ModeloSistemaCobro {

    /**
     * Conectar a BD Moon para ver estado cliente
     */
    static public function conectarMoon() {
        
        // Intentar obtener credenciales desde .env
        $host = $_ENV['MOON_DB_HOST'] ?? getenv('MOON_DB_HOST');
        $db = $_ENV['MOON_DB_NAME'] ?? getenv('MOON_DB_NAME');
        $user = $_ENV['MOON_DB_USER'] ?? getenv('MOON_DB_USER');
        $pass = $_ENV['MOON_DB_PASS'] ?? getenv('MOON_DB_PASS');
        
        // Si no están en .env, usar valores por defecto (compatibilidad)
        if (empty($host) || empty($db) || empty($user) || empty($pass)) {
            $host = '107.161.23.241';
            $db = 'moondesa_moon';
            $user = 'moondesa_moon';
            $pass = 'F!b+hn#i3Vk-';
        }

        try {
            $link = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $link->exec("set names utf8");
            return $link;
            
        } catch (PDOException $e) {
            error_log("Error conectando a Moon DB: " . $e->getMessage());
            throw new Exception("Error de conexión a sistema de cobro");
        }
    }

    /**
     * Mostrar cliente de sistema de cobro
     */
    static public function mdlMostrarClienteCobro($idCliente) {
        
        try {
            $stmt = self::conectarMoon()->prepare("
                SELECT * FROM clientes 
                WHERE id = :id
            ");

            $stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();

        } catch (Exception $e) {
            error_log("Error obteniendo cliente Moon: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mostrar saldo de cuenta corriente
     */
    static public function mdlMostrarSaldoCuentaCorriente($idCliente) {
        
        try {
            $stmt = self::conectarMoon()->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN tipo = 1 THEN importe ELSE -importe END), 0) as saldo
                FROM cuenta_corriente
                WHERE id_cliente = :id_cliente
            ");

            $stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();

        } catch (Exception $e) {
            error_log("Error obteniendo saldo: " . $e->getMessage());
            return ['saldo' => 0];
        }
    }

    /**
     * Mostrar movimientos de cuenta corriente
     */
    static public function mdlMostrarMovimientoCuentaCorriente($idCliente) {
        
        try {
            $stmt = self::conectarMoon()->prepare("
                SELECT * FROM cuenta_corriente 
                WHERE id_cliente = :id_cliente 
                ORDER BY fecha DESC 
                LIMIT 10
            ");

            $stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error obteniendo movimientos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar estado de bloqueo del cliente
     */
    static public function mdlActualizarClienteCobro($idCliente, $estadoBloqueo) {
        
        try {
            $stmt = self::conectarMoon()->prepare("
                UPDATE clientes 
                SET estado_bloqueo = :estado_bloqueo 
                WHERE id = :id
            ");

            $stmt->bindParam(":estado_bloqueo", $estadoBloqueo, PDO::PARAM_INT);
            $stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error actualizando cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar pago de cliente
     */
    static public function mdlRegistrarPagoCliente($idCliente, $monto, $descripcion) {
        
        try {
            $stmt = self::conectarMoon()->prepare("
                INSERT INTO cuenta_corriente 
                (id_cliente, tipo, importe, descripcion, fecha)
                VALUES (:id_cliente, 2, :importe, :descripcion, NOW())
            ");

            $stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
            $stmt->bindParam(":importe", $monto, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Error registrando pago: " . $e->getMessage());
            return false;
        }
    }
}
