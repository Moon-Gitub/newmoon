<?php

require_once "modelos/mercadopago.modelo.php";
require_once "modelos/sistema_cobro.modelo.php";

class ControladorMercadoPago {

    /**
     * Obtener credenciales de MercadoPago desde .env o config
     */
    static public function ctrObtenerCredenciales() {
        
        // Primero intentar desde .env
        $clavePublica = $_ENV['MP_PUBLIC_KEY'] ?? getenv('MP_PUBLIC_KEY');
        $accessToken = $_ENV['MP_ACCESS_TOKEN'] ?? getenv('MP_ACCESS_TOKEN');
        
        // Si no están en .env, usar las del código (compatibilidad)
        if (empty($clavePublica) || empty($accessToken)) {
            $clavePublica = 'TEST-9e420918-959d-45dc-a85f-33bcda359e78';
            $accessToken = 'TEST-3927436741225472-082909-b379465087e47bff35a8716eb049526a-1188183100';
        }

        return [
            'public_key' => $clavePublica,
            'access_token' => $accessToken
        ];
    }

    /**
     * Calcular monto a cobrar según fecha y estado
     */
    static public function ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente) {
        
        $diaActual = date("d");
        $abonoMensual = $clienteMoon["mensual"];
        $estadoClienteBarra = '';
        $mensajeCliente = '';
        $muestroModal = false;
        $fijoModal = false;
        $aplicarRecargo = 0;

        // Cliente al día o con saldo a favor
        if ($ctaCteCliente["saldo"] <= 0) {
            return [
                'monto' => 0,
                'mostrar_modal' => false,
                'fijar_modal' => false,
                'mensaje' => '',
                'estado_barra' => '',
                'descripcion' => 'Cliente al día'
            ];
        }

        // Cliente con deuda
        if ($clienteMoon["estado_bloqueo"] == "1") {
            // Cliente bloqueado
            $estadoClienteBarra = 'style="background-color: red;"';
            $mensajeCliente = '<span><center>SISTEMA SUSPENDIDO. Regularice su situación</center></span>';
            $muestroModal = true;
            $fijoModal = true;
            $aplicarRecargo = 1.10;

        } else {
            // Cliente activo con deuda
            if ($diaActual > 4 && $diaActual <= 9) {
                $mensajeCliente = '<span style="font-size: 12px; color: #fff"><center>Estimado Cliente! Se le recuerda el abono mensual del sistema.</center></span>';
                $aplicarRecargo = 1.0;
                $muestroModal = true;

            } elseif ($diaActual > 10 && $diaActual <= 21) {
                $mensajeCliente = '<span style="font-size: 15px; color: #fff"><center>Estimado Cliente! Se le recuerda el abono mensual del sistema.</center></span>';
                $aplicarRecargo = 1.10;
                $muestroModal = true;

            } elseif ($diaActual > 21 && $diaActual <= 26) {
                $diasCorte = 26 - $diaActual;
                $estadoClienteBarra = 'style="background-color: orange;"';
                $mensajeCliente = '<span style="font-size: 20px"><center>Estimado cliente, solicitamos el abono mensual del uso del sistema. Restan '.$diasCorte.' días para proceder a la suspensión.</center></span>';
                $aplicarRecargo = 1.15;
                $muestroModal = true;

            } elseif ($diaActual > 26) {
                // Bloquear sistema
                ControladorSistemaCobro::ctrActualizarClientesCobro($clienteMoon['id'], 1);
                $estadoClienteBarra = 'style="background-color: red;"';
                $mensajeCliente = '<span><center>SISTEMA SUSPENDIDO. Regularice su situación</center></span>';
                $aplicarRecargo = 1.15;
                $muestroModal = true;
                $fijoModal = true;
            }
        }

        // Calcular monto final
        $montoFinal = $ctaCteCliente["saldo"] - $clienteMoon["mensual"] + ($abonoMensual * $aplicarRecargo);

        return [
            'monto' => round($montoFinal, 2),
            'mostrar_modal' => $muestroModal,
            'fijar_modal' => $fijoModal,
            'mensaje' => $mensajeCliente,
            'estado_barra' => $estadoClienteBarra,
            'descripcion' => 'Mensual-POS',
            'recargo_aplicado' => ($aplicarRecargo - 1) * 100 // Porcentaje
        ];
    }

    /**
     * Crear preferencia de pago
     */
    static public function ctrCrearPreferenciaPago($idClienteMoon, $clienteMoon, $datosCobro) {
        
        try {
            // Obtener credenciales
            $credenciales = self::ctrObtenerCredenciales();
            
            // Configurar SDK de MercadoPago
            require_once 'extensiones/vendor/autoload.php';
            MercadoPago\SDK::setAccessToken($credenciales['access_token']);

            // Crear preferencia
            $preference = new MercadoPago\Preference();

            // Item
            $item = new MercadoPago\Item();
            $item->title = $datosCobro['descripcion'];
            $item->quantity = 1;
            $item->unit_price = floatval($datosCobro['monto']);
            $preference->items = array($item);

            // URLs de retorno
            $baseUrl = self::ctrObtenerUrlBase();
            $preference->back_urls = array(
                "success" => $baseUrl . "/index.php?ruta=procesar-pago",
                "failure" => $baseUrl . "/index.php?ruta=procesar-pago",
                "pending" => $baseUrl . "/index.php?ruta=procesar-pago"
            );

            // URL de notificación (webhook)
            $preference->notification_url = $baseUrl . "/webhook-mercadopago.php";

            // Configuración adicional
            $preference->auto_return = "approved";
            $preference->binary_mode = true;

            // Metadata para identificar el pago
            $preference->metadata = array(
                "id_cliente_moon" => $idClienteMoon
            );

            // Guardar preferencia
            $preference->save();

            // Registrar intento en BD
            $datosIntento = array(
                "id_cliente_moon" => $idClienteMoon,
                "preference_id" => $preference->id,
                "monto" => $datosCobro['monto'],
                "descripcion" => $datosCobro['descripcion']
            );

            ModeloMercadoPago::mdlRegistrarIntentoPago($datosIntento);

            return $preference;

        } catch (Exception $e) {
            error_log("Error creando preferencia MP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Procesar notificación de pago (webhook)
     */
    static public function ctrProcesarNotificacionPago($topic, $id) {
        
        try {
            // Registrar webhook recibido
            $datosWebhook = array(
                "topic" => $topic,
                "resource_id" => $id,
                "datos_json" => json_encode($_GET)
            );
            $idWebhook = ModeloMercadoPago::mdlRegistrarWebhook($datosWebhook);

            // Solo procesar pagos
            if ($topic != 'payment') {
                return false;
            }

            // Obtener información del pago
            $credenciales = self::ctrObtenerCredenciales();
            MercadoPago\SDK::setAccessToken($credenciales['access_token']);

            $payment = MercadoPago\Payment::find_by_id($id);

            // Verificar que no se procesó antes
            if (ModeloMercadoPago::mdlVerificarPagoProcesado($payment->id)) {
                error_log("Pago ya procesado: " . $payment->id);
                return false;
            }

            // Procesar según estado
            if ($payment->status == 'approved') {
                
                // Obtener ID del cliente desde metadata
                $idClienteMoon = $payment->metadata->id_cliente_moon ?? null;

                if ($idClienteMoon) {
                    // Registrar pago confirmado
                    $datosPago = array(
                        "id_cliente_moon" => $idClienteMoon,
                        "payment_id" => $payment->id,
                        "preference_id" => $payment->additional_info->items[0]->id ?? '',
                        "monto" => $payment->transaction_amount,
                        "estado" => $payment->status,
                        "payment_type" => $payment->payment_type_id,
                        "payment_method_id" => $payment->payment_method_id,
                        "datos_json" => json_encode($payment)
                    );

                    ModeloMercadoPago::mdlRegistrarPagoConfirmado($datosPago);

                    // Actualizar cuenta corriente del cliente
                    ControladorSistemaCobro::ctrRegistrarPagoCliente(
                        $idClienteMoon, 
                        $payment->transaction_amount,
                        "Pago MercadoPago ID: " . $payment->id
                    );

                    // Desbloquear cliente si estaba bloqueado
                    ControladorSistemaCobro::ctrActualizarClientesCobro($idClienteMoon, 0);

                    error_log("Pago procesado exitosamente: " . $payment->id);
                }
            }

            // Marcar webhook como procesado
            if ($idWebhook) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($idWebhook);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error procesando notificación MP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener URL base del sitio
     */
    static private function ctrObtenerUrlBase() {
        
        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        
        return $protocolo . $host;
    }

    /**
     * Obtener historial de pagos
     */
    static public function ctrObtenerHistorialPagos($idClienteMoon) {
        return ModeloMercadoPago::mdlObtenerHistorialPagos($idClienteMoon);
    }
}

