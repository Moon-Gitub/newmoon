<?php

//========================
// CONEXION AFIP
//========================
$conAfip = false;
$msjError="";
if($arrayEmpresa["entorno_facturacion"]){

 try {

   $wsaa = new WSAA($arrayEmpresa);

   if (date('Y-m-d H:i:s', strtotime($wsaa->get_expiration())) < date('Y-m-d H:i:s')) {
     $wsaa->generar_TA();
   }

   $wsfe = new WSFE($arrayEmpresa);
   $test = $wsfe->openTA();

   if (isset($test)){
       $conAfip = true;
   } else {
       $conAfip = false;
   }

 } catch (Exception $e) {
   $conAfip = false;
   $msjError = $e->getMessage();
 }
}

//========================
// ARCHIVO COTIZACION
//========================
$result=[];
if ($file = fopen("cotizacion", "r")) {
    $i = 0;
    while(!feof($file)) {
        $line = fgets($file);
        $result[$i] = $line;
        $i++;
    }
    fclose($file);
} else {
    $result[0]="No se pudo cargar la ultima cotización";
    $result[1]="0,00";
}

//==================================
//      SISTEMA DE COBRO MEJORADO
//==================================

// Configuración del cliente
$idCliente = 1; // ID del cliente en sistema Moon

// Obtener credenciales de MercadoPago
$credencialesMP = ControladorMercadoPago::ctrObtenerCredenciales();
$clavePublicaMercadoPago = $credencialesMP['public_key'];
$accesTokenMercadoPago = $credencialesMP['access_token'];

// Obtener datos del cliente
$clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
$ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);

// Calcular datos de cobro
$datosCobro = ControladorMercadoPago::ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente);

// Variables para la vista
$muestroModal = $datosCobro['mostrar_modal'];
$fijoModal = $datosCobro['fijar_modal'];
$estadoClienteBarra = $datosCobro['estado_barra'];
$mensajeCliente = $datosCobro['mensaje'];
$abonoMensual = $datosCobro['monto'];

// Crear preferencia de pago si hay deuda y no existe ya
$preference = null;
if ($abonoMensual > 0 && !isset($_GET["preference_id"])) {
    $preference = ControladorMercadoPago::ctrCrearPreferenciaPago($idCliente, $clienteMoon, $datosCobro);
}

//==================================
//      FIN SISTEMA DE COBRO
//==================================

