<?php

require_once "modelos/sistema_cobro.modelo.php";

class ControladorSistemaCobro {

    /**
     * Mostrar cliente del sistema de cobro
     */
    static public function ctrMostrarClientesCobro($idCliente) {
        return ModeloSistemaCobro::mdlMostrarClienteCobro($idCliente);
    }

    /**
     * Mostrar saldo de cuenta corriente
     */
    static public function ctrMostrarSaldoCuentaCorriente($idCliente) {
        return ModeloSistemaCobro::mdlMostrarSaldoCuentaCorriente($idCliente);
    }

    /**
     * Mostrar movimientos de cuenta corriente
     */
    static public function ctrMostrarMovimientoCuentaCorriente($idCliente) {
        return ModeloSistemaCobro::mdlMostrarMovimientoCuentaCorriente($idCliente);
    }

    /**
     * Actualizar estado del cliente
     */
    static public function ctrActualizarClientesCobro($idCliente, $estadoBloqueo) {
        return ModeloSistemaCobro::mdlActualizarClienteCobro($idCliente, $estadoBloqueo);
    }

    /**
     * Registrar pago de cliente
     */
    static public function ctrRegistrarPagoCliente($idCliente, $monto, $descripcion) {
        return ModeloSistemaCobro::mdlRegistrarPagoCliente($idCliente, $monto, $descripcion);
    }
}
