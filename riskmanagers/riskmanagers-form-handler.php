<?php
// Endpoint para formulario riskmanagers
add_action('rest_api_init', function () {
    register_rest_route('riskmanagers/v1', '/enviar', array(
        'methods' => 'POST',
        'callback' => 'procesar_formulario_riskmanagers',
        'permission_callback' => '__return_true'
    ));
});

function procesar_formulario_riskmanagers($request) {
    // Obtener datos del formulario
    $params = $request->get_params();
    $nombre = $params['nombre'] ?? '';
    $apellido = $params['apellido'] ?? '';
    $correo = $params['correo'] ?? '';
    $telefono = $params['telefono'] ?? '';
    $servicio = $params['servicio'] ?? '';
    $modo_prueba = isset($params['modo_prueba']) ? true : false;

    // Mapa de correos por servicio
    $emailMap = array(
        'riesgos_comerciales' => array(
            'jvega@riskmanagers.com.do',
            'rginebra@riskmanagers.com.do',
            'kgarcía@riskmanagers.com.do',
            'ajerez@riskmanagers.com.do'
        ),
        'seguros_industriales' => array(
            'jvega@riskmanagers.com.do',
            'rginebra@riskmanagers.com.do',
            'kgarcía@riskmanagers.com.do',
            'ajerez@riskmanagers.com.do'
        ),
        'seguros_personas' => array(
            'lcastillo@riskmanagers.com.do',
            'cluciano@riskmanagers.com.do',
            'eguaba@riskmanagers.com.do',
            'penelope.ozuna@riskmanagers.com.do'
        ),
        'corretaje' => array(
            'jvega@riskmanagers.com.do',
            'rginebra@riskmanagers.com.do'
        ),
        'riesgos_generales' => array(
            'kgarcía@riskmanagers.com.do',
            'ajerez@riskmanagers.com.do'
        ),
        'automovil' => array(
            'qcaro@riskmanagers.com.do',
            'kgarcía@riskmanagers.com.do',
            'ajerez@riskmanagers.com.do'
        ),
        'reclamaciones' => array(
            'lreyes@riskmanagers.com.do',
            'eguaba@riskmanagers.com.do',
            'george.gautreaux@riskmanagers.com.do'
        )
    );

    // Obtener los correos de destino
    $emailsDestino = isset($emailMap[$servicio]) ? $emailMap[$servicio] : array('info@riskmanagers.com.do');

    // Configuración de correo
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: formulario@riskmanagers.com.do';

    // Crear el mensaje HTML
    $mensaje = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; }
            .header { background-color: #ff9933; color: white; padding: 10px; }
            .content { padding: 20px; }
            .footer { font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Nueva Solicitud de Servicio</h2>
            </div>
            <div class="content">
                <p><strong>Nombre:</strong> ' . esc_html($nombre) . ' ' . esc_html($apellido) . '</p>
                <p><strong>Correo:</strong> ' . esc_html($correo) . '</p>
                <p><strong>Teléfono:</strong> ' . esc_html($telefono) . '</p>
                <p><strong>Servicio Solicitado:</strong> ' . esc_html(ucfirst(str_replace('_', ' ', $servicio))) . '</p>';

    if ($modo_prueba) {
        $mensaje .= '<p><strong>MODO PRUEBA:</strong> Este correo fue enviado en modo prueba. Los destinatarios originales serían: ' . esc_html(implode(', ', $emailsDestino)) . '</p>';
    }

    $mensaje .= '
            </div>
            <div class="footer">
                <p>Este correo fue enviado desde el formulario de contacto de Riskmanagers.</p>
            </div>
        </div>
    </body>
    </html>';

    // Enviar el correo
    $asunto = "Nueva solicitud de servicio - " . ucfirst(str_replace('_', ' ', $servicio));
    if ($modo_prueba) {
        $asunto = "[MODO PRUEBA] " . $asunto;
    }

    $todosEnviados = true;

    // Si está en modo prueba, enviar solo al correo de prueba
    if ($modo_prueba) {
        $correo_prueba = 'ramfi.delacruz@s22.io';
        $enviado = wp_mail($correo_prueba, $asunto, $mensaje, $headers);
        if (!$enviado) {
            $todosEnviados = false;
            error_log("Error al enviar correo de prueba a: " . $correo_prueba);
        }
    } else {
        // Envío normal a todos los destinatarios
        foreach ($emailsDestino as $email) {
            $enviado = wp_mail($email, $asunto, $mensaje, $headers);
            if (!$enviado) {
                $todosEnviados = false;
                error_log("Error al enviar correo a: " . $email);
            }
        }
    }

    // Responder al cliente
    if ($todosEnviados) {
        return new WP_REST_Response(array(
            'success' => true,
            'message' => $modo_prueba ? 
                'Formulario enviado correctamente en modo prueba' : 
                'Formulario enviado correctamente a todos los destinatarios'
        ), 200);
    } else {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $modo_prueba ? 
                'Error al enviar el formulario en modo prueba' : 
                'Error al enviar el formulario a algunos destinatarios'
        ), 500);
    }
}