<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/config_define.php");


// Campos esperados en la solicitud POST
$campo_esperados = [
    'email' => ['label' => 'Email', 'minimo' => 1, 'maximo' => 320],
    'password' => ['label' => 'Contraseña', 'minimo' => 6, 'maximo' => 60],
];

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosMatriz($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaCamposConEspacioInicioFin($campo_esperados, $_POST);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final, los siguientes campos no cumplen eso: " . $errores_texto . ".");

        }


        //Se verifica que el email ingresado sea valido
        validarCampoEmail($_POST['email']);

        //Verifica que el password no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($_POST['password']);

        //Se obtiene los datos de la solicitud POST
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Se obtiene los datos del usuario 
        $usuario = login_usuario($conexion, $email, $password, 'mobile');

        http_response_code(200);
        $respuesta['usuario'] = $usuario;
    } else {
        http_response_code(405);
        throw new Exception("Metodo no permitido o datos no recibidos");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    http_response_code(400);
    $respuesta['mensaje'] = $e->getMessage();
}
//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