?>
 <header class="main-header">
    
    <!--=====================================
    LOGOTIPO
    ======================================-->
    <a href="inicio" class="logo">
        <!-- logo mini -->
        <span class="logo-mini">
            <i class="fa fa-moon-o fa-2x"></i>
        </span>

        <!-- logo normal -->
        <span class="logo-lg">
            <i class="fa fa-moon-o fa-2x"></i>
            POS | Moon
        </span>
    </a>

    <!--=====================================
    BARRA DE NAVEGACIÓN
    ======================================-->
    <nav class="navbar navbar-static-top" <?php echo $estadoClienteBarra; ?> role="navigation">

        <!-- Botón de navegación -->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <!-- perfil de usuario -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li>
                    <?php echo $mensajeCliente; ?>
                </li>

                <li class="dropdown tasks-menu" style="display: none" id="alertaTiempoSesionRestanteLi">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-clock-o"></i>
                      <span title="Tiempo restante de sesión" class="label label-danger" id="alertaTiempoSesionRestante"></span>
                    </a>
                </li>

                <?php if($_SESSION["perfil"] == "Administrador") { ?>
                
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Estado de Cuenta Moon">
                      <i class="fa fa-moon-o" aria-hidden="true"></i>
                      <?php if($abonoMensual > 0) { ?>
                      <span class="label label-warning"><?php echo number_format($abonoMensual, 0); ?></span>
                      <?php } ?>
                    </a>
                    <ul class="dropdown-menu" style="min-width: 320px;">
                        <li class="header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px;">
                            <i class="fa fa-moon-o"></i> Moon Desarrollos
                        </li>
                        <li>
                           <input type="hidden" id="hiddenClavePublicaMP" value="<?php echo $clavePublicaMercadoPago; ?>">
                            <ul class="menu" style="background-color: #fff; padding: 15px;">
                                <?php 
                                if($abonoMensual > 0) {
                                    echo '<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px; margin-bottom: 10px;">';
                                    echo '<div style="font-size: 13px; color: #6c757d; margin-bottom: 5px;">Saldo Pendiente</div>';
                                    echo '<div style="font-size: 28px; font-weight: 700; color: #dc3545;">$' . number_format($abonoMensual, 2) . '</div>';
                                    echo '</div>';
                                    
                                    if(isset($datosCobro['recargo_aplicado']) && $datosCobro['recargo_aplicado'] > 0) {
                                        echo '<div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 12px;">';
                                        echo '<i class="fa fa-exclamation-triangle" style="color: #ffc107;"></i> ';
                                        echo 'Recargo aplicado: ' . number_format($datosCobro['recargo_aplicado'], 0) . '%';
                                        echo '</div>';
                                    }
                                    
                                    echo '<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalCobro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; font-weight: 600; border-radius: 6px;">';
                                    echo '<i class="fa fa-credit-card"></i> Pagar Ahora';
                                    echo '</button>';
                                } else {
                                    echo '<div style="text-align: center; padding: 20px;">';
                                    echo '<i class="fa fa-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 10px;"></i>';
                                    echo '<p style="margin: 0; font-weight: 600; color: #28a745;">¡Cuenta al día!</p>';
                                    echo '<p style="margin: 5px 0 0 0; font-size: 13px; color: #6c757d;">No hay pagos pendientes</p>';
                                    echo '</div>';
                                }
                                ?>
                            </ul>
                        </li>
                    </ul>
                </li>   
            
                <?php } ?>   

                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <img src="vistas/img/plantilla/afipicon.ico" >
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000"><img src="vistas/img/plantilla/AFIPlogoChico.png" width="30%"></li>
                        <li>
                            <ul class="menu" style="background-color: #eee;">
                                <?php 
                                echo '<p>Conexion con servidor de AFIP ';

                                if ( $conAfip ){
                                  $fecform = date_create($wsfe->datosTA()["Exp"]);
                                  echo '<i class="fa fa-check-circle-o fa-2x" style="color: green"></i></p>';
                                  echo '<p>CUIT: '. $arrayEmpresa['cuit'] . '</p>
                                  <p>Ticket acceso valido hasta: <br/>' . $fecform->format('d/m/Y - H:i:s') .' </p>';
                                  echo '<p>Entorno: ' .$arrayEmpresa['entorno_facturacion'] . '</p>';
                                } else {
                                    echo '<i class="fa fa-times-circle-o fa-2x" style="color: red"></i></p>';
                                    echo $msjError;
                                }
                                ?>
                            <li class="footer"></li>
                            </ul>
                        </li>
                    </ul>
                </li>               

            <?php if($objParametros->getPrecioDolar()) { ?>
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-money"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000; color: #fff">Ultima actualizacion dolar</li>
                        <li>
                            <ul class="menu" style="background-color: #eee;">
                                <?php
                                 echo '<li>
                                      <h4>
                                        Fecha: <span>'.$result[0].'</span>
                                      </h4>
                                       <h4>
                                        Valor: $ <span id="cabezoteCotizacionPesos">'. $result[1] .'</span>
                                      </h4>
                                  </li>';
                              ?>
                                  <li class="footer">
                                    <center>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaCotizacion">
                                      Nueva Cotización
                                        </button>
                                    </center>
                                  </li>
                        </ul>
                      </li>
                    </ul>
                  </li>
            <?php } ?>
            
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <?php
                    if($_SESSION["foto"] != ""){
                        echo '<img src="'.$_SESSION["foto"].'" class="user-image">';
                    }else{
                        echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';
                    }
                    ?>
                        <span class="hidden-xs"><?php  echo $_SESSION["nombre"]; ?></span>
                    </a>

                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000; color: #fff; padding: 5px">Datos usuario</li>
                        <li>
                            <ul class="menu" style="background-color: #eee;">
                                <p>Nombre: <?php echo $_SESSION["nombre"]; ?></p>
                                <p>Usuario: <?php echo $_SESSION["usuario"]; ?></p>
                                <p>Perfil: <?php echo $_SESSION["perfil"]; ?></p>
                                <center>
                                    <a href="salir" class="btn btn-primary ">Salir</a>
                                </center>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
 </header>

 <!--=====================================
MODAL NUEVA COTIZACION
======================================-->
<div id="modalNuevaCotizacion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">

        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Nueva Cotización</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <?php
                    date_default_timezone_set('America/Argentina/Buenos_Aires');
                    $fecha = date('d-m-Y');
                ?>
                <input type="text" readonly class="form-control input-lg" id="nuevaCotizacionFecha" name="nuevaCotizacionFecha" value="<?php echo $fecha; ?> ">
              </div>
            </div>
  
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <input type="number" step="0.01" min="0" class="form-control input-lg" name="nuevaCotizacionPesos" placeholder="Ingresar cotización" required>
              </div>
            </div>
  
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cotización</button>
        </div>

        <?php
          $nuevaCotizacion = new ControladorCotizacion();
          $nuevaCotizacion -> ctrNuevaCotizacion();
        ?>

      </form>
    </div>
  </div>
</div>  

<!--=====================================
MODAL COBRO - DISEÑO MEJORADO
======================================-->
<style>
.modal-cobro-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 5px 5px 0 0;
    text-align: center;
}

.modal-cobro-header h3 {
    margin: 0;
    font-weight: 300;
    font-size: 28px;
}

.modal-cobro-header .icon-moon {
    font-size: 50px;
    margin-bottom: 10px;
}

.alert-cobro {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    margin: 20px 0;
    border-radius: 4px;
}

.alert-cobro i {
    color: #ffc107;
    margin-right: 10px;
    font-size: 20px;
}

.info-cliente-cobro {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.info-cliente-cobro .row {
    margin-bottom: 10px;
}

.info-cliente-cobro .label-info {
    color: #6c757d;
    font-size: 13px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.info-cliente-cobro .value-info {
    color: #212529;
    font-size: 16px;
    font-weight: 400;
}

.total-cobro-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 8px;
    text-align: center;
    margin: 20px 0;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.total-cobro-box .label-total {
    font-size: 14px;
    font-weight: 300;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.total-cobro-box .monto-total {
    font-size: 42px;
    font-weight: 700;
    margin: 10px 0;
}

.btn-pagar-wrapper {
    text-align: center;
    margin: 25px 0;
}

.checkout-btn {
    display: inline-block;
}

.checkout-btn button,
.checkout-btn a {
    background: #009ee3 !important;
    color: white !important;
    border: none !important;
    padding: 15px 50px !important;
    font-size: 18px !important;
    font-weight: 600 !important;
    border-radius: 50px !important;
    box-shadow: 0 4px 15px rgba(0, 158, 227, 0.3) !important;
    transition: all 0.3s ease !important;
    text-decoration: none !important;
    display: inline-block !important;
}

.checkout-btn button:hover,
.checkout-btn a:hover {
    background: #0082be !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 158, 227, 0.4) !important;
}

.icono-servicio {
    font-size: 40px;
    color: #667eea;
    margin-right: 15px;
}

.detalle-recargo {
    background: #fff3cd;
    padding: 10px 15px;
    border-radius: 6px;
    margin: 15px 0;
    font-size: 13px;
    color: #856404;
}

.detalle-recargo i {
    color: #ffc107;
    margin-right: 8px;
}

.logo-mp {
    max-width: 150px;
    margin: 20px auto;
    display: block;
}

.metodos-pago {
    text-align: center;
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.metodos-pago p {
    margin: 5px 0;
    color: #6c757d;
    font-size: 13px;
}

.metodos-pago i {
    font-size: 24px;
    margin: 0 8px;
    color: #667eea;
}
</style>

<div id="modalCobro" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 10px; overflow: hidden;">

      <!-- HEADER MEJORADO -->
      <div class="modal-cobro-header">
        <div class="icon-moon">
          <i class="fa fa-moon-o"></i>
        </div>
        <h3>Sistema de Cobro Moon POS</h3>
        <p style="margin: 10px 0 0 0; font-weight: 300; opacity: 0.9;">Servicio Mensual</p>
      </div>

      <!-- BODY MEJORADO -->
      <div class="modal-body" style="padding: 30px;">
        
        <!-- ALERTA IMPORTANTE -->
        <div class="alert-cobro">
          <i class="fa fa-exclamation-triangle"></i>
          <strong>Información Importante:</strong><br>
          Los pagos del servicio mensual deberán realizarse <strong>antes del día 10</strong> de cada mes.<br>
          <small>
            • Del día 10 al 20: <strong>+10% de recargo</strong><br>
            • Del día 20 al 25: <strong>+15% de recargo</strong><br>
            • Después del día 25: <strong>Sistema suspendido</strong>
          </small>
        </div>

        <!-- INFORMACIÓN DEL CLIENTE -->
        <div class="info-cliente-cobro">
          <div class="row">
            <div class="col-xs-12">
              <h4 style="margin-top: 0; color: #667eea;">
                <i class="icono-servicio fa fa-user-circle"></i>
                Detalle del Servicio
              </h4>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="label-info">Cliente</div>
              <div class="value-info"><?php echo $clienteMoon["nombre"]; ?></div>
            </div>
            <div class="col-sm-6">
              <div class="label-info">Servicio</div>
              <div class="value-info">
                <i class="fa fa-desktop" style="color: #667eea; margin-right: 5px;"></i>
                <?php echo $datosCobro['descripcion']; ?>
              </div>
            </div>
          </div>

          <?php if(isset($datosCobro['recargo_aplicado']) && $datosCobro['recargo_aplicado'] > 0) { ?>
          <div class="row" style="margin-top: 15px;">
            <div class="col-xs-12">
              <div class="detalle-recargo">
                <i class="fa fa-info-circle"></i>
                <strong>Recargo aplicado:</strong> <?php echo number_format($datosCobro['recargo_aplicado'], 0); ?>%
                por pago fuera de término
              </div>
            </div>
          </div>
          <?php } ?>
        </div>

        <!-- TOTAL A PAGAR -->
        <div class="total-cobro-box">
          <div class="label-total">Total a Pagar</div>
          <div class="monto-total">
            $<?php echo number_format($abonoMensual, 2); ?>
          </div>
          <div style="font-size: 13px; font-weight: 300; margin-top: 5px;">
            <i class="fa fa-calendar-o"></i>
            Pago correspondiente a <?php echo date('F Y'); ?>
          </div>
        </div>

        <!-- MÉTODOS DE PAGO -->
        <div class="metodos-pago">
          <p style="font-weight: 600; color: #212529; margin-bottom: 10px;">
            Métodos de pago disponibles
          </p>
          <i class="fa fa-credit-card" title="Tarjeta de Crédito"></i>
          <i class="fa fa-credit-card-alt" title="Tarjeta de Débito"></i>
          <i class="fa fa-money" title="Efectivo"></i>
          <i class="fa fa-university" title="Transferencia"></i>
          <p style="margin-top: 8px;">Pago 100% seguro con MercadoPago</p>
        </div>

        <!-- BOTÓN DE PAGO -->
        <div class="btn-pagar-wrapper">
          <div class="checkout-btn"></div>
        </div>

        <?php if($muestroModal && $preference) { ?>
        <!-- SDK MERCADOPAGO -->
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script type="text/javascript">
        (function() {
            var clavePublicaMP = document.getElementById('hiddenClavePublicaMP').value;
            const mp = new MercadoPago(clavePublicaMP, {locale: "es-AR"});

            mp.checkout({
                preference: {
                    id: '<?php echo $preference->id; ?>',
                },
                render: {
                    container: '.checkout-btn',
                    label: 'Pagar con MercadoPago',
                },
            });
        })();
        </script>
        <?php } ?>

        <!-- LOGO MERCADOPAGO -->
        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
          <img src="vistas/img/mp.png" alt="MercadoPago" style="max-width: 120px; opacity: 0.7;">
          <p style="font-size: 11px; color: #6c757d; margin-top: 5px;">
            Procesado de forma segura por MercadoPago
          </p>
        </div>

      </div>

      <!-- FOOTER OPCIONAL -->
      <div class="modal-footer" style="background: #f8f9fa; text-align: center;">
        <p style="margin: 0; font-size: 12px; color: #6c757d;">
          <i class="fa fa-lock" style="color: #28a745;"></i>
          Tus datos están protegidos con encriptación SSL
        </p>
      </div>

    </div>
  </div>
</div>

<script type="text/javascript">
    $(function(){

        <?php if($muestroModal && $fijoModal) { ?>
            $("#modalCobro").modal({backdrop: 'static', keyboard: false});
        <?php } elseif ($muestroModal) {?>
                var diaDeHoyModal = new Date, dateCformat = [diaDeHoyModal.getDate(), (diaDeHoyModal.getMonth()+1), diaDeHoyModal.getFullYear()].join('/');
                var diaAnterior = localStorage.getItem('diaMostrandoModal');
                if(dateCformat != diaAnterior){
                    var cantidadMostrado = Number(localStorage.getItem('modalCobroMostrado'));
                    if(!cantidadMostrado){
                        localStorage.setItem('modalCobroMostrado', 0);
                    }
                    if(cantidadMostrado != 3) {
                        $("#modalCobro").modal();
                        cantidadMostrado = cantidadMostrado + 1;
                        localStorage.setItem('modalCobroMostrado', cantidadMostrado);
                    } else if (cantidadMostrado == 3) {
                        localStorage.setItem('diaMostrandoModal', dateCformat);
                        localStorage.setItem('modalCobroMostrado', 0);
                    }
                }
    <?php } ?>
    
    });
</script>

